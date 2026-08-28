<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Cek Darah</title>
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
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        table.info td {
            padding: 1px 0;
            vertical-align: top;
        }

        table.info td.label {
            width: 32%;
        }

        table.ref {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.ref caption {
            text-align: left;
            font-weight: bold;
            margin-bottom: 2px;
        }

        table.ref th,
        table.ref td {
            border: 1px solid #000;
            padding: 2px 4px;
            text-align: center;
            font-size: 10px;
        }

        table.ref td.label {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="center">
        <div class="title">{{ $setheader->satu ?? '' }}</div>
        <div>{{ $setheader->dua ?? '' }}</div>
        <div>{{ $setheader->tiga ?? '' }}</div>
        <div>{{ $setheader->empat ?? '' }}</div>
        <div>{{ $setheader->lima ?? '' }}</div>
        <div>{{ $setheader->enam ?? '' }}</div>
        <div>{{ $setheader->tujuh ?? '' }}</div>
    </div>

    <hr>

    <div class="center">
        <div class="title">HASIL CEK DARAH</div>
        <div>{{ $cekdarah->waktu?->format('d-m-Y H:i') }}</div>
    </div>

    <br>

    <table class="info">
        <tr>
            <td class="label">Pasien</td>
            <td>: {{ $cekdarah->pelanggan->nm_pelanggan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Glukosa</td>
            <td>: {{ $cekdarah->gula }} mg/dl</td>
        </tr>
        <tr>
            <td class="label">Asam Urat</td>
            <td>: {{ $cekdarah->asamurat }} mg/dl</td>
        </tr>
        <tr>
            <td class="label">Kolesterol</td>
            <td>: {{ $cekdarah->kolesterol }} mg/dl</td>
        </tr>
        <tr>
            <td class="label">Tensi</td>
            <td>: {{ $cekdarah->tensi }} mmHg</td>
        </tr>
    </table>

    <br>

    <table class="ref">
        <caption>Tabel Glukosa Darah</caption>
        <tr>
            <th></th>
            <th>Normal</th>
            <th>Pre DM</th>
            <th>DM</th>
        </tr>
        <tr>
            <td class="label">Puasa</td>
            <td>70-100</td>
            <td>100-124</td>
            <td>&gt;125</td>
        </tr>
        <tr>
            <td class="label">2PP</td>
            <td>&lt;140</td>
            <td>140-200</td>
            <td>&gt;200</td>
        </tr>
    </table>

    <table class="ref">
        <caption>Tabel Asam Urat</caption>
        <tr>
            <th>Usia</th>
            <th>10-18</th>
            <th>18-40</th>
            <th>&gt;40</th>
        </tr>
        <tr>
            <td class="label">Pria</td>
            <td>3.6-5.5</td>
            <td>2-7.5</td>
            <td>2-8.5</td>
        </tr>
        <tr>
            <td class="label">Wanita</td>
            <td>3.6-5.5</td>
            <td>2-6.5</td>
            <td>2-8</td>
        </tr>
    </table>

    <table class="ref">
        <caption>Tabel Kolesterol Total</caption>
        <tr>
            <th>Kelamin</th>
            <th>Normal</th>
            <th>PreTinggi</th>
            <th>Tinggi</th>
        </tr>
        <tr>
            <td class="label">P/W</td>
            <td>&lt;200</td>
            <td>200-239</td>
            <td>&gt;240</td>
        </tr>
    </table>

    <table class="ref">
        <caption>Tabel Tekanan Darah</caption>
        <tr>
            <th>Kategori</th>
            <th>Sistolik</th>
            <th>Diastolik</th>
        </tr>
        <tr>
            <td class="label">Optimal</td>
            <td>&lt;120</td>
            <td>&lt;80</td>
        </tr>
        <tr>
            <td class="label">Normal</td>
            <td>&lt;130</td>
            <td>&lt;85</td>
        </tr>
        <tr>
            <td class="label">Pre Hipertensi</td>
            <td>130-139</td>
            <td>85-89</td>
        </tr>
        <tr>
            <td class="label">Derajat 1</td>
            <td>140-159</td>
            <td>90-99</td>
        </tr>
        <tr>
            <td class="label">Derajat 2</td>
            <td>160-179</td>
            <td>100-109</td>
        </tr>
        <tr>
            <td class="label">Derajat 3</td>
            <td>&gt;180</td>
            <td>&gt;110</td>
        </tr>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
