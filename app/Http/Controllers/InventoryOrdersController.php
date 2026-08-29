<?php

namespace App\Http\Controllers;

use App\Models\Kdbm;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\Setheader;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderDetail;
use App\Models\SupplierOrderDetailHist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryOrdersController extends Controller
{
    /**
     * Modul "Pesan Barang" (module=orders), mengikuti
     * public/apotekberlian/masuk/modul/mod_orders/{orders,aksi_orders,orders_serverside,
     * barang-serverside,simpandetail_tbm,hapusdetail_tbm,update_qtygrosir_tbm,tbl_detail,
     * autonamabarang_stok,autonamabarang_enter,autobarang}.php.
     *
     * Header (tabel `orders`, id_resto='pesan') vs detail (tabel `ordersdetail`, terhubung
     * lewat kd_trbmasuk -- BUKAN foreign key ke id_trbmasuk) sengaja dipisah: sama seperti
     * legacy, baris detail bisa ditambah dulu (via kode transaksi "terbuka" di tabel `kdbm`)
     * sebelum baris header sendiri disimpan -- baru saat "SIMPAN TRANSAKSI" diklik, header-nya
     * benar-benar di-INSERT/UPDATE. Class model dinamai SupplierOrder* (bukan Order*) karena
     * 'Order' sudah dipakai storefront (order_online) -- lihat memory catatan sesi ini.
     */
    public function index()
    {
        return view('inventory.orders.index', [
            'judul' => 'Inventory',
        ]);
    }

    public function data()
    {
        $query = SupplierOrder::query()->where('id_resto', 'pesan')->select([
            'id_trbmasuk', 'petugas', 'kd_trbmasuk', 'tgl_trbmasuk', 'nm_supplier',
            'ket_trbmasuk', 'ttl_trbmasuk', 'dp_bayar', 'sisa_bayar',
        ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('tgl_trbmasuk', fn ($row) => $row->tgl_trbmasuk?->format('Y-m-d'))
            ->addColumn('belum_diproses', function ($row) {
                return SupplierOrderDetail::where('kd_trbmasuk', $row->kd_trbmasuk)->where('masuk', '1')->count();
            })
            ->addColumn('telah_diproses', function ($row) {
                return SupplierOrderDetail::where('kd_trbmasuk', $row->kd_trbmasuk)->where('masuk', '0')->count();
            })
            ->addColumn('aksi', function ($row) {
                $id = $row->id_trbmasuk;
                $kode = urlencode($row->kd_trbmasuk);

                return '<div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button>
                    <div class="dropdown-menu p-2 shadow" style="min-width:170px;">
                        <a href="' . route('inventory.orders.edit', $id) . '" class="btn btn-warning btn-sm w-100 mb-1">Edit</a>
                        <a href="' . route('inventory.orders.print.reguler', $id) . '" target="_blank" class="btn btn-info btn-sm w-100 mb-1">SP Reguler</a>
                        <a href="' . route('inventory.orders.print.prekursor', $id) . '" target="_blank" class="btn btn-success btn-sm w-100 mb-1">SP Prekursor</a>
                        <a href="' . route('inventory.orders.print.oot', $id) . '" target="_blank" class="btn btn-primary btn-sm w-100 mb-1">SP OOT</a>
                        <a href="' . route('inventory.orders.print.alkes', $id) . '" target="_blank" class="btn btn-secondary btn-sm w-100 mb-1">SP Alkes</a>
                        <form action="' . route('inventory.orders.destroy', $id) . '" method="POST" id="delete-order-' . $id . '">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="button" onclick="confirmDelete(\'delete-order-' . $id . '\', \'pesanan ' . e($row->kd_trbmasuk) . '\')" class="btn btn-danger btn-sm w-100">Hapus</button>
                        </form>
                    </div>
                </div>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        return view('inventory.orders.form', array_merge($this->sharedFormData(), [
            'order' => null,
            'kdTransaksi' => $this->resolveOpenKode($admin->id_admin),
            'petugas' => $admin->nama_lengkap,
        ]));
    }

    public function edit(SupplierOrder $order)
    {
        return view('inventory.orders.form', array_merge($this->sharedFormData(), [
            'order' => $order,
            'kdTransaksi' => $order->kd_trbmasuk,
            'petugas' => $order->petugas,
        ]));
    }

    private function sharedFormData(): array
    {
        return [
            'judul' => 'Inventory',
            'satuanList' => Satuan::orderBy('nm_satuan')->get(),
            'supplierList' => Supplier::orderBy('nm_supplier')->get(['id_supplier', 'nm_supplier', 'tlp_supplier', 'alamat_supplier']),
        ];
    }

    /**
     * Finalisasi transaksi baru, mengikuti act=input_trbmasuk.
     */
    public function store(Request $request)
    {
        $validated = $this->validateHeader($request);
        $admin = Auth::guard('admin')->user();

        SupplierOrder::create(array_merge($validated, [
            'id_resto' => 'pesan',
            'petugas' => $admin->nama_lengkap,
        ]));

        Kdbm::where('id_admin', $admin->id_admin)
            ->where('id_resto', 'pesan')
            ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
            ->update(['stt_kdbm' => 'OFF']);

        return redirect()->route('inventory.orders.index')->with('success', 'Transaksi pesanan berhasil disimpan.');
    }

    /**
     * Finalisasi perubahan transaksi, mengikuti act=ubah_trbmasuk.
     */
    public function update(Request $request, SupplierOrder $order)
    {
        $validated = $this->validateHeader($request);
        $admin = Auth::guard('admin')->user();

        $order->update($validated);

        Kdbm::where('id_admin', $admin->id_admin)
            ->where('id_resto', 'pesan')
            ->where('kd_trbmasuk', $order->kd_trbmasuk)
            ->update(['stt_kdbm' => 'OFF']);

        return redirect()->route('inventory.orders.index')->with('success', 'Transaksi pesanan berhasil diperbarui.');
    }

    /**
     * Hapus pesanan (header + semua detail, diarsipkan ke ordersdetail_hist dulu),
     * mengikuti act=hapus.
     */
    public function destroy(SupplierOrder $order)
    {
        DB::transaction(function () use ($order) {
            $this->archiveDetail($order->kd_trbmasuk);
            SupplierOrderDetail::where('kd_trbmasuk', $order->kd_trbmasuk)->delete();
            $order->delete();
        });

        return redirect()->route('inventory.orders.index')->with('success', 'Pesanan berhasil dihapus.');
    }

    /**
     * Tabel detail + subtotal/diskon (AJAX partial), mengikuti tbl_detail.php.
     */
    public function detailIndex(Request $request)
    {
        $kdTrbmasuk = (string) $request->query('kd_trbmasuk', '');

        $detail = SupplierOrderDetail::where('kd_trbmasuk', $kdTrbmasuk)->orderBy('nmbrg_dtrbmasuk')->get();
        $order = SupplierOrder::where('kd_trbmasuk', $kdTrbmasuk)->first();
        $subtotal = $detail->sum('hrgttl_dtrbmasuk');

        return view('inventory.orders.partials.detail-table', [
            'kdTrbmasuk' => $kdTrbmasuk,
            'detail' => $detail,
            'subtotal' => $subtotal,
            'dpBayar' => $order->dp_bayar ?? 0,
        ]);
    }

    /**
     * Tambah/gabung baris detail, mengikuti simpandetail_tbm.php.
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
            'satgrosir_dtrbmasuk' => 'required|string|max:30',
            'qtygrosir_dtrbmasuk' => 'required|numeric|min:0',
            'konversi' => 'required|numeric|min:0.0001',
        ]);

        $existing = SupplierOrderDetail::where('kd_barang', $validated['kd_barang'])
            ->where('kd_trbmasuk', $validated['kd_trbmasuk'])
            ->first();

        if ($existing) {
            $ttlQty = $existing->qty_dtrbmasuk + $validated['qty_dtrbmasuk'];

            $existing->update([
                'qty_dtrbmasuk' => $ttlQty,
                'hrgsat_dtrbmasuk' => $validated['hrgsat_dtrbmasuk'],
                'hrgttl_dtrbmasuk' => $ttlQty * $validated['hrgsat_dtrbmasuk'],
                'satgrosir_dtrbmasuk' => $validated['satgrosir_dtrbmasuk'],
                'qtygrosir_dtrbmasuk' => $existing->qtygrosir_dtrbmasuk + $validated['qtygrosir_dtrbmasuk'],
                // masuk dipaksa '1' (belum diterima) karena qty baru saja diubah -- lihat
                // catatan di simpandetail_tbm.php.
                'masuk' => '1',
            ]);
        } else {
            $barang = Product::find($validated['id_barang']);

            SupplierOrderDetail::create([
                'kd_trbmasuk' => $validated['kd_trbmasuk'],
                'id_barang' => $validated['id_barang'],
                'kd_barang' => $validated['kd_barang'],
                'nmbrg_dtrbmasuk' => $validated['nmbrg_dtrbmasuk'],
                'qty_dtrbmasuk' => $validated['qty_dtrbmasuk'],
                'sat_dtrbmasuk' => $validated['sat_dtrbmasuk'],
                'konversi' => $validated['konversi'],
                'hrgsat_dtrbmasuk' => $validated['hrgsat_dtrbmasuk'],
                'hrgttl_dtrbmasuk' => $validated['qty_dtrbmasuk'] * $validated['hrgsat_dtrbmasuk'],
                'hrgjual_dtrbmasuk' => $barang->hrgjual_barang ?? 0,
                'satgrosir_dtrbmasuk' => $validated['satgrosir_dtrbmasuk'],
                'qtygrosir_dtrbmasuk' => $validated['qtygrosir_dtrbmasuk'],
                'hnasat_dtrbmasuk' => $barang->hna ?? 0,
                'diskon' => 0,
                'no_batch' => '',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hapus satu baris detail (diarsipkan dulu ke ordersdetail_hist), mengikuti
     * hapusdetail_tbm.php.
     */
    public function detailDestroy(SupplierOrderDetail $detail)
    {
        $kdTrbmasuk = $detail->kd_trbmasuk;

        DB::transaction(function () use ($detail) {
            SupplierOrderDetailHist::create($detail->only([
                'kd_trbmasuk', 'id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk',
                'sat_dtrbmasuk', 'hnasat_dtrbmasuk', 'diskon', 'konversi', 'hrgsat_dtrbmasuk',
                'hrgjual_dtrbmasuk', 'hrgttl_dtrbmasuk', 'qtygrosir_dtrbmasuk', 'satgrosir_dtrbmasuk',
                'no_batch', 'exp_date', 'masuk',
            ]));
            $detail->delete();
        });

        $subtotal = SupplierOrderDetail::where('kd_trbmasuk', $kdTrbmasuk)->sum('hrgttl_dtrbmasuk');

        return response()->json(['status' => 'ok', 'subtotal' => number_format($subtotal, 0, ',', '.')]);
    }

    /**
     * Update inline Qty Grosir (AJAX), mengikuti update_qtygrosir_tbm.php.
     */
    public function detailUpdateQty(Request $request, SupplierOrderDetail $detail)
    {
        $validated = $request->validate([
            'qtygrosir_dtrbmasuk' => 'required|numeric|gt:0',
        ]);

        $qtyDtrbmasuk = $detail->konversi * $validated['qtygrosir_dtrbmasuk'];
        $hrgttlDtrbmasuk = $detail->hrgsat_dtrbmasuk * $qtyDtrbmasuk;

        $detail->update([
            'qtygrosir_dtrbmasuk' => $validated['qtygrosir_dtrbmasuk'],
            'qty_dtrbmasuk' => $qtyDtrbmasuk,
            'hrgttl_dtrbmasuk' => $hrgttlDtrbmasuk,
        ]);

        $subtotal = SupplierOrderDetail::where('kd_trbmasuk', $detail->kd_trbmasuk)->sum('hrgttl_dtrbmasuk');
        $qtyText = rtrim(rtrim(sprintf('%.4f', $qtyDtrbmasuk), '0'), '.');
        if ($qtyText === '') {
            $qtyText = '0';
        }

        return response()->json([
            'status' => 'ok',
            'qty_dtrbmasuk' => $qtyText,
            'hrgttl_dtrbmasuk' => number_format($hrgttlDtrbmasuk, 0, ',', '.'),
            'subtotal' => number_format($subtotal, 0, ',', '.'),
        ]);
    }

    /**
     * Autocomplete nama barang (typeahead), mengikuti autonamabarang_stok.php.
     */
    public function itemSearch(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        if ($query === '') {
            return response()->json([]);
        }

        $items = Product::where('nm_barang', 'like', "%{$query}%")
            ->orderBy('nm_barang')
            ->limit(20)
            ->get(['nm_barang', 'stok_barang', 'sat_barang']);

        return response()->json($items);
    }

    /**
     * Resolusi detail barang lengkap by nama ATAU kode, mengikuti
     * autonamabarang_enter.php / autobarang.php.
     */
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
            'hrgjual_barang' => $barang->hrgjual_barang,
            'hrgsat_barang' => $barang->hrgsat_barang,
            'kd_barang' => (string) $barang->kd_barang,
        ]);
    }

    /**
     * Item picker per supplier dengan metrik traffic T30/Q30/SF (AJAX DataTables),
     * mengikuti barang-serverside.php.
     */
    public function supplierItems(Request $request)
    {
        $idSupplier = (int) $request->query('id_supplier', 0);
        $akhir = now()->toDateString();
        $awal = now()->subDays(30)->toDateString();

        $t30Sub = DB::table('trkasir_detail')
            ->selectRaw('COUNT(trkasir_detail.id_dtrkasir)')
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$awal, $akhir])
            ->whereColumn('trkasir_detail.id_barang', 'barang_supplier.id_barang');

        $q30Sub = DB::table('trkasir_detail')
            ->selectRaw('COALESCE(SUM(trkasir_detail.qty_dtrkasir), 0)')
            ->join('trkasir', 'trkasir.kd_trkasir', '=', 'trkasir_detail.kd_trkasir')
            ->whereBetween('trkasir.tgl_trkasir', [$awal, $akhir])
            ->whereColumn('trkasir_detail.id_barang', 'barang_supplier.id_barang');

        $query = DB::table('barang_supplier')
            ->join('barang', 'barang_supplier.id_barang', '=', 'barang.id_barang')
            ->where('barang_supplier.id_supplier', $idSupplier)
            ->select([
                'barang.id_barang', 'barang.kd_barang', 'barang.nm_barang', 'barang.stok_barang',
                'barang.sat_barang', 'barang.hrgsat_barang',
            ])
            ->selectSub($t30Sub, 't30')
            ->selectSub($q30Sub, 'q30');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('kd_barang', fn ($row) => (string) $row->kd_barang)
            ->addColumn('satuan', fn ($row) => $row->sat_barang)
            ->editColumn('q30', fn ($row) => max(0, (int) $row->q30))
            ->addColumn('sf', fn ($row) => max(0, (int) $row->q30) - $row->stok_barang)
            ->addColumn('harga_beli', fn ($row) => $row->hrgsat_barang)
            ->addColumn('pilih', function ($row) {
                return "<button type='button' class='btn btn-xs btn-info btn-pilih-barang'
                    data-nm_barang='" . e($row->nm_barang) . "'>
                    <i class='fa fa-check'></i></button>";
            })
            ->rawColumns(['pilih'])
            ->make(true);
    }

    public function printReguler(SupplierOrder $order)
    {
        return $this->renderPrint($order, 'simple', 'SURAT PESANAN OBAT', 'reguler');
    }

    public function printAlkes(SupplierOrder $order)
    {
        return $this->renderPrint($order, 'simple', 'SURAT PESANAN ALAT KESEHATAN', 'alkes');
    }

    public function printPrekursor(SupplierOrder $order)
    {
        return $this->renderPrint($order, 'formal', 'SURAT PESANAN OBAT MENGANDUNG PREKURSOR FARMASI', 'prekursor');
    }

    public function printOot(SupplierOrder $order)
    {
        return $this->renderPrint($order, 'formal', 'SURAT PESANAN OBAT OBAT TERTENTU (OOT)', 'oot');
    }

    private function renderPrint(SupplierOrder $order, string $layout, string $title, string $jenis)
    {
        $order->load('detail.barang');
        $supplier = Supplier::find($order->id_supplier);

        foreach ($order->detail as $row) {
            $qty = $row->qtygrosir_dtrbmasuk > 0 ? $row->qtygrosir_dtrbmasuk : $row->qty_dtrbmasuk;
            $row->qty_tampil = $qty;
            $row->satuan_tampil = $row->satgrosir_dtrbmasuk ?: $row->sat_dtrbmasuk;
            $row->terbilang = $this->terbilang((int) $qty);
        }

        return view('inventory.orders.print', [
            'order' => $order,
            'supplier' => $supplier,
            'setheader' => Setheader::first(),
            'layout' => $layout,
            'title' => $title,
            'jenis' => $jenis,
            'noSp' => in_array($jenis, ['reguler', 'alkes'], true)
                ? $order->kd_trbmasuk
                : strtoupper($jenis) . '-' . substr($order->kd_trbmasuk, 4, 12),
        ]);
    }

    private function validateHeader(Request $request): array
    {
        $validated = $request->validate([
            'kd_trbmasuk' => 'required|string|max:100',
            'tgl_trbmasuk' => 'required|date',
            'id_supplier' => 'required|integer|exists:supplier,id_supplier',
            'nm_supplier' => 'required|string|max:50',
            'tlp_supplier' => 'nullable|string|max:50',
            'alamat_trbmasuk' => 'nullable|string',
            'ket_trbmasuk' => 'required|string|max:20',
            'ttl_trbmasuk' => 'required|numeric|min:0',
            'dp_bayar' => 'required|numeric|min:0',
            'sisa_bayar' => 'required|numeric|min:0',
            'tandatangan' => 'required|in:TIDAK,YA',
        ]);

        // 'tlp_supplier' dan 'alamat_trbmasuk' NOT NULL di database, tapi middleware
        // Laravel mengubah input string kosong menjadi null sebelum validasi.
        $validated['tlp_supplier'] = $validated['tlp_supplier'] ?? '';
        $validated['alamat_trbmasuk'] = $validated['alamat_trbmasuk'] ?? '';

        return $validated;
    }

    /**
     * Nomor kode transaksi terbuka milik admin ini, atau bikin baru, mengikuti
     * pengecekan tabel `kdbm` di case 'tambah'.
     */
    private function resolveOpenKode(int $idAdmin): string
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

    /**
     * Angka jadi teks Indonesia, mengikuti configurasi/fungsi_rupiah.php (teks/terbilang).
     */
    private function terbilang(int $nilai): string
    {
        return trim($nilai < 0 ? 'minus ' . $this->teks(abs($nilai)) : $this->teks($nilai));
    }

    private function teks(int $nilai): string
    {
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nilai < 12) {
            return ' ' . $huruf[$nilai];
        }
        if ($nilai < 20) {
            return $this->teks($nilai - 10) . ' Belas';
        }
        if ($nilai < 100) {
            return $this->teks(intdiv($nilai, 10)) . ' Puluh' . $this->teks($nilai % 10);
        }
        if ($nilai < 200) {
            return ' Seratus' . $this->teks($nilai - 100);
        }
        if ($nilai < 1000) {
            return $this->teks(intdiv($nilai, 100)) . ' Ratus' . $this->teks($nilai % 100);
        }
        if ($nilai < 2000) {
            return ' Seribu' . $this->teks($nilai - 1000);
        }
        if ($nilai < 1000000) {
            return $this->teks(intdiv($nilai, 1000)) . ' Ribu' . $this->teks($nilai % 1000);
        }
        if ($nilai < 1000000000) {
            return $this->teks(intdiv($nilai, 1000000)) . ' Juta' . $this->teks($nilai % 1000000);
        }

        return $this->teks(intdiv($nilai, 1000000000)) . ' Milyar' . $this->teks($nilai % 1000000000);
    }

    private function archiveDetail(string $kdTrbmasuk): void
    {
        $rows = SupplierOrderDetail::where('kd_trbmasuk', $kdTrbmasuk)->get();

        foreach ($rows as $row) {
            SupplierOrderDetailHist::create($row->only([
                'kd_trbmasuk', 'id_barang', 'kd_barang', 'nmbrg_dtrbmasuk', 'qty_dtrbmasuk',
                'sat_dtrbmasuk', 'hnasat_dtrbmasuk', 'diskon', 'konversi', 'hrgsat_dtrbmasuk',
                'hrgjual_dtrbmasuk', 'hrgttl_dtrbmasuk', 'qtygrosir_dtrbmasuk', 'satgrosir_dtrbmasuk',
                'no_batch', 'exp_date', 'masuk',
            ]));
        }
    }
}
