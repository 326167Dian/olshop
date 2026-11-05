<?php

namespace App\Http\Controllers;

use App\Models\QrisPayment;
use Illuminate\Http\Request;

/**
 * Menerima notifikasi yang DITERUSKAN hub (mysifacenter.my.id) -- bukan dari GDC
 * langsung (apotek ini bukan partner GDC terdaftar, lihat HubQrisService).
 * Dipakai sama untuk QrisPayment manapun ('marketplace' maupun 'kasir'); efek
 * sampingnya (update Order, kembalikan stok) ada di QrisPayment::markAsPaid()/
 * markAsFailed(), bukan di sini, supaya endpoint poll (OrderController) dan
 * webhook ini selalu konsisten.
 */
class HubQrisWebhookController extends Controller
{
    public function notification(Request $request)
    {
        $key = (string) $request->header('X-Branch-Key', '');

        abort_unless($key !== '' && hash_equals((string) config('hubqris.api_key'), $key), 401);

        $qrisPayment = QrisPayment::where('reference_no', (string) $request->input('reference_no'))->first();

        abort_unless($qrisPayment, 404);

        $status = (string) $request->input('status');

        if ($status === 'paid') {
            $qrisPayment->markAsPaid();
        } elseif (in_array($status, ['failed', 'expired'], true)) {
            $qrisPayment->markAsFailed($status);
        }

        return response()->json(['status' => 'ok']);
    }
}
