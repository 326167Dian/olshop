@extends('inventory.layouts.app')

@section('header', 'PIO')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data PIO (Pelayanan Informasi Obat)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. PIO</th>
                        <th>Tanggal</th>
                        <th>Nama Pasien</th>
                        <th>Nama Penanya</th>
                        <th>Pertanyaan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pio as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->no_pio ?? '-' }}</td>
                            <td>{{ $row->tanggal?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $row->pelanggan->nm_pelanggan ?? '-' }}</td>
                            <td>{{ $row->nama_penanya ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($row->uraian_pertanyaan, 50) }}</td>
                            <td>
                                @if ($row->jawaban)
                                    <span class="badge bg-success">Terjawab</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventory.pio.show', $row->id_pio) }}" target="_blank"
                                    class="btn btn-info btn-sm">Lihat</a>
                                <a href="{{ route('inventory.pio.edit', $row->id_pio) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('inventory.pio.destroy', $row->id_pio) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id_pio }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_pio }}', 'data PIO ini')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
