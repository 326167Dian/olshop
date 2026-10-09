@extends('inventory.layouts.app')

@section('header', 'Skema Kehadiran Tiap Bulan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Skema Kehadiran Tiap Bulan</h3>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.kehadiran.index') }}">Kembali</a>

            <form method="GET" action="{{ route('inventory.kehadiran.skema.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-3 col-sm-6 form-group">
                    <label for="bulan_skema">Bulan</label>
                    <select class="form-control" name="bulan_skema" id="bulan_skema">
                        @for ($b = 1; $b <= 12; $b++)
                            <option value="{{ $b }}" {{ $b == $bulanSkema ? 'selected' : '' }}>
                                {{ \App\Models\GajiDetail::namaBulan($b) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 form-group">
                    <label for="tahun_skema">Tahun</label>
                    <select class="form-control" name="tahun_skema" id="tahun_skema">
                        @for ($t = (int) date('Y') - 2; $t <= (int) date('Y') + 1; $t++)
                            <option value="{{ $t }}" {{ $t == $tahunSkema ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 col-sm-12 form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </form>

            <h5 class="mb-3">Jadwal Kehadiran Pegawai {{ \App\Models\GajiDetail::namaBulan($bulanSkema) }} {{ $tahunSkema }}</h5>

            @foreach ($mingguSkema as $hariDalamMinggu)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Shift</th>
                                @foreach ($hariDalamMinggu as $hari)
                                    <th class="{{ $hari['dalam_bulan'] ? '' : 'text-muted bg-light' }}">
                                        {{ $hari['tanggal']->translatedFormat('l') }}<br>
                                        <span class="fw-normal">{{ $hari['tanggal']->translatedFormat('d F Y') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shiftListSkema as $shift)
                                <tr>
                                    <td class="fw-bold text-start">{{ $shift->nama_shift }}</td>
                                    @foreach ($hariDalamMinggu as $hari)
                                        <td class="{{ $hari['dalam_bulan'] ? '' : 'bg-light' }}">
                                            @if ($hari['dalam_bulan'])
                                                @foreach ($hari['nama_per_shift'][$shift->id_shift] ?? [] as $nama)
                                                    <div>{{ $nama }}</div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
