@extends('backend.layouts.app')

@section('title', 'Banner')
@section('header', 'Halaman Data Banner')
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
                <h3 class="card-title">Data Banner</h3>
            </div>

            <div class="card-body">
                <a href="{{ route('banner.create') }}" class="btn btn-sm btn-primary mb-3">
                    <i class="fas fa-plus"></i> Tambah Banner
                </a>
                <table class="table table-auto table-sm table-bordered table-striped w-100" id="example1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($banners as $index)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $index->judul }}</td>
                            <td>{{ $index->status }}</td>
                            <td>{{ $index->deskripsi }}</td>
                            <td><img src="{{ asset('storage/' . $index->foto) }}" width="100"></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('banner.edit', $index->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('banner.destroy', $index->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Hapus banner {{ $index->judul }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
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
</script>
@endpush