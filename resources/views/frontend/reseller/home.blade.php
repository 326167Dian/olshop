<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="{{ asset('storage/' . $companySetting->logo) }}" type="image/png">
    <title>Reseller | {{ $companySetting->nama_perusahaan }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('newadmin/assets/css/app.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                class="btn btn-sm btn-outline-danger">
                <i class="fas fa-unlock-alt"></i> Logout
            </a>
            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>

        <div class="d-flex justify-content-center mb-3">
            <div class="text-center logo">
                <img alt="logo" class="img-fluid" src="{{ asset('storage/' . $companySetting->logo) }}" style="max-height: 60px;">
            </div>
        </div>

        <div class="text-center mb-4">
            <h3 class="fw-bolder">Halaman Reseller</h3>
            <p class="text-muted">Halo, {{ $reseller->nama_lengkap }}.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Link referral --}}
        <div class="card mb-4">
            <div class="card-body">
                <p class="text-muted mb-1">Bagikan link ini ke calon pembeli. Setiap orang yang mendaftar lewat link ini akan otomatis tercatat sebagai ajakan Anda.</p>
                <div class="input-group">
                    <input type="text" id="reseller-link" class="form-control" readonly
                        value="{{ url('/' . $reseller->id) }}">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyResellerLink()">
                        <i class="fas fa-copy"></i> Salin
                    </button>
                </div>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Omzet Bulan Ini</p>
                        <h4 class="fw-bolder mb-0">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Komisi Bulan Ini ({{ rtrim(rtrim(number_format($komisiPersen, 2, ',', '.'), '0'), ',') }}%)</p>
                        <h4 class="fw-bolder mb-0 text-success">Rp {{ number_format($komisiBulanIni, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Pelanggan Direkrut</p>
                        <h4 class="fw-bolder mb-0">{{ $pelanggan->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar pelanggan --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pelanggan yang Anda Rekrut</h5>
            </div>
            <div class="card-body">
                @if ($pelanggan->isEmpty())
                    <p class="text-muted mb-0">Belum ada pelanggan yang mendaftar lewat link referral Anda.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                    <th>Total Pesanan</th>
                                    <th>Total Belanja</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pelanggan as $index => $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td>{{ $item->no_tlp ?? '-' }}</td>
                                    <td>{{ $item->total_pesanan }}</td>
                                    <td>Rp {{ number_format($item->total_belanja ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('reseller.pelanggan.detail', $item->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Core Vendors JS -->
    <script src="{{ asset('newadmin/assets/js/vendors.min.js') }}"></script>
    <!-- Core JS -->
    <script src="{{ asset('newadmin/assets/js/app.min.js') }}"></script>

    <script>
        function copyResellerLink() {
            var input = document.getElementById('reseller-link');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value);
        }
    </script>
</body>

</html>
