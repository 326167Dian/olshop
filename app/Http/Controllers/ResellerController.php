<?php

namespace App\Http\Controllers;

use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerController extends Controller
{
    /**
     * Halaman domain/reseller/register -- tiga kondisi ditangani di satu view yang
     * sama (bukan 3 route terpisah) supaya URL-nya tetap satu selama proses:
     * 1. Belum login (guard web) -> tombol "Daftar dengan Google".
     * 2. Sudah login, belum punya profil reseller -> form isi data reseller.
     * 3. Sudah login DAN sudah punya profil reseller -> lempar ke halaman reseller
     *    (tidak perlu daftar ulang).
     */
    public function register()
    {
        $user = Auth::guard('web')->user();

        if ($user && $user->reseller) {
            return redirect()->route('reseller.home');
        }

        return view('frontend.reseller.register', [
            'judul' => 'Daftar Reseller',
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();
        abort_unless($user, 403, 'Silakan login dengan Google terlebih dahulu.');

        // Satu akun cuma boleh daftar sekali -- kalau sudah punya profil, jangan
        // dibuat ulang/ditimpa lewat form ini, cukup lempar ke halaman reseller.
        if ($user->reseller) {
            return redirect()->route('reseller.home');
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|max:30',
            'nama_bank' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:50',
        ]);

        Reseller::create(array_merge($validated, [
            'user_id' => $user->id,
        ]));

        return redirect()->route('reseller.home')->with('success', 'Pendaftaran reseller berhasil.');
    }

    /**
     * Halaman utama reseller -- masih placeholder ("dalam pengembangan"), hanya
     * bisa diakses reseller yang sudah benar-benar terdaftar.
     */
    public function home()
    {
        $user = Auth::guard('web')->user();

        if (!$user || !$user->reseller) {
            return redirect()->route('reseller.register');
        }

        return view('frontend.reseller.home', [
            'judul' => 'Reseller',
            'reseller' => $user->reseller,
        ]);
    }
}
