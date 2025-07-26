@extends('admin.layouts.base')
@section('title', 'Data Status Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Status Tabung</h6>
        <a href="{{ route('admin.status_tabung.create') }}" class="btn btn-primary btn-sm">Tambah Status Tabung</a>
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
                        <th>Status Tabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statusTabungs as $index => $statusTabung)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $statusTabung->status_tabung }}</td>
                            <td>
                                <a href="{{ route('admin.status_tabung.show', $statusTabung->id_status_tabung) }}" class="btn btn-info btn-sm">Detail</a>
                                <a href="{{ route('admin.status_tabung.edit', $statusTabung->id_status_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.status_tabung.destroy', $statusTabung->id_status_tabung) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus status tabung ini?')">Hapus</button>
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
    });
</script>
@endpush