<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Print Barcode {{ $barang->kd_barang }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.6/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 148mm 210mm;
            margin: 6mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #fff;
        }

        .sheet {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            gap: 2mm 2mm;
        }

        .barcode-wrap {
            width: 33mm;
            height: 15mm;
            padding: 0.4mm;
            box-sizing: border-box;
            text-align: center;
            overflow: hidden;
            background: #fff;
        }

        .barcode-wrap svg {
            width: 100%;
            height: 11mm;
        }

        .item-code {
            margin-top: 0.3mm;
            font-size: 3.2mm;
            line-height: 1;
            letter-spacing: 0.35mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #000;
        }

        .actions {
            margin-top: 6px;
            text-align: center;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="sheet">
        @for ($i = 0; $i < $qty; $i++)
            <div class="barcode-wrap">
                <svg class="barcode-svg"></svg>
                <div class="item-code">{{ $barang->kd_barang }}</div>
            </div>
        @endfor
    </div>

    <div class="actions">
        <span>Jumlah label: {{ $qty }}</span>
        &nbsp;|&nbsp;
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <script>
        document.querySelectorAll('.barcode-svg').forEach(function(el) {
            JsBarcode(el, "{{ $barang->kd_barang }}", {
                format: 'CODE128',
                displayValue: false,
                margin: 0,
                height: 40,
            });
        });
    </script>
</body>

</html>
