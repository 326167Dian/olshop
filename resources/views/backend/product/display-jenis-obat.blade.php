@extends('backend.layouts.app')

@section('title', 'Display Jenis Obat')

@section('header', 'Display Jenis Obat')

@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Display Jenis Obat</h3>
                    <a href="{{ route('product.index') }}" class="btn btn-secondary btn-sm">Kembali ke Data Produk</a>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    Obat berlabel <strong>Resep</strong> hanya tampil ke pelanggan yang sudah login. Obat berlabel
                    <strong>Bebas</strong> bisa dilihat semua pengunjung, termasuk yang belum login -- penting untuk
                    landing page iklan supaya tidak menampilkan obat resep ke publik.
                </div>

                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Obat</th>
                            <th>Nama Obat</th>
                            <th>Display Jenis Obat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
  $(function () {
    $('#example1').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('backend.product.displayJenisObatData') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kd_barang', name: 'kd_barang' },
            { data: 'nm_barang', name: 'nm_barang' },
            { data: 'display_select', name: 'display', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
        ]
    });

    // Ubah dropdown langsung menyimpan via AJAX, tabel tidak reload penuh.
    $(document).on('focus', '.select-display-inline', function () {
        $(this).data('previous-value', $(this).val());
    });

    $(document).on('change', '.select-display-inline', function () {
        var $select = $(this);
        var id = $select.data('id');
        var previousValue = $select.data('previous-value');
        var display = $select.val();

        $select.prop('disabled', true);

        $.ajax({
            url: "{{ route('product.updateDisplay', ':id') }}".replace(':id', id),
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                display: display
            },
            success: function (res) {
                $select.data('previous-value', display);
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function (xhr) {
                $select.val(previousValue);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'Tidak dapat menyimpan perubahan.'
                });
            },
            complete: function () {
                $select.prop('disabled', false);
            }
        });
    });
  });
</script>
@endpush
