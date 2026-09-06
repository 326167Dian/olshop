<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\BundleDetail;
use App\Models\Product;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryBundleController extends Controller
{
    /**
     * Modul "Program Promo > Bundle/Paket Produk", mengikuti mod_bundle/bundle.php
     * (list + form tambah/ubah/detail) + mod_bundle/aksi_bundle.php (simpan/hapus) +
     * mod_bundle/autonamabarang.php (autocomplete item) + mod_bundle/hapusdetail_bundle.php
     * (hapus satu baris komponen saat edit).
     *
     * **Tidak digerbang flag admin manapun, mengikuti legacy persis** -- sidebar legacy
     * (`media_admin.php`) me-render link "Program Promo > Bundle/Paket Produk" TANPA
     * pembungkus `if ($_SESSION[...]=='Y')` sama sekali, satu-satunya menu top-level yang
     * begitu di seluruh sidebar. Rute di sini karena itu TIDAK diberi middleware
     * `inventory.module:x` (sama seperti konseling/meso/pio/pto/cpp/homecare yang juga
     * tanpa flag) -- semua admin yang sudah login bisa akses. Sidebar "Program Promo"
     * sendiri disuntikkan manual sebagai grup baru di `app.blade.php` (bukan lewat
     * `Admin::PERMISSION_GROUPS`, karena grup itu murni dikendalikan oleh keberadaan
     * kolom flag -- tidak ada kolom untuk modul ini), dirender tanpa syarat untuk admin
     * manapun yang login, sama seperti perlakuannya di legacy.
     *
     * **Fitur "kd_barang mulai dengan BUND" di layar Penjualan/Kasir SUDAH ADA lebih
     * dulu** (`InventoryTrkasirController::tambahBundle()`/`bundlePicker()`/
     * `bundleResolve()`, dibangun sewaktu porting modul `tpk`) -- yang benar-benar belum
     * ada, dan dibangun di sini, HANYA layar manajemen (CRUD) untuk mendefinisikan paket
     * bundle itu sendiri (nama, satuan, kuota, harga jual, daftar item komponen).
     *
     * **Bug nyata di `aksi_bundle.php` (act=input_bundle), TIDAK direplikasi:** kalau
     * admin membuat bundle baru dengan `nm_bundle` yang KEBETULAN sama dengan bundle
     * yang sudah ada, kode ini masuk ke cabang "UPDATE" tapi memakai `$kd_bundle` yang
     * BARU SAJA di-generate oleh `get_kode_bundle()` (bukan kode bundle lama yang benar2
     * cocok dengan nama itu) -- akibatnya `UPDATE bundle ... WHERE kd_bundle=$kodeBaru`
     * cocok 0 baris (kode itu belum pernah ada), lalu `bundle_detail` yang di-insert
     * berikutnya nempel ke `kd_bundle` yang TIDAK PUNYA baris header `bundle` sama sekali
     * -- bundle_detail yatim, tidak pernah muncul di manapun, data rusak diam-diam tanpa
     * error apapun. Diperbaiki dengan validasi `unique` pada `nm_bundle` saat membuat
     * bundle baru (pesan error jelas, bukan korupsi data diam-diam) -- mengubah nama
     * bundle yang sudah ada tetap bisa lewat form UBAH (`update()`), yang di legacy
     * sendiri memang jalur terpisah dan tidak kena bug ini.
     *
     * **Opsi Harga "Custom" per komponen dipertahankan** (bukan cuma pajangan) --
     * `subtotal` tiap baris dan validasi "Total item harus sama dengan Harga Jual
     * Bundling" sebelum submit murni untuk KONSISTENSI HARGA TAMPILAN/LAPORAN paket ini,
     * BUKAN yang benar-benar dipotong dari pelanggan (itu `bundle.hrgjual_bundle`, satu
     * harga paket flat) -- sesuai perilaku form aslinya.
     */
    public function index()
    {
        $bundles = Bundle::with('detail')->orderByDesc('id_bundle')->get();

        return view('inventory.bundle.index', ['judul' => 'Inventory', 'bundles' => $bundles]);
    }

    public function create()
    {
        return view('inventory.bundle.create', [
            'judul' => 'Inventory',
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBundle($request, null);

        $admin = Auth::guard('admin')->user();
        $kdBundle = $this->generateKodeBundle();

        DB::transaction(function () use ($validated, $admin, $kdBundle) {
            Bundle::create([
                'kd_bundle' => $kdBundle,
                'nm_bundle' => $validated['nm_bundle'],
                'sat_bundle' => $validated['sat_bundle'],
                'qty_bundle' => $validated['qty_bundle'],
                'hrgjual_bundle' => $validated['hrgjual_bundle'],
                'petugas' => $admin->nama_lengkap,
                'created_at' => now(),
            ]);

            foreach ($validated['obat_id'] as $i => $idBarang) {
                BundleDetail::create([
                    'kd_bundle' => $kdBundle,
                    'id_barang' => $idBarang,
                    'kd_barang' => $validated['obat_kd'][$i],
                    'nm_barang' => $validated['obat_nama'][$i],
                    'qty_barang' => $validated['qty'][$i],
                    'sat_barang' => $validated['sat_barang'][$i],
                    'hrgjual_barang' => $validated['hrgjual'][$i],
                    'subtotal' => $validated['subtotal'][$i],
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('inventory.bundle.index')->with('success', 'Paket produk berhasil ditambahkan.');
    }

    public function edit(Bundle $bundle)
    {
        $bundle->load('detail');

        return view('inventory.bundle.edit', [
            'judul' => 'Inventory',
            'bundle' => $bundle,
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
        ]);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $validated = $this->validateBundle($request, $bundle);

        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($validated, $admin, $bundle) {
            $bundle->update([
                'nm_bundle' => $validated['nm_bundle'],
                'sat_bundle' => $validated['sat_bundle'],
                'qty_bundle' => $validated['qty_bundle'],
                'hrgjual_bundle' => $validated['hrgjual_bundle'],
                'petugas' => $admin->nama_lengkap,
                'update_at' => now(),
            ]);

            foreach ($validated['obat_id'] as $i => $idBarang) {
                $kdBarang = $validated['obat_kd'][$i];
                $existing = BundleDetail::where('kd_bundle', $bundle->kd_bundle)->where('kd_barang', $kdBarang)->first();

                $data = [
                    'qty_barang' => $validated['qty'][$i],
                    'hrgjual_barang' => $validated['hrgjual'][$i],
                    'subtotal' => $validated['subtotal'][$i],
                    'update_at' => now(),
                ];

                if ($existing) {
                    $existing->update($data);
                } else {
                    BundleDetail::create(array_merge($data, [
                        'kd_bundle' => $bundle->kd_bundle,
                        'id_barang' => $idBarang,
                        'kd_barang' => $kdBarang,
                        'nm_barang' => $validated['obat_nama'][$i],
                        'created_at' => now(),
                    ]));
                }
            }
        });

        return redirect()->route('inventory.bundle.index')->with('success', 'Paket produk berhasil diperbarui.');
    }

    public function show(Bundle $bundle)
    {
        $bundle->load('detail');

        return view('inventory.bundle.show', ['judul' => 'Inventory', 'bundle' => $bundle]);
    }

    public function destroy(Bundle $bundle)
    {
        DB::transaction(function () use ($bundle) {
            BundleDetail::where('kd_bundle', $bundle->kd_bundle)->delete();
            $bundle->delete();
        });

        return redirect()->route('inventory.bundle.index')->with('success', 'Paket produk berhasil dihapus.');
    }

    /**
     * Hapus satu baris komponen saat edit (AJAX), port hapusdetail_bundle.php.
     */
    public function detailDestroy(BundleDetail $bundleDetail)
    {
        $bundleDetail->delete();

        return response()->json(['status' => 'success', 'data' => 'Berhasil hapus data']);
    }

    /**
     * Autocomplete item, port autonamabarang.php. Ditambah LIMIT 20 (legacy tidak
     * membatasi sama sekali) -- perbaikan kecil yang sudah jadi konvensi di modul lain
     * (mis. obatSearch Swamedikasi) untuk cari-nama dengan dataset besar.
     */
    public function itemSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $items = Product::where('nm_barang', 'like', '%' . $query . '%')
            ->orderBy('nm_barang')
            ->limit(20)
            ->get(['id_barang', 'kd_barang', 'nm_barang', 'sat_barang', 'hrgjual_barang']);

        return response()->json($items);
    }

    private function validateBundle(Request $request, ?Bundle $bundle): array
    {
        $uniqueRule = 'unique:bundle,nm_bundle';
        if ($bundle) {
            $uniqueRule .= ',' . $bundle->id_bundle . ',id_bundle';
        }

        return $request->validate([
            'nm_bundle' => ['required', 'string', 'max:100', $uniqueRule],
            'sat_bundle' => 'required|string|max:50',
            'qty_bundle' => 'required|integer|min:1',
            'hrgjual_bundle' => 'required|numeric|min:0',
            'obat_id' => 'required|array|min:1',
            'obat_id.*' => 'required|integer',
            'obat_kd' => 'required|array|min:1',
            'obat_kd.*' => 'required',
            'obat_nama' => 'required|array|min:1',
            'obat_nama.*' => 'required|string',
            'qty' => 'required|array|min:1',
            'qty.*' => 'required|integer|min:1',
            'sat_barang' => 'required|array|min:1',
            'sat_barang.*' => 'required|string',
            'hrgjual' => 'required|array|min:1',
            'hrgjual.*' => 'required|numeric|min:0',
            'subtotal' => 'required|array|min:1',
            'subtotal.*' => 'required|numeric|min:0',
        ]);
    }

    private function generateKodeBundle(): string
    {
        $prefix = 'BUND-' . now()->format('ym');
        $last = Bundle::where('kd_bundle', 'like', $prefix . '%')->orderByDesc('kd_bundle')->value('kd_bundle');
        $seq = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
