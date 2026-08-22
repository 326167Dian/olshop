@extends('backend.layouts.app')

@section('title', 'Pengaturan Profil Perusahaan')
@section('header', 'Halaman Pengaturan Profil Perusahaan')

@section('content')
<div class="container-fluid p-3">
    <form
        action="{{ isset($companySetting) ? route('company-setting.update', $companySetting->id) : route('company-setting.store') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($companySetting))
        @method('PUT')
        @endif

        <div class="row">
            {{-- Card Kiri --}}
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Identitas Perusahaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="nama_perusahaan" class="form-control"
                                value="{{ old('nama_perusahaan', $companySetting->nama_perusahaan ?? '') }}" required>
                            @error('nama_perusahaan')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Perusahaan</label>
                            <textarea name="deskripsi" class="form-control"
                                rows="3">{{ old('deskripsi', $companySetting->deskripsi ?? '') }}</textarea>
                            @error('deskripsi')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Perusahaan</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $companySetting->email ?? '') }}">
                            @error('email')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo Perusahaan</label>
                            @if(isset($companySetting->logo))
                            <div class="mb-2 text-center">
                                <img src="{{ asset('storage/' . $companySetting->logo) }}" alt="Logo"
                                    class="img-thumbnail" style="max-height: 182px; max-width: auto;">
                            </div>
                            @endif
                            <input type="file" name="logo" class="form-control">
                            @error('logo')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Card Kanan --}}
            <div class="col-md-6 mt-4 mt-md-0">
                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kontak & Lokasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">No Telepon</label>
                            <input name="telepon" class="form-control"
                                value="{{ old('telepon', $companySetting->telepon ?? '') }}">
                            @error('telepon')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website Perusahaan</label>
                            <input name="website" class="form-control"
                                value="{{ old('website', $companySetting->website ?? '') }}">
                            @error('website')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control"
                                rows="3">{{ old('alamat', $companySetting->alamat ?? '') }}</textarea>
                            @error('alamat')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Peta Lokasi (iframe Google Maps)</label>
                            <textarea name="peta_lokasi" class="form-control"
                                rows="5">{{ old('peta_lokasi', $companySetting->peta_lokasi ?? '') }}</textarea>
                            @error('peta_lokasi')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="align-middle" data-feather="save"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-4 mt-md-0">
                <div class="card card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ketentuan Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="catatan">Tulis ketentuan pengiriman</label>
                            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                                rows="7" id="catatan"
                                placeholder="Isi catatan di sini...">{{ old('catatan', $companySetting->catatan ?? '') }}</textarea>

                            @error('catatan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi CKEditor
        ClassicEditor
            .create(document.querySelector('#catatan'))
            .catch(error => {
                console.error(error);
            });
    });
</script>

@endpush