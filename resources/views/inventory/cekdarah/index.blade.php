@extends('inventory.layouts.app')

@section('header', 'Cek Darah')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Cek Darah</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.cekdarah.create') }}">
                <i class="fas fa-plus"></i> Mulai Cek Darah
            </a>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pasien</th>
                        <th>Petugas</th>
                        <th>Glukosa</th>
                        <th>Asam Urat</th>
                        <th>Kolesterol</th>
                        <th>Tensi</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cekdarah as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->pelanggan->nm_pelanggan ?? '-' }}</td>
                            <td>{{ $row->petugas }}</td>
                            <td>{{ $row->gula }}</td>
                            <td>{{ $row->asamurat }}</td>
                            <td>{{ $row->kolesterol }}</td>
                            <td>{{ $row->tensi }}</td>
                            <td>{{ $row->waktu?->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('inventory.cekdarah.edit', $row->id_cekdarah) }}"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('inventory.cekdarah.print', $row->id_cekdarah) }}" target="_blank"
                                    class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                <form action="{{ route('inventory.cekdarah.destroy', $row->id_cekdarah) }}"
                                    method="POST" class="d-inline" id="delete-form-{{ $row->id_cekdarah }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id_cekdarah }}', 'hasil cek darah ini')"
                                        class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
