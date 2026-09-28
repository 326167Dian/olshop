@extends('inventory.layouts.app')

@section('header', 'Rincian Kehadiran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Rincian Kehadiran {{ $admin->nama_lengkap }} -- {{ $tglAwal }} s/d {{ $tglAkhir }}</h3>
            <small class="text-muted">Kolom Nilai sudah termasuk lembur yang masih berstatus "Diajukan" (lihat badge per baris) supaya bisa ditinjau sebelum disetujui -- Slip Gaji sungguhan tetap hanya membayar lembur yang sudah disetujui pemilik.</small>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-primary-subtle">
                        <div class="text-muted small">Jumlah Hari (hadir + cuti disetujui)</div>
                        <div class="fs-5 fw-bold">{{ $rincian['jumlah_hari'] }}</div>
                    </div>
                </div>
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-success-subtle">
                        <div class="text-muted small">Total Jam Lembur Disetujui</div>
                        <div class="fs-5 fw-bold">{{ $rincian['total_lembur_disetujui_jam'] }} jam</div>
                    </div>
                </div>
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-warning-subtle">
                        <div class="text-muted small">Jam Lembur Masih Diajukan</div>
                        <div class="fs-5 fw-bold">{{ $rincian['total_lembur_diajukan_jam'] }} jam</div>
                        <small class="text-muted">Belum ikut ke Slip Gaji sampai disetujui pemilik.</small>
                    </div>
                </div>
                <div class="col-sm-4 mb-2">
                    <div class="p-3 rounded bg-danger-subtle">
                        <div class="text-muted small">Total Potongan Telat</div>
                        <div class="fs-5 fw-bold">Rp {{ number_format($rincian['total_potongan_telat'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tabel-rincian" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="30" class="text-center">No</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Shift &amp; Jam</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Kontribusi Jumlah Hari</th>
                            <th class="text-center">Lembur</th>
                            <th class="text-center">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rincian['hari'] as $i => $h)
                            <tr>
                                <td width="30">{{ $i + 1 }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($h['tanggal'])->translatedFormat('d M Y (D)') }}</td>
                                <td>
                                    @forelse ($h['shift'] as $s)
                                        <div class="mb-1">
                                            {{ $s['nama_shift'] }}: {{ $s['jam_masuk'] }} - {{ $s['jam_pulang'] }}
                                            @if ($s['ekstra'])
                                                <span class="badge bg-info">Shift Tambahan</span>
                                            @endif
                                            @if ($s['menit_terlambat'] > 0)
                                                <br>
                                                <span class="badge bg-danger">
                                                    Telat {{ $s['menit_terlambat'] }} menit &minus; Rp {{ number_format($s['potongan_telat'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    @if ($h['status'] === 'Hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif ($h['status'] === 'Cuti (Disetujui)')
                                        <span class="badge bg-primary">Cuti (Disetujui)</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Hadir</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $h['kontribusi_hari'] }}</td>
                                <td>
                                    @forelse ($h['shift'] as $s)
                                        @if ($s['jam_lembur'] !== null)
                                            <div class="mb-1">
                                                {{ $s['jam_lembur'] }} jam
                                                <span class="text-muted">({{ $s['tipe_lembur'] === 'per_shift' ? '1x Tarif Lembur 1 Shift' : 'per jam (manual)' }})</span>
                                                @if ($s['status_approval_lembur'] === 'disetujui')
                                                    <span class="badge bg-success">Disetujui</span>
                                                @elseif ($s['status_approval_lembur'] === 'ditolak')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Diajukan</span>
                                                @endif
                                            </div>
                                        @endif
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse
                                </td>
                                <td class="text-end">Rp {{ number_format($h['nilai'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="font-weight:bold; background-color:aqua; font-size:large;">
                        <tr>
                            <td colspan="4" class="text-right">Total Jumlah Hari</td>
                            <td class="text-center">{{ $rincian['jumlah_hari'] }}</td>
                            <td></td>
                            <td class="text-end">Rp {{ number_format($rincian['total_nilai'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-rincian').DataTable({ order: [], paging: false });
    });
</script>
@endpush
