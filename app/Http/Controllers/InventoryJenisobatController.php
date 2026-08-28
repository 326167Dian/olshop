<?php

namespace App\Http\Controllers;

use App\Models\JenisObat;
use Illuminate\Http\Request;

class InventoryJenisobatController extends Controller
{
    /**
     * Modul "Jenis Obat & Rak Obat" (module=jenisobat), mengikuti
     * public/apotekberlian/masuk/modul/mod_jenisobat/jenisobat.php.
     */
    public function index()
    {
        $jenisobat = JenisObat::orderBy('idjenis')->get();

        return view('inventory.jenisobat.index', [
            'judul' => 'Inventory',
            'jenisobat' => $jenisobat,
        ]);
    }

    public function create()
    {
        return view('inventory.jenisobat.create', [
            'judul' => 'Inventory',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenisobat' => 'required|string|max:50',
            'ket' => 'required|string|max:250',
        ]);

        if (JenisObat::where('jenisobat', $validated['jenisobat'])->exists()) {
            return back()->withInput()->withErrors(['jenisobat' => 'Jenis Obat sudah ada!']);
        }

        JenisObat::create($validated);

        return redirect()->route('inventory.jenisobat.index')->with('success', 'Jenis obat berhasil ditambahkan.');
    }

    public function edit(JenisObat $jenisobat)
    {
        return view('inventory.jenisobat.edit', [
            'judul' => 'Inventory',
            'jenisobat' => $jenisobat,
        ]);
    }

    public function update(Request $request, JenisObat $jenisobat)
    {
        $validated = $request->validate([
            'jenisobat' => 'required|string|max:50',
            'ket' => 'required|string|max:250',
        ]);

        $jenisobat->update($validated);

        return redirect()->route('inventory.jenisobat.index')->with('success', 'Jenis obat berhasil diperbarui.');
    }

    public function destroy(JenisObat $jenisobat)
    {
        $jenisobat->delete();

        return redirect()->route('inventory.jenisobat.index')->with('success', 'Jenis obat berhasil dihapus.');
    }
}
