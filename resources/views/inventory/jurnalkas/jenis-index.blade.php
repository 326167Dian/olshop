@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">JENIS TRANSAKSI JURNAL</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-warning btn-flat" href="{{ route('inventory.jurnalkas.jenis.create') }}">Tambah Jenis Transaksi</a>
            <br><br>
            @include('inventory.jurnalkas.partials.nav')
            <br><br>

            <div class="table-responsive">
                <table id="tabel-jenis" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Transaksi</th>
                            <th>Nama Transaksi</th>
                            <th>Tipe Transaksi</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->idjenis }}</td>
                                <td>{{ $r->nm_jurnal }}</td>
                                <td class="text-center">{{ $r->tipe == 1 ? 'KELUAR' : 'MASUK' }}</td>
                                <td>
                                    <a href="{{ route('inventory.jurnalkas.jenis.edit', $r) }}" title="UBAH" class="btn btn-warning btn-xs">UBAH</a>
                                    <form action="{{ route('inventory.jurnalkas.jenis.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jenis transaksi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="HAPUS" class="btn btn-danger btn-xs">HAPUS</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada jenis transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="history.back()">KEMBALI</button>
                <a class="btn btn-success btn-flat" href="{{ route('inventory.jurnalkas.index') }}">HOME</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-jenis').DataTable();
    });
</script>
@endpush
