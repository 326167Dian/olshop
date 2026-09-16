<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Lembur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLemburController extends Controller
{
    /**
     * Modul Kehadiran Pegawai > Lembur -- rekap jam lembur, sumber untuk
     * auto-isi `gaji_detail.lembur` (lihat InventoryGajiDetailController).
     * Baris `sumber='otomatis'` dibuat dari selisih check-out vs jadwal shift
     * (lihat InventoryKehadiranController::checkout()); baris manual dicatat
     * di sini. Approval & manajemen KHUSUS pemilik -- ini yang menentukan
     * angka lembur yang akhirnya masuk ke Slip Gaji, jadi perlu kontrol yang
     * sama ketatnya dengan modul Gaji sendiri. Petugas dengan akses
     * `kehadiran` hanya bisa melihat rekap lembur miliknya sendiri.
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $tglAwal = $request->query('tgl_awal') ?: now()->startOfMonth()->toDateString();
        $tglAkhir = $request->query('tgl_akhir') ?: now()->endOfMonth()->toDateString();
        $idAdminFilter = (int) $request->query('id_admin', 0);

        $query = Lembur::with(['admin', 'admin.gaji'])->whereBetween('tanggal', [$tglAwal, $tglAkhir]);

        if (!$admin->isPemilik()) {
            $query->where('id_admin', $admin->id_admin);
        } elseif ($idAdminFilter > 0) {
            $query->where('id_admin', $idAdminFilter);
        }

        $lemburList = $query->orderByDesc('tanggal')->get()->sortBy(fn ($l) => $l->admin->nama_lengkap ?? '')->values();

        return view('inventory.kehadiran.lembur.index', [
            'judul' => 'Inventory',
            'lemburList' => $lemburList,
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

        return view('inventory.kehadiran.lembur.create', [
            'judul' => 'Inventory',
            'pegawaiList' => Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->gatePemilik();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'tanggal' => ['required', 'date'],
            'jam_lembur' => ['required', 'numeric', 'min:0.01'],
            'keterangan' => ['nullable', 'string'],
        ]);

        Lembur::create([
            'id_admin' => $validated['id_admin'],
            'tanggal' => $validated['tanggal'],
            'jam_lembur' => $validated['jam_lembur'],
            'sumber' => 'manual',
            'status_approval' => 'disetujui',
            'keterangan' => $validated['keterangan'] ?? null,
            'dicatat_oleh' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('inventory.kehadiran.lembur.index')->with('success', 'Lembur berhasil dicatat.');
    }

    public function approve(Lembur $lembur)
    {
        $this->gatePemilik();

        $lembur->update(['status_approval' => 'disetujui']);

        return back()->with('success', 'Lembur berhasil disetujui.');
    }

    public function reject(Lembur $lembur)
    {
        $this->gatePemilik();

        $lembur->update(['status_approval' => 'ditolak']);

        return back()->with('success', 'Lembur berhasil ditolak.');
    }

    public function destroy(Lembur $lembur)
    {
        $this->gatePemilik();

        if ($lembur->ditarik_ke_gaji) {
            return back()->with('error', 'Lembur ini sudah ditarik ke Slip Gaji dan tidak bisa dihapus.');
        }

        $lembur->delete();

        return back()->with('success', 'Lembur berhasil dihapus.');
    }

    private function gatePemilik(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Fitur ini khusus Pemilik.');
    }
}
