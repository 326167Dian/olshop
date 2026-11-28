<?php

namespace App\Http\Middleware;

use App\Models\Fitur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotInTransisi
{
    /**
     * Masa transisi (Fitur::isTransisiAktif(), lihat catatan di model) -- tolak
     * SEMUA route /inventory kecuali Buka/Tutup Kasir & Penjualan/Kasir (supaya
     * operasional harian tetap jalan), dan kecuali Pengaturan Fitur sendiri
     * (supaya vendor/mysifa masih bisa menonaktifkan masa transisi ini lagi
     * nanti -- tanpa pengecualian ini, begitu diaktifkan tidak ada jalan untuk
     * mematikannya lewat UI sama sekali).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Fitur::isTransisiAktif()) {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();
        $diizinkan = ['inventory.trkasir.', 'inventory.shiftkerja.', 'inventory.fitur.'];

        foreach ($diizinkan as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        // Pesan sengaja dibuat diplomatis (terkesan kendala teknis), bukan
        // menyebut langsung soal masa transisi/lisensi -- permintaan user.
        return redirect()->route('inventory.trkasir.index')
            ->with('error', 'Saat ini terjadi penurunan bandwidth oleh server, beberapa fitur tidak bisa diakses terutama yang membutuhkan memori besar. Silahkan hubungi admin untuk upgrade paket.');
    }
}
