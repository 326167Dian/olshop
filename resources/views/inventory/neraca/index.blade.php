@extends('inventory.layouts.app')

@section('header', 'Neraca Laba Rugi')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Neraca Laba Rugi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Awal</label>
                        <input type="date" class="form-control" id="tgl_awal" value="{{ $tglAwal }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tgl_akhir" value="{{ $tglAkhir }}" required>
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" id="btnSubmit">SUBMIT</button>
                @if ($hasil)
                    <button type="button" class="btn btn-success" id="btnPrint">PRINT</button>
                @endif
            </div>

            @if ($hasil)
                <hr>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="3">NERACA LABA RUGI Range: {{ $tglAwal }} s/d {{ $tglAkhir }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" width="50px">1.</td>
                                <td width="250px">
                                    Penjualan<br>
                                    Penjualan Reguler<br>
                                    Penjualan Member<br>
                                    Penjualan Marketplace
                                </td>
                                <td>
                                    Rp {{ number_format($hasil['penjualan'], 0, ',', '.') }}<br>
                                    Rp {{ number_format($hasil['reguler'], 0, ',', '.') }}<br>
                                    Rp {{ number_format($hasil['member'], 0, ',', '.') }}<br>
                                    Rp {{ number_format($hasil['marketplace'], 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">2.</td>
                                <td>Pembelian Cash</td>
                                <td>Rp {{ number_format($hasil['pembelianCash'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">3.</td>
                                <td>Piutang<br>Total Penjualan Belum Dibayar.</td>
                                <td>Rp {{ number_format($hasil['piutang'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">4.</td>
                                <td>Hutang<br>Total Pembelian Belum Dibayar.</td>
                                <td>Rp {{ number_format($hasil['hutang'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">5.</td>
                                <td>Total Asset Lancar</td>
                                <td>Rp {{ number_format($hasil['asetLancar'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">6.</td>
                                <td>Total Asset Tidak Lancar</td>
                                <td>Rp {{ number_format($hasil['asetTidakLancar'], 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-center">7.</td>
                                <td>Neraca Laba/Rugi</td>
                                <td>Rp {{ number_format($hasil['neraca'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnSubmit').addEventListener('click', function() {
        var tglAwal = document.getElementById('tgl_awal').value;
        var tglAkhir = document.getElementById('tgl_akhir').value;
        if (!tglAwal || !tglAkhir) { alert('Tanggal awal dan akhir wajib diisi.'); return; }
        var qs = new URLSearchParams({ tgl_awal: tglAwal, tgl_akhir: tglAkhir }).toString();
        window.location.href = "{{ route('inventory.neraca.index') }}?" + qs;
    });

    @if ($hasil)
    document.getElementById('btnPrint').addEventListener('click', function() {
        var qs = new URLSearchParams({
            tgl_awal: "{{ $tglAwal }}",
            tgl_akhir: "{{ $tglAkhir }}",
        }).toString();
        window.open("{{ route('inventory.neraca.cetak') }}?" + qs, '_blank');
    });
    @endif
</script>
@endpush
