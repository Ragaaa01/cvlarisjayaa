@extends('admin.layouts.base')
@section('title', 'Data Akun')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Akun</h6>
        <a href="{{ route('admin.akun.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Akun
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Nama Orang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($akuns as $index => $akun)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $akun->email ?? '-' }}</td>
                            <td>{{ $akun->role->nama_role ?? '-' }}</td>
                            <td>{{ $akun->orang->nama_lengkap ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $akun->status_aktif ? 'success' : 'danger' }}">
                                    {{ $akun->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.akun.show', $akun->id_akun) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.akun.edit', $akun->id_akun) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $akun->id_akun }}"
                                    data-url="{{ route('admin.akun.destroy', $akun->id_akun) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        display: flex;
        align-items: center;
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
        $('.btn-delete').on('click', function() {
            const id = $(this).data('id');
            const url = $(this).data('url');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Akun ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire(
                                'Terhapus!',
                                'Akun telah dihapus.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Gagal menghapus akun: ' + (xhr.responseJSON.message || 'Terjadi kesalahan'),
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>
@endpush