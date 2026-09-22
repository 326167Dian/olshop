<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>Update Data Diri | {{ $companySetting->nama_perusahaan }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-full-height d-flex flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="m-2">
                                <div class="d-flex justify-content-center mt-3">
                                    <div class="text-center logo">
                                        <img alt="logo" class="img-fluid" src="{{ asset('storage/' . $companySetting->logo) }}" style="max-height: 70px;">
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="fw-bolder">Update Data Diri</h3>
                                    <p class="text-muted">{{ $companySetting->nama_perusahaan }}</p>
                                </div>

                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif

                                <form method="POST" action="{{ route('reseller.profil.update') }}" class="mt-3">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group mb-3">
                                        <label>Nama Lengkap</label>
                                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $reseller->nama_lengkap) }}"
                                            class="form-control @error('nama_lengkap') is-invalid @enderror"
                                            placeholder="Nama lengkap sesuai identitas">
                                        @error('nama_lengkap')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>No HP/WhatsApp</label>
                                        <input type="text" name="no_hp" value="{{ old('no_hp', $reseller->no_hp) }}"
                                            class="form-control @error('no_hp') is-invalid @enderror"
                                            placeholder="Contoh: 081234567890">
                                        @error('no_hp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Alamat</label>
                                        <textarea name="alamat" rows="3"
                                            class="form-control @error('alamat') is-invalid @enderror"
                                            placeholder="Alamat lengkap">{{ old('alamat', $reseller->alamat) }}</textarea>
                                        @error('alamat')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Nama Bank</label>
                                        <input type="text" name="nama_bank" value="{{ old('nama_bank', $reseller->nama_bank) }}"
                                            class="form-control @error('nama_bank') is-invalid @enderror"
                                            placeholder="Contoh: BCA, Mandiri, BRI">
                                        @error('nama_bank')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Nomor Rekening</label>
                                        <input type="text" name="no_rekening" value="{{ old('no_rekening', $reseller->no_rekening) }}"
                                            class="form-control @error('no_rekening') is-invalid @enderror"
                                            placeholder="Nomor rekening bank di atas">
                                        @error('no_rekening')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 mt-2">Simpan</button>
                                    <a href="{{ route('reseller.home') }}" class="btn btn-outline-secondary w-100 mt-2">Kembali</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Vendors JS -->
    <script src="{{ asset('newadmin/assets/js/vendors.min.js') }}"></script>
    <!-- Core JS -->
    <script src="{{ asset('newadmin/assets/js/app.min.js') }}"></script>
</body>

</html>
