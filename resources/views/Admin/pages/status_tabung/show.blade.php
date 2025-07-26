@extends('admin.layouts.base')
@section('title', 'Detail Status Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Status Tabung</h6>
    </div>
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <p><strong>Status Tabung:</strong> {{ $statusTabung->status_tabung }}</p>
                <p><strong>Dibuat Pada:</strong> {{ $statusTabung->created_at ? $statusTabung->created_at->format('d-m-Y H:i') : '-' }}</p>
                <p><strong>Diperbarui Pada:</strong> {{ $statusTabung->updated_at ? $statusTabung->updated_at->format('d-m-Y H:i') : '-' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.status_tabung.edit', $statusTabung->id_status_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
        <a href="{{ route('admin.status_tabung.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>
@endsection