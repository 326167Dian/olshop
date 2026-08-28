@extends('inventory.layouts.app')

@section('header', 'Tambah Konseling')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Konseling</h3>
        </div>
        <form action="{{ route('inventory.konseling.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="id_pelanggan">Nama Pelanggan</label>
                        <select name="id_pelanggan" id="id_pelanggan"
                            class="form-control @error('id_pelanggan') is-invalid @enderror">
                            <option value="" disabled {{ old('id_pelanggan', $idPelangganTerpilih) == '' ? 'selected' : '' }}>- Pilih -</option>
                            @foreach ($pelangganList as $p)
                                <option value="{{ $p->id_pelanggan }}" {{ old('id_pelanggan', $idPelangganTerpilih) == $p->id_pelanggan ? 'selected' : '' }}>
                                    {{ $p->nm_pelanggan }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_pelanggan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tgl_konseling">Tanggal Konseling</label>
                        <input type="date" name="tgl_konseling" id="tgl_konseling"
                            class="form-control @error('tgl_konseling') is-invalid @enderror"
                            value="{{ old('tgl_konseling', now()->format('Y-m-d')) }}">
                        @error('tgl_konseling') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="nama_dokter">Nama Dokter</label>
                        <input type="text" name="nama_dokter" id="nama_dokter"
                            class="form-control @error('nama_dokter') is-invalid @enderror"
                            value="{{ old('nama_dokter') }}">
                        @error('nama_dokter') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="diagnosa">Diagnosa</label>
                    <textarea name="diagnosa" id="diagnosa" rows="2"
                        class="form-control @error('diagnosa') is-invalid @enderror">{{ old('diagnosa') }}</textarea>
                    @error('diagnosa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="riwayat_penyakit">Riwayat Penyakit</label>
                    <textarea name="riwayat_penyakit" id="riwayat_penyakit" rows="2"
                        class="form-control @error('riwayat_penyakit') is-invalid @enderror">{{ old('riwayat_penyakit') }}</textarea>
                    @error('riwayat_penyakit') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="riwayat_alergi">Riwayat Alergi</label>
                    <textarea name="riwayat_alergi" id="riwayat_alergi" rows="2"
                        class="form-control @error('riwayat_alergi') is-invalid @enderror">{{ old('riwayat_alergi') }}</textarea>
                    @error('riwayat_alergi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="keluhan">Keluhan</label>
                    <textarea name="keluhan" id="keluhan" rows="2"
                        class="form-control @error('keluhan') is-invalid @enderror">{{ old('keluhan') }}</textarea>
                    @error('keluhan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 form-group p-0">
                    <label for="visite">Visite Sebelumnya</label>
                    <input type="text" name="visite" id="visite"
                        class="form-control @error('visite') is-invalid @enderror" value="{{ old('visite') }}">
                    @error('visite') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="tindakan">Tindakan</label>
                    <textarea name="tindakan" id="tindakan" rows="2"
                        class="form-control @error('tindakan') is-invalid @enderror">{{ old('tindakan') }}</textarea>
                    @error('tindakan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.konseling.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
