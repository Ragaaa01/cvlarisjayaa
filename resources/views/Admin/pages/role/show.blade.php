@extends('admin.layouts.base')
@section('title', 'Detail Role')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Role</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Role:</strong> {{ $role->nama_role }}</p>
                <p><strong>Tanggal Dibuat:</strong> {{ $role->created_at->format('d-m-Y H:i') }}</p>
                <p><strong>Tanggal Diperbarui:</strong> {{ $role->updated_at->format('d-m-Y H:i') }}</p>
            </div>
        </div>
        <a href="{{ route('admin.role.index') }}" class="btn btn-secondary">Kembali</a>
        <a href="{{ route('admin.role.edit', $role->id_role) }}" class="btn btn-warning">Edit</a>
    </div>
</div>
@endsection