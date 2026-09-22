<?php

namespace App\Http\Controllers;

use App\Models\LokasiAntar;
use Illuminate\Http\Request;

class LokasiAntarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lokasiAntar = LokasiAntar::orderBy('jarak_min')->get();
        return view('backend.setting.lokasiantar.index', compact('lokasiAntar'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.setting.lokasiantar.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jarak_min' => 'required|numeric|min:0',
            'jarak_max' => 'required|numeric|gt:jarak_min|max:5',
            'biaya_antar' => 'required|numeric|min:0',
        ]);

        LokasiAntar::create($validated);

        return redirect()->route('lokasi-antar.index')->with('success', 'Lokasi antar berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LokasiAntar $lokasiAntar)
    {
        return view('backend.setting.lokasiantar.edit', compact('lokasiAntar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LokasiAntar $lokasiAntar)
    {
        $validated = $request->validate([
            'jarak_min' => 'required|numeric|min:0',
            'jarak_max' => 'required|numeric|gt:jarak_min|max:5',
            'biaya_antar' => 'required|numeric|min:0',
        ]);

        $lokasiAntar->update($validated);

        return redirect()->route('lokasi-antar.index')->with('success', 'Lokasi antar berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LokasiAntar $lokasiAntar)
    {
        $lokasiAntar->delete();

        return redirect()->route('lokasi-antar.index')->with('success', 'Lokasi antar berhasil dihapus.');
    }
}
