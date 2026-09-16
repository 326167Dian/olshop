<?php

namespace App\Http\Controllers;

use App\Models\MasterShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMasterShiftController extends Controller
{
    /**
     * Modul Kehadiran Pegawai > Master Shift -- definisi shift (jam masuk,
     * jam pulang, toleransi telat). KHUSUS pemilik untuk kelola (tambah/ubah/
     * hapus); petugas dengan akses modul `kehadiran` hanya bisa melihat lewat
     * dropdown di form Jadwal Shift, bukan lewat halaman ini langsung.
     */
    public function index()
    {
        $this->gatePemilik();

        $shiftList = MasterShift::orderBy('jam_masuk')->get();

        return view('inventory.kehadiran.shift.index', [
            'judul' => 'Inventory',
            'shiftList' => $shiftList,
        ]);
    }

    public function create()
    {
        $this->gatePemilik();

        return view('inventory.kehadiran.shift.create', ['judul' => 'Inventory']);
    }

    public function store(Request $request)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:50'],
            'jam_masuk' => ['required'],
            'jam_pulang' => ['required'],
            'toleransi_telat' => ['required', 'integer', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        MasterShift::create($validated);

        return redirect()->route('inventory.kehadiran.shift.index')->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(MasterShift $shift)
    {
        $this->gatePemilik();

        return view('inventory.kehadiran.shift.edit', [
            'judul' => 'Inventory',
            'shift' => $shift,
        ]);
    }

    public function update(Request $request, MasterShift $shift)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:50'],
            'jam_masuk' => ['required'],
            'jam_pulang' => ['required'],
            'toleransi_telat' => ['required', 'integer', 'min:0'],
            'status_aktif' => ['required', 'in:0,1'],
        ]);

        $shift->update($validated);

        return redirect()->route('inventory.kehadiran.shift.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(MasterShift $shift)
    {
        $this->gatePemilik();

        if ($shift->jadwal()->exists()) {
            return back()->with('error', 'Shift tidak bisa dihapus karena sudah dipakai di Jadwal Shift. Nonaktifkan saja lewat Edit.');
        }

        $shift->delete();

        return redirect()->route('inventory.kehadiran.shift.index')->with('success', 'Shift berhasil dihapus.');
    }

    private function gatePemilik(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Fitur ini khusus Pemilik.');
    }
}
