<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /**
     * Catat satu kunjungan halaman (nama route jadi label 'page') ke tabel
     * page_visits, dipasang per-route di routes/web.php untuk halaman publik yang
     * memang mau dipantau -- bukan dipasang global, supaya endpoint AJAX/data
     * (mis. *-data untuk DataTables) tidak ikut terhitung sebagai "kunjungan halaman".
     */
    public function handle(Request $request, Closure $next): Response
    {
        PageVisit::create([
            'page' => $request->route()?->getName() ?? $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }
}
