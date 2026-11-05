@extends('inventory.layouts.app')

@section('header', 'Edit/Retur/Hapus Pembelian')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit/Retur/Hapus Pembelian</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar seluruh transaksi Barang Masuk (non-PBF maupun PBF). Retur barang dilakukan lewat
                halaman Edit (kurangi Qty atau hapus baris item untuk merefleksikan barang yang dikembalikan ke supplier).</p>

            <div class="table-responsive">
                <table id="tabel-byrkredit" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Jenis</th>
                            <th>Petugas</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>No Faktur</th>
                            <th>Sisa Bayar</th>
                            <th>Status Pembayaran</th>
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
        var table = $('#tabel-byrkredit').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: "{{ route('inventory.byrkredit.data') }}",
            order: [],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kd_trbmasuk', name: 'kd_trbmasuk' },
                { data: 'jenis', name: 'jenis', className: 'text-center' },
                { data: 'petugas', name: 'petugas' },
                { data: 'tgl_trbmasuk', name: 'tgl_trbmasuk', className: 'text-center' },
                { data: 'nm_supplier', name: 'nm_supplier' },
                { data: 'ket_trbmasuk', name: 'ket_trbmasuk' },
                { data: 'sisa_bayar', name: 'sisa_bayar', className: 'text-end' },
                { data: 'carabayar', name: 'carabayar', className: 'text-center' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });

        $(document).on('click', '.btn-hapus-transaksi', function() {
            if (!confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')) return;

            // Tombol dimatikan selama request berjalan -- mencegah klik ganda (mis. karena
            // koneksi lambat/tidak sabar) mengirim DELETE dua kali untuk baris yang sama;
            // percobaan kedua akan 404 (baris sudah terhapus di percobaan pertama) dan
            // menampilkan "Gagal menghapus" padahal penghapusannya sendiri sudah berhasil.
            var $btn = $(this).prop('disabled', true);
            var id = $btn.data('id');
            var jenis = $btn.data('jenis');
            var url = jenis === 'pbf'
                ? "{{ url('inventory/trbmasukpbf') }}/" + id
                : "{{ url('inventory/byrkredit') }}/" + id;

            fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                })
                .then(function(res) {
                    if (!res.ok) throw new Error('Gagal menghapus');
                    table.ajax.reload(null, false);
                })
                .catch(function() {
                    alert('Gagal menghapus transaksi.');
                    $btn.prop('disabled', false);
                });
        });
    });
</script>
@endpush
