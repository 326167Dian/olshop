<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED'),
    'is_3ds' => env('MIDTRANS_IS_3DS'),
    // Script Snap hanya dimuat di frontend bila diaktifkan (saat ini pembayaran
    // memakai QRIS manual/COD, jadi default-nya mati).
    'enabled' => filter_var(env('MIDTRANS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
];
