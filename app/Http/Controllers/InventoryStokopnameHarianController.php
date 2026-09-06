<?php

namespace App\Http\Controllers;

use App\Models\StokOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryStokopnameHarianController extends Controller
{
    /**
     * Modul "Stok Opname Harian" (grup Inventory, flag admin `soharian`), mengikuti
     * `mod_stokopname/stokopname_harian.php` (index + act=tampil) + `tabel_stokopname.php`/
     * `tabel_stokopname_rekap.php`/`simpan_stokopname.php` (folder `mod_stokopname/`,
     * BUKAN `mod_lapstok/` -- lihat catatan penemuan di bawah) + `mod_lapstok/soharian_excel.php`.
     *
     * **Penemuan penting saat menelusuri routing legacy:** `content_admin.php` punya
     * baris `elseif ($_GET['module']=='soharian') { // include ".../mod_lapstok/soharian.php";
     * include ".../mod_stokopname/stokopname_harian.php"; }` -- file `mod_lapstok/soharian.php`
     * (folder yang sama dengan modul Nilai Stok/Stok Kritis) sudah DIKOMENTARI dan
     * DIGANTI oleh file di folder `mod_stokopname/` yang BERBEDA. Jadi modul Harian yang
     * benar-benar aktif ada di folder `mod_stokopname/`, bukan `mod_lapstok/soharian.php`
     * yang sekilas namanya lebih cocok -- dikonfirmasi baca langsung `content_admin.php`,
     * bukan tebakan dari nama file.
     *
     * **Dua file "sinkronisasi" di folder yang sama DIKONFIRMASI MATI, tidak diporting:**
     * `sinkronisasi_stok_minus.php` menangkap tgl_awal/tgl_akhir/shift dari POST tapi
     * TIDAK PERNAH memakainya sama sekali (tidak ada query/update apa pun) -- murni
     * redirect kosong, stub yang tidak pernah selesai dibuat. `sinkronisasi_stok_plus.php`
     * melakukan sesuatu yang SAMA SEKALI TIDAK ADA HUBUNGANNYA dengan nama filenya atau
     * konteks stok opname manapun: loop SEMUA baris `barang` (bukan hanya yang di-opname),
     * menghitung ulang selisih beli-jual sepanjang masa, lalu MENIMPA `stok_barang`
     * langsung ke angka itu (bukan menambah/mengurangi seperti sinkronisasi normal).
     * Keduanya dikonfirmasi TIDAK PERNAH direferensikan/ditautkan di mana pun di seluruh
     * pohon legacy (grep bersih) -- dead code, bukan fitur yang sekadar belum dipakai.
     *
     * **Optimasi N+1 diterapkan** (pola yang sama seperti mstok/Laporan Stok Opname):
     * grid (`tabel_stokopname.php`) legacy menjalankan hingga 3 query TERPISAH per baris
     * item yang terjual (cek sudah-diopname, lalu 2 query beli/jual all-time) --
     * diporting jadi: 1 query utama (sudah meng-exclude item yang sudah diopname lewat
     * `NOT EXISTS`) + 2 query agregat `whereIn` untuk SEMUA item sekaligus, bukan per
     * item. Excel (`soharian_excel.php`) juga N+1 (2 query tambahan per item terjual,
     * PhpSpreadsheet) -- diporting jadi 1 query gabungan, format HTML/CSS `.xls` yang
     * konsisten dengan seluruh export lain di port ini (lihat catatan yang sama di
     * `InventoryStokopnameController`).
     *
     * **Bug legacy diperbaiki, tidak direplikasi:**
     * - `tgl_current` ditulis `date('ymdHis')` (string tak valid) dan `exp_date` kosong
     *   ditulis `'0000-00-00'` -- sama persis bug yang sudah ditemukan & diperbaiki di
     *   modul Stok Opname Bulanan (lihat catatan di sana); diperbaiki dengan cara yang
     *   sama (`now()` dan sentinel `'1970-01-01'`).
     * - `exportExcel()` legacy mengirim `tgl_akhir` dari sebuah input `#tgl_akhir` yang
     *   TIDAK PERNAH ada di form (dihapus/dikomentari, hanya `#tgl_awal` dan `#shift`
     *   yang benar-benar ada) -- setiap export nyata mengirim `finish=''`, membuat query
     *   `BETWEEN '$tgl_awal' AND ''` yang pasti kosong. Modul ini memang murni satu
     *   tanggal (act=tampil juga tidak pernah memakai tgl_akhir), jadi diporting sebagai
     *   satu tanggal saja, bukan direplikasi sebagai rentang yang pasti rusak.
     * - `soharian_excel.php` melabeli shift selain '1' sebagai "SORE" tanpa syarat
     *   (`$shift == '1' ? 'PAGI' : 'SORE'`), jadi shift Malam ikut tertulis "SORE" --
     *   diperbaiki menangani ketiga label dengan benar.
     *
     * **Perbedaan nyata dari modul sibling Stok Opname Bulanan, direplikasi apa adanya:**
     * kolom "Stok Sistem" di grid Harian SELALU tampil untuk semua role (Bulanan
     * membatasinya khusus pemilik) -- dikonfirmasi dari source, bukan salah porting.
     * Tombol HAPUS di rekap Harian juga TIDAK PERNAH disembunyikan di UI legacy untuk
     * role manapun (Bulanan setidaknya mencoba menyembunyikannya di klien) -- gerbang
     * server-side pemilik-only tetap ditambahkan di sini (endpoint `destroy()` yang
     * SAMA persis dipakai ulang dari `InventoryStokopnameController`, lihat routes),
     * konsisten dengan perbaikan keamanan yang sudah diterapkan di modul itu.
     */
    public function index()
    {
        return view('inventory.stokopname-harian.index', ['judul' => 'Inventory']);
    }

    public function tampil(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'shift' => 'required|integer|min:1|max:3',
        ]);

        return view('inventory.stokopname-harian.tampil', [
            'judul' => 'Inventory',
            'tglAwal' => $validated['tgl_awal'],
            'shift' => $validated['shift'],
        ]);
    }

    /** Ports tabel_stokopname.php (mod_stokopname) -- item yang TERJUAL shift ini dan BELUM diopname. */
    public function grid(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'shift' => 'required|integer|min:1|max:3',
        ]);

        $rows = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->join('barang as b', 'b.id_barang', '=', 'td.id_barang')
            ->where('tk.tgl_trkasir', $validated['tgl_awal'])
            ->where('tk.shift', $validated['shift'])
            ->whereNotExists(function ($q) use ($validated) {
                $q->select(DB::raw(1))
                    ->from('stok_opname as so')
                    ->whereColumn('so.id_barang', 'b.id_barang')
                    ->where('so.shift', $validated['shift'])
                    ->where('so.tgl_stokopname', $validated['tgl_awal']);
            })
            ->groupBy('b.id_barang', 'td.kd_barang', 'b.nm_barang', 'b.sat_barang', 'b.jenisobat', 'b.hrgsat_barang')
            ->selectRaw('
                b.id_barang, td.kd_barang, b.nm_barang, b.sat_barang, b.jenisobat, b.hrgsat_barang,
                SUM(td.qty_dtrkasir) as ttlqty
            ')
            ->get();

        $idBarangList = $rows->pluck('id_barang');

        $totalBeli = DB::table('trbmasuk_detail')
            ->whereIn('id_barang', $idBarangList)
            ->groupBy('id_barang')
            ->selectRaw('id_barang, SUM(qty_dtrbmasuk) as total')
            ->pluck('total', 'id_barang');

        $totalJual = DB::table('trkasir_detail')
            ->whereIn('id_barang', $idBarangList)
            ->groupBy('id_barang')
            ->selectRaw('id_barang, SUM(qty_dtrkasir) as total')
            ->pluck('total', 'id_barang');

        $rows = $rows->map(function ($r) use ($totalBeli, $totalJual) {
            $r->stok_sistem = (float) ($totalBeli[$r->id_barang] ?? 0) - (float) ($totalJual[$r->id_barang] ?? 0);

            return $r;
        });

        return view('inventory.stokopname-harian.partials.grid', [
            'rows' => $rows,
        ]);
    }

    /** Ports tabel_stokopname_rekap.php (mod_stokopname) -- item yang SUDAH diopname shift ini. */
    public function rekap(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'shift' => 'required|integer|min:1|max:3',
        ]);

        $rows = StokOpname::query()
            ->join('barang', 'barang.id_barang', '=', 'stok_opname.id_barang')
            ->where('stok_opname.shift', $validated['shift'])
            ->where('stok_opname.tgl_stokopname', $validated['tgl_awal'])
            ->orderBy('barang.nm_barang')
            ->select('stok_opname.*', 'barang.nm_barang', 'barang.sat_barang')
            ->get();

        $admin = Auth::guard('admin')->user();

        return view('inventory.stokopname-harian.partials.rekap', [
            'rows' => $rows,
            'isPemilik' => $admin->isPemilik(),
        ]);
    }

    /** Ports simpan_stokopname.php (mod_stokopname). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|integer|exists:barang,id_barang',
            'kd_barang' => 'required|string',
            'hrgsat_barang' => 'required|numeric',
            'stok_fisik' => 'required|numeric|min:0',
            'shift' => 'required|integer|min:1|max:3',
            'tgl_awal' => 'required|date',
        ]);

        $totalBeli = (float) DB::table('trbmasuk_detail')->where('id_barang', $validated['id_barang'])->sum('qty_dtrbmasuk');
        $totalJual = (float) DB::table('trkasir_detail')->where('id_barang', $validated['id_barang'])->sum('qty_dtrkasir');
        $stokSistem = $totalBeli - $totalJual;
        $selisih = (float) $validated['stok_fisik'] - $stokSistem;

        $admin = Auth::guard('admin')->user();

        StokOpname::create([
            'id_barang' => $validated['id_barang'],
            'kd_barang' => $validated['kd_barang'],
            'stok_sistem' => $stokSistem,
            'stok_fisik' => $validated['stok_fisik'],
            'exp_date' => '1970-01-01',
            'jml' => 0,
            'selisih' => $selisih,
            'hrgsat_barang' => $validated['hrgsat_barang'],
            'ttl_hrgbrg' => $selisih * (float) $validated['hrgsat_barang'],
            'tgl_current' => now(),
            'tgl_stokopname' => $validated['tgl_awal'],
            'shift' => $validated['shift'],
            'id_admin' => $admin->id_admin,
        ]);

        return response()->json(['status' => true]);
    }

    /** Ports mod_lapstok/soharian_excel.php -- lihat catatan kelas soal optimasi & bug tgl_akhir. */
    public function excel(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'shift' => 'required|integer|min:1|max:3',
        ]);

        $rows = DB::table('trkasir_detail as td')
            ->join('trkasir as tk', 'tk.kd_trkasir', '=', 'td.kd_trkasir')
            ->join('barang as b', 'b.kd_barang', '=', 'td.kd_barang')
            ->where('tk.tgl_trkasir', $validated['tgl_awal'])
            ->where('tk.shift', $validated['shift'])
            ->groupBy('td.kd_barang', 'td.nmbrg_dtrkasir', 'td.sat_dtrkasir', 'td.hrgjual_dtrkasir', 'b.stok_barang', 'b.hrgsat_barang')
            ->selectRaw('
                td.kd_barang, td.nmbrg_dtrkasir, td.sat_dtrkasir, td.hrgjual_dtrkasir,
                b.stok_barang, b.hrgsat_barang,
                SUM(td.qty_dtrkasir) as ttlqty
            ')
            ->orderBy('td.nmbrg_dtrkasir')
            ->get();

        $shiftLabel = match ((int) $validated['shift']) {
            1 => 'PAGI',
            2 => 'SORE',
            3 => 'MALAM',
            default => (string) $validated['shift'],
        };

        return response()->view('inventory.stokopname-harian.excel', [
            'rows' => $rows,
            'tglAwal' => $validated['tgl_awal'],
            'shiftLabel' => $shiftLabel,
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="stok_opname_harian.xls"',
        ]);
    }
}
