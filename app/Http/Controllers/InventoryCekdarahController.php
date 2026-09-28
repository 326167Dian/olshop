<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Bundle;
use App\Models\BundleDetail;
use App\Models\CekDarah;
use App\Models\Pelanggan;
use App\Models\Product;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryCekdarahController extends Controller
{
    /**
     * Modul "Cek Darah" (module=cekdarah), mengikuti
     * public/apotekberlian/masuk/modul/mod_cekdarah/cekdarah.php.
     */
    public function index()
    {
        $cekdarah = CekDarah::with('pelanggan')->orderByDesc('id_cekdarah')->get();

        return view('inventory.cekdarah.index', [
            'judul' => 'Inventory',
            'cekdarah' => $cekdarah,
        ]);
    }

    public function create(Request $request)
    {
        return view('inventory.cekdarah.create', [
            'judul' => 'Inventory',
            'pelangganList' => Pelanggan::orderBy('nm_pelanggan')->get(),
            'idPelangganTerpilih' => $request->query('id', ''),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'gula' => 'required|string|max:50',
            'gula_2pp' => 'nullable|string|max:50',
            'asamurat' => 'required|string|max:50',
            'kolesterol' => 'required|string|max:50',
            'tensi' => 'required|string|max:50',
        ]);

        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($validated, $admin) {
            $cekdarah = CekDarah::create(array_merge($validated, [
                'petugas' => $admin->nama_lengkap,
                'waktu' => now(),
            ]));

            $this->bebankanKeKasir($cekdarah, $admin);
        });

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Hasil cek darah berhasil disimpan.');
    }

    public function edit(CekDarah $cekdarah)
    {
        $cekdarah->load('pelanggan');

        return view('inventory.cekdarah.edit', [
            'judul' => 'Inventory',
            'cekdarah' => $cekdarah,
        ]);
    }

    public function update(Request $request, CekDarah $cekdarah)
    {
        $validated = $request->validate([
            'gula' => 'required|string|max:50',
            'gula_2pp' => 'nullable|string|max:50',
            'asamurat' => 'required|string|max:50',
            'kolesterol' => 'required|string|max:50',
            'tensi' => 'required|string|max:50',
        ]);

        $cekdarah->update($validated);

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Hasil cek darah berhasil diperbarui.');
    }

    public function destroy(CekDarah $cekdarah)
    {
        $cekdarah->delete();

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Data cek darah berhasil dihapus.');
    }

    /**
     * Cetak hasil cek darah, mengikuti
     * public/apotekberlian/masuk/modul/mod_cekdarah/print.php (di sana pakai FPDF;
     * di sini pakai halaman cetak HTML seperti invoice pesanan, tanpa dependensi baru).
     */
    public function print(CekDarah $cekdarah)
    {
        $cekdarah->load('pelanggan');

        return view('inventory.cekdarah.print', [
            'cekdarah' => $cekdarah,
            'setheader' => Setheader::first(),
        ]);
    }

    /**
     * Bebankan biaya Glukosa/Asam Urat/Kolesterol (Tensi tidak dikenakan tarif) ke draft
     * keranjang kasir (kdtk) milik petugas yang melakukan cek ini, memakai harga jual
     * barang yang berlaku saat ini (barang "Cek Gula Darah"/"Cek Asam Urat"/
     * "Cek Kolesterol"). Kalau ketiganya diperiksa dan kombinasinya persis cocok dengan
     * satu paket bundling yang terdaftar (lihat cariBundleCekDarahLengkap()), paket itu
     * yang dipakai (harga bundling), bukan tiga baris satuan.
     *
     * Transaksi SENGAJA tidak difinalisasi (trkasir belum dibuat, stt_kdtk tetap 'ON')
     * -- pelanggan mungkin masih mau belanja lain, biarkan petugas jaga yang menekan
     * "Simpan Transaksi" (lihat InventoryTrkasirController::store()) belakangan.
     *
     * Barang yang tidak ditemukan (mis. belum pernah dibuat di Data Master) dilewati
     * begitu saja -- hasil cek darah tetap tersimpan walau pembebanan kasirnya sebagian
     * atau seluruhnya tidak bisa dilakukan.
     */
    private function bebankanKeKasir(CekDarah $cekdarah, Admin $admin): void
    {
        $produk = [
            'gula' => trim((string) $cekdarah->gula) !== '' ? Product::where('nm_barang', 'Cek Gula Darah')->first() : null,
            'asamurat' => trim((string) $cekdarah->asamurat) !== '' ? Product::where('nm_barang', 'Cek Asam Urat')->first() : null,
            'kolesterol' => trim((string) $cekdarah->kolesterol) !== '' ? Product::where('nm_barang', 'Cek Kolesterol')->first() : null,
        ];
        $produk = array_filter($produk);

        if (empty($produk)) {
            return;
        }

        $trkasirController = app(InventoryTrkasirController::class);
        $kdTrkasir = $trkasirController->resolveKdtk(
            $admin->id_admin,
            $cekdarah->id_pelanggan,
            $cekdarah->pelanggan->nm_pelanggan ?? null
        );

        $bundle = count($produk) === 3 ? $this->cariBundleCekDarahLengkap($produk) : null;

        if ($bundle) {
            $trkasirController->tambahBundle([
                'kd_trkasir' => $kdTrkasir,
                'kd_barang' => $bundle->kd_bundle,
                'qty_dtrkasir' => 1,
                'tipe' => 1,
            ], $admin, $admin, 0);

            return;
        }

        foreach ($produk as $barang) {
            $trkasirController->tambahBarang([
                'kd_trkasir' => $kdTrkasir,
                'id_barang' => $barang->id_barang,
                'kd_barang' => $barang->kd_barang,
                'nmbrg_dtrkasir' => $barang->nm_barang,
                'qty_dtrkasir' => 1,
                'sat_dtrkasir' => $barang->sat_barang,
                'hrgjual_dtrkasir' => $barang->hrgjual_barang,
                'tipe' => 1,
            ], $admin, $admin, 0, 'TIDAK');
        }
    }

    /**
     * Cari satu bundle yang komponennya PERSIS sama dengan $produk (tidak kurang, tidak
     * lebih) -- kalau ada beberapa yang cocok, ambil yang id_bundle-nya paling kecil.
     *
     * @param  array<string, Product>  $produk
     */
    private function cariBundleCekDarahLengkap(array $produk): ?Bundle
    {
        $idDiperlukan = collect($produk)->pluck('id_barang')->sort()->values()->all();

        return Bundle::orderBy('id_bundle')->get()->first(function ($bundle) use ($idDiperlukan) {
            $komponen = BundleDetail::where('kd_bundle', $bundle->kd_bundle)->pluck('id_barang')->sort()->values()->all();

            return $komponen === $idDiperlukan;
        });
    }
}
