<?php

namespace App\Http\Controllers;

use App\Models\Konseling;
use App\Models\Pelanggan;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryKonselingController extends Controller
{
    /**
     * Modul "Konseling" (module=konseling), mengikuti
     * public/apotekberlian/masuk/modul/mod_konseling/konseling.php.
     * Tidak digerbang flag admin manapun di legacy — semua admin yang login bisa akses.
     */
    public function index()
    {
        $konseling = Konseling::orderByDesc('id_konseling')->get();

        return view('inventory.konseling.index', [
            'judul' => 'Inventory',
            'konseling' => $konseling,
        ]);
    }

    public function create(Request $request)
    {
        return view('inventory.konseling.create', [
            'judul' => 'Inventory',
            'pelangganList' => Pelanggan::orderBy('nm_pelanggan')->get(),
            'idPelangganTerpilih' => $request->query('id_pelanggan', ''),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tgl_konseling' => 'required|date',
            'nama_dokter' => 'required|string|max:100',
            'diagnosa' => 'required|string',
            'riwayat_penyakit' => 'required|string',
            'riwayat_alergi' => 'required|string',
            'keluhan' => 'required|string',
            'visite' => 'required|string|max:100',
            'tindakan' => 'required|string',
        ]);

        $pelanggan = Pelanggan::findOrFail($validated['id_pelanggan']);
        $admin = Auth::guard('admin')->user();

        Konseling::create(array_merge($validated, [
            'nm_pelanggan' => $pelanggan->nm_pelanggan,
            'id_admin' => $admin->id_admin,
            'nama_lengkap' => $admin->nama_lengkap,
        ]));

        return redirect()->route('inventory.konseling.index')->with('success', 'Data konseling berhasil disimpan.');
    }

    public function edit(Konseling $konseling)
    {
        return view('inventory.konseling.edit', [
            'judul' => 'Inventory',
            'konseling' => $konseling,
            'pelangganList' => Pelanggan::orderBy('nm_pelanggan')->get(),
        ]);
    }

    public function update(Request $request, Konseling $konseling)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'tgl_konseling' => 'required|date',
            'nama_dokter' => 'required|string|max:100',
            'diagnosa' => 'required|string',
            'riwayat_penyakit' => 'required|string',
            'riwayat_alergi' => 'required|string',
            'keluhan' => 'required|string',
            'visite' => 'required|string|max:100',
            'tindakan' => 'required|string',
        ]);

        $pelanggan = Pelanggan::findOrFail($validated['id_pelanggan']);
        $admin = Auth::guard('admin')->user();

        $konseling->update(array_merge($validated, [
            'nm_pelanggan' => $pelanggan->nm_pelanggan,
            'id_admin' => $admin->id_admin,
            'nama_lengkap' => $admin->nama_lengkap,
        ]));

        return redirect()->route('inventory.konseling.index')->with('success', 'Data konseling berhasil diperbarui.');
    }

    public function destroy(Konseling $konseling)
    {
        $konseling->delete();

        return redirect()->route('inventory.konseling.index')->with('success', 'Data konseling berhasil dihapus.');
    }

    /**
     * Cetak dokumentasi konseling, mengikuti
     * public/apotekberlian/masuk/modul/mod_konseling/tampil_konseling.php.
     */
    public function print(Konseling $konseling)
    {
        $konseling->load('pelanggan');

        return view('inventory.konseling.print', [
            'konseling' => $konseling,
            'setheader' => Setheader::first(),
        ]);
    }
}
