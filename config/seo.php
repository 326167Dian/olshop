<?php

/*
|--------------------------------------------------------------------------
| Auto SEO
|--------------------------------------------------------------------------
| Nama apotek, deskripsi, alamat, telepon & logo diambil otomatis dari
| tabel company_settings (menu Company Setting), jadi tidak perlu diisi
| di sini. File ini hanya untuk pengaturan teknis.
*/

return [
    'separator' => ' | ',

    // Title default beranda: "<nama apotek> | <tagline>"
    'tagline' => env('SEO_TAGLINE', 'Belanja Obat & Alkes Online, Diantar ke Rumah'),

    // Batas panjang (standar Google)
    'title_max' => 60,
    'description_max' => 160,

    // Kota default untuk schema Pharmacy bila alamat tidak memuatnya
    'city' => env('SEO_CITY', 'Bekasi'),

    // IndexNow (Bing, Yandex, Seznam, Naver). Google membaca sitemap.
    // Buat key: php -r "echo bin2hex(random_bytes(16));"
    'indexnow' => [
        'enabled' => env('INDEXNOW_ENABLED', false),
        'key' => env('INDEXNOW_KEY'),
        'endpoint' => 'https://api.indexnow.org/indexnow',
    ],

    // Model yang masuk sitemap: class => [changefreq, priority].
    // Tiap model menentukan sendiri data mana yang layak tampil lewat
    // scopeSeoSitemap() (mis. obat resep TIDAK dimasukkan).
    'sitemap' => [
        'cache_minutes' => 60,
        'models' => [
            \App\Models\Product::class => ['weekly', 0.8],
            \App\Models\Category::class => ['weekly', 0.6],
            \App\Models\Article::class => ['monthly', 0.6],
        ],
        // route name => [changefreq, priority]
        'static_routes' => [
            'home-page' => ['daily', 1.0],
            'produk.all' => ['daily', 0.7],
            'artikel.all' => ['weekly', 0.5],
            'testimoni.all' => ['monthly', 0.3],
            'video-edukasi.all' => ['weekly', 0.4],
        ],
    ],

    // Path yang dilarang di-crawl (robots.txt)
    'robots_disallow' => [
        '/inventory', '/backend', '/staf', '/login', '/logout',
        '/cart', '/customer', '/reseller', '/order', '/history',
        '/select-payment', '/select-shipping', '/produk/cari',
        '/webhooks', '/midtrans-callback',
    ],
];
