<!doctype html>
<html>
<head>
    <title>Laporan Laba Data Penjualan</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; }
        table th, table td { border: 1px solid #3c3c3c; padding: 3px 8px; }
    </style>
</head>
<body>
    <center><h4>MySIFA LAPORAN LABA PENJUALAN</h4></center>
    <br>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Kode Barang</th>
                <th style="text-align:center">Nama Barang</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:center">Satuan</th>
                <th style="text-align:center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td style="width:150px">{{ $row->kd_barang }}</td>
                    <td style="width:300px">{{ $row->nmbrg_dtrkasir }}</td>
                    <td style="text-align:center; width:80px">{{ $row->qty_total }}</td>
                    <td style="text-align:center; width:100px">{{ $row->sat_dtrkasir }}</td>
                    <td style="text-align:right; width:100px">{{ number_format($row->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" style="text-align:right">Total Nilai Penjualan</td>
                <td style="text-align:right">{{ number_format($totalNilaiPenjualan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right">Diskon Transaksi</td>
                <td style="text-align:right">{{ number_format($diskon, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right">Total Nilai Transaksi</td>
                <td style="text-align:right">{{ number_format($totalNilaiTransaksi, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right">Laba Tanpa Diskon</td>
                <td style="text-align:right">{{ number_format($labaTanpaDiskon, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:right">Laba Setelah Diskon</td>
                <td style="text-align:right">{{ number_format($labaSetelahDiskon, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
