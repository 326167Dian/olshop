@extends('inventory.layouts.app')

@section('header', 'Edit/Retur/Hapus Penjualan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit/Retur/Hapus Penjualan</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar transaksi penjualan kemarin s.d. 360 hari lalu (pelengkap dari dashboard
                Penjualan/Kasir yang hanya menampilkan transaksi hari ini). Retur dilakukan lewat halaman Edit
                (kurangi Qty atau hapus baris item untuk merefleksikan barang yang dikembalikan pelanggan).</p>

            <div class="table-responsive">
                <table id="tabel-penjualansebelum" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Kode Order</th>
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
        $('#tabel-penjualansebelum').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.penjualansebelum.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_trkasir', name: 'kd_trkasir' },
                { data: 'tgl_trkasir', name: 'tgl_trkasir', className: 'text-center' },
                { data: 'nm_pelanggan', name: 'nm_pelanggan' },
                { data: 'kodetx', name: 'kodetx' },
                { data: 'nm_carabayar', name: 'nm_carabayar', className: 'text-center' },
                { data: 'ttl_trkasir', name: 'ttl_trkasir', className: 'text-end' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush
