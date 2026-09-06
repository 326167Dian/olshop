<!doctype html>
<html>
<head>
    <title>Laporan Jenis Penjualan</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h4>LAPORAN JENIS PENJUALAN : {{ $tipeLabel }}</h4></center>
    <center>Tanggal: {{ $tglAwal }} s/d {{ $tglAkhir }}</center>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Tgl</th>
                <th style="text-align:center">Kode Transaksi</th>
                <th style="text-align:center">Petugas</th>
                <th style="text-align:center">Pelanggan</th>
                <th style="text-align:center">No Pesanan</th>
                <th style="text-align:center">Nilai Pesanan</th>
                <th style="text-align:center">Jenis Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td style="width:100px">{{ optional($row->tgl_trkasir)->format('Y-m-d') }}</td>
                    <td style="width:150px">{{ $row->kd_trkasir }}</td>
                    <td style="width:150px">{{ $row->petugas }}</td>
                    <td style="width:200px">{{ $row->nm_pelanggan }}</td>
                    <td style="text-align:center; width:120px">{{ $row->kodetx }}</td>
                    <td style="text-align:right; width:120px">{{ number_format($row->ttl_trkasir, 0, ',', '.') }}</td>
                    <td style="width:150px">{{ $row->jenis_transaksi }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="7" style="text-align:right">Total Penjualan</td>
                <td style="text-align:right">{{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
