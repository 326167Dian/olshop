<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>@yield('title') | {{ $companySetting->nama_perusahaan }}</title>

    <!-- Font Awesome (dipakai oleh ikon-ikon di halaman konten) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Espire core css -->
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">

    @stack('css')
    <style>
        .dropdown-menu.center-below {
            position: absolute !important;
            left: 50% !important;
            top: 100% !important;
            transform: translateX(-50%) !important;
            margin-top: 0.3rem;
            z-index: 9999;
        }

        /* Halaman lama membungkus konten dengan <section class="content">,
           yang bentrok dengan class ".content" milik Espire (wrapper utama
           dengan margin-left/margin-top untuk sidebar & topbar). Netralkan
           supaya tidak ada margin dobel di dalam .main. */
        .main .content {
            margin: 0 !important;
            width: 100% !important;
        }

        /* Hilangkan jarak kosong di sisi kiri konten (padding .main +
           padding bawaan .container-fluid). */
        .main {
            padding-left: 0 !important;
        }

        .main .container-fluid {
            padding-left: 0 !important;
        }
    </style>
</head>

<body>
    <div class="layout">
        <div class="vertical-layout">

            <!-- Header START -->
            <div class="header-text-dark header-nav layout-vertical">
                <div class="header-nav-wrap">
                    <div class="header-nav-left">
                        <div class="header-nav-item desktop-toggle">
                            <div class="header-nav-item-select cursor-pointer">
                                <i class="nav-icon feather icon-menu icon-arrow-right"></i>
                            </div>
                        </div>
                        <div class="header-nav-item mobile-toggle">
                            <div class="header-nav-item-select cursor-pointer">
                                <i class="nav-icon feather icon-menu icon-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                    <div class="header-nav-right">
                        <div class="header-nav-item">
                            <div class="header-nav-item-select">
                                <div class="toggle-wrapper" data-bs-toggle="modal" data-bs-target="#quick-view">
                                    <i class="nav-icon feather icon-settings"></i>
                                </div>
                            </div>
                        </div>
                        <div class="header-nav-item">
                            <div class="dropdown header-nav-item-select nav-profile">
                                <div class="toggle-wrapper" id="nav-profile-dropdown" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-circle avatar-image" style="width: 35px; height: 35px; line-height: 35px;">
                                        <img src="{{ asset('newadmin/assets/images/avatars/default-avatar.jpg') }}" alt="">
                                    </div>
                                    <span class="fw-bold mx-1">{{ Auth::user()->nama_lengkap }}</span>
                                    <i class="feather icon-chevron-down"></i>
                                </div>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="nav-profile-header">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-circle avatar-image">
                                                <img src="{{ asset('newadmin/assets/images/avatars/default-avatar.jpg') }}" alt="">
                                            </div>
                                            <div class="d-flex flex-column ms-1">
                                                <span class="fw-bold text-dark">{{ Auth::user()->nama_lengkap }}</span>
                                                <span class="font-size-sm">{{ Auth::user()->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0)" class="dropdown-item" onclick="logoutConfirm(event)">
                                        <div class="d-flex align-items-center">
                                            <i class="font-size-lg me-2 feather icon-log-out"></i>
                                            <span>Logout</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Header END -->

            <!-- Side Nav START -->
            <div class="side-nav vertical-menu nav-menu-light scrollable">
                <div class="nav-logo">
                    <div class="w-100 logo">
                        <img class="img-fluid" src="{{ asset('storage/' . $companySetting->logo) }}" style="max-height: 60px;" alt="logo">
                    </div>
                    <div class="mobile-close">
                        <i class="icon-arrow-left feather"></i>
                    </div>
                </div>
                <div class="small text-center text-muted px-2 mb-2">{{ $companySetting->nama_perusahaan }}</div>
                <ul class="nav-menu">
                    <li class="nav-menu-item {{ Route::currentRouteName() == 'backend.dashboard' ? 'active' : '' }}">
                        <a href="{{ route('backend.dashboard') }}">
                            <i class="feather icon-home"></i>
                            <span class="nav-menu-item-title">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-group-title">DATA</li>
                    <li class="nav-submenu">
                        <a class="nav-submenu-title" href="javascript:void(0)">
                            <i class="feather icon-box"></i>
                            <span>Data Master</span>
                            <i class="nav-submenu-arrow"></i>
                        </a>
                        <ul class="nav-menu menu-collapse">
                            <li class="nav-menu-item {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.show']) ? 'active' : '' }}">
                                <a href="{{ route('customer.index') }}">
                                    <i class="feather icon-users"></i>
                                    <span class="nav-menu-item-title">Customer</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ in_array(Route::currentRouteName(), ['category.index', 'category.create', 'category.edit']) ? 'active' : '' }}">
                                <a href="{{ route('category.index') }}">
                                    <i class="feather icon-tag"></i>
                                    <span class="nav-menu-item-title">Kategori</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ in_array(Route::currentRouteName(), ['product.index', 'product.edit', 'product.show']) ? 'active' : '' }}">
                                <a href="{{ route('product.index') }}">
                                    <i class="feather icon-package"></i>
                                    <span class="nav-menu-item-title">Produk</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ in_array(Route::currentRouteName(), ['article.index', 'article.create', 'article.edit', 'article.show']) ? 'active' : '' }}">
                                <a href="{{ route('article.index') }}">
                                    <i class="feather icon-file-text"></i>
                                    <span class="nav-menu-item-title">Artikel</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-submenu">
                        <a class="nav-submenu-title" href="javascript:void(0)">
                            <i class="feather icon-shopping-bag"></i>
                            <span>Pesanan</span>
                            <i class="nav-submenu-arrow"></i>
                        </a>
                        <ul class="nav-menu menu-collapse">
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'pesanan.proses' ? 'active' : '' }}">
                                <a href="{{ route('pesanan.proses') }}">
                                    <i class="feather icon-refresh-cw"></i>
                                    <span class="nav-menu-item-title">Pesanan Proses</span>
                                    @if(($pendingPaymentCount ?? 0) > 0)
                                    <span class="badge bg-danger ms-1">{{ $pendingPaymentCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'pesanan.selesai' ? 'active' : '' }}">
                                <a href="{{ route('pesanan.selesai') }}">
                                    <i class="feather icon-check-circle"></i>
                                    <span class="nav-menu-item-title">Pesanan Selesai</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-submenu">
                        <a class="nav-submenu-title" href="javascript:void(0)">
                            <i class="feather icon-bar-chart-2"></i>
                            <span>Laporan</span>
                            <i class="nav-submenu-arrow"></i>
                        </a>
                        <ul class="nav-menu menu-collapse">
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'report.process' ? 'active' : '' }}">
                                <a href="{{ route('report.process') }}">
                                    <i class="feather icon-file-text"></i>
                                    <span class="nav-menu-item-title">Laporan Pesanan Proses</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'report.finished' ? 'active' : '' }}">
                                <a href="{{ route('report.finished') }}">
                                    <i class="feather icon-file-text"></i>
                                    <span class="nav-menu-item-title">Laporan Pesanan Selesai</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'report.visits' ? 'active' : '' }}">
                                <a href="{{ route('report.visits') }}">
                                    <i class="feather icon-eye"></i>
                                    <span class="nav-menu-item-title">Laporan Kunjungan Website</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-group-title">PENGATURAN</li>
                    <li class="nav-submenu">
                        <a class="nav-submenu-title" href="javascript:void(0)">
                            <i class="feather icon-settings"></i>
                            <span>Setting</span>
                            <i class="nav-submenu-arrow"></i>
                        </a>
                        <ul class="nav-menu menu-collapse">
                            <li class="nav-menu-item {{ Route::currentRouteName() == 'company-setting.index' ? 'active' : '' }}">
                                <a href="{{ route('company-setting.index') }}">
                                    <i class="feather icon-sliders"></i>
                                    <span class="nav-menu-item-title">Profil Perusahaan</span>
                                </a>
                            </li>
                            <li class="nav-menu-item {{ in_array(Route::currentRouteName(), ['banner.index', 'banner.create', 'banner.edit']) ? 'active' : '' }}">
                                <a href="{{ route('banner.index') }}">
                                    <i class="feather icon-image"></i>
                                    <span class="nav-menu-item-title">Banner</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-menu-item">
                        <a href="javascript:void(0)" onclick="logoutConfirm(event)">
                            <i class="feather icon-log-out"></i>
                            <span class="nav-menu-item-title">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Side Nav END -->

            <!-- Content START -->
            <div class="content">
                <div class="main">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h4 class="mb-0">@yield('header')</h4>
                    </div>

                    @yield('content')
                </div>

                <!-- Footer START -->
                <div class="footer">
                    <div class="footer-content">
                        <p class="mb-0">Copyright &copy; 2025 <a href="#">Apotek E-Commerce</a>. All rights reserved.</p>
                    </div>
                </div>
                <!-- Footer END -->
            </div>
            <!-- Content END -->

            <!-- Quick View START -->
            <div class="modal modal-right fade quick-view" id="quick-view">
                <div class="modal-dialog right">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title pull-left">Theme Config</h4>
                            <button type="button" class="close pull-right" data-bs-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body scrollable">
                            <div class="mb-4">
                                <h5 class="mb-0">Header Color</h5>
                                <p>Config header background color</p>
                                <div class="theme-configurator d-flex mt-2">
                                    <div class="radio">
                                        <input id="header-default" name="header-theme" type="radio" checked value="#ffffff">
                                        <label for="header-default"></label>
                                    </div>
                                    <div class="radio">
                                        <input id="header-primary" name="header-theme" type="radio" value="#11a1fd">
                                        <label for="header-primary"></label>
                                    </div>
                                    <div class="radio">
                                        <input id="header-success" name="header-theme" type="radio" value="#00c569">
                                        <label for="header-success"></label>
                                    </div>
                                    <div class="radio">
                                        <input id="header-info" name="header-theme" type="radio" value="#5a75f9">
                                        <label for="header-info"></label>
                                    </div>
                                    <div class="radio">
                                        <input id="header-warning" name="header-theme" type="radio" value="#ffc833">
                                        <label for="header-warning"></label>
                                    </div>
                                    <div class="radio">
                                        <input id="header-danger" name="header-theme" type="radio" value="#f46363">
                                        <label for="header-danger"></label>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <h5 class="mb-0">Side Nav Dark</h5>
                                <p>Change Side Nav to dark</p>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="side-nav-theme-toggle" id="side-nav-theme-toggle">
                                    <label class="form-check-label" for="side-nav-theme-toggle"></label>
                                </div>
                            </div>
                            <hr>
                            <div>
                                <h5 class="mb-0">Folded Menu</h5>
                                <p>Toggle Folded Menu</p>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="side-nav-fold-toogle" id="side-nav-fold-toogle">
                                    <label class="form-check-label" for="side-nav-fold-toogle"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Quick View END -->
        </div>
    </div>
    <!-- ./layout -->

    <form id="keluar-app" action="{{ route('backend.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Core Vendors JS (jQuery + Bootstrap 5 + Popper + feather icons) -->
    <script src="{{ asset('newadmin/assets/js/vendors.min.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('newadmin/assets/vendors/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('newadmin/assets/vendors/datatables/dataTables.bootstrap.min.js') }}"></script>

    <!-- Core JS (sidebar toggle, dropdown, theme configurator, dst) -->
    <script src="{{ asset('newadmin/assets/js/app.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="{{ asset('plugins/sweetalert/sweetalert2.all.min.js') }}"></script>

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: true,
            timer: 1500
        });
    </script>
    @endif

    <script type="text/javascript">
        $('.show_confirm').click(function(event) {
            var form = $(this).closest("form");
            var konfdelete = $(this).data("konf-delete");
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Hapus Data?',
                html: "Data yang dihapus <strong>" + konfdelete + "</strong> tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, dihapus',
                cancelButtonText: 'Batal',
                timer: 1500
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
    <script>
        function batalkanPesanan(url) {
            if (confirm('Yakin ingin membatalkan pesanan ini?')) {
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        alert('Terjadi kesalahan saat membatalkan pesanan.');
                        console.error(error);
                    });
            }
        }
    </script>

    <script>
        function logoutConfirm(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin logout?',
                text: "Kamu akan keluar dari aplikasi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('keluar-app').submit();
                }
            });
        }
    </script>

    @yield('script')
    @stack('scripts')
</body>

</html>
