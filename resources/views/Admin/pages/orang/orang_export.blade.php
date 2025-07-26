<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Orang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 10pt;
        }
        h1 {
            text-align: center;
            color: #014A7F;
            margin-bottom: 20px;
        }
        .export-info {
            text-align: center;
            font-size: 9pt;
            color: #333;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
            max-width: 200px;
        }
        th {
            background-color: #014A7F;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
 çe
        }
        @page {
            size: A4 landscape;
            margin: 20mm;
        }
    </style>
</head>
<body>
    <h1>Data Orang</h1>
    <div class="export-info">
        Dibuat pada: {{ date('d-m-Y H:i:s') }}
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Nomor</th>
                <th style="width: 20%;">Nama Lengkap</th>
                <th style="width: 15%;">NIK</th>
                <th style="width: 15%;">No Telepon</th>
                <th style="width: 25%;">Alamat</th>
                <th style="width: 20%;">Perusahaan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orangs as $index => $orang)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $orang->nama_lengkap }}</td>
                    <td>{{ $orang->nik }}</td>
                    <td>{{ $orang->no_telepon }}</td>
                    <td>{{ $orang->alamat }}</td>
                    <td>{{ $orang->perusahaan->isNotEmpty() ? $orang->perusahaan->pluck('nama_perusahaan')->implode(', ') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data orang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>