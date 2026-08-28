<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Supplier</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            padding: 20px;
            background: #f0f0f0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 15px;
        }

        table.supplier-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.supplier-table th,
        table.supplier-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        table.supplier-table th {
            background: #dcdcdc;
            text-align: center;
        }

        .footer-print {
            margin-top: 15px;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>DATA SUPPLIER</h2>

        <table class="supplier-table">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th style="width:200px;">Nama Supplier</th>
                    <th style="width:120px;">Telp</th>
                    <th>Alamat Supplier</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($supplier as $index => $row)
                    <tr>
                        <td style="text-align:center;">{{ $index + 1 }}</td>
                        <td>{{ $row->nm_supplier }}</td>
                        <td>{{ $row->tlp_supplier }}</td>
                        <td>{{ $row->alamat_supplier }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;">Tidak ada data supplier.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="footer-print">Tanggal Cetak: {{ now()->format('d-m-Y H:i:s') }} || Dicetak Oleh: {{ $dicetakOleh }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Cetak</button>
    </div>

</body>

</html>
