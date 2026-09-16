<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\JadwalShift;
use App\Models\KuotaCuti;
use App\Models\Lembur;
use App\Models\MasterShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryKehadiranController extends Controller
{
    /**
     * Modul "Kehadiran Pegawai" -- halaman utama (ringkasan + akses cepat ke
     * Master Shift/Jadwal Shift/Absensi/Lembur/Cuti) dan aksi self-service
     * check-in/check-out untuk admin yang sedang login sendiri.
     *
     * Terbuka untuk admin manapun dengan flag `kehadiran='Y'` (lihat
     * `inventory.module:kehadiran` middleware di routes/web.php), TIDAK
     * khusus pemilik seperti modul Gaji -- baik petugas maupun pemilik
     * memakai modul ini (petugas: self check-in/out, lihat jadwal & kuota
     * cuti sendiri, ajukan cuti; pemilik: kelola shift, approve jadwal/cuti,
     * koreksi absensi, generate & approve lembur). Aksi yang khusus pemilik
     * dikunci lewat abort_unless(isPemilik()) di masing-masing controller
     * anak (MasterShift/JadwalShift/Absensi/Lembur/Cuti), bukan di sini.
     *
     * Kenapa perlu halaman self-service terpisah dari modul Buka/Tutup Kasir
     * (`shiftkerja`) yang sudah ada: satu shift kasir biasanya dibuka/ditutup
     * oleh SATU orang saja, jadi pegawai lain yang masuk shift yang sama
     * tidak pernah tercatat di `waktukerja` -- modul ini melacak KEHADIRAN
     * per pegawai, terpisah dari siapa yang pegang laci kasir.
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $hariIni = now()->toDateString();

        $jadwalHariIni = JadwalShift::with('shift')
            ->where('id_admin', $admin->id_admin)
            ->where('tanggal', $hariIni)
            ->where('status_approval', 'disetujui')
            ->get();

        $absensiHariIni = Absensi::where('id_admin', $admin->id_admin)
            ->where('tanggal', $hariIni)
            ->get()
            ->keyBy('id_jadwal');

        $tahunIni = (int) now()->year;

        return view('inventory.kehadiran.index', [
            'judul' => 'Inventory',
            'isPemilik' => $admin->isPemilik(),
            'jadwalHariIni' => $jadwalHariIni,
            'absensiHariIni' => $absensiHariIni,
            'sisaKuotaCuti' => KuotaCuti::sisaUntuk($admin->id_admin, $tahunIni),
            'kuotaCutiTahun' => $tahunIni,
            'menungguApprovalJadwal' => $admin->isPemilik() ? JadwalShift::where('status_approval', 'diajukan')->count() : 0,
            'menungguApprovalCuti' => $admin->isPemilik() ? Cuti::where('status', 'diajukan')->count() : 0,
            'menungguApprovalLembur' => $admin->isPemilik() ? Lembur::where('status_approval', 'diajukan')->count() : 0,
        ]);
    }

    /**
     * Check-in mandiri untuk diri sendiri. Kalau ada TEPAT SATU jadwal
     * disetujui hari ini yang belum diabsen, langsung dipakai. Kalau lebih
     * dari satu, admin harus pilih dulu (form checkinForm). Kalau tidak ada
     * jadwal sama sekali, tetap boleh check-in tanpa jadwal (ad-hoc).
     */
    public function checkinForm()
    {
        $admin = Auth::guard('admin')->user();
        $hariIni = now()->toDateString();

        $sudahDiabsen = Absensi::where('id_admin', $admin->id_admin)->where('tanggal', $hariIni)->pluck('id_jadwal')->all();

        $jadwalBelumAbsen = JadwalShift::with('shift')
            ->where('id_admin', $admin->id_admin)
            ->where('tanggal', $hariIni)
            ->where('status_approval', 'disetujui')
            ->whereNotIn('id_jadwal', $sudahDiabsen)
            ->get();

        return view('inventory.kehadiran.checkin', [
            'judul' => 'Inventory',
            'jadwalBelumAbsen' => $jadwalBelumAbsen,
        ]);
    }

    public function checkin(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $hariIni = now()->toDateString();
        $idJadwal = $request->input('id_jadwal') ? (int) $request->input('id_jadwal') : null;

        $shift = null;
        if ($idJadwal) {
            $jadwal = JadwalShift::with('shift')->where('id_jadwal', $idJadwal)
                ->where('id_admin', $admin->id_admin)
                ->where('status_approval', 'disetujui')
                ->first();

            abort_unless($jadwal, 422, 'Jadwal tidak ditemukan.');

            if (Absensi::where('id_jadwal', $idJadwal)->exists()) {
                return back()->with('error', 'Anda sudah check-in untuk jadwal ini.');
            }

            $shift = $jadwal->shift;
        }

        $jamMasuk = now()->format('H:i:s');

        Absensi::create([
            'id_admin' => $admin->id_admin,
            'id_jadwal' => $idJadwal,
            'id_shift' => $shift?->id_shift,
            'tanggal' => $hariIni,
            'jam_masuk' => $jamMasuk,
            'status' => Absensi::hitungStatusMasuk($shift, $jamMasuk),
            'sumber' => 'self_service',
            'dicatat_oleh' => null,
        ]);

        return redirect()->route('inventory.kehadiran.index')->with('success', 'Check-in berhasil dicatat.');
    }

    public function checkout(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $hariIni = now()->toDateString();
        $idAbsensi = (int) $request->input('id_absensi');

        $absensi = Absensi::with('shift')->where('id_absensi', $idAbsensi)
            ->where('id_admin', $admin->id_admin)
            ->where('tanggal', $hariIni)
            ->first();

        abort_unless($absensi, 422, 'Data absensi hari ini tidak ditemukan.');

        if ($absensi->jam_pulang) {
            return back()->with('error', 'Anda sudah check-out untuk absensi ini.');
        }

        $jamPulang = now()->format('H:i:s');
        $absensi->update(['jam_pulang' => $jamPulang]);

        $jamLembur = Absensi::hitungJamLembur($absensi->shift, $jamPulang);
        if ($jamLembur > 0) {
            Lembur::create([
                'id_admin' => $admin->id_admin,
                'id_absensi' => $absensi->id_absensi,
                'tanggal' => $hariIni,
                'jam_lembur' => $jamLembur,
                'sumber' => 'otomatis',
                'status_approval' => 'diajukan',
                'keterangan' => 'Otomatis dari selisih jam pulang check-out vs jadwal shift.',
                'dicatat_oleh' => null,
            ]);
        }

        return redirect()->route('inventory.kehadiran.index')->with('success', 'Check-out berhasil dicatat.' . ($jamLembur > 0 ? " Lembur $jamLembur jam tercatat, menunggu approval pemilik." : ''));
    }
}
