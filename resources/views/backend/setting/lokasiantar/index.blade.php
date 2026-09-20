@extends('backend.layouts.app')

@section('title', 'Lokasi Antar')
@section('header', 'Halaman Data Lokasi Antar')
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
                <h3 class="card-title">Data Lokasi Antar</h3>
                <p class="text-muted mb-0">Daftar kelurahan yang dilayani pengantaran obat beserta biaya antarnya. Daftar ini akan muncul sebagai pilihan wilayah saat pelanggan checkout dengan metode "Pengantaran".</p>
            </div>

            <div class="card-body">
                <a href="{{ route('lokasi-antar.create') }}" class="btn btn-sm btn-primary mb-3">
                    <i class="fas fa-plus"></i> Tambah Lokasi
                </a>
                <table class="table table-auto table-sm table-bordered table-striped w-100" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelurahan</th>
                            <th>Biaya Antar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lokasiAntar as $index)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $index->nama_kelurahan }}</td>
                            <td>Rp {{ number_format($index->biaya_antar, 0, ',', '.') }}</td>
                            <td>
                                <div class="dropdown position-relative d-inline-block">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu center-below p-2 shadow" style="min-width: 140px;">
                                        <a href="{{ route('lokasi-antar.edit', $index->id) }}"
                                            class="btn btn-warning btn-sm w-100 mb-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('lokasi-antar.destroy', $index->id) }}" method="POST"
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
