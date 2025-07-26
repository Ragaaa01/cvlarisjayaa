@extends('admin.layouts.base')
@section('title', 'Detail Akun')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Akun</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><strong>Email:</strong></h6>
                <p>{{ $akun->email ?? '-' }}</p>
                <h6><strong>Role:</strong></h6>
                <p>{{ $akun->role->nama_role ?? '-' }}</p>
                <h6><strong>Nama Orang:</strong></h6>
                <p>{{ $akun->orang->nama_lengkap ?? '-' }}</p>
                <h6><strong>Status Aktif:</strong></h6>
                <p>
                    <span class="badge badge-{{ $akun->status_aktif ? 'success' : 'danger' }}">
                        {{ $akun->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </p>
            </div>
        </div>
        <a href="{{ route('admin.akun.index') }}" class="btn btn-secondary">Kembali</a>
        <a href="{{ route('admin.akun.edit', $akun->id_akun) }}" class="btn btn-warning">Edit</a>
    </div>
</div>
@endsection