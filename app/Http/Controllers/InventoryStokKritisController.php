<?php

namespace App\Http\Controllers;

use App\Models\Kdbm;
use App\Models\Product;
use App\Models\SupplierOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryStokKritisController extends Controller
{
    /**
     * Modul "Stok Kritis" (grup Inventory, flag admin `stok_kritis`), mengikuti
     * `mod_lapstok/stok_kritis.php` -- BEDA dari modul "Nilai Stok & Traffic Barang"
     * (flag `mstok`, sudah diporting) meski berbagi folder legacy `mod_lapstok/` dan
     * bahkan berbagi KOLOM CACHE YANG SAMA di tabel `barang` (`t30`/`q30`).
     *
     * **Overlap kolom cache dengan modul `mstok`, bukan bug port ini:** tombol
     * "PROSES ANALISA DATA" di sini (act=kritis30) menulis ulang `barang.t30`/`q30`
     * untuk SEMUA barang (tanpa syarat pernah diterima via trbmasuk_detail), rentang
     * tanggal sama [hari ini - 30, hari ini] -- BEDA dari `mstok`'s "PROSES UPDATE
     * DATABASE" yang membatasi hanya barang yang pernah muncul di trbmasuk_detail
     * DAN juga menulis t60/gr (kolom yang modul ini sama sekali tidak sentuh).
     * Menjalankan salah satu tombol bisa "menimpa" hasil tombol yang lain untuk
     * barang yang sama -- kondisi yang sudah ada di aplikasi legacy sendiri (dua
     * file berbeda menulis ke kolom yang sama dengan cakupan berbeda), diikuti apa
     * adanya, bukan disatukan.
     *
     * Query `kritis30` legacy SUDAH efisien (1 statement UPDATE...JOIN, bukan loop
     * PHP per item) -- diporting langsung tanpa perlu optimasi N+1 seperti modul
     * `mstok` kemarin.
     *
     * **Optimasi kecil ditambahkan (bukan diminta eksplisit, tapi sejalan):**
     * legacy `tampil30`/`over30` melakukan `SELECT * FROM barang` (~5000 baris)
     * lalu memfilter kriteria kritis/overstok satu-per-satu di PHP dengan sebuah
     * `while` loop -- di-porting dengan filter kriteria langsung di `WHERE` SQL,
     * jadi hanya baris yang benar-benar relevan yang pernah diambil dari database.
     *
     * **Fitur "ADD TO ORDER" (act=orders, aksi_simpan_orders.php) TIDAK dibangun
     * ulang sebagai form terpisah** -- legacy sendiri mendaur ulang endpoint modul
     * Pesan Barang (`mod_orders/aksi_orders.php`, `simpandetail_tbm.php`, dst) untuk
     * melanjutkan draft yang sama persis. Di sini: memilih item + klik "ADD TO
     * ORDER" langsung insert baris `ordersdetail` ke kode draft `kdbm` terbuka milik
     * admin ini (logika sama persis dengan `InventoryOrdersController::resolveOpenKode()`,
     * diduplikasi secara sengaja -- bukan diekstrak jadi service baru, mengikuti pola
     * duplikasi terkontrol yang sudah dipakai di modul lain seperti item-search
     * Trbmasuk vs Orders) lalu redirect ke `inventory.orders.create` yang SUDAH
     * membaca kode draft terbuka yang sama -- pengguna melanjutkan mengisi
     * form pemesanan yang sudah ada dan teruji, bukan form duplikat.
     *
     * **Picker Supplier di halaman Estimasi TIDAK diporting** -- legacy menangkap
     * `$_POST['id_supplier']` dari modal pemilihan supplier tapi TIDAK PERNAH benar-benar
     * memakainya di mana pun dalam `aksi_simpan_orders.php` (tidak disimpan ke tabel apa
     * pun di titik ini -- supplier sungguhan baru diisi belakangan di form Pesan Barang
     * itu sendiri). UI modal itu murni dekoratif/tidak berfungsi di legacy, jadi tidak
     * direplikasi -- pemilihan supplier tetap terjadi, hanya di layar berikutnya
     * (form Pesan Barang) yang memang sudah punya picker supplier sendiri.
     */
    public function index()
    {
        return view('inventory.stok-kritis.index', ['judul' => 'Inventory']);
    }

    /** Ports act=kritis30 -- refresh cache barang.t30/q30 untuk SEMUA barang. */
    public function recompute()
    {
        $today = now()->toDateString();
        $tglAwal30 = now()->copy()->subDays(30)->toDateString();

        DB::statement('
            UPDATE barang b
            LEFT JOIN (
                SELECT td.kd_barang, COUNT(*) AS t30, COALESCE(SUM(td.qty_dtrkasir), 0) AS q30
                FROM trkasir_detail td
                JOIN trkasir t ON t.kd_trkasir = td.kd_trkasir
                WHERE t.tgl_trkasir BETWEEN ? AND ?
                GROUP BY td.kd_barang
            ) s ON s.kd_barang = b.kd_barang
            SET b.t30 = COALESCE(s.t30, 0),
                b.q30 = COALESCE(s.q30, 0)
        ', [$tglAwal30, $today]);

        return redirect()->route('inventory.stok-kritis.estimasi')->with('success', 'Analisa T30/Q30 berhasil diperbarui.');
    }

    /** Ports act=tampil30 -- Estimasi Stok Kritis (t30>0 dan stok <= 25% dari t30). */
    public function estimasi()
    {
        $rows = Product::query()
            ->where('t30', '>', 0)
            ->whereRaw('stok_barang <= (0.25 * t30)')
            ->orderBy('nm_barang')
            ->get(['id_barang', 'kd_barang', 'nm_barang', 'jenisobat', 'stok_barang', 'sat_barang', 't30', 'q30'])
            ->map(function ($barang) {
                $barang->sfc_max30 = (float) $barang->t30 - (float) $barang->stok_barang;
                $barang->sfc_max_week = round(((float) $barang->q30 - (float) $barang->stok_barang) / 4);
                [$barang->kategori_label, $barang->kategori_color] = $this->kategori((int) $barang->t30);

                return $barang;
            });

        return view('inventory.stok-kritis.estimasi', [
            'judul' => 'Inventory',
            'rows' => $rows,
        ]);
    }

    /** Ports act=over30 -- Overstok (t30>=0 dan stok > 2x q30). */
    public function overstok()
    {
        $rows = Product::query()
            ->where('t30', '>=', 0)
            ->whereRaw('stok_barang > (2 * q30)')
            ->orderBy('nm_barang')
            ->get(['id_barang', 'kd_barang', 'nm_barang', 'jenisobat', 'stok_barang', 'sat_barang', 't30', 'q30'])
            ->map(function ($barang) {
                $barang->on_t = (float) $barang->stok_barang - (float) $barang->t30;
                [$barang->kategori_label, $barang->kategori_color] = $this->kategori((int) $barang->t30);

                return $barang;
            });

        return view('inventory.stok-kritis.overstok', [
            'judul' => 'Inventory',
            'rows' => $rows,
        ]);
    }

    /** Ports mod_laporan/cetak_stokkritis.php -- Excel, kriteria sama dengan estimasi(). */
    public function estimasiExcel()
    {
        $rows = Product::query()
            ->where('t30', '>', 0)
            ->whereRaw('stok_barang <= (0.25 * t30)')
            ->orderBy('nm_barang')
            ->get(['nm_barang', 'stok_barang', 'sat_barang', 't30', 'q30'])
            ->map(function ($barang) {
                $barang->sfc_max30 = (float) $barang->t30 - (float) $barang->stok_barang;
                $barang->sfc_max_week = round(((float) $barang->q30 - (float) $barang->stok_barang) / 4);
                [$barang->kategori_label] = $this->kategori((int) $barang->t30);

                return $barang;
            });

        $admin = Auth::guard('admin')->user();

        return response()->view('inventory.stok-kritis.estimasi-excel', [
            'rows' => $rows,
            'printedBy' => $admin->nama_lengkap ?? '',
        ], 200, [
            'Content-Type' => 'application/vnd-ms-excel',
            'Content-Disposition' => 'attachment; filename="Data_barang.xls"',
        ]);
    }

    /** Ports aksi_simpan_orders.php -- bulk tambah item terpilih ke draft Pesan Barang. */
    public function addToOrder(Request $request)
    {
        $validated = $request->validate([
            'kd_barang' => 'required|array|min:1',
            'kd_barang.*' => 'string|exists:barang,kd_barang',
        ]);

        $barangList = Product::whereIn('kd_barang', $validated['kd_barang'])->get()->keyBy('kd_barang');

        foreach ($validated['kd_barang'] as $kdBarang) {
            $barang = $barangList->get($kdBarang);
            if (!$barang || (float) $barang->konversi <= 0) {
                return back()->withErrors(['kd_barang' => 'Konversi harus lebih besar dari 0 untuk item ' . ($barang->nm_barang ?? $kdBarang) . '.']);
            }
        }

        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($validated, $barangList, $admin) {
            $kode = $this->resolveOpenOrderKode($admin->id_admin);

            foreach ($validated['kd_barang'] as $kdBarang) {
                $barang = $barangList->get($kdBarang);
                $qtyRetail = (float) $barang->t30 - (float) $barang->stok_barang;
                $konversi = (float) $barang->konversi;

                SupplierOrderDetail::create([
                    'kd_trbmasuk' => $kode,
                    'id_barang' => $barang->id_barang,
                    'kd_barang' => $barang->kd_barang,
                    'nmbrg_dtrbmasuk' => $barang->nm_barang,
                    'qty_dtrbmasuk' => $qtyRetail,
                    'sat_dtrbmasuk' => $barang->sat_barang,
                    'hrgsat_dtrbmasuk' => $barang->hrgsat_barang,
                    'hrgjual_dtrbmasuk' => $barang->hrgjual_barang,
                    'hnasat_dtrbmasuk' => $barang->hna,
                    'hrgttl_dtrbmasuk' => (float) $barang->hrgsat_barang * $qtyRetail,
                    'konversi' => $konversi,
                    'satgrosir_dtrbmasuk' => $barang->sat_grosir,
                    'qtygrosir_dtrbmasuk' => $qtyRetail / $konversi,
                    'diskon' => 0,
                    'no_batch' => '',
                ]);
            }
        });

        return redirect()->route('inventory.orders.create')->with('success', 'Item berhasil ditambahkan ke draft pesanan.');
    }

    /** Duplikasi sengaja dari InventoryOrdersController::resolveOpenKode() -- lihat catatan kelas. */
    private function resolveOpenOrderKode(int $idAdmin): string
    {
        $existing = Kdbm::where('id_admin', $idAdmin)
            ->where('id_resto', 'pesan')
            ->where('stt_kdbm', 'ON')
            ->first();

        if ($existing) {
            return $existing->kd_trbmasuk;
        }

        $kode = 'ORD-' . now()->format('dmyhis');
        if (Kdbm::where('kd_trbmasuk', $kode)->exists()) {
            $kode = 'ORD-' . now()->addSecond()->format('dmyhis');
        }

        Kdbm::create(['kd_trbmasuk' => $kode, 'id_resto' => 'pesan', 'id_admin' => $idAdmin]);

        return $kode;
    }

    /** @return array{0: string, 1: string} [label, warna] -- sama seperti bucket mstok. */
    private function kategori(int $t30): array
    {
        if ($t30 <= 0) {
            return ['MACET', '#dd4b39'];
        }
        if ($t30 <= 5) {
            return ['SLOW', '#f39c12'];
        }
        if ($t30 <= 10) {
            return ['LANCAR', '#00a65a'];
        }

        return ['LAKU', '#00c0ef'];
    }
}
