<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Tampilkan form login admin backend.
     */
    public function loginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('backend.dashboard');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('home-page');
        }

        return view('backend.auth.login', [
            'judul' => 'Login',
        ]);
    }

    /**
     * Proses autentikasi admin.
     */
    public function unifiedLogin(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        if (Auth::guard('admin')->attempt(['username' => $login, 'password' => $password])) {
            return redirect()->route('backend.dashboard');
        }

        if (Auth::guard('web')->attempt(['email' => $login, 'password' => $password])) {
            return redirect()->route('home-page');
        }

        return back()->withErrors(['auth' => 'Username/email atau password salah.']);
    }

    /**
     * Logout admin dari backend.
     */
    public function logoutBackend()
    {
        Auth::guard('admin')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(route('login'))->with('success', 'Anda telah berhasil logout.');
    }

    public function logoutFrontend()
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(route('home-page'))->with('success', 'Anda telah berhasil logout.');
    }

    // Arahkan ke Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle callback dari Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Simpan atau update user
            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                ]
            );

            Auth::login($user);
            session()->regenerate();
            // Cek apakah telephone dan alamat sudah diisi
            if (
                is_null($user->no_tlp) || $user->no_tlp === '' ||
                is_null($user->alamat) || $user->alamat === ''
            ) {
                return redirect()->route('customer.akun', ['id' => Auth::user()->id])->with('warning', 'Lengkapi data telephone dan alamat terlebih dahulu.');
            }


            return redirect()->route('home-page');
        } catch (\Throwable $th) {
            return redirect()->route('login')->with('error', 'Gagal login via Google.');
        }
    }
}
