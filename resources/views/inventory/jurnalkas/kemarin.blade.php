@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">JURNAL PENGELUARAN KEMARIN</h3>
        </div>
        <div class="card-body">
            @include('inventory.jurnalkas.partials.nav')
            <br><br>

            <div class="table-responsive">
                <table id="tabel-kemarin" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Petugas</th>
                            <th>Jenis Transaksi</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->tanggal->format('Y-m-d') }}</td>
                                <td>{{ $r->ket }}</td>
                                <td>{{ $r->petugas }}</td>
                                <td>{{ optional($r->jenis)->nm_jurnal }}</td>
                                <td class="text-right">Rp. {{ number_format((float) $r->debit, 0, ',', '.') }}</td>
                                <td class="text-right">Rp. {{ number_format((float) $r->kredit, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('inventory.jurnalkas.edit', $r) }}" title="EDIT" class="btn btn-warning btn-xs">EDIT</a>
                                    <form action="{{ route('inventory.jurnalkas.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurnal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="HAPUS" class="btn btn-danger btn-xs">HAPUS</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a class="btn btn-danger" href="{{ route('inventory.jurnalkas.index') }}">KEMBALI</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-kemarin').DataTable();
    });
</script>
@endpush
