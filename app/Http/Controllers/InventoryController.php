<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryController extends Controller
{
    /**
     * Modul yang sudah punya halaman sendiri: nilai ?module=xxx yang sama seperti
     * public/apotekberlian/masuk/content_admin.php => nama route index-nya. Modul
     * lain yang dikenal (lihat Admin::PERMISSION_GROUPS) tapi belum ada di map ini
     * akan menampilkan placeholder "belum tersedia".
     */
    private const MODULE_ROUTES = [
        'admin' => 'inventory.admin.index',
        'setheader' => 'inventory.setheader.index',
        'carabayar' => 'inventory.carabayar.index',
        'pelanggan' => 'inventory.pelanggan.index',
        'supplier' => 'inventory.supplier.index',
        'satuan' => 'inventory.satuan.index',
        'jenisobat' => 'inventory.jenisobat.index',
        'barang' => 'inventory.barang.index',
        'komisi' => 'inventory.komisi.index',
        'mstok' => 'inventory.mstok.index',
        'stok_kritis' => 'inventory.stok-kritis.index',
        'stokopname' => 'inventory.stokopname.index',
        'soharian' => 'inventory.soharian.index',
        'jurnalkas' => 'inventory.jurnalkas.index',
        'ujian' => 'inventory.ujian.index',
        'cekdarah' => 'inventory.cekdarah.index',
        'konseling' => 'inventory.konseling.index',
        'meso' => 'inventory.meso.index',
        'pio' => 'inventory.pio.index',
        'pto' => 'inventory.pto.index',
        'cpp' => 'inventory.cpp.index',
        'homecare' => 'inventory.homecare.index',
        'swamedikasi' => 'inventory.swamedikasi.index',
        'poin' => 'inventory.poin.index',
        'bundle' => 'inventory.bundle.index',
        'orders' => 'inventory.orders.index',
        'trbmasuk' => 'inventory.trbmasuk.index',
        'trbmasukpbf' => 'inventory.trbmasukpbf.index',
        'byrkredit' => 'inventory.byrkredit.index',
        'shiftkerja' => 'inventory.shiftkerja.index',
        'trkasir' => 'inventory.trkasir.index',
        // Nilai ?module= legacy untuk modul ini genap "penjualansebelumnya" (dengan
        // akhiran "nya") -- BEDA dari nama kolom flag admin-nya sendiri, 'penjualansebelum'
        // (tanpa akhiran) -- inkonsistensi yang sama persis ada di legacy sendiri
        // (bandingkan admin.penjualansebelum di database vs href="?module=penjualansebelumnya"
        // di media_admin.php), bukan salah ketik di port ini.
        'penjualansebelumnya' => 'inventory.penjualansebelum.index',
        'catatan' => 'inventory.catatan.index',
        'lpitem' => 'inventory.lpitem.index',
        'lpbrgmasuk' => 'inventory.lpbrgmasuk.index',
        'lpkasir' => 'inventory.lpkasir.index',
        'labapenjualan' => 'inventory.labapenjualan.index',
        'labajenisobat' => 'inventory.labajenisobat.index',
        'neraca' => 'inventory.neraca.index',
        'lapstokopname' => 'inventory.lapstokopname.index',
        'lapkomisi' => 'inventory.lapkomisi.index',
        'evaluasi' => 'inventory.evaluasi.index',
        'gaji' => 'inventory.gaji.index',
        'gajidetail' => 'inventory.gajidetail.index',
        'rekap_gaji' => 'inventory.rekapgaji.index',
        'kehadiran' => 'inventory.kehadiran.index',
    ];

    /**
     * Modul yang dipanggil dari dropdown Aksi pelanggan (lihat
     * public/apotekberlian/masuk/modul/mod_pelanggan/pelanggan_serverside.php)
     * tapi TIDAK digerbang oleh flag kolom admin manapun di legacy (semua admin
     * yang login bisa buka) dan belum dibuatkan halamannya di Laravel. Modul
     * ini ditampilkan sebagai placeholder, bukan "akses ditolak".
     */
    private const UNGATED_MODULES = [];

    /**
     * Dispatcher halaman inventory berbasis ?module=xxx, mengikuti skema
     * public/apotekberlian/masuk/content_admin.php. module=home menampilkan
     * dashboard; module lain yang sudah dikenali (lihat Admin::PERMISSION_GROUPS)
     * tapi belum dibuatkan halamannya menampilkan placeholder "belum tersedia".
     */
    public function index(Request $request)
    {
        $module = $request->query('module', 'home');

        if ($module === 'home') {
            return $this->dashboard();
        }

        if (array_key_exists($module, self::MODULE_ROUTES)) {
            return redirect()->route(self::MODULE_ROUTES[$module]);
        }

        if (array_key_exists($module, self::UNGATED_MODULES)) {
            return view('inventory.placeholder', [
                'judul' => 'Inventory',
                'module' => $module,
                'label' => self::UNGATED_MODULES[$module],
            ]);
        }

        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->hasModuleAccess($module)) {
            return view('inventory.unauthorized', [
                'judul' => 'Inventory',
            ]);
        }

        return view('inventory.placeholder', [
            'judul' => 'Inventory',
            'module' => $module,
            'label' => $this->moduleLabel($module),
        ]);
    }

    private function dashboard()
    {
        return view('inventory.dashboard', [
            'judul' => 'Inventory',
        ]);
    }

    /**
     * Data grafik penjualan/swamedikasi untuk dashboard, mengikuti persis logika
     * public/apotekberlian/masuk/ajax_penjualan_realtime.php: total per hari pada
     * bulan yang dipilih dibandingkan dengan bulan sebelumnya (disejajarkan
     * berdasarkan tanggal-dalam-bulan, bukan tanggal kalender penuh).
     *
     * - Penjualan: SUM(ttl_trkasir) dari tabel trkasir, dikelompokkan per tgl_trkasir.
     * - Swamedikasi: COUNT(*) dari tabel riwayat_pelanggan, dikelompokkan per tgl.
     *
     * "Bulan Dipilih" dibatasi sampai hari ini kalau bulan yang dipilih adalah
     * bulan berjalan (biar tidak menampilkan hari-hari yang belum terjadi
     * sebagai 0 dan mendistorsi grafik/total), TAPI "Bulan Lalu" SELALU bulan
     * penuh -- lihat komentar di titik pembentukan $dataBulanLalu di bawah.
     */
    public function salesChartData(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $tipe = $request->query('tipe') === 'swamedikasi' ? 'swamedikasi' : 'penjualan';

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = now()->year;
        }

        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhirBulanPenuh = $awalBulan->copy()->endOfMonth()->startOfDay();
        $akhirPeriode = ($bulan === now()->month && $tahun === now()->year)
            ? now()->startOfDay()
            : $akhirBulanPenuh;

        $awalBulanSebelumnya = $awalBulan->copy()->subMonthNoOverflow()->startOfMonth();
        $akhirBulanSebelumnya = $awalBulanSebelumnya->copy()->endOfMonth()->startOfDay();

        if ($tipe === 'swamedikasi') {
            $judulTipe = 'Swamedikasi';
            $format = 'angka';

            if (!Schema::hasTable('riwayat_pelanggan')) {
                return response()->json(['status' => false, 'message' => 'Tabel riwayat_pelanggan belum tersedia'], 500);
            }

            $rows = DB::table('riwayat_pelanggan')
                ->selectRaw('tgl as tanggal, COUNT(*) as total_nilai')
                ->whereBetween('tgl', [$awalBulan->toDateString(), $akhirPeriode->toDateString()])
                ->groupBy('tgl')
                ->orderBy('tgl')
                ->get();

            $rowsPrev = DB::table('riwayat_pelanggan')
                ->selectRaw('tgl as tanggal, COUNT(*) as total_nilai')
                ->whereBetween('tgl', [$awalBulanSebelumnya->toDateString(), $akhirBulanSebelumnya->toDateString()])
                ->groupBy('tgl')
                ->orderBy('tgl')
                ->get();
        } else {
            $judulTipe = 'Penjualan';
            $format = 'rupiah';

            $rows = DB::table('trkasir')
                ->selectRaw('tgl_trkasir as tanggal, SUM(ttl_trkasir) as total_nilai')
                ->whereBetween('tgl_trkasir', [$awalBulan->toDateString(), $akhirPeriode->toDateString()])
                ->groupBy('tgl_trkasir')
                ->orderBy('tgl_trkasir')
                ->get();

            $rowsPrev = DB::table('trkasir')
                ->selectRaw('tgl_trkasir as tanggal, SUM(ttl_trkasir) as total_nilai')
                ->whereBetween('tgl_trkasir', [$awalBulanSebelumnya->toDateString(), $akhirBulanSebelumnya->toDateString()])
                ->groupBy('tgl_trkasir')
                ->orderBy('tgl_trkasir')
                ->get();
        }

        $mapNilai = [];
        foreach ($rows as $row) {
            $mapNilai[(string) $row->tanggal] = (float) $row->total_nilai;
        }

        $mapNilaiPrevByHari = [];
        foreach ($rowsPrev as $row) {
            $hari = Carbon::parse($row->tanggal)->format('d');
            $mapNilaiPrevByHari[$hari] = (float) $row->total_nilai;
        }

        $dataHarian = [];
        $cursor = $awalBulan->copy();
        while ($cursor->lte($akhirPeriode)) {
            $dataHarian[] = [
                'tanggal' => $cursor->toDateString(),
                'nilai' => $mapNilai[$cursor->toDateString()] ?? 0,
            ];

            $cursor->addDay();
        }

        // "Bulan Lalu" SENGAJA memakai rentang bulan sebelumnya PENUH (bukan
        // dibatasi $akhirPeriode, yang hanya sampai "hari ini" kalau bulan yang
        // dipilih adalah bulan berjalan) -- kalau ikut dibatasi, total & grafik
        // "Bulan Lalu" cuma menjumlah tanggal 1 s.d. hari-ini-bulan-lalu,
        // membuat "Total Bulan Lalu" jauh lebih kecil dari total bulan itu yang
        // sebenarnya (bug yang dilaporkan user 2026-09-17: bulan lalu tampil
        // Rp 5.000, padahal totalnya yang benar Rp 2.468.500 kalau bulan itu
        // dipilih langsung). Perbandingan "Bulan Lalu" harus selalu bulan penuh.
        $dataBulanLalu = [];
        $cursorPrev = $awalBulanSebelumnya->copy();
        while ($cursorPrev->lte($akhirBulanSebelumnya)) {
            $dataBulanLalu[] = [
                'hari' => $cursorPrev->format('d'),
                'nilai' => $mapNilaiPrevByHari[$cursorPrev->format('d')] ?? 0,
            ];

            $cursorPrev->addDay();
        }

        return response()->json([
            'status' => true,
            'tipe' => $tipe,
            'judul_tipe' => $judulTipe,
            'format' => $format,
            'data' => $dataHarian,
            'data_bulan_lalu' => $dataBulanLalu,
            'periode_label' => $awalBulan->format('F Y'),
            'periode_sebelumnya_label' => $awalBulanSebelumnya->format('F Y'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function moduleLabel(string $module): string
    {
        foreach (Admin::PERMISSION_GROUPS as $items) {
            if (array_key_exists($module, $items)) {
                return $items[$module];
            }
        }

        return ucfirst($module);
    }
}
