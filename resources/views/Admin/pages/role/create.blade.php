@extends('admin.layouts.base')
@section('title', 'Tambah Role')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Role</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.role.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama_role">Nama Role</label>
                <input type="text" name="nama_role" id="nama_role" class="form-control" value="{{ old('nama_role') }}" required>
                @error('nama_role')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.role.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection