@extends('admin.layouts.base')
@section('title', 'Data Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Tabung</h6>
        <a href="{{ route('admin.tabung.create') }}" class="btn btn-primary btn-sm">Tambah Tabung</a>
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
                        <th>Kode Tabung</th>
                        <th>Jenis Tabung</th>
                        <th>Status Tabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tabungs as $index => $tabung)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $tabung->kode_tabung }}</td>
                            <td>{{ $tabung->jenisTabung->nama_jenis ?? 'Tidak diketahui' }}</td>
                            <td>{{ $tabung->statusTabung->status_tabung ?? 'Tidak diketahui' }}</td>
                            <td>
                                <a href="{{ route('admin.tabung.show', $tabung->id_tabung) }}" class="btn btn-info btn-sm">Detail</a>
                                <a href="{{ route('admin.tabung.edit', $tabung->id_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.tabung.destroy', $tabung->id_tabung) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-btn" data-id="{{ $tabung->id_tabung }}">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            }
        });

        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Yakin ingin menghapus tabung ini?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush