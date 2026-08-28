@extends('inventory.layouts.app')

@section('header', 'Formulir Dokumentasi PIO')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Formulir Dokumentasi PIO (Pelayanan Informasi Obat)</h3>
        </div>
        <form action="{{ route('inventory.pio.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_pelanggan" value="{{ $idPelanggan }}">
            <div class="card-body">
                <h5 class="fw-bold"><u>Informasi Dasar</u></h5>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label>No. PIO</label>
                        <input type="text" name="no_pio" class="form-control" value="{{ $noPio }}" readonly>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', now()->format('Y-m-d')) }}">
                        @error('tanggal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="waktu">Waktu</label>
                        <input type="time" name="waktu" id="waktu"
                            class="form-control @error('waktu') is-invalid @enderror"
                            value="{{ old('waktu', now()->format('H:i')) }}">
                        @error('waktu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <label>Metode</label><br>
                    @foreach (['Lisan', 'Tertulis', 'Telepon'] as $m)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="metode" id="metode_{{ $m }}" value="{{ $m }}" {{ old('metode', 'Lisan') == $m ? 'checked' : '' }}>
                            <label class="form-check-label" for="metode_{{ $m }}">{{ $m }}</label>
                        </div>
                    @endforeach
                </div>

                <h5 class="fw-bold mt-3"><u>Identitas Penanya</u></h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="nama_penanya">Nama Penanya</label>
                        <input type="text" name="nama_penanya" id="nama_penanya"
                            class="form-control @error('nama_penanya') is-invalid @enderror" value="{{ old('nama_penanya') }}">
                        @error('nama_penanya') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="no_telp_penanya">No. Telp</label>
                        <input type="text" name="no_telp_penanya" id="no_telp_penanya" class="form-control" value="{{ old('no_telp_penanya') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label><br>
                    @foreach (['Pasien', 'Keluarga Pasien', 'Petugas Kesehatan'] as $s)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_penanya" id="sp_{{ Str::slug($s) }}" value="{{ $s }}" {{ old('status_penanya', 'Pasien') == $s ? 'checked' : '' }}>
                            <label class="form-check-label" for="sp_{{ Str::slug($s) }}">{{ $s }}</label>
                        </div>
                    @endforeach
                    <input type="text" name="status_penanya_ket" class="form-control mt-2" style="max-width: 400px;"
                        placeholder="Instansi/Jabatan (untuk Petugas Kesehatan)" value="{{ old('status_penanya_ket') }}">
                </div>

                <h5 class="fw-bold mt-3"><u>Data Pasien</u></h5>
                <div class="form-group">
                    <label>Nama Pasien</label>
                    <input type="text" class="form-control" style="max-width: 400px;" value="{{ $pelanggan->nm_pelanggan ?? '' }}" readonly>
                </div>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label for="umur_pasien">Umur (tahun)</label>
                        <input type="number" name="umur_pasien" id="umur_pasien" class="form-control" value="{{ old('umur_pasien', $umur) }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="tinggi_pasien">Tinggi (cm)</label>
                        <input type="number" name="tinggi_pasien" id="tinggi_pasien" class="form-control" value="{{ old('tinggi_pasien') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="berat_pasien">Berat (kg)</label>
                        <input type="number" name="berat_pasien" id="berat_pasien" class="form-control" value="{{ old('berat_pasien') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label><br>
                    @php $jkPelanggan = ($pelanggan->jenis_kelamin ?? '') == 'PRIA' ? 'L' : (($pelanggan->jenis_kelamin ?? '') == 'WANITA' ? 'P' : ''); @endphp
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" {{ old('jenis_kelamin', $jkPelanggan) == 'L' ? 'checked' : '' }}>
                        <label class="form-check-label" for="jk_l">Laki-laki</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" {{ old('jenis_kelamin', $jkPelanggan) == 'P' ? 'checked' : '' }}>
                        <label class="form-check-label" for="jk_p">Perempuan</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Kehamilan</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="kehamilan" id="kehamilan" value="1" onclick="document.getElementById('kehamilan_minggu').disabled = !this.checked">
                            <label class="form-check-label" for="kehamilan">Ya</label>
                        </div>
                        <input type="number" id="kehamilan_minggu" name="kehamilan_minggu" class="form-control d-inline-block" style="width: 100px;" placeholder="Minggu" disabled>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Menyusui</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="menyusui" id="menyusui" value="1">
                            <label class="form-check-label" for="menyusui">Ya</label>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mt-3"><u>Pertanyaan</u></h5>
                <div class="form-group">
                    <label for="uraian_pertanyaan">Uraian Pertanyaan</label>
                    <textarea name="uraian_pertanyaan" id="uraian_pertanyaan" rows="4"
                        class="form-control @error('uraian_pertanyaan') is-invalid @enderror">{{ old('uraian_pertanyaan') }}</textarea>
                    @error('uraian_pertanyaan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Jenis Pertanyaan</label>
                    <div class="row">
                        @foreach (\App\Models\Pio::JENIS_PERTANYAAN as $key => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_pertanyaan[]" id="jp_{{ $key }}" value="{{ $key }}"
                                        @if ($key === 'lain_lain') onclick="document.getElementById('jp_lain_lain_ket').disabled = !this.checked" @endif>
                                    <label class="form-check-label" for="jp_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="text" id="jp_lain_lain_ket" name="jenis_pertanyaan_lain_lain_ket" class="form-control mt-2"
                        style="max-width: 400px;" placeholder="Sebutkan" disabled>
                </div>

                <h5 class="fw-bold mt-3"><u>Jawaban & Referensi</u></h5>
                <div class="form-group">
                    <label for="jawaban">Jawaban</label>
                    <textarea name="jawaban" id="jawaban" rows="5" class="form-control">{{ old('jawaban') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="referensi">Referensi</label>
                    <textarea name="referensi" id="referensi" rows="3" class="form-control">{{ old('referensi') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Penyampaian Jawaban</label><br>
                    @foreach (['Segera', 'Dalam 24 jam', 'Lebih dari 24 jam'] as $pj)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="penyampaian_jawaban" id="pj_{{ Str::slug($pj) }}" value="{{ $pj }}" {{ old('penyampaian_jawaban') == $pj ? 'checked' : '' }}>
                            <label class="form-check-label" for="pj_{{ Str::slug($pj) }}">{{ $pj }}</label>
                        </div>
                    @endforeach
                </div>

                <h5 class="fw-bold mt-3"><u>Apoteker Penjawab</u></h5>
                <div class="form-group">
                    <label for="apoteker_penjawab">Nama Apoteker</label>
                    <input type="text" name="apoteker_penjawab" id="apoteker_penjawab" class="form-control"
                        style="max-width: 400px;" value="{{ old('apoteker_penjawab', auth('admin')->user()->nama_lengkap) }}">
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="tanggal_jawab">Tanggal Jawab</label>
                        <input type="date" name="tanggal_jawab" id="tanggal_jawab" class="form-control" value="{{ old('tanggal_jawab', now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="waktu_jawab">Waktu Jawab</label>
                        <input type="time" name="waktu_jawab" id="waktu_jawab" class="form-control" value="{{ old('waktu_jawab', now()->format('H:i')) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Metode Jawaban</label><br>
                    @foreach (['Lisan', 'Tertulis', 'Telepon'] as $mj)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="metode_jawab" id="mj_{{ $mj }}" value="{{ $mj }}" {{ old('metode_jawab') == $mj ? 'checked' : '' }}>
                            <label class="form-check-label" for="mj_{{ $mj }}">{{ $mj }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pio.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
