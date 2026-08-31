@extends('inventory.layouts.app')

@section('header', 'Detail Barang Masuk PBF')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Detail Barang Masuk PBF — {{ $trbmasuk->kd_trbmasuk }}</h3>
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
                        <tr><th style="width:160px;">No Faktur</th><td>: {{ $trbmasuk->ket_trbmasuk }}</td></tr>
                        <tr><th>Cara Bayar</th><td>: {{ $trbmasuk->carabayar }}</td></tr>
                        @if ($trbmasuk->carabayar !== 'LUNAS')
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
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>No. Batch</th>
                            <th>Kadaluarsa</th>
                            <th>HNA</th>
                            <th>Disc</th>
                            <th>Total</th>
                            <th>Tipe</th>
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
                                <td class="text-end">{{ number_format($row->hnasat_dtrbmasuk, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $row->diskon }}%</td>
                                <td class="text-end">{{ number_format($row->hrgttl_dtrbmasuk, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $row->tipe_barang === 'bonus' ? 'Bonus' : 'Reguler' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Belum ada item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-end">Total Harga + PPN</th>
                            <th colspan="2" class="text-end">{{ number_format($trbmasuk->detail->sum('hrgttl_dtrbmasuk'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <a href="{{ route('inventory.trbmasukpbf.index') }}" class="btn btn-secondary">Kembali</a>
            @if (Auth::guard('admin')->user()->isPemilik())
                <a href="{{ route('inventory.trbmasukpbf.edit', $trbmasuk->id_trbmasuk) }}" class="btn btn-primary">Edit</a>
            @endif
            <button type="button" class="btn btn-danger" onclick="hapusTransaksi()">Hapus Transaksi</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function hapusTransaksi() {
        if (!confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')) return;

        fetch("{{ route('inventory.trbmasukpbf.destroy', $trbmasuk->id_trbmasuk) }}", {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Gagal menghapus');
                window.location = "{{ route('inventory.trbmasukpbf.index') }}";
            })
            .catch(function() { alert('Gagal menghapus transaksi.'); });
    }
</script>
@endpush
