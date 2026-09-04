<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Etiket -- {{ $trkasir->kd_trkasir }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 12px;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }

        .input-wrapper {
            max-width: 420px;
            margin-bottom: 12px;
            background: #fff;
            border: 1px solid #ccc;
            padding: 12px;
        }

        .input-wrapper label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
        }

        .input-wrapper input {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            border: 1px solid #aaa;
            margin-bottom: 8px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .checkbox-row label {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
        }

        .checkbox-row input {
            width: auto;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .input-wrapper button {
            padding: 8px 12px;
            border: 0;
            background: #337ab7;
            color: #fff;
            cursor: pointer;
        }

        .label-container {
            width: 70mm;
            height: 38mm;
            background-color: white;
            border: 1px solid #000;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
        }

        .label-container.obat-luar {
            background-color: #d9f2ff;
        }

        .obat-luar-badge {
            font-size: 6pt;
            font-weight: bold;
            border: 1px solid #000;
            padding: 0.4mm 1.2mm;
            background: #fff;
            display: inline-block;
            line-height: 1.1;
        }

        .header {
            display: flex;
            align-items: center;
            padding: 2mm;
            border-bottom: 1.5px solid #000;
            height: 14mm;
        }

        .logo-box {
            width: 12mm;
            margin-right: 2mm;
        }

        .logo-box img {
            width: 100%;
            height: auto;
        }

        .pharmacy-info {
            flex-grow: 1;
            text-align: center;
        }

        .pharmacy-name {
            font-size: 10pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .pharmacy-address,
        .pharmacy-contact {
            font-size: 5.5pt;
            margin: 1px 0;
            line-height: 1.1;
        }

        .body-content {
            padding: 2mm;
            flex-grow: 1;
            font-size: 7pt;
            display: flex;
            flex-direction: column;
            gap: 2mm;
        }

        .row-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2mm;
        }

        .row-top .left-col,
        .row-top .right-col {
            white-space: nowrap;
        }

        .row-top .mid-col {
            flex: 1;
            text-align: center;
        }

        .input-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 15mm;
            padding: 0 1px;
        }

        .patient-section {
            text-align: center;
            margin-top: 0;
            position: relative;
            top: -1.5mm;
        }

        .patient-name {
            font-size: 9pt;
            font-weight: bold;
            display: block;
            border-bottom: 1px solid #000;
            margin: 1mm auto;
            width: 80%;
            min-height: 4mm;
        }

        .usage-section {
            font-weight: bold;
            font-size: 8pt;
            margin-top: 1mm;
        }

        .usage-line {
            border-bottom: 1px solid #000;
            width: 10mm;
            display: inline-block;
            text-align: center;
        }

        .print-sheet {
            width: 210mm;
            display: grid;
            grid-template-columns: repeat(3, 70mm);
            grid-auto-rows: 38mm;
            justify-content: start;
            align-content: start;
            margin: 0;
            padding: 0;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-sheet {
                width: 210mm;
                margin: 0;
                padding: 0;
            }

            .input-wrapper {
                display: none;
            }

            .label-container,
            .label-container * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .label-container.obat-luar {
                background-color: #d9f2ff !important;
            }
        }
    </style>
</head>

<body>

    <div class="input-wrapper">
        <form method="GET" action="{{ route('inventory.trkasir.etiket', $trkasir) }}">
            <label for="aturan_dosis">Aturan Pakai misal 1 tablet</label>
            <input type="text" id="aturan_dosis" name="aturan_dosis" value="{{ $aturanDosis }}" placeholder="contoh: 1">

            <label for="aturan_kali">Sehari Berapa Kali misal 3</label>
            <input type="text" id="aturan_kali" name="aturan_kali" value="{{ $aturanKali }}" placeholder="contoh: 3">

            <label for="jumlah_etiket">Jumlah Etiket Print</label>
            <input type="number" id="jumlah_etiket" name="jumlah_etiket" min="1" max="200" value="{{ $jumlahEtiket }}" required>

            <div class="checkbox-row">
                <input type="checkbox" id="obat_luar" name="obat_luar" value="1" @checked($obatLuar)>
                <label for="obat_luar">OBAT LUAR</label>
            </div>

            <button type="submit">Tampilkan Etiket</button>
            <button type="button" onclick="window.print()" style="margin-left:8px; background:#28a745;">Print Etiket</button>
        </form>
    </div>

    <div class="print-sheet">
        @for ($i = 1; $i <= $jumlahEtiket; $i++)
            <div class="label-container {{ $obatLuar ? 'obat-luar' : '' }}">
                <div class="header">
                    <div class="logo-box">
                        @if ($setheader?->logo_url)
                            <img src="{{ $setheader->logo_url }}" alt="Logo">
                        @endif
                    </div>
                    <div class="pharmacy-info">
                        <p class="pharmacy-name">{{ $setheader->satu ?? '' }}</p>
                        <p class="pharmacy-address">{{ $setheader->dua ?? '' }}</p>
                        <p class="pharmacy-address">SIA No. : {{ $setheader->lima ?? '' }}</p>
                        <p class="pharmacy-address">APJ : {{ $setheader->empat ?? '' }}</p>
                        <p class="pharmacy-address">Telp : {{ $setheader->enam ?? '' }}</p>
                    </div>
                </div>

                <div class="body-content">
                    <div class="row-top">
                        <span class="left-col">No. <span class="input-line">{{ $trkasir->kd_trkasir }}</span></span>
                        <span class="mid-col">
                            @if ($obatLuar)
                                <span class="obat-luar-badge">OBAT LUAR</span>
                            @endif
                        </span>
                        <span class="right-col">Tgl. <span class="input-line">{{ $trkasir->tgl_trkasir?->format('Y-m-d') }}</span></span>
                    </div>

                    <div class="patient-section">
                        <span>Kepada Yth :</span>
                        <span class="patient-name">{{ $trkasir->nm_pelanggan }}</span>
                        <span>{{ $aturanDosis }}</span>
                    </div>

                    <div class="usage-section">
                        Sehari <span class="usage-line">{{ $aturanKali }}</span> x <span class="usage-line">{{ $aturanDosis }}</span>
                    </div>
                </div>
            </div>
        @endfor
    </div>

</body>
</html>
