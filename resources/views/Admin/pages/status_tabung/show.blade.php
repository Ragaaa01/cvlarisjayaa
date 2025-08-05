@extends('admin.layouts.base')
@section('title', 'Detail Status Tabung')

@section('styles')
<style>
    .detail-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .card-header {
        background-color: #014A7F;
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .detail-item {
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #333;
    }
    .detail-value {
        color: #555;
    }
    .btn-custom {
        background-color: #014A7F !important;
        color: white !important;
        border: none;
    }
    .btn-custom:hover {
        background-color: #001B36 !important;
    }
</style>
@endsection

@section('content')
<h2>Detail Status Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Informasi Status Tabung</h6>
    </div>
    <div class="card-body detail-container">
        <div class="detail-item">
            <div class="row">
                <div class="col-md-4 detail-label">Status Tabung</div>
                <div class="col-md-8 detail-value">{{ $statusTabung->status_tabung }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="row">
                <div class="col-md-4 detail-label">Dibuat Pada</div>
                <div class="col-md-8 detail-value">{{ $statusTabung->created_at->format('d-m-Y H:i:s') }}</div>
            </div>
        </div>
        <div class="detail-item">
            <div class="row">
                <div class="col-md-4 detail-label">Diperbarui Pada</div>
                <div class="col-md-8 detail-value">{{ $statusTabung->updated_at->format('d-m-Y H:i:s') }}</div>
            </div>
        </div>
        <div class="text-right mt-3">
            <a href="{{ route('admin.status_tabung.index') }}" class="btn btn-custom">Kembali</a>
        </div>
    </div>
</div>
@endsection