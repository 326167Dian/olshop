@extends('inventory.layouts.app')

@section('header', 'Bundle/Paket Produk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Paket Produk (Bundling)</h3>
            <div class="card-header-action">
                <a class="btn btn-success" href="{{ route('inventory.bundle.create') }}"><i class="fa fa-plus-circle"></i> TAMBAH</a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-bundle" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Nama Paket Produk</th>
                        <th class="text-center">Isi Paket</th>
                        <th class="text-center">Harga Jual</th>
                        <th class="text-center">Sisa Kuota</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bundles as $i => $b)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $b->nm_bundle }}</td>
                            <td>
                                <ul class="mb-0 ps-3">
                                    @foreach ($b->detail as $d)
                                        <li>{{ $d->nm_barang }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="text-right">Rp. {{ number_format($b->hrgjual_bundle, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $b->qty_bundle }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <a href="{{ route('inventory.bundle.edit', $b) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="{{ route('inventory.bundle.show', $b) }}" class="btn btn-sm btn-info">Detail</a>
                                    <form action="{{ route('inventory.bundle.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus paket produk {{ $b->nm_bundle }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada paket produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-bundle').DataTable({ order: [] });
    });
</script>
@endpush
