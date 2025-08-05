@extends('admin.layouts.base')
@section('title', 'Tambah Kepemilikan Tabung')

@section('styles')
<style>
    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .form-control {
        border-radius: 0.35rem;
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
    .card {
        border-radius: 0.35rem;
    }
</style>
@endsection

@section('content')
<h2>Tambah Kepemilikan Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Kepemilikan Tabung</h6>
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
        <form action="{{ route('admin.kepemilikan_tabung.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="keterangan_kepemilikan">Keterangan Kepemilikan</label>
                <input type="text" class="form-control" id="keterangan_kepemilikan" name="keterangan_kepemilikan" value="{{ old('keterangan_kepemilikan') }}" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-custom">Simpan</button>
                <a href="{{ route('admin.kepemilikan_tabung.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection