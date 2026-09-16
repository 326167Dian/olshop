@extends('inventory.layouts.app')

@section('header', 'Ajukan Cuti')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ajukan Cuti</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.cuti.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="id_admin">Pegawai</label>
                    <select class="form-control @error('id_admin') is-invalid @enderror" name="id_admin" required
                        {{ $isPemilik ? '' : 'disabled' }}>
                        @foreach ($pegawaiList as $p)
                            <option value="{{ $p->id_admin }}" {{ old('id_admin') == $p->id_admin ? 'selected' : '' }}>{{ $p->nama_lengkap }} ({{ $p->username }})</option>
                        @endforeach
                    </select>
                    @if (!$isPemilik)
                        <input type="hidden" name="id_admin" value="{{ $pegawaiList->first()->id_admin ?? '' }}">
                    @endif
                    @error('id_admin') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="jenis_cuti">Jenis Cuti</label>
                    <select class="form-control @error('jenis_cuti') is-invalid @enderror" name="jenis_cuti" required>
                        <option value="tahunan" {{ old('jenis_cuti', 'tahunan') == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                        <option value="sakit" {{ old('jenis_cuti') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ old('jenis_cuti') == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="lainnya" {{ old('jenis_cuti') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('jenis_cuti') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="tanggal_mulai">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                            class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                        @error('tanggal_mulai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="tanggal_selesai">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                            class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}" required>
                        @error('tanggal_selesai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="alasan">Alasan</label>
                    <textarea name="alasan" id="alasan" class="form-control @error('alasan') is-invalid @enderror" rows="3" required>{{ old('alasan') }}</textarea>
                    @error('alasan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.cuti.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
@endsection
