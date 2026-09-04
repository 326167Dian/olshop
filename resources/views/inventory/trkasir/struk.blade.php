<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Penjualan</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            width: 74mm;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .title {
            font-weight: bold;
            font-size: 13px;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table.item {
            width: 100%;
            border-collapse: collapse;
        }

        table.item td {
            padding: 1px 0;
            vertical-align: top;
        }

        table.info td {
            padding: 1px 0;
            vertical-align: top;
        }

        table.info td.label {
            width: 40%;
        }

        table.info td.val,
        .text-end {
            text-align: right;
        }

        .btn-print {
            margin: 8px 0;
        }

        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="center">
        @if ($setheader)
            <div class="title">{{ $setheader->satu }}</div>
            <div>{{ $setheader->dua }}</div>
            <div>{{ $setheader->tiga }}</div>
            <div>{{ $setheader->empat }}</div>
        @endif
    </div>

    <hr>
    <table class="info">
        <tr>
            <td class="label">No. Transaksi</td>
            <td class="val">{{ $trkasir->kd_trkasir }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="val">{{ \Illuminate\Support\Carbon::parse($trkasir->tgl_trkasir)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kasir</td>
            <td class="val">{{ $trkasir->petugas }}</td>
        </tr>
        @if ($trkasir->nm_pelanggan)
            <tr>
                <td class="label">Pelanggan</td>
                <td class="val">{{ $trkasir->nm_pelanggan }}</td>
            </tr>
        @endif
    </table>
    <hr>

    <table class="item">
        @foreach ($trkasir->detail as $d)
            <tr>
                <td colspan="2">{{ $d->nmbrg_dtrkasir }}</td>
            </tr>
            <tr>
                <td>{{ rtrim(rtrim(number_format($d->qty_dtrkasir, 2, ',', '.'), '0'), ',') }} {{ $d->sat_dtrkasir }} x
                    {{ number_format($d->hrgjual_dtrkasir, 0, ',', '.') }}
                    @if ($d->disc > 0)
                        (-{{ $d->disc }}%)
                    @endif
                </td>
                <td class="text-end">{{ number_format($d->hrgttl_dtrkasir, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
    <hr>

    <table class="info">
        <tr>
            <td class="label">Diskon Faktur</td>
            <td class="val">{{ $trkasir->diskon1 }}% + {{ number_format($trkasir->diskon2, 0, ',', '.') }}</td>
        </tr>
        @if ($trkasir->redeem_poin > 0)
            <tr>
                <td class="label">Tukar Poin</td>
                <td class="val">-{{ number_format($trkasir->redeem_poin, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="label title">Total</td>
            <td class="val title">{{ number_format($trkasir->ttl_trkasir, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">{{ optional($carabayar)->nm_carabayar }}</td>
            <td class="val">{{ number_format($trkasir->dp_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Kembali</td>
            <td class="val">{{ number_format(max(0, $trkasir->sisa_bayar), 0, ',', '.') }}</td>
        </tr>
        @if ($trkasir->tambahan_poin > 0)
            <tr>
                <td class="label">Poin Didapat</td>
                <td class="val">{{ number_format($trkasir->tambahan_poin, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <hr>
    <div class="center">
        @if ($setheader)
            <div>{{ $setheader->delapan }}</div>
            <div>{{ $setheader->sembilan }}</div>
            <div>{{ $setheader->sepuluh }}</div>
        @endif
    </div>

    <div class="center btn-print">
        <button onclick="window.print()">Cetak</button>
    </div>
</body>

</html>
