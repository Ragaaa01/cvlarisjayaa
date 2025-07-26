@extends('admin.layouts.base')
@section('title', 'Detail Orang')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Detail Orang</h1>
    <div class="card mb-4">
        <div class="cardo-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Nama Lengkap</h5>
                    <p>{{ $orang->nama_lengkap }}</p>
                </div>
                <div class="col-md-6">
                    <h5>NIK</h5>
                    <p>{{ $orang->nik }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h5>No Telepon</h5>
                    <p>{{ $orang->no_telepon }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Alamat</h5>
                    <p>{{ $orang->alamat }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('admin.orang.edit', $orang->id_orang) }}" class="btn btn-warning">Edit</a>
                    <a href="{{ route('admin.orang.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection