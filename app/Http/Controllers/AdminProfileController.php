<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    /**
     * Tampilkan form profil admin yang sedang login.
     */
    public function edit()
    {
        $admin = Auth::user();
        return view('backend.profile.edit', compact('admin'));
    }

    /**
     * Update foto profil admin yang sedang login.
     */
    public function update(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $admin = Auth::user();

        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }

        // Hapus foto lama jika ada
        if ($admin->foto && Storage::disk('public')->exists($admin->foto)) {
            Storage::disk('public')->delete($admin->foto);
        }

        $admin->foto = $request->file('foto')->store('avatars', 'public');
        $admin->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Update info rekening bank/e-wallet admin yang sedang login, dipakai untuk
     * info transfer di Slip Gaji (lihat InventoryGajiDetailController).
     */
    public function updateBank(Request $request)
    {
        $validated = $request->validate([
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'rekening_bank' => ['nullable', 'string', 'max:50'],
        ]);

        Auth::user()->update($validated);

        return redirect()->route('admin.profile.edit')->with('success', 'Info rekening bank berhasil diperbarui.');
    }

    /**
     * Ganti password admin yang sedang login. Password baru langsung menimpa
     * yang lama, tanpa perlu konfirmasi password lama.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_baru.required' => 'Password baru wajib diisi.',
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $admin = Auth::user();
        $admin->password = $request->input('password_baru');
        $admin->save();

        return redirect()->route('admin.profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}
