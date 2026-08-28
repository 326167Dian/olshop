@extends('inventory.layouts.app')

@section('header', 'Input CPP')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Catatan Pengobatan Pasien (CPP)</h3>
        </div>
        <form action="{{ route('inventory.cpp.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_pelanggan" value="{{ $idPelanggan }}">
            <div class="card-body">
                <div class="form-group" style="max-width: 200px;">
                    <label>No. CPP</label>
                    <input type="text" name="no_cpp" class="form-control" value="{{ $noCpp }}" readonly>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nama Pasien *</label>
                        <input type="text" name="nama_pasien" class="form-control"
                            value="{{ old('nama_pasien', $pelanggan->nm_pelanggan ?? '') }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Jenis Kelamin *</label>
                        <select name="jk" class="form-control" required>
                            <option value="">Pilih</option>
                            <option value="Laki-laki" {{ old('jk', $jk) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jk', $jk) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Umur</label>
                        <input type="text" name="umur" class="form-control" value="{{ old('umur', $umur) }}" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $pelanggan->alamat_pelanggan ?? '') }}">
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $pelanggan->tlp_pelanggan ?? '') }}">
                </div>

                <hr>
                <h5 class="fw-bold">Detail Obat</h5>
                <div id="detail-container">
                    @include('inventory.cpp.partials.detail-row', ['detail' => []])
                </div>
                <button type="button" class="btn btn-info btn-sm mb-3" onclick="addDetailRow()">+ Tambah Baris Obat</button>

                <hr>
                <h5 class="fw-bold">Tanda Tangan</h5>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tanggal TTD</label>
                        <input type="text" name="tgl_ttd" class="form-control" value="{{ old('tgl_ttd', now()->translatedFormat('d F Y')) }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Tahun (2 digit)</label>
                        <input type="text" name="thn_ttd" class="form-control" maxlength="2" value="{{ old('thn_ttd', now()->format('y')) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Nama Apoteker</label>
                        <input type="text" name="nama_apoteker" class="form-control" value="{{ old('nama_apoteker', $namaApoteker) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>No. SIPA</label>
                        <input type="text" name="sipa_apoteker" class="form-control" value="{{ old('sipa_apoteker', $sipaApoteker) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pelanggan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan CPP</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function addDetailRow() {
        var container = document.getElementById('detail-container');
        var newRow = container.querySelector('.detail-row').cloneNode(true);
        newRow.querySelectorAll('input, textarea').forEach(function(el) { el.value = ''; });
        container.appendChild(newRow);
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove-row')) {
            var rows = document.querySelectorAll('.detail-row');
            if (rows.length > 1) {
                e.target.closest('.detail-row').remove();
            }
        }
    });
</script>
@endpush
