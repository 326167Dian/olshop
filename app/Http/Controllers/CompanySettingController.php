<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan profil perusahaan.
     */
    public function index()
    {
        $companySetting = CompanySetting::first();
        return view('backend.setting.company-setting', compact('companySetting'));
    }

    /**
     * Simpan data baru profil perusahaan.
     */
    public function store(Request $request)
    {

        if (CompanySetting::count() > 0) {
            return redirect()->route('company-setting.index')
                ->with('error', 'Profil perusahaan sudah ada. Silakan update data.');
        }

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|max:255',
            'deskripsi'       => 'nullable|string',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'alamat'          => 'nullable|string',
            'telepon'         => 'nullable|string|max:16',
            'peta_lokasi'     => 'nullable|string',
            'catatan'         => 'nullable|string',
        ]);

        // Simpan file logo jika diupload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        CompanySetting::create($validated);

        return redirect()->route('company-setting.index')
            ->with('success', 'Profil perusahaan berhasil disimpan.');
    }

    /**
     * Update data profil perusahaan yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $companySetting = CompanySetting::findOrFail($id);

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|max:255',
            'deskripsi'       => 'nullable|string',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'alamat'          => 'nullable|string',
            'telepon'         => 'nullable|string|max:16',
            'peta_lokasi'     => 'nullable|string',
            'catatan'         => 'nullable|string',
        ]);

        // Hapus logo lama dan simpan yang baru jika diupload
        if ($request->hasFile('logo')) {
            if ($companySetting->logo) {
                Storage::disk('public')->delete($companySetting->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $companySetting->update($validated);

        return redirect()->route('company-setting.index')
            ->with('success', 'Profil perusahaan berhasil diperbarui.');
    }
}
