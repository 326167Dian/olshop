@extends('backend.layouts.app')

@section('title', 'Pesanan Proses')

@section('header', 'Halaman Data Pesanan Proses')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <strong>{{ session('error') }}</strong>
        </div>
    @endif
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
                                <th>Tipe Layanan</th>
                                <th>Tipe Pembayaran</th>
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
                ajax: '{{ route('pesanan.proses.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode_pesanan',
                        name: 'kode_pesanan'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'total_harga',
                        name: 'total_harga'
                    },
                    {
                        data: 'tipe_layanan',
                        name: 'tipe_layanan'
                    },
                    {
                        data: 'tipe_pembayaran',
                        name: 'tipe_pembayaran'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'pelanggan',
                        name: 'pelanggan',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
    <script>
        function batalkanPesanan(url) {
            Swal.fire({
                title: 'Batalkan Pesanan?',
                text: "Pesanan akan dibatalkan dan stok produk akan dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.status) {
                                Swal.fire('Berhasil!', res.message, 'success');
                                $('#tablePesanan').DataTable().ajax.reload(); // ✅ diperbaiki ID-nya
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Terjadi kesalahan saat membatalkan pesanan.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
