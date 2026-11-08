<?php

namespace App\Http\Middleware;

use App\Models\Fitur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEcommerceActive
{
    /**
     * Tolak akses ke halaman toko online (produk, keranjang, checkout, login
     * pelanggan, dll) jika fitur 'E-Commerce' (tabel fitur) dinonaktifkan
     * pemilik lewat Pengaturan Fitur, lihat Fitur::isEcommerceAktif().
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Fitur::isEcommerceAktif()) {
            return redirect('/inventory');
        }

        return $next($request);
    }
}
