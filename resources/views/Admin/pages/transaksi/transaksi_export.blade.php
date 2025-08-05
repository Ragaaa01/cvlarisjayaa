<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Transaksi</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #333333;
            line-height: 1.4;
            margin: 0;
        }
        .header {
            position: fixed;
            top: -10mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            border-bottom: 1px solid #d1d1d1;
            padding-bottom: 5mm;
        }
        .header h1 {
            font-size: 14pt;
            color: #014A7F;
            margin: 0;
            font-weight: bold;
        }
        .header .subtitle {
            font-size: 8pt;
            color: #666666;
            margin-top: 2mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20mm;
            font-size: 9pt;
        }
        th, td {
            border: 0.5pt solid #999999;
            padding: 3mm 4mm;
            vertical-align: top;
            text-align: left;
        }
        th {
            background-color: #014A7F;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        td {
            background-color: #ffffff;
        }
        .no-column {
            width: 10%;
            text-align: center;
        }
        .nama-pelanggan-column {
            width: 25%;
        }
        .total-transaksi-column {
            width: 20%;
        }
        .tanggal-transaksi-column {
            width: 20%;
            text-align: right;
        }
        .waktu-transaksi-column {
            width: 15%;
        }
        .status-column {
            width: 10%;
        }
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #666666;
            border-top: 1px solid #d1d1d1;
            padding-top: 3mm;
        }
        .footer .date {
            margin-bottom: 2mm;
        }
        .footer .page-number:after {
            content: "Halaman " counter(page) " dari " counter(pages);
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        tr:hover td {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Transaksi</h1>
        <div class="subtitle">Laporan Data Transaksi - {{ date('d M Y') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th class="no-column">No</th>
                <th class="nama-pelanggan-column">Nama Pelanggan</th>
                <th class="total-transaksi-column">Total Transaksi</th>
                <th class="tanggal-transaksi-column">Tanggal Transaksi</th>
                <th class="waktu-transaksi-column">Waktu Transaksi</th>
                <th class="status-column">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksis as $index => $transaksi)
                <tr>
                    <td class="no-column">{{ $index + 1 }}</td>
                    <td class="nama-pelanggan-column">{{ $transaksi->orang ? $transaksi->orang->nama_lengkap : '-' }}</td>
                    <td class="total-transaksi-column">Rp {{ number_format($transaksi->total_transaksi, 2, ',', '.') }}</td>
                    <td class="tanggal-transaksi-column">{{ $transaksi->tanggal_transaksi instanceof \Carbon\Carbon ? $transaksi->tanggal_transaksi->format('Y-m-d') : ($transaksi->tanggal_transaksi ? substr($transaksi->tanggal_transaksi, 0, 10) : '-') }}</td>
                    <td class="waktu-transaksi-column">{{ $transaksi->waktu_transaksi ?? '-' }}</td>
                    <td class="status-column">{{ $transaksi->status_valid ? 'Valid' : 'Batal' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <div class="date">Dicetak pada: {{ date('d-m-Y H:i:s') }}</div>
        <div class="page-number"></div>
    </div>
</body>
</html>