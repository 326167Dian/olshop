@extends('inventory.layouts.app')

@section('header', 'Pesan Barang')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pesan Barang</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.orders.create') }}">Tambah</a>

            <div class="table-responsive">
                <table id="tabel-orders" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Jenis Pesanan</th>
                            <th>Sub Total</th>
                            <th>Diskon</th>
                            <th>Total Bayar</th>
                            <th>Belum Diproses</th>
                            <th>Telah Diproses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function() {
        $('#tabel-orders').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.orders.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'petugas', name: 'petugas' },
                { data: 'kd_trbmasuk', name: 'kd_trbmasuk' },
                { data: 'tgl_trbmasuk', name: 'tgl_trbmasuk', className: 'text-center' },
                { data: 'nm_supplier', name: 'nm_supplier' },
                { data: 'ket_trbmasuk', name: 'ket_trbmasuk' },
                { data: 'ttl_trbmasuk', name: 'ttl_trbmasuk', className: 'text-end' },
                { data: 'dp_bayar', name: 'dp_bayar', className: 'text-end' },
                { data: 'sisa_bayar', name: 'sisa_bayar', className: 'text-end' },
                { data: 'belum_diproses', name: 'belum_diproses', orderable: false, searchable: false, className: 'text-center' },
                { data: 'telah_diproses', name: 'telah_diproses', orderable: false, searchable: false, className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush
