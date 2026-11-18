<style>
    table {
        border-collapse: collapse;
        width: 100%;
        border: 1px solid #ccc;
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    table th,
    table td {
        border: 1px solid #ccc;
        padding: 6px;
        text-align: left;
    }

    table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .text-end {
        text-align: right;
    }

    .total-row td {
        font-weight: bold;
        background-color: #f2f2f2;
    }
</style>

<table>
    <tr>
        <td>
            <strong>Perihal:</strong> {{ $judul }} <br>
            <strong>Laporan:</strong> {{ $subJudul }} <br>
            <strong>Tanggal:</strong> {{ $tanggalAwal }} s.d {{ $tanggalAkhir }}
        </td>
    </tr>
</table>

<p></p>

@php
    $totalOngkir = $cetak->sum('biaya_ongkir');
    $totalBiteship = $cetak->sum('biteship_price');
@endphp

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>ID Pesanan</th>
            <th>Nama Pemesan</th>
            <th>Tanggal Pesanan</th>
            <th>Kurir</th>
            <th>Status Kurir</th>
            <th class="text-end">Ongkir Dibayar Pelanggan</th>
            <th class="text-end">Tagihan Biteship</th>
            <th class="text-end">Selisih</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($cetak as $row)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->kode_pesanan }}</td>
            <td>{{ $row->user->name ?? '-' }}</td>
            <td>{{ $row->created_at->format('d M Y H:i') }}</td>
            <td>{{ ucfirst($row->kurir_company) }} {{ ucfirst($row->kurir_type) }}</td>
            <td>{{ \App\Services\BiteshipService::statusLabel($row->biteship_status) }}</td>
            <td class="text-end">Rp. {{ number_format($row->biaya_ongkir ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">Rp. {{ number_format($row->biteship_price ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">Rp. {{ number_format(($row->biaya_ongkir ?? 0) - ($row->biteship_price ?? 0), 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9">Tidak ada pesanan Kurir Instan pada rentang tanggal ini.</td>
        </tr>
        @endforelse
    </tbody>
    @if ($cetak->isNotEmpty())
    <tfoot>
        <tr class="total-row">
            <td colspan="6">TOTAL</td>
            <td class="text-end">Rp. {{ number_format($totalOngkir, 0, ',', '.') }}</td>
            <td class="text-end">Rp. {{ number_format($totalBiteship, 0, ',', '.') }}</td>
            <td class="text-end">Rp. {{ number_format($totalOngkir - $totalBiteship, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
    @endif
</table>
