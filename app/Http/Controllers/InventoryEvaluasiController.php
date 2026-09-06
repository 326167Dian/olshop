<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\TrkasirDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryEvaluasiController extends Controller
{
    /**
     * Modul "Laporan > Evaluasi Pegawai" (grup Laporan), mengikuti
     * `mod_evaluasi/lapevaluasi.php` -- evaluasi kinerja tiap petugas berdasarkan
     * laba penjualan (dari baris yang MEREKA layani, `trkasir_detail.idadmin`) dan
     * omzet penjualan (dari transaksi yang mereka BUAT sebagai kasir,
     * `trkasir.id_user`).
     *
     * **Digerbang manual, sama seperti Laporan > Komisi Pegawai**: legacy hanya
     * mengecek `$_SESSION['level']=='pemilik'`, TANPA kolom flag admin sama sekali
     * (menu ini muncul untuk SEMUA pemilik, terlepas dari centang izin apa pun) --
     * sidebar & `abort_unless()` di sini mengikuti persis, tidak ditambahkan
     * kolom flag baru yang tidak ada di legacy.
     *
     * **Bug nyata diperbaiki, tidak direplikasi** (ditemukan waktu membaca source,
     * bukan tebakan): baris utama legacy menjalankan
     * `SELECT SUM(profit) as tambahan, qty_dtrkasir, id_user FROM ... WHERE idadmin=?`
     * TANPA `GROUP BY` sama sekali -- mencampur kolom teragregasi (`SUM`) dengan
     * kolom mentah (`qty_dtrkasir`) dalam satu SELECT tanpa pengelompokan. Di
     * sql_mode longgar (khas hosting legacy lama), MySQL diam-diam memilih nilai
     * `qty_dtrkasir` dari SATU baris SEMBARANG sementara `SUM(profit)` tetap
     * dihitung dari SEMUA baris yang cocok -- lalu `$pk = SUM(profit) * (qty baris
     * sembarang itu)`, sebuah perkalian yang tidak masuk akal. Query yang SAMA
     * dijalankan koneksi Laravel ini (`sql_mode` termasuk `ONLY_FULL_GROUP_BY`)
     * akan GAGAL total, bukan sekadar salah hasil. Diperbaiki ke rumus yang
     * dikonfirmasi benar dari halaman DETAIL laporan yang sama (`$per['profit'] *
     * $per['qty_dtrkasir']`, dijumlahkan per baris) -- tapi rumus rujukan itu
     * sendiri TERNYATA JUGA SALAH (lihat bug kedua di bawah). Setelah bug kedua
     * diperbaiki, agregasi tetap lewat 1 query `GROUP BY` per admin, bukan
     * disatukan lagi di PHP secara manual sesudahnya.
     *
     * **Bug kedua, ditemukan dari laporan user** (Grand Total Laba tampil
     * Rp1.856.534, sedangkan `SELECT SUM(profit) FROM trkasir_detail` langsung di
     * database untuk rentang tanggal yang sama menghasilkan Rp1.093.974):
     * `trkasir_detail.profit` **sudah tersimpan sebagai TOTAL PER BARIS** (sudah
     * dikalikan qty saat baris kasir disimpan -- lihat `InventoryTrkasirController`,
     * mis. `'profit' => $totalBaru - ($modal * $qtyBaru)` dengan `$totalBaru =
     * $qtyBaru * $hrgDisc`), sama seperti kolom `trkasir_detail.komisi` (lihat
     * `InventoryLapkomisiController`, yang SUDAH benar). Baik `lapevaluasi.php`
     * legacy (rumus detail `$per['profit'] * $per['qty_dtrkasir']`) maupun
     * percobaan porting pertama di sini mengalikan `profit` dengan qty SEKALI
     * LAGI, menghitung qty dua kali untuk baris manapun yang qty > 1. Diperbaiki:
     * `profit` dipakai apa adanya (baik di `hitungRekap()` maupun `detail()`),
     * tidak dikalikan qty lagi -- konsisten dengan cara `labapenjualan` dan
     * `lapkomisi` sudah menangani kolom total per baris masing-masing.
     *
     * **Baris mati juga tidak diporting:** legacy menghitung
     * `$subtotal = format_rupiah(($komisi['total_komisi'])+$pk)` di baris utama --
     * `$komisi` TIDAK PERNAH didefinisikan di file ini sama sekali (jelas sisa
     * copy-paste dari `lapkomisi.php`, yang memang punya variabel bernama sama)
     * dan `$subtotal` sendiri TIDAK PERNAH ditampilkan di tabel manapun (`<thead>`
     * cuma punya 4 kolom: No/Nama Petugas/Laba Penjualan/Omzet Penjualan -- cocok
     * dengan yang benar-benar dirender). Baris ini di legacy hanya menghasilkan
     * PHP notice (undefined variable) tanpa efek apa pun -- tidak diporting.
     *
     * **Optimasi N+1 diterapkan** (pola yang sama seperti seluruh modul Laporan
     * lain): legacy menjalankan 2 query per admin di dalam loop -- diganti 2
     * query `GROUP BY` sekali untuk semua admin.
     */
    public function index()
    {
        $this->gate();

        return view('inventory.evaluasi.index', ['judul' => 'Inventory']);
    }

    public function tampil(Request $request)
    {
        $this->gate();

        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = $this->hitungRekap($validated['tgl_awal'], $validated['tgl_akhir']);

        return view('inventory.evaluasi.tampil', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'totalLaba' => $rows->sum('laba'),
            'totalOmzet' => $rows->sum('omzet'),
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
            ->whereBetween('trkasir.tgl_trkasir', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->select('trkasir_detail.nmbrg_dtrkasir', 'trkasir_detail.qty_dtrkasir', 'trkasir_detail.profit')
            ->get()
            ->map(function ($r) {
                $r->subtotal = (float) $r->profit;

                return $r;
            });

        return view('inventory.evaluasi.detail', [
            'judul' => 'Inventory',
            'petugas' => $admin->nama_lengkap,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('subtotal'),
        ]);
    }

    private function hitungRekap(string $tglAwal, string $tglAkhir)
    {
        $labaPerAdmin = TrkasirDetail::query()
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$tglAwal, $tglAkhir])
            ->groupBy('trkasir_detail.idadmin')
            ->selectRaw('trkasir_detail.idadmin, SUM(trkasir_detail.profit) as total')
            ->pluck('total', 'idadmin');

        $omzetPerAdmin = DB::table('trkasir')
            ->whereBetween('tgl_trkasir', [$tglAwal, $tglAkhir])
            ->groupBy('id_user')
            ->selectRaw('id_user, SUM(ttl_trkasir) as total')
            ->pluck('total', 'id_user');

        return Admin::where('blokir', 'N')->orderBy('nama_lengkap')->get()->map(function ($admin) use ($labaPerAdmin, $omzetPerAdmin) {
            return (object) [
                'id_admin' => $admin->id_admin,
                'nama_lengkap' => $admin->nama_lengkap,
                'laba' => (float) ($labaPerAdmin[$admin->id_admin] ?? 0),
                'omzet' => (float) ($omzetPerAdmin[$admin->id_admin] ?? 0),
            ];
        });
    }

    private function gate(): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403);
    }
}
