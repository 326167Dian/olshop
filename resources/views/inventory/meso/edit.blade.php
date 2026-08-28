@extends('inventory.layouts.app')

@section('header', 'Edit Formulir Pelaporan ESO')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Formulir Pelaporan Efek Samping Obat (ESO)</h3>
        </div>
        <form action="{{ route('inventory.meso.update', $meso->id_meso) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="kode_sumber_data">Kode Sumber Data</label>
                    <input type="text" name="kode_sumber_data" id="kode_sumber_data" class="form-control"
                        style="max-width: 300px;" value="{{ old('kode_sumber_data', $meso->kode_sumber_data) }}">
                </div>

                <h5 class="fw-bold mt-3"><u>Penderita</u></h5>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Nama Pasien</label>
                        <input type="text" class="form-control" value="{{ $meso->nama_singkat }}" readonly>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Umur</label>
                        <input type="text" class="form-control" value="{{ $meso->umur }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Jenis Kelamin</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" {{ old('jenis_kelamin', $meso->jenis_kelamin) == 'L' ? 'checked' : '' }}>
                            <label class="form-check-label" for="jk_l">Pria</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" {{ old('jenis_kelamin', $meso->jenis_kelamin) == 'P' ? 'checked' : '' }}>
                            <label class="form-check-label" for="jk_p">Wanita</label>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="status_hamil">Status Kehamilan</label>
                        <select name="status_hamil" id="status_hamil" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="hamil" {{ old('status_hamil', $meso->status_hamil) == 'hamil' ? 'selected' : '' }}>Hamil</option>
                            <option value="tidak_hamil" {{ old('status_hamil', $meso->status_hamil) == 'tidak_hamil' ? 'selected' : '' }}>Tidak Hamil</option>
                            <option value="tidak_tahu" {{ old('status_hamil', $meso->status_hamil) == 'tidak_tahu' ? 'selected' : '' }}>Tidak Tahu</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="suku">Suku</label>
                        <input type="text" name="suku" id="suku" class="form-control" value="{{ old('suku', $meso->suku) }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="berat_badan">Berat Badan (kg)</label>
                        <input type="text" name="berat_badan" id="berat_badan" class="form-control" value="{{ old('berat_badan', $meso->berat_badan) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="pekerjaan">Pekerjaan</label>
                        <input type="text" name="pekerjaan" id="pekerjaan" class="form-control" value="{{ old('pekerjaan', $meso->pekerjaan) }}">
                    </div>
                </div>

                <h5 class="fw-bold mt-3"><u>Penyakit</u></h5>
                <div class="form-group">
                    <label for="penyakit_utama">Penyakit Utama</label>
                    <textarea name="penyakit_utama" id="penyakit_utama" rows="3"
                        class="form-control @error('penyakit_utama') is-invalid @enderror">{{ old('penyakit_utama', $meso->penyakit_utama) }}</textarea>
                    @error('penyakit_utama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Kondisi Lain yang Menyertai</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="gangguan_ginjal" id="gangguan_ginjal" value="1" {{ $meso->gangguan_ginjal ? 'checked' : '' }}>
                        <label class="form-check-label" for="gangguan_ginjal">Gangguan Ginjal</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="gangguan_hati" id="gangguan_hati" value="1" {{ $meso->gangguan_hati ? 'checked' : '' }}>
                        <label class="form-check-label" for="gangguan_hati">Gangguan Hati</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="alergi" id="alergi" value="1" {{ $meso->alergi ? 'checked' : '' }}>
                        <label class="form-check-label" for="alergi">Alergi</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="kondisi_medis_lain" id="kondisi_medis_lain" value="1" {{ $meso->kondisi_medis_lain ? 'checked' : '' }}>
                        <label class="form-check-label" for="kondisi_medis_lain">Kondisi Medis Lainnya</label>
                    </div>
                    <input type="text" name="kondisi_medis_lain_ket" class="form-control mt-2"
                        placeholder="Sebutkan kondisi medis lainnya" value="{{ old('kondisi_medis_lain_ket', $meso->kondisi_medis_lain_ket) }}">
                </div>
                <div class="form-group">
                    <label>Kesudahan Penyakit Utama</label><br>
                    @foreach (['sembuh' => 'Sembuh', 'sembuh_gejala_sisa' => 'Sembuh dengan gejala sisa', 'belum_sembuh' => 'Belum sembuh', 'meninggal' => 'Meninggal', 'tidak_tahu' => 'Tidak Tahu'] as $val => $label)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="kesudahan_penyakit" id="kp_{{ $val }}" value="{{ $val }}" {{ old('kesudahan_penyakit', $meso->kesudahan_penyakit) == $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="kp_{{ $val }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>

                <h5 class="fw-bold mt-3"><u>Efek Samping Obat</u></h5>
                <div class="form-group">
                    <label for="manifestasi_eso">Bentuk / Manifestasi ESO</label>
                    <textarea name="manifestasi_eso" id="manifestasi_eso" rows="3"
                        class="form-control @error('manifestasi_eso') is-invalid @enderror">{{ old('manifestasi_eso', $meso->manifestasi_eso) }}</textarea>
                    @error('manifestasi_eso') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="masalah_mutu_produk">Masalah pada Mutu/Kualitas Produk Obat</label>
                    <textarea name="masalah_mutu_produk" id="masalah_mutu_produk" rows="2" class="form-control">{{ old('masalah_mutu_produk', $meso->masalah_mutu_produk) }}</textarea>
                </div>
                <div class="col-md-4 form-group p-0">
                    <label for="tanggal_mula_eso">Tanggal Mula Terjadi</label>
                    <input type="date" name="tanggal_mula_eso" id="tanggal_mula_eso"
                        class="form-control @error('tanggal_mula_eso') is-invalid @enderror"
                        value="{{ old('tanggal_mula_eso', $meso->tanggal_mula_eso?->format('Y-m-d')) }}">
                    @error('tanggal_mula_eso') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Kesudahan ESO</label><br>
                    @foreach (['sembuh' => 'Sembuh', 'sembuh_gejala_sisa' => 'Sembuh dengan gejala sisa', 'belum_sembuh' => 'Belum sembuh', 'meninggal' => 'Meninggal', 'tidak_tahu' => 'Tidak Tahu'] as $val => $label)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="kesudahan_eso" id="ke_{{ $val }}" value="{{ $val }}" {{ old('kesudahan_eso', $meso->kesudahan_eso) == $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="ke_{{ $val }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-group">
                    <label for="riwayat_eso">Riwayat ESO yang Pernah Dialami</label>
                    <textarea name="riwayat_eso" id="riwayat_eso" rows="2" class="form-control">{{ old('riwayat_eso', $meso->riwayat_eso) }}</textarea>
                </div>

                <h5 class="fw-bold mt-3"><u>Obat yang Dikonsumsi</u></h5>
                <div id="obat-container">
                    @forelse ($meso->data_obat ?? [] as $obat)
                        @include('inventory.meso.partials.obat-row', ['obat' => $obat])
                    @empty
                        @include('inventory.meso.partials.obat-row', ['obat' => []])
                    @endforelse
                </div>
                <button type="button" class="btn btn-sm btn-success mb-3" onclick="tambahObat()">+ Tambah Obat</button>

                <h5 class="fw-bold mt-3"><u>Keterangan Tambahan</u></h5>
                <div class="form-group">
                    <label for="keterangan_tambahan">Keterangan Tambahan</label>
                    <textarea name="keterangan_tambahan" id="keterangan_tambahan" rows="3" class="form-control"
                        placeholder="Contoh: kecepatan timbulnya ESO, reaksi setelah obat dihentikan, dsb">{{ old('keterangan_tambahan', $meso->keterangan_tambahan) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="data_laboratorium">Data Laboratorium</label>
                    <textarea name="data_laboratorium" id="data_laboratorium" rows="2" class="form-control">{{ old('data_laboratorium', $meso->data_laboratorium) }}</textarea>
                </div>
                <div class="col-md-3 form-group p-0">
                    <label for="tanggal_pemeriksaan_lab">Tanggal Pemeriksaan Lab</label>
                    <input type="date" name="tanggal_pemeriksaan_lab" id="tanggal_pemeriksaan_lab" class="form-control"
                        value="{{ old('tanggal_pemeriksaan_lab', $meso->tanggal_pemeriksaan_lab?->format('Y-m-d')) }}">
                </div>

                <h5 class="fw-bold mt-3"><u>Pelapor</u></h5>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="tanggal_laporan">Tanggal Laporan</label>
                        <input type="date" name="tanggal_laporan" id="tanggal_laporan"
                            class="form-control @error('tanggal_laporan') is-invalid @enderror"
                            value="{{ old('tanggal_laporan', $meso->tanggal_laporan?->format('Y-m-d')) }}">
                        @error('tanggal_laporan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="nama_pelapor">Nama Pelapor</label>
                        <input type="text" name="nama_pelapor" id="nama_pelapor"
                            class="form-control @error('nama_pelapor') is-invalid @enderror"
                            value="{{ old('nama_pelapor', $meso->nama_pelapor) }}">
                        @error('nama_pelapor') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.meso.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function tambahObat() {
        var container = document.getElementById('obat-container');
        var newRow = container.querySelector('.obat-row').cloneNode(true);
        newRow.querySelectorAll('input[type="text"], input[type="date"]').forEach(function(input) {
            input.value = '';
        });
        newRow.querySelectorAll('input[type="checkbox"]').forEach(function(input) {
            input.checked = false;
        });
        container.appendChild(newRow);
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-obat')) {
            var rows = document.querySelectorAll('.obat-row');
            if (rows.length > 1) {
                e.target.closest('.obat-row').remove();
            }
        }
    });
</script>
@endpush
