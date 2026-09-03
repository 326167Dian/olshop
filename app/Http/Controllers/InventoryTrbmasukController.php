<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\KartuStok;
use App\Models\Kdbm;
use App\Models\Product;
use App\Models\Setheader;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderDetail;
use App\Models\Trbmasuk;
use App\Models\TrbmasukDetail;
use App\Models\TrbmasukDetailHist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTrbmasukController extends Controller
{
    /**
     * Modul "Barang Masuk non PBF" (module=trbmasuk), mengikuti
     * public/apotekberlian/masuk/modul/mod_trbmasuk/*.php.
     *
     * Cakupan: input barang masuk langsung (tanpa pesanan) + daftar + review baca-saja,
     * terima barang dari Pesanan Barang (menyelesaikan modul Pesan Barang), Evaluasi
     * Barang Masuk (bandingkan qty/harga pesan vs masuk), dan Cari No. Batch.
     * TIDAK diadaptasi (di luar cakupan, modul terpisah "Edit/Retur/Hapus Pembelian"):
     * byrkredit.php/byrkredit2.php.
     * TIDAK diadaptasi (kenyamanan tambahan, bukan inti): scan barcode via kamera --
     * field Kode Barang manual + Enter tetap ada.
     *
     * Legacy punya 7 endpoint AJAX nyaris identik (simpandetail_{batch,diskon,expdate,
     * hrgbeli,hrgjual,konversi,qtygrosir}_order.php) untuk edit-inline per kolom di grid
     * "terima dari pesanan" -- di sini digabung jadi satu method receiveDetailUpdate()
     * dengan parameter 'field', supaya tidak menduplikasi logika "cari baris existing di
     * trbmasuk_detail, kalau belum ada migrasikan dari ordersdetail" tujuh kali.
     *
     * Bug legacy yang TIDAK direplikasi: hapusdetail_order.php, kalau baris yang dihapus
     * belum pernah disentuh (masih murni di ordersdetail, belum bermigrasi ke
     * trbmasuk_detail), menandai ordersdetail.masuk='0' (arti: "sudah diterima") padahal
     * tidak ada barang yang benar-benar diterima/masuk stok -- baris itu lenyap begitu
     * saja secara diam-diam. Di sini baris yang belum pernah disentuh cukup di-no-op
     * (tidak ada perubahan DB), meniru perilaku JS legacy yang cuma menghilangkan baris
     * dari tabel saat itu saja (baris akan muncul lagi saat halaman dimuat ulang, sesuai
     * mestinya karena belum benar-benar diterima).
     */
    public function index()
    {
        return view('inventory.trbmasuk.index', ['judul' => 'Inventory']);
    }

    public function data()
    {
        $query = Trbmasuk::query()
            ->where('id_resto', 'pusat')
            ->where('jenis', 'nonpbf')
            ->select(['id_trbmasuk', 'kd_trbmasuk', 'petugas', 'tgl_trbmasuk', 'nm_supplier', 'ket_trbmasuk', 'sisa_bayar', 'carabayar']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->editColumn('sisa_bayar', fn ($row) => number_format($row->sisa_bayar, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('inventory.trbmasuk.show', $row->id_trbmasuk) . '" class="btn btn-warning btn-sm">Tampil</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Form input barang masuk langsung, mengikuti case 'tambah'.
     */
    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $setheader = Setheader::first();

        return view('inventory.trbmasuk.create', [
            'judul' => 'Inventory',
            'kdTransaksi' => $this->resolveDirectEntryKode($admin->id_admin),
            'petugas' => $admin->nama_lengkap,
            'minExpDate' => $setheader->empatbelas ?? 0,
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier', 'tlp_supplier', 'alamat_supplier']),
        ]);
    }

    /**
     * Review baca-saja satu transaksi barang masuk, mengikuti case 'ubah' pada modul
     * trbmasuk itu sendiri. Tombol "Simpan Transaksi" di legacy DI SINI sengaja
     * dikomentari -- staf biasa (flag 'tbm') hanya boleh melihat, tidak mengedit. Edit
     * transaksi tersimpan (termasuk tambah/hapus item) hanya tersedia lewat modul
     * terpisah "Edit/Retur/Hapus Pembelian" (lihat edit()/update() di bawah, dipakai
     * dari route yang digerbang flag 'byrkredit', BUKAN dari sini).
     */
    public function show(Trbmasuk $trbmasuk)
    {
        $trbmasuk->load('detail');

        return view('inventory.trbmasuk.show', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
        ]);
    }

    /**
     * Form ubah transaksi tersimpan, mengikuti mod_trbmasuk/byrkredit.php's case 'ubah'
     * (module=byrkredit) -- BUKAN trbmasuk's sendiri case 'ubah' yang mati/baca-saja.
     * Dipanggil lewat route yang digerbang flag 'byrkredit' (lihat routes/web.php),
     * bukan 'tbm' -- byrkredit adalah modul TERPISAH khusus untuk mengedit/retur/hapus
     * pembelian yang sudah tersimpan, tanpa pengecekan level pemilik (legacy juga tidak
     * membatasi berdasar level di sini, beda dari trbmasukpbf yang sengaja diperketat).
     */
    public function edit(Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->jenis === 'nonpbf', 404);

        return view('inventory.byrkredit.edit', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier', 'tlp_supplier', 'alamat_supplier']),
        ]);
    }

    /**
     * Simpan perubahan header transaksi tersimpan, mengikuti act=ubah_trbmasuk.
     */
    public function update(Request $request, Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->jenis === 'nonpbf', 404);

        $validated = $this->validateHeader($request);
        // kd_orders TIDAK boleh ikut diupdate di sini -- validateHeader() men-default-kan
        // ke '' untuk alur input langsung yang baru, tapi form ubah ini tidak pernah
        // mengirim kd_orders sama sekali, jadi kalau ikut disimpan akan diam-diam
        // menghapus tautan ke pesanan asal transaksi yang berasal dari alur terima pesanan.
        unset($validated['kd_orders']);
        $trbmasuk->update($validated);

        return redirect()->route('inventory.byrkredit.index')->with('success', 'Transaksi barang masuk berhasil diperbarui.');
    }

    /**
     * Hapus transaksi (header + semua detail): balikkan stok, arsipkan ke
     * trbmasuk_detail_hist, hapus baris batch, kembalikan status ordersdetail kalau
     * asalnya dari pesanan, hapus kartu_stok. Mengikuti act=hapus di aksi_trbmasuk.php.
     */
    public function destroy(Trbmasuk $trbmasuk)
    {
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
                    ->where('status', 'masuk')
                    ->delete();

                if ($row->kd_orders) {
                    SupplierOrderDetail::where('id_barang', $row->id_barang)
                        ->where('kd_trbmasuk', $row->kd_orders)
                        ->update(['masuk' => '1']);
                }

                $row->delete();
            }

            KartuStok::where('kode_transaksi', $trbmasuk->kd_trbmasuk)->delete();
            $trbmasuk->delete();
        });

        return redirect()->route('inventory.trbmasuk.index')->with('success', 'Transaksi barang masuk berhasil dihapus.');
    }

    // ==================== DETAIL LANGSUNG (tanpa pesanan) ====================

    public function detailIndex(Request $request)
    {
        $kdTrbmasuk = (string) $request->query('kd_trbmasuk', '');

        return view('inventory.trbmasuk.partials.detail-table', [
            'kdTrbmasuk' => $kdTrbmasuk,
            'detail' => TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)->orderBy('id_dtrbmasuk')->get(),
            'header' => Trbmasuk::where('kd_trbmasuk', $kdTrbmasuk)->first(),
            // 'byrkredit' saat partial ini dimuat dari layar Edit/Retur/Hapus Pembelian --
            // supaya tombol qty/hapus di partial memakai rute yang digerbang flag 'byrkredit',
            // bukan 'tbm' (partial ini dipakai bersama oleh kedua modul).
            'mode' => $request->query('mode', 'tbm'),
        ]);
    }

    /**
     * Tambah/gabung baris detail langsung, mengikuti simpandetail_tbm.php. Baris
     * digabung kalau (kd_barang, kd_trbmasuk, no_batch) sama.
     */
    public function detailStore(Request $request)
    {
        $validated = $request->validate([
            'kd_trbmasuk' => 'required|string|max:100',
            'id_barang' => 'required|integer|exists:barang,id_barang',
            'kd_barang' => 'required|string|max:50',
            'nmbrg_dtrbmasuk' => 'required|string|max:100',
            'qty_dtrbmasuk' => 'required|numeric|min:0',
            'sat_dtrbmasuk' => 'required|string|max:30',
            'hrgsat_dtrbmasuk' => 'required|numeric|min:0',
            'hrgjual_dtrbmasuk' => 'required|numeric|min:0',
            'hrgjual_dtrbmasuk_resep' => 'required|numeric|min:0',
            'hrgjual_dtrbmasuk_nakes' => 'required|numeric|min:0',
            // max:10 mengikuti batas kolom batch.no_batch (varchar(10)) -- lebih ketat dari
            // trbmasuk_detail.no_batch (varchar(100)) supaya baris batch selalu bisa dibuat.
            'no_batch' => 'required|string|max:10',
            'exp_date' => 'nullable|date',
        ]);

        $expDate = $validated['exp_date'] ?? now()->addDays(720)->toDateString();

        DB::transaction(function () use ($validated, $expDate) {
            $existing = TrbmasukDetail::where('kd_barang', $validated['kd_barang'])
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->where('no_batch', $validated['no_batch'])
                ->first();

            if ($existing) {
                $ttlQty = $existing->qty_dtrbmasuk + $validated['qty_dtrbmasuk'];

                $existing->update([
                    'qty_dtrbmasuk' => $ttlQty,
                    'hrgsat_dtrbmasuk' => $validated['hrgsat_dtrbmasuk'],
                    'hrgjual_dtrbmasuk' => $validated['hrgjual_dtrbmasuk'],
                    'hrgttl_dtrbmasuk' => $ttlQty * $validated['hrgsat_dtrbmasuk'],
                    'exp_date' => $expDate,
                ]);

                Product::where('id_barang', $validated['id_barang'])->update([
                    'stok_barang' => DB::raw('stok_barang + ' . (float) $validated['qty_dtrbmasuk']),
                    'sat_barang' => $validated['sat_dtrbmasuk'],
                    'hrgsat_barang' => $validated['hrgsat_dtrbmasuk'],
                ]);

                $batch = Batch::where('no_batch', $validated['no_batch'])
                    ->where('kd_transaksi', $validated['kd_trbmasuk'])
                    ->where('kd_barang', $validated['kd_barang'])
                    ->where('status', 'masuk')
                    ->first();
                if ($batch) {
                    $batch->update(['qty' => $batch->qty + $validated['qty_dtrbmasuk']]);
                }
            } else {
                $barang = Product::find($validated['id_barang']);
                $ttlHarga = $validated['qty_dtrbmasuk'] * $validated['hrgsat_dtrbmasuk'];

                TrbmasukDetail::create([
                    'kd_trbmasuk' => $validated['kd_trbmasuk'],
                    'kd_orders' => '',
                    'id_barang' => $validated['id_barang'],
                    'kd_barang' => $validated['kd_barang'],
                    'nmbrg_dtrbmasuk' => $validated['nmbrg_dtrbmasuk'],
                    'qty_dtrbmasuk' => $validated['qty_dtrbmasuk'],
                    'sat_dtrbmasuk' => $validated['sat_dtrbmasuk'],
                    'qty_grosir' => $validated['qty_dtrbmasuk'],
                    'satgrosir_dtrbmasuk' => $validated['sat_dtrbmasuk'],
                    'konversi' => 1,
                    'diskon' => 0,
                    'tipe' => 0,
                    'hrgsat_dtrbmasuk' => $validated['hrgsat_dtrbmasuk'],
                    'hrgjual_dtrbmasuk' => $validated['hrgjual_dtrbmasuk'],
                    'hrgttl_dtrbmasuk' => $ttlHarga,
                    'hnasat_dtrbmasuk' => $barang->hna ?? 0,
                    'no_batch' => $validated['no_batch'],
                    'exp_date' => $expDate,
                ]);

                Product::where('id_barang', $validated['id_barang'])->update([
                    'stok_barang' => DB::raw('stok_barang + ' . (float) $validated['qty_dtrbmasuk']),
                    'sat_barang' => $validated['sat_dtrbmasuk'],
                    'hrgsat_barang' => $validated['hrgsat_dtrbmasuk'],
                    'hrgjual_barang' => $validated['hrgjual_dtrbmasuk'],
                    'hrgjual_barang1' => $validated['hrgjual_dtrbmasuk_resep'],
                    'hrgjual_barang2' => $validated['hrgjual_dtrbmasuk_nakes'],
                ]);

                Batch::create([
                    'tgl_transaksi' => now(),
                    'no_batch' => $validated['no_batch'],
                    'exp_date' => $expDate,
                    'qty' => $validated['qty_dtrbmasuk'],
                    'satuan' => $validated['sat_dtrbmasuk'],
                    'kd_transaksi' => $validated['kd_trbmasuk'],
                    'kd_barang' => $validated['kd_barang'],
                    'status' => 'masuk',
                ]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Edit inline Qty (AJAX), mengikuti simpandetail_qty.php.
     */
    public function detailUpdateQty(Request $request, TrbmasukDetail $detail)
    {
        $validated = $request->validate([
            'qty_dtrbmasuk' => 'required|numeric|gt:0',
        ]);

        DB::transaction(function () use ($detail, $validated) {
            $selisih = $validated['qty_dtrbmasuk'] - $detail->qty_dtrbmasuk;

            $detail->update([
                'qty_dtrbmasuk' => $validated['qty_dtrbmasuk'],
                'hrgttl_dtrbmasuk' => round($validated['qty_dtrbmasuk'] * $detail->hrgsat_dtrbmasuk),
            ]);

            Product::where('id_barang', $detail->id_barang)->update(['stok_barang' => DB::raw('stok_barang + (' . (float) $selisih . ')')]);

            if ($detail->no_batch) {
                Batch::where('kd_transaksi', $detail->kd_trbmasuk)
                    ->where('kd_barang', $detail->kd_barang)
                    ->where('no_batch', $detail->no_batch)
                    ->where('status', 'masuk')
                    ->update(['qty' => $validated['qty_dtrbmasuk']]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hapus baris detail langsung (diarsipkan dulu), mengikuti hapusdetail_tbm.php.
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
                ->where('status', 'masuk')
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
                'jenis' => 'nonpbf',
                'petugas' => $admin->nama_lengkap,
            ]));

            KartuStok::create(['kode_transaksi' => $validated['kd_trbmasuk']]);

            Kdbm::where('id_admin', $admin->id_admin)
                ->where('id_resto', 'pusat')
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->update(['stt_kdbm' => 'OFF']);
        });

        return redirect()->route('inventory.trbmasuk.index')->with('success', 'Transaksi barang masuk berhasil disimpan.');
    }

    /**
     * Simpan header transaksi terima-dari-pesanan (upsert: insert kalau baru,
     * update kalau sudah ada -- transaksi terima bisa disimpan berkali-kali sambil
     * baris detail-nya diedit satu per satu), mengikuti act=input_order_trbmasuk.
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
                    'jenis' => 'nonpbf',
                    'petugas' => $admin->nama_lengkap,
                ]));

                KartuStok::create(['kode_transaksi' => $validated['kd_trbmasuk']]);
            }

            Kdbm::where('id_admin', $admin->id_admin)
                ->where('id_resto', 'pusat')
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->update(['stt_kdbm' => 'OFF']);

            // Tandai pesanan sebagai sudah masuk barangnya. Legacy non-PBF tidak pernah
            // melakukan ini (dicek langsung di aksi_trbmasuk.php-nya) -- kolom orders.masuk
            // di sini disamakan dengan versi PBF yang sudah diperbaiki, supaya kolom ini
            // tetap akurat untuk siapa pun/apa pun lain yang membacanya dari DB bersama.
            // Status "Belum/Telah Diproses" yang ditampilkan modul ini sendiri TIDAK
            // bergantung pada kolom ini -- selalu dihitung langsung dari ordersdetail.masuk.
            SupplierOrder::where('kd_trbmasuk', $validated['kd_orders'])->update(['masuk' => '0']);
        });

        return redirect()->route('inventory.trbmasuk.index')->with('success', 'Transaksi terima barang berhasil disimpan.');
    }

    // ==================== TERIMA DARI PESANAN ====================

    /**
     * Daftar pesanan yang bisa diterima ("Cek Pesanan"), mengikuti case 'orders'.
     */
    public function ordersIndex()
    {
        return view('inventory.trbmasuk.orders', ['judul' => 'Inventory']);
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
                $sudahDiproses = Trbmasuk::where('kd_orders', $row->kd_trbmasuk)->where('jenis', 'nonpbf')->exists();
                $adaPending = SupplierOrderDetail::where('kd_trbmasuk', $row->kd_trbmasuk)->where('masuk', '1')->exists();

                $link = route('inventory.trbmasuk.orders-detail', ['id' => $row->id_trbmasuk]);

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

        return view('inventory.trbmasuk.receive', [
            'judul' => 'Inventory',
            'order' => $order,
            'kdTransaksi' => $kdTransaksi,
            'petugas' => $admin->nama_lengkap,
            'minExpDate' => $setheader->empatbelas ?? 0,
        ]);
    }

    public function receiveDetailIndex(Request $request)
    {
        $kdTrbmasuk = (string) $request->query('kd_trbmasuk', '');
        $kdOrders = (string) $request->query('kd_orders', '');

        $migrated = TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)->where('kd_orders', $kdOrders)->get();
        $pending = SupplierOrderDetail::where('kd_trbmasuk', $kdOrders)->where('masuk', '1')->get();

        return view('inventory.trbmasuk.partials.receive-detail-table', [
            'kdTrbmasuk' => $kdTrbmasuk,
            'kdOrders' => $kdOrders,
            'migrated' => $migrated,
            'pending' => $pending,
            'subtotal' => $this->hitungSubtotalOrder($kdTrbmasuk, $kdOrders),
            'header' => Trbmasuk::where('kd_trbmasuk', $kdTrbmasuk)->first(),
        ]);
    }

    /**
     * Edit satu kolom baris terima-dari-pesanan (AJAX), menggabungkan 7 endpoint
     * simpandetail_{batch,diskon,expdate,hrgbeli,hrgjual,konversi,qtygrosir}_order.php
     * legacy jadi satu. Baris yang belum pernah disentuh masih murni di ordersdetail --
     * disalin/"dimigrasikan" ke trbmasuk_detail saat kolom apa pun pertama kali diedit.
     */
    public function receiveDetailUpdate(Request $request)
    {
        $validated = $request->validate([
            'field' => 'required|in:batch,diskon,expdate,hrgbeli,hrgjual,konversi,qtygrosir',
            'kd_trbmasuk' => 'required|string|max:100',
            'kd_orders' => 'required|string|max:100',
            'kd_barang' => 'required|string|max:50',
            'id_dtrbmasuk' => 'nullable|integer',
            // batch.no_batch (varchar(10)) lebih pendek dari trbmasuk_detail.no_batch --
            // dibatasi juga di sini supaya field 'batch' tidak lolos validasi lalu gagal SQL.
            'value' => 'required|string|max:100',
        ]);

        if ($validated['field'] === 'batch' && strlen($validated['value']) > 10) {
            return response()->json(['status' => 'error', 'message' => 'No. Batch maksimal 10 karakter.'], 422);
        }

        $field = $validated['field'];
        $value = $validated['value'];

        $detail = DB::transaction(function () use ($validated, $field, $value) {
            $detail = TrbmasukDetail::where('kd_barang', $validated['kd_barang'])
                ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
                ->first();

            if (!$detail) {
                $detail = $this->migrateFromOrder($validated, $field, $value);
            } else {
                $this->applyFieldUpdate($detail, $field, $value);
            }

            return $detail->fresh();
        });

        return response()->json([
            'status' => 'ok',
            'id_dtrbmasuk' => $detail->id_dtrbmasuk,
            'qty_grosir' => $detail->qty_grosir,
            'hrgbelidisc_text' => number_format($detail->hrgsat_dtrbmasuk * (1 - $detail->diskon / 100), 0, ',', '.'),
            'total_text' => number_format($detail->hrgttl_dtrbmasuk, 0, ',', '.'),
            'subtotal' => number_format($this->hitungSubtotalOrder($validated['kd_trbmasuk'], $validated['kd_orders']), 0, ',', '.'),
        ]);
    }

    /**
     * Hapus baris terima-dari-pesanan, mengikuti hapusdetail_order.php -- tapi baris
     * yang belum pernah bermigrasi ke trbmasuk_detail cukup di-no-op (lihat catatan
     * bug di komentar kelas ini), tidak menandai ordersdetail sebagai "sudah diterima".
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
        // Baris yang belum pernah bermigrasi (masih murni di ordersdetail): tidak ada
        // apa pun yang perlu dihapus/diubah -- baris cukup hilang dari tampilan saat
        // ini saja (akan muncul lagi di reload berikutnya, karena memang belum diterima).

        return response()->json([
            'status' => 'ok',
            'subtotal' => number_format($this->hitungSubtotalOrder($validated['kd_trbmasuk'], $validated['kd_orders']), 0, ',', '.'),
        ]);
    }

    // ==================== EVALUASI BARANG MASUK ====================

    public function evaluasiIndex()
    {
        return view('inventory.trbmasuk.evaluasi', ['judul' => 'Inventory']);
    }

    public function evaluasiData()
    {
        $query = Trbmasuk::query()
            ->where('id_resto', 'pusat')
            ->where('jenis', 'nonpbf')
            ->where('kd_orders', '!=', '')
            ->select(['id_trbmasuk', 'kd_trbmasuk', 'petugas', 'tgl_trbmasuk', 'nm_supplier', 'ket_trbmasuk', 'sisa_bayar', 'jatuhtempo', 'carabayar']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->editColumn('sisa_bayar', fn ($row) => number_format($row->sisa_bayar, 0, ',', '.'))
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('inventory.trbmasuk.evaluasi.show', $row->id_trbmasuk) . '" class="btn btn-info btn-sm">Tampil</a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Perbandingan qty/harga pesan vs masuk untuk satu transaksi, mengikuti
     * case 'evaluasi_tampil'.
     */
    public function evaluasiShow(Trbmasuk $trbmasuk)
    {
        abort_unless($trbmasuk->kd_orders !== '', 404);

        $rows = DB::table('ordersdetail as od')
            ->leftJoin('trbmasuk_detail as td', function ($join) {
                $join->on('td.kd_orders', '=', 'od.kd_trbmasuk')->on('td.id_barang', '=', 'od.id_barang');
            })
            ->where('od.kd_trbmasuk', $trbmasuk->kd_orders)
            ->groupBy('od.id_barang', 'od.kd_barang', 'od.nmbrg_dtrbmasuk', 'od.sat_dtrbmasuk', 'od.qty_dtrbmasuk', 'od.hrgsat_dtrbmasuk')
            ->orderBy('od.nmbrg_dtrbmasuk')
            ->selectRaw('od.kd_barang, od.id_barang, od.nmbrg_dtrbmasuk, od.sat_dtrbmasuk,
                od.qty_dtrbmasuk as qty_pesan, od.hrgsat_dtrbmasuk as hrgsat_pesan,
                COALESCE(SUM(td.qty_dtrbmasuk), 0) as qty_masuk,
                COALESCE(SUM(td.qty_dtrbmasuk * td.hrgsat_dtrbmasuk), 0) as totalharga_masuk')
            ->get()
            ->map(function ($row) {
                $qtyMasuk = (float) $row->qty_masuk;
                $totalMasuk = (float) $row->totalharga_masuk;
                $row->hrgsat_masuk = $qtyMasuk > 0 ? round($totalMasuk / $qtyMasuk) : 0;

                return $row;
            });

        return view('inventory.trbmasuk.evaluasi-show', [
            'judul' => 'Inventory',
            'trbmasuk' => $trbmasuk,
            'rows' => $rows,
            'total' => $rows->sum('totalharga_masuk'),
        ]);
    }

    // ==================== CARI NO. BATCH ====================

    public function batchSearchForm()
    {
        return view('inventory.trbmasuk.batch-search', ['judul' => 'Inventory']);
    }

    public function batchSearchResult(Request $request)
    {
        $noBatch = (string) $request->input('no_batch', '');

        $rows = DB::table('trbmasuk_detail as a')
            ->join('trbmasuk as b', 'a.kd_trbmasuk', '=', 'b.kd_trbmasuk')
            ->where('a.no_batch', $noBatch)
            ->orderByDesc('b.tgl_trbmasuk')
            ->select(['a.*', 'b.nm_supplier', 'b.tgl_trbmasuk'])
            ->get();

        return view('inventory.trbmasuk.batch-search-result', [
            'judul' => 'Inventory',
            'noBatch' => $noBatch,
            'rows' => $rows,
            'ringkasan' => $rows->first(),
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
            'hrgsat_barang' => $barang->hrgsat_barang,
            'hrgjual_barang' => $barang->hrgjual_barang,
            'hrgjual_barang1' => $barang->hrgjual_barang1,
            'hrgjual_barang2' => $barang->hrgjual_barang2,
            'kd_barang' => (string) $barang->kd_barang,
        ]);
    }

    /**
     * Item picker global (tidak dibatasi supplier), mengikuti barang-serverside.php
     * di folder ini (beda dari punya modul Pesan Barang yang dibatasi per-supplier).
     */
    public function itemPicker()
    {
        $query = Product::query()->select(['id_barang', 'kd_barang', 'nm_barang', 'stok_barang', 'sat_barang', 'hrgsat_barang', 'hrgjual_barang']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('kd_barang', fn ($row) => (string) $row->kd_barang)
            ->editColumn('hrgsat_barang', fn ($row) => number_format($row->hrgsat_barang, 0, ',', '.'))
            ->editColumn('hrgjual_barang', fn ($row) => number_format($row->hrgjual_barang, 0, ',', '.'))
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-barang' data-nm_barang='" . e($row->nm_barang) . "'><i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    // ==================== HELPER PRIVAT ====================

    private function validateHeader(Request $request): array
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
            'carabayar' => 'required|in:LUNAS,KREDIT',
            'jatuhtempo' => 'nullable|string|max:20',
        ]);

        $validated['tlp_supplier'] = $validated['tlp_supplier'] ?? '';
        $validated['alamat_trbmasuk'] = $validated['alamat_trbmasuk'] ?? '';
        $validated['ket_trbmasuk'] = $validated['ket_trbmasuk'] ?? '';
        $validated['jatuhtempo'] = $validated['jatuhtempo'] ?? '';
        $validated['kd_orders'] = $validated['kd_orders'] ?? '';
        $validated['tgl_lunas'] = $validated['carabayar'] === 'LUNAS' ? $validated['tgl_trbmasuk'] : '1970-01-01';
        $validated['petugas_lunas'] = $validated['carabayar'] === 'LUNAS' ? ($validated['petugas_lunas'] ?? '') : '';

        return $validated;
    }

    /**
     * Kode transaksi terbuka untuk input langsung, mengikuti pengecekan kdbm di case
     * 'tambah': draft dipakai ulang HANYA kalau belum berisi baris pesanan apa pun
     * (supaya input manual tidak tercampur draft yang sedang dipakai menerima pesanan).
     */
    private function resolveDirectEntryKode(int $idAdmin): string
    {
        $existing = Kdbm::where('id_admin', $idAdmin)
            ->where('id_resto', 'pusat')
            ->where('stt_kdbm', 'ON')
            ->whereNotIn('kd_trbmasuk', function ($q) {
                $q->select('kd_trbmasuk')->from('trbmasuk_detail')->where('kd_orders', '!=', '');
            })
            ->orderByDesc('id_kdbm')
            ->first();

        if ($existing) {
            return $existing->kd_trbmasuk;
        }

        return $this->createKdbm($idAdmin, 'BMP-');
    }

    /**
     * Kode transaksi untuk menerima pesanan tertentu, mengikuti case 'orders_detail':
     * kalau transaksi terima untuk pesanan ini sudah pernah dimulai, pakai kode itu
     * lagi; kalau belum, cari/berikan draft 'ON' yang kosong atau sudah dikhususkan
     * untuk pesanan yang sama (supaya item pesanan lain yang masih berjalan tidak
     * ikut tercampur).
     */
    private function resolveOrderReceiveKode(int $idAdmin, string $kdOrders): string
    {
        $existing = Trbmasuk::where('kd_orders', $kdOrders)->first();
        if ($existing) {
            return $existing->kd_trbmasuk;
        }

        $existingKdbm = Kdbm::where('id_admin', $idAdmin)
            ->where('id_resto', 'pusat')
            ->where('stt_kdbm', 'ON')
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

        return $this->createKdbm($idAdmin, 'BMP-');
    }

    private function createKdbm(int $idAdmin, string $prefix): string
    {
        $kode = $prefix . now()->format('dmyhis');
        if (Kdbm::where('kd_trbmasuk', $kode)->exists()) {
            $kode = $prefix . now()->addSecond()->format('dmyhis');
        }

        Kdbm::create(['kd_trbmasuk' => $kode, 'id_resto' => 'pusat', 'id_admin' => $idAdmin]);

        return $kode;
    }

    /**
     * Migrasi baris dari ordersdetail (masih murni "pesanan") ke trbmasuk_detail
     * (sudah "diterima"), sekaligus menerapkan nilai kolom yang baru pertama kali
     * diedit. Mengikuti pola bersama di semua simpandetail_*_order.php.
     */
    private function migrateFromOrder(array $validated, string $field, string $value): TrbmasukDetail
    {
        $odt = SupplierOrderDetail::where('kd_barang', $validated['kd_barang'])
            ->where('kd_trbmasuk', $validated['kd_orders'])
            ->when(!empty($validated['id_dtrbmasuk']), fn ($q) => $q->where('id_dtrbmasuk', $validated['id_dtrbmasuk']))
            ->first();

        if (!$odt || $odt->masuk !== '1') {
            abort(422, 'Item sudah diterima pada transaksi lain. Silakan muat ulang halaman.');
        }

        $konversi = $field === 'konversi' ? (float) $value : $odt->konversi;
        $qtyGrosir = $field === 'qtygrosir' ? (float) $value : $odt->qtygrosir_dtrbmasuk;
        $hrgsat = $field === 'hrgbeli' ? (float) str_replace('.', '', $value) : $odt->hrgsat_dtrbmasuk;
        $hrgjual = $field === 'hrgjual' ? (float) str_replace('.', '', $value) : $odt->hrgjual_dtrbmasuk;
        $diskon = $field === 'diskon' ? (float) $value : 0;
        $noBatch = $field === 'batch' ? $value : '';
        $expDate = $field === 'expdate' ? $value : now()->addDays(720)->toDateString();
        $qtyDtrbmasuk = $konversi * $qtyGrosir;
        $hrgttl = round($hrgsat * (1 - $diskon / 100) * $qtyDtrbmasuk);

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
            'hnasat_dtrbmasuk' => round($hrgsat / 1.11),
            'diskon' => $diskon,
            'tipe' => 0,
            'hrgsat_dtrbmasuk' => $hrgsat,
            'hrgjual_dtrbmasuk' => $hrgjual,
            'hrgttl_dtrbmasuk' => $hrgttl,
            'no_batch' => $noBatch,
            'exp_date' => $expDate,
        ]);

        Product::where('id_barang', $odt->id_barang)->increment('stok_barang', $qtyDtrbmasuk);
        Product::where('kd_barang', $odt->kd_barang)->update([
            'hrgsat_barang' => $konversi > 0 ? $hrgsat / $konversi : $hrgsat,
            'hrgsat_grosir' => $hrgsat,
        ]);
        if ($field === 'hrgjual') {
            Product::where('kd_barang', $odt->kd_barang)->update(['hrgjual_barang' => $hrgjual]);
        }

        $odt->update(['masuk' => '0']);

        if ($noBatch !== '') {
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

        return $detail;
    }

    /**
     * Terapkan perubahan satu kolom pada baris yang sudah bermigrasi ke
     * trbmasuk_detail. Mengikuti cabang "found" di semua simpandetail_*_order.php.
     */
    private function applyFieldUpdate(TrbmasukDetail $detail, string $field, string $value): void
    {
        $qtyLama = $detail->qty_dtrbmasuk;

        switch ($field) {
            case 'batch':
                Batch::where('kd_transaksi', $detail->kd_trbmasuk)->where('kd_barang', $detail->kd_barang)
                    ->where('no_batch', $detail->no_batch)->where('status', 'masuk')->delete();
                $detail->no_batch = $value;
                if ($value !== '') {
                    Batch::create([
                        'tgl_transaksi' => now(), 'no_batch' => $value, 'exp_date' => $detail->exp_date,
                        'qty' => $detail->qty_grosir, 'satuan' => $detail->satgrosir_dtrbmasuk,
                        'kd_transaksi' => $detail->kd_trbmasuk, 'kd_barang' => $detail->kd_barang, 'status' => 'masuk',
                    ]);
                }
                break;

            case 'diskon':
                $detail->diskon = (float) $value;
                break;

            case 'expdate':
                $detail->exp_date = $value;
                if ($detail->no_batch) {
                    Batch::where('kd_transaksi', $detail->kd_trbmasuk)->where('kd_barang', $detail->kd_barang)
                        ->where('no_batch', $detail->no_batch)->where('status', 'masuk')
                        ->update(['exp_date' => $value]);
                }
                break;

            case 'hrgbeli':
                $baru = (float) str_replace('.', '', $value);
                $detail->hrgsat_dtrbmasuk = $baru;
                $detail->hnasat_dtrbmasuk = round($baru / 1.11);
                Product::where('kd_barang', $detail->kd_barang)->update([
                    'hrgsat_barang' => $detail->konversi > 0 ? $baru / $detail->konversi : $baru,
                    'hrgsat_grosir' => $baru,
                ]);
                break;

            case 'hrgjual':
                $baru = (float) str_replace('.', '', $value);
                $detail->hrgjual_dtrbmasuk = $baru;
                Product::where('kd_barang', $detail->kd_barang)->update(['hrgjual_barang' => $baru]);
                break;

            case 'konversi':
                $detail->konversi = (float) $value;
                $detail->qty_dtrbmasuk = $detail->konversi * $detail->qty_grosir;
                Product::where('kd_barang', $detail->kd_barang)->update([
                    'hrgsat_barang' => $detail->konversi > 0 ? $detail->hrgsat_dtrbmasuk / $detail->konversi : $detail->hrgsat_dtrbmasuk,
                ]);
                break;

            case 'qtygrosir':
                $detail->qty_grosir = (float) $value;
                $detail->qty_dtrbmasuk = $detail->konversi * $detail->qty_grosir;
                if ($detail->no_batch) {
                    Batch::where('kd_transaksi', $detail->kd_trbmasuk)->where('kd_barang', $detail->kd_barang)
                        ->where('no_batch', $detail->no_batch)->where('status', 'masuk')
                        ->update(['qty' => $detail->qty_grosir]);
                }
                break;
        }

        $detail->hrgttl_dtrbmasuk = round($detail->hrgsat_dtrbmasuk * (1 - $detail->diskon / 100) * $detail->qty_dtrbmasuk);
        $detail->save();

        $selisihQty = $detail->qty_dtrbmasuk - $qtyLama;
        if ($selisihQty != 0) {
            Product::where('id_barang', $detail->id_barang)->update(['stok_barang' => DB::raw('stok_barang + (' . (float) $selisihQty . ')')]);
        }
    }

    /**
     * Subtotal gabungan baris yang sudah bermigrasi (trbmasuk_detail) + baris yang
     * masih pending (ordersdetail), mengikuti helper_subtotal_order.php.
     */
    private function hitungSubtotalOrder(string $kdTrbmasuk, string $kdOrders): float
    {
        $dariTrbmasuk = TrbmasukDetail::where('kd_trbmasuk', $kdTrbmasuk)
            ->selectRaw('COALESCE(SUM(ROUND(hrgsat_dtrbmasuk * (1 - diskon / 100) * qty_grosir)), 0) as total')
            ->value('total');

        $dariOrders = SupplierOrderDetail::where('kd_trbmasuk', $kdOrders)->where('masuk', '1')
            ->selectRaw('COALESCE(SUM(ROUND(hrgsat_dtrbmasuk * qtygrosir_dtrbmasuk)), 0) as total')
            ->value('total');

        return (float) $dariTrbmasuk + (float) $dariOrders;
    }
}
