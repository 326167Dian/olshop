<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use carbon\Carbon;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('backend.v_pesanan.proses', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
        ]);
    }

    public function addToCart(Request $request, $id)
    {
        $customer = User::where('id', Auth::id())->first();
        $produk = Product::findOrFail($id);

        // Hitung harga setelah diskon (jika ada diskon)
        $hargaFinal = $produk->hrgjual_barang2;
        if ($produk->diskon > 0) {
            $hargaFinal -= ($produk->hrgjual_barang2 * $produk->diskon / 100);
        }

        // Cek apakah stok mencukupi
        if ($produk->stok_barang <= 0) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi');
        }

        // Ambil atau buat order
        $order = Order::firstOrCreate(
            ['user_id' => $customer->id, 'status' => 'pending'],
        );

        // Ambil atau buat item order
        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id_barang],
            ['quantity' => 1, 'harga' => $hargaFinal]
        );

        if (!$orderItem->wasRecentlyCreated) {
            if ($produk->stok_barang < 1) {
                return redirect()->back()->with('error', 'Stok produk tidak mencukupi');
            }

            $orderItem->quantity++;
            $orderItem->save();
        }

        // Tambahkan harga final ke total
        $order->total_harga += $hargaFinal;
        $order->save();

        // Cek redirect
        if ($request->input('redirect') == '0') {
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang');
        } else {
            return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
        }
    }




    public function viewCart()
    {
        $jarak = 12;
        $customer = User::where('id', Auth::id())->first();
        $order = Order::where('user_id', $customer->id)->where('status', ['pending', 'paid'])->first();
        if ($order) {
            $order->load('orderItems.produk');
        }
        return view('frontend.v_order.cart', compact('order', 'jarak'));
    }



    public function removeFromCart(Request $request, $id)
    {
        $customer = User::where('id', Auth::id())->first();
        $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();
        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('produk_id', $id)->first();
            if ($orderItem) {
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->delete();

                if ($order->total_harga <= 0) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }
        }
        return redirect()->route('order.cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function updateCart(Request $request, $id)
    {
        $customer = User::where('id', Auth::id())->first();

        $order = Order::where('user_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return redirect()->route('order.cart')->with('error', 'Order tidak ditemukan');
        }

        $orderItem = $order->orderItems()->where('id', $id)->first();

        if (!$orderItem) {
            return redirect()->route('order.cart')->with('error', 'Item tidak ditemukan di keranjang');
        }

        $orderItem->load('produk');

        $stok = $orderItem->produk->stok_barang ?? 0;

        $quantity = (int) $request->input('quantity');

        // Cek manual apakah quantity melebihi stok
        if ($quantity > $stok) {
            return redirect()->route('order.cart')->with('error', 'Jumlah produk melebihi stok yang tersedia');
        }

        // Validasi input quantity min 1
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Update total harga
        $order->total_harga -= $orderItem->harga * $orderItem->quantity;

        $orderItem->quantity = $quantity;
        $orderItem->save();

        $order->total_harga += $orderItem->harga * $orderItem->quantity;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Keranjang berhasil diperbarui');
    }


    public function selectShipping(Request $request)
    {

        $customer = User::where('id', Auth::id())->first();
        $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();
        if (!$order || $order->orderItems->count() == 0) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }
        $jenisLayanan = $order->total_harga >= 50000 ? 'Instan' : 'Reguler';
        return view('frontend.v_order.shipping', compact('order', 'customer', 'jenisLayanan'));
    }

    public function selectPayment()
    {
        $customer = Auth::user();
        $order = Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();


        $origin = session('origin');        // Kode kota asal
        $originName = session('originName'); // Nama kota asal

        if (!$order) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }

        // Muat relasi orderItems dan produk terkait
        $order->load('orderItems.produk');

        // Hitung total harga produk
        $totalHarga = 0;
        foreach ($order->orderItems as $item) {
            $totalHarga += $item->harga * $item->quantity;
        }

        // Tambahkan biaya ongkir ke total harga
        $grossAmount = $totalHarga + $order->biaya_ongkir;

        // Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Generate unique order_id
        $orderId = $order->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $grossAmount, // Pastikan gross_amount adalah integer
            ],
            'customer_details' => [
                'first_name' => $customer->nama,
                'email' => $customer->email,
                'phone' => $customer->hp,
            ],
            'enabled_payments' => [
                'gopay',
                'bank_transfer',
                'credit_card',
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        return view('frontend.v_order.payment', [
            'order' => $order,
            'origin' => $origin,
            'originName' => $originName,
            'snapToken' => $snapToken,
        ]);
    }

    public function updateOngkir(Request $request)
    {
        $customer = User::where('id', Auth::id())->first();
        $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();


        if ($order) {
            if (empty($request->input('alamat')) && empty($request->input('no_tlp'))) {
                return redirect()
                    ->route('customer.akun', ['id' => $order->user_id])
                    ->with('error', 'Alamat dan nomor telepon harus diisi sebelum melanjutkan.');
            }

            $jenisLayanan = $order->total_harga >= 50000 ? 'Instan' : 'Reguler';
            $order->layanan_pengiriman = $jenisLayanan;
            $order->alamat = $request->input('alamat');
            $order->no_tlp = $request->input('no_tlp');
            $order->save();
            // Simpan ke session flash agar bisa diakses di halaman tujuan
            return redirect()->route('order.selectpayment');
        }

        return back()->with('error', 'Gagal menyimpan data ongkir');
    }

    public function orderHistory()
    {
        $customer = User::where('id', Auth::id())->first();;;
        // $orders = Order::where('customer_id', $customer->id)->where('status', 'completed')->get();
        $statuses = ['Paid', 'Kirim', 'Selesai', 'Proses COD', 'Dibatalkan'];
        $orders = Order::where('user_id', $customer->id)
            ->whereIn('status', $statuses)
            ->orderBy('id', 'desc')
            ->get();
        return view('frontend.v_order.history', compact('orders'));
    }

    public function invoiceFrontend($id)
    {
        $order = Order::findOrFail($id);
        return view('frontend.v_order.invoice', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'order' => $order,
        ]);
    }

    public function invoiceBackend($id)
    {
        $order = Order::with('orderItems.produk')->findOrFail($id);

        return view('backend.order.invoice', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'order' => $order,
        ]);
    }


    public function complete()
    {
        $customer = Auth::user();
        // Menggunakan whereIn untuk mencari status 'pending' ATAU 'Proses COD'
        $order = Order::where('user_id', $customer->id)
            ->whereIn('status', ['pending']) // Koreksi di sini
            ->with('orderItems.produk') // Pastikan relasi orderItems dan produk terkait dimuat
            ->first();

        // Jika tidak ada order yang ditemukan dengan status pending/Proses COD
        if (!$order) {
            return redirect()->route('order.history')->with('error', 'Tidak ada pesanan yang perlu diselesaikan atau status tidak valid.');
        }

        $today = date('Y-m-d');
        // Pastikan Anda hanya menghitung order yang sudah paid atau completed untuk nomor invoice
        $todayOrderCount = Order::whereDate('created_at', $today)->where('status', 'Paid')->count();
        $nextOrderNumber = $todayOrderCount + 1;
        $invoice = 'ORD-' . date('Ymd') . '-' . str_pad($nextOrderNumber, 4, '0', STR_PAD_LEFT);

        // Update status order dan kode pesanan
        $order->tipe_pembayaran = 'Midtrans';
        $order->status = 'Paid'; // Atau 'Completed' jika itu status akhir Anda
        $order->kode_pesanan = $invoice;

        // 🚀 Tentukan layanan pengiriman
        if ($order->tipe_layanan === 'Dikirim ke alamat') {
            $jenisLayanan = $order->total_harga >= 50000 ? 'Instan' : 'Reguler';
            $order->layanan_pengiriman = $jenisLayanan;
        } elseif ($order->tipe_layanan === 'Ambil di toko') {
            $order->layanan_pengiriman = 'Ambil ditempat';
        }

        // --- Logika Pengurangan Stok ---
        foreach ($order->orderItems as $item) {
            $product = $item->produk; // Akses model Produk melalui relasi
            if ($product) {
                // Pastikan stok tidak menjadi negatif (opsional, tergantung kebijakan Anda)
                if ($product->stok_barang >= $item->quantity) {
                    $product->stok_barang -= $item->quantity; // Kurangi stok
                    $product->save(); // Simpan perubahan stok ke database
                } else {
                    // Opsional: Tangani kasus stok tidak mencukupi
                    // Ini bisa terjadi jika stok berkurang di antara waktu user checkout dan complete
                    // Anda bisa log error, mengembalikan pembayaran, atau memberi tahu user
                    return redirect()->route('order.cart')->with('error', 'Stok barang ' . $product->nm_barang . ' tidak mencukupi.');
                }
            }
        }
        // --- Akhir Logika Pengurangan Stok ---

        $order->save(); // Simpan perubahan status dan kode_pesanan order

        return redirect()->route('order.history')->with('success', 'Checkout berhasil. Pesanan Anda telah diproses.');
    }

    public function callback(Request $request)
    {
        // dd($request->all());
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashed == $request->signature_key) {
            $order = Order::find($request->order_id);
            if ($order) {
                $order->update(['status' => 'Paid']);
            }
        }
    }

    public function processOrder()
    {
        //backend 
        $order = Order::whereIn('status', ['Paid', 'Kirim'])->orderBy('id', 'desc')->get();
        return view('backend.order.process', [
            'index' => $order
        ]);
    }

    public function getProcessOrder(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with('user')
                ->whereIn('status', ['Proses COD', 'Paid', 'Kirim', 'Barang Siap Diambil'])
                ->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
                ->editColumn('total_harga', fn($row) => 'Rp ' . number_format($row->total_harga, 0, ',', '.'))
                ->addColumn('pelanggan', fn($row) => $row->user->name ?? '-')
                ->addColumn('aksi', function ($row) {
                    $btn = '<div class="dropdown position-relative d-inline-block">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                        Action
                    </button>
                    <div class="dropdown-menu center-below p-2 shadow" style="min-width: 160px;">
                        <a href="' . route('pesanan.proses.detail', $row->id) . '" class="btn btn-primary btn-sm w-100 mb-1">
                             Update Pesanan
                        </a>
                        <button onclick=\'batalkanPesanan("' . route('pesanan.batalkan', $row->id) . '")\' class="btn btn-danger btn-sm w-100 mb-1">
                            Batalkan Pesanan
                        </button>
                        <a href="' . route('invoice.backend', $row->id) . '" target="_blank" class="btn btn-success btn-sm w-100 mb-1">
                            Cetak Invoice
                        </a>';

                    // Tambahkan tombol WhatsApp
                    if (!empty($row->user?->no_tlp)) {
                        // ubah 08xxx menjadi 628xxx
                        $phone = preg_replace('/^0/', '62', $row->user->no_tlp);

                        // bisa ditambah template pesan otomatis
                        $message = urlencode("Halo {$row->user->name}, pesanan Anda dengan ID #{$row->id} sedang diproses.");

                        $btn .= '<a href="https://wa.me/' . $phone . '?text=' . $message . '" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-1">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                             </a>';
                    }

                    $btn .= '</div></div>';
                    return $btn;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function finishedOrders()
    {
        //backend 
        $order = Order::where('status', 'Selesai')->orderBy('id', 'desc')->get();
        return view('backend.order.finished', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Selesai',
            'judul' => 'Data Transaksi',
            'index' => $order
        ]);
    }

    public function getFinishedOrders(Request $request)
    {
        if ($request->ajax()) {
            $data = Order::with('user')
                ->where('status', 'Selesai')
                ->orWhere('status', 'Dibatalkan')
                ->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
                ->editColumn('total_harga', fn($row) => 'Rp ' . number_format($row->total_harga, 0, ',', '.'))
                ->addColumn('pelanggan', fn($row) => $row->user->name ?? '-')
                ->addColumn('aksi', function ($row) {
                    $btn = '<div class="dropdown position-relative d-inline-block">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                            Action
                        </button>
                        <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                            <a href="' . route('pesanan.proses.detail', $row->id) . '" class="btn btn-primary btn-sm w-100 mb-1">
                                 Detail Pesanan
                            </a>
                            <a href=" ' . route('invoice.backend', $row->id) . ' " target="_blank" class="btn btn-success btn-sm w-100">
                                Cetak Invoice
                            </a>
                        </div>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }


    public function statusDetail($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.order.show', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'order' => $order,
        ]);
    }

    public function statusUpdate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'required|in:Paid,Kirim,Selesai,Barang Siap Diambil,Dibatalkan,Proses COD',
        ]);

        $oldStatus = $order->status;
        $tglharini = Carbon::now()->format('Y-m-d');

        $activeShift = DB::table('waktukerja')
            ->where('status', 'ON')
            ->where('tanggal', $tglharini)
            ->orderBy('id_shift', 'desc')
            ->first();

        if (!$activeShift) {
            return redirect()->route('pesanan.proses')
                ->with('error', 'Tidak ada shift aktif. Silakan buka shift terlebih dahulu.');
        }

        $shift = $activeShift->shift;

        try {
            DB::beginTransaction();

            // Jika status berubah ke "Selesai", insert ke trkasir
            if ($validatedData['status'] === 'Selesai' && $oldStatus !== 'Selesai') {
                $user = $order->user;

                // insert ke trkasir
                DB::table('trkasir')->insertGetId([
                    'kd_trkasir'       => $order->kode_pesanan,
                    'petugas'          => Auth::user()->username,
                    'shift'            => $shift,
                    'tgl_trkasir'      => now(),
                    'nm_pelanggan'     => $user->name,
                    'tlp_pelanggan'    => $user->no_tlp ?? '-',
                    'alamat_pelanggan' => $user->alamat ?? '-',
                    'ttl_trkasir'      => $order->total_harga,
                    'id_carabayar'     => $order->tipe_pembayaran === 'COD' ? 1 : 2,
                    'jenistx'          => 3,
                ]);

                // insert detail barang
                foreach ($order->orderItems as $item) {
                    $barang = DB::table('barang')->where('id_barang', $item->produk_id)->first();
                    if (!$barang) {
                        throw new \Exception("Barang dengan ID {$item->produk_id} tidak ditemukan");
                    }

                    DB::table('trkasir_detail')->insert([
                        'kd_trkasir'       => $order->kode_pesanan,
                        'id_barang'        => $barang->id_barang,
                        'kd_barang'        => $barang->kd_barang,
                        'nmbrg_dtrkasir'   => $barang->nm_barang,
                        'qty_dtrkasir'     => $item->quantity,
                        'sat_dtrkasir'     => $barang->sat_barang,
                        'hrgjual_dtrkasir' => $item->harga,
                        'hrgttl_dtrkasir'  => $item->quantity * $item->harga,
                        'tipe'             => 3,
                        'idadmin'          => Auth::id(),
                        'waktu'            => now(),
                    ]);
                }
            }

            // Update status order hanya kalau semua berhasil
            $order->update([
                'status' => $validatedData['status']
            ]);

            DB::commit();

            return redirect()->route('pesanan.proses')
                ->with('success', 'Status pesanan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('pesanan.proses')
                ->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }



    public function batalkan($id)
    {
        DB::beginTransaction();

        try {
            // Ambil pesanan beserta item dan produk
            $order = Order::with('orderItems.produk')->findOrFail($id);

            // Validasi status
            if (in_array($order->status, ['Paid', 'Kirim'])) {
                return response()->json([
                    'status' => false,
                    'message' => match ($order->status) {
                        'Paid'  => 'Pesanan tidak bisa dibatalkan karena status pesanan sudah dibayar.',
                        'Kirim' => 'Pesanan tidak bisa dibatalkan karena status pesanan sedang dikirim.',
                        default => 'Pesanan tidak bisa dibatalkan karena status pesanan sudah di ' . $order->status . '.',
                    }
                ]);
            }

            // Kembalikan stok setiap produk dari order items
            foreach ($order->orderItems as $item) {
                $produk = $item->produk;

                // Jika produk ditemukan
                if ($produk) {
                    $produk->stok_barang += $item->quantity;
                    $produk->save(); // Eloquent ORM save
                }
            }

            // Update status pesanan
            $order->status = 'Dibatalkan';
            $order->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pesanan berhasil dibatalkan dan stok dikembalikan.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function cod()
    {
        $customer = Auth::user();

        $order = Order::where('user_id', $customer->id)
            ->where('status', 'pending') // hanya order yang belum dibayar
            ->with('orderItems.produk')
            ->first();

        if (!$order) {
            return redirect()->route('order.history')->with('error', 'Tidak ada pesanan yang bisa diproses COD.');
        }

        DB::beginTransaction();
        try {
            $alamat = $customer->alamat;

            // Update status dan tipe pembayaran lebih dulu
            $order->tipe_pembayaran = 'COD';
            $order->status = 'Proses COD';

            // 🚀 Tentukan layanan pengiriman
            if ($order->tipe_layanan === 'Dikirim ke alamat') {
                $jenisLayanan = $order->total_harga >= 50000 ? 'Instan' : 'Reguler';
                $order->layanan_pengiriman = $jenisLayanan;
            } elseif ($order->tipe_layanan === 'Ambil di toko') {
                $order->layanan_pengiriman = 'Ambil ditempat';
            }

            // Kurangi stok barang
            foreach ($order->orderItems as $item) {
                $product = $item->produk;
                if ($product) {
                    if ($product->stok_barang >= $item->quantity) {
                        $product->stok_barang -= $item->quantity;
                        $product->save();
                    } else {
                        DB::rollBack();
                        return redirect()->route('order.cart')->with('error', 'Stok barang ' . $product->nm_barang . ' tidak mencukupi.');
                    }
                }
            }

            // Simpan dulu biar dapat ID
            $order->save();

            // Generate kode pesanan berdasarkan ID (auto increment)
            $invoice = 'ORD-' . date('Ymd') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $order->kode_pesanan = $invoice;
            $order->save();

            DB::commit();

            return redirect()->route('order.history')->with('success', 'Pesanan COD berhasil dibuat. Silakan tunggu kurir kami.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('order.cart')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bankTransfer()
    {
        $customer = Auth::user();

        $order = Order::where('user_id', $customer->id)
            ->where('status', 'pending') // hanya order yang belum dibayar
            ->with('orderItems.produk')
            ->first();

        if (!$order) {
            return redirect()->route('order.history')->with('error', 'Tidak ada pesanan yang bisa diproses.');
        }
        DB::beginTransaction();
        try {
            // Update status dan tipe pembayaran lebih dulu
            $order->tipe_pembayaran = 'Bank Transfer / Qris';
            $order->status = 'Proses konfirmasi pembayaran'; // tetap pending sampai pembayaran dikonfirmasi

            // Kurangi stok barang
            foreach ($order->orderItems as $item) {
                $product = $item->produk;
                if ($product) {
                    if ($product->stok_barang >= $item->quantity) {
                        $product->stok_barang -= $item->quantity;
                        $product->save();
                    } else {
                        DB::rollBack();
                        return redirect()->route('order.cart')->with('error', 'Stok barang ' . $product->nm_barang . ' tidak mencukupi.');
                    }
                }
            }

            // Simpan dulu biar dapat ID
            $order->save();
            // Generate kode pesanan berdasarkan ID (auto increment)
            $invoice = 'ORD-' . date('Ymd') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
            $order->kode_pesanan = $invoice;
            $order->save();
            DB::commit();
            return redirect()->route('order.history')->with('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('order.cart')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    public function selectPickup(Request $request)
    {
        $request->validate([
            'tipe_layanan' => 'required|in:Dikirim ke alamat,Ambil di toko',
        ]);

        $customer = Auth::user();
        $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();

        if (!$order) {
            return redirect()->route('order.cart')->with('error', 'Pesanan tidak ditemukan.');
        }

        // simpan pilihan user
        $order->tipe_layanan = $request->tipe_layanan;
        $order->save();

        // arahkan sesuai pilihan
        if ($request->tipe_layanan === 'Dikirim ke alamat') {
            return redirect()->route('order.selectShipping');
        }

        return redirect()->route('order.selectpayment');
    }
}
