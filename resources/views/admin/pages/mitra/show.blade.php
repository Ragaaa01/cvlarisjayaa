@extends('admin.layouts.base')
@section('title', 'Detail Data Mitra')

@section('content')
<h2>Detail Data Mitra</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Mitra</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label><strong>Nama Mitra</strong></label>
                    <p>{{ $mitra->nama_mitra }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Alamat</strong></label>
                    <p>{{ $mitra->alamat_mitra ?? '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label><strong>Status Verifikasi</strong></label>
                    <p>{{ $mitra->verified ? 'Terverifikasi' : 'Belum Terverifikasi' }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection