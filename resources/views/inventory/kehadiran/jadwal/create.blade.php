@extends('inventory.layouts.app')

@section('header', 'Jadwal Shift')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $isPemilik ? 'Jadwalkan Pegawai' : 'Ajukan Jadwal' }}</h3>
        </div>
        <form method="POST" action="{{ route('inventory.kehadiran.jadwal.store') }}">
            @csrf
            <div class="card-body">
                @if (!$isPemilik)
                    <div class="alert alert-info">Pengajuan ini akan menunggu approval Pemilik sebelum dianggap sah.</div>
                @endif

                <div class="form-group">
                    <label for="id_admin">Pegawai</label>
                    <select class="form-control @error('id_admin') is-invalid @enderror" name="id_admin" id="id_admin" required
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
                    <label for="id_shift">Shift</label>
                    <select class="form-control @error('id_shift') is-invalid @enderror" name="id_shift" id="id_shift" required>
                        <option value="">-- Pilih Shift --</option>
                        @foreach ($shiftList as $s)
                            <option value="{{ $s->id_shift }}" {{ old('id_shift') == $s->id_shift ? 'selected' : '' }}>
                                {{ $s->nama_shift }} ({{ $s->jam_masuk }} - {{ $s->jam_pulang }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_shift') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
                        <small class="text-muted">Isi sama dengan Tanggal Mulai untuk satu hari saja, atau rentang untuk jadwal mingguan/bulanan sekaligus.</small>
                        @error('tanggal_selesai') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.kehadiran.jadwal.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
