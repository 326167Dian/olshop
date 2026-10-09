@extends('inventory.layouts.app')

@section('header', 'Jadwal Shift')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $isPemilik ? 'Jadwalkan & Setujui Pegawai' : 'Ajukan Jadwal' }}</h3>
        </div>
        <div class="card-body">
            @if (!$isPemilik)
                <div class="alert alert-info">Pengajuan ini akan menunggu approval Pemilik sebelum dianggap sah. Tanggal yang sudah lewat tidak bisa diajukan lagi.</div>
            @else
                <div class="alert alert-info">
                    Centang = pegawai masuk shift itu. Hapus centang pada sel yang sudah terisi akan membatalkan/menghapus
                    jadwal tersebut. Klik <b>Simpan</b> untuk langsung menyetujui semua pengajuan yang masih tercentang.
                    Tanggal yang sudah lewat tidak bisa diubah lagi.
                </div>
            @endif

            <form method="GET" action="{{ route('inventory.kehadiran.jadwal.create') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-3 col-sm-6 form-group">
                    <label for="bulan">Bulan</label>
                    <select class="form-control" name="bulan" id="bulan">
                        @for ($b = 1; $b <= 12; $b++)
                            <option value="{{ $b }}" {{ $b == $bulan ? 'selected' : '' }}>
                                {{ \App\Models\GajiDetail::namaBulan($b) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 form-group">
                    <label for="tahun">Tahun</label>
                    <select class="form-control" name="tahun" id="tahun">
                        @for ($t = (int) date('Y') - 1; $t <= (int) date('Y') + 1; $t++)
                            <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 col-sm-12 form-group">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Tampilkan
                    </button>
                </div>
            </form>

            @if (!$isPemilik)
                <p class="text-muted small">
                    Centang shift &amp; tanggal yang ingin diajukan. Nama pegawai lain yang sudah mengajukan shift
                    yang sama (walaupun belum disetujui) ditampilkan di bawah kotak centang, supaya shift tidak
                    menumpuk terlalu banyak pegawai di hari yang sama.
                </p>
            @endif

            <form method="POST" action="{{ route('inventory.kehadiran.jadwal.store') }}">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                @foreach ($mingguGrid as $hariDalamMinggu)
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm text-center align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;">Shift</th>
                                    @foreach ($hariDalamMinggu as $hari)
                                        <th class="{{ $hari['dalam_bulan'] && !$hari['lewat'] ? '' : 'text-muted bg-light' }}">
                                            {{ $hari['tanggal']->translatedFormat('l') }}<br>
                                            <span class="fw-normal">{{ $hari['tanggal']->translatedFormat('d F Y') }}</span>
                                            @if ($hari['dalam_bulan'] && $hari['lewat'])
                                                <br><span class="badge bg-secondary">Sudah lewat</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shiftList as $shift)
                                    <tr>
                                        <td class="fw-bold text-start">{{ $shift->nama_shift }}</td>
                                        @foreach ($hariDalamMinggu as $hari)
                                            <td class="{{ $hari['dalam_bulan'] && !$hari['lewat'] ? '' : 'bg-light' }}">
                                                @if ($hari['dalam_bulan'] && !$hari['lewat'])
                                                    @if ($isPemilik)
                                                        @php $perAdmin = $hari['sel'][$shift->id_shift] ?? []; @endphp
                                                        <div class="text-start">
                                                            @foreach ($pegawaiAktif as $pegawai)
                                                                @php $status = $perAdmin[$pegawai->id_admin] ?? null; @endphp
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="cell_{{ $hari['tanggal']->toDateString() }}_{{ $shift->id_shift }}_{{ $pegawai->id_admin }}"
                                                                        name="pilihan[{{ $hari['tanggal']->toDateString() }}][{{ $shift->id_shift }}][{{ $pegawai->id_admin }}]"
                                                                        value="1" {{ $status ? 'checked' : '' }}>
                                                                    <label class="form-check-label small"
                                                                        for="cell_{{ $hari['tanggal']->toDateString() }}_{{ $shift->id_shift }}_{{ $pegawai->id_admin }}">
                                                                        {{ $pegawai->nama_lengkap }}
                                                                        @if ($status === 'diajukan')
                                                                            <span class="badge bg-warning">Diajukan</span>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        @php $sel = $hari['sel'][$shift->id_shift] ?? null; @endphp
                                                        @if ($sel && $sel['milik_sendiri'])
                                                            <input type="checkbox" checked disabled>
                                                            <div>
                                                                <span class="badge bg-{{ $sel['milik_sendiri']->status_approval === 'disetujui' ? 'success' : 'warning' }}">
                                                                    {{ ucfirst($sel['milik_sendiri']->status_approval) }}
                                                                </span>
                                                            </div>
                                                        @else
                                                            <input type="checkbox"
                                                                name="pilihan[{{ $hari['tanggal']->toDateString() }}][{{ $shift->id_shift }}][{{ Auth::guard('admin')->id() }}]"
                                                                value="1">
                                                        @endif

                                                        @if ($sel && !empty($sel['pegawai_lain']))
                                                            <div class="small text-muted mt-1">
                                                                @foreach ($sel['pegawai_lain'] as $nama)
                                                                    <div>{{ $nama }}</div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <div class="form-group mb-3">
                    <label for="catatan">Catatan (opsional, berlaku untuk jadwal baru yang dicentang)</label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="2"></textarea>
                </div>

                <a href="{{ route('inventory.kehadiran.jadwal.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
