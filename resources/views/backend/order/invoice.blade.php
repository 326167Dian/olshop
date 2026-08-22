<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            /* Light background similar to the image */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .invoice-wrapper {
            max-width: 800px;
            /* Adjust as needed for desired width */
            margin: 40px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .invoice-logo {
            max-width: 150px;
            /* Adjust logo size */
            height: auto;
        }

        .invoice-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #343a40;
        }

        .address-block {
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .contact-info p {
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #555;
        }

        .details-section {
            background-color: #f8f9fa;
            /* Light grey background for details */
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: flex;
            /* Using flexbox for alignment */
            justify-content: space-between;
            flex-wrap: wrap;
            /* Allow wrapping on smaller screens */
        }

        .details-col {
            flex: 1;
            /* Equal width columns */
            min-width: 200px;
            /* Minimum width before wrapping */
            margin-right: 20px;
            /* Spacing between columns */
        }

        .details-col:last-child {
            margin-right: 0;
        }

        .details-col p {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .details-col strong {
            display: inline-block;
            /* Keep label and value on same line */
            min-width: 80px;
            /* Align values */
        }

        .table-product thead th {
            background-color: #343a40;
            /* Darker header for table */
            color: #fff;
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 12px 10px;
        }

        .table-product tbody td {
            padding: 10px;
            vertical-align: middle;
        }

        .table-product tfoot td {
            padding: 8px 10px;
        }

        .total-row .fw-bold {
            font-size: 1.2rem;
        }

        .total-value {
            font-size: 1.4rem;
            font-weight: bold;
            color: #007bff;
            /* Blue color for total */
        }

        .customer-message-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-top: 30px;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        /* Print styles */
        Anda benar sekali. Ini adalah masalah umum saat mencetak dari browser. Tampilan di layar (screen) dan tampilan cetak (print) ditangani secara berbeda oleh browser. Agar hasil cetak semirip mungkin dengan yang Anda lihat di layar,
        kita perlu menerapkan beberapa penyesuaian khusus untuk media cetak (@media print). Ada beberapa hal yang menyebabkan perbedaan ini: Backgrounds dan Shadows: Browser sering menonaktifkan pencetakan warna latar belakang dan bayangan secara default untuk menghemat tinta. Margin Halaman: Browser menambahkan margin default pada halaman cetak. Ukuran Font: Satuan px atau rem bisa diinterpretasikan sedikit berbeda saat dicetak. Menggunakan pt (points) di media cetak bisa lebih konsisten. Page Breaks: Konten mungkin terpotong di tengah bagian atau tabel jika tidak diatur dengan baik. Header/Footer Browser: Browser sering menambahkan URL,
        tanggal,
        dan judul halaman di header/footer cetakan. Ini harus dimatikan secara manual oleh pengguna di dialog cetak. Berikut adalah penyesuaian CSS yang lebih lengkap di dalam blok @media print untuk meningkatkan akurasi cetakan Anda: HTML < !DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Invoice # {
                {
                $order->id
            }
        }

        </title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"><style>body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI',
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;
            color: #333;
        }

        .invoice-wrapper {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .invoice-logo {
            max-width: 150px;
            height: auto;
        }

        .invoice-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #343a40;
        }

        .address-block {
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .contact-info p {
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #555;
        }

        .details-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .details-col {
            flex: 1;
            min-width: 200px;
            margin-right: 20px;
        }

        .details-col:last-child {
            margin-right: 0;
        }

        .details-col p {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .details-col strong {
            display: inline-block;
            min-width: 80px;
        }

        .table-product thead th {
            background-color: #343a40;
            color: #fff;
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 12px 10px;
        }

        .table-product tbody td {
            padding: 10px;
            vertical-align: middle;
        }

        .table-product tfoot td {
            padding: 8px 10px;
        }

        .total-row .fw-bold {
            font-size: 1.2rem;
        }

        .total-value {
            font-size: 1.4rem;
            font-weight: bold;
            color: #007bff;
        }

        .customer-message-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-top: 30px;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        /* --- PENTING: ATURAN UNTUK PRINTING YANG LEBIH AKURAT --- */
        @media print {

            /* Aturan @page untuk mengatur ukuran dan margin halaman cetak */
            @page {
                /* Untuk A4: size: A4 portrait; atau size: 210mm 297mm; */
                /* Untuk Letter: size: Letter portrait; atau size: 216mm 279mm; */
                /* Jika ingin tanpa margin default browser, gunakan margin: 0; */
                margin: 2cm;
                /* Contoh: Margin 1cm di semua sisi */
            }

            /* Global print adjustments */
            body {
                background-color: #fff !important;
                /* Pastikan latar belakang putih */
                margin: 0;
                /* Hapus margin body saat print */
                padding: 0;
                /* Hapus padding body saat print */
                color: #000;
                /* Pastikan warna teks hitam */
                /* Paksa browser mencetak warna latar belakang dan bayangan */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                font-size: 10pt;
                /* Gunakan pt (points) untuk ukuran font yang lebih konsisten di cetakan */
            }

            .invoice-wrapper {
                box-shadow: none !important;
                /* Hapus bayangan */
                margin: 0 auto;
                /* Tengah, tanpa margin samping yang besar dari body */
                padding: 0.5cm;
                /* Hapus padding wrapper saat print */
                border-radius: 0;
                /* Hapus border-radius */
                max-width: 100%;
                /* Ambil lebar penuh yang tersedia */
            }

            /* Pastikan bagian-bagian penting tidak terpotong antar halaman */
            .invoice-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                flex-wrap: nowrap !important;
            }

            .invoice-header>.col-md-6 {
                width: 50% !important;
                padding: 0 !important;
            }

            .invoice-header>.text-end {
                padding-top: 1cm !important;
                text-align: right !important;
            }

            .details-section,
            .table-product,
            .customer-message-box {
                page-break-inside: avoid !important;
                /* Hindari pemotongan di tengah elemen */
            }

            /* Penyesuaian ukuran font untuk cetak agar lebih rapi */
            .invoice-title {
                font-size: 24pt !important;
            }

            .address-block,
            .contact-info p,
            .details-col p {
                font-size: 9pt !important;
            }

            .table-product thead th {
                font-size: 8.5pt !important;
                background-color: #e9ecef !important;
                /* Latar header lebih terang untuk hemat tinta */
                color: #000 !important;
                /* Teks header hitam */
            }

            .table-product tbody td,
            .table-product tfoot td {
                font-size: 9.5pt !important;
            }

            .total-row .fw-bold {
                font-size: 11.5pt !important;
            }

            .total-value {
                font-size: 13.5pt !important;
                color: #000 !important;
                /* Pastikan total berwarna hitam di cetakan */
            }

            .customer-message-box {
                font-size: 9pt !important;
                background-color: #f8f9fa !important;
                /* Pertahankan latar belakang message box */
            }

            .col-md-7 {
                width: 65% !important;
                float: left;
            }

            .col-md-5 {
                width: 35% !important;
                float: right;
            }

            .row::after {
                content: "";
                display: table;
                clear: both;
            }

            /* Sembunyikan elemen yang tidak perlu dicetak (jika ada) */
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-wrapper">
        <div class="row invoice-header align-items-center">
            <div class="col-md-6">
                <img src="{{ asset('storage/' . $companySetting->logo) }}" alt="Company Logo" class="invoice-logo mb-3">
            </div>
            <div class="col-md-6 text-end">
                <h1 class="invoice-title">INVOICE</h1>
                <div class="contact-info mt-3">
                    <p class="mb-0"><i class="fas fa-phone-alt me-2"></i>{{ $companySetting->telepon }}</p>
                    <p class="mb-0"><i class="fas fa-envelope me-2"></i>{{ $companySetting->email }}</p>
                    <p class="mb-0"><i class="fas fa-globe me-2"></i>{{ $companySetting->website }}</p>
                </div>
            </div>
        </div>

        <div class="details-section">
            <div class="details-col">
                <p class="fw-bold mb-2 text-primary">Nota Pesanan untuk</p>
                <p class="mb-1"><strong class="me-2">Nama Pembeli:</strong>{{ $order->user->name }}</p>
                <p class="mb-1"><strong class="me-2">No. Handphone Pembeli:</strong>{{ $order->user->no_tlp ?? 'N/A' }}
                </p>
                <p class="mb-1"><strong class="me-2">Alamat Pembeli:</strong>{{ $order->user->alamat ?? 'N/A' }}</p>
            </div>
            <div class="details-col">
                <p class="fw-bold mb-2 text-primary">Detail Pesanan</p>
                <p class="mb-1"><strong class="me-2">No. Pesanan:</strong>{{ $order->kode_pesanan }}</p>
                <p class="mb-1"><strong class="me-2">Tanggal transaksi:</strong>
                    {{ $order->created_at->format('d/m/Y') }}</p>
                <p class="mb-1"><strong class="me-2">Layanan:</strong><b>{{ $order->tipe_layanan}}</b></p>
                @if ($order->tipe_layanan == 'Ambil di toko')
                <p class="mb-1"><strong class="me-2">Pengambilan:</strong>{{ $order->layanan_pengiriman ?? 'N/A' }}</p>
                @elseif ($order->tipe_layanan == 'Dikirim ke alamat' )
                <p class="mb-1"><strong class="me-2">Kurir:</strong>{{ $order->layanan_pengiriman ?? 'N/A' }}
                </p>
                @endif

            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-product">
                <thead>
                    <tr>
                        <th class="w-25">Produk</th>
                        <th class="w-25">Deskripsi</th>
                        <th class="text-center">Kuantitas</th>
                        <th class="text-end">Tarif</th>
                        <th class="text-end">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $subtotal = 0;
                    @endphp
                    @foreach ($order->orderItems as $item)
                    @php
                    $lineTotal = $item->harga * $item->quantity;
                    $subtotal += $lineTotal;
                    @endphp
                    <tr>
                        <td>{{ $item->produk->nm_barang }}</td>
                        <td>{{ $item->produk->category->name ?? 'Description of product or service' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp. {{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @for ($i = 0; $i < 5 - count($order->orderItems); $i++)
                        @endfor
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-7 customer-message-box">
                <div class="address-block">
                    <h5 class="mb-1">{{ $companySetting->nama_perusahaan }}</h5>
                    <p class="mb-0">{{ $companySetting->alamat }}</p>
                </div>
            </div>
            <div class="col-md-5">
                <table class="table table-borderless table-sm text-end">
                    <tbody>
                        <tr class="total-row">
                            <td class="pe-0 fw-bold">Total</td>
                            <td class="ps-0 total-value">Rp.
                                {{ number_format($subtotal + $order->biaya_ongkir, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>