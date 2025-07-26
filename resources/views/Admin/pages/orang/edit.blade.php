@extends('admin.layouts.base')
@section('title', 'Edit Orang')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Orang</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.orang.update', $orang->id_orang) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $orang->nama_lengkap) }}">
                    @error('nama_lengkap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $orang->nik) }}">
                    @error('nik')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="no_telepon">No Telepon</label>
                    <input type="text" name="no_telepon" class="form-control @error('no_telepon') is-invalid @enderror" value="{{ old('no_telepon', $orang->no_telepon) }}">
                    @error('no_telepon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $orang->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="id_perusahaan">Perusahaan (Opsional)</label>
                    <select name="id_perusahaan" id="id_perusahaan" class="form-control select2 @error('id_perusahaan') is-invalid @enderror">
                        <option value="">Pilih Perusahaan</option>
                        @foreach ($perusahaans as $perusahaan)
                            <option value="{{ $perusahaan->id_perusahaan }}" {{ $orang->perusahaan->contains('id_perusahaan', $perusahaan->id_perusahaan) ? 'selected' : '' }}>
                                {{ $perusahaan->nama_perusahaan }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_perusahaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('admin.orang.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#id_perusahaan').select2({
                placeholder: 'Pilih Perusahaan',
                allowClear: true,
                minimumInputLength: 0, // Mulai pencarian dari 0 karakter
                width: '100%',
                dropdownCssClass: 'select2-dropdown-custom'
            });
        });
    </script>
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem);
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem);
        }
        .select2-dropdown-custom {
            z-index: 1050; /* Pastikan dropdown di atas elemen lain */
        }
        .is-invalid + .select2-container .select2-selection--single {
            border-color: #dc3545;
        }
    </style>
@endpush