@extends('admin.layouts.base')
@section('title', 'Edit Status Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Status Tabung</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('admin.status_tabung.update', $statusTabung->id_status_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="status_tabung">Status Tabung</label>
                <input type="text" name="status_tabung" class="form-control" value="{{ old('status_tabung', $statusTabung->status_tabung) }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.status_tabung.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection