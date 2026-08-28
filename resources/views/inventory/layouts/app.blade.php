<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>@yield('header', 'Inventory') | {{ $companySetting->nama_perusahaan }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">

    @stack('css')
    <style>
        .main .content {
            margin: 0 !important;
            width: 100% !important;
        }

        .main {
            padding-left: 0 !important;
            padding-top: 0 !important;
        }

        .main .container-fluid {
            padding-left: 0 !important;
        }

        /* Nama apotek berjalan dari kanan ke kiri di bawah logo MySIFA (sidebar). */
        .mysifa-marquee {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .mysifa-marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: mysifa-marquee-scroll 12s linear infinite;
            font-size: 0.8rem;
        }

        @keyframes mysifa-marquee-scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        /*
         * Logo MySIFA sengaja TIDAK memakai class "logo" bawaan Espire: script
         * newadmin/assets/js/app.min.js (fungsi setLogo()) menimpa paksa atribut
         * src pada setiap <img> di dalam ".side-nav .logo" ke path tema
         * (/assets/images/logo/logo.png dkk, yang tidak ada di proyek ini) setiap
         * kali sidebar di-toggle collapse/expand — itulah sebabnya logo hilang
         * setelah hide/unhide berulang. Class custom di bawah ini meniru tata
         * letak ".logo" bawaan tanpa ikut tersasar oleh selector tersebut.
         */
        .nav-logo .mysifa-logo-wrap {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .side-nav.nav-menu-collapse:not(.nav-menu-quick-expand) .nav-logo .mysifa-logo-wrap {
            justify-content: center;
        }

        .side-nav.nav-menu-collapse:not(.nav-menu-quick-expand) .nav-logo .mysifa-logo-wrap img {
            height: 40px;
            max-height: 40px !important;
        }
    </style>
</head>

<body>
    @php
        /** @var \App\Models\Admin $currentAdmin */
        $currentAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $adminAvatar = $currentAdmin->foto ? asset('storage/' . $currentAdmin->foto) : asset('newadmin/assets/images/avatars/default-avatar.jpg');

        // Modul yang sudah punya halaman/route sendiri: kolom flag admin => prefix nama route-nya.
        $columnRoutes = [
            'mpengguna' => 'inventory.admin.',
            'mheader' => 'inventory.setheader.',
            'mjenisbayar' => 'inventory.carabayar.',
            'mpelanggan' => 'inventory.pelanggan.',
        ];

        $activeModule = 'home';
        foreach ($columnRoutes as $routedColumn => $routePrefix) {
            if (request()->routeIs($routePrefix . '*')) {
                $activeModule = $routedColumn;
                break;
            }
        }
        if ($activeModule === 'home' && request()->routeIs('inventory.index')) {
            $activeModule = request()->query('module', 'home');
        }
    @endphp

    <div class="layout">
        <div class="vertical-layout">

            <!-- Header START -->
            <div class="header-text-dark header-nav layout-vertical">
                <div class="header-nav-wrap">
                    <div class="header-nav-left d-flex align-items-center">
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
                        <h4 class="mb-0 ms-2">@yield('header', 'Dashboard')</h4>
                    </div>
                    <div class="header-nav-right">
                        <div class="header-nav-item">
                            <div class="dropdown header-nav-item-select nav-profile">
                                <div class="toggle-wrapper" id="nav-profile-dropdown" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-circle avatar-image" style="width: 35px; height: 35px; line-height: 35px;">
                                        <img src="{{ $adminAvatar }}" alt="">
                                    </div>
                                    <span class="fw-bold mx-1">{{ $currentAdmin->nama_lengkap }}</span>
                                    <i class="feather icon-chevron-down"></i>
                                </div>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="nav-profile-header">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-circle avatar-image">
                                                <img src="{{ $adminAvatar }}" alt="">
                                            </div>
                                            <div class="d-flex flex-column ms-1">
                                                <span class="fw-bold text-dark">{{ $currentAdmin->nama_lengkap }}</span>
                                                <span class="font-size-sm">{{ ucfirst($currentAdmin->akses_level) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('backend.dashboard') }}" class="dropdown-item">
                                        <div class="d-flex align-items-center">
                                            <i class="font-size-lg me-2 feather icon-grid"></i>
                                            <span>Dashboard Backend</span>
                                        </div>
                                    </a>
                                    <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                                        <div class="d-flex align-items-center">
                                            <i class="font-size-lg me-2 feather icon-user"></i>
                                            <span>Edit Profil</span>
                                        </div>
                                    </a>
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
                <div class="nav-logo flex-column">
                    <div class="w-100 mysifa-logo-wrap">
                        <img class="img-fluid" id="mysifa-logo"
                            src="{{ asset('newadmin/assets/images/mysifalogo.png') }}" style="max-height: 70px;"
                            alt="MySIFA - Smart Inventory For Apotek">
                    </div>
                    <div class="mobile-close">
                        <i class="icon-arrow-left feather"></i>
                    </div>
                </div>
                <div class="mysifa-marquee px-2 mb-2">
                    <span class="fw-semibold text-muted">{{ \Illuminate\Support\Facades\DB::table('setheader')->value('satu') ?? 'APOTEK' }}</span>
                </div>
                <ul class="nav-menu">
                    <li class="nav-menu-item {{ $activeModule === 'home' ? 'active' : '' }}">
                        <a href="{{ route('inventory.index') }}">
                            <i class="feather icon-home"></i>
                            <span class="nav-menu-item-title">Dashboard</span>
                        </a>
                    </li>

                    @php
                        $sidebarIcons = [
                            'mpengguna' => 'icon-user',
                            'mheader' => 'icon-align-center',
                            'mjenisbayar' => 'icon-credit-card',
                            'mpelanggan' => 'icon-users',
                            'msupplier' => 'icon-truck',
                            'msatuan' => 'icon-flag',
                            'mjenisobat' => 'icon-tag',
                            'mbarang' => 'icon-package',
                            'komisi' => 'icon-percent',
                            'ujian' => 'icon-file-text',
                            'mstok' => 'icon-bar-chart-2',
                            'stok_kritis' => 'icon-alert-triangle',
                            'stokopname' => 'icon-clipboard',
                            'soharian' => 'icon-printer',
                            'kartustok' => 'icon-repeat',
                            'jurnalkas' => 'icon-book',
                            'orders' => 'icon-send',
                            'tbm' => 'icon-download',
                            'tbmpbf' => 'icon-download',
                            'byrkredit' => 'icon-check-square',
                            'cekdarah' => 'icon-droplet',
                            'shiftkerja' => 'icon-clock',
                            'tpk' => 'icon-shopping-cart',
                            'penjualansebelum' => 'icon-rotate-ccw',
                            'catatan' => 'icon-edit-3',
                            'lpitem' => 'icon-printer',
                            'lpbrgmasuk' => 'icon-printer',
                            'lpkasir' => 'icon-printer',
                            'labapenjualan' => 'icon-printer',
                            'labajenisobat' => 'icon-printer',
                            'lpsupplier' => 'icon-printer',
                            'lppelanggan' => 'icon-printer',
                            'neraca' => 'icon-printer',
                        ];

                        $groupIcons = [
                            'Data Master' => 'icon-box',
                            'Inventory' => 'icon-shopping-bag',
                            'Transaksi' => 'icon-repeat',
                            'Laporan' => 'icon-file-text',
                        ];
                    @endphp

                    @foreach (\App\Models\Admin::PERMISSION_GROUPS as $groupName => $items)
                        @php
                            $visibleItems = collect($items)->filter(fn($label, $column) => $currentAdmin->hasModuleAccess($column));
                        @endphp
                        @if ($visibleItems->isNotEmpty())
                            <li class="nav-group-title">{{ strtoupper($groupName) }}</li>
                            <li class="nav-submenu">
                                <a class="nav-submenu-title" href="javascript:void(0)">
                                    <i class="feather {{ $groupIcons[$groupName] ?? 'icon-folder' }}"></i>
                                    <span>{{ $groupName }}</span>
                                    <i class="nav-submenu-arrow"></i>
                                </a>
                                <ul class="nav-menu menu-collapse">
                                    @foreach ($visibleItems as $column => $label)
                                        <li class="nav-menu-item {{ $activeModule === $column ? 'active' : '' }}">
                                            <a href="{{ isset($columnRoutes[$column]) ? route($columnRoutes[$column] . 'index') : route('inventory.index', ['module' => $column]) }}">
                                                <i class="feather {{ $sidebarIcons[$column] ?? 'icon-circle' }}"></i>
                                                <span class="nav-menu-item-title">{{ $label }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach

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
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @yield('content')
                </div>

                <!-- Footer START -->
                <div class="footer">
                    <div class="footer-content">
                        <p class="mb-0">Copyright &copy; {{ date('Y') }} <a href="#">Apotek E-Commerce</a>. All rights reserved.</p>
                    </div>
                </div>
                <!-- Footer END -->
            </div>
            <!-- Content END -->
        </div>
    </div>
    <!-- ./layout -->

    <form id="keluar-app" action="{{ route('backend.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="{{ asset('newadmin/assets/js/vendors.min.js') }}"></script>
    <script src="{{ asset('newadmin/assets/vendors/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('newadmin/assets/vendors/datatables/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('newadmin/assets/js/app.min.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert/sweetalert2.all.min.js') }}"></script>

    <script>
        $(function() {
            $('#example1').DataTable({
                responsive: true,
                autoWidth: false
            });
        });

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

        function confirmDelete(formId, label) {
            Swal.fire({
                title: 'Konfirmasi Hapus Data?',
                html: "Data yang dihapus <strong>" + label + "</strong> tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, dihapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>
