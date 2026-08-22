@extends('backend.layouts.app')
@section('title', 'Update Status Pesanan')

@section('header', 'Update Status Pesanan')

@section('content')
<!-- template -->
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card mb-3">
        <div class="card-body">
            <div class="invoice-title text-center mb-3">
                <h2>Detail Pesanan #{{ $order->kode_pesanan }}</h2>
                <strong>Tanggal:</strong> {{ $order->created_at->format('d M Y H:i') }}
            </div>
            <form action="{{ route('pesanan.proses.update', $order->id) }}" method="post">
                @csrf
                @method('PUT')
                <hr>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <h5>Pelanggan</h5>
                        <address>
                            Nama: {{ $order->user->name ?? '-' }}<br>
                            Email: {{ $order->user->email ?? '-' }}<br>
                            Hp: {{ $order->user->no_tlp ?? '-' }}<br>
                            Alamat: {{ $order->user->alamat ?? '-' }}<br>
                        </address>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 text-right">
                        @if (!empty($order->layanan_pengiriman))
                        <h5>Ongkos Kirim</h5>
                        @endif

                        <address>
                            {{-- Kurir: {{ $order->kurir }}<br> --}}
                            Layanan: {{ empty($order->layanan_pengiriman) ? 'Ambil di tempat' :
                            $order->layanan_pengiriman }}<br>
                            {{-- Estimasi: {{ $order->estimasi_ongkir }} Hari<br>
                            Berat: {{ $order->total_berat }} Gram<br> --}}
                        </address>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <h5>Produk</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="2">Produk</th>
                                        <th class="text-center">Harga</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Total</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $totalHarga = 0;
                                    $totalBerat = 0;
                                    @endphp
                                    @foreach ($order->orderItems as $item)
                                    @php
                                    $totalHarga += $item->harga * $item->quantity;
                                    $totalBerat += $item->produk->berat * $item->quantity;
                                    @endphp
                                    <tr>
                                        <td align="center" style="width: 200px;">
                                            <img src="{{ asset('storage/' . ($item->produk->image)) }}" alt=""
                                                style="width: 100px; height: auto;">
                                        </td>

                                        <td class="details">
                                            <a>{{ $item->produk->nm_barang }}
                                                @if($item->produk->category)
                                                #{{ $item->produk->category->name }}
                                                @endif
                                            </a>
                                            <ul>
                                                {{-- <li>
                                                    <span>Jenis Obat: {{ $item->produk->jenisobat }}</span>
                                                </li> --}}
                                                <li>
                                                    <span>Stok Produk : {{ $item->produk->stok_barang }} {{
                                                        $item->produk->sat_barang }}</span>
                                                </li>
                                            </ul>
                                        </td>
                                        <td class="price text-center">Rp.
                                            {{ number_format($item->harga, 0, ',', '.') }}</td>
                                        <td class="qty text-center">
                                            <a> {{ $item->quantity }} </a>
                                        </td>
                                        <td class="total text-center">Rp.
                                            {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="empty" rowspan="3" colspan="3"></th>
                                        <td>Subtotal</td>
                                        <td colspan="2">Rp. {{ number_format($totalHarga, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Ongkos Kirim</td>
                                        <td colspan="2">
                                            Rp. {{ number_format($order->biaya_ongkir, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>TOTAL BAYAR</th>
                                        <th colspan="2" class="total">Rp.
                                            {{ number_format($totalHarga + $order->biaya_ongkir, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        {{-- <div class="form-group">
                            <label>No. Resi</label>
                            <input type="text" name="noresi" value="{{ old('noresi', $order->kode_pesanan) }}"
                                class="form-control @error('noresi') is-invalid @enderror"
                                placeholder="Masukkan Nomor Resi" readonly>
                            @error('noresi')
                            <span class="invalid-feedback alert-danger" role="alert">
                                {{ $message }}
                            </span>
                            @enderror
                        </div> --}}
                        <div class="form-group">
                            <label>Kode Pesanan</label>
                            <input type="text" name="kode_pesanan"
                                value="{{ old('kode_pesanan', $order->kode_pesanan) }}" class="form-control" readonly>
                        </div>
                    </div>
                    @php
                    $statusOptions = config("order_status.{$order->tipe_layanan}.{$order->tipe_pembayaran}", []);
                    @endphp

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control @error('status') is-invalid @enderror" {{
                                in_array($order->status, ['Selesai', 'Dibatalkan']) ? 'disabled' : '' }}>

                                <option value="" {{ old('status', $order->status) == '' ? 'selected' : '' }}>
                                    - Pilih Status Pesanan -
                                </option>

                                @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" {{ old('status', $order->status) == $status ? 'selected' :
                                    '' }}>
                                    {{ $status }}
                                </option>
                                @endforeach
                            </select>

                            @error('status')
                            <span class="invalid-feedback alert-danger" role="alert">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <br>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('pesanan.proses') }}">
                    <button type="button" class="btn btn-secondary">Kembali</button>
                </a>

            </form>
        </div>
    </div>
</div>

<!-- end template-->
@endsection