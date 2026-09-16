@extends('inventory.layouts.app')

@section('header', 'Tambah Slip Gaji')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Slip Gaji</h3>
        </div>
        <div class="card-body">
            @if ($karyawan->isEmpty())
                <div class="alert alert-warning">
                    Belum ada karyawan aktif di Data Gaji. Tambahkan dulu di menu Gaji Karyawan.
                </div>
                <a class="btn btn-danger" href="{{ route('inventory.gajidetail.index') }}">Kembali</a>
            @else
                <form method="POST" action="{{ route('inventory.gajidetail.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="id_gaji">Nama Karyawan</label>
                            <select class="form-control @error('id_gaji') is-invalid @enderror" name="id_gaji"
                                id="id_gaji" required onchange="hitungGajiPokok()">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($karyawan as $k)
                                    <option value="{{ $k->id_gaji }}" data-gaji-harian="{{ $k->gaji_harian }}"
                                        data-transportasi-harian="{{ $k->transportasi_harian }}"
                                        {{ (old('id_gaji', $preselectIdGaji) == $k->id_gaji) ? 'selected' : '' }}>
                                        {{ $k->admin->nama_lengkap }} ({{ $k->admin->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_gaji') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="bulan">Bulan</label>
                            <select class="form-control @error('bulan') is-invalid @enderror" name="bulan" required>
                                @for ($b = 1; $b <= 12; $b++)
                                    <option value="{{ $b }}" {{ old('bulan', (int) date('n')) == $b ? 'selected' : '' }}>
                                        {{ \App\Models\GajiDetail::namaBulan($b) }}
                                    </option>
                                @endfor
                            </select>
                            @error('bulan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="tahun">Tahun</label>
                            <select class="form-control @error('tahun') is-invalid @enderror" name="tahun" required>
                                @for ($t = (int) date('Y') - 1; $t <= (int) date('Y') + 1; $t++)
                                    <option value="{{ $t }}" {{ old('tahun', (int) date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
                            @error('tahun') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="tgl_awal">Tanggal Awal</label>
                            <input type="date" name="tgl_awal" id="tgl_awal"
                                class="form-control @error('tgl_awal') is-invalid @enderror" value="{{ old('tgl_awal') }}" required>
                            @error('tgl_awal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="tgl_akhir">Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" id="tgl_akhir"
                                class="form-control @error('tgl_akhir') is-invalid @enderror" value="{{ old('tgl_akhir') }}" required>
                            @error('tgl_akhir') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="jumlah_hari">Jumlah Hari</label>
                            <input type="number" step="1" min="0" name="jumlah_hari" id="jumlah_hari"
                                class="form-control @error('jumlah_hari') is-invalid @enderror"
                                value="{{ old('jumlah_hari', 0) }}" required oninput="hitungGajiPokok()">
                            @error('jumlah_hari') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="gaji_pokok">Gaji Pokok</label>
                            <input type="number" step="0.01" min="0" name="gaji_pokok" id="gaji_pokok"
                                class="form-control @error('gaji_pokok') is-invalid @enderror" value="{{ old('gaji_pokok', 0) }}" required>
                            <small class="text-muted">Otomatis = Jumlah Hari &times; Gaji Pokok, boleh disesuaikan.</small>
                            @error('gaji_pokok') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="transportasi">Transportasi / Extra fooding</label>
                            <input type="number" step="0.01" min="0" name="transportasi" id="transportasi"
                                class="form-control @error('transportasi') is-invalid @enderror" value="{{ old('transportasi', 0) }}" required>
                            <small class="text-muted">Otomatis = Jumlah Hari &times; Transportasi/Ekstra Fooding, boleh disesuaikan.</small>
                            @error('transportasi') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="pinjaman">Pinjaman / Kasbon</label>
                            <input type="number" step="0.01" min="0" name="pinjaman" id="pinjaman"
                                class="form-control @error('pinjaman') is-invalid @enderror" value="{{ old('pinjaman', 0) }}" required>
                            <small class="text-muted">Akan mengurangi total penerimaan gaji.</small>
                            @error('pinjaman') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="lembur">Lembur</label>
                            <input type="number" step="0.01" min="0" name="lembur" id="lembur"
                                class="form-control @error('lembur') is-invalid @enderror" value="{{ old('lembur') }}"
                                placeholder="Kosongkan untuk tarik otomatis">
                            <small class="text-muted">Kosongkan untuk menarik otomatis dari Kehadiran Pegawai &gt; Lembur (jam disetujui &times; tarif lembur/jam) sesuai Tanggal Awal &amp; Akhir di atas.</small>
                            @error('lembur') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="komisi">Komisi</label>
                            <input type="number" step="0.01" min="0" name="komisi" id="komisi"
                                class="form-control @error('komisi') is-invalid @enderror" value="{{ old('komisi') }}"
                                placeholder="Kosongkan untuk hitung otomatis">
                            <small class="text-muted">Kosongkan untuk dihitung otomatis dari Laporan Komisi Pegawai sesuai Tanggal Awal &amp; Akhir di atas.</small>
                            @error('komisi') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="disetujui_oleh">Disetujui Oleh</label>
                            <select class="form-control @error('disetujui_oleh') is-invalid @enderror" name="disetujui_oleh">
                                <option value="">-- Belum Disetujui --</option>
                                @foreach ($pemilik as $p)
                                    <option value="{{ $p->id_admin }}" {{ old('disetujui_oleh') == $p->id_admin ? 'selected' : '' }}>
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
                        var opt = document.getElementById('id_gaji').selectedOptions[0];
                        var gajiHarian = opt ? (parseFloat(opt.getAttribute('data-gaji-harian')) || 0) : 0;
                        var transportasiHarian = opt ? (parseFloat(opt.getAttribute('data-transportasi-harian')) || 0) : 0;
                        var hari = parseFloat(document.getElementById('jumlah_hari').value) || 0;
                        document.getElementById('gaji_pokok').value = (gajiHarian * hari).toFixed(2);
                        document.getElementById('transportasi').value = (transportasiHarian * hari).toFixed(2);
                    }

                    @if ($preselectIdGaji > 0)
                        hitungGajiPokok();
                    @endif
                </script>
            @endif
        </div>
    </div>
@endsection
