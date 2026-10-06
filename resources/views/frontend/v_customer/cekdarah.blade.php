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
                        </div>

                        @if ($pelanggan)
                            {{-- Sudah tertaut: tampilkan riwayat --}}
                            <div class="col-md-12">
                                <p class="text-muted">
                                    Akun Anda terhubung dengan data pelanggan <strong>{{ $pelanggan->nm_pelanggan }}</strong>.
                                </p>

                                @if ($riwayat->isEmpty())
                                    <p>Belum ada riwayat cek darah.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Glukosa Puasa</th>
                                                    <th>Glukosa 2 PP</th>
                                                    <th>Asam Urat</th>
                                                    <th>Kolesterol</th>
                                                    <th>Tensi</th>
                                                    <th>Petugas</th>
                                                    <th>Info Detail</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($riwayat as $r)
                                                    <tr>
                                                        <td>{{ $r->waktu?->format('d-m-Y H:i') }}</td>
                                                        <td>{{ $r->gula }} mg/dl</td>
                                                        <td>{{ $r->gula_2pp }} mg/dl</td>
                                                        <td>{{ $r->asamurat }} mg/dl</td>
                                                        <td>{{ $r->kolesterol }} mg/dl</td>
                                                        <td>{{ $r->tensi }} mmHg</td>
                                                        <td>{{ $r->petugas }}</td>
                                                        <td>
                                                            <a href="{{ route('customer.cekdarah.detail', $r->id_cekdarah) }}"
                                                                class="btn btn-sm btn-info">
                                                                Tampil
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- Belum tertaut: pilih nama dari daftar pelanggan apotek --}}
                            <div class="col-md-12">
                                <p class="text-muted">
                                    Akun Anda belum terhubung dengan data pelanggan apotek, jadi riwayat cek darah belum
                                    bisa ditampilkan. Kalau Anda pernah cek darah langsung di apotek, pilih nama Anda di
                                    bawah ini untuk menautkan akun.
                                </p>

                                @if ($pelangganList->isEmpty())
                                    <p>Tidak ada data pelanggan yang bisa ditautkan saat ini. Silakan hubungi apotek.</p>
                                @else
                                    <form action="{{ route('customer.cekdarah.link') }}" method="POST" class="mb-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="id_pelanggan">Nama Saya</label>
                                            <select name="id_pelanggan" id="id_pelanggan" class="form-control" required>
                                                <option value="" selected disabled>-- Pilih Nama --</option>
                                                @foreach ($pelangganList as $p)
                                                    <option value="{{ $p->id_pelanggan }}">{{ $p->nm_pelanggan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="primary-btn mt-2">Hubungkan Akun</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
