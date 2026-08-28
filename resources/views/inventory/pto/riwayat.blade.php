@extends('inventory.layouts.app')

@section('header', 'Riwayat PTO')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat PTO - {{ $pelanggan->nm_pelanggan }}</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-success mb-3"
                href="{{ route('inventory.pto.create', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}">Input PTO
                Baru</a>
            <a class="btn btn-sm btn-danger mb-3"
                href="{{ route('inventory.pto.export-pdf', ['id_pelanggan' => $pelanggan->id_pelanggan, 'tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir]) }}"
                target="_blank">Export PDF</a>
            <a class="btn btn-sm btn-primary mb-3" href="{{ route('inventory.pelanggan.index') }}">Kembali ke
                Pelanggan</a>

            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="id_pelanggan" value="{{ $pelanggan->id_pelanggan }}">
                <div class="col-auto">
                    <label class="form-label mb-0">Dari</label>
                    <input type="date" name="tgl_awal" class="form-control form-control-sm" value="{{ $tglAwal }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Sampai</label>
                    <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="{{ $tglAkhir }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
                    <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}">Reset</a>
                </div>
            </form>

            <table class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal 1</th>
                        <th>Tanggal 2</th>
                        <th>Tempat TTD</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->tanggal_1?->format('d-m-Y') }}</td>
                            <td>{{ $row->tanggal_2?->format('d-m-Y') }}</td>
                            <td>{{ $row->tempat_ttd }}</td>
                            <td>{{ $row->created_by }}</td>
                            <td>
                                <a href="{{ route('inventory.pto.show', $row->id_pto) }}" target="_blank"
                                    class="btn btn-info btn-sm">Lihat</a>
                                @if ($isPemilik)
                                    <a href="{{ route('inventory.pto.edit', $row->id_pto) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('inventory.pto.destroy', $row->id_pto) }}" method="POST"
                                        class="d-inline" id="delete-form-{{ $row->id_pto }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                            onclick="confirmDelete('delete-form-{{ $row->id_pto }}', 'data PTO ini')"
                                            class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data PTO.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
