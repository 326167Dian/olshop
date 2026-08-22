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
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $products->count() }}</h3>

                        <p>Total produk</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pricetag"></i>
                    </div>
                    <a href="{{ route('product.index') }}" class="small-box-footer">Lihat detail <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $orders->count() }}</h3>

                        <p>Pesanan</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="{{ route('pesanan.proses') }} " class="small-box-footer">Lihat detail <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $customers->count() }}</h3>

                        <p>Pelanggan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-friends"></i>

                    </div>
                    <a href="{{ route('customer.index') }}" class="small-box-footer">Lihat detail <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $articles->count() }}</h3>

                        <p>Artikel</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="{{ route('article.index') }}" class="small-box-footer">Lihat detail <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12 connectedSortable">
                <!-- Custom tabs (Charts with tabs)-->
                <div class="card bg-gradient-primary">
                    <div class="card-header border-0">
                        <h3 class="card-title">
                            <i class="fas fa-user-shield"></i> Selamat Datang, admin!
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3" style="font-size: 16px;">
                            Anda berhasil login sebagai <strong>admin</strong>. Silakan kelola data,
                            memonitor transaksi, serta mengatur seluruh aktivitas sistem dengan
                            mudah melalui menu navigasi yang telah disediakan.
                            Pastikan Anda rutin melakukan pengecekan data dan pembaruan informasi untuk menjaga kinerja
                            sistem tetap optimal.
                        </p>
                    </div>
                </div>
                <!-- /.card -->

            </section>
            <!-- /.Left col -->
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