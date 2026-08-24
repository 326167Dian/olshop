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
}
