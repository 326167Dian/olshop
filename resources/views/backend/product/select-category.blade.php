@extends('backend.layouts.app')

@section('title', 'Kategori Produk')

@section('header', 'Halaman Atur Kategori Produk')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Atur Kategori Produk</h3>
            </div>

            <div class="card-body">
                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Satuan</th>
                            <th>Gambar</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    var categories = @json($categories);

    function buildCategoryOptions(selectedId) {
        var html = '<option value="">- Pilih Kategori -</option>';
        categories.forEach(function (cat) {
            var selected = (String(cat.id) === String(selectedId)) ? 'selected' : '';
            html += '<option value="' + cat.id + '" ' + selected + '>' + cat.name + '</option>';
        });
        return html;
    }

    $(function () {
        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('backend.product.selectCategoryData') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kd_barang', name: 'kd_barang' },
                { data: 'nm_barang', name: 'nm_barang' },
                { data: 'sat_barang', name: 'sat_barang' },
                { data: 'gambar', name: 'gambar', orderable: false, searchable: false },
                {
                    data: 'category_id',
                    name: 'category_id',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return '<select class="form-select form-select-sm category-select" data-id="' + row.id_barang + '">'
                            + buildCategoryOptions(data) + '</select>';
                    }
                },
            ]
        });
    });

    $(document).on('change', '.category-select', function () {
        var $select = $(this);
        var productId = $select.data('id');
        var categoryId = $select.val();
        var url = "{{ route('product.updateCategory', ['product' => '__ID__']) }}".replace('__ID__', productId);

        $select.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                category_id: categoryId
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan',
                    text: res.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Tidak dapat memperbarui kategori produk.'
                });
            },
            complete: function () {
                $select.prop('disabled', false);
            }
        });
    });
</script>
@endpush
