@extends('admin.layouts.base')
@section('title', 'Detail Data Role')

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
    <h2 class="mb-4">Detail Data Role</h2>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Role</h6>
        </div>
        <div class="card-body">
            <dl class="detail-container">
                <dt>Nama Role</dt>
                <dd>{{ $role->nama_role }}</dd>
            </dl>
            <div class="detail-container mt-4">
                <a href="{{ route('admin.role.index') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection