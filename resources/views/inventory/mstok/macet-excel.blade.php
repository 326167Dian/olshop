<!doctype html>
<html>
<head>
    <title>Laporan Data Barang Macet</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h4>MySIFA TRAFFIC ANALYSIS</h4></center>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Kode</th>
                <th style="text-align:center">Nama Barang</th>
                <th style="text-align:center">Satuan</th>
                <th style="text-align:center">Rak</th>
                <th style="text-align:center">Stok</th>
                <th style="text-align:center">Stok Fisik</th>
                <th style="text-align:center">Exp Date.</th>
                <th style="text-align:center">Waktu</th>
                <th style="text-align:center">ACC Manager</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td style="width:150px">'{{ $row->kd_barang }}</td>
                    <td style="width:300px">{{ $row->nm_barang }}</td>
                    <td style="text-align:center; width:80px">{{ $row->sat_barang }}</td>
                    <td style="text-align:center; width:80px">{{ $row->jenisobat }}</td>
                    <td style="text-align:center; width:100px">{{ $row->stok_barang }}</td>
                    <td style="width:100px"></td>
                    <td style="width:100px"></td>
                    <td style="width:100px"></td>
                    <td style="width:100px"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
