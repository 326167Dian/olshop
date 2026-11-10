<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SinkronisasiStokBarang extends Command
{
    protected $signature = 'stok:sinkronisasi';

    protected $description = 'Samakan barang.stok_barang dengan total pembelian - total penjualan riil per kd_barang (port job_sinkronisasi_stok.php cron, dengan JOIN ke trbmasuk/trkasir supaya baris trbmasuk_detail/trkasir_detail yang tidak punya header -- data orphan -- tidak ikut dihitung)';

    public function handle(): int
    {
        $start = microtime(true);

        // WHERE kd_barang REGEXP "^[0-9]+$": barang.kd_barang bertipe bigint, tapi
        // trbmasuk_detail/trkasir_detail.kd_barang bertipe varchar -- data lama yang
        // kd_barang-nya kosong/bukan angka (mis. '', 'E000009341') bikin MySQL gagal
        // meng-cast ke angka saat JOIN (error 1292 Truncated incorrect DECIMAL value)
        // dan membatalkan SELURUH UPDATE. Baris semacam itu dikecualikan di sini saja
        // (bukan dihapus), supaya satu baris kotor tidak memblokir sinkronisasi semua
        // barang lain.
        $affected = DB::update('
            UPDATE barang b
            LEFT JOIN (
                SELECT td.kd_barang, SUM(td.qty_dtrbmasuk) AS totalbeli
                FROM trbmasuk_detail td
                INNER JOIN trbmasuk t ON t.kd_trbmasuk = td.kd_trbmasuk
                WHERE td.kd_barang REGEXP "^[0-9]+$"
                GROUP BY td.kd_barang
            ) beli ON beli.kd_barang = b.kd_barang
            LEFT JOIN (
                SELECT td.kd_barang, SUM(td.qty_dtrkasir) AS totaljual
                FROM trkasir_detail td
                INNER JOIN trkasir t ON t.kd_trkasir = td.kd_trkasir
                WHERE td.kd_barang REGEXP "^[0-9]+$"
                GROUP BY td.kd_barang
            ) jual ON jual.kd_barang = b.kd_barang
            SET b.stok_barang = (COALESCE(beli.totalbeli, 0) - COALESCE(jual.totaljual, 0))
            WHERE b.stok_barang <> (COALESCE(beli.totalbeli, 0) - COALESCE(jual.totaljual, 0))
               OR b.stok_barang IS NULL
        ');

        $durationMs = round((microtime(true) - $start) * 1000);

        Log::info("Sinkronisasi stok_barang selesai: {$affected} baris diperbarui ({$durationMs} ms).");
        $this->info("Sinkronisasi stok_barang selesai: {$affected} baris diperbarui ({$durationMs} ms).");

        return self::SUCCESS;
    }
}
