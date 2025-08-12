<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Nota Pengembalian #{{ $pengembalian->id_pengembalian }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .nota-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .nota-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .nota-header h2 {
            color: #014A7F;
            margin: 0;
        }
        .nota-header p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #555;
        }
        .nota-details .row {
            display: flex;
            margin-bottom: 10px;
        }
        .nota-details .label {
            font-weight: bold;
            width: 30%;
        }
        .nota-details .value {
            width: 70%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 0.9rem;
        }
        .table th {
            background-color: #014A7F;
            color: white;
            text-align: center;
        }
        .table .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="nota-container">
        <div class="nota-header">
            <h2>Nota Pengembalian</h2>
            <p>CV Laris Jaya Gas</p>
            <p>Pengembalian #{{ $pengembalian->id_pengembalian }}</p>
            <p>Tanggal: {{ \Carbon\Carbon::parse($pengembalian->tanggal_pinjam)->format('d-m-Y') }}</p>
        </div>
        <div class="nota-details">
            <div class="row">
                <div class="label">ID Pengembalian</div>
                <div class="value">{{ $pengembalian->id_pengembalian }}</div>
            </div>
            <div class="row">
                <div class="label">Nama Pelanggan</div>
                <div class="value">
                    {{ $pengembalian->transaksiDetail && $pengembalian->transaksiDetail->transaksi && $pengembalian->transaksiDetail->transaksi->orang
                        ? $pengembalian->transaksiDetail->transaksi->orang->nama_lengkap
                        : '-' }}
                </div>
            </div>
            <div class="row">
                <div class="label">Kode Tabung</div>
                <div class="value">{{ $pengembalian->tabung ? $pengembalian->tabung->kode_tabung : '-' }}</div>
            </div>
            <div class="row">
                <div class="label">Jenis Tabung</div>
                <div class="value">
                    {{ $pengembalian->tabung && $pengembalian->tabung->jenisTabung
                        ? $pengembalian->tabung->jenisTabung->nama_jenis
                        : '-' }}
                </div>
            </div>
            <div class="row">
                <div class="label">Tanggal Pinjam</div>
                <div class="value">
                    {{ $pengembalian->tanggal_pinjam instanceof \Carbon\Carbon
                        ? $pengembalian->tanggal_pinjam->format('d-m-Y')
                        : ($pengembalian->tanggal_pinjam ? \Carbon\Carbon::parse($pengembalian->tanggal_pinjam)->format('d-m-Y') : '-') }}
                </div>
            </div>
            <div class="row">
                <div class="label">Waktu Pinjam</div>
                <div class="value">{{ $pengembalian->waktu_pinjam ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="label">Tanggal Pengembalian</div>
                <div class="value">
                    {{ $pengembalian->tanggal_pengembalian instanceof \Carbon\Carbon
                        ? $pengembalian->tanggal_pengembalian->format('d-m-Y')
                        : ($pengembalian->tanggal_pengembalian ? \Carbon\Carbon::parse($pengembalian->tanggal_pengembalian)->format('d-m-Y') : '-') }}
                </div>
            </div>
            <div class="row">
                <div class="label">Waktu Pengembalian</div>
                <div class="value">{{ $pengembalian->waktu_pengembalian ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="label">Jumlah Keterlambatan</div>
                <div class="value">{{ $pengembalian->jumlah_keterlambatan_bulan }} periode</div>
            </div>
            <div class="row">
                <div class="label">Status Tabung</div>
                <div class="value">{{ $pengembalian->statusTabung ? $pengembalian->statusTabung->status_tabung : '-' }}</div>
            </div>
        </div>

        <h5 style="margin-top: 20px;">Detail Biaya</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Deposit Awal</td>
                    <td>Rp {{ number_format($pengembalian->deposit, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya Admin</td>
                    <td>Rp {{ number_format($pengembalian->biaya_admin > 0 ? $pengembalian->biaya_admin : 50000, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Denda Keterlambatan</td>
                    <td>Rp {{ number_format($pengembalian->jumlah_keterlambatan_bulan * 50000, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Denda Kondisi Tabung</td>
                    <td>Rp {{ number_format($pengembalian->denda_kondisi_tabung, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Denda</td>
                    <td>Rp {{ number_format($pengembalian->total_denda, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Sisa Deposit</td>
                    <td>Rp {{ number_format($pengembalian->sisa_deposit, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>