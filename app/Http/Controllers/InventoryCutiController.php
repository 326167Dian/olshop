<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Cuti;
use App\Models\KuotaCuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryCutiController extends Controller
{
    /**
     * Modul Kehadiran Pegawai > Cuti -- pengajuan & approval cuti. Petugas
     * dengan akses `kehadiran` bisa mengajukan cuti untuk dirinya sendiri dan
     * melihat riwayat + sisa kuota miliknya sendiri; pemilik bisa mengajukan
     * atas nama siapa saja, melihat semua pengajuan, approve/reject, dan
     * atur kuota tahunan per pegawai. Tanggal yang tercakup cuti berstatus
     * disetujui otomatis tidak dihitung Alpha (lihat
     * InventoryAbsensiController::generateAlpha() & Cuti::adaCutiDisetujui()).
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $statusFilter = $request->query('status', '');
        $idAdminFilter = (int) $request->query('id_admin', 0);

        $query = Cuti::with(['admin', 'penyetuju']);

        if (!$admin->isPemilik()) {
            $query->where('id_admin', $admin->id_admin);
        } elseif ($idAdminFilter > 0) {
            $query->where('id_admin', $idAdminFilter);
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $cutiList = $query->orderByDesc('tanggal_mulai')->get();

        $tahunIni = (int) now()->year;

        return view('inventory.kehadiran.cuti.index', [
            'judul' => 'Inventory',
            'cutiList' => $cutiList,
            'statusFilter' => $statusFilter,
            'idAdminFilter' => $idAdminFilter,
            'pegawaiList' => $admin->isPemilik() ? Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get() : collect(),
            'isPemilik' => $admin->isPemilik(),
            'sisaKuotaSendiri' => KuotaCuti::sisaUntuk($admin->id_admin, $tahunIni),
            'tahunIni' => $tahunIni,
        ]);
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        return view('inventory.kehadiran.cuti.create', [
            'judul' => 'Inventory',
            'isPemilik' => $admin->isPemilik(),
            'pegawaiList' => $admin->isPemilik() ? Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get() : collect([$admin]),
        ]);
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'jenis_cuti' => ['required', 'in:tahunan,sakit,izin,lainnya'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string'],
        ]);

        if (!$admin->isPemilik() && (int) $validated['id_admin'] !== $admin->id_admin) {
            abort(403, 'Anda hanya bisa mengajukan cuti untuk diri sendiri.');
        }

        $jumlahHari = Carbon::parse($validated['tanggal_mulai'])->diffInDays(Carbon::parse($validated['tanggal_selesai'])) + 1;

        if ($validated['jenis_cuti'] === 'tahunan') {
            $tahun = (int) Carbon::parse($validated['tanggal_mulai'])->year;
            $sisa = KuotaCuti::sisaUntuk((int) $validated['id_admin'], $tahun);
            if ($jumlahHari > $sisa) {
                return back()->withInput()->withErrors(['tanggal_selesai' => "Sisa kuota cuti tahunan $tahun hanya $sisa hari, pengajuan ini $jumlahHari hari."]);
            }
        }

        Cuti::create([
            'id_admin' => $validated['id_admin'],
            'jenis_cuti' => $validated['jenis_cuti'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'jumlah_hari' => $jumlahHari,
            'alasan' => $validated['alasan'],
            'status' => 'diajukan',
        ]);

        return redirect()->route('inventory.kehadiran.cuti.index')->with('success', 'Pengajuan cuti berhasil dikirim, menunggu approval.');
    }

    public function approve(Request $request, Cuti $cuti)
    {
        $this->gatePemilik();

        $validated = $request->validate(['catatan_approval' => ['nullable', 'string']]);

        $cuti->update([
            'status' => 'disetujui',
            'disetujui_oleh' => Auth::guard('admin')->id(),
            'disetujui_pada' => now(),
            'catatan_approval' => $validated['catatan_approval'] ?? null,
        ]);

        return back()->with('success', 'Cuti berhasil disetujui.');
    }

    public function reject(Request $request, Cuti $cuti)
    {
        $this->gatePemilik();

        $validated = $request->validate(['catatan_approval' => ['nullable', 'string']]);

        $cuti->update([
            'status' => 'ditolak',
            'disetujui_oleh' => Auth::guard('admin')->id(),
            'disetujui_pada' => now(),
            'catatan_approval' => $validated['catatan_approval'] ?? null,
        ]);

        return back()->with('success', 'Cuti berhasil ditolak.');
    }

    public function destroy(Cuti $cuti)
    {
        $admin = Auth::guard('admin')->user();
        $bolehHapus = $admin->isPemilik()
            || ($cuti->id_admin === $admin->id_admin && $cuti->status === 'diajukan');

        abort_unless($bolehHapus, 403);

        $cuti->delete();

        return back()->with('success', 'Pengajuan cuti berhasil dihapus.');
    }

    /**
     * Kelola kuota cuti tahunan per pegawai per tahun. KHUSUS pemilik.
     */
    public function kuotaIndex(Request $request)
    {
        $this->gatePemilik();

        $tahun = (int) $request->query('tahun', now()->year);

        $pegawaiList = Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get()->map(function ($p) use ($tahun) {
            $p->kuota_hari = KuotaCuti::kuotaUntuk($p->id_admin, $tahun);
            $p->terpakai = KuotaCuti::terpakaiUntuk($p->id_admin, $tahun);
            $p->sisa = $p->kuota_hari - $p->terpakai;

            return $p;
        });

        return view('inventory.kehadiran.cuti.kuota', [
            'judul' => 'Inventory',
            'pegawaiList' => $pegawaiList,
            'tahun' => $tahun,
        ]);
    }

    public function kuotaUpdate(Request $request)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'tahun' => ['required', 'integer', 'min:2000'],
            'kuota_hari' => ['required', 'integer', 'min:0'],
        ]);

        KuotaCuti::updateOrCreate(
            ['id_admin' => $validated['id_admin'], 'tahun' => $validated['tahun']],
            ['kuota_hari' => $validated['kuota_hari']]
        );

        return back()->with('success', 'Kuota cuti berhasil diperbarui.');
    }

    private function gatePemilik(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Fitur ini khusus Pemilik.');
    }
}
