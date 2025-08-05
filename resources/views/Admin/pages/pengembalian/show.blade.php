@extends('admin.layouts.base')
@section('title', 'Detail Pengembalian')

@section('styles')
    <style>
        .detail-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-container h4 {
            margin-bottom: 20px;
            color: #014A7F;
        }
        .detail-container .row {
            margin-bottom: 15px;
        }
        .detail-container .label {
            font-weight: bold;
            color: #333;
        }
        .detail-container .value {
            color: #555;
        }
        .btn-custom {
            background-color: #014A7F !important;
            color: white !important;
            border: none;
        }
        .btn-custom:hover {
            background-color: #001B36 !important;
            color: white !important;
        }
    </style>
@endsection

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Detail Pengembalian</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="detail-container">
                <h4>Informasi Pengembalian</h4>
                <div class="row">
                    <div class="col-md-3 label">Nama Pelanggan</div>
                    <div class="col-md-9 value">
                        {{ $pengembalian->transaksiDetail && $pengembalian->transaksiDetail->transaksi && $pengembalian->transaksiDetail->transaksi->orang
                            ? $pengembalian->transaksiDetail->transaksi->orang->nama_lengkap
                            : '-' }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Kode Tabung</div>
                    <div class="col-md-9 value">{{ $pengembalian->tabung ? $pengembalian->tabung->kode_tabung : '-' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Jenis Tabung</div>
                    <div class="col-md-9 value">
                        {{ $pengembalian->tabung && $pengembalian->tabung->jenisTabung
                            ? $pengembalian->tabung->jenisTabung->nama_jenis
                            : '-' }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Tanggal Pinjam</div>
                    <div class="col-md-9 value">
                        {{ $pengembalian->tanggal_pinjam instanceof \Carbon\Carbon
                            ? $pengembalian->tanggal_pinjam->format('Y-m-d')
                            : ($pengembalian->tanggal_pinjam ? substr($pengembalian->tanggal_pinjam, 0, 10) : '-') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Waktu Pinjam</div>
                    <div class="col-md-9 value">{{ $pengembalian->waktu_pinjam ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Tanggal Pengembalian</div>
                    <div class="col-md-9 value">
                        {{ $pengembalian->tanggal_pengembalian instanceof \Carbon\Carbon
                            ? $pengembalian->tanggal_pengembalian->format('Y-m-d')
                            : ($pengembalian->tanggal_pengembalian ? substr($pengembalian->tanggal_pengembalian, 0, 10) : '-') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Waktu Pengembalian</div>
                    <div class="col-md-9 value">{{ $pengembalian->waktu_pengembalian ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Jumlah Keterlambatan (Periode)</div>
                    <div class="col-md-9 value">{{ $pengembalian->jumlah_keterlambatan_bulan }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Deposit</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->deposit, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Biaya Admin</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->biaya_admin > 0 ? $pengembalian->biaya_admin : 50000, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Denda Keterlambatan</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->jumlah_keterlambatan_bulan * 50000, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Denda Kondisi Tabung</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->denda_kondisi_tabung, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Total Denda</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->total_denda, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Sisa Deposit</div>
                    <div class="col-md-9 value">Rp {{ number_format($pengembalian->sisa_deposit, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Status Tabung</div>
                    <div class="col-md-9 value">{{ $pengembalian->statusTabung ? $pengembalian->statusTabung->status_tabung : '-' }}</div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('pengembalian.index') }}" class="btn btn-custom">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection