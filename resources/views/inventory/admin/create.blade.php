@extends('inventory.layouts.app')

@section('header', 'Tambah Operator')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Operator</h3>
        </div>

        <form action="{{ route('inventory.admin.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username"
                            class="form-control @error('username') is-invalid @enderror" placeholder="Username"
                            value="{{ old('username') }}">
                        @error('username') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="password">Password</label>
                        <input type="text" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="akses_level">Akses Level</label>
                        <select class="form-control" name="akses_level" id="akses_level">
                            <option value="pemilik" {{ old('akses_level') == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                            <option value="petugas" {{ old('akses_level', 'petugas') == 'petugas' ? 'selected' : '' }}>Petugas</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap"
                            class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama Lengkap"
                            value="{{ old('nama_lengkap') }}">
                        @error('nama_lengkap') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="no_telp">Telp/HP</label>
                        <input type="text" name="no_telp" id="no_telp"
                            class="form-control @error('no_telp') is-invalid @enderror" placeholder="No Telepon"
                            value="{{ old('no_telp') }}">
                        @error('no_telp') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Blokir</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="blokir" id="blokir_y" value="Y" {{ old('blokir') == 'Y' ? 'checked' : '' }}>
                            <label class="form-check-label" for="blokir_y">Y</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="blokir" id="blokir_n" value="N" {{ old('blokir', 'N') == 'N' ? 'checked' : '' }}>
                            <label class="form-check-label" for="blokir_n">N</label>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    @foreach ($groups as $groupName => $items)
                        <div class="col-md-3 form-group">
                            <label class="fw-bold">{{ $groupName }}</label>
                            @foreach ($items as $column => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="{{ $column }}"
                                        id="{{ $column }}" value="Y" checked>
                                    <label class="form-check-label" for="{{ $column }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('inventory.admin.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
