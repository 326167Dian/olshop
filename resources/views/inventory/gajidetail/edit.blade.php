@extends('inventory.layouts.app')

@section('header', 'Ubah Slip Gaji')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Slip Gaji</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.gajidetail.update', $slip->id_gaji_detail) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nama Karyawan</label>
                        <input type="text" class="form-control"
                            value="{{ ($slip->admin->nama_lengkap ?? '-') . ' (' . ($slip->admin->username ?? '-') . ')' }}"
                            disabled>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Periode</label>
                        <input type="text" class="form-control" value="{{ $slip->periode_text }}" disabled>
                        <small class="text-muted">Karyawan &amp; periode tidak bisa diubah. Hapus lalu tambahkan ulang jika salah.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="tgl_awal">Tanggal Awal</label>
                        <input type="date" name="tgl_awal" id="tgl_awal"
                            class="form-control @error('tgl_awal') is-invalid @enderror"
                            value="{{ old('tgl_awal', optional($slip->tgl_awal)->format('Y-m-d')) }}" required>
                        @error('tgl_awal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="tgl_akhir">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" id="tgl_akhir"
                            class="form-control @error('tgl_akhir') is-invalid @enderror"
                            value="{{ old('tgl_akhir', optional($slip->tgl_akhir)->format('Y-m-d')) }}" required>
                        @error('tgl_akhir') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="jumlah_hari">Jumlah Hari</label>
                        <input type="number" step="1" min="0" name="jumlah_hari" id="jumlah_hari"
                            class="form-control @error('jumlah_hari') is-invalid @enderror"
                            value="{{ old('jumlah_hari', $slip->jumlah_hari) }}" required oninput="hitungGajiPokok()">
                        @error('jumlah_hari') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="gaji_pokok">Gaji Pokok</label>
                        <input type="number" step="0.01" min="0" name="gaji_pokok" id="gaji_pokok"
                            class="form-control @error('gaji_pokok') is-invalid @enderror"
                            value="{{ old('gaji_pokok', $slip->gaji_pokok) }}" required>
                        <small class="text-muted">Otomatis = Jumlah Hari &times; Gaji Pokok, boleh disesuaikan.</small>
                        @error('gaji_pokok') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="transportasi">Transportasi / Extra fooding</label>
                        <input type="number" step="0.01" min="0" name="transportasi" id="transportasi"
                            class="form-control @error('transportasi') is-invalid @enderror"
                            value="{{ old('transportasi', $slip->transportasi) }}" required>
                        <small class="text-muted">Otomatis = Jumlah Hari &times; Transportasi/Ekstra Fooding, boleh disesuaikan.</small>
                        @error('transportasi') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="pinjaman">Pinjaman / Kasbon</label>
                        <input type="number" step="0.01" min="0" name="pinjaman" id="pinjaman"
                            class="form-control @error('pinjaman') is-invalid @enderror"
                            value="{{ old('pinjaman', $slip->pinjaman) }}" required>
                        <small class="text-muted">Akan mengurangi total penerimaan gaji.</small>
                        @error('pinjaman') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="lembur">Lembur</label>
                        <input type="number" step="0.01" min="0" name="lembur" id="lembur"
                            class="form-control @error('lembur') is-invalid @enderror"
                            value="{{ old('lembur', $slip->lembur) }}">
                        <small class="text-muted">Kosongkan lalu simpan untuk menarik ulang otomatis dari Kehadiran Pegawai &gt; Lembur.</small>
                        @error('lembur') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="komisi">Komisi</label>
                        <input type="number" step="0.01" min="0" name="komisi" id="komisi"
                            class="form-control @error('komisi') is-invalid @enderror"
                            value="{{ old('komisi', $slip->komisi) }}">
                        <small class="text-muted">Kosongkan lalu simpan untuk dihitung ulang otomatis dari Laporan Komisi Pegawai.</small>
                        @error('komisi') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="disetujui_oleh">Disetujui Oleh</label>
                        <select class="form-control @error('disetujui_oleh') is-invalid @enderror" name="disetujui_oleh">
                            <option value="">-- Belum Disetujui --</option>
                            @foreach ($pemilik as $p)
                                <option value="{{ $p->id_admin }}" {{ old('disetujui_oleh', $slip->disetujui_oleh) == $p->id_admin ? 'selected' : '' }}>
                                    {{ $p->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('disetujui_oleh') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('inventory.gajidetail.index') }}" class="btn btn-danger">Batal</a>
                </div>
            </form>

            <script>
                function hitungGajiPokok() {
                    var gajiHarian = {{ (float) ($slip->gaji->gaji_harian ?? 0) }};
                    var transportasiHarian = {{ (float) ($slip->gaji->transportasi_harian ?? 0) }};
                    var hari = parseFloat(document.getElementById('jumlah_hari').value) || 0;
                    document.getElementById('gaji_pokok').value = (gajiHarian * hari).toFixed(2);
                    document.getElementById('transportasi').value = (transportasiHarian * hari).toFixed(2);
                }
            </script>
        </div>
    </div>
@endsection
