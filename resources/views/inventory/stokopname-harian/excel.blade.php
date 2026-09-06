<!doctype html>
<html>
<head>
    <title>Stok Opname Harian</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h4>STOK OPNAME SHIFT {{ $shiftLabel }}</h4></center>
    <div>Tanggal : {{ $tglAwal }}</div>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Kode</th>
                <th style="text-align:center">Nama Barang</th>
                <th style="text-align:center">Satuan</th>
                <th style="text-align:center">Stok</th>
                <th style="text-align:center">Terjual</th>
                <th style="text-align:center">Stok Fisik</th>
                <th style="text-align:center">Exp. Date</th>
                <th style="text-align:center">Harga Beli</th>
                <th style="text-align:center">Harga Jual</th>
                <th style="text-align:center">Waktu</th>
                <th style="text-align:center">Acc Manager</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $r)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td style="width:150px">'{{ $r->kd_barang }}</td>
                    <td style="width:300px">{{ $r->nmbrg_dtrkasir }}</td>
                    <td style="text-align:center; width:80px">{{ $r->sat_dtrkasir }}</td>
                    <td style="text-align:center; width:80px">{{ $r->stok_barang }}</td>
                    <td style="text-align:center; width:80px">{{ $r->ttlqty }}</td>
                    <td style="width:100px"></td>
                    <td style="width:100px"></td>
                    <td style="text-align:right; width:100px">{{ number_format((float) $r->hrgsat_barang, 0, ',', '.') }}</td>
                    <td style="text-align:right; width:100px">{{ number_format((float) $r->hrgjual_dtrkasir, 0, ',', '.') }}</td>
                    <td style="width:100px"></td>
                    <td style="width:100px"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
