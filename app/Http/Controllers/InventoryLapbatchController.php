<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;

class InventoryLapbatchController extends Controller
{
    /**
     * Modul "Laporan > Laporan Dekat Expired" (flag admin BARU `lapbatch`), mengikuti
     * public/apotekberlian/masuk/modul/mod_batch/laporan_batch.php (module=lapbatch).
     *
     * Rekap sisa stok per batch (masuk - keluar dari ledger `batch`) yang tanggal
     * kadaluarsanya jatuh di antara dua tanggal yang dipilih, HANYA baris yang masih
     * ada sisanya (> 0) -- dipakai apoteker untuk menentukan barang mana yang perlu
     * diprioritaskan terjual/diretur sebelum kadaluarsa.
     *
     * Perbaikan atas legacy:
     * - Query asli melakukan 1 query lookup nm_barang TERPISAH per baris hasil
     *   (N+1) -- di-porting jadi JOIN langsung ke `barang`.
     * - GROUP BY legacy cuma `no_batch` sendirian -- kalau no_batch yang sama
     *   (kebetulan) dipakai lebih dari satu kd_barang/exp_date, MySQL non-strict
     *   mencampurnya jadi satu baris dengan kd_barang/exp_date yang dipilih
     *   sembarangan. Di sini digabung berdasar (kd_barang, no_batch, exp_date)
     *   sekaligus, kunci yang sama dipakai ledger batch di modul lain (lihat
     *   fefoAllocate() di InventoryTrkasirController).
     * - `batch.kd_barang` bertipe varchar tapi `barang.kd_barang` bigint -- JOIN
     *   langsung bisa gagal kalau ada baris lama yang kd_barang-nya kosong/bukan
     *   angka (error 1292 Truncated incorrect DECIMAL value, sama seperti catatan
     *   di SinkronisasiStokBarang/sinkronisasiStok()), dikecualikan di sini juga.
     * - Ditemukan saat porting: ~19% baris `batch` (459 dari 2388 di data lokal)
     *   punya exp_date RUSAK (mis. '2029-00-01', bulan/tanggal = 0) -- bukan
     *   sentinel '0000-00-00' yang sudah dikenal, tapi tanggal tidak valid lain
     *   yang lolos tersimpan. MySQL's BETWEEN tidak mengecualikannya secara
     *   intuitif (nilai rusak itu tetap "ikut lolos" filter rentang tanggal
     *   manapun), jadi dikecualikan eksplisit lewat MONTH()/DAY() di sini supaya
     *   laporan ini tidak menampilkan tanggal kadaluarsa yang membingungkan.
     *   Data rusaknya sendiri TIDAK dibersihkan di sini (di luar cakupan laporan
     *   ini) -- layak jadi pembersihan data terpisah.
     *
     * Flag `lapbatch` BARU dibuat khusus untuk port ini (migration
     * add_lapbatch_flag_to_admin_table) -- legacy TIDAK PERNAH menggerbang halaman
     * ini per-admin (link sidebar selalu tampil untuk siapa pun yang login).
     */
    public function index()
    {
        return view('inventory.lapbatch.index', ['judul' => 'Inventory']);
    }

    public function tampil(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = Batch::query()
            ->join('barang', 'barang.kd_barang', '=', 'batch.kd_barang')
            ->whereRaw("batch.kd_barang REGEXP '^[0-9]+$'")
            ->whereRaw('MONTH(batch.exp_date) != 0 AND DAY(batch.exp_date) != 0')
            ->whereBetween('batch.exp_date', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->selectRaw("
                batch.no_batch, batch.exp_date, batch.kd_barang, barang.nm_barang,
                SUM(CASE WHEN batch.status = 'masuk' THEN batch.qty ELSE 0 END) as total_masuk,
                SUM(CASE WHEN batch.status = 'keluar' THEN batch.qty ELSE 0 END) as total_keluar,
                SUM(CASE WHEN batch.status = 'masuk' THEN batch.qty ELSE 0 END)
                    - SUM(CASE WHEN batch.status = 'keluar' THEN batch.qty ELSE 0 END) as sisa_qty
            ")
            ->groupBy('batch.kd_barang', 'batch.no_batch', 'batch.exp_date', 'barang.nm_barang')
            ->havingRaw("
                SUM(CASE WHEN batch.status = 'masuk' THEN batch.qty ELSE 0 END)
                    - SUM(CASE WHEN batch.status = 'keluar' THEN batch.qty ELSE 0 END) > 0
            ")
            ->orderBy('batch.exp_date')
            ->get();

        return view('inventory.lapbatch.result', [
            'judul' => 'Inventory',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
        ]);
    }
}
