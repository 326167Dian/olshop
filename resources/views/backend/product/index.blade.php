@extends('backend.layouts.app')

@section('title', 'Produk')

@section('header', 'Halaman Data Produk')

@section('content')

<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Data Produk</h3>
                    <p>Ubah Status Produk : <button id="btnSubmit" class="btn btn-primary btn-md">Submit</button></p>
                </div>
            </div>




            <div class="card-body">
                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th><input type="checkbox" id="checkAll"> </th>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Status</th>
                            <th>Kategori</th>
                            <th>Jenis Obat</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Diskon</th>
                            <th>Gambar Produk</th>
                            <th>Aksi</th>
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
    let table = $('#example1').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('backend.product.data') }}",
        displayStart: {{ request('start', 0) }},
        pageLength: {{ request('length', 10) }},
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'kd_barang', name: 'kd_barang' },
            { 
                data: 'nm_barang', 
                name: 'nm_barang',
                render: function (data, type, row) {
                    if (!data) return '-';
                    const maxLength = 35; // ubah sesuai kebutuhan
                    const text = data.length > maxLength ? data.substring(0, maxLength) + '...' : data;
                    // tambahkan title agar saat hover muncul teks lengkap
                    return `<span title="${data}">${text}</span>`;
                }
            },
            { data: 'status', name: 'status' },
            { data: 'kategori', name: 'kategori' },
            { data: 'jenisobat', name: 'jenisobat' },
            { data: 'hrgsat_barang', name: 'hrgsat_barang', render: $.fn.dataTable.render.number('.', ',', 0) },
            { data: 'hrgjual_barang2', name: 'hrgjual_barang2', render: $.fn.dataTable.render.number('.', ',', 0) },
            { 
                data: 'diskon', 
                name: 'diskon',
                render: function(data) {
                    return data + ' %';
                }
            },
            { data: 'gambar_status', name: 'gambar_status', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
        ]
    });

    // Klik tombol check all
    $(document).on('change', '#checkAll', function () {
        $('.checkItem').prop('checked', $(this).prop('checked'));
    });

    // Klik tombol submit
    $('#btnSubmit').on('click', function () {
        let allIds = [];
        let activeIds = [];

        // Ambil semua ID produk (checkbox value)
        $('.checkItem').each(function () {
            allIds.push($(this).val());
            if ($(this).is(':checked')) {
                activeIds.push($(this).val());
            }
        });

        $.ajax({
            url: '/backend/product/multiple-update-status',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                all_ids: allIds,       // semua produk di tabel
                active_ids: activeIds  // hanya yang dicentang
            },
            success: function (res) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: res.message, // ambil dari response()->json(['message' => '...'])
                showConfirmButton: true,
                timer: 1500
            });
            table.ajax.reload(null, false); 
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: xhr.responseJSON.message || 'Terjadi kesalahan saat update!',
            });
        }
        });
    });

});

</script>
@endpush