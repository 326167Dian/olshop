<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Order;
use App\Services\BiteshipService;
use App\Services\DeliveryPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Kurir Instan (GoSend / GrabExpress lewat Biteship).
 *  - rates()   : dipanggil AJAX dari halaman checkout pengantaran (pelanggan).
 *  - pesan()   : admin memanggil driver setelah pesanan siap.
 *  - refresh() : admin menarik status terbaru (cadangan kalau webhook belum masuk).
 *  - batal()   : admin membatalkan driver.
 */
class KurirInstanController extends Controller
{
    public function __construct(private BiteshipService $biteship)
    {
    }

    public function rates(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if (!$this->biteship->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Kurir instan belum diaktifkan.'], 422);
        }

        $order = Order::where('user_id', Auth::id())->where('status', 'pending')->first();

        if (!$order) {
            return response()->json(['ok' => false, 'error' => 'Keranjang belanja kosong.'], 422);
        }

        $km = (CompanySetting::first() ?? new CompanySetting())->jarakKmDari((float) $data['lat'], (float) $data['lng']);

        if ($km === null) {
            return response()->json(['ok' => false, 'error' => 'Lokasi apotek belum diatur. Hubungi admin.'], 422);
        }

        if ($km > DeliveryPricingService::MAX_RADIUS_KM) {
            return response()->json(['ok' => false, 'error' => sprintf(
                'Alamat di luar radius pengantaran maksimal %g km (jarak %.2f km).',
                DeliveryPricingService::MAX_RADIUS_KM,
                $km
            )], 422);
        }

        try {
            $rates = $this->biteship->rates((float) $data['lat'], (float) $data['lng'], $order);
        } catch (\Throwable $e) {
            Log::warning('Biteship rates gagal', ['error' => $e->getMessage()]);

            return response()->json(['ok' => false, 'error' => 'Tarif kurir instan sedang tidak bisa diambil. Silakan pilih Reguler/Express.'], 502);
        }

        if (empty($rates)) {
            return response()->json(['ok' => false, 'error' => 'Saat ini tidak ada driver GoSend/Grab yang tersedia untuk alamat ini.'], 200);
        }

        return response()->json(['ok' => true, 'km' => round($km, 2), 'rates' => $rates]);
    }

    public function pesan(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$order->kurir_company) {
            return back()->with('error', 'Pesanan ini tidak memakai Kurir Instan.');
        }

        if ($order->biteship_order_id && !BiteshipService::isFinalFailed($order->biteship_status)) {
            return back()->with('error', 'Kurir untuk pesanan ini sudah dipesan.');
        }

        if (!in_array($order->status, ['Paid', 'Kirim'], true)) {
            return back()->with('error', 'Kurir hanya bisa dipesan untuk pesanan berstatus Paid/Kirim.');
        }

        try {
            $result = $this->biteship->createOrder($order);
        } catch (\Throwable $e) {
            Log::error('Biteship create order gagal', ['order_id' => $order->id, 'error' => $e->getMessage()]);

            return back()->with('error', $e->getMessage());
        }

        DB::transaction(function () use ($order, $result) {
            $this->biteship->applyToOrder($order, $result);
            $order->status = 'Kirim';
            $order->save();
        });

        return back()->with('success', 'Kurir berhasil dipesan. Driver sedang dicari.');
    }

    public function refresh(string $id)
    {
        $order = Order::findOrFail($id);

        if (!$order->biteship_order_id) {
            return back()->with('error', 'Kurir belum dipesan.');
        }

        try {
            $this->biteship->applyToOrder($order, $this->biteship->getOrder($order->biteship_order_id));
            $order->save();
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Status kurir diperbarui: ' . BiteshipService::statusLabel($order->biteship_status));
    }

    public function batal(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        if (!$order->biteship_order_id) {
            return back()->with('error', 'Kurir belum dipesan.');
        }

        $reason = $request->input('alasan') ?: 'Dibatalkan oleh apotek';

        try {
            $this->biteship->applyToOrder($order, $this->biteship->cancelOrder($order->biteship_order_id, $reason));
            $order->biteship_status = 'cancelled';
            $order->save();
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Kurir dibatalkan. Anda bisa memesan ulang bila perlu.');
    }
}
