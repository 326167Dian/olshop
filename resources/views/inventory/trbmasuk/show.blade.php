@extends('inventory.layouts.app')

@section('header', 'Detail Barang Masuk')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Detail Barang Masuk — {{ $trbmasuk->kd_trbmasuk }}</h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:160px;">Tanggal</th><td>: {{ $trbmasuk->tgl_trbmasuk?->format('Y-m-d') }}</td></tr>
                        <tr><th>Petugas</th><td>: {{ $trbmasuk->petugas }}</td></tr>
                        <tr><th>Supplier</th><td>: {{ $trbmasuk->nm_supplier }}</td></tr>
                        <tr><th>Telepon</th><td>: {{ $trbmasuk->tlp_supplier }}</td></tr>
                        <tr><th>Alamat</th><td>: {{ $trbmasuk->alamat_trbmasuk }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:160px;">Keterangan</th><td>: {{ $trbmasuk->ket_trbmasuk }}</td></tr>
                        <tr><th>Cara Bayar</th><td>: {{ $trbmasuk->carabayar }}</td></tr>
                        @if ($trbmasuk->carabayar === 'KREDIT')
                            <tr><th>Jatuh Tempo</th><td>: {{ $trbmasuk->jatuhtempo }}</td></tr>
                        @endif
                        <tr><th>Dari Pesanan</th><td>: {{ $trbmasuk->kd_orders ?: '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>No. Batch</th>
                            <th>Kadaluarsa</th>
                            <th>Hrg Beli</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trbmasuk->detail as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->kd_barang }}</td>
                                <td>{{ $row->nmbrg_dtrbmasuk }}</td>
                                <td class="text-end">{{ $row->qty_dtrbmasuk }}</td>
                                <td class="text-center">{{ $row->sat_dtrbmasuk }}</td>
                                <td class="text-center">{{ $row->no_batch }}</td>
                                <td class="text-center">{{ $row->exp_date?->format('Y-m-d') }}</td>
                                <td class="text-end">{{ number_format($row->hrgsat_dtrbmasuk, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row->hrgttl_dtrbmasuk, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="8" class="text-end">Total</th>
                            <th class="text-end">{{ number_format($trbmasuk->detail->sum('hrgttl_dtrbmasuk'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <a href="{{ route('inventory.trbmasuk.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="button" class="btn btn-danger" onclick="hapusTransaksi()">Hapus Transaksi</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function hapusTransaksi() {
        if (!confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')) return;

        fetch("{{ route('inventory.trbmasuk.destroy', $trbmasuk->id_trbmasuk) }}", {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menghapus');
                window.location = "{{ route('inventory.trbmasuk.index') }}";
            })
            .catch(function() { alert('Gagal menghapus transaksi.'); });
    }
</script>
@endpush
