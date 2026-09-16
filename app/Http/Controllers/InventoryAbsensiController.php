<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Admin;
use App\Models\Cuti;
use App\Models\JadwalShift;
use App\Models\MasterShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryAbsensiController extends Controller
{
    /**
     * Modul Kehadiran Pegawai > Absensi -- rekap kehadiran aktual + koreksi
     * manual. Input/edit/hapus manual KHUSUS pemilik ("koreksi absensi" yang
     * diminta user); self check-in/out untuk diri sendiri ada di
     * [[InventoryKehadiranController]], bukan di sini. Petugas dengan akses
     * `kehadiran` hanya bisa melihat rekap miliknya sendiri di sini.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $tglAwal = $request->query('tgl_awal') ?: now()->startOfMonth()->toDateString();
        $tglAkhir = $request->query('tgl_akhir') ?: now()->endOfMonth()->toDateString();
        $idAdminFilter = (int) $request->query('id_admin', 0);

        $query = Absensi::with(['admin', 'shift'])->whereBetween('tanggal', [$tglAwal, $tglAkhir]);

        if (!$admin->isPemilik()) {
            $query->where('id_admin', $admin->id_admin);
        } elseif ($idAdminFilter > 0) {
            $query->where('id_admin', $idAdminFilter);
        }

        $absensiList = $query->orderByDesc('tanggal')->get()->sortBy(fn ($a) => $a->admin->nama_lengkap ?? '')->values();

        $rekapStatus = $absensiList->groupBy('status')->map->count();

        return view('inventory.kehadiran.absensi.index', [
            'judul' => 'Inventory',
            'absensiList' => $absensiList,
            'rekapStatus' => $rekapStatus,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'idAdminFilter' => $idAdminFilter,
            'pegawaiList' => $admin->isPemilik() ? Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get() : collect(),
            'isPemilik' => $admin->isPemilik(),
        ]);
    }

    public function create()
    {
        $this->gatePemilik();

        return view('inventory.kehadiran.absensi.create', [
            'judul' => 'Inventory',
            'pegawaiList' => Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get(),
            'shiftList' => MasterShift::where('status_aktif', true)->orderBy('jam_masuk')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'id_shift' => ['nullable', 'integer', 'exists:master_shift,id_shift'],
            'tanggal' => ['required', 'date'],
            'jam_masuk' => ['nullable'],
            'jam_pulang' => ['nullable'],
            'status' => ['required', 'in:hadir,terlambat,alpha,izin,cuti'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (Absensi::where('id_admin', $validated['id_admin'])->where('tanggal', $validated['tanggal'])->where('id_shift', $validated['id_shift'] ?? null)->exists()) {
            return back()->withInput()->withErrors(['tanggal' => 'Pegawai ini sudah punya catatan absensi untuk tanggal & shift tersebut!']);
        }

        Absensi::create([
            'id_admin' => $validated['id_admin'],
            'id_jadwal' => null,
            'id_shift' => $validated['id_shift'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jam_masuk' => $validated['jam_masuk'] ?? null,
            'jam_pulang' => $validated['jam_pulang'] ?? null,
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
            'sumber' => 'manual',
            'dicatat_oleh' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('inventory.kehadiran.absensi.index')->with('success', 'Absensi berhasil ditambahkan.');
    }

    public function edit(Absensi $absensi)
    {
        $this->gatePemilik();

        return view('inventory.kehadiran.absensi.edit', [
            'judul' => 'Inventory',
            'absensi' => $absensi->load(['admin', 'shift']),
        ]);
    }

    public function update(Request $request, Absensi $absensi)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'jam_masuk' => ['nullable'],
            'jam_pulang' => ['nullable'],
            'status' => ['required', 'in:hadir,terlambat,alpha,izin,cuti'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $absensi->update([
            'jam_masuk' => $validated['jam_masuk'] ?? null,
            'jam_pulang' => $validated['jam_pulang'] ?? null,
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('inventory.kehadiran.absensi.index')->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroy(Absensi $absensi)
    {
        $this->gatePemilik();

        $absensi->delete();

        return back()->with('success', 'Absensi berhasil dihapus.');
    }

    /**
     * Tandai Alpha otomatis: cari jadwal_shift disetujui dalam rentang tanggal
     * yang sudah lewat hari ini, belum ada baris absensi, dan tidak tercakup
     * cuti disetujui -- buat baris absensi status 'alpha' sumber 'sistem'.
     * Dipicu manual lewat tombol (bukan cron, di luar kendali app ini pada
     * hosting), mengikuti pola "sinkron" pada modul Stok Opname.
     */
    public function generateAlpha(Request $request)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'tgl_awal' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date', 'after_or_equal:tgl_awal', 'before:today'],
        ]);

        $sudahDiabsen = Absensi::whereBetween('tanggal', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->pluck('id_jadwal')->filter()->all();

        $jadwalTanpaAbsen = JadwalShift::with('shift')
            ->where('status_approval', 'disetujui')
            ->whereBetween('tanggal', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->whereNotIn('id_jadwal', $sudahDiabsen)
            ->get();

        $dibuat = 0;
        foreach ($jadwalTanpaAbsen as $jadwal) {
            if (Cuti::adaCutiDisetujui($jadwal->id_admin, $jadwal->tanggal->toDateString())) {
                continue;
            }

            Absensi::create([
                'id_admin' => $jadwal->id_admin,
                'id_jadwal' => $jadwal->id_jadwal,
                'id_shift' => $jadwal->id_shift,
                'tanggal' => $jadwal->tanggal,
                'status' => 'alpha',
                'sumber' => 'sistem',
                'keterangan' => 'Ditandai otomatis: terjadwal tapi tidak ada catatan kehadiran.',
            ]);
            $dibuat++;
        }

        return back()->with('success', "$dibuat baris Alpha berhasil dibuat otomatis.");
    }

    private function gatePemilik(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Fitur ini khusus Pemilik.');
    }
}
