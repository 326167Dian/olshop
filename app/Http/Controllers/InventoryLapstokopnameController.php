<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\StokOpname;
use App\Models\Trbmasuk;
use App\Models\TrbmasukDetail;
use App\Models\Trkasir;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryLapstokopnameController extends Controller
{
    /**
     * Modul "Laporan > Stok Opname" (grup Laporan, flag admin BARU `lapstokopname`),
     * mengikuti `mod_laporan/laporan_stokopname.php` (index + act=laporanbulanan +
     * act=detail_belum_dicek + act=sinkron_min + act=sinkron_plus) + `cetak_stokopname_excel.php`.
     *
     * **Flag `lapstokopname` BARU dibuat khusus untuk port ini (migration
     * `add_lapstokopname_flag_to_admin_table`) -- legacy TIDAK PERNAH menggerbang
     * halaman ini per-admin.** Link menu di `media_admin.php` hanya dicek
     * `$_SESSION['level'] == 'pemilik' || 'petugas'` (semua staf yang login bisa
     * lihat, sesuai permintaan eksplisit user 2026-09-06: laporan ini sekarang
     * digerbang seperti modul lain -- via checklist di form Admin/Operator,
     * bukan lagi otomatis untuk semua level.
     *
     * **Berbeda dari SELURUH modul Laporan lain yang sudah diporting: modul ini
     * punya 2 aksi yang MENGUBAH DATA NYATA** (`sinkronMinus()`/`sinkronPlus()`),
     * bukan cuma menampilkan laporan. Legacy memicunya lewat link `<a href=...>`
     * biasa (GET) -- diperbaiki jadi route POST (kesalahan REST yang sama yang
     * sudah diperbaiki di modul lain sepanjang port ini, mis. "Undo Transaksi
     * Terhapus"), dibungkus `DB::transaction()`.
     *
     * **Optimasi N+1 diterapkan (sejalan dengan permintaan optimasi sebelumnya):**
     * - Ringkasan "3 bulan terakhir" di index() legacy menjalankan 2 query
     *   TERPISAH (minus/lebih) untuk SETIAP baris tanggal+shift distinct --
     *   di-porting jadi SATU query agregat (`SUM(CASE WHEN ...)`) dikelompokkan
     *   per tanggal+shift.
     * - Export Excel (`cetak_stokopname_excel.php`) melakukan query `barang` DAN
     *   `admin` TERPISAH untuk SETIAP baris stok_opname -- di-porting jadi SATU
     *   query dengan JOIN.
     * - **Bug ditemukan & diperbaiki saat porting** (bukan optimasi): ringkasan
     *   3-bulan legacy memfilter minus/lebih dengan `shift='$shift'` padahal
     *   variabel `$shift` TIDAK PERNAH di-assign di scope ini (harusnya
     *   `$r['shift']`, variabel baris loop) -- artinya angka Minus/Lebih di
     *   ringkasan legacy SELALU salah (PHP undefined-variable jadi string kosong,
     *   match `shift=0` doang) untuk shift Harian manapun. Diperbaiki pakai baris
     *   yang benar.
     *
     * **Ketidakkonsistenan legacy direplikasi apa adanya, bukan disatukan:**
     * daftar detail di `laporanBulanan()` HANYA untuk SATU tanggal persis
     * (`tgl_stokopname = tgl_awal`), sedangkan total footer & rekap per-rak di
     * halaman yang SAMA memakai RENTANG (`BETWEEN tgl_awal AND tgl_akhir`) --
     * dua cakupan tanggal berbeda di satu halaman, sama seperti pola yang sudah
     * ditemukan & didokumentasikan di modul Neraca Laba Rugi/mstok sebelumnya.
     *
     * **Perbaikan ditambahkan pada sinkronisasi (bukan penggantian logika inti):**
     * banyak kolom NOT NULL tanpa default di `trkasir`/`trkasir_detail`/`trbmasuk`/
     * `trbmasuk_detail` yang legacy tidak pernah isi sama sekali (mengandalkan
     * MySQL non-strict) -- diisi nilai wajar mengikuti pola yang SUDAH dipakai di
     * `InventoryTrkasirController`/`InventoryTrbmasukController` (mis. modal/profit
     * per baris, id_pelanggan=0 untuk "tanpa pelanggan", tgl_lunas='1970-01-01'
     * untuk "belum lunas"). Header `trkasir.ttl_trkasir`/`trbmasuk.ttl_trbmasuk`
     * JUGA tidak pernah diisi legacy (selalu default 0 walau baris detail-nya
     * bernilai nyata) -- diperbaiki: dihitung dari SUM baris detail yang baru
     * dibuat, supaya transaksi sinkronisasi ini tidak "tidak terlihat" di laporan
     * penjualan/neraca lain yang membaca `ttl_trkasir`/`ttl_trbmasuk`.
     * Header HANYA dibuat jika ada minimal 1 baris yang disinkron (legacy selalu
     * bikin header meski nol baris -- dihindari di sini, sekadar mencegah sampah
     * baris histori kosong, bukan mengubah logika sinkronisasi itu sendiri).
     */
    public function index()
    {
        $tglAwal = now()->copy()->subDays(120)->toDateString();
        $tglAkhir = now()->toDateString();

        $ringkasan = StokOpname::query()
            ->whereBetween('tgl_stokopname', [$tglAwal, $tglAkhir])
            ->groupBy('tgl_stokopname', 'shift')
            ->orderByDesc('tgl_stokopname')
            ->selectRaw('
                tgl_stokopname, shift,
                SUM(CASE WHEN ttl_hrgbrg < 0 THEN ttl_hrgbrg ELSE 0 END) as minus,
                SUM(CASE WHEN ttl_hrgbrg > 0 THEN ttl_hrgbrg ELSE 0 END) as plus
            ')
            ->get();

        return view('inventory.lapstokopname.index', [
            'judul' => 'Inventory',
            'ringkasan' => $ringkasan,
        ]);
    }

    /** Ports act=laporanbulanan. */
    public function laporan(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:0|max:3',
        ]);

        $detail = StokOpname::query()
            ->join('barang', 'barang.id_barang', '=', 'stok_opname.id_barang')
            ->leftJoin('admin', 'admin.id_admin', '=', 'stok_opname.id_admin')
            ->where('stok_opname.shift', $validated['shift'])
            ->where('stok_opname.tgl_stokopname', $validated['tgl_awal'])
            ->orderBy('barang.nm_barang')
            ->select('stok_opname.*', 'barang.nm_barang', 'barang.sat_barang', 'admin.nama_lengkap')
            ->get();

        $totals = StokOpname::query()
            ->where('shift', $validated['shift'])
            ->whereBetween('tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->selectRaw('
                SUM(CASE WHEN ttl_hrgbrg < 0 THEN ttl_hrgbrg ELSE 0 END) as minus,
                SUM(CASE WHEN ttl_hrgbrg > 0 THEN ttl_hrgbrg ELSE 0 END) as plus
            ')
            ->first();

        $rekapJenis = DB::table('barang as b')
            ->leftJoin('stok_opname as so', function ($join) use ($validated) {
                $join->on('so.id_barang', '=', 'b.id_barang')
                    ->where('so.shift', '=', $validated['shift'])
                    ->whereBetween('so.tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']]);
            })
            ->where('b.stok_barang', '>', 0)
            ->groupBy('b.jenisobat')
            ->orderBy('b.jenisobat')
            ->selectRaw('b.jenisobat, COUNT(DISTINCT b.id_barang) as total_item, COUNT(DISTINCT so.id_barang) as sudah_dicek')
            ->get()
            ->map(function ($row) {
                $row->belum_dicek = $row->total_item - $row->sudah_dicek;

                return $row;
            });

        return view('inventory.lapstokopname.laporan', [
            'judul' => 'Inventory',
            'detail' => $detail,
            'totals' => $totals,
            'rekapJenis' => $rekapJenis,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'shift' => $validated['shift'],
        ]);
    }

    /** Ports act=detail_belum_dicek. */
    public function detailBelumDicek(Request $request)
    {
        $validated = $request->validate([
            'jenisobat' => 'nullable|string',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:0|max:3',
        ]);
        $jenisobat = $validated['jenisobat'] ?? '';

        $rows = Product::query()
            ->where('jenisobat', $jenisobat)
            ->where('stok_barang', '>', 0)
            ->whereNotExists(function ($q) use ($validated) {
                $q->select(DB::raw(1))
                    ->from('stok_opname as so')
                    ->whereColumn('so.id_barang', 'barang.id_barang')
                    ->where('so.shift', $validated['shift'])
                    ->whereBetween('so.tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']]);
            })
            ->orderBy('nm_barang')
            ->get(['kd_barang', 'nm_barang', 'sat_barang', 'stok_barang']);

        return view('inventory.lapstokopname.detail-belum-dicek', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'jenisobat' => $jenisobat,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ]);
    }

    /** Ports cetak_stokopname_excel.php -- lihat catatan kelas soal optimasi N+1. */
    public function excel(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:0|max:3',
        ]);

        $rows = StokOpname::query()
            ->join('barang', 'barang.id_barang', '=', 'stok_opname.id_barang')
            ->leftJoin('admin', 'admin.id_admin', '=', 'stok_opname.id_admin')
            ->where('stok_opname.shift', $validated['shift'])
            ->whereBetween('stok_opname.tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->orderBy('barang.nm_barang')
            ->select('stok_opname.*', 'barang.nm_barang', 'barang.sat_barang', 'admin.nama_lengkap')
            ->get();

        $admin = Auth::guard('admin')->user();

        return response()->view('inventory.lapstokopname.excel', [
            'rows' => $rows,
            'printedBy' => $admin->nama_lengkap ?? '',
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_stokopname.xls"',
        ]);
    }

    /**
     * Ports act=sinkron_min -- tulis-off item minus (stok fisik < sistem) sebagai
     * "penjualan" sintetis, mengurangi stok_barang. Pemilik-only (aksi mengubah
     * data keuangan/stok nyata secara permanen).
     */
    public function sinkronMinus(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:0|max:3',
        ]);

        $rows = StokOpname::query()
            ->where('ttl_hrgbrg', '<', 0)
            ->where('shift', $validated['shift'])
            ->whereBetween('tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('info', 'Tidak ada item minus untuk disinkronisasi pada rentang ini.');
        }

        $kode = DB::transaction(function () use ($rows, $admin, $validated) {
            $kode = 'MINUS-' . now()->format('dmyHis');
            $waktu = now();
            $ttlTrkasir = 0;

            foreach ($rows as $row) {
                $barang = Product::find($row->id_barang);
                if (!$barang) {
                    continue;
                }

                $qtyMin = abs((float) $row->selisih);
                $modal = (float) $barang->hrgsat_barang;

                // FEFO: sama seperti tambahBarang() di InventoryTrkasirController --
                // satu baris trkasir_detail + satu baris ledger batch('keluar') per
                // batch yang benar-benar terpakai, bukan no_batch kosong seperti
                // legacy (lihat catatan kelas -- diminta user 2026-09-06).
                $alokasi = $this->fefoAllocateUntukSinkron($row->kd_barang, $qtyMin);

                foreach ($alokasi as $a) {
                    $qtyChunk = $a['qty'];
                    $hrgTtlChunk = (float) $barang->hrgjual_barang * $qtyChunk;

                    TrkasirDetail::create([
                        'kd_trkasir' => $kode,
                        'id_barang' => $row->id_barang,
                        'kd_barang' => $row->kd_barang,
                        'nmbrg_dtrkasir' => $barang->nm_barang,
                        'qty_dtrkasir' => $qtyChunk,
                        'sat_dtrkasir' => $barang->sat_barang,
                        'hrgjual_dtrkasir' => $barang->hrgjual_barang,
                        'disc' => 0,
                        'modal' => $modal,
                        'profit' => $hrgTtlChunk - ($modal * $qtyChunk),
                        'hrgttl_dtrkasir' => $hrgTtlChunk,
                        'no_batch' => $a['no_batch'],
                        'exp_date' => $a['exp_date'],
                        'tipe' => 1,
                        'komisi' => 0,
                        'idadmin' => $admin->id_admin,
                        'kd_bundle' => '',
                        'nm_bundle' => '',
                        'waktu' => $waktu,
                    ]);

                    Batch::create([
                        'tgl_transaksi' => $waktu,
                        'no_batch' => $a['no_batch'],
                        'exp_date' => $a['exp_date'] ?? '9999-12-31',
                        'qty' => $qtyChunk,
                        'satuan' => $barang->sat_barang,
                        'kd_transaksi' => $kode,
                        'kd_barang' => $row->kd_barang,
                        'status' => 'keluar',
                    ]);

                    $ttlTrkasir += $hrgTtlChunk;
                }

                Product::where('id_barang', $row->id_barang)->decrement('stok_barang', $qtyMin);
            }

            Trkasir::create([
                'kd_trkasir' => $kode,
                'id_user' => $admin->id_admin,
                'petugas' => $admin->nama_lengkap,
                'shift' => $validated['shift'],
                'tgl_trkasir' => now()->toDateString(),
                'id_pelanggan' => 0,
                'nm_pelanggan' => 'SINKRONISASI MINUS',
                'tlp_pelanggan' => '',
                'alamat_pelanggan' => '',
                'kodetx' => '',
                'ttl_trkasir' => $ttlTrkasir,
                'id_carabayar' => 1,
                'jenistx' => 1,
                'poin_awal' => 0,
                'tambahan_poin' => 0,
                'redeem_poin' => 0,
            ]);

            return $kode;
        });

        return back()->with('success', 'Sinkronisasi stok minus berhasil, transaksi ' . $rows->count() . ' item tercatat (kode ' . $kode . ').');
    }

    /**
     * Ports act=sinkron_plus -- catat item lebih (stok fisik > sistem) sebagai
     * "pembelian" sintetis, menambah stok_barang. Pemilik-only.
     */
    public function sinkronPlus(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer|min:0|max:3',
        ]);

        $rows = StokOpname::query()
            ->where('selisih', '>', 0)
            ->where('shift', $validated['shift'])
            ->whereBetween('tgl_stokopname', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('info', 'Tidak ada item lebih untuk disinkronisasi pada rentang ini.');
        }

        $kode = DB::transaction(function () use ($rows, $admin) {
            $kode = 'PLUS-' . now()->format('dmyHis');
            $waktu = now();
            $ttlTrbmasuk = 0;

            foreach ($rows as $row) {
                $barang = Product::find($row->id_barang);
                if (!$barang) {
                    continue;
                }

                $qtyPlus = (float) $row->selisih;

                TrbmasukDetail::create([
                    'kd_trbmasuk' => $kode,
                    'kd_orders' => '',
                    'id_barang' => $row->id_barang,
                    'kd_barang' => $row->kd_barang,
                    'nmbrg_dtrbmasuk' => $barang->nm_barang,
                    'qty_dtrbmasuk' => $qtyPlus,
                    'sat_dtrbmasuk' => $barang->sat_barang,
                    'qty_grosir' => 0,
                    'satgrosir_dtrbmasuk' => '',
                    'hnasat_dtrbmasuk' => $row->hrgsat_barang,
                    'diskon' => 0,
                    'konversi' => 1,
                    'hrgsat_dtrbmasuk' => $row->hrgsat_barang,
                    'hrgjual_dtrbmasuk' => $barang->hrgjual_barang,
                    'hrgttl_dtrbmasuk' => $row->ttl_hrgbrg,
                    'no_batch' => '',
                    'tipe' => 1,
                    'waktu' => $waktu,
                ]);

                Product::where('id_barang', $row->id_barang)->increment('stok_barang', $qtyPlus);

                $ttlTrbmasuk += (float) $row->ttl_hrgbrg;
            }

            Trbmasuk::create([
                'id_resto' => 'pusat',
                'petugas' => $admin->nama_lengkap,
                'kd_trbmasuk' => $kode,
                'kd_orders' => '',
                'tgl_trbmasuk' => now()->toDateString(),
                'id_supplier' => 0,
                'nm_supplier' => 'SINKRONISASI PLUS',
                'tlp_supplier' => '',
                'alamat_trbmasuk' => '',
                'ttl_trbmasuk' => $ttlTrbmasuk,
                'dp_bayar' => 0,
                'sisa_bayar' => 0,
                'ket_trbmasuk' => 'SINKRONISASI PLUS',
                'jatuhtempo' => '',
                'carabayar' => 'TUNAI',
                'jenis' => 'nonpbf',
                'tgl_lunas' => '1970-01-01',
                'petugas_lunas' => '',
            ]);

            return $kode;
        });

        return back()->with('success', 'Sinkronisasi stok lebih berhasil, transaksi ' . $rows->count() . ' item tercatat (kode ' . $kode . ').');
    }

    /**
     * Duplikasi sengaja dari InventoryTrkasirController::fefoAllocate() (method itu
     * private di controller lain, tidak bisa dipanggil langsung dari sini -- pola
     * duplikasi terkontrol yang sama dengan resolveOpenOrderKode() di
     * InventoryStokKritisController). Dipakai HANYA oleh sinkronMinus(): item minus
     * hasil stok opname sekarang ditarik dari batch dengan Exp Date TERDEKAT (FEFO),
     * sama seperti alur normal Penjualan/Kasir, bukan dibiarkan tanpa no_batch/exp_date
     * seperti legacy -- diminta user 2026-09-06 setelah mendapati baris sinkronisasi
     * legacy tidak pernah menyertakan batch sama sekali.
     *
     * @return array<int, array{no_batch: string, exp_date: ?string, qty: float}>
     */
    private function fefoAllocateUntukSinkron(string $kdBarang, float $qty): array
    {
        $ledger = Batch::query()
            ->where('kd_barang', $kdBarang)
            ->selectRaw("no_batch, exp_date, SUM(CASE WHEN status='masuk' THEN qty ELSE 0 END) - SUM(CASE WHEN status='keluar' THEN qty ELSE 0 END) as sisa")
            ->groupBy('no_batch', 'exp_date')
            ->havingRaw('SUM(CASE WHEN status=\'masuk\' THEN qty ELSE 0 END) - SUM(CASE WHEN status=\'keluar\' THEN qty ELSE 0 END) > 0')
            ->orderByRaw('CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC')
            ->get();

        $alokasi = [];
        $sisaButuh = $qty;
        foreach ($ledger as $b) {
            if ($sisaButuh <= 0) {
                break;
            }
            $ambil = min($sisaButuh, (float) $b->sisa);
            if ($ambil <= 0) {
                continue;
            }

            $alokasi[] = ['no_batch' => (string) $b->no_batch, 'exp_date' => $b->exp_date, 'qty' => $ambil];
            $sisaButuh -= $ambil;
        }

        if ($sisaButuh > 0) {
            $alokasi[] = ['no_batch' => '', 'exp_date' => null, 'qty' => $sisaButuh];
        }

        return $alokasi;
    }
}
