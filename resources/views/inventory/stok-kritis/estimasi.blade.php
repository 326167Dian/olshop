@extends('inventory.layouts.app')

@section('header', 'Stok Kritis')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">ESTIMASI STOK KRITIS</h3>
        </div>
        <div class="card-body table-responsive">
            <span class="btn btn-primary">LAKU</span> Jumlah Transaksi &gt; 10 per 30 hari
            <span class="btn btn-success">LANCAR</span> Jumlah Transaksi 6 - 10 per 30 hari
            <span class="btn btn-warning">SLOW</span> Jumlah Transaksi 1 - 5 per 30 hari
            <hr>

            <a class="btn btn-danger btn-flat mb-2" href="{{ route('inventory.stok-kritis.estimasi.excel') }}" target="_blank">EXPORT TO EXCEL</a>

            <form method="POST" action="{{ route('inventory.stok-kritis.add-to-order') }}" id="frmAddOrder">
                @csrf
                <button class="btn btn-success btn-flat mb-2" type="submit" onclick="return confirm('Tambahkan item terpilih ke draft pesanan?');">ADD TO ORDER</button>
                <hr>

                <table id="tabel-estimasi" class="table table-bordered table-striped">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Qty/Stok</th>
                            <th class="text-right">T30</th>
                            <th class="text-center">Q30</th>
                            <th class="text-center">SFC max30</th>
                            <th class="text-center">SFCmax/ week</th>
                            <th class="text-right">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $r)
                            <tr>
                                <td><input type="checkbox" name="kd_barang[]" value="{{ $r->kd_barang }}"> {{ $i + 1 }}</td>
                                <td class="text-center" style="background-color: {{ $r->kategori_color }}; color:#fff;"><strong>{{ $r->kategori_label }}</strong></td>
                                <td>{{ $r->nm_barang }}</td>
                                <td class="text-right">{{ $r->stok_barang }}</td>
                                <td class="text-center">{{ $r->t30 }}</td>
                                <td class="text-center">{{ $r->q30 }}</td>
                                <td class="text-center">{{ $r->sfc_max30 }}</td>
                                <td class="text-center">{{ $r->sfc_max_week }}</td>
                                <td class="text-right">{{ $r->sat_barang }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-success" onclick="history.back()">KEMBALI</button>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-estimasi').DataTable({ order: [] });
    });
</script>
@endpush
