<?php

namespace App\Http\Controllers;

use App\Models\JenisObat;
use App\Models\Product;
use App\Models\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryStokopnameController extends Controller
{
    /**
     * Modul "Stok Opname Bulanan" (grup Inventory, flag admin `stokopname`), mengikuti
     * `mod_lapstok/stokopname.php` (form + act=tampil) + `tabel_stokopname.php` (grid
     * input, AJAX) + `tabel_stokopname_rekap.php` (rekap, AJAX) + `simpan_stokopname.php`
     * (simpan satu baris, AJAX) + `hapus_stokopname.php` (hapus satu baris, AJAX) +
     * `stokopname_excel.php` (export). BEDA dari modul "Stok Kritis" dan "Nilai Stok &
     * Traffic Barang" -- ini benar-benar mencatat hasil hitung fisik manual per rak
     * obat, bukan laporan baca-saja atas data transaksi.
     *
     * Tabel `stok_opname` DIBAGI dengan modul sibling "Stok Opname Harian" (flag
     * `soharian`, belum diporting) -- kolom `shift` ada di tabel tapi TIDAK PERNAH
     * diisi oleh `simpan_stokopname.php` versi Bulanan ini (hanya versi Harian yang
     * mengisinya). Dikonfirmasi dari 428 baris nyata yang sudah ada: baris-baris itu
     * punya `shift` terisi dan `tgl_current` berupa datetime valid -- pola yang TIDAK
     * mungkin dihasilkan oleh `simpan_stokopname.php` (lihat catatan bug di bawah),
     * jadi baris-baris tersebut pasti berasal dari modul Harian, bukan Bulanan.
     * Kolom `shift` diisi `0` di sini sebagai nilai netral (kolom tidak relevan untuk
     * konsep Bulanan), bukan dikosongkan (kolom NOT NULL tanpa default).
     *
     * **Bug legacy diperbaiki, tidak direplikasi:** `simpan_stokopname.php` menulis
     * `tgl_current` (kolom asli bertipe `datetime`) dengan `date('ymdHis')` -- string
     * seperti "260906013634", BUKAN format datetime yang valid -- dan `exp_date`
     * kosong ditulis sebagai string `'0000:00:00'` (titik dua, bukan strip).
     * Diperbaiki: `tgl_current` = `now()` (datetime asli). Untuk `exp_date` kosong,
     * percobaan awal memakai `'0000-00-00'` (format valid & terbukti tersimpan di
     * baris-baris lama) ternyata TETAP GAGAL lewat koneksi Laravel -- sql_mode
     * koneksi ini mengaktifkan `NO_ZERO_DATE` (baris lama pasti masuk lewat sesi
     * PDO legacy yang sql_mode-nya berbeda/lebih longgar). Diperbaiki ke sentinel
     * `'1970-01-01'` (tanggal valid di sql_mode manapun), pola yang sama dengan
     * `trbmasuk.tgl_lunas` untuk kasus "belum lunas" di `InventoryTrbmasukController`.
     * `StokOpname::$casts` juga SENGAJA tidak meng-cast `exp_date` ke `'date'` --
     * Carbon mem-parse `'0000-00-00'` (nilai lama yang masih ada di DB) jadi tanggal
     * tak masuk akal ("-0001-11-30"), bukan menampilkannya apa adanya.
     *
     * **Export Excel diporting dengan konvensi HTML+CSS (trik `.xls` browser) yang
     * konsisten dengan SELURUH export Excel lain di port ini, bukan PhpSpreadsheet**
     * (paket yang dipakai `stokopname_excel.php` legacy) -- legacy sendiri punya versi
     * dead-code HTML biasa untuk fitur yang sama persis (`stokopname_excel2.php`,
     * dikonfirmasi tidak direferensikan di mana pun), jadi pola ini BUKAN sesuatu
     * yang asing bagi aplikasi lama, hanya bukan yang aktif dipakai. Kolom
     * Stok Fisik/Exp Date/Waktu/Acc Manager tetap kosong (lembar kerja manual),
     * `tgl_awal`/`tgl_akhir` yang legacy tangkap dari form TIDAK PERNAH benar-benar
     * dipakai memfilter data (`query`-nya cuma `WHERE jenisobat=?`) -- tidak
     * direplikasi sebagai parameter fungsional, cuma teks tanggal cetak.
     */
    public function index()
    {
        $jumlahStokAda = Product::where('stok_barang', '>', 0)->count();

        $jenisobatAsingCounts = DB::table('barang as b')
            ->leftJoin('jenis_obat as j', 'j.jenisobat', '=', 'b.jenisobat')
            ->whereNull('j.jenisobat')
            ->selectRaw('b.jenisobat, COUNT(*) as jumlah')
            ->groupBy('b.jenisobat')
            ->orderBy('b.jenisobat')
            ->get();

        $jenisobatAsingItems = Product::query()
            ->whereIn('jenisobat', $jenisobatAsingCounts->pluck('jenisobat'))
            ->orderBy('nm_barang')
            ->get(['kd_barang', 'nm_barang', 'jenisobat'])
            ->groupBy('jenisobat');

        $jenisobatAsing = $jenisobatAsingCounts->map(function ($row) use ($jenisobatAsingItems) {
            $row->items = $jenisobatAsingItems->get($row->jenisobat, collect());

            return $row;
        });

        $jenisobatTakTerpakai = JenisObat::query()
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('barang')->whereColumn('barang.jenisobat', 'jenis_obat.jenisobat');
            })
            ->orderBy('jenisobat')
            ->get(['jenisobat', 'ket']);

        $daftarRak = Product::query()->select('jenisobat')->distinct()->orderBy('jenisobat')->pluck('jenisobat');

        return view('inventory.stokopname.index', [
            'judul' => 'Inventory',
            'jumlahStokAda' => $jumlahStokAda,
            'jenisobatAsing' => $jenisobatAsing,
            'jenisobatTakTerpakai' => $jenisobatTakTerpakai,
            'daftarRak' => $daftarRak,
        ]);
    }

    public function tampil(Request $request)
    {
        $validated = $request->validate([
            'jenisobat' => 'required|string',
            'tgl' => 'required|date',
        ]);

        return view('inventory.stokopname.tampil', [
            'judul' => 'Inventory',
            'jenisobat' => $validated['jenisobat'],
            'tgl' => $validated['tgl'],
        ]);
    }

    /** Ports tabel_stokopname.php -- grid item yang BELUM dicatat untuk rak+tanggal ini. */
    public function grid(Request $request)
    {
        $validated = $request->validate([
            'jenisobat' => 'required|string',
            'tgl' => 'required|date',
        ]);

        $rows = DB::table('barang as a')
            ->leftJoinSub(
                DB::table('trbmasuk_detail')->selectRaw('kd_barang, SUM(qty_dtrbmasuk) as totalbeli')->groupBy('kd_barang'),
                'beli',
                'beli.kd_barang',
                '=',
                'a.kd_barang'
            )
            ->leftJoinSub(
                DB::table('trkasir_detail')->selectRaw('kd_barang, SUM(qty_dtrkasir) as totaljual')->groupBy('kd_barang'),
                'jual',
                'jual.kd_barang',
                '=',
                'a.kd_barang'
            )
            ->leftJoin('stok_opname as so', function ($join) use ($validated) {
                $join->on('so.kd_barang', '=', 'a.kd_barang')->where('so.tgl_stokopname', '=', $validated['tgl']);
            })
            ->where('a.jenisobat', $validated['jenisobat'])
            ->where(function ($q) {
                $q->whereNotNull('beli.kd_barang')->orWhereNotNull('jual.kd_barang');
            })
            ->whereNull('so.id_stok_opname')
            ->orderBy('a.nm_barang')
            ->selectRaw('
                a.id_barang, a.kd_barang, a.nm_barang, a.sat_barang, a.hrgsat_barang,
                (COALESCE(beli.totalbeli, 0) - COALESCE(jual.totaljual, 0)) as selisih
            ')
            ->get();

        $admin = Auth::guard('admin')->user();

        return view('inventory.stokopname.partials.grid', [
            'rows' => $rows,
            'isPemilik' => $admin->isPemilik(),
            'tgl' => $validated['tgl'],
        ]);
    }

    /** Ports tabel_stokopname_rekap.php -- item yang SUDAH dicatat untuk rak+tanggal ini. */
    public function rekap(Request $request)
    {
        $validated = $request->validate([
            'jenisobat' => 'required|string',
            'tgl' => 'required|date',
        ]);

        $rows = StokOpname::query()
            ->join('barang', 'barang.id_barang', '=', 'stok_opname.id_barang')
            ->where('barang.jenisobat', $validated['jenisobat'])
            ->where('stok_opname.tgl_stokopname', $validated['tgl'])
            ->orderBy('barang.nm_barang')
            ->select('stok_opname.*', 'barang.nm_barang', 'barang.sat_barang')
            ->get();

        $admin = Auth::guard('admin')->user();

        return view('inventory.stokopname.partials.rekap', [
            'rows' => $rows,
            'isPemilik' => $admin->isPemilik(),
        ]);
    }

    /** Ports simpan_stokopname.php. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|integer|exists:barang,id_barang',
            'kd_barang' => 'required|string',
            'hrgsat_barang' => 'required|numeric',
            'stok_fisik' => 'required|numeric|min:0',
            'exp_date' => 'nullable|date',
            'jml' => 'nullable|integer|min:0',
            'tgl_awal' => 'required|date',
        ]);

        $totalBeli = (float) DB::table('trbmasuk_detail')->where('id_barang', $validated['id_barang'])->sum('qty_dtrbmasuk');
        $totalJual = (float) DB::table('trkasir_detail')->where('id_barang', $validated['id_barang'])->sum('qty_dtrkasir');
        $stokSistem = $totalBeli - $totalJual;
        $selisih = (float) $validated['stok_fisik'] - $stokSistem;

        $admin = Auth::guard('admin')->user();

        StokOpname::create([
            'id_barang' => $validated['id_barang'],
            'kd_barang' => $validated['kd_barang'],
            'stok_sistem' => $stokSistem,
            'stok_fisik' => $validated['stok_fisik'],
            'exp_date' => $validated['exp_date'] ?? '1970-01-01',
            'jml' => $validated['jml'] ?? 0,
            'selisih' => $selisih,
            'hrgsat_barang' => $validated['hrgsat_barang'],
            'ttl_hrgbrg' => $selisih * (float) $validated['hrgsat_barang'],
            'tgl_current' => now(),
            'tgl_stokopname' => $validated['tgl_awal'],
            'shift' => 0,
            'id_admin' => $admin->id_admin,
        ]);

        return response()->json(['status' => true]);
    }

    /**
     * Ports hapus_stokopname.php -- server-side digerbang pemilik-only, bukan cuma
     * disembunyikan di tombol seperti legacy (perbaikan konsisten dengan pola yang
     * sudah dipakai di seluruh port ini untuk aksi hapus sensitif).
     */
    public function destroy(StokOpname $stokOpname)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $stokOpname->delete();

        return response()->json(['status' => true]);
    }

    /** Ports stokopname_excel.php -- lihat catatan kelas soal konvensi HTML/CSS. */
    public function excel(Request $request)
    {
        $validated = $request->validate(['jenisobat' => 'required|string']);

        $rows = Product::where('jenisobat', $validated['jenisobat'])
            ->orderBy('nm_barang')
            ->get(['kd_barang', 'nm_barang', 'sat_barang', 'stok_barang', 'hrgjual_barang']);

        return response()->view('inventory.stokopname.excel', [
            'rows' => $rows,
            'jenisobat' => $validated['jenisobat'],
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="stok_opname_bulanan.xls"',
        ]);
    }
}
