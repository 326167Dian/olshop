@extends('inventory.layouts.app')

@section('header', 'Item Penjualan Terhapus')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Item Penjualan Terhapus</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Catatan seluruh baris item yang pernah dihapus dari transaksi mana pun (baik lewat
                hapus satu baris keranjang maupun hapus total transaksi). Murni catatan -- tidak ada aksi restore di
                sini, untuk mengembalikan transaksi yang dihapus total gunakan Undo Transaksi Terhapus.</p>

            <div class="table-responsive">
                <table id="tabel-item-terhapus" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu Dihapus</th>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Disc</th>
                            <th>Total</th>
                            <th>Petugas</th>
                            <th>Dihapus Oleh</th>
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
        $('#tabel-item-terhapus').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.trkasir.item-terhapus.data') }}",
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'waktu_hapus', name: 'waktu_hapus' },
                { data: 'kd_trkasir', name: 'kd_trkasir' },
                { data: 'tgl_trkasir', name: 'tgl_trkasir', className: 'text-center' },
                { data: 'nm_pelanggan', name: 'nm_pelanggan' },
                { data: 'kd_barang', name: 'kd_barang' },
                { data: 'nmbrg_dtrkasir', name: 'nmbrg_dtrkasir' },
                { data: 'qty_dtrkasir', name: 'qty_dtrkasir', className: 'text-center' },
                { data: 'sat_dtrkasir', name: 'sat_dtrkasir', className: 'text-center' },
                { data: 'hrgjual_dtrkasir', name: 'hrgjual_dtrkasir', className: 'text-end' },
                { data: 'disc', name: 'disc', className: 'text-center' },
                { data: 'hrgttl_dtrkasir', name: 'hrgttl_dtrkasir', className: 'text-end' },
                { data: 'nama_admin', name: 'nama_admin' },
                { data: 'nama_admin_hapus', name: 'nama_admin_hapus' },
            ]
        });
    });
</script>
@endpush
