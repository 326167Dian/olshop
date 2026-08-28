<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class InventoryPelangganController extends Controller
{
    /**
     * Modul "Pelanggan" (module=pelanggan): data master pelanggan saja,
     * mengikuti public/apotekberlian/masuk/modul/mod_pelanggan/pelanggan.php
     * (kasus default/tambah/edit). Fitur riwayat/swamedikasi/poin di modul
     * legacy tersebut adalah modul terpisah dan belum diadaptasi di sini.
     */
    public function index()
    {
        $pelanggan = Pelanggan::orderBy('id_pelanggan')->get();

        return view('inventory.pelanggan.index', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
        ]);
    }

    public function create()
    {
        return view('inventory.pelanggan.create', [
            'judul' => 'Inventory',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nm_pelanggan' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:PRIA,WANITA',
            'tanggal_lahir' => 'nullable|date',
            'tlp_pelanggan' => 'nullable|string|max:30',
            'alamat_pelanggan' => 'nullable|string',
            'ket_pelanggan' => 'nullable|string',
        ]);

        Pelanggan::create(array_merge($validated, [
            'tlp_pelanggan' => $validated['tlp_pelanggan'] ?? '',
            'alamat_pelanggan' => $validated['alamat_pelanggan'] ?? '',
            'ket_pelanggan' => $validated['ket_pelanggan'] ?? '',
            'unit' => 1,
            'total_poin' => 0,
        ]));

        return redirect()->route('inventory.pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('inventory.pelanggan.edit', [
            'judul' => 'Inventory',
            'pelanggan' => $pelanggan,
        ]);
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'nm_pelanggan' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:PRIA,WANITA',
            'tanggal_lahir' => 'nullable|date',
            'tlp_pelanggan' => 'nullable|string|max:30',
            'alamat_pelanggan' => 'nullable|string',
            'ket_pelanggan' => 'nullable|string',
        ]);

        $pelanggan->update(array_merge($validated, [
            'tlp_pelanggan' => $validated['tlp_pelanggan'] ?? '',
            'alamat_pelanggan' => $validated['alamat_pelanggan'] ?? '',
            'ket_pelanggan' => $validated['ket_pelanggan'] ?? '',
        ]));

        return redirect()->route('inventory.pelanggan.index')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();

        return redirect()->route('inventory.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
