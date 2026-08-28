@extends('backend.layouts.app')
@section('title')
Dashboard
@endsection
@push('css')
<style>
    /* Border di header Toolbar FullCalendar */
    .fc-header-toolbar {
        border-bottom: 1px solid #dee2e6;
        /* Warna border soft */
        padding-bottom: 8px;
        margin-bottom: 10px;
    }

    /* Optional: spasi tombol */
    .fc-header-toolbar button {
        margin: 0 2px;
    }

    .fc-day-today {
        background-color: #007bff !important;
        /* primary */
        color: #fff !important;
    }

    .fc-day-today a {
        color: #fff !important;
    }
</style>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Stat cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $products->count() }}</h3>
                                <span class="text-muted fw-semibold">Total Produk</span>
                            </div>
                            <div class="avatar avatar-circle bg-info-subtle text-info" style="width: 45px; height: 45px; line-height: 45px;">
                                <i class="feather icon-package"></i>
                            </div>
                        </div>
                        <a href="{{ route('product.index') }}" class="d-block mt-3">Lihat detail <i class="feather icon-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $orders->count() }}</h3>
                                <span class="text-muted fw-semibold">Pesanan</span>
                            </div>
                            <div class="avatar avatar-circle bg-success-subtle text-success" style="width: 45px; height: 45px; line-height: 45px;">
                                <i class="feather icon-shopping-bag"></i>
                            </div>
                        </div>
                        <a href="{{ route('pesanan.proses') }}" class="d-block mt-3">Lihat detail <i class="feather icon-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $customers->count() }}</h3>
                                <span class="text-muted fw-semibold">Pelanggan</span>
                            </div>
                            <div class="avatar avatar-circle bg-warning-subtle text-warning" style="width: 45px; height: 45px; line-height: 45px;">
                                <i class="feather icon-users"></i>
                            </div>
                        </div>
                        <a href="{{ route('customer.index') }}" class="d-block mt-3">Lihat detail <i class="feather icon-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h3 class="mb-0">{{ $articles->count() }}</h3>
                                <span class="text-muted fw-semibold">Artikel</span>
                            </div>
                            <div class="avatar avatar-circle bg-danger-subtle text-danger" style="width: 45px; height: 45px; line-height: 45px;">
                                <i class="feather icon-file-text"></i>
                            </div>
                        </div>
                        <a href="{{ route('article.index') }}" class="d-block mt-3">Lihat detail <i class="feather icon-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-primary bg-gradient text-white">
                    <div class="card-header border-0">
                        <h3 class="card-title text-white">
                            <i class="fas fa-user-shield"></i> Selamat Datang, {{ Auth::user()->nama_lengkap }}!
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3" style="font-size: 16px;">
                            Anda berhasil login sebagai <strong>{{ Auth::user()->nama_lengkap }}</strong>. Silakan kelola data,
                            memonitor transaksi, serta mengatur seluruh aktivitas sistem dengan
                            mudah melalui menu navigasi yang telah disediakan.
                            Pastikan Anda rutin melakukan pengecekan data dan pembaruan informasi untuk menjaga kinerja
                            sistem tetap optimal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap',
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next',
                },
            });

            calendar.render();

            // Handle click dropdown untuk ganti view
            document.querySelectorAll('.dropdown-menu [data-view]').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    var viewName = this.getAttribute('data-view');
                    calendar.changeView(viewName);
                });
            });
        });
</script>
@endpush