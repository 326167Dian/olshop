@extends('inventory.layouts.app')

@section('header', 'Barang Masuk dari PBF')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Barang Masuk dari PBF</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-sm btn-success" href="{{ route('inventory.trbmasukpbf.create') }}">Input Manual</a>
                <a class="btn btn-sm btn-warning" href="{{ route('inventory.trbmasukpbf.orders.index') }}">Cek Pesanan</a>
                <a class="btn btn-sm btn-info" href="{{ route('inventory.trbmasukpbf.evaluasi.index') }}">Evaluasi Barang Masuk</a>
                <a class="btn btn-sm btn-secondary" href="{{ route('inventory.trbmasukpbf.batch-search.form') }}">Cari No. Batch</a>
                <a class="btn btn-sm btn-danger" href="{{ route('inventory.trbmasukpbf.jatuh-tempo.form') }}">Filter Jatuh Tempo</a>
                <a class="btn btn-sm btn-primary" href="{{ route('inventory.trbmasukpbf.pembelian.form') }}">Filter Pembelian</a>
                <a class="btn btn-sm btn-success" href="{{ route('inventory.trbmasukpbf.distributor.form') }}">Filter Distributor</a>
            </div>

            @if (Auth::guard('admin')->user()->isPemilik())
                <form method="POST" action="{{ route('inventory.trbmasukpbf.mark-lunas') }}" id="formPelunasan" class="mb-2">
                    @csrf
                    <div id="checkboxContainer"></div>
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Tandai transaksi terpilih sebagai LUNAS?')">Submit Pelunasan</button>
                </form>
            @endif

            <div class="table-responsive">
                <table id="tabel-trbmasukpbf" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th></th>
                            <th>No</th>
                            <th>Petugas</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>No Faktur</th>
                            <th>Sisa Bayar</th>
                            <th>Jatuh Tempo</th>
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
        var table = $('#tabel-trbmasukpbf').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trbmasukpbf.data') }}",
            order: [[1, 'desc']],
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'petugas', name: 'petugas' },
                { data: 'kd_trbmasuk', name: 'kd_trbmasuk' },
                { data: 'tgl_trbmasuk', name: 'tgl_trbmasuk', className: 'text-center' },
                { data: 'nm_supplier', name: 'nm_supplier' },
                { data: 'ket_trbmasuk', name: 'ket_trbmasuk' },
                { data: 'sisa_bayar', name: 'sisa_bayar', className: 'text-end' },
                { data: 'jatuhtempo', name: 'jatuhtempo', className: 'text-center' },
                { data: 'carabayar', name: 'carabayar', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });

        $(document).on('change', '.checkItem', function() {
            var container = document.getElementById('checkboxContainer');
            if (!container) return;
            container.innerHTML = '';
            $('.checkItem:checked').each(function() {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'kode[]';
                input.value = $(this).val();
                container.appendChild(input);
            });
        });
    });
</script>
@endpush
