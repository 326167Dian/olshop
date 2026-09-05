<?php

namespace App\Http\Controllers;

use App\Models\Trkasir;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLabapenjualanController extends Controller
{
    /**
     * Modul "Laba Penjualan" (grup Laporan, flag admin `labapenjualan`), mengikuti
     * mod_lappenjualan/labapenjualan.php (form filter) + mod_laporan/tampil_labapenjualan.php
     * (cetak PDF, FPDF asli -- beda dari lappenjualan.php yang act=view-nya HTML biasa)
     * + mod_lappenjualan/laplabapenjualan_excel.php (export Excel). Laba per baris
     * dibaca langsung dari trkasir_detail.profit (sudah dihitung saat barang
     * ditambahkan ke keranjang di modul Penjualan/Kasir, lihat komentar profit di
     * InventoryTrkasirController) -- laporan ini murni membaca ulang, tidak menghitung
     * ulang harga modal/jual dari awal.
     *
     * Filter shift PDF: 1=Pagi/2=Sore/3=Malam/0=Semua Shift (nilai sentinel "Semua"
     * BEDA dari lappenjualan yang pakai 4 -- masing-masing modul legacy punya
     * konvensinya sendiri, diikuti apa adanya per modul, bukan diseragamkan).
     * **Export Excel TIDAK punya filter shift sama sekali** (legacy sendiri begitu --
     * `laplabapenjualan_excel.php` hanya menerima tgl_awal/tgl_akhir, JS
     * `exportExcel()`-nya juga tidak pernah mengirim shift) -- selalu mengagregasi
     * SEMUA shift pada rentang tanggal, diikuti apa adanya, bukan gap yang perlu
     * "diaktifkan" seperti kasus supplier filter di lpbrgmasuk (di sana field-nya ADA
     * di HTML tapi dikomentari; di sini field shift memang tidak pernah ada sama sekali
     * di form/JS Excel-nya).
     *
     * **Bug kosmetik diperbaiki, tidak direplikasi:** kolom "Harga" pada PDF legacy
     * dihitung `$hrgawl = hrgjual_dtrkasir + disc` -- menjumlahkan harga (rupiah)
     * dengan `disc` (persentase 0-99, kolom `int(2)`), hasilnya angka yang tidak
     * bermakna apa pun (mis. 15000 + 10 = 15010). Tidak memengaruhi angka laba manapun
     * (`profit` dibaca langsung dari kolom yang sudah tersimpan, tidak diturunkan dari
     * `$hrgawl`) -- murni salah tampil di satu kolom. Diperbaiki ke `hrgjual_dtrkasir`
     * apa adanya (harga jual per unit yang sesungguhnya tersimpan).
     */
    public function index()
    {
        return view('inventory.labapenjualan.index', ['judul' => 'Inventory']);
    }

    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'shift' => 'required|integer',
        ]);

        $shiftArr = $this->resolveShiftArray((int) $validated['shift']);

        $transaksi = Trkasir::whereIn('shift', $shiftArr)
            ->whereBetween('tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->with(['detail', 'caraBayar'])
            ->orderBy('id_carabayar')
            ->orderBy('kd_trkasir')
            ->get();

        $pdf = $this->buildCetakPdf($transaksi, $validated['tgl_awal'], $validated['tgl_akhir']);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan-Laba-Penjualan.pdf"',
        ]);
    }

    private function buildCetakPdf($transaksi, string $tglAwal, string $tglAkhir): \FPDF
    {
        $pdf = new \FPDF('P', 'cm', 'A4');
        $pdf->SetMargins(1, 1, 1);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $admin = Auth::guard('admin')->user();

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25.5, 0.7, 'LAPORAN LABA PENJUALAN', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(5.5, 0.5, 'Tanggal Cetak : ' . now()->format('d-m-Y H:i:s'), 0, 0, 'L');
        $pdf->Cell(5, 0.5, 'Dicetak Oleh : ' . $admin->nama_lengkap, 0, 1, 'L');
        $pdf->Cell(5.5, 0.5, 'Periode : ' . $this->tglIndo($tglAwal) . ' - ' . $this->tglIndo($tglAkhir), 0, 1, 'L');

        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', '', 9);

        $no = 1;
        $totalNilaiTransaksi = 0;
        $totalLaba = 0;

        foreach ($transaksi as $trx) {
            $totalNilaiTransaksi += (float) $trx->ttl_trkasir;

            $pdf->Cell(3, 0.4, 'No', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, (string) $no, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Nama Pelanggan', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, (string) $trx->nm_pelanggan, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Kode Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $trx->kd_trkasir, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Metode Bayar', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, (string) optional($trx->caraBayar)->nm_carabayar, 0, 1, 'L');

            $pdf->Cell(3, 0.4, 'Total Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0.4, ': ', 0, 0, 'L');
            $pdf->Cell(5, 0.4, $this->formatRupiah($trx->ttl_trkasir), 0, 1, 'L');

            $pdf->Cell(1, 0.7, 'No', 1, 0, 'C');
            $pdf->Cell(9.5, 0.7, 'Nama Barang', 1, 0, 'C');
            $pdf->Cell(1, 0.7, 'Jml', 1, 0, 'C');
            $pdf->Cell(1.5, 0.7, 'Sat', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Harga', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Modal', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Sub Total', 1, 1, 'C');
            $pdf->SetFont('Arial', '', 8);

            $sumProfit = 0;
            $sumHrgTtl = 0;
            $no2 = 1;

            foreach ($trx->detail as $det) {
                $pdf->Cell(1, 0.6, (string) $no2, 1, 0, 'C');
                $pdf->Cell(9.5, 0.6, $det->nmbrg_dtrkasir, 1, 0, 'L');
                $pdf->Cell(1, 0.6, (string) $det->qty_dtrkasir, 1, 0, 'C');
                $pdf->Cell(1.5, 0.6, (string) $det->sat_dtrkasir, 1, 0, 'C');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->hrgjual_dtrkasir), 1, 0, 'R');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->modal), 1, 0, 'R');
                $pdf->Cell(2, 0.6, $this->formatRupiah($det->profit), 1, 1, 'R');

                $sumProfit += (float) $det->profit;
                $sumHrgTtl += (float) $det->hrgttl_dtrkasir;
                $no2++;
            }

            $diskonFaktur = $sumHrgTtl - $trx->ttl_trkasir;
            $ttlLaba = $sumProfit - $diskonFaktur;
            $totalLaba += $ttlLaba;

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(17, 0.6, 'Sub Total Profit', 1, 0, 'R');
            $pdf->Cell(2, 0.6, $this->formatRupiah($sumProfit), 1, 1, 'R');
            $pdf->Cell(17, 0.6, 'Diskon Transaksi', 1, 0, 'R');
            $pdf->Cell(2, 0.6, $this->formatRupiah($diskonFaktur), 1, 1, 'R');
            $pdf->Cell(17, 0.6, 'Subtotal Laba', 1, 0, 'R');
            $pdf->Cell(2, 0.6, $this->formatRupiah($ttlLaba), 1, 1, 'R');
            $pdf->Ln(0.3);

            $no++;
        }

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Ln(0.3);
        $pdf->Cell(6, 0.7, 'Total Nilai Transaksi', 0, 0, 'L');
        $pdf->Cell(0.5, 0.7, ': Rp. ', 0, 0, 'L');
        $pdf->Cell(5, 0.7, $this->formatRupiah($totalNilaiTransaksi), 0, 1, 'R');

        $pdf->Cell(6, 0.7, 'Total Laba', 0, 0, 'L');
        $pdf->Cell(0.5, 0.7, ': Rp. ', 0, 0, 'L');
        $pdf->Cell(5, 0.7, $this->formatRupiah($totalLaba), 0, 1, 'R');

        return $pdf;
    }

    /**
     * Export Excel -- SELALU semua shift (lihat catatan kelas), diagregasi per kd_barang
     * mengikuti GROUP BY legacy, plus ringkasan laba di baris terakhir.
     */
    public function excel(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->groupBy('trkasir_detail.kd_barang', 'trkasir_detail.nmbrg_dtrkasir', 'trkasir_detail.sat_dtrkasir')
            ->orderBy('trkasir_detail.nmbrg_dtrkasir')
            ->selectRaw('trkasir_detail.kd_barang, trkasir_detail.nmbrg_dtrkasir, trkasir_detail.sat_dtrkasir,
                SUM(trkasir_detail.qty_dtrkasir) as qty_total, SUM(trkasir_detail.hrgttl_dtrkasir) as total_harga')
            ->get();

        $totalNilaiPenjualan = (float) TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->sum('trkasir_detail.hrgttl_dtrkasir');

        $labaTanpaDiskon = (float) TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->sum('trkasir_detail.profit');

        $totalNilaiTransaksi = (float) Trkasir::whereBetween('tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])->sum('ttl_trkasir');

        $diskon = $totalNilaiPenjualan - $totalNilaiTransaksi;
        $labaSetelahDiskon = $labaTanpaDiskon - $diskon;

        return response()->view('inventory.labapenjualan.excel', [
            'rows' => $rows,
            'totalNilaiPenjualan' => $totalNilaiPenjualan,
            'diskon' => $diskon,
            'totalNilaiTransaksi' => $totalNilaiTransaksi,
            'labaTanpaDiskon' => $labaTanpaDiskon,
            'labaSetelahDiskon' => $labaSetelahDiskon,
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_data_laba_penjualan.xls"',
        ]);
    }

    /** @return int[] */
    private function resolveShiftArray(int $shift): array
    {
        return in_array($shift, [1, 2, 3], true) ? [$shift] : [1, 2, 3];
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
