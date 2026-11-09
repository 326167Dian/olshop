<?php

/*
|--------------------------------------------------------------------------
| Biteship -- agregator kurir instan (GoSend / GrabExpress)
|--------------------------------------------------------------------------
|
| Dipakai untuk opsi "Kurir Instan" di checkout (lihat App\Services\BiteshipService).
| Kosongkan BITESHIP_API_KEY untuk menyembunyikan opsi ini di checkout.
|
| API key sandbox berawalan "biteship_test." -- order yang dibuat dengan key ini
| tidak memanggil driver sungguhan. Ganti ke key production di .env setelah tes.
|
*/

return [
    'api_key' => env('BITESHIP_API_KEY'),

    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),

    // Kode kurir Biteship yang ditawarkan ke pelanggan, dipisah koma.
    'couriers' => env('BITESHIP_COURIERS', 'gojek,grab'),

    // Hanya layanan dengan tipe ini yang ditampilkan (kosongkan = semua tipe).
    'service_types' => array_filter(array_map('trim', explode(',', env('BITESHIP_SERVICE_TYPES', 'instant')))),

    // Token rahasia di URL webhook: https://domain/webhooks/biteship/{token}
    'webhook_token' => env('BITESHIP_WEBHOOK_TOKEN'),

    // Berat default per item (gram) -- tabel barang tidak menyimpan berat.
    'default_item_weight' => (int) env('BITESHIP_DEFAULT_ITEM_WEIGHT', 100),

    'timeout' => 20,
];
