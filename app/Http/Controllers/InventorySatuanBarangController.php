<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Satuan;
use App\Models\SatuanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventorySatuanBarangController extends Controller
{
    /**
     * Nama sat_barang (lowercase) yang dianggap sudah satuan paling kecil (tidak
     * bisa dipecah lagi) -- hanya item dengan sat_barang di daftar ini yang boleh
     * dimigrasikan otomatis ke satuan_barang. Nilai di luar daftar ini (strip,
     * blister, botol, box, dst) BISA jadi satuan dasarnya bukan ini -- tidak ada
     * cara mengetahui angka konversi yang benar dari data yang ada, jadi item
     * tersebut ditandai "perlu dilengkapi manual", bukan ditebak.
     */
    private const ATOMIC_UNIT_MAP = [
        'tablet' => 'Tablet',
        'kapsul' => 'Kapsul',
        'kaplet' => 'Kaplet',
        'pcs' => 'Pcs',
        'ampul' => 'Ampul',
        'amp' => 'Ampul',
        'vial' => 'Vial',
        'suppositoria' => 'Suppositoria',
    ];

    /**
     * Kelola satuan bertingkat satu item.
     */
    public function index(Product $barang)
    {
        $tingkat = $barang->satuanBertingkat()->with('satuan')->get();

        return view('inventory.barang.satuan', [
            'judul' => 'Inventory',
            'barang' => $barang,
            'tingkat' => $tingkat,
            'kumulatif' => SatuanBarang::kumulatifMap($tingkat),
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
            'urutanBerikutnya' => $tingkat->max('urutan') ? $tingkat->max('urutan') + 1 : 1,
        ]);
    }

    public function store(Request $request, Product $barang)
    {
        $urutanBerikutnya = (int) ($barang->satuanBertingkat()->max('urutan') ?? 0) + 1;

        $validated = $request->validate([
            'id_satuan' => 'required|integer|exists:satuan,id_satuan',
            'rasio_ke_bawah' => $urutanBerikutnya === 1 ? 'nullable|numeric' : 'required|numeric|min:0.01',
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
        ]);

        SatuanBarang::create([
            'kd_barang' => $barang->kd_barang,
            'urutan' => $urutanBerikutnya,
            'id_satuan' => $validated['id_satuan'],
            'rasio_ke_bawah' => $urutanBerikutnya === 1 ? 1 : $validated['rasio_ke_bawah'],
            'harga_jual' => $validated['harga_jual'],
            'harga_beli' => $validated['harga_beli'] ?? 0,
        ]);

        return redirect()->route('inventory.barang.satuan.index', $barang->id_barang)
            ->with('success', 'Tingkat satuan berhasil ditambahkan.');
    }

    public function update(Request $request, Product $barang, SatuanBarang $satuanBarang)
    {
        abort_unless($satuanBarang->kd_barang === $barang->kd_barang, 404);

        $validated = $request->validate([
            'rasio_ke_bawah' => $satuanBarang->urutan === 1 ? 'nullable|numeric' : 'required|numeric|min:0.01',
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $satuanBarang->update([
            'rasio_ke_bawah' => $satuanBarang->urutan === 1 ? 1 : $validated['rasio_ke_bawah'],
            'harga_jual' => $validated['harga_jual'],
            'harga_beli' => $validated['harga_beli'] ?? 0,
            'status' => $validated['status'],
        ]);

        return redirect()->route('inventory.barang.satuan.index', $barang->id_barang)
            ->with('success', 'Tingkat satuan berhasil diperbarui.');
    }

    public function destroy(Product $barang, SatuanBarang $satuanBarang)
    {
        abort_unless($satuanBarang->kd_barang === $barang->kd_barang, 404);

        $urutanTertinggi = $barang->satuanBertingkat()->max('urutan');

        if ($satuanBarang->urutan !== $urutanTertinggi) {
            return back()->with('error', 'Hanya tingkat satuan paling atas yang bisa dihapus, supaya urutan tidak bolong.');
        }

        $satuanBarang->delete();

        return redirect()->route('inventory.barang.satuan.index', $barang->id_barang)
            ->with('success', 'Tingkat satuan berhasil dihapus.');
    }

    /**
     * Daftar item yang belum punya satuan_barang sama sekali -- pekerjaan rumah
     * admin, dipisah mana yang bisa dimigrasi otomatis vs harus dilengkapi manual.
     */
    public function kelengkapan()
    {
        $atomicNames = array_keys(self::ATOMIC_UNIT_MAP);

        $total = Product::whereDoesntHave('satuanBertingkat')->count();
        $siapOtomatis = Product::whereDoesntHave('satuanBertingkat')
            ->whereIn(DB::raw('LOWER(sat_barang)'), $atomicNames)
            ->count();

        return view('inventory.barang.satuan-kelengkapan', [
            'judul' => 'Inventory',
            'total' => $total,
            'siapOtomatis' => $siapOtomatis,
            'perluManual' => $total - $siapOtomatis,
        ]);
    }

    public function kelengkapanData(Request $request)
    {
        $atomicNames = array_keys(self::ATOMIC_UNIT_MAP);

        $query = Product::whereDoesntHave('satuanBertingkat')
            ->select(['id_barang', 'kd_barang', 'nm_barang', 'sat_barang', 'sat_grosir', 'konversi']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kelengkapan', function (Product $row) use ($atomicNames) {
                return in_array(strtolower($row->sat_barang), $atomicNames, true)
                    ? '<span class="badge bg-success">Siap otomatis</span>'
                    : '<span class="badge bg-warning text-dark">Perlu manual</span>';
            })
            ->addColumn('aksi', function (Product $row) {
                return '<a href="' . route('inventory.barang.satuan.index', $row->id_barang) . '" class="btn btn-primary btn-sm">Kelola Satuan</a>';
            })
            ->rawColumns(['kelengkapan', 'aksi'])
            ->make(true);
    }

    /**
     * Migrasi otomatis: item dengan sat_barang di ATOMIC_UNIT_MAP dan belum
     * punya satuan_barang sama sekali -> dibuatkan tingkat 1 (satuan dasar)
     * dari data yang sudah ada (sat_barang, hrgjual_barang, hrgsat_barang).
     * Aman dijalankan berkali-kali -- item yang sudah punya baris dilewati.
     */
    public function migrateOtomatis()
    {
        $atomicNames = array_keys(self::ATOMIC_UNIT_MAP);

        $items = Product::whereDoesntHave('satuanBertingkat')
            ->whereIn(DB::raw('LOWER(sat_barang)'), $atomicNames)
            ->get(['kd_barang', 'sat_barang', 'hrgjual_barang', 'hrgsat_barang']);

        $dibuat = 0;

        DB::transaction(function () use ($items, $atomicNames, &$dibuat) {
            foreach ($items as $item) {
                $namaKanonik = self::ATOMIC_UNIT_MAP[strtolower($item->sat_barang)];

                $satuan = Satuan::whereRaw('LOWER(nm_satuan) = ?', [strtolower($namaKanonik)])->first()
                    ?? Satuan::create(['nm_satuan' => $namaKanonik, 'deskripsi' => '']);

                SatuanBarang::create([
                    'kd_barang' => $item->kd_barang,
                    'urutan' => 1,
                    'id_satuan' => $satuan->id_satuan,
                    'rasio_ke_bawah' => 1,
                    'harga_jual' => $item->hrgjual_barang ?? 0,
                    'harga_beli' => $item->hrgsat_barang ?? 0,
                ]);

                $dibuat++;
            }
        });

        return back()->with('success', "Migrasi otomatis selesai: {$dibuat} item dilengkapi satuan dasarnya.");
    }
}
