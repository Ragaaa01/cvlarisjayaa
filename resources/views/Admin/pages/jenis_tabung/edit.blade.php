@extends('admin.layouts.base')
@section('title', 'Edit Jenis Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Jenis Tabung</h6>
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
        <form action="{{ route('admin.jenis_tabung.update', $jenisTabung->id_jenis_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nama_jenis">Nama Jenis</label>
                <input type="text" name="nama_jenis" class="form-control" value="{{ old('nama_jenis', $jenisTabung->nama_jenis) }}" required>
            </div>
            <div class="form-group">
                <label for="harga_pinjam">Harga Pinjam (Rp)</label>
                <input type="number" name="harga_pinjam" class="form-control" value="{{ old('harga_pinjam', $jenisTabung->harga_pinjam) }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="harga_isi_ulang">Harga Isi Ulang (Rp)</label>
                <input type="number" name="harga_isi_ulang" class="form-control" value="{{ old('harga_isi_ulang', $jenisTabung->harga_isi_ulang) }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="nilai_deposit">Nilai Deposit (Rp)</label>
                <input type="number" name="nilai_deposit" class="form-control" value="{{ old('nilai_deposit', $jenisTabung->nilai_deposit) }}" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.jenis_tabung.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection