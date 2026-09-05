<!doctype html>
<html>
<head>
    <title>Laporan Pembelian Barang</title>
    <style>
        body { font-family: sans-serif; }
        table { margin: 20px auto; border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center>
        <h4>MySIFA LAPORAN BARANG MASUK <br> {{ $supplierNama }} dari tanggal {{ $tglAwal }} hingga {{ $tglAkhir }}</h4>
    </center>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>tanggal Pembelian</th>
                <th>Kode Transaksi</th>
                <th>Status Pembayaran</th>
                <th>Nilai Faktur</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td style="text-align:left; width:150px;">{{ $r->tgl_trbmasuk?->format('Y-m-d') }}</td>
                    <td style="text-align:left; width:300px;">{{ $r->kd_trbmasuk }}</td>
                    <td style="text-align:center; width:100px;">{{ $r->carabayar }}</td>
                    <td style="text-align:right; width:100px;">{{ number_format($r->ttl_trbmasuk, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;">
                <td colspan="4" style="text-align:right;">Total</td>
                <td style="text-align:right;">{{ number_format($rows->sum('ttl_trbmasuk'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
