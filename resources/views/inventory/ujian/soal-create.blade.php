@extends('inventory.layouts.app')

@section('header', 'Tambah Soal Ujian')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Tambah Soal Ujian</h3>
        </div>
        <div class="card-body">
            @if (!$prefillUjian)
                <div class="alert alert-warning">Belum ada Nama Ujian. Silakan simpan Nama Ujian terlebih dahulu pada
                    halaman Kelola Soal Ujian.</div>
            @endif

            <form method="POST" action="{{ route('inventory.ujian.soal.store') }}">
                @csrf
                @if ($prefillUjian)
                    <input type="hidden" name="id_soal" value="{{ $prefillUjian->id_soal }}">
                    <div class="form-group">
                        <label>Nama Ujian</label>
                        <input type="text" class="form-control" value="{{ $prefillUjian->nm_ujian }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Durasi (menit)</label>
                        <input type="text" class="form-control" value="{{ $prefillUjian->durasi }}" readonly>
                    </div>
                @endif
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="pertanyaan" class="form-control" rows="6" required>{{ old('pertanyaan') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Opsi A</label>
                    <input type="text" name="opsi_a" class="form-control" required value="{{ old('opsi_a') }}">
                </div>
                <div class="form-group">
                    <label>Opsi B</label>
                    <input type="text" name="opsi_b" class="form-control" required value="{{ old('opsi_b') }}">
                </div>
                <div class="form-group">
                    <label>Opsi C</label>
                    <input type="text" name="opsi_c" class="form-control" required value="{{ old('opsi_c') }}">
                </div>
                <div class="form-group">
                    <label>Kunci Jawaban</label>
                    <select name="jawaban_benar" class="form-control" required>
                        <option value="a">A</option>
                        <option value="b">B</option>
                        <option value="c">C</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" {{ $prefillUjian ? '' : 'disabled' }}>Simpan</button>
                    <a href="{{ route('inventory.ujian.kelola', $prefillUjian ? ['ujian_id' => $prefillUjian->id_soal] : []) }}"
                        class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
