@extends('admin.layouts.base')
@section('title', 'Tambah Akun')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Akun Baru</h6>
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
        <form action="{{ route('admin.akun.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                    placeholder="Masukkan email" required>
            </div>
            <div class="form-group">
                <label for="password">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="Masukkan password" required>
            </div>
            <div class="form-group">
                <label for="id_role">Role <span class="text-danger">*</span></label>
                <select name="id_role" id="id_role" class="form-control" required>
                    <option value="">Pilih Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id_role }}" {{ $role->nama_role == 'Pelanggan' ? 'selected' : '' }}>
                            {{ $role->nama_role }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="id_orang">Orang</label>
                <select name="id_orang" id="id_orang" class="form-control select2">
                    <option value="">Pilih Orang (Opsional)</option>
                    @foreach ($orangs as $orang)
                        <option value="{{ $orang->id_orang }}">{{ $orang->nama_lengkap }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status_aktif">Status Aktif <span class="text-danger">*</span></label>
                <select name="status_aktif" id="status_aktif" class="form-control" required>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.akun.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 untuk dropdown Orang
        $('#id_orang').select2({
            placeholder: "Pilih Orang (Opsional)",
            allowClear: true
        });

        // SweetAlert2 untuk error validasi
        @if ($errors->has('email'))
            Swal.fire({
                icon: 'error',
                title: 'Email Tidak Valid',
                text: '{{ $errors->first('email') }}',
                confirmButtonText: 'OK'
            });
        @elseif ($errors->any())
            let errorMessage = '';
            @foreach ($errors->all() as $error)
                errorMessage += '{{ $error }}<br>';
            @endforeach
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan Akun',
                html: errorMessage,
                confirmButtonText: 'OK'
            });
        @endif
    });
</script>
@endpush