<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Gaji;
use App\Models\GajiDetail;
use App\Models\KomisiGlobal;
use App\Models\Lembur;
use App\Models\Setheader;
use App\Models\TrkasirDetail;
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
     * dikosongkan, dihitung dari SUM(lembur.jam_lembur) milik id_admin yang
     * berstatus disetujui & belum ditarik dalam rentang Tanggal Awal-Akhir,
     * dikali `gaji.rate_lembur` (Rupiah/jam) -- lalu baris-baris `lembur`
     * yang ikut dihitung ditandai ditarik_ke_gaji=true supaya tidak terhitung
     * dobel di slip periode berikutnya. Penandaan ini SENGAJA hanya jalan
     * kalau field dikosongkan (auto-calc) -- kalau pemilik mengisi manual,
     * baris `lembur` dibiarkan apa adanya karena tidak ada jaminan angka
     * manual itu benar-benar berasal dari menjumlah baris-baris tersebut.
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

    private function hitungLemburOtomatis(Gaji $gaji, string $tglAwal, string $tglAkhir): float
    {
        $totalJam = (float) Lembur::where('id_admin', $gaji->id_admin)
            ->where('status_approval', 'disetujui')
            ->where('ditarik_ke_gaji', false)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->sum('jam_lembur');

        return round($totalJam * (float) $gaji->rate_lembur);
    }

    private function tarikLemburKeGaji(int $idAdmin, string $tglAwal, string $tglAkhir, int $idGajiDetail): void
    {
        Lembur::where('id_admin', $idAdmin)
            ->where('status_approval', 'disetujui')
            ->where('ditarik_ke_gaji', false)
            ->whereBetween('tanggal', [$tglAwal, $tglAkhir])
            ->update(['ditarik_ke_gaji' => true, 'id_gaji_detail' => $idGajiDetail]);
    }

    private function gate(): void
    {
        abort_unless(Auth::guard('admin')->user()?->isPemilik(), 403, 'Anda tidak berhak mengakses halaman ini. Fitur ini khusus Pemilik.');
    }
}
