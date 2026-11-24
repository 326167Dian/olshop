@extends('inventory.layouts.app')

@section('header', 'Riwayat Sinkronisasi Stok')

@section('content')
    <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.trkasir.index') }}">Kembali</a>

    @forelse ($grouped as $kodeSinkron => $rows)
        @php $first = $rows->first(); @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    {{ $kodeSinkron }} -- {{ $first->waktu->format('Y-m-d H:i:s') }}
                    oleh {{ $adminNames[$first->id_admin] ?? ('id_admin ' . $first->id_admin) }}
                    ({{ $rows->count() }} barang berubah)
                </h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-condensed table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Stok Sebelum</th>
                            <th class="text-center">Stok Sesudah</th>
                            <th class="text-center">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td class="text-center">{{ $r->kd_barang }}</td>
                                <td>{{ $r->nm_barang }}</td>
                                <td class="text-center">{{ $r->stok_sebelum }}</td>
                                <td class="text-center">{{ $r->stok_sesudah }}</td>
                                <td class="text-center {{ $r->selisih < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $r->selisih > 0 ? '+' : '' }}{{ $r->selisih }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Belum ada riwayat sinkronisasi.</div>
    @endforelse
@endsection
