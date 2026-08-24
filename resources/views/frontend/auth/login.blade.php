<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>Masuk / Login | {{ $companySetting->nama_perusahaan }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-full-height d-flex flex-row align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="m-2">
                                <div class="d-flex justify-content-center mt-3">
                                    <div class="text-center logo">
                                        <img alt="logo" class="img-fluid" src="{{ asset('storage/' . $companySetting->logo) }}" style="max-height: 70px;">
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <h3 class="fw-bolder">Sign In</h3>
                                    <p class="text-muted">Masuk ke {{ $companySetting->nama_perusahaan }}</p>
                                </div>

                                @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif

                                <div class="row mt-4">
                                    <div class="col px-1">
                                        <a href="{{ route('google.login') }}" class="btn btn-outline-secondary w-100">
                                            <i class="fab fa-google me-2"></i> Masuk dengan Google
                                        </a>
                                    </div>
                                </div>
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
