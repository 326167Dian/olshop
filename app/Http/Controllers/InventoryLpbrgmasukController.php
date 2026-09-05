<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Trbmasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLpbrgmasukController extends Controller
{
    /**
     * Modul "Barang Masuk" (grup Laporan, flag admin `lpbrgmasuk`), mengikuti
     * public/apotekberlian/masuk/modul/mod_lapbrgmasuk/lapbrgmasuk.php (form filter) +
     * mod_laporan/cetak_brgmasuk.php (cetak PDF) + mod_lapbrgmasuk/barangmasuk_excel.php
     * (export Excel). BEDA dari modul transaksi Barang Masuk (`tbm`/`tbmpbf`, sudah
     * diporting) -- ini murni laporan baca-saja atas trbmasuk/trbmasuk_detail.
     *
     * Filter supplier DIAKTIFKAN di sini (keputusan pengguna 2026-09-05) -- di legacy
     * dropdown supplier sudah disiapkan di HTML tapi dikomentari, dan form filter yang
     * benar-benar tampil POST langsung ke cetak_brgmasuk.php yang memang tidak pernah
     * membaca parameter supplier sama sekali (selalu semua supplier). Ada jalur lain
     * (`act=tampil` + barangmasuk_excel.php) yang SUDAH baca parameter supplier, tapi
     * tidak tersambung ke UI mana pun (dead code). Di sini supplier jadi filter OPSIONAL
     * yang benar-benar berfungsi baik di cetak PDF maupun export Excel (dikosongkan =
     * semua supplier, sama seperti perilaku legacy yang benar-benar berjalan sekarang).
     *
     * Bug diperbaiki, tidak direplikasi: barangmasuk_excel.php legacy mengambil nama
     * supplier untuk judul laporan dengan `fetch()` SEBELUM loop utama pada result set
     * PDO yang sama -- baris pertama diam-diam hilang dari tabel karena sudah
     * "termakan" untuk judul. Di sini nama supplier diambil lewat query terpisah
     * (Supplier::find()), tidak menyentuh result set utama sama sekali.
     */
    public function index()
    {
        return view('inventory.lpbrgmasuk.index', [
            'judul' => 'Inventory',
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier']),
        ]);
    }

    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'id_supplier' => 'nullable|integer',
        ]);

        $query = Trbmasuk::whereBetween('tgl_trbmasuk', [$validated['tgl_awal'], $validated['tgl_akhir']]);
        if (!empty($validated['id_supplier'])) {
            $query->where('id_supplier', $validated['id_supplier']);
        }
        $headers = $query->get();

        $pdf = $this->buildCetakPdf($headers, $validated['tgl_awal'], $validated['tgl_akhir']);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Data-Barang-Masuk.pdf"',
        ]);
    }

    private function buildCetakPdf($headers, string $tglAwal, string $tglAkhir): \FPDF
    {
        $pdf = new \FPDF('L', 'cm', 'A4');
        $pdf->SetMargins(1, 1, 1);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $admin = Auth::guard('admin')->user();

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25.5, 0.7, 'LAPORAN DATA BARANG MASUK', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(5.5, 0.5, 'Tanggal Cetak : ' . now()->format('d-m-Y H:i:s'), 0, 0, 'L');
        $pdf->Cell(5, 0.5, 'Dicetak Oleh : ' . $admin->nama_lengkap, 0, 1, 'L');
        $pdf->Cell(5.5, 0.5, 'Periode : ' . $this->tglIndo($tglAwal) . ' - ' . $this->tglIndo($tglAkhir), 0, 0, 'L');
        $pdf->Line(1, 2.7, 28.5, 2.7);
        $pdf->Ln(1.5);

        foreach ($headers as $r1) {
            $pdf->SetX(1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(3, 0, 'Kode Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0, ':', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(1, 0, (string) $r1->kd_trbmasuk, 0, 0, 'L');

            $pdf->SetX(10);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(3, 0, 'Supplier', 0, 0, 'L');
            $pdf->Cell(0.3, 0, ':', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(1, 0, (string) $r1->nm_supplier, 0, 0, 'L');

            $pdf->Ln(0.5);

            $pdf->SetX(1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(3, 0, 'Tanggal Transaksi', 0, 0, 'L');
            $pdf->Cell(0.5, 0, ':', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(1, 0, $this->tglIndo((string) $r1->tgl_trbmasuk), 0, 0, 'L');

            $pdf->SetX(10);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(3, 0, 'Telepon', 0, 0, 'L');
            $pdf->Cell(0.3, 0, ':', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(1, 0, (string) $r1->tlp_supplier, 0, 0, 'L');

            $pdf->Ln(0.5);

            $pdf->SetX(1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(3, 0, 'Keterangan', 0, 0, 'L');
            $pdf->Cell(0.5, 0, ':', 0, 0, 'L');
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(1, 0, (string) $r1->ket_trbmasuk, 0, 0, 'L');

            $pdf->Ln(0.5);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(1, 0.7, 'NO', 1, 0, 'C');
            $pdf->Cell(3, 0.7, 'Kode Barang', 1, 0, 'L');
            $pdf->Cell(8.5, 0.7, 'Nama Barang', 1, 0, 'L');
            $pdf->Cell(2, 0.7, 'Qty/Stok', 1, 0, 'R');
            $pdf->Cell(2, 0.7, 'No Batch', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Exp Date', 1, 0, 'C');
            $pdf->Cell(2, 0.7, 'Satuan', 1, 0, 'C');
            $pdf->Cell(1.5, 0.7, 'HNA', 1, 0, 'C');
            $pdf->Cell(1, 0.7, 'Disc', 1, 0, 'C');
            $pdf->Cell(1.5, 0.7, 'Hrg beli', 1, 0, 'R');
            $pdf->Cell(3, 0.7, 'Total', 1, 1, 'R');
            $pdf->SetFont('Arial', '', 8);

            $no2 = 1;
            foreach ($r1->detail()->orderBy('nmbrg_dtrbmasuk')->get() as $lihat) {
                $pdf->Cell(1, 0.5, (string) $no2, 1, 0, 'C');
                $pdf->Cell(3, 0.5, (string) $lihat->kd_barang, 1, 0, 'L');
                $pdf->Cell(8.5, 0.5, (string) $lihat->nmbrg_dtrbmasuk, 1, 0, 'L');
                $pdf->Cell(2, 0.5, $this->formatRupiah($lihat->qty_dtrbmasuk), 1, 0, 'R');
                $pdf->Cell(2, 0.5, (string) $lihat->no_batch, 1, 0, 'R');
                $pdf->Cell(2, 0.5, $lihat->exp_date?->format('Y-m-d') ?? '', 1, 0, 'R');
                $pdf->Cell(2, 0.5, (string) $lihat->sat_dtrbmasuk, 1, 0, 'R');
                $pdf->Cell(1.5, 0.5, $this->formatRupiah($lihat->hnasat_dtrbmasuk), 1, 0, 'R');
                $pdf->Cell(1, 0.5, (string) $lihat->diskon, 1, 0, 'C');
                $pdf->Cell(1.5, 0.5, $this->formatRupiah($lihat->hrgsat_dtrbmasuk), 1, 0, 'R');
                $pdf->Cell(3, 0.5, $this->formatRupiah($lihat->hrgttl_dtrbmasuk), 1, 1, 'R');
                $no2++;
            }

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(24.5, 0.7, 'Total + PPN 11%', 1, 0, 'R');
            $pdf->Cell(3, 0.7, $this->formatRupiah($r1->ttl_trbmasuk), 1, 1, 'R');
            $pdf->Ln(0.7);
        }

        // Beda dari legacy: total ini SELALU ikut menghormati filter supplier yang sama
        // dengan daftar di atasnya ($headers sudah difilter di cetak()) -- legacy
        // menjumlahkan ulang lewat query terpisah TANPA filter supplier sama sekali
        // (karena filter supplier legacy sendiri memang tidak pernah tersambung), yang
        // akan menyesatkan begitu supplier benar-benar dipilih.
        $totalKeseluruhan = $headers->sum('ttl_trbmasuk');

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(24.5, 0.7, 'Total Barang Masuk dari tanggal ' . $this->tglIndo($tglAwal) . ' sampai dengan ' . $this->tglIndo($tglAkhir) . ' Senilai', 0, 0, 'R');
        $pdf->Cell(3, 0.7, 'Rp ' . $this->formatRupiah($totalKeseluruhan), 0, 1, 'R');

        return $pdf;
    }

    public function excel(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'id_supplier' => 'nullable|integer',
        ]);

        $query = Trbmasuk::whereBetween('tgl_trbmasuk', [$validated['tgl_awal'], $validated['tgl_akhir']]);
        if (!empty($validated['id_supplier'])) {
            $query->where('id_supplier', $validated['id_supplier']);
        }
        $rows = $query->orderBy('tgl_trbmasuk')->get();

        $supplierNama = !empty($validated['id_supplier'])
            ? (Supplier::find($validated['id_supplier'])->nm_supplier ?? '')
            : 'Semua Supplier';

        return response()->view('inventory.lpbrgmasuk.excel', [
            'rows' => $rows,
            'supplierNama' => $supplierNama,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_Pembelian_Obat.xls"',
        ]);
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
