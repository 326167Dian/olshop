@extends('frontend.layouts.index')
@section('content')
<!-- section -->
<div class="section">
    <!-- container -->
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="billing-details">
                    <div class="section-title">
                        <h3 class="title">{{ $judul }}</h3>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            {{-- Alert Sukses --}}
                            @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <strong>{{ session('success') }}</strong>
                            </div>
                            @endif

                            {{-- Alert Error --}}
                            @if (session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <strong>{{ session('error') }}</strong>
                            </div>
                            @endif

                            {{-- Alert Error --}}
                            @if (session('msgError'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <strong>{{ session('msgError') }}</strong>
                            </div>
                            @endif
                        </div>

                        {{-- Form Update Akun --}}
                        <form action="{{ route('customer.updateakun', $edit->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="col-md-12">
                                {{-- Nama --}}
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" name="name" value="{{ old('name', $edit->name) }}"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="Masukkan Nama">
                                    @error('name')
                                    <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="{{ old('email', $edit->email) }}"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Masukkan Email">
                                    @error('email')
                                    <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Alamat</label>
                                    <input type="text" name="alamat" value="{{ old('alamat', $edit->alamat) }}"
                                        class="form-control @error('alamat') is-invalid @enderror"
                                        placeholder="Masukkan alamat">
                                    @error('alamat')
                                    <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>No Telephone</label>
                                    <input type="number" name="no_tlp" value="{{ old('no_tlp', $edit->no_tlp) }}"
                                        class="form-control @error('no_tlp') is-invalid @enderror"
                                        placeholder="Masukkan Nomor Telephone Anda">
                                    @error('no_tlp')
                                    <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="form-group">
                                    <label>Password Baru (opsional)</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Biarkan kosong jika tidak diganti">
                                    @error('password')
                                    <div class="invalid-feedback alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tombol Submit --}}
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="primary-btn">Simpan</button>
                            </div>
                        </form>
                        {{-- End Form --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection