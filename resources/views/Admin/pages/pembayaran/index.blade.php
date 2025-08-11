@extends('admin.layouts.base')
@section('title', 'Data Pembayaran')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
        .dataTables_paginate {
            margin-top: 20px !important;
            margin-bottom: 10px !important;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-right: 15px;
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
        .tanggal-column {
            text-align: right !important;
        }
    </style>
@endsection

@section('content')
<h2>Data Pembayaran Lunas</h2>
<div class="card shadow mt-4 mb-4">
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
                        <th>Nama Pelanggan</th>
                        <th>Total Transaksi</th>
                        <th>Jumlah Pembayaran</th>
                        <th>Sisa Tagihan</th>
                        <th>Metode Pembayaran</th>
                        <th class="tanggal-column">Tanggal</th>
                        <th>Waktu</th>
                        <th class="action-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            var table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("pembayaran.index") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_orang', name: 'orang.nama_lengkap' },
                    { data: 'total_transaksi', name: 'total_transaksi' },
                    { data: 'jumlah_pembayaran', name: 'jumlah_pembayaran' },
                    { data: 'sisa_pembayaran', name: 'sisa_pembayaran' },
                    { data: 'metode_pembayaran', name: 'metode_pembayaran' },
                    { data: 'tanggal_pembayaran', name: 'tanggal_pembayaran', className: 'tanggal-column' },
                    { data: 'waktu_pembayaran', name: 'waktu_pembayaran' },
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
                pagingType: 'simple_numbers',
                drawCallback: function(settings) {
                    var api = this.api();
                    var pageInfo = api.page.info();
                    var currentPage = pageInfo.page + 1;
                    var totalPages = api.page.pages;
                    var maxPagesToShow = 3;

                    var $pagination = $('.dataTables_paginate', this.dom).find('.pagination');
                    $pagination.find('.paginate_button').not('.previous, .next').remove();

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

                    var startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
                    var endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

                    if (endPage - startPage < maxPagesToShow - 1) {
                        startPage = Math.max(1, endPage - maxPagesToShow + 1);
                    }

                    if (startPage > 2) {
                        var $ellipsisStart = $('<span>', {
                            class: 'ellipsis',
                            text: '...'
                        });
                        $pagination.find('.paginate_button.previous').after($ellipsisStart);
                    }

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

                    if (endPage < totalPages - 1) {
                        var $ellipsisEnd = $('<span>', {
                            class: 'ellipsis',
                            text: '...'
                        });
                        $pagination.find('.paginate_button.next').before($ellipsisEnd);
                    }

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