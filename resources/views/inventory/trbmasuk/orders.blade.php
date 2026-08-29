@extends('inventory.layouts.app')

@section('header', 'Cek Pesanan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cek Pesanan (Terima Barang dari Pesanan)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.trbmasuk.index') }}">Kembali</a>

            <div class="table-responsive">
                <table id="tabel-orders-trbmasuk" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Jenis Pesanan</th>
                            <th>Total</th>
                            <th>DP</th>
                            <th>Sisa</th>
                            <th>Status</th>
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
        $('#tabel-orders-trbmasuk').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trbmasuk.orders.data') }}",
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
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
            ]
        });
    });
</script>
@endpush
