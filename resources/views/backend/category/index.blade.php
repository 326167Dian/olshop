@extends('backend.layouts.app')

@section('title', 'Kategori')

@section('header', 'Halaman Data Kategori')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Kategori</h3>
            </div>

            <div class="card-body">
                <a href="{{ route('category.create') }}" class="btn btn-sm btn-primary mb-3">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </a>
                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
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

@push('css')
@endpush

@push('scripts')
<script>
    var table;

    $(function () {
        var table = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('backend.category.data') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });

    function deleteData(url, element) {
        var konfdelete = $(element).data("konf-delete");
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: "Data yang dihapus <strong>" + konfdelete + "</strong> tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type:   'POST', // tetap POST karena kita spoof DELETE
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'DELETE' // spoof DELETE method
                    },
                    success: function (response) {
                        $('#example1').DataTable().ajax.reload();

                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Data berhasil dihapus.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Tidak dapat menghapus data.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

</script>
@endpush