<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BiteshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Biteship (event order.status / order.waybill_id / order.price).
 * Daftarkan URL ini di Dashboard Biteship -> Integrasi -> Webhook:
 *   https://domain-anda/webhooks/biteship/{BITESHIP_WEBHOOK_TOKEN}
 *
 * Biteship mengirim request uji (body kosong) saat URL didaftarkan dan
 * mengharapkan HTTP 200 -- karena itu payload tanpa order_id tetap dibalas ok.
 *
 * Status "delivered" TIDAK otomatis mengubah pesanan jadi "Selesai", karena
 * "Selesai" mencatat transaksi kasir dan butuh shift aktif (lihat
 * OrderController::statusUpdate). Admin tetap menandai Selesai seperti biasa.
 */
class BiteshipWebhookController extends Controller
{
    public function handle(Request $request, string $token, BiteshipService $biteship)
    {
        $expected = (string) config('biteship.webhook_token');

        abort_unless($expected !== '' && hash_equals($expected, $token), 401);

        $orderId = (string) $request->input('order_id', '');

        if ($orderId === '') {
            return response()->json(['status' => 'ok']);
        }

        $order = Order::where('biteship_order_id', $orderId)->first();

        if (!$order) {
            Log::info('Webhook Biteship untuk order tidak dikenal', ['order_id' => $orderId]);

            return response()->json(['status' => 'ok']);
        }

        $biteship->applyToOrder($order, $request->all());
        $order->save();

        return response()->json(['status' => 'ok']);
    }
}
