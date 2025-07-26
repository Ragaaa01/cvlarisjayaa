@extends('admin.layouts.base')
@section('title', 'Detail Relasi Orang-Perusahaan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Relasi Orang-Perusahaan</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Orang:</strong> {{ $orangPerusahaan->orang->nama_lengkap }}</p>
                <p><strong>Nama Perusahaan:</strong> {{ $orangPerusahaan->perusahaan->nama_perusahaan }}</p>
                <p><strong>Status:</strong> {{ $orangPerusahaan->status }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $orangPerusahaan->created_at->format('d-m-Y H:i') }}</p>
                <p><strong>Tanggal Diperbarui:</strong> {{ $orangPerusahaan->updated_at->format('d-m-Y H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.orang_perusahaan.index') }}" class="btn btn-secondary">Kembali</a>
        <a href="{{ route('admin.orang_perusahaan.edit', $orangPerusahaan->id_orang_perusahaan) }}" class="btn btn-warning">Edit</a>
    </div>
</div>
@endsection