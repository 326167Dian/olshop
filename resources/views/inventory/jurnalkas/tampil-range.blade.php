@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">JURNAL PILIHAN TANGGAL {{ $tglAwal }} DAN {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabel-range" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Petugas</th>
                            <th>Jenis Transaksi</th>
                            <th>Cash / Transfer</th>
                            <th>Debit</th>
                            <th>Kredit</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->tanggal->format('Y-m-d') }}</td>
                                <td>{{ $r->ket }}</td>
                                <td>{{ $r->petugas }}</td>
                                <td>{{ optional($r->jenis)->nm_jurnal }}</td>
                                <td class="text-center">{{ $r->carabayar }}</td>
                                <td class="text-right">Rp. {{ number_format((float) $r->debit, 0, ',', '.') }}</td>
                                <td class="text-right">Rp. {{ number_format((float) $r->kredit, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('inventory.jurnalkas.edit', $r) }}" title="EDIT" class="btn btn-warning btn-xs">EDIT</a>
                                    <form action="{{ route('inventory.jurnalkas.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurnal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="HAPUS" class="btn btn-danger btn-xs">HAPUS</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data pada rentang ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold; background-color:#DB7093; text-align:center; font-size:large;">
                            <td colspan="6">Pengeluaran Tunai Rp. {{ number_format($totals['pengeluaranTunai'], 0, ',', '.') }} dan Transfer Rp. {{ number_format($totals['pengeluaranTransfer'], 0, ',', '.') }}</td>
                            <td>Total</td>
                            <td colspan="2">Rp {{ number_format($totals['totalDebit'], 0, ',', '.') }}</td>
                        </tr>
                        <tr style="font-weight:bold; background-color:#98FB98; text-align:center; font-size:large;">
                            <td colspan="6">Pemasukan Tunai Rp. {{ number_format($totals['pemasukanTunai'], 0, ',', '.') }} dan Transfer Rp. {{ number_format($totals['pemasukanTransfer'], 0, ',', '.') }}</td>
                            <td>Total</td>
                            <td colspan="2">Rp {{ number_format($totals['totalKredit'], 0, ',', '.') }}</td>
                        </tr>
                        <tr style="font-weight:bold; background-color:#87CEEB; text-align:center; font-size:large;">
                            <td colspan="6">Saldo Tunai Rp. {{ number_format($totals['saldoTunai'], 0, ',', '.') }} dan Saldo Transfer Rp. {{ number_format($totals['saldoTransfer'], 0, ',', '.') }}</td>
                            <td>Saldo</td>
                            <td colspan="2">Rp {{ number_format($totals['totalSaldo'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <a class="btn btn-danger" href="{{ route('inventory.jurnalkas.index') }}">KEMBALI</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-range').DataTable({ order: [] });
    });
</script>
@endpush
