@extends('inventory.layouts.app')

@section('header', 'Penjualan/Kasir')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaksi Penjualan Hari Ini</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.trkasir.create') }}">(F4) Tambah Penjualan</a>

            <div class="table-responsive">
                <table id="tabel-trkasir" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Shift</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Petugas</th>
                            <th>Cara Bayar</th>
                            <th>Total</th>
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
        $('#tabel-trkasir').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trkasir.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_trkasir', name: 'kd_trkasir' },
                { data: 'shift_label', name: 'shift_label', className: 'text-center' },
                { data: 'tgl_trkasir', name: 'tgl_trkasir', className: 'text-center' },
                { data: 'nm_pelanggan', name: 'nm_pelanggan' },
                { data: 'petugas', name: 'petugas' },
                { data: 'nm_carabayar', name: 'nm_carabayar', className: 'text-center' },
                { data: 'ttl_trkasir', name: 'ttl_trkasir', className: 'text-end' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'F4') {
            event.preventDefault();
            window.location = "{{ route('inventory.trkasir.create') }}";
        }
    });
</script>
@endpush
