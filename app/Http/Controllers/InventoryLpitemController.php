<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class InventoryLpitemController extends Controller
{
    /**
     * Modul "Item Barang" (grup Laporan, flag admin `lpitem`), mengikuti
     * public/apotekberlian/masuk/modul/mod_laporan/cetak_barang.php (cetak PDF) dan
     * mod_barang/cetak_barang_excel.php (export Excel). BEDA dari modul Item Barang
     * CRUD (`mbarang`, sudah diporting sebagai InventoryBarangController) -- ini murni
     * laporan baca-saja atas tabel barang yang sama, digerbang flag admin terpisah.
     *
     * cetak_barang_excel.php di legacy sebenarnya digerbang `$_SESSION['level']=='pemilik'`
     * (bukan flag lpitem) dan link-nya ada di halaman CRUD Item Barang, bukan di menu
     * Laporan -- tapi InventoryBarangController sendiri sejak awal menandai fitur ini
     * "bagian dari grup Laporan, digerbang flag berbeda" dan menundanya sampai modul ini
     * dibangun. Disatukan di sini dengan cetak PDF-nya di bawah flag `lpitem` yang sama,
     * sesuai keputusan pengguna 2026-09-05.
     *
     * Baik cetak maupun excel TIDAK punya filter apa pun (sesuai legacy) -- dump penuh
     * tabel barang, diurutkan nama.
     */
    public function index()
    {
        return view('inventory.lpitem.index', ['judul' => 'Inventory']);
    }

    public function cetak()
    {
        $pdf = new \FPDF('L', 'cm', 'A4');
        $pdf->SetMargins(1, 1, 1);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25.5, 0.7, 'LAPORAN DATA BARANG', 0, 1, 'L');
        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(1, 0.7, 'NO', 1, 0, 'C');
        $pdf->Cell(3, 0.7, 'Kode Barang', 1, 0, 'L');
        $pdf->Cell(11, 0.7, 'Nama Barang', 1, 0, 'L');
        $pdf->Cell(1.5, 0.7, 'Qty', 1, 0, 'R');
        $pdf->Cell(2, 0.7, 'Satuan', 1, 0, 'C');
        $pdf->Cell(3, 0.7, 'Harga Beli', 1, 0, 'R');
        $pdf->Cell(3, 0.7, 'Harga Jual', 1, 0, 'R');
        $pdf->Cell(3, 0.7, 'Indikasi', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 8);

        $no = 1;
        // chunk() -- bukan get() sekaligus -- karena tabel barang ~5000 baris (sama
        // seperti pertimbangan performa yang sudah dipakai di Item Barang CRUD).
        Product::orderBy('nm_barang')->chunk(500, function ($chunk) use ($pdf, &$no) {
            foreach ($chunk as $barang) {
                $pdf->Cell(1, 0.6, (string) $no, 1, 0, 'C');
                $pdf->Cell(3, 0.6, (string) $barang->kd_barang, 1, 0, 'L');
                $pdf->Cell(11, 0.6, (string) $barang->nm_barang, 1, 0, 'L');
                $pdf->Cell(1.5, 0.6, (string) $barang->stok_barang, 1, 0, 'R');
                $pdf->Cell(2, 0.6, (string) $barang->sat_barang, 1, 0, 'C');
                $pdf->Cell(3, 0.6, $this->formatRupiah($barang->hrgsat_barang), 1, 0, 'R');
                $pdf->Cell(3, 0.6, $this->formatRupiah($barang->hrgjual_barang), 1, 0, 'R');
                // 'lihat aplikasi' persis legacy -- kolom indikasi barang bisa berisi
                // teks HTML panjang, tidak muat di satu baris tabel laporan ringkas ini.
                $pdf->Cell(3, 0.6, 'lihat aplikasi', 1, 1, 'C');
                $no++;
            }
        });

        $admin = Auth::guard('admin')->user();
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.7, 'Tanggal Cetak : ' . now()->format('d-m-Y H:i:s') . ' || Dicetak Oleh : ' . $admin->nama_lengkap, 0, 0, 'L');

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Laporan_data_barang.pdf"',
        ]);
    }

    public function excel()
    {
        $admin = Auth::guard('admin')->user();

        // Satu query untuk SEMUA batch aktif (bukan satu query per barang seperti
        // legacy -- pola N+1 pada ~5000 barang), dikelompokkan per kd_barang di memori.
        $batchPerBarang = Batch::where('status', 'masuk')
            ->where('exp_date', '>', now()->toDateString())
            ->orderBy('no_batch')
            ->get(['kd_barang', 'no_batch'])
            ->groupBy('kd_barang');

        return response()->view('inventory.lpitem.excel', [
            'rows' => Product::orderBy('nm_barang')->get(),
            'batchPerBarang' => $batchPerBarang,
            'admin' => $admin,
            'tanggal' => now()->format('d-m-Y'),
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Data_barang.xls"',
        ]);
    }

    private function formatRupiah($angka): string
    {
        if ($angka === null || $angka === '') {
            return '0';
        }

        return number_format((float) $angka, 0, ',', '.');
    }
}
