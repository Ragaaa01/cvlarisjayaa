@extends('admin.layouts.base')
@section('title', 'Detail Data Orang Mitra')

@section('styles')
<style>
    .detail-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .detail-container .card-body {
        font-size: 0.9rem;
    }
    .detail-container .row {
        margin-bottom: 0.5rem;
    }
    .detail-container .label {
        font-weight: 600;
        color: #333;
    }
    .btn-back {
        background-color: #6c757d !important;
        color: white !important;
    }
    .btn-back:hover {
        background-color: #5a6268 !important;
        color: white !important;
    }
</style>
@endsection

@section('content')
<h2>Detail Data Orang Mitra</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Data Orang Mitra</h6>
    </div>
    <div class="card-body detail-container">
        <div class="row">
            <div class="col-md-4 label">Nama Orang</div>
            <div class="col-md-8">{{ $orangMitra->orang->nama_lengkap }}</div>
        </div>
        <div class="row">
            <div class="col-md-4 label">Nama Mitra</div>
            <div class="col-md-8">{{ $orangMitra->mitra->nama_mitra }}</div>
        </div>
        <div class="row">
            <div class="col-md-4 label">Status Valid</div>
            <div class="col-md-8">{{ $orangMitra->status_valid ? 'Valid' : 'Tidak Valid' }}</div>
        </div>
        <div class="row">
            <div class="col-md-4 label">Tanggal Dibuat</div>
            <div class="col-md-8">{{ $orangMitra->created_at->format('d-m-Y H:i:s') }}</div>
        </div>
        <div class="row">
            <div class="col-md-4 label">Tanggal Diperbarui</div>
            <div class="col-md-8">{{ $orangMitra->updated_at->format('d-m-Y H:i:s') }}</div>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.orang_mitra.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection