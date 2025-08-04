@extends('admin.layouts.base')
@section('title', 'Edit Status Tabung')

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
<h2>Edit Status Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Form Edit Status Tabung</h6>
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
        <form action="{{ route('admin.status_tabung.update', $statusTabung->id_status_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="status_tabung">Status Tabung</label>
                <input type="text" class="form-control" id="status_tabung" name="status_tabung" value="{{ old('status_tabung', $statusTabung->status_tabung) }}" required>
            </div>
            <div class="form-group text-right">
                <a href="{{ route('admin.status_tabung.index') }}" class="btn btn-cancel mr-2">Batal</a>
                <button type="submit" class="btn btn-custom">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection