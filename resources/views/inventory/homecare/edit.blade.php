@extends('inventory.layouts.app')

@section('header', 'Edit Home Care')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Home Pharmacy Care</h3>
        </div>
        <form action="{{ route('inventory.homecare.update', $homecare->id_homecare) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group" style="max-width: 200px;">
                    <label>No. Home Care</label>
                    <input type="text" class="form-control" value="{{ $homecare->no_homecare }}" readonly>
                </div>

                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>Nama Pasien *</label>
                        <input type="text" name="nama_pasien" class="form-control" value="{{ old('nama_pasien', $homecare->nama_pasien) }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Umur</label>
                        <input type="text" name="umur" class="form-control" value="{{ old('umur', $homecare->umur) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $homecare->alamat) }}">
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="telp" class="form-control" value="{{ old('telp', $homecare->telp) }}">
                </div>

                <hr>
                <h5 class="fw-bold">Kunjungan</h5>
                <div id="detail-container">
                    @forelse ($homecare->detail as $detail)
                        @include('inventory.homecare.partials.detail-row', ['detail' => $detail->toArray()])
                    @empty
                        @include('inventory.homecare.partials.detail-row', ['detail' => []])
                    @endforelse
                </div>
                <button type="button" class="btn btn-info btn-sm mb-3" onclick="addDetailRow()">+ Tambah Baris Kunjungan</button>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.homecare.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update Home Care</button>
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
