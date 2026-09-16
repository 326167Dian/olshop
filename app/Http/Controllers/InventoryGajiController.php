<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Gaji;
use App\Models\GajiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryGajiController extends Controller
{
    /**
     * Modul "Gaji Karyawan" (adaptasi public/yasfigrup/masuk/modul/mod_gaji ->
     * gaji.php/aksi_gaji.php). Data dasar gaji pokok harian + transportasi/ekstra
     * fooding harian per karyawan, dipakai sebagai basis hitung saat membuat Slip
     * Gaji (lihat [[InventoryGajiDetailController]]).
     *
     * Sama seperti legacy Yasfi (`$_SESSION['level'] != 'pemilik'`), modul ini
     * KHUSUS pemilik -- tidak ada kolom flag admin sendiri, digerbang manual
     * lewat gate() + disuntik ke sidebar app.blade.php, mengikuti pola
     * InventoryLapkomisiController/InventoryEvaluasiController.
     *
     * Beda dari legacy Yasfi: tidak ada pengecualian `id_admin != 3` (khusus
     * database Yasfi) -- di sini karyawan yang bisa didaftarkan cukup admin
     * dengan blokir='N' dan belum punya baris gaji.
     */
    public function index()
    {
        $this->gate();

        $gajiList = Gaji::with('admin')->get()->sortBy(fn ($g) => $g->admin->nama_lengkap ?? '')->values();

        return view('inventory.gaji.index', [
            'judul' => 'Inventory',
            'gajiList' => $gajiList,
        ]);
    }

    public function create()
    {
        $this->gate();

        $petugas = Admin::where('blokir', 'N')
            ->whereDoesntHave('gaji')
            ->orderBy('nama_lengkap')
            ->get();

        return view('inventory.gaji.create', [
            'judul' => 'Inventory',
            'petugas' => $petugas,
        ]);
    }

    public function store(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'gaji_harian' => ['required', 'numeric', 'min:0'],
            'transportasi_harian' => ['required', 'numeric', 'min:0'],
            'rate_lembur' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $admin = Admin::where('id_admin', $validated['id_admin'])->where('blokir', 'N')->first();
        if (!$admin) {
            return back()->withInput()->withErrors(['id_admin' => 'Karyawan tidak ditemukan atau diblokir!']);
        }

        if (Gaji::where('id_admin', $admin->id_admin)->exists()) {
            return back()->withInput()->withErrors(['id_admin' => 'Karyawan ini sudah memiliki data gaji!']);
        }

        Gaji::create($validated);

        return redirect()->route('inventory.gaji.index')->with('success', 'Data gaji berhasil ditambahkan.');
    }

    public function edit(Gaji $gaji)
    {
        $this->gate();

        return view('inventory.gaji.edit', [
            'judul' => 'Inventory',
            'gaji' => $gaji->load('admin'),
        ]);
    }

    public function update(Request $request, Gaji $gaji)
    {
        $this->gate();

        $validated = $request->validate([
            'gaji_harian' => ['required', 'numeric', 'min:0'],
            'transportasi_harian' => ['required', 'numeric', 'min:0'],
            'rate_lembur' => ['required', 'numeric', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $gaji->update($validated);

        return redirect()->route('inventory.gaji.index')->with('success', 'Data gaji berhasil diperbarui.');
    }

    public function destroy(Gaji $gaji)
    {
        $this->gate();

        if (GajiDetail::where('id_gaji', $gaji->id_gaji)->exists()) {
            return back()->with('error', 'Data gaji tidak bisa dihapus karena karyawan ini sudah memiliki riwayat slip gaji. Nonaktifkan saja datanya lewat EDIT.');
        }

        $gaji->delete();

        return redirect()->route('inventory.gaji.index')->with('success', 'Data gaji berhasil dihapus.');
    }

    private function gate(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Anda tidak berhak mengakses halaman ini. Fitur ini khusus Pemilik.');
    }
}
