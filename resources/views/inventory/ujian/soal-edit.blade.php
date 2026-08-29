@extends('inventory.layouts.app')

@section('header', 'Edit Soal Ujian')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Edit Soal Ujian</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('inventory.ujian.soal.update', $soal->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nama Ujian</label>
                    <select name="id_soal" id="id_soal_edit" class="form-control" required>
                        <option value="">-- Pilih Nama Ujian --</option>
                        @foreach ($daftarUjian as $u)
                            <option value="{{ $u->id_soal }}" data-durasi="{{ $u->durasi }}" {{ $soal->id_soal == $u->id_soal ? 'selected' : '' }}>{{ $u->nm_ujian }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Durasi (menit)</label>
                    <input type="text" id="durasi_edit" class="form-control" value="{{ $soal->header->durasi ?? '' }}" readonly>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="pertanyaan" class="form-control" rows="6" required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Opsi A</label>
                    <input type="text" name="opsi_a" class="form-control" required value="{{ old('opsi_a', $soal->opsi_a) }}">
                </div>
                <div class="form-group">
                    <label>Opsi B</label>
                    <input type="text" name="opsi_b" class="form-control" required value="{{ old('opsi_b', $soal->opsi_b) }}">
                </div>
                <div class="form-group">
                    <label>Opsi C</label>
                    <input type="text" name="opsi_c" class="form-control" required value="{{ old('opsi_c', $soal->opsi_c) }}">
                </div>
                <div class="form-group">
                    <label>Kunci Jawaban</label>
                    <select name="jawaban_benar" class="form-control" required>
                        <option value="a" {{ $soal->jawaban_benar === 'a' ? 'selected' : '' }}>A</option>
                        <option value="b" {{ $soal->jawaban_benar === 'b' ? 'selected' : '' }}>B</option>
                        <option value="c" {{ $soal->jawaban_benar === 'c' ? 'selected' : '' }}>C</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('inventory.ujian.kelola', ['ujian_id' => $soal->id_soal]) }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        var selectEl = document.getElementById('id_soal_edit');
        var durasiEl = document.getElementById('durasi_edit');
        if (!selectEl || !durasiEl) return;

        selectEl.addEventListener('change', function() {
            var opt = selectEl.options[selectEl.selectedIndex];
            durasiEl.value = opt ? (opt.getAttribute('data-durasi') || '') : '';
        });
    })();
</script>
@endpush
