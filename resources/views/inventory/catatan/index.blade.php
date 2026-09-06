@extends('inventory.layouts.app')

@section('header', 'Catatan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CATATAN HARIAN</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-success btn-flat" href="{{ route('inventory.catatan.create') }}">TAMBAH</a>
            <br><br>

            <div class="table-responsive">
                <table id="tabel-catatan" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Petugas</th>
                            <th>Catatan Singkat</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->tgl->format('Y-m-d') }}</td>
                                <td>{{ $r->shift }}</td>
                                <td>{{ $r->petugas }}</td>
                                <td title="{{ $r->preview }}" data-toggle="tooltip" data-placement="top">{{ $r->preview }}</td>
                                <td>
                                    <a href="{{ route('inventory.catatan.edit', $r) }}" title="EDIT" class="btn btn-warning btn-xs">EDIT</a>
                                    <a href="{{ route('inventory.catatan.show', $r) }}" title="TAMPIL" class="btn btn-primary btn-xs">TAMPIL</a>
                                    <form action="{{ route('inventory.catatan.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus catatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="HAPUS" class="btn btn-danger btn-xs">HAPUS</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada catatan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-catatan').DataTable();
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
</script>
@endpush
