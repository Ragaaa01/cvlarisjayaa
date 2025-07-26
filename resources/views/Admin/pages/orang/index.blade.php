@extends('admin.layouts.base')
@section('title', 'Data Orang')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Data Orang</h1>

    <!-- Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Tabel Data -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex justify-content-between w-100">
                <a href="{{ route('admin.orang.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Data
                </a>
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-download mr-1"></i> Export Data
                    </button>
                    <div class="dropdown-menu" aria-labelledby="exportDropdown">
                        <a class="dropdown-item" href="{{ route('admin.orang.export.excel') }}" id="exportExcel">Export ke Excel</a>
                        <a class="dropdown-item" href="{{ route('admin.orang.export.pdf') }}" id="exportPdf">Export ke PDF</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="background">
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>No Telepon</th>
                            <th>Alamat</th>
                            <th>Perusahaan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orangs as $d)
                            <tr>
                                <td class="text-center">{{ $orangs->firstItem() + $loop->index }}</td>
                                <td>{{ $d->nama_lengkap }}</td>
                                <td>{{ $d->nik }}</td>
                                <td>{{ $d->no_telepon }}</td>
                                <td>{{ $d->alamat }}</td>
                                <td>
                                    @if($d->perusahaan->isNotEmpty())
                                        {{ $d->perusahaan->pluck('nama_perusahaan')->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex">
                                        <a href="{{ route('admin.orang.show', $d->id_orang) }}" class="btn btn-sm btn-info mr-1" title="Lihat Detail">
                                            <i class="fas fa-eye action-icon"></i>
                                        </a>
                                        <a href="{{ route('admin.orang.edit', $d->id_orang) }}" class="btn btn-sm btn-warning mr-1" title="Edit Data">
                                            <i class="fas fa-edit action-icon"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.orang.destroy', $d->id_orang) }}" onsubmit="return confirm('Yakin ingin hapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Hapus Data">
                                                <i class="fas fa-trash-alt action-icon"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Paginasi -->
            <div class="d-flex justify-content-center mt-3">
                {{ $orangs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Debugging dependensi
        console.log('jQuery:', typeof $ !== 'undefined' ? 'Loaded' : 'Not Loaded');
        console.log('Popper:', typeof Popper !== 'undefined' ? 'Loaded' : 'Not Loaded');
        console.log('Bootstrap Dropdown:', typeof $.fn.dropdown !== 'undefined' ? 'Loaded' : 'Not Loaded');

        // Notifikasi SweetAlert2 untuk session success/warning/error
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @elseif(session('warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: '{{ session('warning') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @elseif($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ $errors->first() }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        // Logika untuk export Excel dan PDF
        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Mengunduh...',
                text: 'Sedang memproses file Excel.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            fetch(e.target.href, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                if (!response.ok) throw new Error('Gagal mengunduh Excel');
                return response.blob();
            }).then(blob => {
                Swal.close();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Data_Orang_' + new Date().toISOString().replace(/:/g, '-') + '.xlsx';
                document.body.appendChild(a);
                a.click();
                a.remove();
            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal mengunduh file Excel. Silakan coba lagi.',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        });

        $('#exportPdf').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Mengunduh...',
                text: 'Sedang memproses file PDF.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            fetch(e.target.href, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                if (!response.ok) throw new Error('Gagal mengunduh PDF');
                return response.blob();
            }).then(blob => {
                Swal.close();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Data_Orang_' + new Date().toISOString().replace(/:/g, '-') + '.pdf';
                document.body.appendChild(a);
                a.click();
                a.remove();
            }).catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal mengunduh file PDF. Silakan coba lagi.',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        });

        // Debugging untuk dropdown
        $('#exportDropdown').on('click', function() {
            console.log('Tombol Export Data diklik');
            if ($('.dropdown-menu').is(':visible')) {
                console.log('Dropdown terbuka');
            } else {
                console.log('Dropdown tidak terbuka');
            }
        });
    });
</script>

<style>
    .table th, .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .action-icon {
        font-size: 0.85rem;
        line-height: 1;
    }
    .background {
        background-color: #014A7F !important;
        color: white !important;
    }
    .btn-primary {
        background-color: #014A7F !important;
        border-color: #014A7F !important;
    }
    .btn-primary:hover {
        background-color: #001B36 !important;
        border-color: #001B36 !important;
    }
    .btn-success {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
    }
    .btn-success:hover {
        background-color: #218838 !important;
        border-color: #218838 !important;
    }
    .dropdown-menu {
        min-width: 10rem;
        margin: 0.125rem 0 0;
        border: 1px solid rgba(0,0,0,0.15);
        border-radius: 0.25rem;
    }
</style>
@endpush