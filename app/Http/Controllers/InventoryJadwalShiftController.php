<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\JadwalShift;
use App\Models\MasterShift;
use Carbon\Carbon;
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

    /**
     * Form pengajuan/approval jadwal sebulan sekaligus dalam bentuk grid centang
     * (meniru roster Excel manual yang dulu dipakai): per minggu (Senin-Minggu) x
     * per shift. Tanggal yang sudah lewat tidak ditampilkan sebagai input sama
     * sekali (cuma riwayat, bukan lagi bisa direncanakan).
     *
     * Dua mode tampilan menurut peran (lihat juga store()):
     * - Petugas: grid cuma utk DIRINYA SENDIRI, satu checkbox per sel (shift+tanggal).
     *   Sel yang sudah punya pengajuan aktif miliknya sendiri ditampilkan terkunci
     *   (bukan checkbox lagi), dan nama pegawai LAIN yang sudah mengajukan sel yang
     *   sama ikut ditampilkan sebagai info supaya tidak menumpuk pengajuan.
     * - Pemilik: grid menampilkan SEMUA pegawai aktif sekaligus per sel (satu
     *   checkbox per pegawai per shift per tanggal), sudah tercentang kalau pegawai
     *   itu sudah punya pengajuan/jadwal aktif di sel itu -- pemilik tinggal
     *   centang/hapus centang lalu Simpan utk approve/reassign/batalkan sekaligus
     *   (lihat store()).
     */
    public function create(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isPemilik = $admin->isPemilik();

        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $shiftList = MasterShift::where('status_aktif', true)->orderBy('jam_masuk')->get();

        $data = [
            'judul' => 'Inventory',
            'isPemilik' => $isPemilik,
            'shiftList' => $shiftList,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ];

        if ($isPemilik) {
            $pegawaiAktif = Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get();
            $data['pegawaiAktif'] = $pegawaiAktif;
            $data['mingguGrid'] = $this->buildGridApprovalSemua($bulan, $tahun, $shiftList, $pegawaiAktif);
        } else {
            $data['mingguGrid'] = $this->buildGridPengajuanSendiri($bulan, $tahun, $admin->id_admin);
        }

        return view('inventory.kehadiran.jadwal.create', $data);
    }

    /**
     * Grid utk petugas (lihat create()): sel diisi hanya kalau dalam bulan yang
     * dipilih DAN belum lewat tanggalnya.
     */
    private function buildGridPengajuanSendiri(int $bulan, int $tahun, int $idAdminTarget): array
    {
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $hariIni = Carbon::today();

        $jadwalBulanIni = JadwalShift::with('admin')
            ->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->whereIn('status_approval', ['diajukan', 'disetujui'])
            ->get();

        $perTanggalShift = [];
        foreach ($jadwalBulanIni as $jadwal) {
            $perTanggalShift[$jadwal->tanggal->toDateString()][$jadwal->id_shift][] = $jadwal;
        }

        $mingguAwal = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $mingguAkhir = $akhirBulan->copy()->endOfWeek(Carbon::SUNDAY);

        $minggu = [];
        $cursor = $mingguAwal->copy();

        while ($cursor->lte($mingguAkhir)) {
            $hari = [];
            for ($i = 0; $i < 7; $i++) {
                $tanggal = $cursor->copy()->addDays($i);
                $dalamBulan = $tanggal->month === $bulan && $tanggal->year === $tahun;
                $lewat = $tanggal->lt($hariIni);

                $sel = [];
                if ($dalamBulan && !$lewat) {
                    $jadwalHariIni = $perTanggalShift[$tanggal->toDateString()] ?? [];
                    foreach ($jadwalHariIni as $idShift => $daftarJadwal) {
                        $milikSendiri = collect($daftarJadwal)->first(fn ($j) => $j->id_admin === $idAdminTarget);
                        $pegawaiLain = collect($daftarJadwal)
                            ->where('id_admin', '!=', $idAdminTarget)
                            ->map(fn ($j) => ($j->admin->nama_lengkap ?? '-') . ($j->status_approval === 'diajukan' ? ' (diajukan)' : ''))
                            ->values()
                            ->all();

                        $sel[$idShift] = [
                            'milik_sendiri' => $milikSendiri,
                            'pegawai_lain' => $pegawaiLain,
                        ];
                    }
                }

                $hari[] = [
                    'tanggal' => $tanggal,
                    'dalam_bulan' => $dalamBulan,
                    'lewat' => $lewat,
                    'sel' => $sel,
                ];
            }
            $minggu[] = $hari;
            $cursor->addWeek();
        }

        return $minggu;
    }

    /**
     * Grid utk pemilik (lihat create()): tiap sel (shift+tanggal, dalam bulan &
     * belum lewat) berisi status tiap pegawai aktif (null = belum ada jadwal aktif,
     * 'diajukan'/'disetujui' = sudah ada) supaya view bisa merender satu checkbox
     * per pegawai, sudah tercentang sesuai status saat ini.
     */
    private function buildGridApprovalSemua(int $bulan, int $tahun, $shiftList, $pegawaiAktif): array
    {
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $hariIni = Carbon::today();

        $statusPerKey = [];
        JadwalShift::whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->whereIn('status_approval', ['diajukan', 'disetujui'])
            ->get(['tanggal', 'id_shift', 'id_admin', 'status_approval'])
            ->each(function ($jadwal) use (&$statusPerKey) {
                $statusPerKey[$jadwal->tanggal->toDateString() . '|' . $jadwal->id_shift . '|' . $jadwal->id_admin] = $jadwal->status_approval;
            });

        $mingguAwal = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $mingguAkhir = $akhirBulan->copy()->endOfWeek(Carbon::SUNDAY);

        $minggu = [];
        $cursor = $mingguAwal->copy();

        while ($cursor->lte($mingguAkhir)) {
            $hari = [];
            for ($i = 0; $i < 7; $i++) {
                $tanggal = $cursor->copy()->addDays($i);
                $dalamBulan = $tanggal->month === $bulan && $tanggal->year === $tahun;
                $lewat = $tanggal->lt($hariIni);

                $sel = [];
                if ($dalamBulan && !$lewat) {
                    foreach ($shiftList as $shift) {
                        $perAdmin = [];
                        foreach ($pegawaiAktif as $pegawai) {
                            $key = $tanggal->toDateString() . '|' . $shift->id_shift . '|' . $pegawai->id_admin;
                            $perAdmin[$pegawai->id_admin] = $statusPerKey[$key] ?? null;
                        }
                        $sel[$shift->id_shift] = $perAdmin;
                    }
                }

                $hari[] = [
                    'tanggal' => $tanggal,
                    'dalam_bulan' => $dalamBulan,
                    'lewat' => $lewat,
                    'sel' => $sel,
                ];
            }
            $minggu[] = $hari;
            $cursor->addWeek();
        }

        return $minggu;
    }

    /**
     * Simpan hasil centang grid (lihat create()). Tanggal yang sudah lewat selalu
     * diabaikan di sini juga (pertahanan kedua, bukan cuma andalkan grid tidak
     * merender checkbox-nya di browser).
     *
     * - Petugas: HANYA proses checkbox milik id_admin dirinya sendiri (checkbox
     *   pegawai lain tidak mungkin ter-render utk dia, tapi tetap diabaikan di sini
     *   kalau ada yang nyasar/dimanipulasi) -- centang baru = buat pengajuan
     *   'diajukan', tidak bisa hapus/uncheck pengajuan yang sudah ada dari sini.
     * - Pemilik: proses checkbox SEMUA pegawai aktif sekaligus --
     *   centang baru (belum ada baris aktif) = buat & langsung approve;
     *   centang pada baris yang masih 'diajukan' = approve;
     *   HAPUS CENTANG pada baris aktif yang ada = hapus baris itu (batal/reassign),
     *   kecuali baris itu sudah punya catatan absensi (tidak boleh dihapus).
     */
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $isPemilik = $admin->isPemilik();

        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'catatan' => ['nullable', 'string'],
            'pilihan' => ['nullable', 'array'],
        ]);

        $bulan = $validated['bulan'];
        $tahun = $validated['tahun'];
        $pilihan = $validated['pilihan'] ?? [];
        $catatan = $validated['catatan'] ?? null;

        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $hariIni = Carbon::today();

        if ($isPemilik) {
            $adminIds = Admin::where('blokir', 'N')->pluck('id_admin');
            $shiftIds = MasterShift::where('status_aktif', true)->pluck('id_shift');

            $existing = JadwalShift::whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
                ->whereIn('status_approval', ['diajukan', 'disetujui'])
                ->get()
                ->keyBy(fn ($j) => $j->tanggal->toDateString() . '|' . $j->id_shift . '|' . $j->id_admin);

            $dibuat = 0;
            $disetujui = 0;
            $dihapus = 0;

            for ($tgl = $awalBulan->copy(); $tgl->lte($akhirBulan); $tgl->addDay()) {
                if ($tgl->lt($hariIni)) {
                    continue;
                }
                $tanggal = $tgl->toDateString();

                foreach ($shiftIds as $idShift) {
                    foreach ($adminIds as $idAdmin) {
                        $checked = !empty($pilihan[$tanggal][$idShift][$idAdmin]);
                        $row = $existing->get("$tanggal|$idShift|$idAdmin");

                        if ($checked && !$row) {
                            JadwalShift::create([
                                'id_admin' => $idAdmin,
                                'id_shift' => $idShift,
                                'tanggal' => $tanggal,
                                'status_approval' => 'disetujui',
                                'diajukan_oleh' => $admin->id_admin,
                                'disetujui_oleh' => $admin->id_admin,
                                'disetujui_pada' => now(),
                                'catatan' => $catatan,
                            ]);
                            $dibuat++;
                        } elseif ($checked && $row && $row->status_approval === 'diajukan') {
                            $row->update([
                                'status_approval' => 'disetujui',
                                'disetujui_oleh' => $admin->id_admin,
                                'disetujui_pada' => now(),
                            ]);
                            $disetujui++;
                        } elseif (!$checked && $row && !$row->absensi()->exists()) {
                            $row->delete();
                            $dihapus++;
                        }
                    }
                }
            }

            $pesan = "$dibuat jadwal baru dibuat & disetujui, $disetujui pengajuan disetujui.";
            if ($dihapus > 0) {
                $pesan .= " $dihapus jadwal dibatalkan/dihapus.";
            }

            return redirect()->route('inventory.kehadiran.jadwal.create', [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ])->with('success', $pesan);
        }

        // Petugas: cuma boleh menambah pengajuan utk diri sendiri, tidak bisa hapus
        // yang sudah ada dari sini, dan tanggal yang sudah lewat tetap diabaikan.
        $dibuat = 0;
        $dilewati = 0;

        foreach ($pilihan as $tanggal => $perShift) {
            if (Carbon::parse($tanggal)->lt($hariIni)) {
                continue;
            }

            foreach ($perShift as $idShift => $perAdmin) {
                if (empty($perAdmin[$admin->id_admin])) {
                    continue;
                }

                $sudahAda = JadwalShift::where('id_admin', $admin->id_admin)
                    ->where('id_shift', $idShift)
                    ->where('tanggal', $tanggal)
                    ->whereIn('status_approval', ['diajukan', 'disetujui'])
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;
                    continue;
                }

                JadwalShift::create([
                    'id_admin' => $admin->id_admin,
                    'id_shift' => $idShift,
                    'tanggal' => $tanggal,
                    'status_approval' => 'diajukan',
                    'diajukan_oleh' => $admin->id_admin,
                    'disetujui_oleh' => null,
                    'disetujui_pada' => null,
                    'catatan' => $catatan,
                ]);
                $dibuat++;
            }
        }

        $pesan = "$dibuat jadwal berhasil diajukan.";
        if ($dilewati > 0) {
            $pesan .= " $dilewati sel dilewati karena sudah ada jadwal aktif.";
        }

        return redirect()->route('inventory.kehadiran.jadwal.create', [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ])->with('success', $pesan);
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
