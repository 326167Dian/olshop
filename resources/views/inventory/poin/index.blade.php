@extends('inventory.layouts.app')

@section('header', 'Poin Member')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Input Ketentuan Poin</h3>
        </div>
        <form action="{{ route('inventory.poin.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nm_outlet">Nama Outlet</label>
                    <input type="text" class="form-control" name="nm_outlet" id="nm_outlet"
                        placeholder="Nama Outlet"
                        value="{{ old('nm_outlet', $poin->nm_outlet ?? $namaOutletDefault) }}">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_outlet" id="is_outlet" value="1"
                        {{ old('is_outlet', $poin->is_outlet ?? '') === 'ya' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_outlet">Gunakan Nama Outlet Pada Kartu Member</label>
                </div>

                <div class="form-group">
                    <label for="min_penjualan">Minimal Penjualan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" min="0" class="form-control" id="min_penjualan" name="min_penjualan"
                            placeholder="Amount" value="{{ old('min_penjualan', $poin->min_penjualan ?? '') }}">
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_kelipatan" id="is_kelipatan" value="1"
                        {{ old('is_kelipatan', $poin->is_kelipatan ?? '') === 'ya' ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_kelipatan">Berlaku kelipatan</label>
                </div>

                <div class="form-group">
                    <label for="poin_member">Poin yang diberikan</label>
                    <div class="input-group">
                        <input type="number" min="0" class="form-control" id="poin_member" name="poin_member"
                            placeholder="Amount" value="{{ old('poin_member', $poin->poin_pelanggan ?? '') }}">
                        <span class="input-group-text">Poin</span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('inventory.pelanggan.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
@endsection
