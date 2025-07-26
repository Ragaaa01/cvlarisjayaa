@extends('admin.layouts.base')
@section('title', 'Data Jenis Tabung')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Data Jenis Tabung</h6>
        <a href="{{ route('admin.jenis_tabung.create') }}" class="btn btn-primary btn-sm">Tambah Jenis Tabung</a>
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
                        <th>Nama Jenis</th>
                        <th>Harga Pinjam</th>
                        <th>Harga Isi Ulang</th>
                        <th>Nilai Deposit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jenisTabungs as $index => $jenisTabung)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jenisTabung->nama_jenis }}</td>
                            <td>Rp {{ number_format($jenisTabung->harga_pinjam, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($jenisTabung->harga_isi_ulang, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($jenisTabung->nilai_deposit, 2, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('admin.jenis_tabung.show', $jenisTabung->id_jenis_tabung) }}" class="btn btn-info btn-sm">Detail</a>
                                <a href="{{ route('admin.jenis_tabung.edit', $jenisTabung->id_jenis_tabung) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('admin.jenis_tabung.destroy', $jenisTabung->id_jenis_tabung) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus jenis tabung ini?')">Hapus</button>
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