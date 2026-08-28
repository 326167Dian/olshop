@extends('inventory.layouts.app')

@section('header', 'Edit CPP')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Catatan Pengobatan Pasien (CPP)</h3>
        </div>
        <form action="{{ route('inventory.cpp.update', $cpp->id_cpp) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group" style="max-width: 200px;">
                    <label>No. CPP</label>
                    <input type="text" class="form-control" value="{{ $cpp->no_cpp }}" readonly>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nama Pasien *</label>
                        <input type="text" name="nama_pasien" class="form-control" value="{{ old('nama_pasien', $cpp->nama_pasien) }}" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Jenis Kelamin *</label>
                        <select name="jk" class="form-control" required>
                            <option value="">Pilih</option>
                            <option value="Laki-laki" {{ old('jk', $cpp->jk) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jk', $cpp->jk) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Umur</label>
                        <input type="text" name="umur" class="form-control" value="{{ old('umur', $cpp->umur) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $cpp->alamat) }}">
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $cpp->telp) }}">
                </div>

                <hr>
                <h5 class="fw-bold">Detail Obat</h5>
                <div id="detail-container">
                    @forelse ($cpp->detail as $detail)
                        @include('inventory.cpp.partials.detail-row', ['detail' => $detail->toArray()])
                    @empty
                        @include('inventory.cpp.partials.detail-row', ['detail' => []])
                    @endforelse
                </div>
                <button type="button" class="btn btn-info btn-sm mb-3" onclick="addDetailRow()">+ Tambah Baris Obat</button>

                <hr>
                <h5 class="fw-bold">Tanda Tangan</h5>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tanggal TTD</label>
                        <input type="text" name="tgl_ttd" class="form-control" value="{{ old('tgl_ttd', $cpp->tgl_ttd) }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Tahun (2 digit)</label>
                        <input type="text" name="thn_ttd" class="form-control" maxlength="2" value="{{ old('thn_ttd', $cpp->thn_ttd) }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Nama Apoteker</label>
                        <input type="text" name="nama_apoteker" class="form-control" value="{{ old('nama_apoteker', $cpp->nama_apoteker) }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>No. SIPA</label>
                        <input type="text" name="sipa_apoteker" class="form-control" value="{{ old('sipa_apoteker', $cpp->sipa_apoteker) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.cpp.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update CPP</button>
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
