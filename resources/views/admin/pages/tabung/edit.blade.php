@extends('admin.layouts.base')
@section('title', 'Edit Tabung')

@section('styles')
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-group label {
        font-weight: 600;
        color: #333;
    }
    .form-control {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }
    .form-control:focus {
        border-color: #014A7F;
        box-shadow: 0 0 0 0.2rem rgba(1, 74, 127, 0.25);
    }
    .btn-custom {
        background-color: #014A7F !important;
        color: white !important;
        border: none;
    }
    .btn-custom:hover {
        background-color: #001B36 !important;
    }
    .btn-cancel {
        background-color: #6c757d !important;
        color: white !important;
    }
    .btn-cancel:hover {
        background-color: #5a6268 !important;
    }
    .card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .card-header {
        background-color: #014A7F;
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
    }
</style>
@endsection

@section('content')
<h2>Edit Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Form Edit Tabung</h6>
    </div>
    <div class="card-body form-container">
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
        <form action="{{ route('admin.tabung.update', $tabung->id_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="kode_tabung">Kode Tabung</label>
                <input type="text" class="form-control" id="kode_tabung" name="kode_tabung" value="{{ old('kode_tabung', $tabung->kode_tabung) }}" required>
            </div>
            <div class="form-group">
                <label for="id_jenis_tabung">Jenis Tabung</label>
                <select class="form-control" id="id_jenis_tabung" name="id_jenis_tabung" required>
                    <option value="">Pilih Jenis Tabung</option>
                    @foreach ($jenisTabungs as $jenisTabung)
                        <option value="{{ $jenisTabung->id_jenis_tabung }}" {{ old('id_jenis_tabung', $tabung->id_jenis_tabung) == $jenisTabung->id_jenis_tabung ? 'selected' : '' }}>{{ $jenisTabung->nama_jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="id_status_tabung">Status Tabung</label>
                <select class="form-control" id="id_status_tabung" name="id_status_tabung" required>
                    <option value="">Pilih Status Tabung</option>
                    @foreach ($statusTabungs as $statusTabung)
                        <option value="{{ $statusTabung->id_status_tabung }}" {{ old('id_status_tabung', $tabung->id_status_tabung) == $statusTabung->id_status_tabung ? 'selected' : '' }}>{{ $statusTabung->status_tabung }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="id_kepemilikan">Kepemilikan</label>
                <select class="form-control" id="id_kepemilikan" name="id_kepemilikan" required>
                    <option value="">Pilih Kepemilikan</option>
                    @foreach ($kepemilikans as $kepemilikan)
                        <option value="{{ $kepemilikan->id_kepemilikan }}" {{ old('id_kepemilikan', $tabung->id_kepemilikan) == $kepemilikan->id_kepemilikan ? 'selected' : '' }}>{{ $kepemilikan->keterangan_kepemilikan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group text-right">
                <a href="{{ route('admin.tabung.index') }}" class="btn btn-cancel mr-2">Batal</a>
                <button type="submit" class="btn btn-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection