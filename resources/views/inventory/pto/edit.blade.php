@extends('inventory.layouts.app')

@section('header', 'Edit Dokumentasi PTO')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Dokumentasi Pemantauan Terapi Obat (PTO)</h3>
        </div>
        <form action="{{ route('inventory.pto.update', $pto->id_pto) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <h5 class="fw-bold"><u>Data Pasien</u></h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Nama Pasien</label>
                        <input type="text" class="form-control" value="{{ $pto->nm_pelanggan }}" readonly>
                        <input type="hidden" name="nm_pelanggan" value="{{ $pto->nm_pelanggan }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Jenis Kelamin</label>
                        <input type="text" class="form-control" value="{{ $pto->jenis_kelamin }}" readonly>
                        <input type="hidden" name="jenis_kelamin" value="{{ $pto->jenis_kelamin }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Umur</label>
                        <input type="text" class="form-control" value="{{ $pto->umur }}" readonly>
                        <input type="hidden" name="umur" value="{{ $pto->umur }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>No. Telepon</label>
                        <input type="text" class="form-control" value="{{ $pto->tlp_pelanggan }}" readonly>
                        <input type="hidden" name="tlp_pelanggan" value="{{ $pto->tlp_pelanggan }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control" rows="2" readonly>{{ $pto->alamat_pelanggan }}</textarea>
                    <input type="hidden" name="alamat_pelanggan" value="{{ $pto->alamat_pelanggan }}">
                </div>

                <hr>
                <h5 class="fw-bold"><u>Isi Dokumentasi PTO</u></h5>

                @for ($i = 1; $i <= 2; $i++)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-bold">Baris {{ $i }}</h6>
                        <div class="col-md-4 form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal_{{ $i }}" class="form-control"
                                value="{{ $pto->{'tanggal_' . $i}?->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Catatan Pengobatan</label>
                            <textarea name="catatan_{{ $i }}" class="form-control" rows="2">{{ $pto->{'catatan_' . $i} }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Nama Obat, Dosis, Cara Pemberian</label>
                            <textarea name="obat_{{ $i }}" class="form-control" rows="2">{{ $pto->{'obat_' . $i} }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Identifikasi Masalah</label>
                            <textarea name="masalah_{{ $i }}" class="form-control" rows="2">{{ $pto->{'masalah_' . $i} }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>Rekomendasi / Tindak Lanjut</label>
                            <textarea name="tindak_{{ $i }}" class="form-control" rows="2">{{ $pto->{'tindak_' . $i} }}</textarea>
                        </div>
                    </div>
                @endfor

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tempat</label>
                        <input type="text" name="tempat_ttd" class="form-control" value="{{ $pto->tempat_ttd }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Tanggal TTD</label>
                        <input type="date" name="tanggal_ttd" class="form-control" value="{{ $pto->tanggal_ttd?->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $pto->id_pelanggan]) }}"
                    class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update & Tampilkan PTO</button>
            </div>
        </form>
    </div>
@endsection
