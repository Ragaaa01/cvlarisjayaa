@extends('admin.layouts.base')
@section('title', 'Edit Role')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Role</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.role.update', $role->id_role) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nama_role">Nama Role</label>
                <input type="text" name="nama_role" id="nama_role" class="form-control" value="{{ old('nama_role', $role->nama_role) }}" required>
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