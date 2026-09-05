<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Trbmasuk;
use App\Models\Trkasir;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryNeracaController extends Controller
{
    /**
     * Modul "Neraca Laba Rugi" (grup Laporan, flag admin `neraca`), mengikuti
     * mod_laporan/neraca.php (form filter + act=tes menghitung & menampilkan hasil
     * inline di halaman yang sama) + mod_laporan/tampil_neraca.php (cetak PDF, FPDF
     * asli). Satu halaman saja, tidak ada export Excel di legacy.
     *
     * **Inkonsistensi legacy, sebagian direplikasi apa adanya, sebagian SUDAH
     * disamakan atas permintaan user (2026-09-06):** legacy asli menghitung baris
     * "Penjualan" secara berbeda antara layar (act=tes, SUM(trkasir_detail.hrgttl_dtrkasir))
     * dan PDF (SUM(trkasir.ttl_trkasir) langsung) -- versi trkasir_detail bias
     * terhadap diskon/perubahan harga jual dari waktu ke waktu, jadi port ini
     * SUDAH DIUBAH dari legacy: layar sekarang juga pakai SUM(trkasir.ttl_trkasir),
     * sama seperti PDF (lihat hitungTampil()). Breakdown Reguler/Member/Marketplace
     * (by trkasir_detail.tipe 1/2/3, hanya tampil di layar) tetap dari trkasir_detail
     * karena kolom tipe tidak ada di trkasir -- breakdown ini sengaja tidak lagi
     * menjumlah persis ke total Penjualan (selisih = diskon per baris).
     *
     * - "Hutang" TETAP direplikasi sesuai inkonsistensi legacy asli (belum diminta
     *   diseragamkan): layar = SUM(trbmasuk.ttl_trbmasuk) WHERE carabayar='KREDIT'
     *   TANPA filter tanggal (seluruh histori); PDF = query yang sama DENGAN
     *   filter tgl_trbmasuk BETWEEN periode.
     * - "Piutang" (id_carabayar='3') dan "Total Asset Tidak Lancar"
     *   (SUM(stok_barang*hrgsat_barang), seluruh tabel barang, tanpa filter tanggal)
     *   konsisten sama di layar maupun PDF, tidak berubah.
     */
    public function index(Request $request)
    {
        $tglAwal = $request->query('tgl_awal');
        $tglAkhir = $request->query('tgl_akhir');
        $hasil = null;

        if ($tglAwal && $tglAkhir) {
            $validated = $request->validate([
                'tgl_awal' => 'required|date',
                'tgl_akhir' => 'required|date',
            ]);

            $hasil = $this->hitungTampil($validated['tgl_awal'], $validated['tgl_akhir']);
        }

        return view('inventory.neraca.index', [
            'judul' => 'Inventory',
            'hasil' => $hasil,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
        ]);
    }

    public function cetak(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $hasil = $this->hitungCetak($validated['tgl_awal'], $validated['tgl_akhir']);
        $admin = Auth::guard('admin')->user();

        $pdf = $this->buildNeracaPdf($hasil, $validated['tgl_awal'], $validated['tgl_akhir'], $admin->nama_lengkap ?? '');

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="neraca_laba_rugi.pdf"',
        ]);
    }

    /**
     * Versi layar (act=tes). Baris "Penjualan" DIUBAH dari perilaku legacy asli
     * (lihat catatan kelas) atas permintaan user 2026-09-06: SUM(trkasir_detail.hrgttl_dtrkasir)
     * bias terhadap diskon/perubahan harga jual dari waktu ke waktu, jadi disamakan
     * dengan PDF -- SUM(trkasir.ttl_trkasir) langsung. Breakdown Reguler/Member/
     * Marketplace TETAP dari trkasir_detail.tipe (kolom per-baris, tidak ada di
     * tabel trkasir) sehingga breakdown tidak lagi otomatis menjumlah persis ke
     * total Penjualan -- itu murni selisih diskon per baris, bukan bug.
     */
    private function hitungTampil(string $awal, string $akhir): array
    {
        $penjualan = (float) Trkasir::whereBetween('tgl_trkasir', [$awal, $akhir])->sum('ttl_trkasir');

        $penjualanDetailQuery = fn () => TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$awal, $akhir]);

        $reguler = (float) $penjualanDetailQuery()->where('trkasir_detail.tipe', 1)->sum('trkasir_detail.hrgttl_dtrkasir');
        $member = (float) $penjualanDetailQuery()->where('trkasir_detail.tipe', 2)->sum('trkasir_detail.hrgttl_dtrkasir');
        $marketplace = (float) $penjualanDetailQuery()->where('trkasir_detail.tipe', 3)->sum('trkasir_detail.hrgttl_dtrkasir');

        $pembelianCash = (float) Trbmasuk::where('carabayar', 'LUNAS')
            ->whereBetween('tgl_trbmasuk', [$awal, $akhir])
            ->sum('ttl_trbmasuk');

        $piutang = (float) Trkasir::where('id_carabayar', 3)
            ->whereBetween('tgl_trkasir', [$awal, $akhir])
            ->sum('ttl_trkasir');

        // Sengaja tanpa filter tanggal, mengikuti mod_laporan/neraca.php act=tes.
        $hutang = (float) Trbmasuk::where('carabayar', 'KREDIT')->sum('ttl_trbmasuk');

        $asetTidakLancar = (float) Product::query()->selectRaw('SUM(stok_barang * hrgsat_barang) as total')->value('total') ?? 0;

        $asetLancar = $penjualan - $pembelianCash - $hutang;
        $neraca = $penjualan - $piutang - $hutang - $pembelianCash;

        return compact(
            'penjualan', 'reguler', 'member', 'marketplace', 'pembelianCash',
            'piutang', 'hutang', 'asetTidakLancar', 'asetLancar', 'neraca'
        );
    }

    /** Versi PDF (tampil_neraca.php) -- lihat catatan kelas soal inkonsistensi vs layar. */
    private function hitungCetak(string $awal, string $akhir): array
    {
        $penjualan = (float) Trkasir::whereBetween('tgl_trkasir', [$awal, $akhir])->sum('ttl_trkasir');

        $pembelianCash = (float) Trbmasuk::where('carabayar', 'LUNAS')
            ->whereBetween('tgl_trbmasuk', [$awal, $akhir])
            ->sum('ttl_trbmasuk');

        $piutang = (float) Trkasir::where('id_carabayar', 3)
            ->whereBetween('tgl_trkasir', [$awal, $akhir])
            ->sum('ttl_trkasir');

        $hutang = (float) Trbmasuk::where('carabayar', 'KREDIT')
            ->whereBetween('tgl_trbmasuk', [$awal, $akhir])
            ->sum('ttl_trbmasuk');

        $asetTidakLancar = (float) Product::query()->selectRaw('SUM(stok_barang * hrgsat_barang) as total')->value('total') ?? 0;

        $asetLancar = $penjualan - $pembelianCash - $hutang;
        $neraca = $penjualan - $piutang - $hutang - $pembelianCash;

        return compact('penjualan', 'pembelianCash', 'piutang', 'hutang', 'asetTidakLancar', 'asetLancar', 'neraca');
    }

    private function buildNeracaPdf(array $data, string $tglAwal, string $tglAkhir, string $printedBy): \FPDF
    {
        $pdf = new \FPDF('P', 'cm', 'A4');
        $pdf->SetMargins(1, 1.2, 1);
        $pdf->SetAutoPageBreak(true, 1.5);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(12, 0.7, 'NERACA LABA RUGI', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(12, 0.5, 'Tanggal Cetak : ' . now()->format('d-m-Y H:i:s'), 0, 1, 'L');
        if ($printedBy !== '') {
            $pdf->Cell(12, 0.5, 'Dicetak Oleh : ' . $printedBy, 0, 1, 'L');
        }
        $pdf->Cell(12, 0.5, 'Periode : ' . $this->tglIndo($tglAwal) . ' - ' . $this->tglIndo($tglAkhir), 0, 1, 'L');
        $pdf->Ln(0.2);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(1, 0.7, 'No', 1, 0, 'C');
        $pdf->Cell(10, 0.7, 'Keterangan', 1, 0, 'C');
        $pdf->Cell(4, 0.7, 'Nilai', 1, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $baris = [
            ['1', 'Penjualan', $data['penjualan']],
            ['2', 'Pembelian Cash', $data['pembelianCash']],
            ['3', 'Piutang (Total Penjualan Belum Dibayar)', $data['piutang']],
            ['4', 'Hutang (Total Pembelian Belum Dibayar)', $data['hutang']],
            ['5', 'Total Asset Lancar', $data['asetLancar']],
            ['6', 'Total Asset Tidak Lancar', $data['asetTidakLancar']],
            ['7', 'Neraca Laba/Rugi', $data['neraca']],
        ];

        foreach ($baris as $b) {
            $pdf->Cell(1, 0.7, $b[0], 1, 0, 'C');
            $pdf->Cell(10, 0.7, $b[1], 1, 0, 'L');
            $pdf->Cell(4, 0.7, 'Rp ' . $this->formatRupiah($b[2]), 1, 1, 'R');
        }

        return $pdf;
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
