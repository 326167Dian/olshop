@extends('backend.layouts.app')

@section('title', 'Pesanan Selesai')

@section('header', 'Halaman Data Pesanan Selesai')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Pesanan</h3>
            </div>

            <div class="card-body">
                <table id="tablePesanan" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Order</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pelanggan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection

@push('css')
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tablePesanan').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '{{ route("pesanan.selesai.data") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kode_pesanan', name: 'kode_pesanan' },
                { data: 'created_at', name: 'created_at' },
                { data: 'total_harga', name: 'total_harga' },
                { data: 'status', name: 'status' },
                { data: 'pelanggan', name: 'pelanggan', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush