@extends('inventory.layouts.app')

@section('header', 'Pelanggan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Pelanggan</h3>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex flex-wrap gap-1">
                <a class="btn btn-sm btn-success" href="{{ route('inventory.pelanggan.create') }}">TAMBAH</a>
                <a class="btn btn-sm btn-primary" href="{{ route('inventory.konseling.index') }}" target="_blank">KONSELING</a>
                <a class="btn btn-sm btn-warning" href="{{ route('inventory.meso.index') }}" target="_blank">MESO</a>
                <a class="btn btn-sm btn-danger" href="{{ route('inventory.pio.index') }}" target="_blank">PIO</a>
                <a class="btn btn-sm btn-secondary" href="{{ route('inventory.pto.index') }}" target="_blank">PTO</a>
                <a class="btn btn-sm btn-success" href="{{ route('inventory.cpp.index') }}" target="_blank">CATATAN PENGOBATAN PASIEN (CPP)</a>
                <a class="btn btn-sm btn-info" href="{{ route('inventory.homecare.index') }}" target="_blank">HOME CARE</a>
                <a class="btn btn-sm btn-info" href="{{ route('inventory.swamedikasi.index') }}" target="_blank">SWAMEDIKASI</a>
                <a class="btn btn-sm btn-info" href="{{ route('inventory.cekdarah.index') }}" target="_blank">CEK DARAH</a>
                <a class="btn btn-sm btn-info" href="{{ route('inventory.poin.index') }}" target="_blank">POIN MEMBER</a>
            </div>
            <table id="example1" class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $isPemilik = auth('admin')->user()->isPemilik(); @endphp
                    @foreach ($pelanggan as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->nm_pelanggan }}</td>
                            <td>{{ $row->jenis_kelamin }}</td>
                            <td>{{ $row->tanggal_lahir ? \Carbon\Carbon::parse($row->tanggal_lahir)->format('d-m-Y') : '-' }}</td>
                            <td>{{ $row->tlp_pelanggan }}</td>
                            <td>{{ $row->alamat_pelanggan }}</td>
                            <td>
                                <div class="dropdown position-relative d-inline-block">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <div class="dropdown-menu center-below p-2 shadow" style="min-width: 180px;">
                                        <a href="{{ route('inventory.pelanggan.edit', $row->id_pelanggan) }}"
                                            class="btn btn-info btn-sm w-100 mb-1">EDIT</a>
                                        <a href="{{ route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-success btn-sm w-100 mb-1">SWAMEDIKASI</a>
                                        <a href="{{ route('inventory.cekdarah.create', ['id' => $row->id_pelanggan]) }}"
                                            class="btn btn-primary btn-sm w-100 mb-1">Cek Darah</a>
                                        <a href="{{ route('inventory.konseling.create', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-primary btn-sm w-100 mb-1">KONSELING</a>
                                        <a href="{{ route('inventory.meso.create', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-warning btn-sm w-100 mb-1">MESO</a>
                                        <a href="{{ route('inventory.pio.create', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-danger btn-sm w-100 mb-1">PIO</a>
                                        <a href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-secondary btn-sm w-100 mb-1">PTO</a>
                                        <a href="{{ route('inventory.cpp.create', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-success btn-sm w-100 mb-1">CPP</a>
                                        <a href="{{ route('inventory.homecare.create', ['id_pelanggan' => $row->id_pelanggan]) }}"
                                            class="btn btn-info btn-sm w-100 mb-1">HOMECARE</a>
                                        @if ($isPemilik)
                                            <form action="{{ route('inventory.pelanggan.destroy', $row->id_pelanggan) }}"
                                                method="POST" id="delete-form-{{ $row->id_pelanggan }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmDelete('delete-form-{{ $row->id_pelanggan }}', '{{ $row->nm_pelanggan }}')"
                                                    class="btn btn-danger btn-sm w-100">HAPUS</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
