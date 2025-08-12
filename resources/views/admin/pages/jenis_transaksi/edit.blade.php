@extends('admin.layouts.base')
@section('title', 'Edit Jenis Transaksi')

@section('styles')
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
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
<h2>Edit Jenis Transaksi</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Jenis Transaksi</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="form-container">
            <form action="{{ route('admin.jenis_transaksi.update', $jenisTransaksi->id_jenis_transaksi_detail) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="jenis_transaksi">Jenis Transaksi</label>
                    <input type="text" name="jenis_transaksi" id="jenis_transaksi" class="form-control" value="{{ old('jenis_transaksi', $jenisTransaksi->jenis_transaksi) }}" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-custom"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('admin.jenis_transaksi.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection