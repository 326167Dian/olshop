<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\JadwalShift;
use App\Models\MasterShift;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryJadwalShiftController extends Controller
{
    /**
     * Modul Kehadiran Pegawai > Jadwal Shift -- penjadwalan shift per pegawai
     * per tanggal, dengan approval manual pemilik sebelum dianggap sah (dipakai
     * sebagai acuan hitung status Hadir/Terlambat/Alpha di modul Absensi).
     *
     * Siapa boleh apa: petugas dengan akses `kehadiran` hanya bisa mengajukan
     * jadwal untuk DIRINYA SENDIRI (status 'diajukan', menunggu approval) dan
     * melihat jadwalnya sendiri. Pemilik bisa menjadwalkan siapa saja
     * (langsung 'disetujui', karena penjadwalan oleh pemilik SEKALIGUS
     * approval-nya), melihat semua pegawai, serta approve/reject/hapus jadwal
     * siapa pun.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $tglAwal = $request->query('tgl_awal') ?: now()->startOfWeek()->toDateString();
        $tglAkhir = $request->query('tgl_akhir') ?: now()->endOfWeek()->toDateString();
        $idAdminFilter = (int) $request->query('id_admin', 0);

        $query = JadwalShift::with(['admin', 'shift', 'penyetuju'])
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir]);

        if (!$admin->isPemilik()) {
            $query->where('id_admin', $admin->id_admin);
        } elseif ($idAdminFilter > 0) {
            $query->where('id_admin', $idAdminFilter);
        }

        $jadwalList = $query->orderBy('tanggal')->get()->sortBy(fn ($j) => $j->admin->nama_lengkap ?? '')->values();

        return view('inventory.kehadiran.jadwal.index', [
            'judul' => 'Inventory',
            'jadwalList' => $jadwalList,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'idAdminFilter' => $idAdminFilter,
            'pegawaiList' => $admin->isPemilik() ? Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get() : collect(),
            'isPemilik' => $admin->isPemilik(),
        ]);
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        return view('inventory.kehadiran.jadwal.create', [
            'judul' => 'Inventory',
            'isPemilik' => $admin->isPemilik(),
            'pegawaiList' => $admin->isPemilik() ? Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get() : collect([$admin]),
            'shiftList' => MasterShift::where('status_aktif', true)->orderBy('jam_masuk')->get(),
        ]);
    }

    /**
     * Simpan jadwal untuk satu rentang tanggal sekaligus (mendukung penjadwalan
     * mingguan/bulanan tanpa input satu-satu per hari). Tanggal yang sudah
     * terjadwal shift yang sama dilewati (tidak dianggap error).
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'id_shift' => ['required', 'integer', 'exists:master_shift,id_shift'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!$admin->isPemilik() && (int) $validated['id_admin'] !== $admin->id_admin) {
            abort(403, 'Anda hanya bisa mengajukan jadwal untuk diri sendiri.');
        }

        $statusApproval = $admin->isPemilik() ? 'disetujui' : 'diajukan';
        $dibuat = 0;
        $dilewati = 0;

        foreach (CarbonPeriod::create($validated['tanggal_mulai'], $validated['tanggal_selesai']) as $tanggal) {
            $sudahAda = JadwalShift::where('id_admin', $validated['id_admin'])
                ->where('id_shift', $validated['id_shift'])
                ->where('tanggal', $tanggal->toDateString())
                ->exists();

            if ($sudahAda) {
                $dilewati++;
                continue;
            }

            JadwalShift::create([
                'id_admin' => $validated['id_admin'],
                'id_shift' => $validated['id_shift'],
                'tanggal' => $tanggal->toDateString(),
                'status_approval' => $statusApproval,
                'diajukan_oleh' => $admin->id_admin,
                'disetujui_oleh' => $admin->isPemilik() ? $admin->id_admin : null,
                'disetujui_pada' => $admin->isPemilik() ? now() : null,
                'catatan' => $validated['catatan'] ?? null,
            ]);
            $dibuat++;
        }

        $pesan = "$dibuat jadwal berhasil dibuat.";
        if ($dilewati > 0) {
            $pesan .= " $dilewati tanggal dilewati karena sudah ada jadwal shift yang sama.";
        }

        return redirect()->route('inventory.kehadiran.jadwal.index')->with('success', $pesan);
    }

    public function approve(JadwalShift $jadwal)
    {
        $this->gatePemilik();

        $jadwal->update([
            'status_approval' => 'disetujui',
            'disetujui_oleh' => Auth::guard('admin')->id(),
            'disetujui_pada' => now(),
        ]);

        return back()->with('success', 'Jadwal berhasil disetujui.');
    }

    public function reject(Request $request, JadwalShift $jadwal)
    {
        $this->gatePemilik();

        $validated = $request->validate(['catatan' => ['nullable', 'string']]);

        $jadwal->update([
            'status_approval' => 'ditolak',
            'disetujui_oleh' => Auth::guard('admin')->id(),
            'disetujui_pada' => now(),
            'catatan' => $validated['catatan'] ?? $jadwal->catatan,
        ]);

        return back()->with('success', 'Jadwal berhasil ditolak.');
    }

    public function destroy(JadwalShift $jadwal)
    {
        $admin = Auth::guard('admin')->user();
        $bolehHapus = $admin->isPemilik()
            || ($jadwal->id_admin === $admin->id_admin && $jadwal->status_approval === 'diajukan');

        abort_unless($bolehHapus, 403);

        if ($jadwal->absensi()->exists()) {
            return back()->with('error', 'Jadwal tidak bisa dihapus karena sudah punya catatan absensi.');
        }

        $jadwal->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    private function gatePemilik(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Fitur ini khusus Pemilik.');
    }
}
