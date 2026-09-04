<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Batch;
use App\Models\Bundle;
use App\Models\BundleDetail;
use App\Models\CaraBayar;
use App\Models\KartuStok;
use App\Models\Kdtk;
use App\Models\KomisiPegawai;
use App\Models\Pelanggan;
use App\Models\PoinPelanggan;
use App\Models\Product;
use App\Models\Setheader;
use App\Models\Trkasir;
use App\Models\TrkasirDetail;
use App\Models\TrkasirDetailHist;
use App\Models\TrkasirDetailUbahQty;
use App\Models\TrkasirRestore;
use App\Models\WaktuKerja;
use App\Support\Pdf\RPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryTrkasirController extends Controller
{
    /**
     * Modul "Penjualan/Kasir" (module=trkasir, flag 'tpk'), mengikuti
     * public/apotekberlian/masuk/modul/mod_trkasir/*.php. Modul terpanjang di seluruh
     * port ini -- FASE 1 (dibangun di sini): layar kasir inti -- buka draft (kdtk, pola
     * identik kdbm), tambah/gabung item ke keranjang (barang biasa + bundle, alokasi
     * batch FEFO otomatis), edit qty/resep inline, hapus baris (termasuk cascade semua
     * komponen bundle), member/poin, lalu finalisasi transaksi (Simpan Transaksi) +
     * cetak struk. FASE 2 (menyusul terpisah): cari/ubah transaksi tersimpan, item
     * terhapus, undo transaksi terhapus, riwayat perubahan (perubahantrkasir). FASE 3:
     * laporan (stok macet, dll). "Penjualan Sebelumnya" (module=penjualansebelumnya,
     * flag 'penjualansebelum') SENGAJA di luar cakupan modul ini -- modul terpisah,
     * persis seperti byrkredit terpisah dari trbmasuk.
     *
     * === Perbaikan bug nyata dari legacy (disetujui pengguna, "perbaiki semua") ===
     * 1. Komisi kuadrat: legacy mengalikan qty dua kali (sekali saat ambil rate per unit,
     *    sekali lagi saat hitung total) -- di sini trkasir_detail.komisi HANYA menyimpan
     *    rate per unit, dan setiap total komisi (komisi_pegawai.ttl_komisi) SELALU
     *    rate x qty tepat sekali.
     * 2. Poin redeem tidak divalidasi terhadap poin yang tersedia -- divalidasi di
     *    store() sebelum poin pelanggan dikurangi.
     * 3. Stok barang biasa (non-bundle) tidak divalidasi kecukupannya sama sekali di
     *    legacy (hanya bundle yang divalidasi) -- di sini SEMUA jenis barang divalidasi
     *    lewat UPDATE atomic bersyarat (WHERE stok_barang >= qty), item maupun bundle.
     * 4. Race condition read-then-write di update qty inline (legacy) -- diganti UPDATE
     *    atomic (increment/decrement).
     * 5. Kolom disc tidak ikut ter-update saat qty digabung ke baris existing (legacy)
     *    -- di sini selalu ikut disegarkan bersama total.
     * 6. Komisi tidak ikut dihapus saat hapus baris bundle (legacy hanya menghapusnya
     *    untuk baris non-bundle) -- di sini dihapus untuk kedua jalur lewat satu helper
     *    reverseDetailRow() yang sama.
     * 7. Riwayat hapus (trkasir_detail_hist) tidak konsisten kolomnya antara jalur item
     *    biasa vs bundle di legacy (modal/profit/resep hilang di jalur item biasa) --
     *    di sini satu jalur, satu bentuk kolom untuk keduanya.
     * 8. Restore stok saat hapus baris bundle dikunci ke kd_barang (bisa NULL/salah
     *    baris kalau bundle_detail sudah berubah) di legacy -- di sini SELALU dikunci ke
     *    id_barang milik baris trkasir_detail itu sendiri (bukan hasil lookup ulang).
     * 9. Delete batch ledger saat hapus baris item biasa tidak ikut memfilter kd_barang
     *    di legacy (berisiko ikut menghapus baris batch sibling yang no_batch-nya sama)
     *    -- di sini selalu ikut memfilter kd_barang, konsisten dengan jalur bundle.
     * 10. Diskon dihitung ulang bertingkat di client (field subtotal ditimpa berulang
     *     oleh %, lalu nominal, lalu bisa dobel kalau Enter ditekan dua kali) -- di sini
     *     total akhir SELALU dihitung ulang di server dari subtotal murni (SUM baris
     *     trkasir_detail) + diskon1(%) + diskon2(nominal) + redeem_poin, client hanya
     *     menampilkan pratinjau, nilai yang benar-benar disimpan selalu hasil hitungan
     *     server.
     * 11. Cek shift terbuka di legacy hanya di render-time (halaman tambah) -- di sini
     *     DIVALIDASI ULANG di server saat store() (finalisasi), supaya tab lama/replay
     *     tidak bisa menyelesaikan transaksi setelah shift ditutup.
     * 12. jenistx di header legacy diambil dari GROUP BY non-deterministic (SQL rawan
     *     injeksi pula) -- di sini langsung dari field 'jenistx' yang dipilih user di
     *     form, tidak ada query tebak-tebakan.
     * SENGAJA TIDAK diporting (bukan bug, tapi kejutan/kerumitan yang tidak diminta):
     * -- "grace period" 1 bulan pertama yang diam-diam menimpa harga katalog barang saat
     *    nambah ke keranjang; harga katalog hanya diubah lewat modul Barang.
     * -- pemilihan batch manual per baris (sumber 2 bug batch-merge legacy) -- alokasi
     *    batch FEFO di sini SELALU otomatis per (kd_barang, qty), tidak ada input manual
     *    no_batch, sehingga kelas bug itu tidak mungkin muncul lagi.
     * -- keranjang terpecah jadi beberapa baris untuk satu barang yang sama akibat satu
     *    add-to-cart kebetulan kena split FEFO lintas-batch (legacy) -- di sini SELALU
     *    satu baris keranjang per (kd_trkasir, kd_barang); alokasi FEFO tetap terjadi di
     *    ledger batch di baliknya, tidak memecah tampilan keranjang.
     *
     * === Pola keamanan identitas lintas-tab (permintaan pengguna) ===
     * Session Laravel (seperti PHP native) berlaku per BROWSER, bukan per TAB -- login
     * kedua di tab lain akan menimpa identitas Auth::guard('admin') untuk SEMUA tab di
     * browser yang sama. Form create() di sini membekukan id_admin (siapa yang membuka
     * draft transaksi ini) ke hidden field saat halaman pertama kali dirender; SETIAP
     * endpoint tulis (detailStore/detailUpdateQty/detailDestroy/store) mengambil "siapa
     * yang mengerjakan baris ini" dari id_admin yang DIKIRIM form itu (divalidasi lewat
     * resolveActingAdmin(), bukan dari Auth::guard('admin')->user() yang dibaca ulang
     * saat itu juga) -- supaya transaksi tab lama tidak diam-diam "direbut" atas nama
     * siapa pun yang belakangan login di tab lain pada browser yang sama. Middleware
     * auth:admin/inventory.module tetap mengontrol AKSES (siapa yang login sekarang boleh
     * membuka modul ini) -- tidak berubah, hanya ATRIBUSI baris yang dibekukan.
     */

    // ==================== DAFTAR TRANSAKSI HARI INI ====================

    public function index()
    {
        return view('inventory.trkasir.index', ['judul' => 'Inventory']);
    }

    /**
     * Kolom Aksi mengikuti dropdown penjualanhariini_serverside.php legacy: EDIT & HAPUS
     * hanya untuk pemilik (dicek server-side juga di edit()/update()/destroy(), bukan
     * cuma disembunyikan di sini), PRINT/KWITANSI/INVOICE/ETIKET untuk semua role.
     *
     * Field totalKasir/totalTunai.../totalTempo... (lewat ->with(), fitur bawaan Yajra
     * untuk menambah key level-atas di luar `data`) mengikuti "Ringkasan Transaksi" milik
     * penjualanhariini_serverside.php -- SUM(ttl_trkasir) per id_carabayar (1=Tunai,
     * 2=Transfer, 3=Tempo, dikonfirmasi cocok dengan tabel carabayar produksi) x shift
     * (1=Pagi, 2=Sore), untuk transaksi HARI INI. Beda dari legacy: di sana ringkasan ini
     * ikut menyempit kalau kotak pencarian DataTables dipakai (query SUM-nya memakai WHERE
     * yang sama dengan daftar yang sedang difilter) -- di sini SENGAJA selalu total
     * seluruh hari ini apa adanya, tidak ikut mengecil saat mencari satu transaksi
     * tertentu, supaya ringkasan shift tidak membingungkan kasir yang sedang mencari
     * sesuatu di kotak pencarian.
     */
    public function data()
    {
        $admin = Auth::guard('admin')->user();
        $isPemilik = $admin && $admin->isPemilik();
        $tanggal = now()->toDateString();

        $query = Trkasir::query()->where('tgl_trkasir', $tanggal)->orderByDesc('id_trkasir');

        $summary = Trkasir::query()->where('tgl_trkasir', $tanggal)->selectRaw("
                COALESCE(SUM(ttl_trkasir), 0) as total_kasir,
                COALESCE(SUM(CASE WHEN id_carabayar = 1 THEN ttl_trkasir ELSE 0 END), 0) as total_tunai,
                COALESCE(SUM(CASE WHEN id_carabayar = 1 AND shift = 1 THEN ttl_trkasir ELSE 0 END), 0) as total_tunai_pagi,
                COALESCE(SUM(CASE WHEN id_carabayar = 1 AND shift = 2 THEN ttl_trkasir ELSE 0 END), 0) as total_tunai_sore,
                COALESCE(SUM(CASE WHEN id_carabayar = 2 THEN ttl_trkasir ELSE 0 END), 0) as total_transfer,
                COALESCE(SUM(CASE WHEN id_carabayar = 2 AND shift = 1 THEN ttl_trkasir ELSE 0 END), 0) as total_transfer_pagi,
                COALESCE(SUM(CASE WHEN id_carabayar = 2 AND shift = 2 THEN ttl_trkasir ELSE 0 END), 0) as total_transfer_sore,
                COALESCE(SUM(CASE WHEN id_carabayar = 3 THEN ttl_trkasir ELSE 0 END), 0) as total_tempo,
                COALESCE(SUM(CASE WHEN id_carabayar = 3 AND shift = 1 THEN ttl_trkasir ELSE 0 END), 0) as total_tempo_pagi,
                COALESCE(SUM(CASE WHEN id_carabayar = 3 AND shift = 2 THEN ttl_trkasir ELSE 0 END), 0) as total_tempo_sore
            ")->first();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('ttl_trkasir', fn ($row) => number_format($row->ttl_trkasir, 0, ',', '.'))
            ->addColumn('shift_label', fn ($row) => ['1' => 'Pagi', '2' => 'Siang', '3' => 'Malam'][(string) $row->shift] ?? $row->shift)
            ->addColumn('nm_carabayar', fn ($row) => optional(CaraBayar::find($row->id_carabayar))->nm_carabayar)
            ->addColumn('aksi', function ($row) use ($isPemilik) {
                $html = '<div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                    <div class="dropdown-menu p-2 shadow" style="min-width:170px;">';

                if ($isPemilik) {
                    $html .= '<a href="' . route('inventory.trkasir.edit', $row->id_trkasir) . '" class="btn btn-warning btn-sm w-100 mb-1">Edit</a>';
                }

                $html .= '<a href="' . route('inventory.trkasir.struk', $row->id_trkasir) . '" target="_blank" class="btn btn-info btn-sm w-100 mb-1">Print</a>
                    <a href="' . route('inventory.trkasir.kwitansi', $row->id_trkasir) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">Kwitansi</a>
                    <a href="' . route('inventory.trkasir.invoice', $row->id_trkasir) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">Invoice</a>
                    <a href="' . route('inventory.trkasir.etiket', $row->id_trkasir) . '" target="_blank" class="btn btn-secondary btn-sm w-100 mb-1">Etiket</a>';

                if ($isPemilik) {
                    $html .= '<form action="' . route('inventory.trkasir.destroy', $row->id_trkasir) . '" method="POST" id="delete-trkasir-' . $row->id_trkasir . '">
                        ' . csrf_field() . method_field('DELETE') . '
                        <button type="button" onclick="confirmDelete(\'delete-trkasir-' . $row->id_trkasir . '\', \'transaksi ' . e($row->kd_trkasir) . '\')" class="btn btn-danger btn-sm w-100">Hapus</button>
                    </form>';
                }

                $html .= '</div></div>';

                return $html;
            })
            ->with([
                'totalKasir' => (int) $summary->total_kasir,
                'totalTunai' => (int) $summary->total_tunai,
                'totalTunaiPagi' => (int) $summary->total_tunai_pagi,
                'totalTunaiSore' => (int) $summary->total_tunai_sore,
                'totalTransfer' => (int) $summary->total_transfer,
                'totalTransferPagi' => (int) $summary->total_transfer_pagi,
                'totalTransferSore' => (int) $summary->total_transfer_sore,
                'totalTempo' => (int) $summary->total_tempo,
                'totalTempoPagi' => (int) $summary->total_tempo_pagi,
                'totalTempoSore' => (int) $summary->total_tempo_sore,
            ])
            ->rawColumns(['aksi'])
            ->make(true);
    }

    // ==================== TAMBAH PENJUALAN ====================

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        $waktuKerja = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->first();
        if (!$waktuKerja) {
            return redirect()->route('inventory.trkasir.index')
                ->with('error', 'Shift kasir belum dibuka, Silahkan buka shift kasir terlebih dahulu !');
        }

        return view('inventory.trkasir.create', [
            'judul' => 'Inventory',
            'kdTransaksi' => $this->resolveKdtk($admin->id_admin),
            'admin' => $admin,
            'petugasList' => Admin::where('id_admin', '!=', $admin->id_admin)->orderBy('nama_lengkap')->get(['id_admin', 'nama_lengkap']),
            'carabayarList' => CaraBayar::orderBy('urutan')->get(),
        ]);
    }

    public function detailIndex(Request $request)
    {
        $kdTrkasir = (string) $request->query('kd_trkasir', '');
        $rows = TrkasirDetail::where('kd_trkasir', $kdTrkasir)->orderBy('id_dtrkasir')->get();

        return view('inventory.trkasir.partials.detail-table', [
            'kdTrkasir' => $kdTrkasir,
            'rows' => $rows,
            'subtotal' => $rows->sum('hrgttl_dtrkasir'),
            'carabayarList' => CaraBayar::orderBy('urutan')->get(),
        ]);
    }

    /**
     * Tambah/gabung item (barang biasa atau bundle) ke keranjang, mengikuti
     * simpandetail_trkasir.php (jalur add/merge -- bukan jalur "direct update by id"
     * legacy, yang di sini digantikan seluruhnya oleh detailUpdateQty()).
     */
    public function detailStore(Request $request)
    {
        $validated = $request->validate([
            'kd_trkasir' => 'required|string|max:100',
            'id_barang' => 'required|integer',
            'kd_barang' => 'required|string|max:50',
            'nmbrg_dtrkasir' => 'required|string|max:100',
            'qty_dtrkasir' => 'required|numeric|gt:0',
            'sat_dtrkasir' => 'required|string|max:30',
            'hrgjual_dtrkasir' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'resep' => 'nullable|in:YA,TIDAK',
            'tipe' => 'required|integer|min:1|max:3',
            // no_batch: dari #ModalBatch (cari-batch, mengikuti batch_serverside.php) atau
            // diketik manual -- kosong berarti "otomatis" (lihat fefoAllocate()). max:10
            // mengikuti batas kolom batch.no_batch (varchar(10)).
            'no_batch' => 'nullable|string|max:10',
            // Petugas Pelayanan -- kalau tidak dipilih, kasir (id_admin) sendiri yang jadi
            // default (lihat resolvePetugasPelayanan()).
            'id_user' => 'nullable|integer|exists:admin,id_admin',
            'id_admin' => 'required|integer',
        ]);

        $admin = $this->resolveActingAdmin($request);
        $petugas = $this->resolvePetugasPelayanan($validated['id_user'] ?? null, $admin);
        $disc = $validated['disc'] ?? 0;
        $resep = $validated['resep'] ?? 'TIDAK';
        $isBundle = str_starts_with($validated['kd_barang'], 'BUND');

        DB::transaction(function () use ($validated, $admin, $petugas, $disc, $resep, $isBundle) {
            if ($isBundle) {
                $this->tambahBundle($validated, $admin, $petugas, $disc);
            } else {
                $this->tambahBarang($validated, $admin, $petugas, $disc, $resep);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Tambah barang biasa (bukan bundle) ke keranjang. Mengikuti pola legacy: no_batch
     * SELALU satu paket dengan exp_date. Kalau operator mengisi no_batch (dipilih lewat
     * #ModalBatch, mengikuti batch_serverside.php, atau diketik manual), qty dipotong
     * KHUSUS dari batch itu (resolveManualBatch()). Kalau dikosongkan, sistem otomatis
     * memilih batch dengan expired date terdekat (FEFO) dan memotong qty dari situ --
     * kalau batch terdekat itu sendiri tidak cukup, sisanya otomatis diambilkan dari
     * batch berikutnya (fefoAllocate()), sehingga qty yang diminta tetap selalu terpenuhi
     * tanpa oversell. Setiap batch yang benar-benar dipakai (baik manual maupun FEFO)
     * menghasilkan SATU baris keranjang tersendiri (no_batch berbeda = baris berbeda,
     * digabung kalau no_batch-nya sama), supaya no_batch/exp_date yang tampil di
     * keranjang & struk selalu akurat -- bukan sekadar catatan ledger di balik layar.
     *
     * Komisi & atribusi trkasir_detail.idadmin SELALU mengikuti $petugas (Petugas
     * Pelayanan yang dipilih, atau kasir/$admin sendiri kalau tidak dipilih -- lihat
     * resolvePetugasPelayanan()) -- BUKAN $admin (kasir/session operator) begitu saja.
     * $admin di sini tetap dipakai untuk jejak audit qty (catatUbahQty()), bukan atribusi
     * komisi.
     */
    private function tambahBarang(array $validated, Admin $admin, Admin $petugas, float $disc, string $resep): void
    {
        $qty = (float) $validated['qty_dtrkasir'];
        $hrgJual = (float) $validated['hrgjual_dtrkasir'];
        $noBatch = trim((string) ($validated['no_batch'] ?? ''));

        $this->kurangiStokAtauGagal($validated['id_barang'], $qty);

        $alokasi = $noBatch !== ''
            ? [$this->resolveManualBatch($validated['kd_barang'], $noBatch, $qty)]
            : $this->fefoAllocate($validated['kd_barang'], $qty);

        $barang = Product::find($validated['id_barang']);
        $modal = (float) ($barang->hrgsat_barang ?? 0);
        $komisiPerUnit = $this->komisiPerUnit($barang, $petugas);
        $hrgDisc = $hrgJual * (1 - ($disc / 100));

        foreach ($alokasi as $a) {
            Batch::create([
                'tgl_transaksi' => now(),
                'no_batch' => $a['no_batch'],
                'exp_date' => $a['exp_date'] ?? '9999-12-31',
                'qty' => $a['qty'],
                'satuan' => $validated['sat_dtrkasir'],
                'kd_transaksi' => $validated['kd_trkasir'],
                'kd_barang' => $validated['kd_barang'],
                'status' => 'keluar',
            ]);

            $existing = TrkasirDetail::where('kd_trkasir', $validated['kd_trkasir'])
                ->where('kd_barang', $validated['kd_barang'])
                ->where('kd_bundle', '')
                ->where('no_batch', $a['no_batch'])
                ->first();

            if ($existing) {
                $qtyBaru = $existing->qty_dtrkasir + $a['qty'];
                $totalBaru = $qtyBaru * $hrgDisc;

                $existing->update([
                    'qty_dtrkasir' => $qtyBaru,
                    'hrgjual_dtrkasir' => $hrgJual,
                    'disc' => $disc,
                    'modal' => $modal,
                    'profit' => $totalBaru - ($modal * $qtyBaru),
                    'hrgttl_dtrkasir' => $totalBaru,
                    'komisi' => $komisiPerUnit,
                    'idadmin' => $petugas->id_admin,
                    'resep' => $resep,
                ]);

                $this->catatUbahQty($existing, $existing->qty_dtrkasir - $a['qty'], $existing->hrgttl_dtrkasir, $totalBaru, $admin);
                $this->sesuaikanKomisi($existing->id_dtrkasir, $validated['kd_trkasir'], $petugas->id_admin, $komisiPerUnit * $qtyBaru, true);
            } else {
                $total = $a['qty'] * $hrgDisc;

                $detail = TrkasirDetail::create([
                    'kd_trkasir' => $validated['kd_trkasir'],
                    'id_barang' => $validated['id_barang'],
                    'kd_barang' => $validated['kd_barang'],
                    'nmbrg_dtrkasir' => $validated['nmbrg_dtrkasir'],
                    'qty_dtrkasir' => $a['qty'],
                    'sat_dtrkasir' => $validated['sat_dtrkasir'],
                    'hrgjual_dtrkasir' => $hrgJual,
                    'disc' => $disc,
                    'modal' => $modal,
                    'profit' => $total - ($modal * $a['qty']),
                    'hrgttl_dtrkasir' => $total,
                    'no_batch' => $a['no_batch'],
                    'exp_date' => $a['exp_date'],
                    'tipe' => $validated['tipe'],
                    'komisi' => $komisiPerUnit,
                    'idadmin' => $petugas->id_admin,
                    'tipetx' => 1,
                    'resep' => $resep,
                    'kd_bundle' => '',
                    'nm_bundle' => '',
                ]);

                $this->sesuaikanKomisi($detail->id_dtrkasir, $validated['kd_trkasir'], $petugas->id_admin, $komisiPerUnit * $a['qty'], false);
            }
        }
    }

    private function tambahBundle(array $validated, Admin $admin, Admin $petugas, float $disc): void
    {
        $bundle = Bundle::where('kd_bundle', $validated['kd_barang'])->firstOrFail();
        $components = BundleDetail::where('kd_bundle', $bundle->kd_bundle)->get();
        abort_if($components->isEmpty(), 422, 'Data komponen bundle tidak ditemukan.');

        $qtyBundle = (float) $validated['qty_dtrkasir'];

        // Validasi stok SEMUA komponen dulu sebelum memotong apa pun (all-or-nothing).
        foreach ($components as $component) {
            $butuh = $component->qty_barang * $qtyBundle;
            $stokBarang = (float) (Product::where('id_barang', $component->id_barang)->value('stok_barang') ?? 0);
            abort_if($stokBarang < $butuh, 422, "Stok tidak mencukupi untuk komponen bundle: {$component->nm_barang}.");
        }

        Bundle::where('id_bundle', $bundle->id_bundle)->where('qty_bundle', '>=', $qtyBundle)->decrement('qty_bundle', $qtyBundle);

        foreach ($components as $component) {
            $butuh = $component->qty_barang * $qtyBundle;
            $hrgDisc = $component->hrgjual_barang * (1 - ($disc / 100));

            $this->kurangiStokAtauGagal($component->id_barang, $butuh);
            $this->alokasikanBatchKeluar($validated['kd_trkasir'], $component->kd_barang, $butuh);

            $barang = Product::find($component->id_barang);
            $modal = (float) ($barang->hrgsat_barang ?? 0);
            $komisiPerUnit = $this->komisiPerUnit($barang, $petugas);

            $existing = TrkasirDetail::where('kd_trkasir', $validated['kd_trkasir'])
                ->where('kd_bundle', $bundle->kd_bundle)
                ->where('kd_barang', $component->kd_barang)
                ->first();

            if ($existing) {
                $qtyBaru = $existing->qty_dtrkasir + $butuh;
                $totalBaru = $qtyBaru * $hrgDisc;

                $existing->update([
                    'qty_dtrkasir' => $qtyBaru,
                    'disc' => $disc,
                    'modal' => $modal,
                    'profit' => $totalBaru - ($modal * $qtyBaru),
                    'hrgttl_dtrkasir' => $totalBaru,
                    'komisi' => $komisiPerUnit,
                    'idadmin' => $petugas->id_admin,
                ]);

                $this->catatUbahQty($existing, $existing->qty_dtrkasir - $butuh, $existing->hrgttl_dtrkasir, $totalBaru, $admin);
                $this->sesuaikanKomisi($existing->id_dtrkasir, $validated['kd_trkasir'], $petugas->id_admin, $komisiPerUnit * $qtyBaru, true);
            } else {
                $total = $butuh * $hrgDisc;

                $detail = TrkasirDetail::create([
                    'kd_trkasir' => $validated['kd_trkasir'],
                    'id_barang' => $component->id_barang,
                    'kd_barang' => $component->kd_barang,
                    'nmbrg_dtrkasir' => $component->nm_barang,
                    'qty_dtrkasir' => $butuh,
                    'sat_dtrkasir' => $component->sat_barang,
                    'hrgjual_dtrkasir' => $component->hrgjual_barang,
                    'disc' => $disc,
                    'modal' => $modal,
                    'profit' => $total - ($modal * $butuh),
                    'hrgttl_dtrkasir' => $total,
                    'no_batch' => '',
                    'exp_date' => null,
                    'tipe' => $validated['tipe'],
                    'komisi' => $komisiPerUnit,
                    'idadmin' => $petugas->id_admin,
                    'tipetx' => 1,
                    'resep' => 'TIDAK',
                    'kd_bundle' => $bundle->kd_bundle,
                    'nm_bundle' => $bundle->nm_bundle,
                ]);

                $this->sesuaikanKomisi($detail->id_dtrkasir, $validated['kd_trkasir'], $petugas->id_admin, $komisiPerUnit * $butuh, false);
            }
        }
    }

    /**
     * Edit inline Qty/Resep (AJAX), mengikuti update_detail_inline.php -- diperbaiki:
     * UPDATE atomic (bukan read-then-write), plus catat ke trkasir_detail_ubah_qty
     * (di legacy endpoint ini tidak pernah mencatat audit sama sekali) dan sesuaikan
     * komisi_pegawai secara linear mengikuti selisih qty. Ledger `batch` milik baris ini
     * SENGAJA tidak ikut disesuaikan di sini (sama seperti legacy) -- `barang.stok_barang`
     * (diupdate atomic di atas) tetap sumber kebenaran utama; kalau baris ini pernah
     * dihapus setelah qty-nya diedit di sini, reverseDetailRow() tetap mengembalikan
     * SELURUH stok baris ini dengan benar (dari qty_dtrkasir saat itu), hanya saja jumlah
     * yang dihapus dari ledger `batch` untuk no_batch tsb mungkin sedikit meleset dari
     * qty riil pasca-edit.
     */
    public function detailUpdateQty(Request $request, TrkasirDetail $detail)
    {
        $validated = $request->validate([
            'qty_dtrkasir' => 'required|numeric|gt:0',
            'resep' => 'nullable|in:YA,TIDAK',
            'id_admin' => 'required|integer',
        ]);

        $admin = $this->resolveActingAdmin($request);

        DB::transaction(function () use ($detail, $validated, $admin) {
            $qtyLama = $detail->qty_dtrkasir;
            $qtyBaru = (float) $validated['qty_dtrkasir'];
            $delta = $qtyBaru - $qtyLama;
            $hrgttlLama = $detail->hrgttl_dtrkasir;

            if ($delta > 0) {
                $this->kurangiStokAtauGagal($detail->id_barang, $delta);
            } elseif ($delta < 0) {
                DB::table('barang')->where('id_barang', $detail->id_barang)->increment('stok_barang', abs($delta));
            }

            $hrgDisc = $detail->hrgjual_dtrkasir * (1 - ($detail->disc / 100));
            $totalBaru = round($qtyBaru * $hrgDisc);

            $detail->update([
                'qty_dtrkasir' => $qtyBaru,
                'resep' => $validated['resep'] ?? $detail->resep,
                'hrgttl_dtrkasir' => $totalBaru,
                'profit' => $totalBaru - ($detail->modal * $qtyBaru),
            ]);

            $this->catatUbahQty($detail, $qtyLama, $hrgttlLama, $totalBaru, $admin);

            if ($detail->komisi) {
                // id_admin komisi tetap milik $detail->idadmin (petugas pelayanan yang sudah
                // ditetapkan saat baris ini ditambahkan) -- BUKAN $admin (siapa pun yang
                // sedang mengedit qty baris ini sekarang, bisa jadi orang lain).
                $this->sesuaikanKomisi($detail->id_dtrkasir, $detail->kd_trkasir, $detail->idadmin, $detail->komisi * $qtyBaru, true);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hapus baris keranjang, mengikuti hapusdetail_trkasir.php / hapus_bundletrkasir.php
     * -- digabung jadi satu jalur (reverseDetailRow) supaya kedua jenis baris konsisten
     * (lihat catatan bug #6-#9 di kelas ini).
     */
    public function detailDestroy(Request $request, TrkasirDetail $detail)
    {
        $admin = $this->resolveActingAdmin($request);

        DB::transaction(function () use ($detail, $admin) {
            if ($detail->kd_bundle !== '' && $detail->kd_bundle !== null) {
                $kdBundle = $detail->kd_bundle;
                $rows = TrkasirDetail::where('kd_trkasir', $detail->kd_trkasir)->where('kd_bundle', $kdBundle)->get();

                $qtyBundleRestore = null;
                foreach ($rows as $row) {
                    $bd = BundleDetail::where('kd_bundle', $kdBundle)->where('kd_barang', $row->kd_barang)->first();
                    if ($bd && $bd->qty_barang > 0) {
                        $qtyBundleRestore = $row->qty_dtrkasir / $bd->qty_barang;
                    }
                    $this->reverseDetailRow($row, $admin);
                }

                if ($qtyBundleRestore !== null) {
                    Bundle::where('kd_bundle', $kdBundle)->increment('qty_bundle', $qtyBundleRestore);
                }
            } else {
                $this->reverseDetailRow($detail, $admin);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * $trkasirUntukRestore: kalau diisi, ini bagian dari HAPUS transaksi TERFINALISASI
     * secara utuh (destroy()) -- header transaksi itu masih ada saat helper ini dipanggil
     * (baru dihapus belakangan oleh destroy() sendiri, setelah semua barisnya diproses),
     * jadi sekalian ditulis snapshot ke trkasir_restore (kd_trkasir + tipetx dari sana,
     * bukan dari $row, karena tipetx ada di header bukan di detail). Kalau null (dipanggil
     * dari detailDestroy() -- hapus satu baris keranjang, baik masih draft maupun sudah
     * final), tidak ada snapshot trkasir_restore yang ditulis, persis perilaku Fase 1.
     */
    private function reverseDetailRow(TrkasirDetail $row, Admin $admin, ?Trkasir $trkasirUntukRestore = null): void
    {
        DB::table('barang')->where('id_barang', $row->id_barang)->increment('stok_barang', $row->qty_dtrkasir);

        TrkasirDetailHist::create([
            'kd_trkasir' => $row->kd_trkasir,
            'id_barang' => $row->id_barang,
            'kd_barang' => $row->kd_barang,
            'nmbrg_dtrkasir' => $row->nmbrg_dtrkasir,
            'qty_dtrkasir' => $row->qty_dtrkasir,
            'sat_dtrkasir' => $row->sat_dtrkasir,
            'hrgjual_dtrkasir' => $row->hrgjual_dtrkasir,
            'disc' => $row->disc,
            'modal' => $row->modal,
            'profit' => $row->profit,
            'hrgttl_dtrkasir' => $row->hrgttl_dtrkasir,
            'no_batch' => $row->no_batch ?? '',
            'exp_date' => $row->exp_date,
            'tipe' => $row->tipe,
            'komisi' => $row->komisi,
            'idadmin' => $row->idadmin,
            'tipetx_asal' => $row->tipetx,
            'tipetx_hapus' => $row->tipetx,
            'id_admin_hapus' => $admin->id_admin,
            'resep' => $row->resep,
            'kd_bundle' => $row->kd_bundle ?? '',
            'nm_bundle' => $row->nm_bundle ?? '',
        ]);

        if ($trkasirUntukRestore) {
            TrkasirRestore::create([
                'kd_trkasir' => $trkasirUntukRestore->kd_trkasir,
                'petugas' => $trkasirUntukRestore->petugas,
                'shift' => $trkasirUntukRestore->shift,
                'tgl_trkasir' => $trkasirUntukRestore->tgl_trkasir,
                'nm_pelanggan' => $trkasirUntukRestore->nm_pelanggan,
                'tlp_pelanggan' => $trkasirUntukRestore->tlp_pelanggan,
                'alamat_pelanggan' => $trkasirUntukRestore->alamat_pelanggan,
                'ttl_trkasir' => $trkasirUntukRestore->ttl_trkasir,
                'dp_bayar' => $trkasirUntukRestore->dp_bayar,
                'diskon1' => $trkasirUntukRestore->diskon1,
                'diskon2' => $trkasirUntukRestore->diskon2,
                'sisa_bayar' => $trkasirUntukRestore->sisa_bayar,
                'ket_trkasir' => $trkasirUntukRestore->ket_trkasir,
                'id_carabayar' => $trkasirUntukRestore->id_carabayar,
                'id_dtrkasir' => $row->id_dtrkasir,
                'id_barang' => $row->id_barang,
                'kd_barang' => $row->kd_barang,
                'nmbrg_dtrkasir' => $row->nmbrg_dtrkasir,
                'qty_dtrkasir' => $row->qty_dtrkasir,
                'sat_dtrkasir' => $row->sat_dtrkasir,
                'hrgjual_dtrkasir' => $row->hrgjual_dtrkasir,
                'hrgttl_dtrkasir' => $row->hrgttl_dtrkasir,
                'disc' => $row->disc,
                'resep' => $row->resep,
                'modal' => $row->modal,
                'profit' => $row->profit,
                'no_batch' => $row->no_batch,
                'exp_date' => $row->exp_date,
                'waktu' => $row->waktu,
                'tipe' => $row->tipe,
                'komisi' => $row->komisi,
                'idadmin' => $row->idadmin,
                'kd_bundle' => $row->kd_bundle,
                'nm_bundle' => $row->nm_bundle,
                'tipetx' => $trkasirUntukRestore->tipetx ?? 1,
                'id_admin_hapus' => $admin->id_admin,
                'id_user' => $trkasirUntukRestore->id_user,
                'id_pelanggan' => $trkasirUntukRestore->id_pelanggan,
                'kodetx' => $trkasirUntukRestore->kodetx,
                'jenistx' => $trkasirUntukRestore->jenistx,
                'waktu_trx' => $trkasirUntukRestore->waktu_trx,
                'poin_awal' => $trkasirUntukRestore->poin_awal,
                'tambahan_poin' => $trkasirUntukRestore->tambahan_poin,
                'redeem_poin' => $trkasirUntukRestore->redeem_poin,
            ]);
        }

        // Baris barang biasa SELALU tepat satu no_batch (tambahBarang() memberi baris
        // tersendiri per batch yang dipakai) -- filter presisi per no_batch supaya hapus
        // satu baris tidak ikut menghapus ledger batch milik baris lain punya barang yang
        // sama. Baris bundle-komponen (masih no_batch='' placeholder, lihat tambahBundle())
        // dan baris fallback "tanpa batch" (juga no_batch='') tetap dihapus tanpa filter,
        // karena SEMUA baris `batch` untuk (kd_transaksi, kd_barang) itu pasti hanya
        // berasal dari satu baris keranjang itu saja (tidak mungkin ada baris no_batch=''
        // kedua untuk barang yang sama dalam transaksi yang sama).
        Batch::where('kd_transaksi', $row->kd_trkasir)
            ->where('kd_barang', $row->kd_barang)
            ->where('status', 'keluar')
            ->when($row->no_batch, fn ($q) => $q->where('no_batch', $row->no_batch))
            ->delete();

        KomisiPegawai::where('id_dtrkasir', $row->id_dtrkasir)->delete();

        $row->delete();
    }

    // ==================== UBAH/HAPUS TRANSAKSI TERSIMPAN (FASE 2) ====================

    /**
     * Form ubah header transaksi yang sudah difinalisasi, mengikuti case "ubah" di
     * trkasir.php. Item keranjang tetap diedit lewat endpoint yang sama persis dengan
     * layar Tambah Penjualan (detailIndex/detailStore/detailUpdateQty/detailDestroy --
     * semuanya generik terhadap kd_trkasir, tidak peduli transaksinya draft atau sudah
     * final), jadi tidak ada endpoint baru untuk itu di sini.
     *
     * Perbaikan atas legacy: dropdown Aksi legacy hanya MENYEMBUNYIKAN tombol EDIT/HAPUS
     * dari non-pemilik di UI -- endpoint ubah/ubah_trkasir aslinya bisa diakses siapa saja
     * yang login (tidak ada cek $_SESSION['level'] sama sekali di sana), jadi staf non-
     * pemilik yang tahu URL-nya tetap bisa mengubah transaksi manapun. Di sini ditegakkan
     * juga di server, konsisten dengan HAPUS (yang di legacy memang sudah dicek pemilik).
     */
    public function edit(Trkasir $trkasir)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403, 'Hanya akun pemilik yang dapat mengubah transaksi penjualan yang sudah tersimpan.');

        return view('inventory.trkasir.edit', [
            'judul' => 'Inventory',
            'trkasir' => $trkasir,
            'admin' => $admin,
            'petugasList' => Admin::where('id_admin', '!=', $admin->id_admin)->orderBy('nama_lengkap')->get(['id_admin', 'nama_lengkap']),
            'carabayarList' => CaraBayar::orderBy('urutan')->get(),
        ]);
    }

    /**
     * Simpan ubah header, mengikuti act=ubah_trkasir -- HANYA field header (tanggal,
     * petugas pelayanan, data pelanggan, kode order/keterangan, cara bayar, diskon
     * faktur, jumlah bayar). Total akhir SELALU dihitung ulang di server dari subtotal
     * murni trkasir_detail (sama seperti store(), bukan dipercaya dari client) --
     * redeem_poin transaksi ini SENGAJA tidak ikut diedit di sini (legacy juga tidak
     * pernah menyentuh pelanggan.total_poin saat ubah_trkasir -- membatalkan/menghitung
     * ulang poin dari transaksi yang sudah lama final butuh alur tersendiri, di luar
     * cakupan perbaikan header sederhana ini).
     *
     * Perbaikan atas legacy: ubah_trkasir legacy menimpa trkasir_detail.idadmin SEMUA
     * baris ke id_user yang dipilih di form ini -- persis bug yang sama yang sudah
     * diperbaiki di store() (lihat aturan atribusi komisi 2026-09-04 di doc-block kelas
     * ini): setiap baris keranjang sudah membawa Petugas Pelayanan-nya sendiri sejak
     * ditambahkan, jadi TIDAK ditimpa lagi di sini.
     */
    public function update(Request $request, Trkasir $trkasir)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403, 'Hanya akun pemilik yang dapat mengubah transaksi penjualan yang sudah tersimpan.');

        $validated = $request->validate([
            'id_user' => 'required|integer|exists:admin,id_admin',
            'tgl_trkasir' => 'required|date',
            'id_pelanggan' => 'nullable|integer',
            'nm_pelanggan' => 'nullable|string|max:100',
            'tlp_pelanggan' => 'nullable|string|max:50',
            'alamat_pelanggan' => 'nullable|string',
            'kodetx' => 'nullable|string|max:20',
            'ket_trkasir' => 'nullable|string',
            'id_carabayar' => 'required|integer|exists:carabayar,id_carabayar',
            'diskon1' => 'nullable|numeric|min:0|max:100',
            'diskon2' => 'nullable|numeric|min:0',
            'dp_bayar' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($trkasir, $validated, $admin) {
            $subtotal = (float) TrkasirDetail::where('kd_trkasir', $trkasir->kd_trkasir)->sum('hrgttl_dtrkasir');
            $diskon1 = $validated['diskon1'] ?? 0;
            $diskon2 = $validated['diskon2'] ?? 0;

            $totalAkhir = round($subtotal * (1 - $diskon1 / 100) - $diskon2);
            $totalAkhir = max(0, $totalAkhir - (float) $trkasir->redeem_poin);

            $trkasir->update([
                'tgl_trkasir' => $validated['tgl_trkasir'],
                'id_user' => $validated['id_user'],
                'petugas' => $admin->nama_lengkap,
                'id_pelanggan' => $validated['id_pelanggan'] ?? $trkasir->id_pelanggan,
                'nm_pelanggan' => $validated['nm_pelanggan'] ?? '',
                'tlp_pelanggan' => $validated['tlp_pelanggan'] ?? '',
                'alamat_pelanggan' => $validated['alamat_pelanggan'] ?? '',
                'kodetx' => $validated['kodetx'] ?? '',
                'ket_trkasir' => $validated['ket_trkasir'] ?? '',
                'id_carabayar' => $validated['id_carabayar'],
                'diskon1' => $diskon1,
                'diskon2' => $diskon2,
                'dp_bayar' => $validated['dp_bayar'],
                'ttl_trkasir' => $totalAkhir,
                'sisa_bayar' => $validated['dp_bayar'] - $totalAkhir,
            ]);
        });

        return response()->json(['status' => 'success']);
    }

    /**
     * Hapus total transaksi yang sudah difinalisasi (bukan hapus satu baris keranjang --
     * itu detailDestroy()), mengikuti act=hapus di aksi_trkasir.php: kembalikan stok tiap
     * baris (+ stok bundle), catat ke trkasir_detail_hist DAN trkasir_restore (snapshot
     * lengkap header+baris, dipakai fitur "UNDO TRANSAKSI TERHAPUS" legacy -- tabelnya
     * sudah ada di DB produksi, tidak perlu migration baru), rollback poin pelanggan,
     * hapus header trkasir + kartu_stok, dan ikut hapus order_online/order_online_item
     * kalau transaksi ini berasal dari Market Place (kode_pesanan = kd_trkasir) supaya
     * tidak ada pesanan online yang jadi yatim.
     *
     * Menggunakan ulang reverseDetailRow() (helper yang sama dipakai detailDestroy() di
     * Fase 1) untuk restore stok + insert trkasir_detail_hist + hapus batch/komisi per
     * baris -- reverseDetailRow() sekarang menerima $trkasirUntukRestore opsional yang,
     * kalau diisi, SEKALIAN menulis snapshot trkasir_restore untuk baris itu.
     */
    public function destroy(Trkasir $trkasir)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin->isPemilik(), 403, 'Hanya akun pemilik yang dapat menghapus transaksi penjualan.');

        DB::transaction(function () use ($trkasir, $admin) {
            $kdTrkasir = $trkasir->kd_trkasir;
            $detailRows = TrkasirDetail::where('kd_trkasir', $kdTrkasir)->get();

            $bundleRestore = [];

            foreach ($detailRows as $row) {
                $kdBundle = trim((string) $row->kd_bundle);
                if (str_starts_with($kdBundle, 'BUND')) {
                    $bd = BundleDetail::where('kd_bundle', $kdBundle)->where('kd_barang', $row->kd_barang)->first();
                    if ($bd && $bd->qty_barang > 0) {
                        $bundleRestore[$kdBundle] = $row->qty_dtrkasir / $bd->qty_barang;
                    }
                }

                $this->reverseDetailRow($row, $admin, $trkasir);
            }

            foreach ($bundleRestore as $kdBundle => $qtyBundleRestore) {
                Bundle::where('kd_bundle', $kdBundle)->increment('qty_bundle', $qtyBundleRestore);
            }

            if (!empty($trkasir->id_pelanggan)) {
                $pelanggan = Pelanggan::find($trkasir->id_pelanggan);
                if ($pelanggan) {
                    $pelanggan->update([
                        'total_poin' => ($pelanggan->total_poin - (int) ($trkasir->tambahan_poin ?? 0)) + (int) ($trkasir->redeem_poin ?? 0),
                    ]);
                }
            }

            KartuStok::where('kode_transaksi', $kdTrkasir)->delete();

            // Transaksi Market Place berasal dari order_online (kd_trkasir =
            // order_online.kode_pesanan). Ikut dihapus dalam transaction yang sama
            // supaya tidak ada pesanan online yang nyangkut/yatim. Untuk transaksi
            // reguler (bukan Market Place) query ini tidak menemukan apa-apa, aman.
            $orderOnline = DB::table('order_online')->where('kode_pesanan', $kdTrkasir)->first();
            if ($orderOnline) {
                DB::table('order_online_item')->where('order_id', $orderOnline->id)->delete();
                DB::table('order_online')->where('id', $orderOnline->id)->delete();
            }

            $trkasir->delete();
        });

        return redirect()->route('inventory.trkasir.index')->with('success', 'Transaksi penjualan berhasil dihapus.');
    }

    // ==================== FINALISASI (SIMPAN TRANSAKSI) ====================

    /**
     * Finalisasi transaksi (Simpan Transaksi/F3), mengikuti act=input_trkasir di
     * aksi_trkasir.php.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kd_trkasir' => 'required|string|max:100',
            // Kalau tidak dipilih, header ikut default ke kasir sendiri (id_admin) --
            // sama seperti resolvePetugasPelayanan() di detailStore(). Atribusi komisi per
            // baris (trkasir_detail.idadmin) TIDAK ditimpa dari sini lagi -- setiap baris
            // sudah membawa petugas-nya sendiri sejak ditambahkan ke keranjang.
            'id_user' => 'nullable|integer|exists:admin,id_admin',
            'tgl_trkasir' => 'required|date',
            'id_pelanggan' => 'nullable|integer',
            'nm_pelanggan' => 'nullable|string|max:100',
            'tlp_pelanggan' => 'nullable|string|max:50',
            'alamat_pelanggan' => 'nullable|string',
            'kodetx' => 'nullable|string|max:20',
            'ket_trkasir' => 'nullable|string',
            'jenistx' => 'required|integer',
            'id_carabayar' => 'required|integer|exists:carabayar,id_carabayar',
            'diskon1' => 'nullable|numeric|min:0|max:100',
            'diskon2' => 'nullable|numeric|min:0',
            'dp_bayar' => 'required|numeric|min:0',
            'redeem_poin' => 'nullable|integer|min:0',
            'id_admin' => 'required|integer',
        ]);

        $admin = $this->resolveActingAdmin($request);

        $waktuKerja = WaktuKerja::where('tanggal', now()->toDateString())->where('status', 'ON')->first();
        abort_unless($waktuKerja, 422, 'Shift Kasir Belum Dibuka!');

        $itemCount = TrkasirDetail::where('kd_trkasir', $validated['kd_trkasir'])->count();
        abort_if($itemCount < 1, 422, 'Belum ada item dalam transaksi.');
        abort_if(Trkasir::where('kd_trkasir', $validated['kd_trkasir'])->exists(), 422, 'Transaksi ini sudah difinalisasi.');

        $trkasir = DB::transaction(function () use ($validated, $admin, $waktuKerja) {
            $subtotal = (float) TrkasirDetail::where('kd_trkasir', $validated['kd_trkasir'])->sum('hrgttl_dtrkasir');

            $diskon1 = $validated['diskon1'] ?? 0;
            $diskon2 = $validated['diskon2'] ?? 0;
            $redeemPoin = $validated['redeem_poin'] ?? 0;
            $totalAkhir = round($subtotal * (1 - $diskon1 / 100) - $diskon2);
            $totalAkhir = max(0, $totalAkhir - $redeemPoin);

            [$poinAwal, $tambahanPoin] = $this->prosesPoin($validated['id_pelanggan'] ?? null, $redeemPoin, $totalAkhir);
            $idUser = $validated['id_user'] ?? $admin->id_admin;

            $trkasir = Trkasir::create([
                'kd_trkasir' => $validated['kd_trkasir'],
                'id_user' => $idUser,
                'petugas' => $admin->nama_lengkap,
                'shift' => $waktuKerja->shift,
                'tgl_trkasir' => $validated['tgl_trkasir'],
                'id_pelanggan' => $validated['id_pelanggan'] ?? 0,
                'nm_pelanggan' => $validated['nm_pelanggan'] ?? '',
                'tlp_pelanggan' => $validated['tlp_pelanggan'] ?? '',
                'alamat_pelanggan' => $validated['alamat_pelanggan'] ?? '',
                'kodetx' => $validated['kodetx'] ?? '',
                'ttl_trkasir' => $totalAkhir,
                'dp_bayar' => $validated['dp_bayar'],
                'diskon1' => $diskon1,
                'diskon2' => $diskon2,
                'sisa_bayar' => $validated['dp_bayar'] - $totalAkhir,
                'ket_trkasir' => $validated['ket_trkasir'] ?? '',
                'id_carabayar' => $validated['id_carabayar'],
                'jenistx' => $validated['jenistx'],
                'tipetx' => 1,
                'waktu_trx' => now(),
                'poin_awal' => $poinAwal,
                'tambahan_poin' => $tambahanPoin,
                'redeem_poin' => $redeemPoin,
            ]);

            KartuStok::create(['kode_transaksi' => $validated['kd_trkasir'], 'tgl_sekarang' => now()]);

            Kdtk::where('id_admin', $admin->id_admin)->where('kd_trkasir', $validated['kd_trkasir'])->update(['stt_kdtk' => 'OFF']);

            return $trkasir;
        });

        return response()->json(['status' => 'success', 'kd_trkasir' => $trkasir->kd_trkasir, 'id_trkasir' => $trkasir->id_trkasir]);
    }

    private function prosesPoin(?int $idPelanggan, int $redeemPoin, float $totalAkhir): array
    {
        if (!$idPelanggan) {
            abort_if($redeemPoin > 0, 422, 'Penukaran poin membutuhkan data pelanggan.');

            return [0, 0];
        }

        $pelanggan = Pelanggan::find($idPelanggan);
        abort_unless($pelanggan, 422, 'Data pelanggan tidak ditemukan.');

        $poinAwal = (int) $pelanggan->total_poin;
        abort_if($redeemPoin > $poinAwal, 422, 'Poin yang ditukar melebihi poin yang dimiliki pelanggan.');

        $tambahanPoin = 0;
        $config = PoinPelanggan::where('is_active', 'ya')->first();
        if ($config) {
            if ($config->is_kelipatan === 'ya' && $config->min_penjualan > 0) {
                $tambahanPoin = (int) floor($totalAkhir / $config->min_penjualan) * (int) $config->poin_pelanggan;
            } elseif ($config->is_kelipatan === 'no' && $totalAkhir >= $config->min_penjualan) {
                $tambahanPoin = (int) $config->poin_pelanggan;
            }
        }

        $pelanggan->update(['total_poin' => ($poinAwal + $tambahanPoin) - $redeemPoin]);

        return [$poinAwal, $tambahanPoin];
    }

    /**
     * Cetak struk (FPDF, bukan HTML/CSS browser-print seperti halaman cetak lain di port
     * ini) -- mengikuti public/apotekberlian/masuk/modul/mod_laporan/struk.php SEDEKAT
     * mungkin (koordinat/ukuran cm, kertas thermal tinggi dinamis, pengelompokan baris
     * bundle jadi satu baris per kd_bundle, baris resep digabung jadi satu baris "Resep
     * <kd_trkasir>"), atas permintaan pengguna 2026-09-04 supaya lebih mudah didesain
     * ulang lewat kode FPDF yang sudah familiar dari aplikasi lama. Berlaku untuk struk
     * ini SAJA -- halaman cetak lain di seluruh port tetap HTML/CSS `@page` + tombol
     * Cetak browser, tidak diretrofit.
     */
    public function struk(Trkasir $trkasir)
    {
        $trkasir->load('detail');
        $setheader = Setheader::first();
        $carabayar = CaraBayar::find($trkasir->id_carabayar);
        $pelanggan = $trkasir->id_pelanggan ? Pelanggan::find($trkasir->id_pelanggan) : null;

        $pdf = $this->buildStrukPdf($trkasir, $setheader, $carabayar, $pelanggan);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="struk-' . $trkasir->kd_trkasir . '.pdf"',
        ]);
    }

    private function buildStrukPdf(Trkasir $trkasir, ?Setheader $rh, ?CaraBayar $carabayar, ?Pelanggan $pelanggan): \FPDF
    {
        $detailRows = $trkasir->detail()->orderBy('id_dtrkasir')->get();

        // Susun baris cetak: bundle digabung jadi satu baris per kd_bundle, baris resep
        // (resep='YA') dikumpulkan jadi satu baris "Resep <kd_trkasir>" -- persis pola
        // legacy struk.php.
        $printRows = [];
        $bundleMap = [];
        $totalResep = 0;
        $adaResep = false;

        foreach ($detailRows as $row) {
            if (strtoupper((string) $row->resep) === 'YA') {
                $adaResep = true;
                $totalResep += (float) $row->hrgttl_dtrkasir;
                continue;
            }

            $kdBundle = trim((string) $row->kd_bundle);
            $nmBundle = trim((string) $row->nm_bundle);

            if ($kdBundle !== '' && $nmBundle !== '') {
                if (!isset($bundleMap[$kdBundle])) {
                    $bundleMap[$kdBundle] = ['nm' => $nmBundle, 'qty' => 1, 'harga' => 0, 'disc' => '-', 'jumlah' => 0, 'sat' => ''];
                }
                $bundleMap[$kdBundle]['harga'] += (float) $row->hrgttl_dtrkasir;
                $bundleMap[$kdBundle]['jumlah'] += (float) $row->hrgttl_dtrkasir;
                continue;
            }

            $printRows[] = [
                'nm' => $row->nmbrg_dtrkasir,
                'qty' => $row->qty_dtrkasir,
                'sat' => $row->sat_dtrkasir,
                'harga' => $row->hrgjual_dtrkasir,
                'disc' => $row->disc,
                'jumlah' => $row->hrgttl_dtrkasir,
            ];
        }
        foreach ($bundleMap as $bundleRow) {
            $printRows[] = $bundleRow;
        }

        // Tinggi kertas thermal dihitung dinamis dari jumlah baris + wrap nama barang.
        $ukuran1 = 20.7;
        $tambahUkuran = 0;
        foreach ($printRows as $row) {
            $wrapped = $this->wrapReceiptText($row['nm'], 32);
            $lineCount = max(1, substr_count($wrapped, "\n") + 1);
            $tambahUkuran += ($lineCount * 0.24) + 0.52;
        }
        if ($adaResep) {
            $tambahUkuran += 0.6;
        }
        $tinggiKertas = $ukuran1 + $tambahUkuran;

        $pdf = new \FPDF('P', 'cm', [$tinggiKertas, 5.2]);
        $pdf->SetMargins(0.2, -1, 0.2);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->Line(0.2, 2.9, 4.8, 2.9);
        $pdf->Line(0.2, 4.9, 4.8, 4.9);

        $satu = (string) ($rh->satu ?? '');
        $text = mb_substr($satu, 7);

        $pdf->Ln(1.3);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(5, 0.4, 'APOTEK', 0, 1, 'C');
        $pdf->Cell(5, 0.4, $text, 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(5, 0.4, (string) ($rh->dua ?? ''), 0, 1, 'C');
        $pdf->Cell(5, 0.4, (string) ($rh->tiga ?? ''), 0, 1, 'C');
        $pdf->Cell(5, 0.3, (string) ($rh->empat ?? ''), 0, 1, 'C');
        $pdf->Cell(5, 0.3, 'SIA : ' . ($rh->lima ?? ''), 0, 1, 'C');
        $pdf->Cell(5, 0.3, 'Telp : ' . ($rh->enam ?? ''), 0, 1, 'C');
        $pdf->Cell(5, 0.5, '', 0, 1, 'C');

        $pdf->Ln(0.2);
        $pdf->SetX(0.2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(1.7, 0, 'No Nota', 0, 0, 'L');
        $pdf->Cell(0.2, 0, ':', 0, 0, 'L');
        $pdf->Cell(1.8, 0, $trkasir->kd_trkasir, 0, 0, 'L');

        $pdf->Ln(0.4);
        $pdf->SetX(0.2);
        $pdf->Cell(1.7, 0, 'Tanggal', 0, 0, 'L');
        $pdf->Cell(0.2, 0, ':', 0, 0, 'L');
        $pdf->Cell(1.8, 0, $this->tglIndo((string) $trkasir->tgl_trkasir), 0, 0, 'L');

        $pdf->Ln(0.4);
        $pdf->SetX(0.2);
        $pdf->Cell(1.7, 0, 'Pelanggan', 0, 0, 'L');
        $pdf->Cell(0.2, 0, ':', 0, 0, 'L');
        $pdf->Cell(1.8, 0, (string) $trkasir->nm_pelanggan, 0, 0, 'L');

        $pdf->Ln(0.4);
        $pdf->SetX(0.2);
        $pdf->Cell(1.7, 0, 'No Telp/HP', 0, 0, 'L');
        $pdf->Cell(0.2, 0, ':', 0, 0, 'L');
        $pdf->Cell(1.8, 0, (string) $trkasir->tlp_pelanggan, 0, 0, 'L');

        $pdf->Ln(0.3);
        $pdf->SetX(0.2);
        $pdf->Cell(1, 0.5, 'Item', 0, 0, 'L');
        $pdf->Cell(0.7, 0.5, 'Qty', 0, 0, 'C');
        $pdf->Cell(1, 0.5, 'Harga', 0, 0, 'R');
        $pdf->Cell(1.2, 0.5, 'Disc(%)', 0, 0, 'R');
        $pdf->Cell(0.7, 0.5, 'Jml', 0, 1, 'R');

        foreach ($printRows as $pr) {
            $pdf->SetX(0.2);
            $pdf->MultiCell(4.6, 0.24, $this->wrapReceiptText($pr['nm'], 32), 0, 'L');

            $pdf->SetX(0.2);
            $pdf->Cell(1, 0.34, (string) $pr['qty'], 0, 0, 'R');
            $pdf->Cell(0.7, 0.34, (string) $pr['sat'], 0, 0, 'C');
            $pdf->Cell(1.1, 0.34, $this->formatRupiah($pr['harga']), 0, 0, 'R');
            $discTampil = ($pr['disc'] === '-' || $pr['disc'] === '' || $pr['disc'] === null) ? '-' : $pr['disc'];
            $pdf->Cell(0.7, 0.34, (string) $discTampil, 0, 0, 'R');
            $pdf->Cell(1.1, 0.34, $this->formatRupiah($pr['jumlah']), 0, 1, 'R');
            $pdf->Ln(0.12);
        }

        if ($adaResep) {
            $pdf->SetX(0.2);
            $pdf->Cell(5.7, 0.4, 'Resep ' . $trkasir->kd_trkasir, 0, 1, 'L');
            $pdf->Cell(1, 0.4, '1', 0, 0, 'R');
            $pdf->Cell(1, 0.4, '', 0, 0, 'C');
            $pdf->Cell(1, 0.4, $this->formatRupiah($totalResep), 0, 0, 'R');
            $pdf->Cell(1.5, 0.4, $this->formatRupiah($totalResep), 0, 1, 'R');
            $pdf->Ln(0.1);
        }

        $subtotal = $detailRows->sum('hrgttl_dtrkasir');
        $disc = $subtotal > 0 ? (($subtotal - $trkasir->ttl_trkasir) / $subtotal) * 100 : 0;
        $discTampil = number_format($disc, 1, ',', '.') . '%';

        $lineY = $pdf->GetY() + 0.06;
        $pdf->Line(0.2, $lineY, 4.8, $lineY);
        $pdf->SetY($lineY);

        $pdf->Ln(0.25);
        $pdf->SetX(0.2);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(2, 0.4, 'Met byr : ', 0, 0, 'L');
        $pdf->Cell(1.5, 0.4, 'Sub Total : ', 0, 0, 'R');
        $pdf->Cell(1.2, 0.4, $this->formatRupiah($subtotal), 0, 1, 'R');

        $namaCarabayar = (string) ($carabayar->nm_carabayar ?? '');
        $pdf->SetX(0.2);
        $pdf->Cell(2, 0.4, $namaCarabayar, 0, 0, 'L');
        $pdf->Cell(1.5, 0.4, 'Diskon : ', 0, 0, 'R');
        $pdf->Cell(1.2, 0.4, $discTampil, 0, 1, 'R');

        $pdf->SetX(0.2);
        $pdf->Cell(3.5, 0.4, 'Total : ', 0, 0, 'R');
        $pdf->Cell(1.2, 0.4, $this->formatRupiah($trkasir->ttl_trkasir), 0, 1, 'R');

        $pdf->SetX(0.2);
        $pdf->Cell(3.5, 0.4, 'Pembayaran Pasien : ', 0, 0, 'R');
        $pdf->Cell(1.2, 0.4, $this->formatRupiah($trkasir->dp_bayar), 0, 1, 'R');

        $pdf->SetX(0.2);
        $pdf->Cell(3.5, 0.4, 'Kembalian : ', 0, 0, 'R');
        $pdf->Cell(1.2, 0.4, $this->formatRupiah($trkasir->sisa_bayar), 0, 1, 'R');

        $lineY2 = $pdf->GetY() + 0.08;
        $pdf->Line(0.2, $lineY2, 4.8, $lineY2);
        $pdf->SetY($lineY2);

        $pdf->Ln(0.4);
        if ($trkasir->poin_awal != 0) {
            $totalPoin = $pelanggan->total_poin ?? 0;

            $pdf->SetX(0.2);
            $pdf->Cell(2, 0.4, 'Poin Awal : ', 0, 0, 'L');
            $pdf->Cell(2.7, 0.4, $this->formatRupiah($trkasir->poin_awal), 0, 1, 'R');

            $pdf->SetX(0.2);
            $pdf->Cell(2, 0.4, 'Tambahan Poin : ', 0, 0, 'L');
            $pdf->Cell(2.7, 0.4, $this->formatRupiah($trkasir->tambahan_poin), 0, 1, 'R');

            $pdf->SetX(0.2);
            $pdf->Cell(2, 0.4, 'Redeem Poin : ', 0, 0, 'L');
            $pdf->Cell(2.7, 0.4, $this->formatRupiah($trkasir->redeem_poin * -1), 0, 1, 'R');

            $pdf->SetX(0.2);
            $pdf->Cell(2, 0.4, 'Sisa Poin : ', 0, 0, 'L');
            $pdf->Cell(2.7, 0.4, $this->formatRupiah($totalPoin), 0, 1, 'R');
        }

        $pdf->Ln(0.6);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(4.6, 0.3, (string) ($rh->delapan ?? ''), 0, 1, 'C');
        $pdf->MultiCell(4.6, 0.2, (string) ($rh->sembilan ?? ''), 0, 'C');
        $pdf->Cell(4.6, 0.3, (string) ($rh->sepuluh ?? ''), 0, 1, 'C');
        $pdf->Cell(4.6, 0.3, (string) ($rh->sebelas ?? ''), 0, 1, 'C');
        $pdf->Cell(4.6, 0.3, 'Kasir : ' . $trkasir->petugas, 0, 1, 'C');

        return $pdf;
    }

    private function wrapReceiptText(?string $text, int $maxChars = 30): string
    {
        $clean = trim((string) $text);
        if ($clean === '') {
            return '-';
        }

        return wordwrap($clean, $maxChars, "\n", true);
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

    /**
     * Kwitansi (bukti pembayaran lump-sum, BUKAN faktur berisi rincian item -- itu
     * invoice()), mengikuti public/apotekberlian/masuk/modul/mod_laporan/kwitansi.php
     * SEDEKAT mungkin, termasuk strip nama apotek vertikal di sisi kiri + logo diputar
     * 270 derajat (RPdf::TextWithDirection()/RotatedImage()) sesuai keputusan pengguna
     * 2026-09-04 untuk mereproduksi persis, bukan menyederhanakan.
     */
    public function kwitansi(Trkasir $trkasir)
    {
        $setheader = Setheader::first();
        $pdf = $this->buildKwitansiPdf($trkasir, $setheader);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="kwitansi-' . $trkasir->kd_trkasir . '.pdf"',
        ]);
    }

    private function buildKwitansiPdf(Trkasir $trkasir, ?Setheader $rh): RPdf
    {
        $panjang = $this->terbilang((float) $trkasir->ttl_trkasir);
        $text1 = mb_substr($panjang, 0, 50);
        $text2 = mb_substr($panjang, 50, 100);
        $text3 = mb_strlen($panjang);

        $pdf = new RPdf('P', 'cm', 'A4');
        $pdf->AddPage();

        $satu = (string) ($rh->satu ?? '');
        $text = mb_substr($satu, 7);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->TextWithDirection(1.5, 5.7, 'APOTEK', 'U');
        $pdf->TextWithDirection(2, 5.7, $text, 'U');
        $pdf->SetFont('Arial', '', 7);
        $pdf->TextWithDirection(2.5, 5.7, (string) ($rh->dua ?? ''), 'U');
        $pdf->TextWithDirection(2.8, 5.7, (string) ($rh->tiga ?? ''), 'U');
        $pdf->TextWithDirection(3.1, 5.7, (string) ($rh->tujuh ?? ''), 'U');

        $logoPath = $this->logoFilePath($rh);
        if ($logoPath) {
            $pdf->RotatedImage($logoPath, 1.2, 7.8, 2.0, 2.0, -270);
        }

        $pdf->SetLineWidth(0.1);
        $pdf->Line(0.8, 0.7, 0.8, 8);
        $pdf->Line(4, 0.7, 4, 8);
        $pdf->Line(20, 0.7, 20, 8);
        $pdf->Line(0.8, 0.7, 20, 0.7);
        $pdf->Line(0.8, 8, 20, 8);

        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
        $pdf->Cell(4.2, 0.5, 'No Transaksi', 0, 0, 'L');
        $pdf->Cell(0.1, 0.5, ':', 0, 0, 'C');
        $pdf->Cell(3, 0.5, $trkasir->kd_trkasir, 0, 1, 'L');
        $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
        $pdf->Cell(4.2, 0.5, 'Telah Terima dari', 0, 0, 'L');
        $pdf->Cell(0.1, 0.5, ':', 0, 0, 'C');
        $pdf->Cell(3, 0.5, (string) $trkasir->nm_pelanggan, 0, 1, 'L');

        if ($text3 < 51) {
            $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
            $pdf->Cell(4.2, 0.5, 'Uang Sejumlah', 0, 0, 'L');
            $pdf->Cell(0.1, 0.5, ':', 0, 0, 'C');
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(11, 0.5, $text1 . ' Rupiah', 0, 1, 'L', true);
        } else {
            $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
            $pdf->Cell(4.2, 0.5, 'Uang Sejumlah', 0, 0, 'L');
            $pdf->Cell(0.1, 0.5, ':', 0, 0, 'C');
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(11, 0.5, $text1, 0, 1, 'L', true);
            $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
            $pdf->Cell(4.2, 0.5, '', 0, 0, 'L');
            $pdf->Cell(0.1, 0.5, '', 0, 0, 'C');
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(11, 0.5, $text2 . ' Rupiah', 0, 1, 'L', true);
        }

        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
        $pdf->Cell(4.2, 0.5, 'Untuk Pembayaran', 0, 0, 'L');
        $pdf->Cell(0.1, 0.5, ':', 0, 0, 'C');
        $pdf->Cell(11, 0.5, 'Pembelian obat - obatan', 0, 1, 'L');

        $pdf->Ln(0.5);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
        $pdf->Cell(7, 0.5, '', 0, 0, 'L');
        $pdf->Cell(7, 0.5, ((string) ($rh->tigabelas ?? '')) . ', ' . $this->tglIndo((string) $trkasir->tgl_trkasir), 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(3.5, 0.5, '', 0, 0, 'R');
        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('Arial', '', 20);
        $pdf->Cell(7, 0.5, 'Rp. ' . $this->formatRupiah($trkasir->ttl_trkasir) . ',-', 0, 0, 'L', true);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(7, 0.5, (string) $trkasir->petugas, 0, 1, 'C');

        return $pdf;
    }

    /**
     * Invoice (faktur berisi rincian item + blok tanda tangan Penerima/Apoteker),
     * mengikuti public/apotekberlian/masuk/modul/mod_laporan/invoice.php.
     *
     * Perbaikan atas legacy (disetujui pengguna 2026-09-04, "perbaiki"): cabang nama
     * barang panjang (>37 karakter) di legacy memakai variabel $wp/$disc yang TIDAK
     * PERNAH didefinisikan (sisa dari blok kalkulasi diskon yang dikomentari di atasnya)
     * -- kolom Harga & Disc diam-diam kosong/null kalau ada nama barang panjang. Di sini
     * kedua kolom itu memakai rumus yang sama persis dengan cabang nama pendek (harga
     * jual & % diskon baris ini apa adanya), bukan meniru bug-nya. Kolom Batch di cabang
     * yang sama juga salah baca key ($r2['batch'], padahal kolomnya 'no_batch') --
     * diperbaiki sekalian karena akar masalahnya sama persis (cabang ini jelas tidak
     * pernah benar-benar diuji di legacy).
     */
    public function invoice(Trkasir $trkasir)
    {
        $setheader = Setheader::first();
        $pdf = $this->buildInvoicePdf($trkasir, $setheader);

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice-' . $trkasir->kd_trkasir . '.pdf"',
        ]);
    }

    private function buildInvoicePdf(Trkasir $trkasir, ?Setheader $rh): RPdf
    {
        $detailRows = $trkasir->detail()->orderBy('nmbrg_dtrkasir')->get();

        $pdf = new RPdf('P', 'cm', 'A4');
        $pdf->SetMargins(0, 0.5, 0);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $logoPath = $this->logoFilePath($rh);
        if ($logoPath) {
            $pdf->Image($logoPath, 1, 1, 1.5, 1.4);
        }

        $pdf->SetLineWidth(0.1);
        $pdf->Line(0.7, 3.5, 20.5, 3.5);

        $pdf->SetLineWidth(0.05);
        $pdf->Line(0.7, 5.2, 20.5, 5.2);
        $pdf->Line(0.7, 5.8, 20.5, 5.8);
        $pdf->Line(0.7, 9.5, 20.5, 9.5);
        $pdf->Line(0.7, 5.2, 0.7, 9.5);
        $pdf->Line(1.5, 5.2, 1.5, 9.5);
        $pdf->Line(8, 5.2, 8, 9.5);
        $pdf->Line(10.7, 5.2, 10.7, 9.5);
        $pdf->Line(9.3, 5.2, 9.3, 9.5);
        $pdf->Line(12.8, 5.2, 12.8, 9.5);
        $pdf->Line(14.5, 5.2, 14.5, 9.5);
        $pdf->Line(16.7, 5.2, 16.7, 9.5);
        $pdf->Line(18.5, 5.2, 18.5, 9.5);
        $pdf->Line(20.5, 5.2, 20.5, 9.5);

        $pdf->Ln(0.1);
        $pdf->SetX(0.5);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(20.5, 0.5, 'I N V O I CE', 0, 1, 'C');

        $pdf->Ln(0.1);
        $pdf->SetX(3);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(16.5, 0.5, (string) ($rh->satu ?? ''), 0, 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(3);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(8, 0.2, (string) ($rh->dua ?? ''), 0, 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(3);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(8, 0.2, (string) ($rh->tiga ?? ''), 0, 1, 'L');

        $pdf->Ln(0.4);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.1, 'SIA', 0, 0, 'L');
        $pdf->Cell(0.3, 0.1, ':', 0, 0, 'L');
        $pdf->Cell(10, 0.1, (string) ($rh->lima ?? ''), 0, 0, 'L');
        $pdf->Cell(2, 0.2, 'No. Faktur', 0, 0, 'L');
        $pdf->Cell(0.3, 0.2, ':', 0, 0, 'R');
        $pdf->Cell(6, 0.2, $trkasir->kd_trkasir, 0, 1, 'L');

        $pdf->Ln(0.2);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.1, 'No. Telp/Wa', 0, 0, 'L');
        $pdf->Cell(0.3, 0.1, ':', 0, 0, 'L');
        $pdf->Cell(10, 0.1, (string) ($rh->enam ?? ''), 0, 0, 'L');
        $pdf->Cell(2, 0.2, 'Tgl Faktur', 0, 0, 'L');
        $pdf->Cell(0.3, 0.2, ':', 0, 0, 'R');
        $pdf->Cell(6, 0.2, $this->tglIndo((string) $trkasir->tgl_trkasir), 0, 1, 'L');

        $pdf->Ln(0.6);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.1, 'Kepada Yth', 0, 1, 'L');

        $pdf->Ln(0.2);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(20, 0.2, (string) $trkasir->nm_pelanggan, 0, 1, 'L');

        $pdf->Ln(0.2);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(20, 0.2, ((string) $trkasir->alamat_pelanggan) . ' Telp : ' . ((string) $trkasir->tlp_pelanggan), 0, 1, 'L');

        $pdf->Ln(0.2);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(3.3, 0.2, '', 0, 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(1, 0.4, 'No.', 0, 0, 'C');
        $pdf->Cell(6, 0.4, 'Nama Barang', 0, 0, 'C');
        $pdf->Cell(2, 0.4, 'Jml', 0, 0, 'C');
        $pdf->Cell(1, 0.4, 'Sat', 0, 0, 'C');
        $pdf->Cell(2, 0.4, 'Batch', 0, 0, 'C');
        $pdf->Cell(2, 0.4, 'Exp', 0, 0, 'C');
        $pdf->Cell(2, 0.4, 'Harga', 0, 0, 'C');
        $pdf->Cell(2, 0.4, 'Disc (%)', 0, 0, 'C');
        $pdf->Cell(1.8, 0.4, 'Sub Total', 0, 1, 'C');

        $pdf->Ln(0.3);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);

        $no = 1;
        foreach ($detailRows as $row) {
            $edt = $row->exp_date ? $row->exp_date->format('m-Y') : '-';
            $pdf->SetX(0.6);

            $nama = (string) $row->nmbrg_dtrkasir;
            $text1 = mb_substr($nama, 0, 37);
            $text2 = mb_substr($nama, 37, 72);
            $panjangNama = mb_strlen($nama);

            if ($panjangNama > 37) {
                $pdf->Cell(1, 0.4, $no . '.', 0, 0, 'C');
                $pdf->Cell(6, 0.4, $text1, 0, 0, 'L');
                $pdf->Cell(2, 0.4, (string) $row->qty_dtrkasir, 0, 0, 'R');
                $pdf->Cell(1, 0.4, (string) $row->sat_dtrkasir, 0, 0, 'L');
                $pdf->Cell(2, 0.4, (string) $row->no_batch, 0, 0, 'C');
                $pdf->Cell(2, 0.4, $edt, 0, 0, 'C');
                $pdf->Cell(2, 0.4, $this->formatRupiah($row->hrgjual_dtrkasir), 0, 0, 'R');
                $pdf->Cell(2, 0.4, (string) $row->disc, 0, 0, 'C');
                $pdf->Cell(1.8, 0.4, $this->formatRupiah($row->hrgttl_dtrkasir), 0, 1, 'R');

                $pdf->SetX(0.6);
                $pdf->Cell(1, 0.4, '', 0, 0, 'C');
                $pdf->Cell(6, 0.4, $text2, 0, 0, 'L');
                $pdf->Cell(2, 0.4, '', 0, 0, 'R');
                $pdf->Cell(1, 0.4, '', 0, 0, 'L');
                $pdf->Cell(2, 0.4, '', 0, 0, 'L');
                $pdf->Cell(2, 0.4, '', 0, 0, 'L');
                $pdf->Cell(2, 0.4, '', 0, 0, 'R');
                $pdf->Cell(2, 0.4, '', 0, 0, 'C');
                $pdf->Cell(1.8, 0.4, '', 0, 1, 'R');
            } else {
                $pdf->Cell(1, 0.4, $no . '.', 0, 0, 'C');
                $pdf->Cell(6, 0.4, $text1, 0, 0, 'L');
                $pdf->Cell(2, 0.4, (string) $row->qty_dtrkasir, 0, 0, 'C');
                $pdf->Cell(1, 0.4, (string) $row->sat_dtrkasir, 0, 0, 'C');
                $pdf->Cell(2, 0.4, (string) $row->no_batch, 0, 0, 'C');
                $pdf->Cell(2, 0.4, $edt, 0, 0, 'C');
                $pdf->Cell(2, 0.4, $this->formatRupiah($row->hrgjual_dtrkasir), 0, 0, 'R');
                $pdf->Cell(2, 0.4, (string) $row->disc, 0, 0, 'C');
                $pdf->Cell(1.8, 0.4, $this->formatRupiah($row->hrgttl_dtrkasir), 0, 1, 'R');
            }

            $no++;
        }

        $gt = (float) $detailRows->sum('hrgttl_dtrkasir');
        $subtotal = $this->formatRupiah($gt);

        $pdf->SetY(9.6);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(8, 0.3, 'Terbilang :', 0, 0, 'L');
        $pdf->Cell(9.8, 0.3, '', 0, 0, 'R');
        $pdf->Cell(2.2, 0.3, '', 0, 1, 'R');

        $terbilangText = trim($this->terbilang($gt));
        $terbilang1 = mb_substr($terbilangText, 0, 66);
        $terbilang2 = trim(mb_substr($terbilangText, 66, 130));

        if (mb_strlen($terbilangText) > 66) {
            $pdf->SetX(0.6);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(8, 0.4, $terbilang1, 0, 0, 'L');

            $pdf->SetX(0.6);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(8, 0.4, $terbilang2 . ' Rupiah.', 0, 0, 'L');

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(9.8, 0.4, 'Harus Dibayar : ', 0, 0, 'R');
            $pdf->Cell(2.2, 0.4, $subtotal, 0, 1, 'R');
        } else {
            $pdf->SetX(0.6);
            $pdf->SetFont('Arial', 'IB', 11);
            $pdf->Cell(8, 0.4, $terbilangText . ' Rupiah.', 0, 0, 'L');

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(9.8, 0.4, 'Total Pembayaran : ', 0, 0, 'R');
            $pdf->Cell(2, 0.4, $subtotal, 0, 1, 'R');
        }

        $pdf->Ln(0.4);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(10, 0.4, 'Catatan/Ketentuan :', 'LTR', 0, 'C');
        $pdf->Cell(5, 0.4, 'Penerima', 0, 0, 'C');
        $pdf->Cell(5, 0.4, 'Apoteker', 0, 0, 'C');

        $pdf->Ln(0.4);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.4, 'Pembayaran', 'L', 0, 'L');
        $pdf->Cell(0.3, 0.4, ':', 0, 0, 'L');
        $pdf->Cell(7.7, 0.4, 'Cash On Delivery', 'R', 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(2, 0.1, '', 'L', 0, 'L');
        $pdf->Cell(0.3, 0.1, '', 0, 0, 'L');
        $pdf->Cell(7.7, 0.1, '', 'R', 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(10, 0.1, '', 'LBR', 0, 'L');
        $pdf->Cell(10, 0.1, '', 0, 1, 'L');

        $pdf->Ln(0.1);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(10, 0.3, '', 0, 0, 'L');
        $pdf->Cell(5, 0.3, '( ____________________ )', 0, 0, 'C');

        $apjText = mb_substr((string) ($rh->empat ?? ''), 0, 31);
        $pdf->SetFont('Arial', 'U', 9);
        $pdf->Cell(5, 0.3, $apjText, '', 1, 'C');

        $pdf->Ln(0.1);
        $pdf->SetX(0.6);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(15, 0.3, '', 0, 0, 'L');
        $pdf->Cell(5, 0.3, (string) ($rh->tujuh ?? ''), 0, 1, 'C');

        return $pdf;
    }

    /**
     * Etiket obat (label 70x38mm untuk pasien), mengikuti mod_etiket/etiket.php --
     * halaman HTML/CSS browser-print biasa (BUKAN FPDF, beda dari struk/kwitansi/invoice
     * -- legacy sendiri juga sudah begitu untuk etiket). Aturan pakai/dosis SENGAJA input
     * manual per cetak (bukan dibaca dari trkasir_detail) -- persis legacy, karena resep
     * per baris keranjang tidak menyimpan field dosis terstruktur sama sekali.
     */
    public function etiket(Request $request, Trkasir $trkasir)
    {
        $jumlahEtiket = (int) $request->query('jumlah_etiket', 1);
        $jumlahEtiket = max(1, min(200, $jumlahEtiket));

        return view('inventory.trkasir.etiket', [
            'trkasir' => $trkasir,
            'setheader' => Setheader::first(),
            'aturanDosis' => (string) $request->query('aturan_dosis', ''),
            'aturanKali' => (string) $request->query('aturan_kali', ''),
            'obatLuar' => $request->boolean('obat_luar'),
            'jumlahEtiket' => $jumlahEtiket,
        ]);
    }

    /**
     * Path FILESYSTEM lokal ke logo (bukan URL -- FPDF butuh path fisik untuk Image()),
     * versi Setheader::getLogoUrlAttribute() ini juga membedakan file baru (diunggah
     * lewat form Header Struk Laravel, ada '/') vs nama file polos peninggalan legacy.
     * null kalau kolomnya kosong ATAU filenya ternyata tidak ada di disk (mis. kwitansi's
     * tandatangan yang sudah dikonfirmasi jadi dead link di produksi -- lihat catatan
     * modul ini) -- supaya FPDF::Image() tidak fatal error, logo cukup dilewati.
     */
    private function logoFilePath(?Setheader $rh): ?string
    {
        $logo = $rh->logo ?? null;
        if (!$logo) {
            return null;
        }

        $path = str_contains($logo, '/')
            ? storage_path('app/public/' . $logo)
            : public_path('apotekberlian/masuk/images/' . $logo);

        return is_file($path) ? $path : null;
    }

    /**
     * Port langsung dari teks()/terbilang() di configurasi/fungsi_rupiah.php (angka ke
     * teks terbilang Bahasa Indonesia), dipakai kwitansi & invoice.
     */
    private function teksAngka(float $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' ' . $huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = $this->teksAngka($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $temp = $this->teksAngka($nilai / 10) . ' Puluh' . $this->teksAngka((float) ((int) $nilai % 10));
        } elseif ($nilai < 200) {
            $temp = ' Seratus' . $this->teksAngka($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = $this->teksAngka($nilai / 100) . ' Ratus' . $this->teksAngka((float) ((int) $nilai % 100));
        } elseif ($nilai < 2000) {
            $temp = ' Seribu' . $this->teksAngka($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = $this->teksAngka($nilai / 1000) . ' Ribu' . $this->teksAngka((float) ((int) $nilai % 1000));
        } elseif ($nilai < 1000000000) {
            $temp = $this->teksAngka($nilai / 1000000) . ' Juta' . $this->teksAngka((float) ((int) $nilai % 1000000));
        } elseif ($nilai < 1000000000000) {
            $temp = $this->teksAngka($nilai / 1000000000) . ' Milyar' . $this->teksAngka(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = $this->teksAngka($nilai / 1000000000000) . ' Trilyun' . $this->teksAngka(fmod($nilai, 1000000000000));
        }

        return $temp;
    }

    private function terbilang(float $nilai): string
    {
        return $nilai < 0 ? 'minus ' . trim($this->teksAngka($nilai)) : trim($this->teksAngka($nilai));
    }

    // ==================== PENCARIAN/PEMILIHAN BARANG, BUNDLE, MEMBER ====================

    public function itemSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        if ($query === '') {
            return response()->json([]);
        }

        return response()->json(
            Product::where('nm_barang', 'like', "%{$query}%")->orderBy('nm_barang')->limit(20)
                ->get(['id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'sat_barang'])
        );
    }

    public function itemResolve(Request $request)
    {
        $namaBarang = $request->input('nm_barang');
        $kdBarang = $request->input('kd_barang');
        $jenistx = (int) $request->input('jenistx', 1);

        $query = Product::query();
        if ($namaBarang !== null) {
            $query->where('nm_barang', $namaBarang);
        } elseif ($kdBarang !== null) {
            $query->where('kd_barang', $kdBarang);
        } else {
            return response()->json(['message' => 'Parameter tidak lengkap'], 422);
        }

        $barang = $query->first();
        if (!$barang) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        $hargaTier = match ($jenistx) {
            2 => $barang->hrgjual_barang1,
            3 => $barang->hrgjual_barang2,
            default => $barang->hrgjual_barang,
        };
        $hargaTier = $hargaTier ?: $barang->hrgjual_barang;

        return response()->json([
            'id_barang' => $barang->id_barang,
            'kd_barang' => (string) $barang->kd_barang,
            'nm_barang' => $barang->nm_barang,
            'stok_barang' => $barang->stok_barang,
            'sat_barang' => $barang->sat_barang,
            'hrgjual_barang' => $hargaTier,
        ]);
    }

    public function itemPicker()
    {
        // Komisi & Indikasi ditampilkan supaya karyawan bisa mencari obat berdasarkan
        // indikasi dan melihat besaran komisi per item langsung dari modal pemilihan
        // barang -- mengikuti kolom header di trkasir.php legacy (No, Kode Obat, Nama
        // Obat, Qty, Satuan, Komisi, Indikasi, Hrg Jual, Pilih).
        $query = Product::query()->select(['id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'sat_barang', 'komisi', 'indikasi', 'hrgjual_barang']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('kd_barang', fn ($row) => (string) $row->kd_barang)
            ->editColumn('komisi', fn ($row) => number_format($row->komisi, 0, ',', '.'))
            ->editColumn('indikasi', fn ($row) => \Illuminate\Support\Str::limit(trim(strip_tags((string) $row->indikasi)), 80))
            ->editColumn('hrgjual_barang', fn ($row) => number_format($row->hrgjual_barang, 0, ',', '.'))
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-barang' data-nm_barang='" . e($row->nm_barang) . "'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    public function bundlePicker()
    {
        $query = Bundle::query()->where('qty_bundle', '>', 0)->select(['id_bundle', 'kd_bundle', 'nm_bundle', 'sat_bundle', 'qty_bundle', 'hrgjual_bundle']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('hrgjual_bundle', fn ($row) => number_format($row->hrgjual_bundle, 0, ',', '.'))
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-bundle' data-kd_bundle='" . e($row->kd_bundle) . "'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    public function bundleResolve(Request $request)
    {
        $bundle = Bundle::where('kd_bundle', $request->input('kd_bundle'))->first();
        if (!$bundle) {
            return response()->json(['message' => 'Bundle tidak ditemukan'], 404);
        }

        return response()->json([
            'kd_barang' => $bundle->kd_bundle,
            'nm_barang' => $bundle->nm_bundle,
            'stok_barang' => $bundle->qty_bundle,
            'sat_barang' => $bundle->sat_bundle,
            'hrgjual_barang' => $bundle->hrgjual_bundle,
        ]);
    }

    /**
     * Cari No. Batch untuk satu kd_barang, mengikuti batch_serverside.php -- dipakai oleh
     * #ModalBatch di layar Tambah Penjualan. Diurutkan FEFO (expired date terdekat dulu),
     * hanya batch dengan sisa qty > 0 yang ditampilkan.
     */
    public function batchPicker(Request $request)
    {
        $kdBarang = (string) $request->query('kd_barang', '');

        $query = Batch::query()
            ->where('kd_barang', $kdBarang)
            ->selectRaw("no_batch, exp_date, SUM(CASE WHEN status='masuk' THEN qty ELSE 0 END) - SUM(CASE WHEN status='keluar' THEN qty ELSE 0 END) as sisa")
            ->groupBy('no_batch', 'exp_date')
            ->havingRaw('SUM(CASE WHEN status=\'masuk\' THEN qty ELSE 0 END) - SUM(CASE WHEN status=\'keluar\' THEN qty ELSE 0 END) > 0')
            ->orderByRaw('CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('exp_date', fn ($row) => $row->exp_date ? \Illuminate\Support\Carbon::parse($row->exp_date)->format('d-m-Y') : '-')
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-batch'
                    data-no_batch='" . e($row->no_batch) . "'
                    data-exp_date='" . e($row->exp_date) . "'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    public function pelangganPicker()
    {
        $query = Pelanggan::query()->select(['id_pelanggan', 'nm_pelanggan', 'tlp_pelanggan', 'alamat_pelanggan', 'total_poin']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-pelanggan'
                    data-id_pelanggan='{$row->id_pelanggan}'
                    data-nm_pelanggan='" . e($row->nm_pelanggan) . "'
                    data-tlp_pelanggan='" . e($row->tlp_pelanggan) . "'
                    data-alamat_pelanggan='" . e($row->alamat_pelanggan) . "'
                    data-total_poin='{$row->total_poin}'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    // ==================== HELPER PRIVAT ====================

    /**
     * Ambil admin yang "membuka" draft transaksi ini dari id_admin yang dibekukan di
     * form saat render, BUKAN dari Auth::guard('admin')->user() (lihat catatan pola
     * keamanan identitas lintas-tab di kelas ini).
     */
    private function resolveActingAdmin(Request $request): Admin
    {
        $admin = Admin::find((int) $request->input('id_admin'));
        abort_if(!$admin || $admin->isBlocked(), 422, 'Sesi petugas kasir ini sudah tidak valid. Muat ulang halaman transaksi.');

        return $admin;
    }

    /**
     * Tentukan Petugas Pelayanan yang berhak atas komisi baris ini, mengikuti arahan
     * pengguna 2026-09-04: kalau id_user (Petugas Pelayanan) tidak dipilih di form,
     * kasir (id_admin/session operator) sendiri jadi default-nya. Kelayakan komisi
     * (admin.komisi='Y') dicek terhadap admin HASIL resolusi ini -- bukan terhadap
     * $admin (kasir) begitu saja kalau id_user memang dipilih.
     */
    private function resolvePetugasPelayanan(?int $idUser, Admin $admin): Admin
    {
        if (empty($idUser)) {
            return $admin;
        }

        $petugas = Admin::find($idUser);
        abort_if(!$petugas, 422, 'Petugas pelayanan tidak ditemukan.');

        return $petugas;
    }

    private function resolveKdtk(int $idAdmin): string
    {
        $existing = Kdtk::where('id_admin', $idAdmin)->where('stt_kdtk', 'ON')->orderByDesc('id_kdtk')->first();
        if ($existing) {
            return $existing->kd_trkasir;
        }

        $kode = 'TKP-' . now()->format('dmyHis');
        if (Kdtk::where('kd_trkasir', $kode)->exists()) {
            $kode = 'TKP-' . now()->addSecond()->format('dmyHis');
        }

        Kdtk::create(['kd_trkasir' => $kode, 'id_admin' => $idAdmin, 'stt_kdtk' => 'ON']);

        return $kode;
    }

    private function kurangiStokAtauGagal(int $idBarang, float $qty): void
    {
        $affected = DB::table('barang')->where('id_barang', $idBarang)->where('stok_barang', '>=', $qty)->decrement('stok_barang', $qty);
        abort_if($affected === 0, 422, 'Stok barang tidak mencukupi.');
    }

    /**
     * Alokasi FEFO (First-Expired-First-Out) otomatis dari ledger `batch`, mengikuti
     * get_batch_fifo() -- kecukupan stok SEBENARNYA sudah divalidasi lewat
     * kurangiStokAtauGagal() (kolom stok_barang, sumber kebenaran utama); kalau ledger
     * batch ternyata lebih sedikit dari qty yang diambil (drift pembukuan), sisanya
     * tetap dicatat sebagai baris tanpa no_batch -- aman karena stok sungguhan sudah
     * tervalidasi di atas, bukan celah oversell seperti fallback "phantom" legacy.
     */
    private function alokasikanBatchKeluar(string $kdTrkasir, string $kdBarang, float $qty): void
    {
        $ledger = Batch::query()
            ->where('kd_barang', $kdBarang)
            ->selectRaw("no_batch, exp_date, SUM(CASE WHEN status='masuk' THEN qty ELSE 0 END) - SUM(CASE WHEN status='keluar' THEN qty ELSE 0 END) as sisa")
            ->groupBy('no_batch', 'exp_date')
            ->havingRaw('SUM(CASE WHEN status=\'masuk\' THEN qty ELSE 0 END) - SUM(CASE WHEN status=\'keluar\' THEN qty ELSE 0 END) > 0')
            ->orderByRaw('CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC')
            ->get();

        $sisaButuh = $qty;
        foreach ($ledger as $b) {
            if ($sisaButuh <= 0) {
                break;
            }
            $ambil = min($sisaButuh, (float) $b->sisa);
            if ($ambil <= 0) {
                continue;
            }

            Batch::create([
                'tgl_transaksi' => now(),
                'no_batch' => $b->no_batch,
                'exp_date' => $b->exp_date,
                'qty' => $ambil,
                'satuan' => '',
                'kd_transaksi' => $kdTrkasir,
                'kd_barang' => $kdBarang,
                'status' => 'keluar',
            ]);
            $sisaButuh -= $ambil;
        }

        if ($sisaButuh > 0) {
            Batch::create([
                'tgl_transaksi' => now(),
                'no_batch' => '',
                'exp_date' => '9999-12-31',
                'qty' => $sisaButuh,
                'satuan' => '',
                'kd_transaksi' => $kdTrkasir,
                'kd_barang' => $kdBarang,
                'status' => 'keluar',
            ]);
        }
    }

    /**
     * Sama seperti alokasikanBatchKeluar(), tapi hanya MERENCANAKAN alokasi (tidak
     * langsung menulis baris `batch`) -- dipakai oleh tambahBarang() supaya tiap batch
     * yang benar-benar dipakai bisa menghasilkan baris keranjang tersendiri (lihat
     * catatan di tambahBarang()).
     *
     * @return array<int, array{no_batch: string, exp_date: ?string, qty: float}>
     */
    private function fefoAllocate(string $kdBarang, float $qty): array
    {
        $ledger = Batch::query()
            ->where('kd_barang', $kdBarang)
            ->selectRaw("no_batch, exp_date, SUM(CASE WHEN status='masuk' THEN qty ELSE 0 END) - SUM(CASE WHEN status='keluar' THEN qty ELSE 0 END) as sisa")
            ->groupBy('no_batch', 'exp_date')
            ->havingRaw('SUM(CASE WHEN status=\'masuk\' THEN qty ELSE 0 END) - SUM(CASE WHEN status=\'keluar\' THEN qty ELSE 0 END) > 0')
            ->orderByRaw('CASE WHEN exp_date IS NULL THEN 1 ELSE 0 END, exp_date ASC')
            ->get();

        $alokasi = [];
        $sisaButuh = $qty;
        foreach ($ledger as $b) {
            if ($sisaButuh <= 0) {
                break;
            }
            $ambil = min($sisaButuh, (float) $b->sisa);
            if ($ambil <= 0) {
                continue;
            }

            $alokasi[] = ['no_batch' => (string) $b->no_batch, 'exp_date' => $b->exp_date, 'qty' => $ambil];
            $sisaButuh -= $ambil;
        }

        if ($sisaButuh > 0) {
            $alokasi[] = ['no_batch' => '', 'exp_date' => null, 'qty' => $sisaButuh];
        }

        return $alokasi;
    }

    /**
     * Batch dipilih manual (lewat #ModalBatch atau diketik langsung) -- potong qty
     * KHUSUS dari batch itu, gagal 422 kalau sisanya tidak cukup (tidak pernah diam-diam
     * "nyerempet" ke batch lain seperti fefoAllocate() -- operator sudah sengaja memilih
     * batch tertentu, harus dihormati atau ditolak, bukan disubstitusi).
     */
    private function resolveManualBatch(string $kdBarang, string $noBatch, float $qty): array
    {
        $ledger = Batch::query()
            ->where('kd_barang', $kdBarang)
            ->where('no_batch', $noBatch)
            ->selectRaw("exp_date, SUM(CASE WHEN status='masuk' THEN qty ELSE 0 END) - SUM(CASE WHEN status='keluar' THEN qty ELSE 0 END) as sisa")
            ->groupBy('exp_date')
            ->first();

        abort_if(!$ledger || $ledger->sisa < $qty, 422, "Stok batch {$noBatch} tidak mencukupi.");

        return ['no_batch' => $noBatch, 'exp_date' => $ledger->exp_date, 'qty' => $qty];
    }

    /**
     * Rate komisi per unit untuk baris ini -- diambil dari barang.komisi HANYA kalau
     * admin yang mengerjakan baris ini (id_admin yang dibekukan di form, lihat
     * resolveActingAdmin()) sendiri punya flag admin.komisi='Y'. Ini persis mengikuti
     * $_SESSION['komisi'] legacy (diisi dari admin.komisi saat login, lihat cek_login.php)
     * -- BUKAN tabel `komisiglobal` (itu pengaturan lain: toggle marquee/ringkasan komisi
     * bulanan di layar default trkasir.php, tidak ada hubungannya dengan pemotongan
     * komisi per baris di sini). Bug yang diperbaiki 2026-09-04: versi sebelumnya salah
     * memakai KomisiGlobal sebagai gerbang, sehingga komisi selalu tercatat 0 walau admin
     * yang bersangkutan berhak dan barangnya punya rate komisi.
     */
    private function komisiPerUnit(?Product $barang, Admin $admin): int
    {
        if (!$barang) {
            return 0;
        }

        return strtoupper((string) $admin->komisi) === 'Y' ? (int) $barang->komisi : 0;
    }

    /**
     * Sesuaikan/insert baris komisi_pegawai untuk satu baris keranjang. $ttlKomisi di
     * sini SELALU rate-per-unit x qty final (linear, tepat sekali) -- lihat perbaikan
     * bug #1 di catatan kelas ini.
     */
    private function sesuaikanKomisi(int $idDtrkasir, string $kdTrkasir, int $idAdmin, float $ttlKomisi, bool $update): void
    {
        if ($ttlKomisi <= 0) {
            KomisiPegawai::where('id_dtrkasir', $idDtrkasir)->delete();

            return;
        }

        if ($update) {
            $existing = KomisiPegawai::where('id_dtrkasir', $idDtrkasir)->first();
            if ($existing) {
                $existing->update(['ttl_komisi' => $ttlKomisi]);

                return;
            }
        }

        KomisiPegawai::create([
            'kd_trkasir' => $kdTrkasir,
            'id_dtrkasir' => $idDtrkasir,
            'id_admin' => $idAdmin,
            'ttl_komisi' => $ttlKomisi,
            'tgl_komisi' => now()->toDateString(),
            'status_komisi' => 'on',
        ]);
    }

    /**
     * Catat perubahan qty ke trkasir_detail_ubah_qty -- di legacy hanya dipanggil dari
     * simpandetail_trkasir.php, TIDAK PERNAH dari update_detail_inline.php (celah audit
     * yang diperbaiki di sini: dipanggil dari kedua jalur).
     */
    private function catatUbahQty(TrkasirDetail $detail, float $qtySebelum, float $hrgttlSebelum, float $hrgttlSesudah, Admin $admin): void
    {
        TrkasirDetailUbahQty::create([
            'kd_trkasir' => $detail->kd_trkasir,
            'id_dtrkasir' => $detail->id_dtrkasir,
            'kd_barang' => $detail->kd_barang,
            'nmbrg_dtrkasir' => $detail->nmbrg_dtrkasir,
            'qty_sebelum' => $qtySebelum,
            'qty_sesudah' => $detail->qty_dtrkasir,
            'hrgttl_sebelum' => $hrgttlSebelum,
            'hrgttl_sesudah' => $hrgttlSesudah,
            'tipetx' => $detail->tipetx,
            'id_admin' => $admin->id_admin,
        ]);
    }
}
