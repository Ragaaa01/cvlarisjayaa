@extends('admin.layouts.base')
@section('title', 'Detail Kepemilikan Tabung')

@section('styles')
<style>
    .card {
        border-radius: 0.35rem;
    }
    .detail-label {
        font-weight: 600;
        color: #014A7F;
    }
    .detail-value {
        color: #333;
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
<h2>Detail Kepemilikan Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Kepemilikan Tabung</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <span class="detail-label">Keterangan Kepemilikan:</span>
                    <span class="detail-value">{{ $kepemilikan->keterangan_kepemilikan }}</span>
                </div>
                <div class="mb-3">
                    <span class="detail-label">Dibuat Pada:</span>
                    <span class="detail-value">{{ $kepemilikan->created_at->format('d-m-Y H:i:s') }}</span>
                </div>
                <div class="mb-3">
                    <span class="detail-label">Diperbarui Pada:</span>
                    <span class="detail-value">{{ $kepemilikan->updated_at->format('d-m-Y H:i:s') }}</span>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('admin.kepemilikan_tabung.edit', $kepemilikan->id_kepemilikan) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form action="{{ route('admin.kepemilikan_tabung.destroy', $kepemilikan->id_kepemilikan) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                    <i class="fas fa-trash mr-1"></i> Hapus
                </button>
            </form>
            <a href="{{ route('admin.kepemilikan_tabung.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection