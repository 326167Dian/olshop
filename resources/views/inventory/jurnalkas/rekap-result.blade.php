@extends('inventory.layouts.app')

@section('header', 'Jurnal Kas')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">REKAPITULASI {{ $tglAwal }} SD {{ $tglAkhir }}</h3>
        </div>
        <div class="card-body table-responsive">
            <table id="tabel-rekap-hasil" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5px" class="text-center">No</th>
                        <th>Jenis Jurnal</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('inventory.jurnalkas.rekap.detail', ['id' => $r->idjenis, 'tgl_awal' => $tglAwal, 'tgl_akhir' => $tglAkhir]) }}">{{ $r->nm_jurnal }}</a>
                            </td>
                            <td class="text-right">{{ number_format((float) $r->debit, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format((float) $r->kredit, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background-color:lightblue; font-size:4vh;">
                        <td colspan="2">Sub Total</td>
                        <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background-color:lightblue; font-size:4vh;">
                        <td colspan="3">Total</td>
                        <td class="text-right">{{ number_format($totalKredit - $totalDebit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
            <a class="btn btn-danger" href="{{ route('inventory.jurnalkas.index') }}">KEMBALI</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#tabel-rekap-hasil').DataTable({ order: [] });
    });
</script>
@endpush
