@extends('inventory.layouts.app')

@section('header', 'MESO')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data MESO (Monitoring Efek Samping Obat)</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Laporan</th>
                        <th>Nama Pasien</th>
                        <th>Penyakit Utama</th>
                        <th>Manifestasi ESO</th>
                        <th>Kesudahan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($meso as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->tanggal_laporan?->format('d-m-Y') ?? '-' }}</td>
                            <td>{{ $row->pelanggan->nm_pelanggan ?? $row->nama_singkat ?? '-' }}</td>
                            <td>{{ $row->penyakit_utama ?? '-' }}</td>
                            <td>{{ $row->manifestasi_eso ?? '-' }}</td>
                            <td>{{ $row->kesudahan_eso ?? '-' }}</td>
                            <td>
                                <a href="{{ route('inventory.meso.show', $row->id_meso) }}" target="_blank"
                                    class="btn btn-info btn-sm">Lihat</a>
                                <a href="{{ route('inventory.meso.edit', $row->id_meso) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('inventory.meso.destroy', $row->id_meso) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id_meso }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_meso }}', 'data MESO ini')"
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
