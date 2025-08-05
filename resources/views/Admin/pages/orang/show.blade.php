@extends('admin.layouts.base')
@section('title', 'Detail Orang')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Data Orang</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><strong>Nama Lengkap</strong></label>
                    <p>{{ $orang->nama_lengkap }}</p>
                </div>
                <div class="form-group">
                    <label><strong>NIK</strong></label>
                    <p>{{ $orang->nik ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <label><strong>No Telepon</strong></label>
                    <p>{{ $orang->no_telepon }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Alamat</strong></label>
                    <p>{{ $orang->alamat ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a href="{{ route('admin.orang.edit', $orang->id_orang) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.orang.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
    </div>
</div>
@endsection