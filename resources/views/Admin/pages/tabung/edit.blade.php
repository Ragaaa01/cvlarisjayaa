@extends('admin.layouts.base')
@section('title', 'Edit Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Tabung</h6>
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
        <form action="{{ route('admin.tabung.update', $tabung->id_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="kode_tabung">Kode Tabung</label>
                <input type="text" name="kode_tabung" class="form-control" value="{{ old('kode_tabung', $tabung->kode_tabung) }}" required>
            </div>
            <div class="form-group">
                <label for="id_jenis_tabung">Jenis Tabung</label>
                <select name="id_jenis_tabung" class="form-control select2" required>
                    <option value="">Pilih Jenis Tabung</option>
                    @foreach ($jenisTabungs as $jenis)
                        <option value="{{ $jenis->id_jenis_tabung }}" {{ old('id_jenis_tabung', $tabung->id_jenis_tabung) == $jenis->id_jenis_tabung ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="id_status_tabung">Status Tabung</label>
                <select name="id_status_tabung" class="form-control select2" required>
                    <option value="">Pilih Status Tabung</option>
                    @foreach ($statusTabungs as $status)
                        <option value="{{ $status->id_status_tabung }}" {{ old('id_status_tabung', $tabung->id_status_tabung) == $status->id_status_tabung ? 'selected' : '' }}>{{ $status->status_tabung }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.tabung.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Pilih opsi",
            allowClear: true
        });
    });
</script>
@endpush