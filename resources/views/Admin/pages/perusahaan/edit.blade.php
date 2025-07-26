@extends('admin.layouts.base')
@section('title', 'Edit Perusahaan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Perusahaan</h6>
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
        <form action="{{ route('admin.perusahaan.update', $perusahaan->id_perusahaan) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nama_perusahaan">Nama Perusahaan</label>
                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="{{ old('nama_perusahaan', $perusahaan->nama_perusahaan) }}" required>
            </div>
            <div class="form-group">
                <label for="alamat_perusahaan">Alamat Perusahaan</label>
                <textarea class="form-control" id="alamat_perusahaan" name="alamat_perusahaan">{{ old('alamat_perusahaan', $perusahaan->alamat_perusahaan) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.perusahaan.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection