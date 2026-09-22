@extends('inventory.layouts.app')

@section('header', 'Kehadiran Pegawai')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kehadiran Hari Ini ({{ now()->translatedFormat('d F Y') }})</h3>
                </div>
                <div class="card-body">
                    @if ($jadwalHariIni->isEmpty())
                        <p class="text-muted mb-3">Tidak ada jadwal shift disetujui untuk Anda hari ini.</p>
                    @else
                        <table class="table table-sm table-bordered mb-3">
                            <thead>
                                <tr>
                                    <th>Shift</th>
                                    <th>Jam Masuk / Pulang Shift</th>
                                    <th>Jam Masuk Aktual</th>
                                    <th>Jam Pulang Aktual</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jadwalHariIni as $jadwal)
                                    @php $absen = $absensiHariIni->get($jadwal->id_jadwal); @endphp
                                    <tr>
                                        <td>{{ $jadwal->shift->nama_shift ?? '-' }}</td>
                                        <td>{{ $jadwal->shift->jam_masuk ?? '-' }} - {{ $jadwal->shift->jam_pulang ?? '-' }}</td>
                                        <td>{{ $absen->jam_masuk ?? '-' }}</td>
                                        <td>{{ $absen->jam_pulang ?? '-' }}</td>
                                        <td>
                                            @if ($absen)
                                                <span class="badge bg-{{ $absen->status == 'hadir' ? 'success' : ($absen->status == 'terlambat' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($absen->status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Belum Absen</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt"></i> Check-in/check-out akan meminta izin akses lokasi HP Anda untuk memverifikasi Anda berada di area apotek.</p>

                    <a href="{{ route('inventory.kehadiran.checkin.form') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Check-In
                    </a>

                    <form action="{{ route('inventory.kehadiran.checkout') }}" method="POST" class="js-geo-form d-inline">
                        @csrf
                        @php $absenBelumPulang = $absensiHariIni->first(fn($a) => !$a->jam_pulang); @endphp
                        <input type="hidden" name="id_absensi" value="{{ $absenBelumPulang->id_absensi ?? '' }}">
                        <button type="submit" class="btn btn-warning btn-sm" {{ $absenBelumPulang ? '' : 'disabled' }}>
                            <i class="fas fa-sign-out-alt"></i> Check-Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('inventory.kehadiran.jadwal.index') }}" class="text-decoration-none">
                <div class="card card-primary text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h5>Jadwal Shift</h5>
                        @if ($isPemilik)
                            <span class="badge bg-warning">{{ $menungguApprovalJadwal }} menunggu approval</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('inventory.kehadiran.absensi.index') }}" class="text-decoration-none">
                <div class="card card-primary text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                        <h5>Absensi</h5>
                        <span class="text-muted small">Rekap &amp; koreksi kehadiran</span>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('inventory.kehadiran.lembur.index') }}" class="text-decoration-none">
                <div class="card card-primary text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-business-time fa-2x mb-2"></i>
                        <h5>Lembur</h5>
                        @if ($isPemilik)
                            <span class="badge bg-warning">{{ $menungguApprovalLembur }} menunggu approval</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('inventory.kehadiran.cuti.index') }}" class="text-decoration-none">
                <div class="card card-primary text-center h-100">
                    <div class="card-body">
                        <i class="fas fa-umbrella-beach fa-2x mb-2"></i>
                        <h5>Cuti</h5>
                        <span class="text-muted small">Sisa kuota {{ $kuotaCutiTahun }}: <b>{{ $sisaKuotaCuti }} hari</b></span>
                        @if ($isPemilik)
                            <br><span class="badge bg-warning">{{ $menungguApprovalCuti }} menunggu approval</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    </div>

    @if ($isPemilik)
        <div class="row">
            <div class="col-md-4 mb-3">
                <a href="{{ route('inventory.kehadiran.shift.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-clock"></i> Kelola Master Shift
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('inventory.kehadiran.cuti.kuota.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sliders-h"></i> Kelola Kuota Cuti
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
@include('inventory.kehadiran._geolocation-script')
@endpush
