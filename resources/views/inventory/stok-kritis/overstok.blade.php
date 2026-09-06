@extends('inventory.layouts.app')

@section('header', 'Stok Kritis')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">OVERSTOK</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-overstok" class="table table-bordered table-striped">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Kategori</th>
                        <th>Nama Barang</th>
                        <th class="text-right">Qty/Stok</th>
                        <th class="text-right">T30</th>
                        <th class="text-center">Q30</th>
                        <th class="text-center">On-&gt;T</th>
                        <th class="text-right">Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $r)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center" style="background-color: {{ $r->kategori_color }}; color:#fff;"><strong>{{ $r->kategori_label }}</strong></td>
                            <td>{{ $r->nm_barang }}</td>
                            <td class="text-right">{{ $r->stok_barang }}</td>
                            <td class="text-right">{{ $r->t30 }}</td>
                            <td class="text-center">{{ $r->q30 }}</td>
                            <td class="text-center">{{ $r->on_t }}</td>
                            <td class="text-right">{{ $r->sat_barang }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-success" onclick="history.back()">KEMBALI</button>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-overstok').DataTable({ order: [] });
    });
</script>
@endpush
