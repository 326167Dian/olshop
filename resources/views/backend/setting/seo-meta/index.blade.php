@extends('backend.layouts.app')

@section('title', 'SEO')
@section('header', 'Pengaturan SEO')

@section('content')
<section class="content">
    <div class="container-fluid">

        @if (session('success'))
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
                <h3 class="card-title">Override Judul/Deskripsi Halaman</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Judul, deskripsi, dan gambar halaman produk/kategori/artikel otomatis dibuat dari datanya
                    sendiri. Isi form di sini HANYA kalau mau menimpa nilai otomatis itu untuk satu halaman
                    tertentu -- kosongkan/hapus override untuk kembali memakai nilai otomatis.
                </p>

                <ul class="nav nav-tabs mb-3">
                    @foreach ($types as $slug => [$class, $label, $kolom])
                        <li class="nav-item">
                            <a class="nav-link {{ $type === $slug ? 'active' : '' }}"
                                href="{{ route('seo-meta.index', ['type' => $slug]) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <form method="GET" action="{{ route('seo-meta.index') }}" class="row g-2 mb-3">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <div class="col-auto">
                        <input type="text" name="q" value="{{ $keyword }}" class="form-control form-control-sm"
                            placeholder="Cari {{ strtolower($label) }}...">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-secondary">Cari</button>
                    </div>
                </form>

                <table class="table table-auto table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Status SEO</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php $override = $overrides->get($row->getKey()); @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                                <td>{{ $row->{$types[$type][2]} }}</td>
                                <td>
                                    @if ($override)
                                        <span class="badge bg-success">Ada override</span>
                                        @if ($override->noindex)
                                            <span class="badge bg-warning">noindex</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Otomatis</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('seo-meta.edit', ['type' => $type, 'id' => $row->getKey()]) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $rows->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
