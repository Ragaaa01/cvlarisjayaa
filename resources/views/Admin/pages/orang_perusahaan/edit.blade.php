@extends('admin.layouts.base')
@section('title', 'Edit Relasi Orang-Perusahaan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Relasi Orang-Perusahaan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.orang_perusahaan.update', $orangPerusahaan->id_orang_perusahaan) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="id_orang">Nama Orang</label>
                <select name="id_orang" id="id_orang" class="form-control select2" required>
                    <option value="">Pilih Orang</option>
                    @foreach ($orangs as $orang)
                        <option value="{{ $orang->id_orang }}" {{ $orangPerusahaan->id_orang == $orang->id_orang ? 'selected' : '' }}>
                            {{ $orang->nama_lengkap }} ({{ $orang->nik }})
                        </option>
                    @endforeach
                </select>
                @error('id_orang')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="id_perusahaan">Nama Perusahaan</label>
                <select name="id_perusahaan" id="id_perusahaan" class="form-control select2" required>
                    <option value="">Pilih Perusahaan</option>
                    @foreach ($perusahaans as $perusahaan)
                        <option value="{{ $perusahaan->id_perusahaan }}" {{ $orangPerusahaan->id_perusahaan == $perusahaan->id_perusahaan ? 'selected' : '' }}>
                            {{ $perusahaan->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
                @error('id_perusahaan')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control select2" required>
                    <option value="">Pilih Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ $orangPerusahaan->status == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.orang_perusahaan.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
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
@endsection