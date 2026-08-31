<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\KartuStok;
use App\Models\Kdbm;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\Setheader;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderDetail;
use App\Models\Trbmasuk;
use App\Models\TrbmasukDetail;
use App\Models\TrbmasukDetailHist;
use App\Models\TrxOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTrbmasukPbfController extends Controller
{
    /**
     * Modul "Barang Masuk dari PBF" (module=trbmasukpbf), mengikuti
     * public/apotekberlian/masuk/modul/mod_trbmasukpbf/*.php.
     *
     * Adik sepupu InventoryTrbmasukController (Barang Masuk non PBF) -- memakai tabel yang
     * SAMA (trbmasuk/trbmasuk_detail/trbmasuk_detail_hist/batch/kartu_stok/kdbm), hanya
     * disaring `jenis='pbf'` alih-alih `'nonpbf'`. Perbedaan nyata dari modul non PBF:
     * - Harga pokok memakai HNA (Harga Netto Apotek) yang diinput manual per baris, BUKAN
     *   diturunkan dari harga jual via VAT-backout seperti non PBF. PPN 11% ditambahkan DI
     *   ATAS HNA (bukan dikurangi dari harga jual) -- lihat helper privat hitungHargaSatuan()/
     *   hitungTotalBaris() untuk rumus persisnya.
     * - Cara bayar punya pilihan ketiga: KONSINYASI (selain KREDIT/LUNAS).
     * - Form edit (ubah) tersedia untuk transaksi yang sudah tersimpan, KHUSUS pemilik --
     *   berbeda dari modul non PBF yang tombol edit-nya mati/dikomentari di legacy. Legacy
     *   PBF sendiri cuma menyembunyikan tombolnya untuk non-pemilik (tidak ada pengecekan di
     *   sisi server) -- di sini SENGAJA diperketat dengan abort_unless(isPemilik()) beneran.
     * - Item pesanan yang belum diterima bisa "Dibatalkan" (permanen, ordersdetail.masuk='2'),
     *   beda dari "Hapus" baris yang sudah diterima (balik ke pending, masih bisa diterima lagi).
     * - "Submit Pelunasan" massal: centang banyak transaksi lalu tandai LUNAS sekaligus.
     * - 3 laporan tambahan: Filter Jatuh Tempo (tagihan jatuh tempo per distributor + drill down
     *   + submit pelunasan), Filter Pembelian (rekap per tanggal), Filter Distributor (rekap per
     *   distributor + drill down). Legacy TIDAK menyaring jenis='pbf' pada laporan-laporan ini
     *   (datanya tercampur non PBF) -- diperbaiki di sini, semua disaring jenis='pbf'.
     *
     * TIDAK diadaptasi (di luar cakupan, modul terpisah "Edit/Retur/Hapus Pembelian PBF"):
     * byrkredit.php (module=byrkreditpbf). TIDAK diadaptasi (kenyamanan tambahan): scan barcode
     * kamera -- field Kode Barang manual + Enter tetap ada.
     *
     * Perbaikan bug (bukan direplikasi dari legacy, lihat catatan di masing-masing method):
     * (1) act=input_order_trbmasuk legacy PBF melakukan INSERT polos tanpa cek existing lebih
     *     dulu (beda dari non PBF yang sudah upsert) -- di sini disamakan upsert.
     * (2) act=hapus legacy PBF tidak menghapus baris `batch` -- disamakan dengan non PBF yang
     *     sudah menghapusnya.
     * (3) hapusdetail_order.php's else-branch menandai baris pending yang belum pernah
     *     tersentuh sebagai `masuk='0'` ("diterima") saat dihapus -- sama seperti bug yang
     *     sudah diperbaiki di modul non PBF, di sini juga dibuat no-op.
     * (4) simpandetail_tbm.php (insert item baru langsung) melewatkan faktor *1.11 pada
     *     hrgsat_dtrbmasuk (baris tetangganya yang mengandung formula itu dikomentari,
     *     kelihatan seperti percobaan yang batal) padahal hrgttl_dtrbmasuk pada insert yang
     *     SAMA sudah memakainya, dan ketujuh endpoint edit kolom semuanya konsisten memakai
     *     *1.11 -- di sini distandarkan memakai *1.11 sejak insert pertama juga, supaya
     *     hrgsat_dtrbmasuk/barang.hrgsat_barang tidak diam-diam berubah nilai begitu baris
     *     pertama kali diedit.
     * (5) "distributor"/tampil_distributor case punya bug PHP (`$tb['tepo' > 0]` alih-alih
     *     `$tb['tepo'] > 0`) yang membuat baris rekap tidak pernah tampil -- diperbaiki jadi
     *     perbandingan yang benar.
     *
     * Kode draft kdbm (id_resto='pusat') memakai prefix 'BPF-' (bukan 'BMP-' seperti modul non
     * PBF) supaya pool draft ON kedua modul tidak pernah tertukar -- legacy sendiri memakai
     * prefix 'BMP-' yang SAMA persis untuk kedua modul dan berbagi id_resto='pusat' yang sama,
     * yang secara teori bisa membuat draft kosong satu modul kepakai modul lain kalau staf
     * berpindah antar halaman "Tambah" sebelum transaksi pertama disimpan.
     */
    public function index()
    {
        return view('inventory.trbmasukpbf.index', ['judul' => 'Inventory']);
    }

    public function data()
    {
        $query = Trbmasuk::query()
            ->where('id_resto', 'pusat')
            ->where('jenis', 'pbf')
            ->select(['id_trbmasuk', 'kd_trbmasuk', 'kd_orders', 'petugas', 'tgl_trbmasuk', 'nm_supplier', 'ket_trbmasuk', 'sisa_bayar', 'jatuhtempo', 'carabayar']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->editColumn('sisa_bayar', fn ($row) => number_format($row->sisa_bayar, 0, ',', '.'))
            ->addColumn('checkbox', fn ($row) => '<input type="checkbox" class="checkItem" value="' . $row->kd_trbmasuk . '">')
            ->addColumn('aksi', function ($row) {
                $admin = Auth::guard('admin')->user();
                $btn = '<a href="' . route('inventory.trbmasukpbf.show', $row->id_trbmasuk) . '" class="btn btn-warning btn-xs">Tampil</a>';
                if ($admin->isPemilik()) {
                    $btn .= ' <a href="' . route('inventory.trbmasukpbf.edit', $row->id_trbmasuk) . '" class="btn btn-primary btn-xs">Edit</a>';
                }

                return $btn;
            })
            ->rawColumns(['checkbox', 'aksi'])
            ->make(true);
    }

    /**
     * Form input barang masuk langsung, mengikuti case 'tambah'.
     */
    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $setheader = Setheader::first();

        return view('inventory.trbmasukpbf.form', [
            'judul' => 'Inventory',
            'trbmasuk' => null,
            'kdTransaksi' => $this->resolveDirectEntryKode($admin->id_admin),
            'petugas' => $admin->nama_lengkap,
            'minExpDate' => $setheader->empatbelas ?? 0,
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier', 'tlp_supplier', 'alamat_supplier']),
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
        ]);
    }

    /**
     * Form ubah transaksi tersimpan, mengikuti case 'ubah'. KHUSUS pemilik -- legacy hanya
     * menyembunyikan tautannya untuk non-pemilik tanpa pengecekan di server, di sini
     * ditegakkan beneran.
     */
    public function edit(Trbmasuk $trbmasuk)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);
        abort_unless($trbmasuk->jenis === 'pbf', 404);

        $setheader = Setheader::first();

        return view('inventory.trbmasukpbf.form', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
            'kdTransaksi' => $trbmasuk->kd_trbmasuk,
            'petugas' => Auth::guard('admin')->user()->nama_lengkap,
            'minExpDate' => $setheader->empatbelas ?? 0,
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier', 'tlp_supplier', 'alamat_supplier']),
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
        ]);
    }

    /**
     * Review baca-saja satu transaksi, mengikuti case 'tampil'. Semua role bisa buka.
     */
    public function show(Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->jenis === 'pbf', 404);
        $trbmasuk->load('detail');

        return view('inventory.trbmasukpbf.show', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
        ]);
    }

    /**
     * Hapus transaksi (header + semua detail): balikkan stok, arsipkan ke
     * trbmasuk_detail_hist, hapus baris batch, kembalikan status ordersdetail kalau
     * asalnya dari pesanan, hapus kartu_stok. Mengikuti act=hapus, DIPERBAIKI supaya baris
     * `batch` juga ikut dihapus (legacy PBF tidak menghapusnya sama sekali).
     */
    public function destroy(Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->jenis === 'pbf', 404);

        DB::transaction(function () use ($trbmasuk) {
            foreach (TrbmasukDetail::where('kd_trbmasuk', $trbmasuk->kd_trbmasuk)->get() as $row) {
                Product::where('id_barang', $row->id_barang)->decrement('stok_barang', $row->qty_dtrbmasuk);

                TrbmasukDetailHist::create($row->only([
                    'kd_trbmasuk', 'kd_orders', 'id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk',
                    'sat_dtrbmasuk', 'qty_grosir', 'satgrosir_dtrbmasuk', 'hnasat_dtrbmasuk', 'diskon', 'konversi',
                    'hrgsat_dtrbmasuk', 'hrgjual_dtrbmasuk', 'hrgttl_dtrbmasuk', 'no_batch', 'exp_date', 'tipe',
                ]));

                Batch::where('kd_transaksi', $trbmasuk->kd_trbmasuk)
                    ->where('kd_barang', $row->kd_barang)
                    ->where('no_batch', $row->no_batch)
                    ->delete();

                if ($row->kd_orders) {
                    SupplierOrderDetail::where('id_barang', $row->id_barang)
                        ->where('kd_trbmasuk', $row->kd_orders)
                        ->update(['masuk' => '1']);
                }

                $row->delete();
            }

            TrxOrders::where('kd_trbmasuk', $trbmasuk->kd_trbmasuk)->delete();
            KartuStok::where('kode_transaksi', $trbmasuk->kd_trbmasuk)->delete();
            $trbmasuk->delete();
        });

        return redirect()->route('inventory.trbmasukpbf.index')->with('success', 'Transaksi barang masuk berhasil dihapus.');
    }

    // ==================== DETAIL LANGSUNG (tambah/ubah) ====================

    public function detailIndex(Request $request)
    {
        $kdTrbmasuk = (string) $request->query('kd_trbmasuk', '');

        return view('inventory.trbmasukpbf.partials.detail-table', [
            'kdTrbmasuk' => $kdTrbmasuk,
            'detail' => TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)->orderBy('id_dtrbmasuk')->get(),
            'header' => Trbmasuk::where('kd_trbmasuk', $kdTrbmasuk)->first(),
        ]);
    }

    /**
     * Tambah/gabung baris detail langsung, mengikuti simpandetail_tbm.php. Baris digabung
     * kalau (kd_barang, kd_trbmasuk, no_batch) sama. Item tipe 'bonus' tidak boleh mengubah
     * field apa pun di tabel barang selain stok.
     */
    public function detailStore(Request $request)
    {
        $validated = $request->validate([
            'kd_trbmasuk' => 'required|string|max:100',
            'id_barang' => 'required|integer|exists:barang,id_barang',
            'kd_barang' => 'required|string|max:50',
            'nmbrg_dtrbmasuk' => 'required|string|max:100',
            'qty_grosir' => 'required|numeric|gt:0',
            'sat_grosir' => 'required|string|max:30',
            'konversi' => 'required|numeric|gt:0',
            'hnasat_dtrbmasuk' => 'required|numeric|min:0',
            'hrgjual_dtrbmasuk' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0|max:100',
            // max:10 mengikuti batas kolom batch.no_batch (varchar(10)) -- lebih ketat dari
            // trbmasuk_detail.no_batch (varchar(100)) supaya baris batch selalu bisa dibuat.
            'no_batch' => 'nullable|string|max:10',
            'exp_date' => 'nullable|date',
            'tipe_barang' => 'nullable|in:reguler,bonus',
            // Diisi hanya saat dipanggil dari panel "tambah item" di layar Terima Barang dari
            // Pesanan (mis. barang datang terpecah jadi 2 no. batch berbeda untuk 1 baris
            // pesanan yang sama) -- dikosongkan untuk input langsung ('tambah') biasa.
            'kd_orders' => 'nullable|string|max:100',
        ]);

        $diskon = (float) ($validated['diskon'] ?? 0);
        $tipeBarang = $validated['tipe_barang'] ?? 'reguler';
        $expDate = $validated['exp_date'] ?? now()->addDays(720)->toDateString();
        $noBatch = $validated['no_batch'] ?? '';
        $kdOrders = $validated['kd_orders'] ?? '';

        DB::transaction(function () use ($validated, $diskon, $tipeBarang, $expDate, $noBatch, $kdOrders) {
            $existing = TrbmasukDetail::where('kd_barang', $validated['kd_barang'])
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->where('no_batch', $noBatch)
                ->first();

            if ($existing) {
                $qtyLama = $existing->qty_dtrbmasuk;
                $qtyGrosirBaru = $existing->qty_grosir + $validated['qty_grosir'];
                $qtyRetailBaru = $qtyLama + ($validated['qty_grosir'] * $validated['konversi']);
                $hrgttl = round($qtyGrosirBaru * $validated['hnasat_dtrbmasuk'] * (1 - $diskon / 100) * 1.11);

                $existing->update([
                    'qty_dtrbmasuk' => $qtyRetailBaru,
                    'qty_grosir' => $qtyGrosirBaru,
                    'hnasat_dtrbmasuk' => $validated['hnasat_dtrbmasuk'],
                    'diskon' => $diskon,
                    'hrgsat_dtrbmasuk' => $this->hitungHargaSatuan($validated['hnasat_dtrbmasuk'], $validated['konversi'], $diskon),
                    'hrgjual_dtrbmasuk' => $validated['hrgjual_dtrbmasuk'],
                    'hrgttl_dtrbmasuk' => $hrgttl,
                    'no_batch' => $noBatch,
                    'exp_date' => $expDate,
                    'tipe_barang' => $tipeBarang,
                ]);

                if ($tipeBarang === 'bonus') {
                    Product::where('id_barang', $validated['id_barang'])
                        ->update(['stok_barang' => DB::raw('stok_barang - ' . (float) $qtyLama . ' + ' . (float) $qtyRetailBaru)]);
                } else {
                    Product::where('id_barang', $validated['id_barang'])->update([
                        'stok_barang' => DB::raw('stok_barang - ' . (float) $qtyLama . ' + ' . (float) $qtyRetailBaru),
                        'hna' => $validated['hnasat_dtrbmasuk'],
                        'hrgsat_barang' => $this->hitungHargaSatuan($validated['hnasat_dtrbmasuk'], $validated['konversi'], $diskon),
                        'hrgjual_barang' => round($validated['hrgjual_dtrbmasuk']),
                    ]);
                }
            } else {
                $qtyRetail = $validated['qty_grosir'] * $validated['konversi'];
                $hrgttl = round($validated['qty_grosir'] * $validated['hnasat_dtrbmasuk'] * (1 - $diskon / 100) * 1.11);
                $barang = Product::find($validated['id_barang']);

                TrbmasukDetail::create([
                    'kd_trbmasuk' => $validated['kd_trbmasuk'],
                    'kd_orders' => $kdOrders,
                    'id_barang' => $validated['id_barang'],
                    'kd_barang' => $validated['kd_barang'],
                    'nmbrg_dtrbmasuk' => $validated['nmbrg_dtrbmasuk'],
                    'qty_dtrbmasuk' => $qtyRetail,
                    'sat_dtrbmasuk' => $barang->sat_barang,
                    'qty_grosir' => $validated['qty_grosir'],
                    'satgrosir_dtrbmasuk' => $validated['sat_grosir'],
                    'konversi' => $validated['konversi'],
                    'hnasat_dtrbmasuk' => $validated['hnasat_dtrbmasuk'],
                    'diskon' => $diskon,
                    'tipe' => 1,
                    'hrgsat_dtrbmasuk' => $this->hitungHargaSatuan($validated['hnasat_dtrbmasuk'], $validated['konversi'], $diskon),
                    'hrgjual_dtrbmasuk' => $validated['hrgjual_dtrbmasuk'],
                    'hrgttl_dtrbmasuk' => $hrgttl,
                    'no_batch' => $noBatch,
                    'exp_date' => $expDate,
                    'tipe_barang' => $tipeBarang,
                ]);

                if ($tipeBarang === 'bonus') {
                    Product::where('id_barang', $validated['id_barang'])->update([
                        'stok_barang' => DB::raw('stok_barang + ' . (float) $qtyRetail),
                    ]);
                } else {
                    Product::where('id_barang', $validated['id_barang'])->update([
                        'stok_barang' => DB::raw('stok_barang + ' . (float) $qtyRetail),
                        'hna' => $validated['hnasat_dtrbmasuk'],
                        'konversi' => $validated['konversi'],
                        'sat_grosir' => $validated['sat_grosir'],
                        'hrgsat_barang' => $this->hitungHargaSatuan($validated['hnasat_dtrbmasuk'], $validated['konversi'], $diskon),
                        'hrgjual_barang' => round($validated['hrgjual_dtrbmasuk']),
                    ]);
                }

                if ($noBatch !== '') {
                    $batch = Batch::where('kd_transaksi', $validated['kd_trbmasuk'])
                        ->where('kd_barang', $validated['kd_barang'])
                        ->where('no_batch', $noBatch)
                        ->first();

                    if ($batch) {
                        $batch->update(['qty' => $batch->qty + $validated['qty_grosir']]);
                    } else {
                        Batch::create([
                            'tgl_transaksi' => now(),
                            'no_batch' => $noBatch,
                            'exp_date' => $expDate,
                            'qty' => $validated['qty_grosir'],
                            'satuan' => $validated['sat_grosir'],
                            'kd_transaksi' => $validated['kd_trbmasuk'],
                            'kd_barang' => $validated['kd_barang'],
                            'status' => 'masuk',
                        ]);
                    }
                }
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Edit inline Qty Retail pada tabel item langsung (AJAX), mengikuti simpandetail_qty.php.
     * Beda dari endpoint lain: di sini yang diedit adalah qty RETAIL (qty_dtrbmasuk), qty
     * grosir diturunkan darinya (qty_dtrbmasuk / konversi) -- kebalikan dari alur biasa.
     */
    public function detailUpdateQty(Request $request, TrbmasukDetail $detail)
    {
        $validated = $request->validate([
            'qty_dtrbmasuk' => 'required|numeric|gt:0',
        ]);

        $qtyLama = $detail->qty_dtrbmasuk;
        $qtyBaru = $validated['qty_dtrbmasuk'];
        $qtyGrosir = $detail->konversi > 0 ? $qtyBaru / $detail->konversi : $qtyBaru;
        $hrgttl = round($qtyGrosir * $detail->hnasat_dtrbmasuk * (1 - $detail->diskon / 100) * 1.11);

        $detail->update([
            'qty_dtrbmasuk' => $qtyBaru,
            'qty_grosir' => $qtyGrosir,
            'hrgttl_dtrbmasuk' => $hrgttl,
        ]);

        Product::where('id_barang', $detail->id_barang)->update(['stok_barang' => DB::raw('stok_barang + (' . (float) ($qtyBaru - $qtyLama) . ')')]);

        if ($detail->no_batch) {
            Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                ->where('kd_barang', $detail->kd_barang)
                ->where('no_batch', $detail->no_batch)
                ->where('status', 'masuk')
                ->update(['qty' => $qtyBaru]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hapus baris detail langsung (diarsipkan dulu ke hist -- legacy PBF tidak mengarsipkan
     * baris ini sama sekali, di sini ditambahkan untuk jejak audit yang konsisten dengan
     * modul non PBF dan dengan hapus baris terima-dari-pesanan di bawah).
     */
    public function detailDestroy(TrbmasukDetail $detail)
    {
        DB::transaction(function () use ($detail) {
            Product::where('id_barang', $detail->id_barang)->decrement('stok_barang', $detail->qty_dtrbmasuk);

            TrbmasukDetailHist::create($detail->only([
                'kd_trbmasuk', 'kd_orders', 'id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk',
                'sat_dtrbmasuk', 'qty_grosir', 'satgrosir_dtrbmasuk', 'hnasat_dtrbmasuk', 'diskon', 'konversi',
                'hrgsat_dtrbmasuk', 'hrgjual_dtrbmasuk', 'hrgttl_dtrbmasuk', 'no_batch', 'exp_date', 'tipe',
            ]));

            Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                ->where('kd_barang', $detail->kd_barang)
                ->where('no_batch', $detail->no_batch)
                ->delete();

            $detail->delete();
        });

        return response()->json(['status' => 'ok']);
    }

    // ==================== FINALISASI HEADER ====================

    /**
     * Simpan header transaksi (input langsung), mengikuti act=input_trbmasuk.
     */
    public function store(Request $request)
    {
        $validated = $this->validateHeader($request);
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($validated, $admin) {
            Trbmasuk::create(array_merge($validated, [
                'id_resto' => 'pusat',
                'jenis' => 'pbf',
                'petugas' => $admin->nama_lengkap,
            ]));

            KartuStok::create(['kode_transaksi' => $validated['kd_trbmasuk']]);

            Kdbm::where('id_admin', $admin->id_admin)
                ->where('id_resto', 'pusat')
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->update(['stt_kdbm' => 'OFF']);
        });

        return redirect()->route('inventory.trbmasukpbf.index')->with('success', 'Transaksi barang masuk berhasil disimpan.');
    }

    /**
     * Simpan perubahan header transaksi tersimpan, mengikuti act=ubah_trbmasuk. KHUSUS pemilik.
     */
    public function update(Request $request, Trbmasuk $trbmasuk)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);
        abort_unless($trbmasuk->jenis === 'pbf', 404);

        $validated = $this->validateHeader($request, $trbmasuk);
        $trbmasuk->update($validated);

        return redirect()->route('inventory.trbmasukpbf.index')->with('success', 'Transaksi barang masuk berhasil diperbarui.');
    }

    // ==================== TERIMA DARI PESANAN ====================

    public function ordersIndex()
    {
        return view('inventory.trbmasukpbf.orders', ['judul' => 'Inventory']);
    }

    public function ordersData()
    {
        $query = SupplierOrder::query()->where('id_resto', 'pesan')->select([
            'id_trbmasuk', 'petugas', 'kd_trbmasuk', 'tgl_trbmasuk', 'nm_supplier',
            'ket_trbmasuk', 'ttl_trbmasuk', 'dp_bayar', 'sisa_bayar',
        ]);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->addColumn('aksi', function ($row) {
                $sudahDiproses = Trbmasuk::where('kd_orders', $row->kd_trbmasuk)->where('jenis', 'pbf')->exists();
                $adaPending = SupplierOrderDetail::where('kd_trbmasuk', $row->kd_trbmasuk)->where('masuk', '1')->exists();

                $link = route('inventory.trbmasukpbf.orders-detail', ['id' => $row->id_trbmasuk]);

                return ($sudahDiproses && !$adaPending)
                    ? '<a href="' . $link . '" class="btn btn-success btn-sm">Selesai</a>'
                    : '<a href="' . $link . '" class="btn btn-warning btn-sm">Terima</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Form terima barang dari satu pesanan, mengikuti case 'orders_detail'.
     */
    public function ordersDetail(Request $request)
    {
        $order = SupplierOrder::findOrFail($request->query('id'));
        $admin = Auth::guard('admin')->user();
        $setheader = Setheader::first();

        $kdTransaksi = $this->resolveOrderReceiveKode($admin->id_admin, $order->kd_trbmasuk);

        return view('inventory.trbmasukpbf.receive', [
            'judul' => 'Inventory',
            'order' => $order,
            'kdTransaksi' => $kdTransaksi,
            'petugas' => $admin->nama_lengkap,
            'minExpDate' => $setheader->empatbelas ?? 0,
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
        ]);
    }

    public function receiveDetailIndex(Request $request)
    {
        $kdTrbmasuk = (string) $request->query('kd_trbmasuk', '');
        $kdOrders = (string) $request->query('kd_orders', '');

        $migrated = TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)->where('kd_orders', $kdOrders)->get();
        $pending = SupplierOrderDetail::where('kd_trbmasuk', $kdOrders)->where('masuk', '1')->get();
        $dibatalkan = SupplierOrderDetail::where('kd_trbmasuk', $kdOrders)->where('masuk', '2')->get();

        return view('inventory.trbmasukpbf.partials.receive-detail-table', [
            'kdTrbmasuk' => $kdTrbmasuk,
            'kdOrders' => $kdOrders,
            'migrated' => $migrated,
            'pending' => $pending,
            'dibatalkan' => $dibatalkan,
            'subtotal' => $this->hitungSubtotalPbf($kdTrbmasuk),
            'header' => Trbmasuk::where('kd_trbmasuk', $kdTrbmasuk)->first(),
        ]);
    }

    /**
     * Edit satu kolom baris terima-dari-pesanan (AJAX), menggabungkan 7 endpoint
     * simpandetail_{batch,diskon,expdate,hna,hrgjual,konversi,qtygrosir}.php legacy jadi
     * satu. Baris yang belum pernah disentuh masih murni di ordersdetail -- disalin/
     * "dimigrasikan" ke trbmasuk_detail saat kolom apa pun pertama kali diedit.
     */
    public function receiveDetailUpdate(Request $request)
    {
        $validated = $request->validate([
            'field' => 'required|in:batch,diskon,expdate,hna,hrgjual,konversi,qtygrosir',
            'kd_trbmasuk' => 'required|string|max:100',
            'kd_orders' => 'required|string|max:100',
            'kd_barang' => 'required|string|max:50',
            'no_batch_asal' => 'nullable|string|max:100',
            // batch.no_batch (varchar(10)) lebih pendek dari trbmasuk_detail.no_batch --
            // dibatasi juga di sini supaya field 'batch' tidak lolos validasi lalu gagal SQL.
            'value' => 'required|string|max:100',
        ]);

        if ($validated['field'] === 'batch' && strlen($validated['value']) > 10) {
            return response()->json(['status' => 'error', 'message' => 'No. Batch maksimal 10 karakter.'], 422);
        }

        $field = $validated['field'];
        $value = $validated['value'];
        $noBatchAsal = $validated['no_batch_asal'] ?? '';

        $detail = DB::transaction(function () use ($validated, $field, $value, $noBatchAsal) {
            $detail = TrbmasukDetail::where('kd_barang', $validated['kd_barang'])
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->where('no_batch', $noBatchAsal)
                ->first();

            if (!$detail) {
                $detail = $this->migrateFromOrderPbf($validated, $field, $value);
            } else {
                $this->applyFieldUpdatePbf($detail, $field, $value);
            }

            return $detail->fresh();
        });

        $hnadisc = $detail->hnasat_dtrbmasuk * (1 - $detail->diskon / 100);
        $barisTotal = round($hnadisc * $detail->qty_grosir);

        return response()->json([
            'status' => 'ok',
            'id_dtrbmasuk' => $detail->id_dtrbmasuk,
            'qty_grosir' => $detail->qty_grosir,
            'no_batch' => $detail->no_batch,
            'hnadisc_text' => number_format($hnadisc, 0, ',', '.'),
            'total_text' => number_format($barisTotal, 0, ',', '.'),
            'subtotal' => number_format($this->hitungSubtotalPbf($validated['kd_trbmasuk']), 0, ',', '.'),
        ]);
    }

    /**
     * Hapus baris terima-dari-pesanan, mengikuti hapusdetail_order.php -- tapi baris yang
     * belum pernah bermigrasi ke trbmasuk_detail cukup di-no-op (lihat catatan bug (3) di
     * komentar kelas ini), tidak menandai ordersdetail sebagai "sudah diterima".
     */
    public function receiveDetailDestroy(Request $request)
    {
        $validated = $request->validate([
            'id_dtrbmasuk' => 'required|integer',
            'kd_orders' => 'required|string|max:100',
            'kd_trbmasuk' => 'required|string|max:100',
        ]);

        $detail = TrbmasukDetail::where('id_dtrbmasuk', $validated['id_dtrbmasuk'])
            ->where('kd_orders', $validated['kd_orders'])
            ->first();

        if ($detail) {
            DB::transaction(function () use ($detail) {
                Product::where('id_barang', $detail->id_barang)->decrement('stok_barang', $detail->qty_dtrbmasuk);

                SupplierOrderDetail::where('id_barang', $detail->id_barang)
                    ->where('kd_trbmasuk', $detail->kd_orders)
                    ->update(['masuk' => '1']);

                TrbmasukDetailHist::create($detail->only([
                    'kd_trbmasuk', 'kd_orders', 'id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk',
                    'sat_dtrbmasuk', 'qty_grosir', 'satgrosir_dtrbmasuk', 'hnasat_dtrbmasuk', 'diskon', 'konversi',
                    'hrgsat_dtrbmasuk', 'hrgjual_dtrbmasuk', 'hrgttl_dtrbmasuk', 'no_batch', 'exp_date', 'tipe',
                ]));

                Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                    ->where('kd_barang', $detail->kd_barang)
                    ->where('no_batch', $detail->no_batch)
                    ->delete();

                $detail->delete();
            });
        }
        // Baris yang belum pernah bermigrasi (masih murni di ordersdetail): tidak ada apa pun
        // yang perlu diubah -- baris cukup hilang dari tampilan saat ini saja.

        return response()->json([
            'status' => 'ok',
            'subtotal' => number_format($this->hitungSubtotalPbf($validated['kd_trbmasuk']), 0, ',', '.'),
        ]);
    }

    /**
     * Batalkan item pesanan yang belum diterima (masuk='1' -> '2'), mengikuti
     * batalkan_order.php. Beda dari hapus baris yang sudah diterima: sengaja TIDAK
     * dikembalikan ke pending, karena barangnya memang tidak jadi dikirim.
     */
    public function orderItemCancel(Request $request)
    {
        $validated = $request->validate([
            'kd_barang' => 'required|string|max:50',
            'kd_orders' => 'required|string|max:100',
            'kd_trbmasuk' => 'required|string|max:100',
        ]);

        $odt = SupplierOrderDetail::where('kd_barang', $validated['kd_barang'])
            ->where('kd_trbmasuk', $validated['kd_orders'])
            ->first();

        abort_if(!$odt || $odt->masuk !== '1', 422, 'Item ini sudah diterima atau sudah dibatalkan. Silakan muat ulang halaman.');

        $odt->update(['masuk' => '2']);

        return response()->json([
            'status' => 'ok',
            'subtotal' => number_format($this->hitungSubtotalPbf($validated['kd_trbmasuk']), 0, ',', '.'),
        ]);
    }

    /**
     * Simpan header transaksi terima-dari-pesanan (upsert: insert kalau baru, update kalau
     * sudah ada), mengikuti act=input_order_trbmasuk, DIPERBAIKI supaya upsert (lihat
     * catatan bug (1) di komentar kelas ini -- legacy PBF INSERT polos, bisa gagal kalau
     * disimpan dua kali untuk kd_trbmasuk yang sama).
     */
    public function storeFromOrder(Request $request)
    {
        $validated = $this->validateHeader($request);
        $validated['kd_orders'] = $request->input('kd_orders', '');
        $admin = Auth::guard('admin')->user();

        DB::transaction(function () use ($validated, $admin) {
            $existing = Trbmasuk::where('kd_trbmasuk', $validated['kd_trbmasuk'])->first();

            if ($existing) {
                $existing->update($validated);
            } else {
                Trbmasuk::create(array_merge($validated, [
                    'id_resto' => 'pusat',
                    'jenis' => 'pbf',
                    'petugas' => $admin->nama_lengkap,
                ]));

                KartuStok::create(['kode_transaksi' => $validated['kd_trbmasuk']]);
            }

            if (!TrxOrders::where('kd_trbmasuk', $validated['kd_trbmasuk'])->where('kd_orders', $validated['kd_orders'])->exists()) {
                TrxOrders::create([
                    'kd_trbmasuk' => $validated['kd_trbmasuk'],
                    'kd_orders' => $validated['kd_orders'],
                    'keterangan' => '',
                ]);
            }

            Kdbm::where('id_admin', $admin->id_admin)
                ->where('id_resto', 'pusat')
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->update(['stt_kdbm' => 'OFF']);

            SupplierOrder::where('kd_trbmasuk', $validated['kd_orders'])->update(['masuk' => '0']);
        });

        return redirect()->route('inventory.trbmasukpbf.index')->with('success', 'Transaksi terima barang berhasil disimpan.');
    }

    // ==================== EVALUASI BARANG MASUK ====================

    public function evaluasiIndex()
    {
        return view('inventory.trbmasukpbf.evaluasi', ['judul' => 'Inventory']);
    }

    public function evaluasiData()
    {
        $query = Trbmasuk::query()
            ->where('id_resto', 'pusat')
            ->where('jenis', 'pbf')
            ->where('kd_orders', '!=', '')
            ->select(['id_trbmasuk', 'kd_trbmasuk', 'petugas', 'tgl_trbmasuk', 'nm_supplier', 'ket_trbmasuk', 'sisa_bayar', 'jatuhtempo', 'carabayar']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->editColumn('sisa_bayar', fn ($row) => number_format($row->sisa_bayar, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('inventory.trbmasukpbf.evaluasi.show', $row->id_trbmasuk) . '" class="btn btn-info btn-sm">Tampil</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Perbandingan qty/harga pesan vs masuk, mengikuti case 'evaluasi_tampil'. Beda dari
     * non PBF: baris dengan qty_masuk=0 dibedakan "Dibatalkan" (ordersdetail.masuk='2', dari
     * fitur Batalkan Item) vs "Belum Diterima" (masuk='1', masih pending).
     */
    public function evaluasiShow(Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->kd_orders !== '', 404);

        $rows = DB::table('ordersdetail as od')
            ->leftJoin('trbmasuk_detail as td', function ($join) {
                $join->on('td.kd_orders', '=', 'od.kd_trbmasuk')->on('td.id_barang', '=', 'od.id_barang');
            })
            ->where('od.kd_trbmasuk', $trbmasuk->kd_orders)
            ->groupBy('od.id_barang', 'od.kd_barang', 'od.nmbrg_dtrbmasuk', 'od.sat_dtrbmasuk', 'od.qty_dtrbmasuk', 'od.hrgsat_dtrbmasuk', 'od.masuk')
            ->orderBy('od.nmbrg_dtrbmasuk')
            ->selectRaw('od.kd_barang, od.id_barang, od.nmbrg_dtrbmasuk, od.sat_dtrbmasuk, od.masuk as status_pesan,
                od.qty_dtrbmasuk as qty_pesan, od.hrgsat_dtrbmasuk as hrgsat_pesan,
                COALESCE(SUM(td.qty_dtrbmasuk), 0) as qty_masuk,
                COALESCE(SUM(td.qty_dtrbmasuk * td.hrgsat_dtrbmasuk), 0) as totalharga_masuk')
            ->get()
            ->map(function ($row) {
                $qtyMasuk = (float) $row->qty_masuk;
                $totalMasuk = (float) $row->totalharga_masuk;
                $row->hrgsat_masuk = $qtyMasuk > 0 ? round($totalMasuk / $qtyMasuk) : 0;
                $row->status_label = $qtyMasuk > 0 ? 'Diterima' : ($row->status_pesan === '2' ? 'Dibatalkan' : 'Belum Diterima');

                return $row;
            });

        return view('inventory.trbmasukpbf.evaluasi-show', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
            'rows' => $rows,
            'total' => $rows->sum('totalharga_masuk'),
        ]);
    }

    // ==================== CARI NO. BATCH ====================

    public function batchSearchForm()
    {
        return view('inventory.trbmasukpbf.batch-search', ['judul' => 'Inventory']);
    }

    public function batchSearchResult(Request $request)
    {
        $noBatch = (string) $request->input('no_batch', '');

        $rows = DB::table('trbmasuk_detail as a')
            ->join('trbmasuk as b', 'a.kd_trbmasuk', '=', 'b.kd_trbmasuk')
            ->where('a.no_batch', $noBatch)
            ->where('b.jenis', 'pbf')
            ->orderByDesc('b.tgl_trbmasuk')
            ->select(['a.*', 'b.nm_supplier', 'b.tgl_trbmasuk'])
            ->get();

        return view('inventory.trbmasukpbf.batch-search-result', [
            'judul' => 'Inventory',
            'noBatch' => $noBatch,
            'rows' => $rows,
        ]);
    }

    // ==================== SUBMIT PELUNASAN MASSAL ====================

    /**
     * Tandai beberapa transaksi LUNAS sekaligus, mengikuti ubah_status_lunas.php.
     * Dipakai dari daftar utama, daftar evaluasi, dan drill-down Filter Jatuh Tempo.
     */
    public function markLunas(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->isPemilik(), 403);

        $validated = $request->validate([
            'kode' => 'required|array|min:1',
            'kode.*' => 'string|max:100',
        ]);

        $admin = Auth::guard('admin')->user();

        Trbmasuk::whereIn('kd_trbmasuk', $validated['kode'])
            ->where('jenis', 'pbf')
            ->update([
                'carabayar' => 'LUNAS',
                'tgl_lunas' => now()->toDateString(),
                'petugas_lunas' => $admin->nama_lengkap,
            ]);

        return back()->with('success', count($validated['kode']) . ' transaksi berhasil ditandai LUNAS.');
    }

    // ==================== FILTER JATUH TEMPO ====================

    public function jatuhTempoForm()
    {
        return view('inventory.trbmasukpbf.jatuh-tempo', ['judul' => 'Inventory']);
    }

    public function jatuhTempoResult(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = Trbmasuk::query()
            ->where('jenis', 'pbf')
            ->where('carabayar', '!=', 'LUNAS')
            ->whereBetween('jatuhtempo', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->selectRaw('jatuhtempo, nm_supplier, id_supplier, SUM(ttl_trbmasuk) as hutang')
            ->groupBy('id_supplier', 'nm_supplier', 'jatuhtempo')
            ->orderBy('jatuhtempo')
            ->get();

        return view('inventory.trbmasukpbf.jatuh-tempo-result', [
            'judul' => 'Inventory',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('hutang'),
        ]);
    }

    public function jatuhTempoDetail(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'id' => 'required|integer|exists:supplier,id_supplier',
        ]);

        $rows = Trbmasuk::query()
            ->where('jenis', 'pbf')
            ->where('id_supplier', $validated['id'])
            ->where('carabayar', '!=', 'LUNAS')
            ->whereBetween('jatuhtempo', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->orderBy('jatuhtempo')
            ->get();

        return view('inventory.trbmasukpbf.jatuh-tempo-detail', [
            'judul' => 'Inventory',
            'rows' => $rows,
            'total' => $rows->sum('ttl_trbmasuk'),
            'bisaPelunasan' => Auth::guard('admin')->user()->isPemilik(),
        ]);
    }

    // ==================== FILTER PEMBELIAN ====================

    public function pembelianForm()
    {
        return view('inventory.trbmasukpbf.pembelian', ['judul' => 'Inventory']);
    }

    public function pembelianResult(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $base = Trbmasuk::query()->where('jenis', 'pbf')
            ->whereBetween('tgl_trbmasuk', [$validated['tgl_awal'], $validated['tgl_akhir']]);

        $rows = (clone $base)->orderBy('tgl_trbmasuk')->get();

        return view('inventory.trbmasukpbf.pembelian-result', [
            'judul' => 'Inventory',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('ttl_trbmasuk'),
            'totalLunas' => (clone $base)->where('carabayar', 'LUNAS')->sum('ttl_trbmasuk'),
            'totalKredit' => (clone $base)->where('carabayar', 'KREDIT')->sum('ttl_trbmasuk'),
        ]);
    }

    // ==================== FILTER DISTRIBUTOR ====================

    public function distributorForm()
    {
        return view('inventory.trbmasukpbf.distributor', ['judul' => 'Inventory']);
    }

    /**
     * Rekap pembelian per distributor, mengikuti case 'tampil_distributor'. Legacy punya bug
     * PHP (`$tb['tepo' > 0]` alih-alih `$tb['tepo'] > 0`) yang membuat baris rekap tidak
     * pernah tampil (lihat catatan bug (5) di komentar kelas ini) -- diperbaiki jadi
     * perbandingan yang benar (lewati distributor tanpa transaksi di rentang tanggal ini).
     */
    public function distributorResult(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
        ]);

        $rows = Trbmasuk::query()
            ->where('jenis', 'pbf')
            ->whereBetween('tgl_trbmasuk', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->selectRaw('id_supplier, nm_supplier, SUM(ttl_trbmasuk) as total,
                SUM(CASE WHEN carabayar = "LUNAS" THEN ttl_trbmasuk ELSE 0 END) as total_lunas,
                SUM(CASE WHEN carabayar = "KREDIT" THEN ttl_trbmasuk ELSE 0 END) as total_kredit')
            ->groupBy('id_supplier', 'nm_supplier')
            ->havingRaw('SUM(ttl_trbmasuk) > 0')
            ->orderByDesc('total')
            ->get();

        return view('inventory.trbmasukpbf.distributor-result', [
            'judul' => 'Inventory',
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('total'),
            'totalLunas' => $rows->sum('total_lunas'),
            'totalKredit' => $rows->sum('total_kredit'),
        ]);
    }

    public function distributorDetail(Request $request)
    {
        $validated = $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date',
            'id' => 'required|integer|exists:supplier,id_supplier',
        ]);

        $supplier = Supplier::findOrFail($validated['id']);

        $rows = Trbmasuk::query()
            ->where('jenis', 'pbf')
            ->where('id_supplier', $validated['id'])
            ->whereBetween('tgl_trbmasuk', [$validated['tgl_awal'], $validated['tgl_akhir']])
            ->orderBy('tgl_trbmasuk')
            ->get();

        return view('inventory.trbmasukpbf.distributor-detail', [
            'judul' => 'Inventory',
            'supplier' => $supplier,
            'tglAwal' => $validated['tgl_awal'],
            'tglAkhir' => $validated['tgl_akhir'],
            'rows' => $rows,
            'total' => $rows->sum('ttl_trbmasuk'),
        ]);
    }

    // ==================== PENCARIAN/PEMILIHAN BARANG ====================

    public function itemSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        if ($query === '') {
            return response()->json([]);
        }

        return response()->json(
            Product::where('nm_barang', 'like', "%{$query}%")->orderBy('nm_barang')->limit(20)->get(['nm_barang', 'stok_barang', 'sat_barang'])
        );
    }

    public function itemResolve(Request $request)
    {
        $namaBarang = $request->input('nm_barang');
        $kdBarang = $request->input('kd_barang');

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

        return response()->json([
            'id_barang' => $barang->id_barang,
            'nm_barang' => $barang->nm_barang,
            'stok_barang' => $barang->stok_barang,
            'sat_barang' => $barang->sat_barang,
            'sat_grosir' => $barang->sat_grosir,
            'konversi' => $barang->konversi,
            'hna' => $barang->hna,
            'hrgjual_barang' => $barang->hrgjual_barang,
            'kd_barang' => (string) $barang->kd_barang,
        ]);
    }

    public function itemPicker()
    {
        $query = Product::query()->select(['id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'sat_barang', 'hna', 'hrgjual_barang']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('kd_barang', fn ($row) => (string) $row->kd_barang)
            ->editColumn('hna', fn ($row) => number_format($row->hna, 0, ',', '.'))
            ->editColumn('hrgjual_barang', fn ($row) => number_format($row->hrgjual_barang, 0, ',', '.'))
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-barang' data-nm_barang='" . e($row->nm_barang) . "'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    // ==================== HELPER PRIVAT ====================

    private function validateHeader(Request $request, ?Trbmasuk $existing = null): array
    {
        $validated = $request->validate([
            'kd_trbmasuk' => 'required|string|max:100',
            'tgl_trbmasuk' => 'required|date',
            'id_supplier' => 'required|integer|exists:supplier,id_supplier',
            'nm_supplier' => 'required|string|max:50',
            'tlp_supplier' => 'nullable|string|max:50',
            'alamat_trbmasuk' => 'nullable|string',
            'ket_trbmasuk' => 'nullable|string',
            'ttl_trbmasuk' => 'required|numeric|min:0',
            'dp_bayar' => 'required|numeric|min:0',
            'sisa_bayar' => 'required|numeric|min:0',
            'carabayar' => 'required|in:LUNAS,KREDIT,KONSINYASI',
            'jatuhtempo' => 'nullable|string|max:20',
        ]);

        $validated['tlp_supplier'] = $validated['tlp_supplier'] ?? '';
        $validated['alamat_trbmasuk'] = $validated['alamat_trbmasuk'] ?? '';
        $validated['ket_trbmasuk'] = $validated['ket_trbmasuk'] ?? '';
        $validated['jatuhtempo'] = $validated['jatuhtempo'] ?? '';

        if (!$existing) {
            $validated['kd_orders'] = $validated['kd_orders'] ?? '';
        }

        // Mengikuti act=input_trbmasuk/ubah_trbmasuk legacy: setiap kali disimpan dengan
        // carabayar=LUNAS, tgl_lunas/petugas_lunas SELALU di-stamp ulang ke hari ini/petugas
        // yang sedang login (bukan hanya saat transisi dari belum-lunas ke lunas).
        $validated['tgl_lunas'] = $validated['carabayar'] === 'LUNAS' ? now()->toDateString() : '1970-01-01';
        $validated['petugas_lunas'] = $validated['carabayar'] === 'LUNAS' ? Auth::guard('admin')->user()->nama_lengkap : '';

        return $validated;
    }

    /**
     * Kode transaksi terbuka untuk input langsung, prefix 'BPF-' (lihat catatan pool draft
     * terpisah di komentar kelas ini). Draft dipakai ulang hanya kalau belum berisi baris
     * pesanan apa pun.
     */
    private function resolveDirectEntryKode(int $idAdmin): string
    {
        $existing = Kdbm::where('id_admin', $idAdmin)
            ->where('id_resto', 'pusat')
            ->where('stt_kdbm', 'ON')
            ->where('kd_trbmasuk', 'like', 'BPF-%')
            ->whereNotIn('kd_trbmasuk', function ($q) {
                $q->select('kd_trbmasuk')->from('trbmasuk_detail')->where('kd_orders', '!=', '');
            })
            ->orderByDesc('id_kdbm')
            ->first();

        if ($existing) {
            return $existing->kd_trbmasuk;
        }

        return $this->createKdbm($idAdmin);
    }

    private function resolveOrderReceiveKode(int $idAdmin, string $kdOrders): string
    {
        $existing = Trbmasuk::where('kd_orders', $kdOrders)->where('jenis', 'pbf')->first();
        if ($existing) {
            return $existing->kd_trbmasuk;
        }

        $existingKdbm = Kdbm::where('id_admin', $idAdmin)
            ->where('id_resto', 'pusat')
            ->where('stt_kdbm', 'ON')
            ->where('kd_trbmasuk', 'like', 'BPF-%')
            ->whereNotIn('kd_trbmasuk', function ($q) use ($kdOrders) {
                $q->select('kd_trbmasuk')->from('trbmasuk_detail')
                    ->where(function ($q2) use ($kdOrders) {
                        $q2->where('kd_orders', '')->orWhere('kd_orders', '!=', $kdOrders);
                    });
            })
            ->orderByDesc('id_kdbm')
            ->first();

        if ($existingKdbm) {
            return $existingKdbm->kd_trbmasuk;
        }

        return $this->createKdbm($idAdmin);
    }

    private function createKdbm(int $idAdmin): string
    {
        $kode = 'BPF-' . now()->format('dmyhis');
        if (Kdbm::where('kd_trbmasuk', $kode)->exists()) {
            $kode = 'BPF-' . now()->addSecond()->format('dmyhis');
        }

        Kdbm::create(['kd_trbmasuk' => $kode, 'id_resto' => 'pusat', 'id_admin' => $idAdmin]);

        return $kode;
    }

    /**
     * Harga satuan retail acuan (barang.hrgsat_barang / trbmasuk_detail.hrgsat_dtrbmasuk),
     * mengikuti rumus di ketujuh endpoint simpandetail_*.php: HNA dianggap harga SEBELUM
     * pajak, PPN 11% ditambahkan di atasnya lalu dikonversi ke satuan retail dan dipotong
     * diskon.
     */
    private function hitungHargaSatuan(float $hna, float $konversi, float $diskon): float
    {
        return round(($hna / $konversi) * (1 - $diskon / 100) * 1.11);
    }

    /**
     * Total baris (trbmasuk_detail.hrgttl_dtrbmasuk), rumus persis dari
     * simpandetail_*.php: (HNA * 1.11 * qty_grosir) dibulatkan DULU, baru dipotong diskon.
     */
    private function hitungTotalBaris(float $hna, float $qtyGrosir, float $diskon): float
    {
        return round($hna * 1.11 * $qtyGrosir) * (1 - $diskon / 100);
    }

    /**
     * Migrasi baris dari ordersdetail ke trbmasuk_detail sambil menerapkan nilai kolom yang
     * baru pertama kali diedit, memakai rumus harga HNA-based PBF (lihat hitungHargaSatuan/
     * hitungTotalBaris).
     */
    private function migrateFromOrderPbf(array $validated, string $field, string $value): TrbmasukDetail
    {
        $odt = SupplierOrderDetail::where('kd_barang', $validated['kd_barang'])
            ->where('kd_trbmasuk', $validated['kd_orders'])
            ->first();

        if (!$odt || $odt->masuk !== '1') {
            abort(422, 'Item sudah diterima pada transaksi lain. Silakan muat ulang halaman.');
        }

        $konversi = $field === 'konversi' ? (float) $value : $odt->konversi;
        $qtyGrosir = $field === 'qtygrosir' ? (float) $value : $odt->qtygrosir_dtrbmasuk;
        $hna = $field === 'hna' ? (float) str_replace('.', '', $value) : $odt->hrgsat_dtrbmasuk;
        $hrgjual = $field === 'hrgjual' ? (float) str_replace('.', '', $value) : $odt->hrgjual_dtrbmasuk;
        $diskon = $field === 'diskon' ? (float) $value : $odt->diskon;
        $noBatch = $field === 'batch' ? $value : $odt->no_batch;
        $expDate = $field === 'expdate' ? $value : $odt->exp_date;
        $qtyDtrbmasuk = $konversi * $qtyGrosir;
        $hrgsat = $this->hitungHargaSatuan($hna, $konversi, $diskon);
        $hrgttl = $this->hitungTotalBaris($hna, $qtyGrosir, $diskon);
        $tipeBarang = $odt->tipe_barang ?? 'reguler';

        $detail = TrbmasukDetail::create([
            'kd_trbmasuk' => $validated['kd_trbmasuk'],
            'kd_orders' => $validated['kd_orders'],
            'id_barang' => $odt->id_barang,
            'kd_barang' => $odt->kd_barang,
            'nmbrg_dtrbmasuk' => $odt->nmbrg_dtrbmasuk,
            'qty_dtrbmasuk' => $qtyDtrbmasuk,
            'sat_dtrbmasuk' => $odt->sat_dtrbmasuk,
            'qty_grosir' => $qtyGrosir,
            'satgrosir_dtrbmasuk' => $odt->satgrosir_dtrbmasuk,
            'konversi' => $konversi,
            'hnasat_dtrbmasuk' => $hna,
            'diskon' => $diskon,
            'tipe' => 0,
            'hrgsat_dtrbmasuk' => $hrgsat,
            'hrgjual_dtrbmasuk' => $hrgjual,
            'hrgttl_dtrbmasuk' => $hrgttl,
            'no_batch' => $noBatch,
            'exp_date' => $expDate,
            'tipe_barang' => $tipeBarang,
        ]);

        $hrgjualBarang = round($hrgjual);

        if ($tipeBarang === 'bonus') {
            Product::where('id_barang', $odt->id_barang)->update([
                'stok_barang' => DB::raw('stok_barang + ' . (float) $qtyDtrbmasuk),
            ]);
        } else {
            Product::where('id_barang', $odt->id_barang)->update([
                'stok_barang' => DB::raw('stok_barang + ' . (float) $qtyDtrbmasuk),
                'hna' => $hna,
                'hrgsat_barang' => $hrgsat,
                'hrgsat_grosir' => round($hna),
                'hrgjual_barang' => $hrgjualBarang,
            ]);
        }

        $odt->update(['masuk' => '0']);

        if ($noBatch !== '') {
            $batch = Batch::where('kd_transaksi', $validated['kd_trbmasuk'])
                ->where('kd_barang', $odt->kd_barang)
                ->where('no_batch', $noBatch)
                ->first();

            if ($batch) {
                $batch->update(['qty' => $batch->qty + $qtyGrosir]);
            } else {
                Batch::create([
                    'tgl_transaksi' => now(),
                    'no_batch' => $noBatch,
                    'exp_date' => $expDate,
                    'qty' => $qtyGrosir,
                    'satuan' => $odt->satgrosir_dtrbmasuk,
                    'kd_transaksi' => $validated['kd_trbmasuk'],
                    'kd_barang' => $odt->kd_barang,
                    'status' => 'masuk',
                ]);
            }
        }

        return $detail;
    }

    /**
     * Terapkan perubahan satu kolom pada baris yang sudah bermigrasi ke trbmasuk_detail,
     * memakai rumus harga HNA-based PBF. Item tipe 'bonus' tidak boleh mengubah field
     * apa pun di tabel barang selain stok_barang.
     */
    private function applyFieldUpdatePbf(TrbmasukDetail $detail, string $field, string $value): void
    {
        $qtyLama = $detail->qty_dtrbmasuk;
        $noBatchLama = $detail->no_batch;
        $isBonus = $detail->tipe_barang === 'bonus';

        switch ($field) {
            case 'batch':
                $detail->no_batch = $value;
                $batchLama = Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                    ->where('kd_barang', $detail->kd_barang)
                    ->where('no_batch', $noBatchLama)
                    ->first();

                if ($batchLama) {
                    $batchLama->update(['no_batch' => $value]);
                } elseif ($value !== '') {
                    // Baris ini bermigrasi tanpa no. batch (belum ada baris `batch` untuk
                    // di-rename) -- dibuatkan baris baru supaya tetap muncul di Cari No. Batch.
                    Batch::create([
                        'tgl_transaksi' => now(),
                        'no_batch' => $value,
                        'exp_date' => $detail->exp_date,
                        'qty' => $detail->qty_grosir,
                        'satuan' => $detail->satgrosir_dtrbmasuk,
                        'kd_transaksi' => $detail->kd_trbmasuk,
                        'kd_barang' => $detail->kd_barang,
                        'status' => 'masuk',
                    ]);
                }
                break;

            case 'diskon':
                $detail->diskon = (float) $value;
                break;

            case 'expdate':
                $detail->exp_date = $value;
                Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                    ->where('kd_barang', $detail->kd_barang)
                    ->update(['exp_date' => $value]);
                break;

            case 'hna':
                $detail->hnasat_dtrbmasuk = (float) str_replace('.', '', $value);
                break;

            case 'hrgjual':
                $detail->hrgjual_dtrbmasuk = (float) str_replace('.', '', $value);
                if (!$isBonus) {
                    Product::where('id_barang', $detail->id_barang)->update(['hrgjual_barang' => round($detail->hrgjual_dtrbmasuk)]);
                }
                break;

            case 'konversi':
                $detail->konversi = (float) $value;
                $detail->qty_dtrbmasuk = $detail->konversi * $detail->qty_grosir;
                break;

            case 'qtygrosir':
                $detail->qty_grosir = (float) $value;
                $detail->qty_dtrbmasuk = $detail->konversi * $detail->qty_grosir;
                if ($detail->no_batch) {
                    Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                        ->where('kd_barang', $detail->kd_barang)
                        ->where('no_batch', $detail->no_batch)
                        ->update(['qty' => $detail->qty_grosir]);
                }
                break;
        }

        $detail->hrgsat_dtrbmasuk = $this->hitungHargaSatuan($detail->hnasat_dtrbmasuk, $detail->konversi, $detail->diskon);
        $detail->hrgttl_dtrbmasuk = $this->hitungTotalBaris($detail->hnasat_dtrbmasuk, $detail->qty_grosir, $detail->diskon);
        $detail->save();

        if (!$isBonus && in_array($field, ['hna', 'diskon', 'konversi', 'qtygrosir'], true)) {
            Product::where('id_barang', $detail->id_barang)->update([
                'hna' => $detail->hnasat_dtrbmasuk,
                'hrgsat_barang' => $detail->hrgsat_dtrbmasuk,
                'hrgsat_grosir' => round($detail->hnasat_dtrbmasuk),
            ]);
        }

        $selisihQty = $detail->qty_dtrbmasuk - $qtyLama;
        if ($selisihQty != 0) {
            Product::where('id_barang', $detail->id_barang)->update(['stok_barang' => DB::raw('stok_barang + (' . (float) $selisihQty . ')')]);
        }
    }

    /**
     * Subtotal "Total Harga" (TANPA PPN) untuk footer tabel terima-dari-pesanan, mengikuti
     * helper_subtotal.php's hitung_subtotal_pbf(): HANYA menjumlahkan baris yang SUDAH
     * bermigrasi ke trbmasuk_detail -- beda dari non PBF yang juga menjumlahkan baris
     * pending di ordersdetail. Perilaku aslinya memang begitu (dikonfirmasi dari legacy).
     */
    private function hitungSubtotalPbf(string $kdTrbmasuk): float
    {
        return (float) TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)
            ->selectRaw('COALESCE(SUM(ROUND(hnasat_dtrbmasuk * (1 - diskon / 100) * qty_grosir)), 0) as total')
            ->value('total');
    }
}
