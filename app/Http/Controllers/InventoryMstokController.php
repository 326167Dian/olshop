<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TrbmasukDetail;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryMstokController extends Controller
{
    /**
     * Modul "Nilai Stok & Traffic Barang" (grup Inventory, flag admin `mstok`),
     * mengikuti `mod_lapstok/lapstok.php` (5 tab: default/GLOBAL, laku, lancar,
     * slow, macet, plus act=edit/act=order untuk drill-down Riwayat) + 5 file
     * serverside terpisah (barang-serverside.php, brglaku/brglancar/brgslow/
     * brgmacet-serverside.php) + `SO_barangmacet_excel.php`.
     *
     * **Optimasi utama diminta user 2026-09-06: eliminasi loop N+1 legacy.**
     * Setiap file serverside legacy menghitung T30/T60/Q30/OM30/L30 dengan pola
     * yang mahal:
     * - GLOBAL: 4 correlated subquery BERSARANG per baris (t30/t60/gr masing-masing
     *   mengulang subquery yang sama) -- di-porting jadi SATU query dengan 2
     *   derived-table LEFT JOIN (t30sub gabung hitung t30+q30 sekaligus karena
     *   rentang tanggalnya sama, t60sub terpisah), semua kolom turunan (t30/t60/
     *   gr/q30/nilai_barang) dihitung sekali di SQL, bukan per baris di PHP.
     * - LAKU/LANCAR/SLOW: legacy menjalankan SATU query GROUP BY tanpa LIMIT untuk
     *   menghitung total footer (Total Stok/Omset/Laba), lalu untuk SETIAP baris
     *   hasil (bisa ratusan item) menjalankan query TERPISAH ke tabel `barang`
     *   (loop query-per-item) -- diulang LAGI untuk baris halaman yang ditampilkan.
     *   Di-porting jadi: satu query GROUP BY+JOIN `barang` langsung (tidak perlu
     *   query kedua per baris karena kolom barang sudah ikut di-JOIN), dipakai baik
     *   untuk listing berpaginasi maupun untuk agregat total footer (SUM di atas
     *   subquery yang sama) -- total selalu 2 query per page-load, TIDAK PERNAH
     *   bertambah seiring jumlah item yang cocok (legacy: 2 + 2N query).
     * - MACET: kriteria NOT EXISTS berarti t30/q30 SELALU 0 by definition -- legacy
     *   tetap menjalankan query per-baris yang pasti hasilnya 0, dihapus sepenuhnya
     *   (di-hardcode 0 di SQL, bukan query sia-sia).
     *
     * **Inkonsistensi legacy direplikasi apa adanya (beda default tanggal antar tab,
     * BUKAN keseragaman yang sengaja dibuat, ditemukan saat membaca source):**
     * - GLOBAL: selalu [hari ini - 30, hari ini], tidak bisa diubah user (tidak ada
     *   form filter tanggal sama sekali di tab ini, sesuai legacy).
     * - LAKU/LANCAR/SLOW: filter tanggal kosong pertama kali dibuka -> default KEDUA
     *   tanggal (awal & akhir) ke HARI INI (bukan rentang 30 hari!) -- kemungkinan
     *   kekurangan di aplikasi asli (tab langsung nyaris kosong sebelum user pilih
     *   rentang tanggal & klik SUBMIT), tapi diikuti apa adanya, bukan diubah.
     * - MACET: filter tanggal kosong -> default ke rentang 30 hari terakhir (BEDA
     *   dari 3 tab di atas). Tiga cara default yang berbeda untuk 4 tab yang
     *   secara UI terlihat seragam -- bukan salah porting, memang begitu di sumber.
     *
     * **Fitur "PROSES UPDATE DATABASE" (act=kritis30)** menyegarkan kolom cache
     * `barang.t30/t60/gr/q30` -- TAPI dengan RUMUS YANG BERBEDA dari kolom yang
     * ditampilkan live di listing (lihat method `recompute()`), inkonsistensi lain
     * yang sudah ada di legacy sendiri, direplikasi bukan disatukan. Kolom `t60`
     * dan `gr` belum ada sama sekali di tabel `barang` sebelum port ini (hanya t30/
     * q30 yang sudah ada) -- ditambahkan lewat migration khusus untuk task ini.
     * Sebelumnya menjalankan 1 query awal + (2 query x jumlah item yang PERNAH
     * diterima via trbmasuk_detail) dalam satu loop PHP -- di-porting jadi SATU
     * statement UPDATE...JOIN, independen dari jumlah item.
     *
     * **Sengaja TIDAK diporting** (fitur terkait tapi milik modul lain yang belum
     * dibangun): 4 tombol "STOK OPNAME" (laku/lancar/slow/macet, link ke
     * `SO_barang{tab}.php` -- lembar kerja stok fisik FPDF kosong untuk dicetak
     * dan diisi manual, bagian dari modul Stok Opname/flag `stokopname`/`soharian`
     * yang belum ada di port ini, bukan bagian dari `mstok`).
     */
    private const HAVING_LAKU = ['min' => 10, 'max' => null];
    private const HAVING_LANCAR = ['min' => 5, 'max' => 11];
    private const HAVING_SLOW = ['min' => 0, 'max' => 6];

    public function index()
    {
        return view('inventory.mstok.index', ['judul' => 'Inventory']);
    }

    public function dataGlobal(Request $request)
    {
        $finish = now()->toDateString();
        $start = now()->copy()->subDays(30)->toDateString();
        $tgl60 = now()->copy()->subDays(60)->toDateString();

        $t30Sub = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->whereBetween('tk.tgl_trkasir', [$start, $finish])
            ->groupBy('td.kd_barang')
            ->selectRaw('td.kd_barang, COUNT(*) as cnt, SUM(td.qty_dtrkasir) as qty');

        $t60Sub = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->whereBetween('tk.tgl_trkasir', [$tgl60, $finish])
            ->groupBy('td.kd_barang')
            ->selectRaw('td.kd_barang, COUNT(*) as cnt');

        $query = DB::table('barang as a')
            ->leftJoinSub($t30Sub, 't30sub', 'a.kd_barang', '=', 't30sub.kd_barang')
            ->leftJoinSub($t60Sub, 't60sub', 'a.kd_barang', '=', 't60sub.kd_barang')
            ->selectRaw('
                a.kd_barang, a.nm_barang, a.stok_barang,
                a.sat_barang as satuan, a.hrgsat_barang as harga_beli,
                (a.hrgsat_barang * a.stok_barang) as nilai_barang,
                COALESCE(t30sub.cnt, 0) as t30,
                (COALESCE(t60sub.cnt, 0) - COALESCE(t30sub.cnt, 0)) as t60,
                CASE WHEN (COALESCE(t60sub.cnt,0) - COALESCE(t30sub.cnt,0)) = 0 THEN 0
                     ELSE ROUND((COALESCE(t30sub.cnt,0) / (COALESCE(t60sub.cnt,0) - COALESCE(t30sub.cnt,0))) * 100) - 100
                END as gr,
                COALESCE(t30sub.qty, 0) as q30
            ');

        $totalStok = (float) Product::query()->selectRaw('SUM(hrgsat_barang * stok_barang) as total')->value('total');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kartu_stok', fn ($row) => $this->kartuStokLinks($row->kd_barang))
            ->rawColumns(['kartu_stok'])
            ->with('totalStok', $totalStok)
            ->make(true);
    }

    public function laku(Request $request)
    {
        return view('inventory.mstok.bucket', $this->bucketViewData($request, 'laku'));
    }

    public function lancar(Request $request)
    {
        return view('inventory.mstok.bucket', $this->bucketViewData($request, 'lancar'));
    }

    public function slow(Request $request)
    {
        return view('inventory.mstok.bucket', $this->bucketViewData($request, 'slow'));
    }

    private function bucketViewData(Request $request, string $tab): array
    {
        // Sesuai legacy: default KEDUA tanggal ke hari ini jika belum difilter
        // (lihat catatan kelas -- bukan rentang 30 hari seperti tab Macet).
        $today = now()->toDateString();
        $start = $request->query('start') ?: $today;
        $finish = $request->query('finish') ?: $today;

        return [
            'judul' => 'Inventory',
            'tab' => $tab,
            'start' => $start,
            'finish' => $finish,
        ];
    }

    public function dataLaku(Request $request)
    {
        return $this->bucketData($request, self::HAVING_LAKU);
    }

    public function dataLancar(Request $request)
    {
        return $this->bucketData($request, self::HAVING_LANCAR);
    }

    public function dataSlow(Request $request)
    {
        return $this->bucketData($request, self::HAVING_SLOW);
    }

    private function bucketData(Request $request, array $having)
    {
        $start = $request->query('start') ?: now()->toDateString();
        $finish = $request->query('finish') ?: now()->toDateString();

        $query = $this->bucketBaseQuery($start, $finish, $having);

        $totals = $this->bucketTotals($start, $finish, $having);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kartu_stok', fn ($row) => $this->kartuStokLinks($row->kd_barang))
            ->rawColumns(['kartu_stok'])
            ->with('totalOm30', $totals['totalOm30'])
            ->with('totalL30', $totals['totalL30'])
            ->with('totalStok', $totals['totalStok'])
            ->make(true);
    }

    private function bucketBaseQuery(string $start, string $finish, array $having)
    {
        $query = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->join('barang as b', 'b.kd_barang', '=', 'td.kd_barang')
            ->whereBetween('tk.tgl_trkasir', [$start, $finish])
            ->groupBy('td.kd_barang', 'td.nmbrg_dtrkasir', 'b.stok_barang', 'b.stok_buffer', 'b.sat_barang', 'b.hrgsat_barang')
            ->selectRaw('
                td.kd_barang, td.nmbrg_dtrkasir as nm_barang,
                b.stok_barang, b.stok_buffer, b.sat_barang as satuan, b.hrgsat_barang as harga_beli,
                (b.hrgsat_barang * b.stok_barang) as nilai_barang,
                COUNT(*) as t30,
                SUM(td.qty_dtrkasir) as q30,
                SUM(td.hrgttl_dtrkasir) as om30,
                ROUND(SUM(td.hrgttl_dtrkasir) - (SUM(td.qty_dtrkasir) * b.hrgsat_barang)) as l30
            ');

        $this->applyHaving($query, $having);

        return $query;
    }

    private function bucketTotals(string $start, string $finish, array $having): array
    {
        $sub = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->join('barang as b', 'b.kd_barang', '=', 'td.kd_barang')
            ->whereBetween('tk.tgl_trkasir', [$start, $finish])
            ->groupBy('td.kd_barang', 'b.hrgsat_barang', 'b.stok_barang')
            ->selectRaw('
                SUM(td.hrgttl_dtrkasir) as om30,
                SUM(td.qty_dtrkasir) as q30,
                b.hrgsat_barang, b.stok_barang
            ');

        $this->applyHaving($sub, $having);

        $totals = DB::query()->fromSub($sub, 'sub')->selectRaw('
            COALESCE(SUM(sub.om30), 0) as total_om30,
            COALESCE(SUM(sub.om30 - (sub.q30 * sub.hrgsat_barang)), 0) as total_l30,
            COALESCE(SUM(sub.hrgsat_barang * sub.stok_barang), 0) as total_stok
        ')->first();

        return [
            'totalOm30' => (float) $totals->total_om30,
            'totalL30' => (float) $totals->total_l30,
            'totalStok' => (float) $totals->total_stok,
        ];
    }

    private function applyHaving($query, array $having): void
    {
        if ($having['max'] !== null) {
            $query->havingRaw('COUNT(*) > ? AND COUNT(*) < ?', [$having['min'], $having['max']]);
        } else {
            $query->havingRaw('COUNT(*) > ?', [$having['min']]);
        }
    }

    public function macet(Request $request)
    {
        $finish = $request->query('finish') ?: now()->toDateString();
        $start = $request->query('start') ?: now()->copy()->subDays(30)->toDateString();

        return view('inventory.mstok.macet', [
            'judul' => 'Inventory',
            'start' => $start,
            'finish' => $finish,
        ]);
    }

    public function dataMacet(Request $request)
    {
        $finish = $request->query('finish') ?: now()->toDateString();
        $start = $request->query('start') ?: now()->copy()->subDays(30)->toDateString();

        $query = DB::table('barang as b')
            ->whereNotExists(function ($q) use ($start, $finish) {
                $q->select(DB::raw(1))
                    ->from('trkasir_detail as td')
                    ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
                    ->whereColumn('td.kd_barang', 'b.kd_barang')
                    ->whereBetween('tk.tgl_trkasir', [$start, $finish]);
            })
            ->selectRaw('
                b.kd_barang, b.nm_barang, b.stok_barang, b.stok_buffer,
                b.sat_barang as satuan, b.hrgsat_barang as harga_beli,
                (b.hrgsat_barang * b.stok_barang) as nilai_barang,
                0 as t30, 0 as q30
            ');

        $totalStok = (float) DB::table('barang as b')
            ->whereNotExists(function ($q) use ($start, $finish) {
                $q->select(DB::raw(1))
                    ->from('trkasir_detail as td')
                    ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
                    ->whereColumn('td.kd_barang', 'b.kd_barang')
                    ->whereBetween('tk.tgl_trkasir', [$start, $finish]);
            })
            ->selectRaw('SUM(b.hrgsat_barang * b.stok_barang) as total')
            ->value('total');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('kartu_stok', fn ($row) => $this->kartuStokLinks($row->kd_barang))
            ->rawColumns(['kartu_stok'])
            ->with('totalStok', $totalStok)
            ->make(true);
    }

    public function macetExcel(Request $request)
    {
        $finish = $request->query('finish') ?: now()->toDateString();
        $start = $request->query('start') ?: now()->copy()->subDays(30)->toDateString();

        $rows = DB::table('barang as b')
            ->whereNotExists(function ($q) use ($start, $finish) {
                $q->select(DB::raw(1))
                    ->from('trkasir_detail as td')
                    ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
                    ->whereColumn('td.kd_barang', 'b.kd_barang')
                    ->whereBetween('tk.tgl_trkasir', [$start, $finish]);
            })
            ->where('b.stok_barang', '>', 0)
            ->orderByDesc('b.stok_barang')
            ->select('b.kd_barang', 'b.nm_barang', 'b.sat_barang', 'b.jenisobat', 'b.stok_barang')
            ->get();

        return response()->view('inventory.mstok.macet-excel', [
            'rows' => $rows,
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Data Barang Macet.xls"',
        ]);
    }

    /** Ports act=edit -- Riwayat Penjualan + Riwayat Pembelian untuk satu item. */
    public function riwayat(string $kdBarang)
    {
        $barang = Product::where('kd_barang', $kdBarang)->firstOrFail();

        $penjualan = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->where('trkasir_detail.kd_barang', $kdBarang)
            ->orderByDesc('trkasir.tgl_trkasir')
            ->orderByDesc('trkasir.id_trkasir')
            ->select(
                'trkasir.tgl_trkasir', 'trkasir.kd_trkasir', 'trkasir.nm_pelanggan', 'trkasir.petugas', 'trkasir.kodetx',
                'trkasir_detail.nmbrg_dtrkasir', 'trkasir_detail.sat_dtrkasir', 'trkasir_detail.hrgjual_dtrkasir',
                'trkasir_detail.qty_dtrkasir', 'trkasir_detail.no_batch', 'trkasir_detail.exp_date'
            )
            ->get();

        $totalKeluar = (float) TrkasirDetail::where('kd_barang', $kdBarang)->sum('qty_dtrkasir');

        $pembelian = TrbmasukDetail::query()
            ->join('trbmasuk', 'trbmasuk.kd_trbmasuk', '=', 'trbmasuk_detail.kd_trbmasuk')
            ->where('trbmasuk_detail.kd_barang', $kdBarang)
            ->orderByDesc('trbmasuk_detail.id_dtrbmasuk')
            ->select('trbmasuk_detail.*', 'trbmasuk.ket_trbmasuk', 'trbmasuk.nm_supplier', 'trbmasuk.petugas', 'trbmasuk.tgl_trbmasuk')
            ->get();

        $totalMasuk = (float) TrbmasukDetail::where('kd_barang', $kdBarang)->sum('qty_dtrbmasuk');

        return view('inventory.mstok.riwayat', [
            'judul' => 'Inventory',
            'barang' => $barang,
            'penjualan' => $penjualan,
            'totalKeluar' => $totalKeluar,
            'pembelian' => $pembelian,
            'totalMasuk' => $totalMasuk,
        ]);
    }

    /** Ports act=order -- Riwayat Pesanan (dari modul Pesan Barang/SupplierOrder) untuk satu item. */
    public function riwayatPesanan(string $kdBarang)
    {
        $barang = Product::where('kd_barang', $kdBarang)->firstOrFail();

        $riwayat = DB::table('ordersdetail')
            ->join('orders', 'orders.kd_trbmasuk', '=', 'ordersdetail.kd_trbmasuk')
            ->where('ordersdetail.kd_barang', $kdBarang)
            ->orderByDesc('orders.tgl_trbmasuk')
            ->select(
                'orders.id_trbmasuk', 'orders.kd_trbmasuk', 'orders.petugas', 'orders.tgl_trbmasuk',
                'ordersdetail.hrgsat_dtrbmasuk', 'ordersdetail.hrgjual_dtrbmasuk', 'ordersdetail.qty_dtrbmasuk'
            )
            ->get();

        $totalPesanan = (float) DB::table('ordersdetail')->where('kd_barang', $kdBarang)->sum('qty_dtrbmasuk');

        return view('inventory.mstok.riwayat-pesanan', [
            'judul' => 'Inventory',
            'barang' => $barang,
            'riwayat' => $riwayat,
            'totalPesanan' => $totalPesanan,
        ]);
    }

    /**
     * Ports act=kritis30 ("PROSES UPDATE DATABASE") -- lihat catatan kelas soal
     * rumus t30/t60/gr yang BEDA dari kolom yang ditampilkan live di listing.
     * SATU statement UPDATE...JOIN, bukan loop PHP per item.
     */
    public function recompute(Request $request)
    {
        $today = now()->toDateString();
        $tglAkhir = now()->copy()->subDays(30)->toDateString();
        $tglAwal2 = now()->copy()->subDays(31)->toDateString();
        $tglAkhir2 = now()->copy()->subDays(60)->toDateString();

        DB::statement('
            UPDATE barang b
            INNER JOIN (SELECT DISTINCT id_barang FROM trbmasuk_detail) scoped ON scoped.id_barang = b.id_barang
            LEFT JOIN (
                SELECT td.kd_barang, COUNT(*) as cnt, SUM(td.qty_dtrkasir) as qty
                FROM trkasir_detail td
                JOIN trkasir tk ON tk.kd_trkasir = td.kd_trkasir
                WHERE tk.tgl_trkasir BETWEEN ? AND ?
                GROUP BY td.kd_barang
            ) t30 ON t30.kd_barang = b.kd_barang
            LEFT JOIN (
                SELECT td.kd_barang, COUNT(*) as cnt
                FROM trkasir_detail td
                JOIN trkasir tk ON tk.kd_trkasir = td.kd_trkasir
                WHERE tk.tgl_trkasir BETWEEN ? AND ?
                GROUP BY td.kd_barang
            ) t60 ON t60.kd_barang = b.kd_barang
            SET
                b.t30 = COALESCE(t30.cnt, 0),
                b.q30 = COALESCE(t30.qty, 0),
                b.t60 = COALESCE(t60.cnt, 0),
                b.gr  = CASE WHEN COALESCE(t60.cnt, 0) = 0 THEN 0 ELSE ROUND((COALESCE(t30.qty, 0) / t60.cnt) * 100) END
        ', [$tglAkhir, $today, $tglAkhir2, $tglAwal2]);

        return redirect()->route('inventory.mstok.index')->with('success', 'Data T30/T60/GR/Q30 berhasil diperbarui.');
    }

    private function kartuStokLinks(string $kdBarang): string
    {
        $riwayat = route('inventory.mstok.riwayat', ['kdBarang' => $kdBarang]);
        $pesanan = route('inventory.mstok.riwayat-pesanan', ['kdBarang' => $kdBarang]);

        return "<a href='{$riwayat}' title='Riwayat' class='btn btn-warning btn-xs'>Riwayat</a> "
            . "<a href='{$pesanan}' title='Riwayat Pesanan' class='btn btn-info btn-xs'>Riwayat Pesanan</a>";
    }
}
