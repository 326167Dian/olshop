<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Order;
use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function reportProcess()
    {
        return view('backend.report.process', []);
    }

    public function reportVisits()
    {
        $totalVisits = PageVisit::where('page', 'home-page')->count();

        $dailyVisits = PageVisit::where('page', 'home-page')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->keyBy('tanggal');

        // Susun 30 hari terakhir termasuk hari tanpa kunjungan (jumlah 0)
        $rekap = collect();
        for ($i = 0; $i < 30; $i++) {
            $tanggal = now()->subDays($i)->format('Y-m-d');
            $rekap->push([
                'tanggal' => $tanggal,
                'jumlah' => $dailyVisits->get($tanggal)->jumlah ?? 0,
            ]);
        }

        // Per halaman (30 hari terakhir) -- 'page' di sini adalah nama route, diisi
        // middleware TrackPageVisit untuk halaman publik selain home-page (yang masih
        // dicatat manual di HomepageController, lihat $totalVisits di atas).
        $labelHalaman = [
            'home-page' => 'Halaman Utama',
            'produk.all' => 'Semua Produk',
            'produk.detail' => 'Detail Produk',
            'produk.kategori' => 'Kategori Produk',
            'produk.search' => 'Pencarian Produk',
            'artikel.all' => 'Daftar Artikel',
            'article.show' => 'Detail Artikel',
            'testimoni.all' => 'Testimoni',
            'video-edukasi.all' => 'Daftar Video Edukasi',
            'video-edukasi.show' => 'Detail Video Edukasi',
        ];

        $rekapHalaman = PageVisit::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('page, COUNT(*) as jumlah')
            ->groupBy('page')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($baris) => [
                'label' => $labelHalaman[$baris->page] ?? $baris->page,
                'jumlah' => $baris->jumlah,
            ]);

        // Rincian kategori yang dikunjungi (dari baris 'produk.kategori' di atas) --
        // cuma mencakup kunjungan SETELAH kolom ref_id ada (lihat migration
        // add_ref_id_to_page_visits_table); kunjungan sebelumnya tidak tersimpan id
        // kategorinya jadi tidak ikut terhitung di rincian ini.
        $rekapKategori = PageVisit::where('page', 'produk.kategori')
            ->whereNotNull('ref_id')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('ref_id, COUNT(*) as jumlah')
            ->groupBy('ref_id')
            ->orderByDesc('jumlah')
            ->get()
            ->map(function ($baris) {
                return [
                    'nama' => Category::find($baris->ref_id)->name ?? "Kategori #{$baris->ref_id} (sudah dihapus)",
                    'jumlah' => $baris->jumlah,
                ];
            });

        return view('backend.report.visits', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Kunjungan Website',
            'totalVisits' => $totalVisits,
            'rekap' => $rekap,
            'rekapHalaman' => $rekapHalaman,
            'rekapKategori' => $rekapKategori,
        ]);
    }

    public function cetakOrderProses(Request $request)
    {
        // Menambahkan aturan validasi 
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $order = Order::whereIn('status', ['Paid', 'Kirim'])->orderBy('id', 'desc')->get();
        return view('backend.report.cetakproses', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Proses',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $order
        ]);
    }

    public function reportFinished()
    {
        return view('backend.report.finished', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Selesai',
        ]);
    }

    public function cetakOrderSelesai(Request $request)
    {
        // Validasi tanggal
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ], [
            'tanggal_awal.required' => 'Tanggal Awal harus diisi.',
            'tanggal_akhir.required' => 'Tanggal Akhir harus diisi.',
            'tanggal_akhir.after_or_equal' => 'Tanggal Akhir harus lebih besar atau sama dengan Tanggal Awal.',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $order = Order::where('status', 'Selesai')
            ->whereBetween('created_at', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59'])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.report.cetakfinished', [
            'judul' => 'Laporan',
            'subJudul' => 'Laporan Pesanan Selesai',
            'tanggalAwal' => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
            'cetak' => $order
        ]);
    }
}
