@extends('admin.layouts.base')
@section('title', 'Detail Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Tabung</h6>
    </div>
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <p><strong>Kode Tabung:</strong> {{ $tabung->kode_tabung }}</p>
                <p><strong>Jenis Tabung:</strong> {{ $tabung->jenisTabung->nama_jenis ?? 'Tidak diketahui' }}</p>
                <p><strong>Status Tabung:</strong> {{ $tabung->statusTabung->status_tabung ?? 'Tidak diketahui' }}</p>
                <p><strong>Dibuat Pada:</strong> {{ $tabung->created_at ? $tabung->created_at->format('d-m-Y H:i') : 'Belum tersedia' }}</p>
                <p><strong>Diperbarui Pada:</strong> {{ $tabung->updated_at ? $tabung->updated_at->format('d-m-Y H:i') : 'Belum tersedia' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.tabung.edit', $tabung->id_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
        <a href="{{ route('admin.tabung.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>
@endsection