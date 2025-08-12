@extends('admin.layouts.base')
@section('title', 'Data Mitra')

@section('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .table th {
        text-align: center;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    thead.background {
        background-color: #014A7F !important;
        color: white;
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
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .table {
        min-width: 1000px;
    }
    .action-icon {
        font-size: 0.85rem;
        line-height: 1;
    }
    .action-column {
        position: sticky;
        right: 0;
        background-color: #fff;
        z-index: 1;
        box-shadow: -2px 0 2px rgba(0,0,0,0.1);
        min-width: 120px;
    }
    .action-header {
        position: sticky;
        right: 0;
        background-color: #014A7F !important;
        color: white;
        z-index: 2;
        box-shadow: -2px 0 2px rgba(0,0,0,0.1);
        min-width: 120px;
    }
    .action-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2px;
        padding: 0 5px;
    }
    .address-column {
        max-width: 300px;
        overflow-wrap: break-word;
        white-space: normal;
        word-break: break-word;
    }
    /* Styling untuk input pencarian */
    .dataTables_filter {
        margin-bottom: 1rem;
        text-align: right;
    }
    .dataTables_filter input {
        width: 200px;
        display: inline-block;
    }
    /* Styling untuk paginasi */
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 20px !important; /* Jarak atas paginasi */
        margin-bottom: 10px !important; /* Jarak bawah paginasi */
        display: flex;
        justify-content: flex-end; /* Paginasi rata kanan */
        align-items: center;
        padding-right: 15px; /* Sesuaikan dengan padding tabel untuk sejajar dengan garis */
    }
    .dataTables_paginate .pagination {
        display: inline-flex; /* Menjaga tombol paginasi rapat */
        justify-content: flex-end;
        align-items: center;
    }
    .paginate_button {
        margin: 0 2px !important;
        padding: 5px 10px !important;
        border: 1px solid #ddd !important;
        border-radius: 3px !important;
        text-decoration: none !important;
        color: #333 !important;
        background-color: #fff !important;
        cursor: pointer;
    }
    .paginate_button.current {
        background-color: #014A7F !important;
        color: white !important;
        border: 1px solid #014A7F !important;
    }
    .paginate_button:hover:not(.disabled) {
        background-color: #f8f9fa !important;
        color: #333 !important;
    }
    .paginate_button.disabled {
        color: #ccc !important;
        cursor: not-allowed !important;
        border: 1px solid #ddd !important;
        background-color: #fff !important;
    }
    .ellipsis {
        margin: 0 2px !important;
        padding: 5px 10px !important;
        color: #333 !important;
        cursor: default !important;
    }
    /* Styling untuk teks halaman di bawah paginasi */
    .dataTables_info_custom {
        text-align: center;
        margin-top: 5px !important;
        font-size: 0.9rem;
        color: #333;
        width: 100%; /* Pastikan teks terpusat */
    }
</style>
@endsection

@section('content')
<h2>Data Mitra</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('admin.mitra.create') }}" class="btn btn-custom">
                <i class="fas fa-plus mr-1"></i> Tambah Data
            </a>
        </div>
        <div>
            <a href="{{ route('admin.mitra.export.excel') }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.mitra.export.pdf') }}" class="btn btn-danger">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="background">
                    <tr>
                        <th>No</th>
                        <th>Nama Mitra</th>
                        <th>Alamat</th>
                        <th>Status Verifikasi</th>
                        <th class="action-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <!-- Elemen untuk teks halaman -->
        <div class="dataTables_info_custom" id="dataTable_info_custom"></div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.mitra.index") }}'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nama_mitra', name: 'nama_mitra' },
                { data: 'alamat_mitra', name: 'alamat_mitra', className: 'address-column' },
                { data: 'verified', name: 'verified' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'action-column' }
            ],
            responsive: true,
            scrollX: true,
            pageLength: 10,
            ordering: false,
            lengthChange: false,
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            },
            pagingType: 'simple_numbers', // Gunakan simple_numbers untuk Previous dan Next
            drawCallback: function(settings) {
                var api = this.api();
                var pageInfo = api.page.info();
                var currentPage = pageInfo.page + 1; // Halaman saat ini (1-based index)
                var totalPages = pageInfo.pages;
                var maxPagesToShow = 3; // Maksimal 3 angka ditampilkan

               
                // Ambil elemen paginasi
                var $pagination = $('.dataTables_paginate .pagination');
                $pagination.find('.paginate_button').not('.previous, .next').remove(); // Hapus nomor halaman default

                // Tambahkan tombol "First" jika bukan di halaman pertama
                if (currentPage > 1) {
                    var $firstButton = $('<a>', {
                        class: 'paginate_button',
                        href: '#',
                        text: '« First'
                    }).on('click', function(e) {
                        e.preventDefault();
                        api.page('first').draw('page');
                    });
                    $pagination.find('.paginate_button.previous').after($firstButton);
                }

                // Tentukan nomor halaman yang akan ditampilkan
                var startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
                var endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

                // Sesuaikan startPage jika endPage mencapai batas
                if (endPage - startPage < maxPagesToShow - 1) {
                    startPage = Math.max(1, endPage - maxPagesToShow + 1);
                }

                // Tambahkan elipsis di awal jika startPage > 2
                if (startPage > 2) {
                    var $ellipsisStart = $('<span>', {
                        class: 'ellipsis',
                        text: '...'
                    });
                    $pagination.find('.paginate_button.previous').after($ellipsisStart);
                }

                // Tambahkan nomor halaman
                for (var i = startPage; i <= endPage; i++) {
                    var $pageButton = $('<a>', {
                        class: 'paginate_button ' + (i === currentPage ? 'current' : ''),
                        href: '#',
                        text: i
                    }).on('click', function(e) {
                        e.preventDefault();
                        api.page(parseInt($(this).text()) - 1).draw('page');
                    });
                    $pagination.find('.paginate_button.next').before($pageButton);
                }

                // Tambahkan elipsis di akhir jika endPage < totalPages - 1
                if (endPage < totalPages - 1) {
                    var $ellipsisEnd = $('<span>', {
                        class: 'ellipsis',
                        text: '...'
                    });
                    $pagination.find('.paginate_button.next').before($ellipsisEnd);
                }

                // Tambahkan tombol "Last" jika bukan di halaman terakhir
                if (currentPage < totalPages) {
                    var $lastButton = $('<a>', {
                        class: 'paginate_button',
                        href: '#',
                        text: 'Last »'
                    }).on('click', function(e) {
                        e.preventDefault();
                        api.page('last').draw('page');
                    });
                    $pagination.find('.paginate_button.next').before($lastButton);
                }
            }
        });
    });
</script>
@endpush
@endsection