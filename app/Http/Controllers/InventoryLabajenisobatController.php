<?php

namespace App\Http\Controllers;

use App\Models\JenisPenjualan;
use App\Models\Trkasir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLabajenisobatController extends Controller
{
    /**
     * Modul "Laporan > Detail Jenis Penjualan" (grup Laporan, flag admin
     * `labajenisobat`, sidebar label diganti dari "Jenis Penjualan" -> "Laba Jenis
     * Penjualan" -> akhirnya "Detail Jenis Penjualan" atas permintaan user, dua kali
     * revisi di hari yang sama), mengikuti mod_lappenjualan/labapenjualanjenisobat.php
     * (form filter) + mod_laporan/tampil_jenispenjualan.php (cetak PDF, FPDF asli)
     * + mod_laporan/lapjenispenjualan_excel.php (export Excel). Gerbang flag-only,
     * sama seperti Laba Penjualan (tidak ada syarat role tambahan di legacy).
     *
     * **Filter "Shift Petugas" di form legacy sebenarnya filter JENIS TRANSAKSI**
     * (`trkasir_detail.tipe`), bukan shift kerja kasir -- nama field legacy
     * menyesatkan tapi diikuti (dropdown-nya sendiri berlabel "Shift Petugas" berisi
     * Reguler/Resep/Nakes/SEMUA). Nilai 1=Reguler/2=Resep/3=Nakes/7=SEMUA (SEMUA
     * di-resolve legacy ke tipe 1-6, bukan cuma 1-3 -- diikuti apa adanya meski
     * dropdown hanya punya opsi 1/2/3, karena begitu literalnya kode aslinya).
     * Data produksi saat ini hanya berisi `tipe` 0 dan 1 (nilai 2-6 tidak pernah
     * dipakai) dan tabel `jenispenjualan` sendiri sedang KOSONG (0 baris) --
     * makanya label "Jenis Penjualan : ..." di PDF/Excel akan tampil kosong untuk
     * semua pilihan sampai tabel itu diisi data oleh user. Ini gap data, bukan bug
     * porting -- diikuti apa adanya, sama seperti perlakuan gap `trkasir.petugas`
     * vs `admin.nama_lengkap` di modul Evaluasi/Komisi.
     *
     * **N+1 dihilangkan, 2 tempat:**
     * (1) `tampil_jenispenjualan.php` menjalankan 2 query SUM(hrgttl_dtrkasir)
     * terpisah PER TRANSAKSI di dalam loop (`$detailsub` dan `$detail2`) yang
     * secara harfiah identik satu sama lain (sama-sama `SUM(hrgttl_dtrkasir) WHERE
     * kd_trkasir=...`, tidak difilter `tipe`) -- diganti satu penjumlahan di PHP
     * dari baris detail yang sudah di-eager-load (`$trx->detail`), tidak perlu
     * query ulang sama sekali.
     * (2) `lapjenispenjualan_excel.php` menjalankan `SELECT * FROM jenispenjualan
     * WHERE id_penjualan=...` PER BARIS di dalam loop hasil -- diganti satu
     * `pluck()` di awal (peta id_penjualan => nm_penjualan), dipakai lewat array
     * lookup di PHP untuk semua baris.
     *
     * **Bug kosmetik diperbaiki, tidak direplikasi** (sama persis dengan yang
     * sudah diperbaiki di Laba Penjualan): kolom "Harga" PDF legacy menjumlahkan
     * `hrgjual_dtrkasir + disc` (harga rupiah + persentase diskon `int(2)`) --
     * hasil tidak bermakna, tidak memengaruhi angka Sub Total/Total manapun (semua
     * dibaca dari `hrgttl_dtrkasir` yang sudah tersimpan). Diperbaiki ke
     * `hrgjual_dtrkasir` apa adanya.
     *
     * **Total keseluruhan PDF vs Excel punya cakupan berbeda, diikuti apa adanya
     * (bukan bug):** total per-transaksi di PDF menjumlahkan SEMUA baris detail
     * transaksi yang lolos filter (transaksi lolos jika py PUNYA minimal satu
     * baris `tipe` yang cocok, tapi semua barisnya tetap ditampilkan/dijumlah
     * tanpa filter tipe lagi) -- sedangkan grand total di baris terakhir (baik PDF
     * "TOTAL PENJUALAN" maupun Excel "Total Penjualan") HANYA menjumlahkan baris
     * detail yang `tipe`-nya sendiri cocok filter, lintas semua transaksi. Kedua
     * query legacy memang berbeda cakupan ini, byte-identik dengan yang di-porting
     * di sini -- dihitung ulang dari koleksi eager-loaded yang sama (tidak perlu
     * query SUM terpisah) karena setiap baris yang cocok filter sudah pasti masuk
     * lewat transaksi yang lolos `whereHas`.
     */
    public function index()
    {
        return view('inventory.labajenisobat.index', ['judul' => 'Inventory']);
    }

    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'tipe' => 'required|integer',
        ]);

        $tipeArr = $this->resolveTipeArray((int) $validated['tipe']);
        $tipeLabel = optional(JenisPenjualan::find($validated['tipe']))->nm_penjualan ?? '';

        $transaksi = Trkasir::whereHas('detail', function ($q) use ($tipeArr) {
                $q->whereIn('tipe', $tipeArr);
            })
            ->whereBetween('tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->with('detail')
            ->orderBy('kd_trkasir')
            ->get();

        $pdf = $this->buildCetakPdf($transaksi, $tipeArr, $tipeLabel, $validated['tgl_awal'], $validated['tgl_akhir']);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan-Jenis-Penjualan.pdf"',
        ]);
    }

    private function buildCetakPdf($transaksi, array $tipeArr, string $tipeLabel, string $tglAwal, string $tglAkhir): \FPDF
    {
        $pdf = new \FPDF('P', 'cm', 'A4');
        $pdf->SetMargins(1, 1, 1);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $admin = Auth::guard('admin')->user();

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25.5, 0.7, 'LAPORAN TRANSAKSI BERDASARKAN JENIS PENJUALAN', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(5.5, 0.5, 'Tanggal Cetak : ' . now()->format('d-m-Y H:i:s'), 0, 0, 'L');
        $pdf->Cell(5, 0.5, 'Dicetak Oleh : ' . $admin->nama_lengkap, 0, 1, 'L');
        $pdf->Cell(5.5, 0.5, 'Periode : ' . $this->tglIndo($tglAwal) . ' - ' . $this->tglIndo($tglAkhir), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(5.5, 0.5, 'Jenis Penjualan : ' . $tipeLabel, 0, 1, 'L');

        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', '', 9);

        $no = 1;
        $totalSemua = 0;

        foreach ($transaksi as $trx) {
            $hrgdsr = $trx->detail->sum('hrgttl_dtrkasir');
            $diskon = $hrgdsr - $trx->ttl_trkasir;

            $pdf->Cell(3, 0.4, 'No', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, (string) $no, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Nama Pelanggan', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $trx->nm_pelanggan . '                            Telp : ' . $trx->tlp_pelanggan, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Kode Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $trx->kd_trkasir, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Diskon', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $this->formatRupiah($diskon), 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Nilai Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $this->formatRupiah($trx->ttl_trkasir), 0, 1, 'L');

            $no++;

            $pdf->Cell(1, 0.7, 'No', 1, 0, 'C');
            $pdf->Cell(9.5, 0.7, 'Nama Barang', 1, 0, 'C');
            $pdf->Cell(1, 0.7, 'Jml', 1, 0, 'C');
            $pdf->Cell(1.5, 0.7, 'Sat', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Harga', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Disc', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Sub Total', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 8);

            $no2 = 1;
            foreach ($trx->detail->sortBy('nmbrg_dtrkasir') as $det) {
                $pdf->Cell(1, 0.6, (string) $no2, 1, 0, 'C');
                $pdf->Cell(9.5, 0.6, $det->nmbrg_dtrkasir, 1, 0, 'L');
                $pdf->Cell(1, 0.6, (string) $det->qty_dtrkasir, 1, 0, 'C');
                $pdf->Cell(1.5, 0.6, (string) $det->sat_dtrkasir, 1, 0, 'C');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->hrgjual_dtrkasir), 1, 0, 'R');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->disc), 1, 0, 'R');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->hrgttl_dtrkasir), 1, 1, 'R');
                $no2++;
            }

            $pdf->Cell(1, 0.6, '', 0, 0, 'C');
            $pdf->Cell(9.5, 0.6, '', 0, 0, 'L');
            $pdf->Cell(1, 0.6, '', 0, 0, 'C');
            $pdf->Cell(1.5, 0.6, '', 0, 0, 'C');
            $pdf->Cell(2, 0.6, '', 0, 0, 'R');
            $pdf->Cell(2, 0.6, 'TOTAL', 1, 0, 'C');
            $pdf->Cell(2, 0.6, $this->formatRupiah($hrgdsr), 1, 1, 'R');

            $pdf->Cell(2, 0.7, '', 0, 1, 'C');

            $totalSemua += $trx->detail->whereIn('tipe', $tipeArr)->sum('hrgttl_dtrkasir');
        }

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(4, 0.4, 'TOTAL PENJUALAN', 0, 0, 'L');
        $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
        $pdf->Cell(5, 0.4, 'Rp. ' . $this->formatRupiah($totalSemua), 0, 1, 'L');

        return $pdf;
    }

    /**
     * Export Excel -- kolom & total mengikuti lapjenispenjualan_excel.php persis,
     * lookup nama jenis penjualan di-preload sekali (bukan per baris).
     */
    public function excel(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'tipe' => 'required|integer',
        ]);

        $tipeArr = $this->resolveTipeArray((int) $validated['tipe']);
        $namaMap = JenisPenjualan::pluck('nm_penjualan', 'id_penjualan');

        $transaksi = Trkasir::whereHas('detail', function ($q) use ($tipeArr) {
                $q->whereIn('tipe', $tipeArr);
            })
            ->whereBetween('tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->with('detail')
            ->orderBy('kd_trkasir')
            ->get();

        $rows = $transaksi->map(function ($trx) use ($tipeArr, $namaMap) {
            $tipeCocok = $trx->detail->whereIn('tipe', $tipeArr)->first();

            return (object) [
                'tgl_trkasir' => $trx->tgl_trkasir,
                'kd_trkasir' => $trx->kd_trkasir,
                'petugas' => $trx->petugas,
                'nm_pelanggan' => $trx->nm_pelanggan,
                'kodetx' => $trx->kodetx,
                'ttl_trkasir' => $trx->ttl_trkasir,
                'jenis_transaksi' => $tipeCocok ? ($namaMap[$tipeCocok->tipe] ?? '') : '',
            ];
        });

        $totalSemua = $transaksi->sum(fn ($trx) => $trx->detail->whereIn('tipe', $tipeArr)->sum('hrgttl_dtrkasir'));

        return response()->view('inventory.labajenisobat.excel', [
            'rows' => $rows,
            'totalSemua' => $totalSemua,
            'tipeLabel' => optional(JenisPenjualan::find($validated['tipe']))->nm_penjualan ?? '',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="laporanjenispenjualan.xls"',
        ]);
    }

    /** @return int[] */
    private function resolveTipeArray(int $tipe): array
    {
        return $tipe < 7 ? [$tipe] : [1, 2, 3, 4, 5, 6];
    }

    private function tglIndo(string $tanggal): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return substr($tanggal, 8, 2) . ' ' . ($bulan[(int) substr($tanggal, 5, 2)] ?? '') . ' ' . substr($tanggal, 0, 4);
    }

    private function formatRupiah($angka): string
    {
        if ($angka === null || $angka === '') {
            return '0';
        }

        return number_format((float) $angka, 0, ',', '.');
    }
}
