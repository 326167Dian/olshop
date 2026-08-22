<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::all(); // Ambil semua data banner
        return view('backend.setting.banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.setting.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto' => 'required|image|max:3048',
        ]);

        // Simpan file foto jika diupload
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('banners', 'public');
        }

        Banner::create($validated);

        return redirect()->route('banner.index')->with('success', 'Banner berhasil ditambahkan!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('backend.setting.banner.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        // Simpan file foto baru jika diupload
        if ($request->hasFile('foto')) {
            // Hapus file lama jika ada
            if ($banner->foto) {
                Storage::disk('public')->delete($banner->foto);
            }

            // Simpan file baru
            $validated['foto'] = $request->file('foto')->store('banners', 'public');
        }

        $banner->update($validated);

        return redirect()->route('banner.index')->with('success', 'Banner berhasil diperbarui!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->foto && \Storage::disk('public')->exists($banner->foto)) {
            \Storage::disk('public')->delete($banner->foto);
        }

        // Hapus data dari database
        $banner->delete();

        return redirect()->route('banner.index')->with('success', 'Banner berhasil dihapus.');
    }
}
