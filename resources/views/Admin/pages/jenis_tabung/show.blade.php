@extends('admin.layouts.base')
@section('title', 'Detail Jenis Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Jenis Tabung</h6>
    </div>
    <div class="card-body">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Jenis:</strong> {{ $jenisTabung->nama_jenis }}</p>
                <p><strong>Harga Pinjam:</strong> Rp {{ number_format($jenisTabung->harga_pinjam, 2, ',', '.') }}</p>
                <p><strong>Harga Isi Ulang:</strong> Rp {{ number_format($jenisTabung->harga_isi_ulang, 2, ',', '.') }}</p>
                <p><strong>Nilai Deposit:</strong> Rp {{ number_format($jenisTabung->nilai_deposit, 2, ',', '.') }}</p>
                <p><strong>Dibuat Pada:</strong> {{ $jenisTabung->created_at ? $jenisTabung->created_at->format('d-m-Y H:i') : '-' }}</p>
                <p><strong>Diperbarui Pada:</strong> {{ $jenisTabung->updated_at ? $jenisTabung->updated_at->format('d-m-Y H:i') : '-' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.jenis_tabung.edit', $jenisTabung->id_jenis_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
        <a href="{{ route('admin.jenis_tabung.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
</div>
@endsection