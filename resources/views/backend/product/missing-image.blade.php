@extends('backend.layouts.app')

@section('title', 'Gambar Tidak Lengkap')

@section('header', 'Gambar Tidak Lengkap')

@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Gambar Tidak Lengkap</h3>
                    <a href="{{ route('product.index') }}" class="btn btn-secondary btn-sm">Kembali ke Data Produk</a>
                </div>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    Total <strong class="total-belum-lengkap">{{ $totalCount }}</strong> barang dengan stok &gt; 0 yang gambar produknya belum lengkap
                    (belum diunggah atau filenya tidak ditemukan).
                </div>

                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Stok</th>
                            <th>Harga Jual</th>
                            <th>Gambar Produk</th>
                            <th>Upload</th>
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
    var table = $('#example1').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('backend.product.missingImageData') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'kd_barang', name: 'kd_barang' },
            { data: 'nm_barang', name: 'nm_barang' },
            { data: 'stok_barang', name: 'stok_barang' },
            { data: 'hrgjual_barang2', name: 'hrgjual_barang2' },
            { data: 'gambar_status', name: 'gambar_status', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
        ]
    });

    // Upload gambar inline: pilih file langsung mengunggah (tanpa tombol simpan
    // terpisah), baris otomatis hilang dari tabel begitu berhasil (produk itu
    // sudah tidak lagi termasuk "belum lengkap").
    $(document).on('change', '.input-upload-gambar', function () {
        var $input = $(this);
        var id = $input.data('id');
        var file = this.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('_method', 'PUT');
        formData.append('gambar_produk', file);

        $input.prop('disabled', true);

        $.ajax({
            url: "{{ route('product.updateImage', ':id') }}".replace(':id', id),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });

                table.ajax.reload(null, false);

                var $total = $('.total-belum-lengkap');
                var sisa = Math.max(0, parseInt($total.text(), 10) - 1);
                $total.text(sisa);
            },
            error: function (xhr) {
                $input.prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'Tidak dapat mengunggah gambar.'
                });
            }
        });
    });
  });
</script>
@endpush
