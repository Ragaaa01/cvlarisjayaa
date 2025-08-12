@extends('admin.layouts.base')
@section('title', 'Detail Data Akun')

@section('styles')
<style>
    .detail-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .detail-container dt {
        font-weight: 600;
        color: #333;
    }
    .detail-container dd {
        margin-bottom: 1rem;
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
<div class="container-fluid">
    <h2 class="mb-4">Detail Data Akun</h2>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Akun</h6>
        </div>
        <div class="card-body">
            <dl class="detail-container">
                <dt>Email</dt>
                <dd>{{ $akun->email ?? '-' }}</dd>
                <dt>Nama Orang</dt>
                <dd>{{ $akun->orang->nama_lengkap }}</dd>
                <dt>Role</dt>
                <dd>{{ $akun->role->nama_role }}</dd>
                <dt>Status Aktif</dt>
                <dd>{{ $akun->status_aktif ? 'Aktif' : 'Non-Aktif' }}</dd>
            </dl>
            <div class="detail-container mt-4">
                <a href="{{ route('admin.akun.index') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection