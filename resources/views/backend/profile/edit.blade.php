@extends('backend.layouts.app')

@section('title', 'Profil Saya')
@section('header', 'Halaman Profil Saya')

@section('content')
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h5 class="card-title mb-0">Foto Profil</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <img src="{{ $admin->foto ? asset('storage/' . $admin->foto) : asset('newadmin/assets/images/avatars/default-avatar.jpg') }}"
                            alt="Foto Profil" class="img-thumbnail rounded-circle"
                            style="width: 150px; height: 150px; object-fit: cover;">
                    </div>

                    <div class="mb-3 text-center">
                        <strong>{{ $admin->nama_lengkap }}</strong><br>
                        <span class="text-muted">{{ $admin->username }}</span>
                    </div>

                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Ganti Foto Profil</label>
                            <input type="file" name="foto" accept="image/*"
                                class="form-control @error('foto') is-invalid @enderror">
                            @error('foto')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan Foto</button>
                    </form>
                </div>
            </div>

            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Info Rekening Bank</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update-bank') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Bank/E-Wallet</label>
                            <input type="text" name="nama_bank"
                                class="form-control @error('nama_bank') is-invalid @enderror"
                                placeholder="Nama Bank/E-Wallet" value="{{ old('nama_bank', $admin->nama_bank) }}">
                            @error('nama_bank')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rekening Bank / E-Wallet</label>
                            <input type="text" name="rekening_bank"
                                class="form-control @error('rekening_bank') is-invalid @enderror"
                                placeholder="Rekening Bank" value="{{ old('rekening_bank', $admin->rekening_bank) }}">
                            @error('rekening_bank')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
