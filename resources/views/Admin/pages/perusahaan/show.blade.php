@extends('admin.layouts.base')
@section('title', 'Detail Perusahaan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Perusahaan</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Nama Perusahaan</h6>
                <p>{{ $perusahaan->nama_perusahaan }}</p>
            </div>
            <div class="col-md-6">
                <h6>Alamat Perusahaan</h6>
                <p>{{ $perusahaan->alamat_perusahaan ?? '-' }}</p>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.perusahaan.edit', $perusahaan->id_perusahaan) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('admin.perusahaan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection