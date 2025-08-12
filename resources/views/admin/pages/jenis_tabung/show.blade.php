@extends('admin.layouts.base')
@section('title', 'Detail Jenis Tabung')

@section('styles')
<style>
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .table th, .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .table th {
        width: 30%;
        background-color: #014A7F;
        color: white;
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
</style>
@endsection

@section('content')
<h2>Detail Jenis Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail Data Jenis Tabung</h6>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th>Nama Jenis</th>
                <td>{{ $jenisTabung->nama_jenis }}</td>
            </tr>
            <tr>
                <th>Harga Pinjam (Rp)</th>
                <td>{{ number_format($jenisTabung->harga_pinjam, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Harga Isi Ulang (Rp)</th>
                <td>{{ number_format($jenisTabung->harga_isi_ulang, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Nilai Deposit (Rp)</th>
                <td>{{ number_format($jenisTabung->nilai_deposit, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Dibuat Pada</th>
                <td>{{ $jenisTabung->created_at->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <th>Diperbarui Pada</th>
                <td>{{ $jenisTabung->updated_at->format('d-m-Y H:i:s') }}</td>
            </tr>
        </table>
        <div class="mt-3">
            <a href="{{ route('admin.jenis_tabung.index') }}" class="btn btn-custom">Kembali</a>
        </div>
    </div>
</div>
@endsection