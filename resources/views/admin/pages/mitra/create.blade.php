@extends('admin.layouts.base')
@section('title', 'Tambah Data Mitra')

@section('content')
<h2>Tambah Data Mitra</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Mitra</h6>
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
        <form action="{{ route('admin.mitra.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama_mitra">Nama Mitra</label>
                <input type="text" class="form-control" id="nama_mitra" name="nama_mitra" value="{{ old('nama_mitra') }}" required>
            </div>
            <div class="form-group">
                <label for="alamat_mitra">Alamat</label>
                <textarea class="form-control" id="alamat_mitra" name="alamat_mitra" rows="4" required>{{ old('alamat_mitra') }}</textarea>
            </div>
            <button type="submit" class="btn btn-custom">Simpan</button>
            <a href="{{ route('admin.mitra.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection