@extends('inventory.layouts.app')

@section('header', 'Home Care')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Home Pharmacy Care</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Home Care</th>
                        <th>Nama Pasien</th>
                        <th>Umur</th>
                        <th>Alamat</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($homecare as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->no_homecare }}</td>
                            <td>{{ $row->nama_pasien }}</td>
                            <td>{{ $row->umur }}</td>
                            <td>{{ $row->alamat }}</td>
                            <td>{{ $row->created_at?->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('inventory.homecare.edit', $row->id_homecare) }}"
                                    class="btn btn-info btn-sm">Edit</a>
                                <a href="{{ route('inventory.homecare.show', $row->id_homecare) }}" target="_blank"
                                    class="btn btn-success btn-sm">Cetak</a>
                                <form action="{{ route('inventory.homecare.destroy', $row->id_homecare) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id_homecare }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_homecare }}', 'data Home Care ini')"
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
