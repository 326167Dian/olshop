@extends('inventory.layouts.app')

@section('header', 'Riwayat Swamedikasi')

@section('content')
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Swamedikasi dan Riwayat Pelanggan: {{ $pelanggan->nm_pelanggan }}</h3>
            <a class="btn btn-sm btn-danger"
                href="{{ route('inventory.swamedikasi.export-pdf', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}"
                target="_blank">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
        </div>
        <div class="card-body">
            <a class="btn btn-sm btn-secondary mb-3" href="{{ route('inventory.pelanggan.index') }}">
                <i class="fas fa-arrow-left"></i> Kembali ke Pelanggan
            </a>

            <h5 class="fw-bold">Tambah Riwayat</h5>
            <form method="POST" action="{{ route('inventory.swamedikasi.store') }}">
                @csrf
                <input type="hidden" name="id_pelanggan" value="{{ $pelanggan->id_pelanggan }}">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tgl" class="form-control" required value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Diagnosa</label>
                    <textarea name="diagnosa" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Tindakan (Obat)</label>
                    <div id="obat-wrap">
                        @include('inventory.swamedikasi.partials.obat-row', ['obat' => []])
                    </div>
                    <button type="button" class="btn btn-default btn-sm obat-add-btn" data-target="#obat-wrap">+Tambah
                        Obat</button>
                </div>
                <div class="form-group">
                    <label>Saran Konsultasi</label>
                    <textarea name="followup" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-info">SIMPAN</button>
                </div>
            </form>

            <hr>
            <form method="GET" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="id_pelanggan" value="{{ $pelanggan->id_pelanggan }}">
                <div class="col-auto">
                    <label class="form-label mb-0">Tanggal Start</label>
                    <input type="date" name="tgl_awal" class="form-control form-control-sm" value="{{ $tglAwal }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Tanggal Finish</label>
                    <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="{{ $tglAkhir }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-info">Cari Data</button>
                    <a class="btn btn-sm btn-outline-secondary"
                        href="{{ route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $pelanggan->id_pelanggan]) }}">Reset</a>
                </div>
            </form>

            <h4>Riwayat Sebelumnya</h4>
            <table class="table table-auto table-sm table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Diagnosa</th>
                        <th>Tindakan</th>
                        <th>Saran Konsultasi</th>
                        <th>Tgl Follow Up</th>
                        <th>Follow Up oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->tgl?->format('d-m-Y') }}</td>
                            <td>{{ $row->diagnosa }}</td>
                            <td>
                                @if ($row->obat->isNotEmpty())
                                    @foreach ($row->obat as $ob)
                                        {{ $ob->nm_barang }}@if ($ob->aturan_pakai) - {{ $ob->aturan_pakai }} @endif<br>
                                    @endforeach
                                @else
                                    {{ $row->tindakan }}
                                @endif
                            </td>
                            <td>{{ $row->followup }}</td>
                            <td class="tgl-followup-cell" data-id="{{ $row->id }}">
                                @if ($row->tgl_followup)
                                    {{ $row->tgl_followup->format('d-m-Y H:i') }}
                                @else
                                    <button type="button" data-id="{{ $row->id }}" class="btn-followup btn btn-danger btn-xs">
                                        Klik untuk followup
                                    </button>
                                @endif
                            </td>
                            <td>{{ $row->followup_by }}</td>
                            <td>
                                <a href="{{ route('inventory.swamedikasi.edit', $row->id) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('inventory.swamedikasi.destroy', $row->id) }}" method="POST"
                                    class="d-inline" id="delete-form-{{ $row->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        onclick="confirmDelete('delete-form-{{ $row->id }}', 'riwayat ini')"
                                        class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @include('inventory.swamedikasi.partials.obat-scripts')

    <script>
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-followup')) {
                return;
            }
            var id = e.target.dataset.id;
            var cell = document.querySelector('.tgl-followup-cell[data-id="' + id + '"]');
            fetch('{{ url('inventory/swamedikasi') }}/' + id + '/followup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.status === 'success') {
                        cell.textContent = data.tgl_followup;
                    }
                });
        });
    </script>
@endpush
