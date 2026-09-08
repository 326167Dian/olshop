@extends('backend.layouts.app')

@section('title', 'Customer')

@section('header', 'Halaman Data Customer')

@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Customer</h3>
            </div>

            <div class="card-body">
                <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Referal</th>
                            <th>Komisi</th>
                            <th>Waktu</th>
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
    $(function () {
        $('#example1').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: "{{ route('backend.customer.data') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'referal_nama', name: 'referal_nama' },
                { data: 'komisi', name: 'komisi', orderable: false, searchable: false },
                { data: 'waktu_komisi', name: 'waktu_komisi', orderable: false, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ]
        });
    });

    $(document).on('change', '.komisi-select', function () {
        var $select = $(this);
        var customerId = $select.data('id');
        var status = $select.val();
        var url = "{{ route('customer.updateKomisi', ['customer' => '__ID__']) }}".replace('__ID__', customerId);

        $select.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                komisi_status: status
            },
            success: function (res) {
                $('.waktu-komisi-display[data-id="' + customerId + '"]').text(res.waktu_komisi_lunas || '-');

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
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: (xhr.responseJSON && xhr.responseJSON.message) || 'Tidak dapat memperbarui status komisi.'
                });
            },
            complete: function () {
                $select.prop('disabled', false);
            }
        });
    });

    function deleteData(url, element) {
        var konfdelete = $(element).data("konf-delete");
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: "Data customer <strong>" + konfdelete + "</strong> tidak dapat dikembalikan!",
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
                    type: 'POST', // tetap POST karena kita spoof DELETE
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'DELETE'
                    },
                    success: function (response) {
                        $('#example1').DataTable().ajax.reload();

                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dihapus.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: (xhr.responseJSON && xhr.responseJSON.message) || 'Tidak dapat menghapus data.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush