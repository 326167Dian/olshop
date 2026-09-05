@extends('inventory.layouts.app')

@section('header', 'Laporan Penjualan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Tampil Penjualan Produk Shift {{ $shiftLabel }} Tanggal {{ $tglAwal }} s/d {{ $tglAkhir }} -- Petugas: {{ $petugasNama }}
            </h3>
        </div>
        <div class="card-body">
            @forelse ($transaksi as $i => $trx)
                <table class="table table-sm table-borderless mb-1" style="max-width:500px">
                    <tr>
                        <td style="width:40%">No</td>
                        <td>:</td>
                        <td>{{ $i + 1 }}</td>
                    </tr>
                    <tr>
                        <td>Nama Pelanggan</td>
                        <td>:</td>
                        <td>{{ $trx->nm_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td>Kode Transaksi</td>
                        <td>:</td>
                        <td>{{ $trx->kd_trkasir }}</td>
                    </tr>
                    <tr>
                        <td>Metode Bayar</td>
                        <td>:</td>
                        <td>{{ optional($trx->caraBayar)->nm_carabayar }}</td>
                    </tr>
                    <tr>
                        <td>Waktu Transaksi</td>
                        <td>:</td>
                        <td>{{ $trx->waktu_trx?->translatedFormat('d-F-Y H:i:s') }}</td>
                    </tr>
                </table>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Nama Barang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-center">Harga Jual</th>
                                <th class="text-center">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trx->detail as $j => $d)
                                <tr>
                                    <td>{{ $j + 1 }}</td>
                                    <td>{{ $d->nmbrg_dtrkasir }}</td>
                                    <td class="text-center">{{ $d->qty_dtrkasir }}</td>
                                    <td class="text-center">{{ $d->sat_dtrkasir }}</td>
                                    <td class="text-end">{{ number_format($d->hrgjual_dtrkasir, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($d->qty_dtrkasir * $d->hrgjual_dtrkasir, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">Sub Total</th>
                                <th class="text-end">{{ number_format($trx->subtotal, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-center">Diskon</th>
                                <th class="text-end">{{ number_format($trx->diskon, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-center">TOTAL</th>
                                <th class="text-end">{{ number_format($trx->ttl_trkasir, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <hr>
            @empty
                <p>Tidak ada transaksi pada rentang tanggal/shift ini.</p>
            @endforelse

            @foreach ($breakdown as $b)
                <p class="fw-bold">Pembayaran {{ $b['nm_carabayar'] }} : Rp. {{ number_format($b['total'], 0, ',', '.') }}</p>
            @endforeach
            <p class="fw-bold fs-5">GRAND TOTAL PENJUALAN SHIFT {{ $shiftLabel }} : Rp. {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </div>
    </div>
@endsection
