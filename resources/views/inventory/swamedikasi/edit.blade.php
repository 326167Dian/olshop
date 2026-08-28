@extends('inventory.layouts.app')

@section('header', 'Edit Riwayat Swamedikasi')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Ubah Riwayat Pelanggan: {{ $riwayat->pelanggan->nm_pelanggan ?? '-' }}</h3>
        </div>
        <form method="POST" action="{{ route('inventory.swamedikasi.update', $riwayat->id) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tgl" class="form-control" required
                            value="{{ old('tgl', $riwayat->tgl?->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Diagnosa</label>
                    <textarea name="diagnosa" class="form-control" rows="2">{{ old('diagnosa', $riwayat->diagnosa) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Tindakan (Obat)</label>
                    <div id="obat-wrap-edit">
                        @forelse ($riwayat->obat as $ob)
                            @include('inventory.swamedikasi.partials.obat-row', ['obat' => $ob->toArray()])
                        @empty
                            @include('inventory.swamedikasi.partials.obat-row', ['obat' => ['aturan_pakai' => $riwayat->tindakan]])
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-default btn-sm obat-add-btn" data-target="#obat-wrap-edit">+Tambah
                        Obat</button>
                </div>
                <div class="form-group">
                    <label>Saran Konsultasi</label>
                    <textarea name="followup" class="form-control" rows="2">{{ old('followup', $riwayat->followup) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.swamedikasi.riwayat', ['id_pelanggan' => $riwayat->id_pelanggan]) }}"
                    class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('inventory.swamedikasi.partials.obat-scripts')
@endpush
