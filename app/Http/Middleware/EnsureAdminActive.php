<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminActive
{
    /**
     * Tolak admin yang akunnya diblokir (kolom admin.blokir = 'Y'), meski sesi
     * login guard 'admin' masih valid.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->isBlocked()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login.form')
                ->withErrors(['auth' => 'Akun Anda diblokir. Hubungi administrator.']);
        }

        return $next($request);
    }
}
