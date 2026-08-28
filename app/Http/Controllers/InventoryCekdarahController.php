<?php

namespace App\Http\Controllers;

use App\Models\CekDarah;
use App\Models\Pelanggan;
use App\Models\Setheader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryCekdarahController extends Controller
{
    /**
     * Modul "Cek Darah" (module=cekdarah), mengikuti
     * public/apotekberlian/masuk/modul/mod_cekdarah/cekdarah.php.
     */
    public function index()
    {
        $cekdarah = CekDarah::with('pelanggan')->orderByDesc('id_cekdarah')->get();

        return view('inventory.cekdarah.index', [
            'judul' => 'Inventory',
            'cekdarah' => $cekdarah,
        ]);
    }

    public function create(Request $request)
    {
        return view('inventory.cekdarah.create', [
            'judul' => 'Inventory',
            'pelangganList' => Pelanggan::orderBy('nm_pelanggan')->get(),
            'idPelangganTerpilih' => $request->query('id', ''),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'gula' => 'required|string|max:50',
            'asamurat' => 'required|string|max:50',
            'kolesterol' => 'required|string|max:50',
            'tensi' => 'required|string|max:50',
        ]);

        CekDarah::create(array_merge($validated, [
            'petugas' => Auth::guard('admin')->user()->nama_lengkap,
            'waktu' => now(),
        ]));

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Hasil cek darah berhasil disimpan.');
    }

    public function edit(CekDarah $cekdarah)
    {
        $cekdarah->load('pelanggan');

        return view('inventory.cekdarah.edit', [
            'judul' => 'Inventory',
            'cekdarah' => $cekdarah,
        ]);
    }

    public function update(Request $request, CekDarah $cekdarah)
    {
        $validated = $request->validate([
            'gula' => 'required|string|max:50',
            'asamurat' => 'required|string|max:50',
            'kolesterol' => 'required|string|max:50',
            'tensi' => 'required|string|max:50',
        ]);

        $cekdarah->update($validated);

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Hasil cek darah berhasil diperbarui.');
    }

    public function destroy(CekDarah $cekdarah)
    {
        $cekdarah->delete();

        return redirect()->route('inventory.cekdarah.index')->with('success', 'Data cek darah berhasil dihapus.');
    }

    /**
     * Cetak hasil cek darah, mengikuti
     * public/apotekberlian/masuk/modul/mod_cekdarah/print.php (di sana pakai FPDF;
     * di sini pakai halaman cetak HTML seperti invoice pesanan, tanpa dependensi baru).
     */
    public function print(CekDarah $cekdarah)
    {
        $cekdarah->load('pelanggan');

        return view('inventory.cekdarah.print', [
            'cekdarah' => $cekdarah,
            'setheader' => Setheader::first(),
        ]);
    }
}
