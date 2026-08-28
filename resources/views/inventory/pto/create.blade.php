@extends('inventory.layouts.app')

@section('header', 'Input Dokumentasi PTO')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Dokumentasi Pemantauan Terapi Obat (PTO)</h3>
        </div>
        <form action="{{ route('inventory.pto.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_pelanggan" value="{{ $pelanggan->id_pelanggan }}">
            <div class="card-body">
                <a class="btn btn-sm btn-secondary mb-3"
                    href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}">Riwayat
                    PTO</a>

                <h5 class="fw-bold"><u>Data Pasien</u></h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Nama Pasien</label>
                        <input type="text" class="form-control" value="{{ $pelanggan->nm_pelanggan }}" readonly>
                        <input type="hidden" name="nm_pelanggan" value="{{ $pelanggan->nm_pelanggan }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Jenis Kelamin</label>
                        <input type="text" class="form-control" value="{{ $pelanggan->jenis_kelamin }}" readonly>
                        <input type="hidden" name="jenis_kelamin" value="{{ $pelanggan->jenis_kelamin }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Umur</label>
                        <input type="text" class="form-control" value="{{ $umur }}" readonly>
                        <input type="hidden" name="umur" value="{{ $umur }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>No. Telepon</label>
                        <input type="text" class="form-control" value="{{ $pelanggan->tlp_pelanggan }}" readonly>
                        <input type="hidden" name="tlp_pelanggan" value="{{ $pelanggan->tlp_pelanggan }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea class="form-control" rows="2" readonly>{{ $pelanggan->alamat_pelanggan }}</textarea>
                    <input type="hidden" name="alamat_pelanggan" value="{{ $pelanggan->alamat_pelanggan }}">
                </div>

                <hr>
                <h5 class="fw-bold"><u>Isi Dokumentasi PTO</u></h5>

                @for ($i = 1; $i <= 2; $i++)
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fw-bold">Baris {{ $i }}</h6>
                        <div class="col-md-4 form-group">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal_{{ $i }}" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>Catatan Pengobatan</label>
                            <textarea name="catatan_{{ $i }}" class="form-control" rows="2"
                                placeholder="Riwayat penyakit / penggunaan obat / alergi"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Nama Obat, Dosis, Cara Pemberian</label>
                            <textarea name="obat_{{ $i }}" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Identifikasi Masalah</label>
                            <textarea name="masalah_{{ $i }}" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>Rekomendasi / Tindak Lanjut</label>
                            <textarea name="tindak_{{ $i }}" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                @endfor

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tempat</label>
                        <input type="text" name="tempat_ttd" class="form-control" placeholder="Contoh: Bekasi">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Tanggal TTD</label>
                        <input type="date" name="tanggal_ttd" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pto.riwayat', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}"
                    class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan & Tampilkan PTO</button>
            </div>
        </form>
    </div>
@endsection
