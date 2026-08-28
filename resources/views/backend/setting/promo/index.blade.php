@extends('backend.layouts.app')

@section('title', 'Promo')
@section('header', 'Halaman Data Promo')
@push('css')

@endpush
@section('content')
<section class="content">
    <div class="container-fluid">

        @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 1500,
                showConfirmButton: false
            });
        </script>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Promo</h3>
            </div>

            <div class="card-body">
                <a href="{{ route('promo.create') }}" class="btn btn-sm btn-primary mb-3">
                    <i class="fas fa-plus"></i> Tambah Promo
                </a>
                <table class="table table-auto table-sm table-bordered table-striped w-100" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Promo</th>
                            <th>Tanggal Awal</th>
                            <th>Tanggal Akhir</th>
                            <th>Nilai Diskon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promos as $index)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $index->nama_promo }}</td>
                            <td>{{ \Carbon\Carbon::parse($index->tanggal_awal)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($index->tanggal_akhir)->format('d-m-Y') }}</td>
                            <td>{{ rtrim(rtrim(number_format($index->nilai_diskon, 2, ',', '.'), '0'), ',') }}%</td>
                            <td>{{ $index->status }}</td>
                            <td>
                                <div class="dropdown position-relative d-inline-block">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                                        <a href="{{ route('promo.edit', $index->id) }}"
                                            class="btn btn-warning btn-sm w-100 mb-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('promo.destroy', $index->id) }}" method="POST"
                                            class="d-inline" id="delete-form-{{ $index->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete({{ $index->id }})"
                                                class="btn btn-danger btn-sm w-100">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
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
            responsive: true,
            autoWidth: false
        });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush
