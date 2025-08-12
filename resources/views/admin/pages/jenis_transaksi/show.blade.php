@extends('admin.layouts.base')
@section('title', 'Detail Jenis Transaksi')

@section('styles')
<style>
    .detail-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .detail-container .card-body {
        font-size: 0.9rem;
    }
    .detail-container dt {
        font-weight: bold;
        color: #014A7F;
    }
    .detail-container dd {
        margin-bottom: 1rem;
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
<h2>Detail Jenis Transaksi</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Informasi Jenis Transaksi</h6>
    </div>
    <div class="card-body">
        <div class="detail-container">
            <dl class="row">
                <dt class="col-sm-4">Jenis Transaksi</dt>
                <dd class="col-sm-8">{{ $jenisTransaksi->jenis_transaksi }}</dd>
            </dl>
            <div class="text-center">
                <a href="{{ route('admin.jenis_transaksi.index') }}" class="btn btn-custom"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection