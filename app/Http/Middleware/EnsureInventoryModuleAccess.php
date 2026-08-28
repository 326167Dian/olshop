<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryModuleAccess
{
    /**
     * Tolak akses ke modul inventory jika admin yang login tidak punya flag
     * kolom yang diminta (mis. 'mheader', 'mjenisbayar', 'mpelanggan') di
     * tabel admin, sama seperti gerbang akses per-modul pada aplikasi legacy
     * public/apotekberlian (lihat Admin::hasModuleAccess()).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && $admin->hasModuleAccess($module), 403, 'Anda tidak berhak mengakses halaman ini.');

        return $next($request);
    }
}
