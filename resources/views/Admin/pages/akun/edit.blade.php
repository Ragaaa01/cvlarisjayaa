@extends('admin.layouts.base')
@section('title', 'Edit Data Akun')

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
        font-size: 0.9rem;
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
    .btn-back {
        background-color: #6c757d !important;
        color: white !important;
    }
    .btn-back:hover {
        background-color: #5a6268 !important;
        color: white !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Edit Data Akun</h2>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Data Akun</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <form action="{{ route('admin.akun.update', $akun->id_akuns) }}" method="POST" class="form-container">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $akun->email) }}">
                </div>
                <div class="form-group">
                    <label for="password">Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label for="id_role">Role</label>
                    <select name="id_role" id="id_role" class="form-control" required>
                        <option value="">Pilih Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id_role }}" {{ old('id_role', $akun->id_role) == $role->id_role ? 'selected' : '' }}>{{ $role->nama_role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_orang">Nama Orang</label>
                    <select name="id_orang" id="id_orang" class="form-control" required>
                        <option value="">Pilih Orang</option>
                        @foreach ($orangs as $orang)
                            <option value="{{ $orang->id_orang }}" {{ old('id_orang', $akun->id_orang) == $orang->id_orang ? 'selected' : '' }}>{{ $orang->nama_lengkap }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_aktif">Status Aktif</label>
                    <select name="status_aktif" id="status_aktif" class="form-control" required>
                        <option value="1" {{ old('status_aktif', $akun->status_aktif) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status_aktif', $akun->status_aktif) == 0 ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>
                <div class="form-group d-flex justify-content-between">
                    <a href="{{ route('admin.akun.index') }}" class="btn btn-back">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-custom">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection