@extends('admin.layouts.base')
@section('title', 'Detail Pembayaran')

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
    <h1 class="h3 mb-4 text-gray-800">Detail Pembayaran</h1>
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
                <h4>Informasi Pembayaran</h4>
                <div class="row">
                    <div class="col-md-3 label">Nama Pelanggan</div>
                    <div class="col-md-9 value">{{ $pembayaran->orang ? $pembayaran->orang->nama_lengkap : '-' }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Total Transaksi</div>
                    <div class="col-md-9 value">Rp {{ number_format($pembayaran->total_transaksi, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Jumlah Pembayaran</div>
                    <div class="col-md-9 value">Rp {{ number_format($pembayaran->jumlah_pembayaran, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Sisa Tagihan</div>
                    <div class="col-md-9 value">Rp {{ number_format($pembayaran->total_transaksi - $pembayaran->jumlah_pembayaran, 2, ',', '.') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Metode Pembayaran</div>
                    <div class="col-md-9 value">{{ $pembayaran->metode_pembayaran }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Tanggal Pembayaran</div>
                    <div class="col-md-9 value">{{ $pembayaran->tanggal_pembayaran instanceof \Carbon\Carbon ? $pembayaran->tanggal_pembayaran->format('Y-m-d') : ($pembayaran->tanggal_pembayaran ? substr($pembayaran->tanggal_pembayaran, 0, 10) : '-') }}</div>
                </div>
                <div class="row">
                    <div class="col-md-3 label">Waktu Pembayaran</div>
                    <div class="col-md-9 value">{{ $pembayaran->waktu_pembayaran ?? '-' }}</div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-custom">Kembali</a>
                </div>
            </div>
        </div>
    </div>
@endsection