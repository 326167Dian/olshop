@extends('inventory.layouts.app')

@section('header', 'Barang Masuk non PBF')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Barang Masuk non PBF</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3" href="{{ route('inventory.trbmasuk.create') }}">Input Manual</a>
            <a class="btn btn-sm btn-warning mb-3" href="{{ route('inventory.trbmasuk.orders.index') }}">Cek Pesanan</a>
            <a class="btn btn-sm btn-info mb-3" href="{{ route('inventory.trbmasuk.evaluasi.index') }}">Evaluasi Barang Masuk</a>
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.trbmasuk.batch-search.form') }}">Cari No. Batch</a>

            <div class="table-responsive">
                <table id="tabel-trbmasuk" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Keterangan</th>
                            <th>Sisa Bayar</th>
                            <th>Cara Bayar</th>
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
        $('#tabel-trbmasuk').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trbmasuk.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'petugas', name: 'petugas' },
                { data: 'kd_trbmasuk', name: 'kd_trbmasuk' },
                { data: 'tgl_trbmasuk', name: 'tgl_trbmasuk', className: 'text-center' },
                { data: 'nm_supplier', name: 'nm_supplier' },
                { data: 'ket_trbmasuk', name: 'ket_trbmasuk' },
                { data: 'sisa_bayar', name: 'sisa_bayar', className: 'text-end' },
                { data: 'carabayar', name: 'carabayar', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush
