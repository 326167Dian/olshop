<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>E-Commerce {{ $companySetting->nama_perusahaan }}</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Hind:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Di bagian <head> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="{{ asset('eshop/css/bootstrap.min.css') }}" />

    <!-- Slick -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="{{ asset('eshop/css/nouislider.min.css') }}" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="{{ asset('eshop/css/font-awesome.min.css') }}">

    <!-- Custom stlylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('eshop/css/style.css') }}" />
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
    <style>
        .header-logo {
            display: flex;
            justify-content: flex-start;
            /* default desktop */
        }

        /* Mobile */
        @media (max-width: 768px) {
            .header-logo {
                justify-content: center;
                /* logo di tengah */
            }
        }

        /* Default: sembunyikan desktop */
        .nav-toggle {
            display: none;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .nav-toggle {
                display: flex;
                align-items: center;
            }

            /* kasih jarak antara tombol dan tulisan */
            .nav-toggle button {
                margin-right: 10px;
                /* ini pasti bikin jarak */
            }
        }

        .menu-list a.active {
            font-weight: bold;
            color: #3ea110;
            border-bottom: 2px solid #3ea110;
        }

        .dropdown-backdrop {
            display: none !important;
        }

        .map-responsive {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            /* rasio 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .map-responsive iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>

</head>

<body>
    <!-- HEADER -->
    <header>

        <!-- header -->
        <div id="header">
            <div class="container">
                <div class="pull-left">
                    <!-- Logo -->
                    <div class="header-logo">
                        <a class="logo" href="{{ route('home-page') }}">
                            <img src="{{ asset('storage/' . $companySetting->logo) }}" alt="">
                        </a>
                    </div>
                    <!-- /Logo -->
                    <!-- Search -->
                    <!-- /Search -->
                </div>
                <div class="pull-right">
                    <ul class="header-btns">
                        <!-- Account -->
                        <li class="header-account dropdown default-dropdown" style="margin-left: 15px;">

                            <!-- Ganti dari <div> ke <a> -->


                            @if (Auth::check())
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                    aria-expanded="false">
                                    <div class="header-btns-icon">
                                        <i class="fa fa-user-o"></i>
                                    </div>
                                    <strong class="text-uppercase">My Account <i class="fa fa-caret-down"></i></strong>
                                </a>
                                <a class="text-uppercase">{{ explode(' ', Auth::user()->name)[0] }}</a>
                                <!-- Tambahkan class dropdown-menu agar dikenali Bootstrap -->
                                <ul class="dropdown-menu custom-menu list-unstyled">
                                    <li>
                                        <a href="{{ route('customer.akun', ['id' => Auth::user()->id]) }}">
                                            <i class="fa fa-user-o me-2"></i> My Account
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('order.history') }}">
                                            <i class="fa fa-shopping-cart me-2"></i> History Pemesanan
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#"
                                            onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                            <i class="fa fa-unlock-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                                <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @else
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                    aria-expanded="false">
                                    <div class="header-btns-icon">
                                        <i class="fa fa-user-o"></i>
                                    </div>
                                    <strong class="text-uppercase">Daftar / Masuk <i
                                            class="fa fa-caret-down"></i></strong>
                                </a>
                                <ul class="dropdown-menu custom-menu list-unstyled">
                                    <li>
                                        <a href="{{ route('login.form') }}">
                                            <i class="fa fa-unlock-alt"></i> Daftar / Masuk
                                        </a>
                                    </li>
                                </ul>
                            @endif
                        </li>
                        <!-- /Account -->

                        <!-- Cart -->
                        <li class="header-cart dropdown default-dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                <div class="header-btns-icon">
                                    <a href="{{ route('order.cart') }}">
                                        <i class="fa fa-shopping-cart"></i>
                                        <span class="qty">{{ $cartCount }}</span>
                                    </a>
                                </div>
                                <strong class="text-uppercase">My Cart:</strong>
                                <br>
                                <span style="font-size: 12px">Rp. {{ number_format($cartTotal, 0, ',', '.') }}</span>
                            </a>
                        </li>
                        <!-- /Cart -->

                        <!-- Mobile nav toggle-->
                        <li class="nav-toggle">
                            <button class="nav-toggle-btn main-btn icon-btn">
                                <i class="fa fa-bars"></i>
                            </button>
                            <strong class="text-uppercase">Menu</strong>
                        </li>
                        <!-- / Mobile nav toggle -->
                    </ul>
                </div>
            </div>
            <!-- header -->
        </div>
        <!-- container -->
    </header>
    <!-- /HEADER -->

    <!-- NAVIGATION -->
    <div id="navigation">
        <!-- container -->
        <div class="container">
            <div id="responsive-nav">
                @if (request()->segment(1) == '' || request()->segment(1) == 'home-page')
                    <!-- category nav -->
                    <div class="category-nav">
                        <span class="category-header" onclick="toggleKategoriList()" style="cursor: pointer;">
                            Kategori <i class="fa fa-list"></i>
                        </span>
                        <ul class="category-list" id="kategori-list" style="display: none;">
                            @foreach ($kategori as $item)
                                <li>
                                    <a
                                        href="{{ route('produk.kategori', ['id' => $item->id]) }}">{{ $item->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="category-nav show-on-click">
                        <span class="category-header">Kategori <i class="fa fa-list"></i></span>
                        <ul class="category-list">
                            @foreach ($kategori as $item)
                                <li>
                                    <a
                                        href="{{ route('produk.kategori', ['id' => $item->id]) }}">{{ $item->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- /category nav -->

                <!-- menu nav -->
                <div class="menu-nav">
                    <span class="menu-header">Menu <i class="fa fa-bars"></i></span>
                    <ul class="menu-list">
                        <li>
                            <a href="{{ route('home-page') }}"
                                class="{{ request()->routeIs('home-page') ? 'active' : '' }}">
                                Home
                            </a>
                        </li>
                        @if (auth()->check())
                            <li>
                                <a href="{{ route('produk.all') }}"
                                    class="{{ request()->routeIs('produk.all') ? 'active' : '' }}">
                                    All Produk
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('artikel.all') }}"
                                class="{{ request()->routeIs('artikel.all', 'article.show') ? 'active' : '' }}">
                                Artikel
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- menu nav -->
            </div>
        </div>
        <!-- /container -->
    </div>
    <!-- /NAVIGATION -->

    @yield('content')

    <!-- FOOTER -->
    <footer id="footer" class="section section-grey">
        <div class="container">
            <h3 class="text-center">Tentang Kami</h3>

            <hr>
            <div class="row">
                <!-- Kolom Kiri: Peta Lokasi -->
                <div class="col-md-6 mb-5 mb-md-0" style="padding-bottom: 15px;">
                    <div class="map-responsive shadow rounded overflow-hidden mb-5 pb-3" style="min-height: 250px;">
                        {!! $companySetting->peta_lokasi ?? 'Peta lokasi tidak tersedia.' !!}
                    </div>
                </div>

                <!-- Kolom Kanan: Info Apotek -->
                <div class="col-md-6 mb-4 d-flex flex-column justify-content-between"
                    style="min-height: 250px; padding-bottom: 2rem;">
                    <div>
                        <h2 class="mb-0 font-weight-bold text-dark" style="line-height: 1;">
                            {{ $companySetting->nama_perusahaan ?? 'Nama Apotek' }}
                        </h2>

                        <p class="text-muted mb-3" style="text-align: justify;">
                            {{ $companySetting->deskripsi ?? 'Deskripsi apotek belum tersedia.' }}
                        </p>

                        <ul class="list-unstyled mb-3 text-dark">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-secondary"></i>
                                {{ $companySetting->alamat ?? 'Alamat belum diatur' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone-alt mr-2 text-secondary"></i>
                                {{ $companySetting->telepon ?? '-' }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope mr-2 text-secondary"></i>
                                {{ $companySetting->email ?? '-' }}
                            </li>
                            <li class="mb-2">
                                @php
                                    $website = $companySetting->website;
                                    if ($website && !Str::startsWith($website, ['http://', 'https://'])) {
                                        $website = 'https://' . $website;
                                    }
                                @endphp
                                <a href="{{ $website }}" target="_blank">
                                    <i class="fas fa-globe fa-lg"></i>
                                    {{ $companySetting->website ?? '-' }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Logo di kanan bawah -->
                    <div class="text-right mt-auto">
                        <img src="{{ asset('storage/' . $companySetting->logo) }}" alt="Logo"
                            style="height: 100px; width: auto; object-fit: contain;">
                    </div>
                </div>
            </div>

            <div class="text-center footer-copyright mt-4">
                &copy;
                <script>
                    document.write(new Date().getFullYear());
                </script> All rights reserved | MYSIFA E-COMMERCE
            </div>
        </div>
    </footer>




    <!-- /FOOTER -->

    <!-- jQuery Plugins -->

    <script src="{{ asset('eshop/js/jquery.min.js') }}"></script>
    <script src="{{ asset('eshop/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('eshop/js/slick.min.js') }}"></script>
    <script src="{{ asset('eshop/js/nouislider.min.js') }}"></script>
    <script src="{{ asset('eshop/js/jquery.zoom.min.js') }}"></script>



    <script>
        function toggleKategoriList() {
            const list = document.getElementById('kategori-list');
            list.style.display = (list.style.display === 'none' || list.style.display === '') ? 'block' : 'none';
        }

        // Optional: jika butuh jQuery ready
        $(document).ready(function() {
            // code yang butuh jQuery
        });
    </script>

    <script src="{{ asset('eshop/js/main.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>

    <!-- 🔽 Tambahkan floating button di sini -->
    <div class="wa-float">
        <button onclick="openWhatsApp()" class="wa-button">
            <img src="https://img.icons8.com/color/48/whatsapp--v1.png" alt="WhatsApp Icon">
        </button>
    </div>

    <script>
        function openWhatsApp() {
            // Ganti nomor WA sesuai kebutuhan
            window.open("https://wa.me/{{ $companySetting->telepon }}", "_blank");
        }
    </script>

    <style>
        .wa-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }

        .wa-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            background-color: #25D366;
            box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease-in-out;
        }

        .wa-button:hover {
            transform: scale(1.1);
        }

        .wa-button img {
            width: 35px;
            height: 35px;
        }
    </style>

</body>

</html>
