@extends('admin.layouts.base')
@section('title', 'Edit Data Orang Mitra')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-container {
        max-width: 600px;
        margin: 0 auto;
    }
    .form-group label {
        font-weight: 600;
        color: #333;
    }
    .form-control {
        border-radius: 0.25rem;
        font-size: 0.9rem;
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
    .btn-back {
        background-color: #6c757d !important;
        color: white !important;
    }
    .btn-back:hover {
        background-color: #5a6268 !important;
        color: white !important;
    }
    .select2-container--default .select2-selection--single {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        height: 38px;
        font-size: 0.9rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endsection

@section('content')
<h2>Edit Data Orang Mitra</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Data Orang Mitra</h6>
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
        <form action="{{ route('admin.orang_mitra.update', $orangMitra->id_orang_mitra) }}" method="POST" class="form-container">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="id_orang">Nama Orang</label>
                <select name="id_orang" id="id_orang" class="form-control select2" required>
                    <option value="">Pilih Orang</option>
                    @foreach ($orangs as $orang)
                        <option value="{{ $orang->id_orang }}" {{ $orangMitra->id_orang == $orang->id_orang ? 'selected' : '' }}>
                            {{ $orang->nama_lengkap }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="id_mitra">Nama Mitra</label>
                <select name="id_mitra" id="id_mitra" class="form-control select2" required>
                    <option value="">Pilih Mitra</option>
                    @foreach ($mitras as $mitra)
                        <option value="{{ $mitra->id_mitra }}" {{ $orangMitra->id_mitra == $mitra->id_mitra ? 'selected' : '' }}>
                            {{ $mitra->nama_mitra }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status_valid">Status Valid</label>
                <select name="status_valid" id="status_valid" class="form-control" required @if($hasValidOrang && !$orangMitra->status_valid) disabled @endif>
                    <option value="1" {{ $orangMitra->status_valid ? 'selected' : '' }}>Valid</option>
                    <option value="0" {{ !$orangMitra->status_valid ? 'selected' : '' }}>Tidak Valid</option>
                </select>
                @if($hasValidOrang && !$orangMitra->status_valid)
                    <small class="text-danger">Mitra ini sudah memiliki orang dengan status valid. Anda tidak dapat mengubah status ini menjadi valid.</small>
                @endif
            </div>
            <div class="form-group d-flex justify-content-between">
                <a href="{{ route('admin.orang_mitra.index') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-custom">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih opsi",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@endsection