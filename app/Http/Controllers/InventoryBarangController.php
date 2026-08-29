<?php

namespace App\Http\Controllers;

use App\Models\JenisObat;
use App\Models\Product;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryBarangController extends Controller
{
    /**
     * Modul "Item Barang" (module=barang), mengikuti
     * public/apotekberlian/masuk/modul/mod_barang/barang.php + aksi_barang.php.
     * Tidak diadaptasi: scan barcode kamera (kenyamanan tambahan, bukan CRUD inti),
     * submodul "Zat Aktif/Merk Obat" (module=zataktif, modul terpisah), link
     * "Kartu Stok" (modul terpisah belum diadaptasi), export Excel (bagian dari
     * grup Laporan, digerbang flag berbeda), dan CKEditor (dipakai textarea biasa --
     * data lama yang sudah berisi HTML tetap tampil apa adanya).
     */
    public function index()
    {
        return view('inventory.barang.index', [
            'judul' => 'Inventory',
            'isPemilik' => Auth::guard('admin')->user()->isPemilik(),
            'jenisObatList' => JenisObat::orderBy('jenisobat')->get(),
        ]);
    }

    /**
     * Endpoint AJAX server-side DataTables (pakai Yajra DataTables, sama seperti
     * controller admin lain di app ini), mengikuti data & kolom di barang-serverside.php.
     */
    public function data(Request $request)
    {
        $isPemilik = Auth::guard('admin')->user()->isPemilik();

        $query = Product::query()->select([
            'id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'sat_barang',
            'jenisobat', 'hrgsat_barang', 'hrgjual_barang', 'hrgjual_barang1',
            'hrgjual_barang2', 'zataktif', 'indikasi', 'updated_by',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nm_barang', function ($row) {
                return e($row->nm_barang) . ' <span style="color:#666;">(' . e($row->kd_barang) . ')</span>';
            })
            ->editColumn('jenisobat', function ($row) {
                $value = trim((string) $row->jenisobat);
                $label = $value !== '' ? e($value) : '-';

                return $label . "<div class='mt-1'><button type='button' class='btn btn-xs btn-info btn-edit-jenisobat' data-id='{$row->id_barang}' data-value='" . e($value) . "'>Edit</button></div>";
            })
            ->editColumn('stok_barang', function ($row) {
                $stok = rtrim(rtrim(number_format((float) $row->stok_barang, 2, ',', '.'), '0'), ',');

                return $stok . ' ' . e($row->sat_barang);
            })
            ->editColumn('hrgjual_barang', function ($row) {
                $margin = $row->hrgsat_barang > 0 ? ($row->hrgjual_barang - $row->hrgsat_barang) / $row->hrgsat_barang : 0;
                $color = '#00bfff';
                if ($margin <= 0.2) {
                    $color = '#ff003f';
                } elseif ($margin <= 0.25) {
                    $color = '#f39c12';
                } elseif ($margin <= 0.3) {
                    $color = '#00ff3f';
                }

                return "<table class='mb-0' style='background-color:{$color}; color:#fff;'>
                    <tr><td><b>(R)</b></td><td>" . number_format($row->hrgjual_barang, 0, ',', '.') . '</td></tr>
                    <tr><td><b>(Re)</b></td><td>' . number_format($row->hrgjual_barang1, 0, ',', '.') . '</td></tr>
                    <tr><td><b>(Mp)</b></td><td>' . number_format($row->hrgjual_barang2, 0, ',', '.') . '</td></tr>
                </table>';
            })
            ->editColumn('zataktif', function ($row) {
                // Kolom ini historis berisi HTML (dulu diedit lewat CKEditor), jadi
                // ditampilkan apa adanya (dirender), bukan di-escape jadi teks tag mentah --
                // sama seperti legacy (`echo $r['zataktif']` tanpa htmlspecialchars).
                $display = (string) $row->zataktif;
                if ($row->updated_by) {
                    $display .= " <span style='color:#999; font-size:0.9em;'>(" . e($row->updated_by) . ')</span>';
                }

                return $display . "<div class='mt-1'><button type='button' class='btn btn-xs btn-info btn-edit-zataktif' data-id='{$row->id_barang}' data-value='" . e($row->zataktif) . "'>Edit</button></div>";
            })
            ->editColumn('indikasi', function ($row) {
                return (string) $row->indikasi . "<div class='mt-1'><button type='button' class='btn btn-xs btn-info btn-edit-indikasi' data-id='{$row->id_barang}' data-value='" . e($row->indikasi) . "'>Edit</button></div>";
            })
            ->addColumn('aksi', function ($row) use ($isPemilik) {
                if (!$isPemilik) {
                    return '';
                }

                return '<div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                    <div class="dropdown-menu p-2 shadow" style="min-width:160px;">
                        <a href="' . route('inventory.barang.edit', $row->id_barang) . '" class="btn btn-warning btn-sm w-100 mb-1">Edit</a>
                        <a href="' . route('inventory.barang.show', $row->id_barang) . '" class="btn btn-info btn-sm w-100 mb-1">Detail</a>
                        <a href="' . route('inventory.barang.print-barcode', $row->id_barang) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">Print Barcode</a>
                        <form action="' . route('inventory.barang.destroy', $row->id_barang) . '" method="POST" id="delete-form-barang-' . $row->id_barang . '">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="button" onclick="confirmDelete(\'delete-form-barang-' . $row->id_barang . '\', \'' . e($row->nm_barang) . '\')" class="btn btn-danger btn-sm w-100">Hapus</button>
                        </form>
                    </div>
                </div>';
            })
            ->rawColumns(['nm_barang', 'jenisobat', 'hrgjual_barang', 'zataktif', 'indikasi', 'aksi'])
            ->make(true);
    }

    public function create()
    {
        return view('inventory.barang.create', [
            'judul' => 'Inventory',
            'kodeBarang' => $this->nextKodeBarang(),
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
            'jenisObatList' => JenisObat::orderBy('jenisobat')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kd_barang' => 'nullable|string|max:20',
            'nm_barang' => 'required|string|max:100',
            'sat_barang' => 'required|string|max:15',
            'sat_grosir' => 'required|string|max:15',
            'jenisobat' => 'nullable|string|max:50',
            'konversi' => 'required|integer|min:0',
            'hrgsat_barang' => 'required|numeric|min:0',
            'hrgsat_grosir' => 'required|numeric|min:0',
            'hrgjual_barang' => 'required|numeric|min:0',
            'hrgjual_barang1' => 'required|numeric|min:0',
            'hrgjual_barang2' => 'required|numeric|min:0',
            'zataktif' => 'nullable|string|max:250',
            'indikasi' => 'nullable|string',
            'ket_barang' => 'nullable|string',
        ]);

        $kdBarang = trim((string) $request->input('kd_barang', ''));
        $yearMonth = now()->format('Ym');

        if ($kdBarang === '' || (strlen($kdBarang) === 10 && substr($kdBarang, 0, 6) === $yearMonth)) {
            $kdBarang = $this->nextKodeBarang();
        } elseif (Product::where('kd_barang', $kdBarang)->exists()) {
            return back()->withInput()->with('error', 'Kode Barang sudah ada!');
        }

        if (Product::where('nm_barang', $validated['nm_barang'])->exists()) {
            return back()->withInput()->with('error', 'Nama Barang sudah ada!');
        }

        Product::create([
            'kd_barang' => $kdBarang,
            'category_id' => 0,
            'nm_barang' => $validated['nm_barang'],
            'stok_barang' => 0,
            'stok_buffer' => 0,
            'stok_grosir' => 0,
            'sat_barang' => $validated['sat_barang'],
            'sat_grosir' => $validated['sat_grosir'],
            'jenisobat' => $validated['jenisobat'] ?? '',
            'konversi' => $validated['konversi'],
            'hna' => 0,
            'diskon' => 0,
            'hrgsat_barang' => $validated['hrgsat_barang'],
            'hrgsat_grosir' => $validated['hrgsat_grosir'],
            'hrgjual_barang' => $validated['hrgjual_barang'],
            'hrgjual_barang1' => $validated['hrgjual_barang1'],
            'hrgjual_barang2' => $validated['hrgjual_barang2'],
            'komisi' => 0,
            'zataktif' => $validated['zataktif'] ?? '',
            'indikasi' => $validated['indikasi'] ?? '',
            'ket_barang' => $validated['ket_barang'] ?? '',
            'dosis' => '',
            'waktu' => now(),
            't30' => 0,
            'q30' => 0,
            'petugas' => '',
            'tgl' => now()->toDateString(),
            'updated_by' => Auth::guard('admin')->user()->nama_lengkap,
        ]);

        return redirect()->route('inventory.barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Product $barang)
    {
        return view('inventory.barang.edit', [
            'judul' => 'Inventory',
            'barang' => $barang,
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
            'jenisObatList' => JenisObat::orderBy('jenisobat')->get(),
        ]);
    }

    public function update(Request $request, Product $barang)
    {
        $validated = $request->validate([
            'nm_barang' => 'required|string|max:100',
            'sat_barang' => 'required|string|max:15',
            'sat_grosir' => 'required|string|max:15',
            'jenisobat' => 'nullable|string|max:50',
            'konversi' => 'required|integer|min:0',
            'hrgsat_barang' => 'required|numeric|min:0',
            'hrgsat_grosir' => 'required|numeric|min:0',
            'hrgjual_barang' => 'required|numeric|min:0',
            'hrgjual_barang1' => 'required|numeric|min:0',
            'hrgjual_barang2' => 'required|numeric|min:0',
            'zataktif' => 'nullable|string|max:250',
            'indikasi' => 'nullable|string',
            'ket_barang' => 'nullable|string',
            'dosis' => 'nullable|string|max:100',
        ]);

        if ($validated['sat_barang'] !== $barang->sat_barang) {
            $dipakaiTrbmasuk = DB::table('trbmasuk_detail')
                ->where('kd_barang', $barang->kd_barang)
                ->where('sat_dtrbmasuk', $barang->sat_barang)
                ->exists();
            $dipakaiTrkasir = DB::table('trkasir_detail')
                ->where('kd_barang', $barang->kd_barang)
                ->where('sat_dtrkasir', $barang->sat_barang)
                ->exists();

            if ($dipakaiTrbmasuk || $dipakaiTrkasir) {
                return back()->withInput()->with('error', 'Satuan tidak bisa diubah karena obat ini sudah digunakan dalam transaksi, bila ingin diubah hubungi developer.');
            }
        }

        $barang->update([
            'nm_barang' => $validated['nm_barang'],
            'sat_barang' => $validated['sat_barang'],
            'sat_grosir' => $validated['sat_grosir'],
            'jenisobat' => $validated['jenisobat'] ?? '',
            'konversi' => $validated['konversi'],
            'hrgsat_barang' => $validated['hrgsat_barang'],
            'hrgsat_grosir' => $validated['hrgsat_grosir'],
            'hrgjual_barang' => $validated['hrgjual_barang'],
            'hrgjual_barang1' => $validated['hrgjual_barang1'],
            'hrgjual_barang2' => $validated['hrgjual_barang2'],
            'indikasi' => $validated['indikasi'] ?? '',
            'ket_barang' => $validated['ket_barang'] ?? '',
            'dosis' => $validated['dosis'] ?? '',
            'zataktif' => $validated['zataktif'] ?? '',
            'tgl' => now()->toDateString(),
            'waktu' => now(),
            'updated_by' => Auth::guard('admin')->user()->nama_lengkap,
        ]);

        return redirect()->route('inventory.barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $barang)
    {
        $barang->delete();

        return redirect()->route('inventory.barang.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function show(Product $barang)
    {
        return view('inventory.barang.show', [
            'judul' => 'Inventory',
            'barang' => $barang,
        ]);
    }

    /**
     * Update inline Komposisi dan Indikasi (AJAX), mengikuti act=update_indikasi.
     */
    public function updateIndikasi(Request $request, Product $barang)
    {
        $barang->update(['indikasi' => (string) $request->input('indikasi', '')]);

        return response('OK');
    }

    /**
     * Update inline Zat Aktif (AJAX), mengikuti act=update_zataktif.
     */
    public function updateZataktif(Request $request, Product $barang)
    {
        $barang->update(['zataktif' => (string) $request->input('zataktif', '')]);

        return response('OK');
    }

    /**
     * Update inline Rak Obat (AJAX), mengikuti act=update_jenisobat.
     */
    public function updateJenisobat(Request $request, Product $barang)
    {
        $jenisobat = trim((string) $request->input('jenisobat', ''));

        if ($jenisobat !== '' && !JenisObat::where('jenisobat', $jenisobat)->exists()) {
            return response('Pilihan Rak Obat tidak valid', 400);
        }

        $barang->update(['jenisobat' => $jenisobat]);

        return response('OK');
    }

    /**
     * Cetak label barcode, mengikuti print_barcode.php (browser-print + JsBarcode,
     * ganti generator gambar GD di sisi server).
     */
    public function printBarcode(Request $request, Product $barang)
    {
        $qty = (int) $request->query('qty', 1);
        if ($qty < 1) {
            $qty = 1;
        }
        if ($qty > 500) {
            $qty = 500;
        }

        return view('inventory.barang.print-barcode', [
            'barang' => $barang,
            'qty' => $qty,
        ]);
    }

    /**
     * Nomor kode barang otomatis YYYYMM + 4 digit urut, mengikuti get_kode()
     * di configurasi/fungsi_indotgl.php.
     */
    private function nextKodeBarang(): string
    {
        $yearMonth = now()->format('Ym');

        $last = Product::where('kd_barang', 'like', $yearMonth . '%')
            ->orderByDesc('kd_barang')
            ->value('kd_barang');

        if ($last) {
            $lastSeq = (int) substr((string) $last, -4);

            return $yearMonth . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        }

        return $yearMonth . '0001';
    }
}
