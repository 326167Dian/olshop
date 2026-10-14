@extends('inventory.layouts.app')

@section('header', 'Pengaturan Fitur')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Pengaturan Fitur</h3>
        </div>
        <div class="card-body">
            <p>
                Jumlah item barang dengan stok &gt; 0 saat ini: <strong>{{ $jumlahStok }}</strong>
                dari batas tier Starter (<strong>{{ $batasStarter }}</strong> item).
            </p>
            @if ($jumlahStok > $batasStarter)
                <div class="alert alert-warning">
                    Jumlah item sudah melebihi batas Starter. Jika tier Premium belum aktif,
                    input Barang Masuk (PBF &amp; non PBF) akan ditolak sampai tier Premium diaktifkan.
                </div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Fitur</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fiturList as $row)
                        <tr>
                            <td class="text-capitalize">{{ $row->fitur }}</td>
                            <td>
                                @if (strtoupper($row->acc) === 'Y')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if (strtoupper($row->acc) !== 'Y')
                                    <form action="{{ route('inventory.fitur.update', $row->id_fitur) }}" method="POST"
                                        onsubmit="return confirm('Aktifkan fitur {{ $row->fitur }}?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-primary">Aktifkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
