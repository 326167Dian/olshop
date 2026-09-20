<?php

namespace App\Http\Middleware;

use App\Models\Reseller;
use Closure;
use Illuminate\Support\Facades\Cookie;

class CaptureResellerReferral
{
    /**
     * Referral disimpan di cookie (bukan session) selama 30 hari, supaya pengunjung
     * yang klik link reseller tapi baru daftar/belanja beberapa hari kemudian tetap
     * tertaut ke reseller yang mengajaknya. Dipakai juga oleh ResellerController
     * (link pendek domain/{id}) supaya durasinya konsisten di kedua tempat.
     */
    public const COOKIE_MINUTES = 30 * 24 * 60;

    public function handle($request, Closure $next)
    {
        $ref = $request->query('ref');

        if ($ref !== null && ctype_digit((string) $ref) && Reseller::whereKey($ref)->exists()) {
            Cookie::queue('reseller_ref', $ref, self::COOKIE_MINUTES);
        }

        return $next($request);
    }
}
