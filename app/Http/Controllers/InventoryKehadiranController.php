<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CompanySetting;
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

    /**
     * Validasi lokasi check-in/check-out terhadap radius apotek, kalau sudah
     * dikonfigurasi di Setting (kehadiran_lat/kehadiran_lng). Kalau belum
     * dikonfigurasi, validasi dilewati (tidak mengunci semua orang sebelum
     * fitur ini di-setup). Return ['lat','lng','jarak','error'] -- error null
     * berarti lolos/dilewati.
     */
    private function validasiRadiusKehadiran(Request $request, string $aksi): array
    {
        $companySetting = CompanySetting::first();

        if (!$companySetting || $companySetting->kehadiran_lat === null || $companySetting->kehadiran_lng === null) {
            return ['lat' => null, 'lng' => null, 'jarak' => null, 'error' => null];
        }

        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ], [
            'lat.required' => "Aktifkan akses lokasi di HP Anda untuk {$aksi}.",
            'lng.required' => "Aktifkan akses lokasi di HP Anda untuk {$aksi}.",
        ]);

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');
        $jarak = $companySetting->jarakMeterDari($lat, $lng);

        $error = null;
        if ($jarak > $companySetting->kehadiran_radius) {
            $error = "Anda berada di luar radius apotek (jarak " . round($jarak) . " meter dari apotek, maksimal {$companySetting->kehadiran_radius} meter). " . ucfirst($aksi) . " ditolak.";
        }

        return ['lat' => $lat, 'lng' => $lng, 'jarak' => round($jarak, 2), 'error' => $error];
    }

    public function checkin(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $hariIni = now()->toDateString();
        $idJadwal = $request->input('id_jadwal') ? (int) $request->input('id_jadwal') : null;

        $lokasi = $this->validasiRadiusKehadiran($request, 'check-in');
        if ($lokasi['error']) {
            return back()->with('error', $lokasi['error']);
        }

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
            'lat_masuk' => $lokasi['lat'],
            'lng_masuk' => $lokasi['lng'],
            'jarak_masuk_meter' => $lokasi['jarak'],
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

        $lokasi = $this->validasiRadiusKehadiran($request, 'check-out');
        if ($lokasi['error']) {
            return back()->with('error', $lokasi['error']);
        }

        $jamPulang = now()->format('H:i:s');
        $absensi->update([
            'jam_pulang' => $jamPulang,
            'lat_pulang' => $lokasi['lat'],
            'lng_pulang' => $lokasi['lng'],
            'jarak_pulang_meter' => $lokasi['jarak'],
        ]);

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
