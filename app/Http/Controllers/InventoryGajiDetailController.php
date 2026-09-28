<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Admin;
use App\Models\Cuti;
use App\Models\Gaji;
use App\Models\GajiDetail;
use App\Models\KomisiGlobal;
use App\Models\Lembur;
use App\Models\Setheader;
use App\Models\TrkasirDetail;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryGajiDetailController extends Controller
{
    /**
     * Modul "Slip Gaji" (adaptasi public/yasfigrup/masuk/modul/mod_gajidetail ->
     * gajidetail.php/aksi_gajidetail.php/cetak_slip.php). Satu baris per periode
     * (bulan) per karyawan, dibuat dari data dasar di [[InventoryGajiController]].
     *
     * KHUSUS pemilik, sama seperti modul Gaji -- lihat catatan gate() di sana.
     *
     * Komisi otomatis: kalau field Komisi dikosongkan, dihitung dari
     * Komisi Produk (SUM(trkasir_detail.komisi) milik id_admin pada rentang
     * Tanggal Awal-Akhir) + Komisi Global (persentase `komisiglobal` yang
     * berstatus ON x total penjualan `trkasir.petugas` = nama_lengkap pada
     * rentang yang sama) -- formula & sumber tabel PERSIS sama dengan
     * InventoryLapkomisiController (laporan Komisi Pegawai yang sudah ada),
     * bukan skema tabel legacy Yasfi (aplikasi asal fitur ini beda database
     * dari aplikasi Laravel ini).
     *
     * Kolom `konsumsi` tetap ada di skema (paritas dengan legacy) tapi
     * dinonaktifkan sementara di form, mengikuti legacy yang juga
     * mengomentari field ini.
     *
     * Lembur otomatis (modul Kehadiran Pegawai): kalau field Lembur
     * dikosongkan, dihitung dari baris `lembur` milik id_admin yang berstatus
     * disetujui & belum ditarik dalam rentang Tanggal Awal-Akhir -- DUA jenis
     * dengan formula beda: tipe 'per_jam' (cuma dari input MANUAL pemilik di
     * menu Kehadiran Pegawai > Lembur, mis. lembur di luar pola shift/jadwal)
     * = SUM(jam_lembur) x `gaji.rate_lembur` ("Tarif Lembur 1 Shift", dipakai
     * per-jam untuk kasus manual ini); tipe 'per_shift' (OTOMATIS dari shift
     * tambahan/ke-2+ di hari yang sama, satu-satunya yang otomatis tercatat
     * sejak checkout() diperbarui) = COUNT(*) x `gaji.rate_lembur`, flat per
     * shift, TIDAK dikali jam kerja aktualnya. Shift TUNGGAL yang cuma pulang
     * lewat dari jadwalnya sendiri TIDAK LAGI otomatis tercatat sebagai lembur
     * (dulu begitu, ternyata cuma angka receh 2-20 menit).
     * Baris `lembur` yang ikut dihitung ditandai ditarik_ke_gaji=true supaya
     * tidak terhitung dobel di slip periode berikutnya. Penandaan ini SENGAJA
     * hanya jalan kalau field dikosongkan (auto-calc) -- kalau pemilik mengisi
     * manual, baris `lembur` dibiarkan apa adanya karena tidak ada jaminan
     * angka manual itu benar-benar berasal dari menjumlah baris-baris tersebut.
     *
     * Potongan Telat otomatis: kalau field Potongan Telat dikosongkan,
     * dihitung dari Absensi periode itu -- tiap baris yang jam_masuk aktualnya
     * melewati batasTerlambat() shift (jam_masuk shift + toleransi_telat)
     * kena potongan berjenjang sesuai `gaji.potongan_telat_15_30`/`_30_60`/
     * `_60_lebih` (lihat Absensi::hitungMenitTerlambat()). Beda dari Lembur,
     * potongan ini TIDAK lewat alur approval -- mekanis langsung dari jam
     * tercatat, dan aman dihitung ulang tiap slip karena satu tanggal cuma
     * pernah masuk satu periode (periode_bulan unik per karyawan).
     */
    public function index(Request $request)
    {
        $this->gate();

        $bulanFilter = (int) $request->query('bulan', 0);
        $tahunFilter = (int) $request->query('tahun', 0);
        $idGajiFilter = (int) $request->query('id_gaji', 0);

        $query = GajiDetail::with(['admin', 'pembuat', 'penyetuju']);

        if ($bulanFilter > 0 && $tahunFilter > 0) {
            $query->where('periode_bulan', sprintf('%04d-%02d', $tahunFilter, $bulanFilter));
        }
        if ($idGajiFilter > 0) {
            $query->where('id_gaji', $idGajiFilter);
        }

        $slipList = $query->get()->sortByDesc('periode_bulan')->sortBy(fn ($d) => $d->admin->nama_lengkap ?? '')->values();

        return view('inventory.gajidetail.index', [
            'judul' => 'Inventory',
            'slipList' => $slipList,
            'bulanFilter' => $bulanFilter,
            'tahunFilter' => $tahunFilter,
        ]);
    }

    public function create(Request $request)
    {
        $this->gate();

        $karyawan = Gaji::with('admin')->where('status_aktif', 1)->get()
            ->filter(fn ($g) => $g->admin !== null)
            ->sortBy(fn ($g) => $g->admin->nama_lengkap)
            ->values();

        $pemilik = Admin::where('akses_level', 'pemilik')->where('blokir', 'N')->orderBy('nama_lengkap')->get();

        return view('inventory.gajidetail.create', [
            'judul' => 'Inventory',
            'karyawan' => $karyawan,
            'pemilik' => $pemilik,
            'preselectIdGaji' => (int) $request->query('id_gaji', 0),
        ]);
    }

    public function store(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'id_gaji' => ['required', 'integer', 'exists:gaji,id_gaji'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2000'],
            'tgl_awal' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date'],
            'jumlah_hari' => ['required', 'integer', 'min:0'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'transportasi' => ['required', 'numeric', 'min:0'],
            'pinjaman' => ['required', 'numeric', 'min:0'],
            'lembur' => ['nullable', 'numeric', 'min:0'],
            'komisi' => ['nullable', 'numeric', 'min:0'],
            'potongan_telat' => ['nullable', 'numeric', 'min:0'],
            'disetujui_oleh' => ['nullable', 'integer', 'exists:admin,id_admin'],
        ]);

        $gaji = Gaji::with('admin')->find($validated['id_gaji']);
        if (!$gaji || !$gaji->admin) {
            return back()->withInput()->withErrors(['id_gaji' => 'Karyawan tidak ditemukan di Data Gaji!']);
        }

        $periodeBulan = sprintf('%04d-%02d', $validated['tahun'], $validated['bulan']);

        if (GajiDetail::where('id_admin', $gaji->id_admin)->where('periode_bulan', $periodeBulan)->exists()) {
            return back()->withInput()->withErrors(['bulan' => 'Karyawan ini sudah punya slip gaji untuk periode tersebut!']);
        }

        $komisi = $request->filled('komisi')
            ? (float) $validated['komisi']
            : $this->hitungKomisiOtomatis($gaji->id_admin, $gaji->admin->nama_lengkap, $validated['tgl_awal'], $validated['tgl_akhir']);

        $lemburOtomatis = !$request->filled('lembur');
        $lembur = $lemburOtomatis
            ? $this->hitungLemburOtomatis($gaji, $validated['tgl_awal'], $validated['tgl_akhir'])
            : (float) $validated['lembur'];

        $potonganTelat = $request->filled('potongan_telat')
            ? (float) $validated['potongan_telat']
            : $this->hitungPotonganTelatOtomatis($gaji, $validated['tgl_awal'], $validated['tgl_akhir']);

        $gajiDetail = GajiDetail::create([
            'id_gaji' => $gaji->id_gaji,
            'id_admin' => $gaji->id_admin,
            'periode_bulan' => $periodeBulan,
            'tgl_awal' => $validated['tgl_awal'],
            'tgl_akhir' => $validated['tgl_akhir'],
            'jumlah_hari' => $validated['jumlah_hari'],
            'gaji_pokok' => $validated['gaji_pokok'],
            'transportasi' => $validated['transportasi'],
            'konsumsi' => 0,
            'lembur' => $lembur,
            'komisi' => $komisi,
            'pinjaman' => $validated['pinjaman'],
            'potongan_telat' => $potonganTelat,
            'dibuat_oleh' => Auth::guard('admin')->id(),
            'disetujui_oleh' => $validated['disetujui_oleh'] ?? null,
        ]);

        if ($lemburOtomatis) {
            $this->tarikLemburKeGaji($gaji->id_admin, $validated['tgl_awal'], $validated['tgl_akhir'], $gajiDetail->id_gaji_detail);
        }

        return redirect()->route('inventory.gajidetail.index')->with('success', 'Slip gaji berhasil ditambahkan.');
    }

    public function edit(GajiDetail $gajiDetail)
    {
        $this->gate();

        $pemilik = Admin::where('akses_level', 'pemilik')->where('blokir', 'N')->orderBy('nama_lengkap')->get();

        return view('inventory.gajidetail.edit', [
            'judul' => 'Inventory',
            'slip' => $gajiDetail->load(['admin', 'gaji']),
            'pemilik' => $pemilik,
        ]);
    }

    public function update(Request $request, GajiDetail $gajiDetail)
    {
        $this->gate();

        $validated = $request->validate([
            'tgl_awal' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date'],
            'jumlah_hari' => ['required', 'integer', 'min:0'],
            'gaji_pokok' => ['required', 'numeric', 'min:0'],
            'transportasi' => ['required', 'numeric', 'min:0'],
            'pinjaman' => ['required', 'numeric', 'min:0'],
            'lembur' => ['nullable', 'numeric', 'min:0'],
            'komisi' => ['nullable', 'numeric', 'min:0'],
            'potongan_telat' => ['nullable', 'numeric', 'min:0'],
            'disetujui_oleh' => ['nullable', 'integer', 'exists:admin,id_admin'],
        ]);

        $admin = $gajiDetail->admin;
        $komisi = $request->filled('komisi')
            ? (float) $validated['komisi']
            : $this->hitungKomisiOtomatis($gajiDetail->id_admin, $admin->nama_lengkap ?? '', $validated['tgl_awal'], $validated['tgl_akhir']);

        $lemburOtomatis = !$request->filled('lembur');
        $lembur = $lemburOtomatis
            ? $this->hitungLemburOtomatis($gajiDetail->gaji, $validated['tgl_awal'], $validated['tgl_akhir'])
            : (float) $validated['lembur'];

        $potonganTelat = $request->filled('potongan_telat')
            ? (float) $validated['potongan_telat']
            : $this->hitungPotonganTelatOtomatis($gajiDetail->gaji, $validated['tgl_awal'], $validated['tgl_akhir']);

        $gajiDetail->update([
            'tgl_awal' => $validated['tgl_awal'],
            'tgl_akhir' => $validated['tgl_akhir'],
            'jumlah_hari' => $validated['jumlah_hari'],
            'gaji_pokok' => $validated['gaji_pokok'],
            'transportasi' => $validated['transportasi'],
            'konsumsi' => 0,
            'lembur' => $lembur,
            'komisi' => $komisi,
            'pinjaman' => $validated['pinjaman'],
            'potongan_telat' => $potonganTelat,
            'disetujui_oleh' => $validated['disetujui_oleh'] ?? null,
        ]);

        if ($lemburOtomatis) {
            $this->tarikLemburKeGaji($gajiDetail->id_admin, $validated['tgl_awal'], $validated['tgl_akhir'], $gajiDetail->id_gaji_detail);
        }

        return redirect()->route('inventory.gajidetail.index')->with('success', 'Slip gaji berhasil diperbarui.');
    }

    public function destroy(GajiDetail $gajiDetail)
    {
        $this->gate();

        $gajiDetail->delete();

        return redirect()->route('inventory.gajidetail.index')->with('success', 'Slip gaji berhasil dihapus.');
    }

    /**
     * Cetak Slip Gaji -- browser-print HTML A5 landscape (bukan FPDF), mengikuti
     * konvensi print di seluruh port ini.
     */
    public function cetak(GajiDetail $gajiDetail)
    {
        $this->gate();

        $gajiDetail->load('admin');

        return view('inventory.gajidetail.cetak', [
            'slip' => $gajiDetail,
            'setheader' => Setheader::first(),
        ]);
    }

    /**
     * JSON ringan untuk auto-isi field Jumlah Hari di form Tambah/Ubah Slip Gaji
     * (dipanggil AJAX saat karyawan/tanggal berubah) -- lihat hitungRincianKehadiran()
     * untuk rincian penuhnya.
     */
    public function jumlahHariOtomatis(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'tgl_awal' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date'],
        ]);

        $rincian = $this->hitungRincianKehadiran($validated['id_admin'], $validated['tgl_awal'], $validated['tgl_akhir']);

        return response()->json(['jumlah_hari' => $rincian['jumlah_hari']]);
    }

    /**
     * Halaman rincian kehadiran harian (hadir/cuti/tidak hadir + lembur per tanggal)
     * untuk 1 karyawan pada 1 rentang tanggal -- tombol "Detail" di Tambah/Ubah/index
     * Slip Gaji, supaya Jumlah Hari & Lembur otomatis bisa dikoreksi bersama pemilik
     * & karyawan (bukan cuma angka akhir).
     */
    public function rincianKehadiran(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'id_admin' => ['required', 'integer', 'exists:admin,id_admin'],
            'tgl_awal' => ['required', 'date'],
            'tgl_akhir' => ['required', 'date'],
        ]);

        $admin = Admin::findOrFail($validated['id_admin']);
        $rincian = $this->hitungRincianKehadiran($validated['id_admin'], $validated['tgl_awal'], $validated['tgl_akhir']);

        return view('inventory.gajidetail.rincian', [
            'judul' => 'Inventory',
            'admin' => $admin,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rincian' => $rincian,
        ]);
    }

    /**
     * Jumlah Hari = jumlah tanggal (dalam rentang) yang punya minimal satu pasangan
     * checkin/checkout LENGKAP, ATAU dicakup cuti berstatus disetujui -- satu tanggal
     * dengan 2+ pasangan lengkap (multi-shift) tetap dihitung 1x (shift ke-2 dst
     * dianggap lembur, bukan hari tambahan, lihat InventoryKehadiranController::checkout()).
     * Hari tanpa pasangan lengkap & tanpa cuti disetujui tidak menyumbang apa pun --
     * sistem ini tidak membedakan "libur terjadwal" dari "alpha", keduanya sama-sama
     * tidak dibayar harian.
     *
     * @return array{hari: array<int, array>, jumlah_hari: int,
     *     total_lembur_disetujui_jam: float, total_lembur_diajukan_jam: float,
     *     total_potongan_telat: float, total_nilai: float}
     */
    private function hitungRincianKehadiran(int $idAdmin, string $tglAwal, string $tglAkhir): array
    {
        $gaji = Gaji::where('id_admin', $idAdmin)->first();
        $gajiHarian = (float) ($gaji->gaji_harian ?? 0);
        $transportasiHarian = (float) ($gaji->transportasi_harian ?? 0);
        $rateLembur = (float) ($gaji->rate_lembur ?? 0);

        $absensiPerTanggal = Absensi::with(['shift', 'lembur'])
            ->where('id_admin', $idAdmin)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->get()
            ->groupBy(fn ($a) => $a->tanggal->toDateString());

        $cutiList = Cuti::where('id_admin', $idAdmin)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tglAkhir)
            ->where('tanggal_selesai', '>=', $tglAwal)
            ->get();

        $adaCutiPada = function (string $tanggal) use ($cutiList): bool {
            return $cutiList->contains(fn ($c) => $tanggal >= $c->tanggal_mulai->toDateString() && $tanggal <= $c->tanggal_selesai->toDateString());
        };

        $hari = [];
        $jumlahHari = 0;

        foreach (CarbonPeriod::create($tglAwal, $tglAkhir) as $tanggalCarbon) {
            $tanggal = $tanggalCarbon->toDateString();
            $absensiHariIni = $absensiPerTanggal->get($tanggal, collect());
            $pasanganLengkap = $absensiHariIni->filter(fn ($a) => $a->jam_masuk && $a->jam_pulang)->sortBy('jam_masuk')->values();
            $cutiHariIni = $adaCutiPada($tanggal);

            if ($pasanganLengkap->isNotEmpty()) {
                $status = 'Hadir';
                $kontribusi = 1;
            } elseif ($cutiHariIni) {
                $status = 'Cuti (Disetujui)';
                $kontribusi = 1;
            } else {
                $status = 'Tidak Hadir';
                $kontribusi = 0;
            }

            $jumlahHari += $kontribusi;

            $shiftList = $pasanganLengkap->map(function ($a, $i) use ($gaji) {
                $menitTerlambat = Absensi::hitungMenitTerlambat($a->shift, $a->jam_masuk);

                return [
                    'nama_shift' => $a->shift->nama_shift ?? '-',
                    'jam_masuk' => $a->jam_masuk,
                    'jam_pulang' => $a->jam_pulang,
                    'ekstra' => $i > 0,
                    'jam_lembur' => $a->lembur->jam_lembur ?? null,
                    'tipe_lembur' => $a->lembur->tipe ?? null,
                    'status_approval_lembur' => $a->lembur->status_approval ?? null,
                    'menit_terlambat' => $menitTerlambat,
                    'potongan_telat' => $gaji ? $this->potonganUntukMenit($gaji, $menitTerlambat) : 0,
                ];
            })->values()->all();

            // Nilai (Rupiah) hari ini = (gaji harian + transportasi, kalau Hadir/Cuti)
            // + lembur yang disetujui ATAU masih diajukan (per_jam: jam x rate_lembur;
            // per_shift: flat 1x rate_lembur/"Tarif Lembur 1 Shift", tidak dikali jam)
            // - potongan keterlambatan
            // (mekanis dari jam_masuk vs jadwal, tidak lewat approval). Halaman ini untuk
            // DITINJAU bersama SEBELUM/menjelang persetujuan, jadi lembur yang masih
            // diajukan tetap dihitung di sini (badge di kolom Lembur tetap menunjukkan
            // status aslinya) -- ditolak saja yang tidak ikut dinilai. Slip Gaji
            // SUNGGUHAN tetap hanya membayar yang sudah disetujui pemilik (lihat
            // hitungLemburOtomatis()), jadi angka final baru pasti setelah disetujui.
            $nilaiLemburHariIni = array_sum(array_map(function ($s) use ($rateLembur) {
                if ($s['status_approval_lembur'] === 'ditolak' || $s['status_approval_lembur'] === null) {
                    return 0;
                }

                // per_shift: flat 1x "Tarif Lembur 1 Shift" (gaji.rate_lembur), TIDAK
                // dikali jam kerja. per_jam: cuma dari lembur manual (jam x rate_lembur,
                // arti aslinya) -- checkout() tidak lagi otomatis membuat baris per_jam.
                return $s['tipe_lembur'] === 'per_shift' ? $rateLembur : ($s['jam_lembur'] * $rateLembur);
            }, $shiftList));

            $potonganTelatHariIni = array_sum(array_column($shiftList, 'potongan_telat'));

            $nilai = ($kontribusi * ($gajiHarian + $transportasiHarian)) + $nilaiLemburHariIni - $potonganTelatHariIni;

            $hari[] = [
                'tanggal' => $tanggal,
                'status' => $status,
                'kontribusi_hari' => $kontribusi,
                'shift' => $shiftList,
                'potongan_telat' => $potonganTelatHariIni,
                'nilai' => $nilai,
            ];
        }

        $totalLemburDisetujui = (float) Lembur::where('id_admin', $idAdmin)
            ->where('status_approval', 'disetujui')
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->sum('jam_lembur');

        $totalLemburDiajukan = (float) Lembur::where('id_admin', $idAdmin)
            ->where('status_approval', 'diajukan')
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->sum('jam_lembur');

        return [
            'hari' => $hari,
            'jumlah_hari' => $jumlahHari,
            'total_lembur_disetujui_jam' => $totalLemburDisetujui,
            'total_lembur_diajukan_jam' => $totalLemburDiajukan,
            'total_potongan_telat' => array_sum(array_column($hari, 'potongan_telat')),
            'total_nilai' => array_sum(array_column($hari, 'nilai')),
        ];
    }

    private function hitungKomisiOtomatis(int $idAdmin, string $namaLengkap, string $tglAwal, string $tglAkhir): float
    {
        $komisiProduk = (float) TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->where('trkasir_detail.idadmin', $idAdmin)
            ->whereBetween('trkasir.tgl_trkasir', [$tglAwal, $tglAkhir])
            ->sum('trkasir_detail.komisi');

        $rateGlobal = (float) (KomisiGlobal::where('status', 'ON')->value('nilai') ?? 0) / 100;

        $totalPenjualan = (float) DB::table('trkasir')
            ->whereBetween('tgl_trkasir', [$tglAwal, $tglAkhir])
            ->where('petugas', $namaLengkap)
            ->sum('ttl_trkasir');

        $komisiGlobal = round($totalPenjualan * $rateGlobal);

        return $komisiProduk + $komisiGlobal;
    }

    /**
     * per_jam (cuma dari input manual pemilik sekarang) = jam x rate_lembur.
     * per_shift (otomatis dari shift tambahan/ke-2+ di hari yang sama) = flat
     * 1x rate_lembur ("Tarif Lembur 1 Shift") per baris, TIDAK dikali jam kerja
     * aktualnya -- lihat InventoryKehadiranController::checkout().
     */
    private function hitungLemburOtomatis(Gaji $gaji, string $tglAwal, string $tglAkhir): float
    {
        $totalJamPerJam = (float) Lembur::where('id_admin', $gaji->id_admin)
            ->where('tipe', 'per_jam')
            ->where('status_approval', 'disetujui')
            ->where('ditarik_ke_gaji', false)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->sum('jam_lembur');

        $jumlahShiftTambahan = Lembur::where('id_admin', $gaji->id_admin)
            ->where('tipe', 'per_shift')
            ->where('status_approval', 'disetujui')
            ->where('ditarik_ke_gaji', false)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->count();

        return round($totalJamPerJam * (float) $gaji->rate_lembur) + round($jumlahShiftTambahan * (float) $gaji->rate_lembur);
    }

    private function tarikLemburKeGaji(int $idAdmin, string $tglAwal, string $tglAkhir, int $idGajiDetail): void
    {
        Lembur::where('id_admin', $idAdmin)
            ->where('status_approval', 'disetujui')
            ->where('ditarik_ke_gaji', false)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->update(['ditarik_ke_gaji' => true, 'id_gaji_detail' => $idGajiDetail]);
    }

    /**
     * Potongan keterlambatan berjenjang, dihitung langsung dari Absensi periode itu
     * (bukan lewat approval seperti Lembur -- ini mekanis dari jam_masuk aktual vs
     * batasTerlambat() shift). Aman dihitung ulang tiap kali (tidak perlu flag
     * "sudah ditarik") karena satu tanggal cuma pernah masuk SATU periode slip
     * (periode_bulan unik per karyawan, lihat store()).
     */
    private function hitungPotonganTelatOtomatis(Gaji $gaji, string $tglAwal, string $tglAkhir): float
    {
        $absensiList = Absensi::with('shift')
            ->where('id_admin', $gaji->id_admin)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->whereNotNull('jam_masuk')
            ->get();

        $total = 0;
        foreach ($absensiList as $a) {
            $menit = Absensi::hitungMenitTerlambat($a->shift, $a->jam_masuk);
            $total += $this->potonganUntukMenit($gaji, $menit);
        }

        return $total;
    }

    private function potonganUntukMenit(Gaji $gaji, float $menit): float
    {
        if ($menit >= 60) {
            return (float) $gaji->potongan_telat_60_lebih;
        }
        if ($menit >= 30) {
            return (float) $gaji->potongan_telat_30_60;
        }
        if ($menit >= 15) {
            return (float) $gaji->potongan_telat_15_30;
        }

        return 0;
    }

    private function gate(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Anda tidak berhak mengakses halaman ini. Fitur ini khusus Pemilik.');
    }
}
