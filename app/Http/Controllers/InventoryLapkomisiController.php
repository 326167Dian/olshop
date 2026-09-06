<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\KomisiGlobal;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryLapkomisiController extends Controller
{
    /**
     * Modul "Laporan > Komisi Pegawai" (grup Laporan), mengikuti
     * `mod_komisi/lapkomisi.php`. BEDA dari modul "Komisi Pegawai" di grup lain
     * (flag `komisi`, `InventoryKomisiController` -- pengaturan komisi per item +
     * komisi global) -- ini laporan baca-saja yang MEMAKAI hasil pengaturan itu,
     * bukan mengubahnya.
     *
     * **Digerbang manual (bukan lewat `Admin::PERMISSION_GROUPS`/middleware
     * `inventory.module:x` standar), sesuai legacy:** link ini di `media_admin.php`
     * hanya muncul kalau `$_SESSION['level']=='pemilik' DAN $_SESSION['komisi']=='Y'`
     * -- flag YANG SAMA (`komisi`) dengan modul Data Master di atas, cuma
     * ditambah syarat role pemilik. Karena satu kolom flag tidak bisa dipetakan ke
     * dua entri sidebar berbeda lewat mekanisme `PERMISSION_GROUPS` standar (itu
     * asumsi 1 flag = 1 tujuan), sidebar & gerbang akses untuk modul ini ditulis
     * manual di `app.blade.php` + `abort_unless()` di controller, mengikuti pola
     * yang sama dengan tombol "Perubahan Transaksi"/"Undo Transaksi Terhapus" di
     * dashboard Kasir (pemilik-only tanpa kolom flag sendiri).
     *
     * **Optimasi N+1 diterapkan** (pola yang sama seperti modul-modul Laporan
     * lain sepanjang port ini): legacy menjalankan 2 query TERPISAH di dalam loop
     * PER admin aktif (jumlah komisi produk, total omzet transaksi) -- diganti 2
     * query `GROUP BY` sekali untuk SEMUA admin sekaligus, digabung di PHP.
     *
     * Formula (dari source, tidak diubah): Komisi Produk = SUM(trkasir_detail.komisi)
     * WHERE idadmin=X pada rentang tanggal (nilai yang SUDAH dihitung & disimpan
     * saat baris keranjang dibuat, lihat catatan komisi di InventoryTrkasirController
     * -- laporan ini murni membaca ulang). Komisi Global = SUM(trkasir.ttl_trkasir)
     * WHERE petugas=nama_lengkap pada rentang tanggal, dikali persentase
     * `komisiglobal` yang sedang berstatus 'ON' (0% kalau tidak ada baris aktif).
     */
    public function index()
    {
        $this->gate();

        return view('inventory.lapkomisi.index', ['judul' => 'Inventory']);
    }

    public function tampil(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = $this->hitungRekap($validated['tgl_awal'], $validated['tgl_akhir']);

        return view('inventory.lapkomisi.tampil', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'totalPk' => $rows->sum('komisi_produk'),
            'totalGlobal' => $rows->sum('komisi_global'),
            'totalSub' => $rows->sum('subtotal'),
        ]);
    }

    public function detail(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'id' => 'required|integer|exists:admin,id_admin',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $admin = Admin::find($validated['id']);

        $rows = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->where('trkasir_detail.idadmin', $validated['id'])
            ->where('trkasir_detail.komisi', '!=', 0)
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->select('trkasir_detail.nmbrg_dtrkasir', 'trkasir_detail.qty_dtrkasir', 'trkasir_detail.komisi')
            ->get();

        return view('inventory.lapkomisi.detail', [
            'judul' => 'Inventory',
            'petugas' => $admin->nama_lengkap,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('komisi'),
        ]);
    }

    private function hitungRekap(string $tglAwal, string $tglAkhir)
    {
        $komisiProduk = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$tglAwal, $tglAkhir])
            ->groupBy('trkasir_detail.idadmin')
            ->selectRaw('trkasir_detail.idadmin, SUM(trkasir_detail.komisi) as total')
            ->pluck('total', 'idadmin');

        $omzetPerPetugas = DB::table('trkasir')
            ->whereBetween('tgl_trkasir', [$tglAwal, $tglAkhir])
            ->groupBy('petugas')
            ->selectRaw('petugas, SUM(ttl_trkasir) as total')
            ->pluck('total', 'petugas');

        $persenGlobal = (float) (KomisiGlobal::where('status', 'ON')->value('nilai') ?? 0) / 100;

        return Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get()->map(function ($admin) use ($komisiProduk, $omzetPerPetugas, $persenGlobal) {
            $pk = (float) ($komisiProduk[$admin->id_admin] ?? 0);
            $omzet = (float) ($omzetPerPetugas[$admin->nama_lengkap] ?? 0);
            $global = $omzet * $persenGlobal;

            return (object) [
                'id_admin' => $admin->id_admin,
                'nama_lengkap' => $admin->nama_lengkap,
                'komisi_produk' => $pk,
                'komisi_global' => $global,
                'subtotal' => $pk + $global,
            ];
        });
    }

    private function gate(): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik() && strtoupper((string) $admin->komisi) === 'Y', 403);
    }
}
