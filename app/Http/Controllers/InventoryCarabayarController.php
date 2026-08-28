<?php

namespace App\Http\Controllers;

use App\Models\CaraBayar;
use Illuminate\Http\Request;

class InventoryCarabayarController extends Controller
{
    /**
     * Modul "Jenis Pembayaran" (module=carabayar), mengikuti
     * public/apotekberlian/masuk/modul/mod_carabayar/carabayar.php.
     */
    public function index()
    {
        $carabayar = CaraBayar::orderBy('id_carabayar')->get();

        return view('inventory.carabayar.index', [
            'judul' => 'Inventory',
            'carabayar' => $carabayar,
        ]);
    }

    public function create()
    {
        return view('inventory.carabayar.create', [
            'judul' => 'Inventory',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nm_carabayar' => 'required|string|max:100',
        ]);

        if (CaraBayar::where('nm_carabayar', $validated['nm_carabayar'])->exists()) {
            return back()->withInput()->withErrors(['nm_carabayar' => 'Jenis pembayaran sudah ada!']);
        }

        CaraBayar::create([
            'nm_carabayar' => $validated['nm_carabayar'],
            'urutan' => 0,
        ]);

        return redirect()->route('inventory.carabayar.index')->with('success', 'Jenis pembayaran berhasil ditambahkan.');
    }

    public function edit(CaraBayar $carabayar)
    {
        return view('inventory.carabayar.edit', [
            'judul' => 'Inventory',
            'carabayar' => $carabayar,
        ]);
    }

    public function update(Request $request, CaraBayar $carabayar)
    {
        $validated = $request->validate([
            'nm_carabayar' => 'required|string|max:100',
        ]);

        $carabayar->update($validated);

        return redirect()->route('inventory.carabayar.index')->with('success', 'Jenis pembayaran berhasil diperbarui.');
    }

    public function destroy(CaraBayar $carabayar)
    {
        $carabayar->delete();

        return redirect()->route('inventory.carabayar.index')->with('success', 'Jenis pembayaran berhasil dihapus.');
    }
}
