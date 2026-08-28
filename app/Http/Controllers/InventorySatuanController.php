<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;

class InventorySatuanController extends Controller
{
    /**
     * Modul "Satuan" (module=satuan), mengikuti
     * public/apotekberlian/masuk/modul/mod_satuan/satuan.php.
     */
    public function index()
    {
        $satuan = Satuan::orderBy('id_satuan')->get();

        return view('inventory.satuan.index', [
            'judul' => 'Inventory',
            'satuan' => $satuan,
        ]);
    }

    public function create()
    {
        return view('inventory.satuan.create', [
            'judul' => 'Inventory',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nm_satuan' => 'required|string|max:50',
            'deskripsi' => 'required|string|max:250',
        ]);

        if (Satuan::where('nm_satuan', $validated['nm_satuan'])->exists()) {
            return back()->withInput()->withErrors(['nm_satuan' => 'Satuan sudah ada!']);
        }

        Satuan::create($validated);

        return redirect()->route('inventory.satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Satuan $satuan)
    {
        return view('inventory.satuan.edit', [
            'judul' => 'Inventory',
            'satuan' => $satuan,
        ]);
    }

    public function update(Request $request, Satuan $satuan)
    {
        $validated = $request->validate([
            'nm_satuan' => 'required|string|max:50',
            'deskripsi' => 'required|string|max:250',
        ]);

        $satuan->update($validated);

        return redirect()->route('inventory.satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Satuan $satuan)
    {
        $satuan->delete();

        return redirect()->route('inventory.satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
