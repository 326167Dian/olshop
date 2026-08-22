<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>@yield('title') | {{ $companySetting->nama_perusahaan }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('backend/AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('backend/AdminLTE-3.2.0/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('backend/AdminLTE-3.2.0/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('backend/AdminLTE-3.2.0/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('backend/AdminLTE-3.2.0/plugins/summernote/summernote-bs4.min.css') }}">
    <!-- Datatable -->
    <!-- DataTables -->
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

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
    </style>

</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('backend.dashboard') }}" class="brand-link">
                <img src="{{ asset('storage/' . $companySetting->logo) }}" alt="AdminLTE Logo"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">
                    <marquee direction="left">Selamat datang di {{ $companySetting->nama_perusahaan }}</marquee>
                </span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('backend/AdminLTE-3.2.0/dist/img/user1-128x128.jpg') }}"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ Auth::user()->nama_lengkap }}</a>
                    </div>
                </div>

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                        <li class="nav-header">DASHBOARD</li>
                        <li class="nav-item">
                            <a href="{{ route('backend.dashboard') }}"
                                class="nav-link {{ Route::currentRouteName() == 'backend.dashboard' ? 'active text-white' : 'text-white' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-header">DATA</li>
                        <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['product.index', 'product.edit', 'product.show', 'customer.index', 'customer.show', 'category.index', 'category.create', 'category.edit', 'article.index', 'article.create', 'article.edit', 'article.show']) ? 'menu-open' : '' }}  ">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), ['product.index', 'product.edit', 'product.show', 'customer.index', 'customer.show', 'category.index', 'category.create', 'category.edit', 'article.index', 'article.create', 'article.edit', 'article.show']) ? 'active bg-blue-600 text-white' : 'text-white' }}">
                                <i class="nav-icon fas fa-bars"></i>
                                <p>
                                    Data Master
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('customer.index') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'customer.index' || Route::currentRouteName() == 'customer.show' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-users nav-icon"></i>
                                        <p>Customer</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('category.index') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'category.index' || Route::currentRouteName() == 'category.create' || Route::currentRouteName() == 'category.edit' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-tags nav-icon"></i>
                                        <p>Kategori</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('product.index') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'product.index' || Route::currentRouteName() == 'product.edit' || Route::currentRouteName() == 'product.show' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-database nav-icon"></i>
                                        <p>Produk</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('article.index') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'article.index' || Route::currentRouteName() == 'article.create' || Route::currentRouteName() == 'article.edit' || Route::currentRouteName() == 'article.show' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="far fa-file-alt nav-icon"></i>
                                        <p>Artikel</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['pesanan.proses', 'pesanan.selesai']) ? 'menu-open' : '' }}  ">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), ['pesanan.proses', 'pesanan.selesai']) ? 'active bg-blue-600 text-white' : 'text-white' }}">
                                <i class="nav-icon fas fa-shopping-bag"></i>
                                <p>
                                    Pesanan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('pesanan.proses') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'pesanan.proses' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-sync-alt nav-icon"></i>
                                        <p>Pesanan Proses</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('pesanan.selesai') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'pesanan.selesai' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-check nav-icon"></i>
                                        <p>Pesanan Selesai</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['report.process', 'report.finished']) ? 'menu-open' : '' }}  ">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), ['report.process', 'report.finished']) ? 'active bg-blue-600 text-white' : 'text-white' }}">
                                <i class="nav-icon fas fa-file"></i>
                                <p>
                                    Laporan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('report.process') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'report.process' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-file-alt nav-icon"></i>
                                        <p style="margin: 0; font-size: 15px;">Laporan Pesanan Proses</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('report.finished') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'report.finished' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-file-alt nav-icon"></i>
                                        <p style="margin: 0; font-size: 15px;">Laporan Pesanan Selesai</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['company-setting.index', 'banner.index', 'banner.create', 'banner.edit']) ? 'menu-open' : '' }}  ">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), ['company-setting.index', 'banner.index', 'banner.create', 'banner.edit']) ? 'active bg-blue-600 text-white' : 'text-white' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>
                                    Setting
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('company-setting.index') }}"
                                        class="nav-link pl-4 {{ Route::currentRouteName() == 'company-setting.index' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="fas fa-cogs nav-icon"></i>
                                        <p style="margin: 0; font-size: 15px;">Profil Perusahaan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('banner.index') }}"
                                        class="nav-link pl-4 {{ in_array(Route::currentRouteName(), ['banner.index', 'banner.create', 'banner.edit']) ? 'active bg-blue-600 text-black' : 'text-white' }}">

                                        <i class="fas fa-cogs nav-icon"></i>
                                        <p style="margin: 0; font-size: 15px;">Banner</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{-- <li
                            class="nav-item {{ in_array(Route::currentRouteName(), ['laporan.formpesananselesai', 'laporan.formpesananproses', 'laporan.formpenjualan', 'laporan.formpembelian']) ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ in_array(Route::currentRouteName(), ['laporan.formpesananselesai', 'laporan.formpesananproses', 'laporan.formpenjualan', 'laporan.formpembelian']) ? 'active bg-blue-600 text-white' : 'text-white' }}}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>
                                    Laporan
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('laporan.formpesananselesai') }}"
                                        class="nav-link {{ Route::currentRouteName() == 'laporan.formpesananselesai' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="far  nav-icon"></i>
                                        <p>Pesanan Selesai</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('laporan.formpesananproses') }}"
                                        class="nav-link {{ Route::currentRouteName() == 'laporan.formpesananproses' ? 'active bg-blue-600 text-black' : 'text-white' }}">
                                        <i class="far  nav-icon"></i>
                                        <p>Pesanan Proses</p>
                                    </a>
                                </li>
                            </ul>
                        </li> --}}
                        <li class="nav-header">LOGOUT</li>
                        <li class="nav-item">
                            <a href="pages/gallery.html" class="nav-link" onclick="logoutConfirm(event)">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('header')</h1>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            @yield('content')
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="#">Apotek E-Commerce</a>.</strong>
            All rights reserved.
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <form id="keluar-app" action="{{ route('backend.logout') }}" method="POST" class="dnone">
        @csrf
    </form>

    <!-- jQuery -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- ChartJS -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/chart.js/Chart.min.js') }}"></script>
    <!-- Sparkline -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/sparklines/sparkline.js') }}"></script>
    <!-- JQVMap -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script
        src="{{ asset('backend/AdminLTE-3.2.0/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}">
    </script>
    <!-- Summernote -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}">
    </script>
    <!-- AdminLTE App -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/dist/js/adminlte.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/dist/js/demo.js') }}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{ asset('AdminLTE-3.2.0/dist/js/pages/dashboard.js') }}"></script>
    <!-- Datatable & Plugin -->
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}">
    </script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}">
    </script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}">
    </script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}">
    </script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('backend/AdminLTE-3.2.0/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
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
</body>

</html>