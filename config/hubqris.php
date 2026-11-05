<?php

return [
    // mysifacenter.my.id -- hub yang jadi satu-satunya partner GDC terdaftar;
    // apotek ini membuat/cek QRIS lewat API cabang hub, bukan langsung ke GDC.
    'base_url' => env('HUBQRIS_BASE_URL', 'https://mysifacenter.my.id'),
    'branch_code' => env('HUBQRIS_BRANCH_CODE', 'APB'),
    'api_key' => env('HUBQRIS_API_KEY'),
    'qr_expiry_minutes' => (int) env('HUBQRIS_QR_EXPIRY_MINUTES', 15),
];
