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
     *
     * 'ref_id' menyimpan parameter pertama route-nya (id kategori/produk, atau slug
     * artikel/video) supaya laporan bisa dipecah per entitas, bukan cuma per nama
     * route -- lihat ReportController::reportVisits().
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $refId = $route ? collect($route->parameters())->first() : null;

        PageVisit::create([
            'page' => $route?->getName() ?? $request->path(),
            'ref_id' => $refId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $next($request);
    }
}
