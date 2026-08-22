@extends('frontend.layouts.index')
@section('content')

<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="order-summary clearfix">
                    <!-- msgSuccess -->
                    @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <strong>{{ session('success') }}</strong>
                    </div>
                    @endif
                    <div class="section-title">
                        <h2 class="title">HISTORY PESANAN</h2>
                    </div>
                    <!-- end msgSuccess -->
                    @if ($orders->count() > 0)
                    {{-- TAMPILAN PC/LAPTOP --}}
                    <div class="table-responsive hidden-xs">
                        <table class="shopping-cart-table table">
                            <thead>
                                <tr>
                                    <th>Kode Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->kode_pesanan }}</td>
                                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                    <td>Rp.
                                        {{ number_format($order->total_harga + $order->biaya_ongkir, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @php $status = strtolower($order->status); @endphp
                                        @if ($order->status == 'Paid')
                                        Proses
                                        @elseif ($order->status == 'proses cod')
                                        Proses COD
                                        @else
                                        {{ $order->status }}
                                        @endif
                                    </td>
                                    <td>
                                        <button class="primary-btn" data-toggle="modal"
                                            data-target="#orderDetailModal{{ $order->id }}">Detail</button>
                                        <a href="{{ route('order.invoice', $order->id) }}" target="_blank">
                                            <button type="button" class="primary-btn">Invoice</button>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- TAMPILAN MOBILE MIRIP SHOPEE --}}
                    <div class="visible-xs">
                        @foreach ($orders as $order)
                        <div class="panel panel-default"
                            style="margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                            <div class="panel-heading" style="background-color: #f8f8f8; padding: 10px 15px;">
                                <strong>{{ $order->kode_pesanan }}</strong>
                                @php
                                $status = strtolower($order->status);
                                $labelClass = 'label-default'; // default jika status tidak dikenal

                                switch ($status) {
                                case 'paid':
                                case 'proses cod':
                                case 'kirim':
                                $labelClass = 'label-info';
                                break;
                                case 'selesai':
                                $labelClass = 'label-success';
                                break;
                                case 'dibatalkan':
                                $labelClass = 'label-danger';
                                break;
                                }
                                @endphp

                                <span class="pull-right label {{ $labelClass }}">
                                    @if ($status == 'paid')
                                    Proses
                                    @elseif ($status == 'proses cod')
                                    Proses COD
                                    @else
                                    {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            </div>
                            <div class="panel-body" style="padding: 10px 15px;">
                                <p style="margin: 5px 0;"><strong>Tanggal:</strong>
                                    {{ $order->created_at->format('d M Y H:i') }}</p>
                                <p style="margin: 5px 0;"><strong>Total Bayar:</strong> Rp.
                                    {{ number_format($order->total_harga + $order->biaya_ongkir, 0, ',', '.') }}
                                </p>
                                {{-- Produk singkat (1 produk saja tampil) --}}
                                @php $firstItem = $order->orderItems->first(); @endphp
                                @if ($firstItem)
                                <div class="media" style="margin-top: 10px;">
                                    <div class="media-left">
                                        <img src="{{ $firstItem->produk->image ? asset('storage/' . $firstItem->produk->image) : asset('img/default.jpg') }}"
                                            alt=""
                                            style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
                                    </div>
                                    <div class="media-body" style="padding-left: 10px;">
                                        <div style="font-weight: bold;">{{ $firstItem->produk->nm_barang }}
                                        </div>
                                        <div style="font-size: 13px;">Jumlah: {{ $firstItem->quantity }} |
                                            Harga: Rp {{ number_format($firstItem->harga, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                {{-- Tombol --}}
                                <div class="text-right" style="margin-top: 10px;">
                                    <button class="btn btn-xs btn-primary" data-toggle="modal"
                                        data-target="#orderDetailModal{{ $order->id }}">Lihat
                                        Detail</button>
                                    <a href="{{ route('order.invoice', $order->id) }}" target="_blank"
                                        class="btn btn-xs btn-default">Invoice</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- MODAL UNTUK DETAIL PESANAN (SAMA UNTUK MOBILE & DESKTOP) --}}
                    @foreach ($orders as $order)
                    <div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="orderDetailModalLabel{{ $order->id }}">
                                        Detail Pesanan #{{ $order->kode_pesanan }}
                                    </h5>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama Produk</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderItems as $item)
                                            <tr>
                                                <td>{{ $item->produk->nm_barang }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>Rp. {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                <td>Rp.
                                                    {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p>Anda belum memiliki riwayat pesanan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<style>
    .panel-default {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .btn-xs {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
    }
</style>
@endsection