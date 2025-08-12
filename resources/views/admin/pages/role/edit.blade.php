@extends('admin.layouts.base')
@section('title', 'Edit Data Role')

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
    <h2 class="mb-4">Edit Data Role</h2>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Form Edit Data Role</h6>
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
            <form action="{{ route('admin.role.update', $role->id_role) }}" method="POST" class="form-container">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nama_role">Nama Role</label>
                    <input type="text" name="nama_role" id="nama_role" class="form-control" value="{{ old('nama_role', $role->nama_role) }}" required>
                </div>
                <div class="form-group d-flex justify-content-between">
                    <a href="{{ route('admin.role.index') }}" class="btn btn-back">
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