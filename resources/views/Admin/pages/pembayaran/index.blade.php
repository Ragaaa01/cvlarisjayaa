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
        .btn-success {
            background-color: #28a745 !important;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838 !important;
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
        .modal-content {
            border-radius: 8px;
        }
        .modal-header {
            background-color: #014A7F;
            color: white;
        }
        .modal-footer {
            border-top: none;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-weight: bold;
        }
        .select2-container {
            width: 100% !important;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
        }
        .nav-tabs .nav-link.active {
            background-color: #014A7F;
            color: white;
            border-color: #014A7F;
        }
        .nav-tabs .nav-link {
            color: #014A7F;
        }
        .nav-tabs .nav-link:hover {
            border-color: #dee2e6 #dee2e6 #014A7F;
        }
    </style>
@endsection

@section('content')
<h2>Data Pembayaran</h2>
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
        <ul class="nav nav-tabs" id="pembayaranTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="belum-lunas-tab" data-toggle="tab" href="#belum-lunas" role="tab">Belum Lunas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="lunas-tab" data-toggle="tab" href="#lunas" role="tab">Lunas</a>
            </li>
        </ul>
        <div class="tab-content" id="pembayaranTabContent">
            <div class="tab-pane fade show active" id="belum-lunas" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover" id="dataTableBelumLunas" width="100%" cellspacing="0">
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
            <div class="tab-pane fade" id="lunas" role="tabpanel">
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover" id="dataTableLunas" width="100%" cellspacing="0">
                        <thead class="background">
                            <tr>
                                <th>No</th>
                                <th>Nama Pelanggan</th>
                                <th>Total Transaksi</th>
                                <th>Jumlah Pembayaran</th>
                                <th>Sisa Pembayaran</th>
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
    </div>
</div>

<!-- Modal untuk pembayaran -->
<div class="modal fade" id="pembayaranModal" tabindex="-1" role="dialog" aria-labelledby="pembayaranModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pembayaranModalLabel">Lakukan Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="pembayaranForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="modal_id_orang">Pelanggan</label>
                        <select name="id_orang" id="modal_id_orang" class="form-control select2" disabled required>
                            <option value="">Pilih Pelanggan</option>
                            @foreach ($orangs as $orang)
                                <option value="{{ $orang->id_orang }}">{{ $orang->nama_lengkap }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="id_orang" id="modal_id_orang_hidden">
                    </div>
                    <div class="form-group">
                        <label for="modal_total_transaksi">Total Transaksi</label>
                        <input type="number" name="total_transaksi" id="modal_total_transaksi" class="form-control" step="0.01" readonly required>
                    </div>
                    <div class="form-group">
                        <label for="modal_sisa_pembayaran">Sisa Pembayaran</label>
                        <input type="number" id="modal_sisa_pembayaran" class="form-control" step="0.01" readonly>
                    </div>
                    <div class="form-group">
                        <label for="modal_jumlah_pembayaran">Jumlah Pembayaran</label>
                        <input type="number" name="jumlah_pembayaran" id="modal_jumlah_pembayaran" class="form-control" step="0.01" required>
                        @error('jumlah_pembayaran')
                            <span class="text-danger">{{ $message }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="modal_metode_pembayaran">Metode Pembayaran</label>
                        <select name="metode_pembayaran" id="modal_metode_pembayaran" class="form-control" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="Kartu Kredit">Kartu Kredit</option>
                            <option value="E-Wallet">E-Wallet</option>
                        </select>
                        @error('metode_pembayaran')
                            <span class="text-danger">{{ $message }}</span>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            // Inisialisasi DataTables untuk Belum Lunas
            var tableBelumLunas = $('#dataTableBelumLunas').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("pembayaran.index") }}?status=belum_lunas',
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

            // Inisialisasi DataTables untuk Lunas
            var tableLunas = $('#dataTableLunas').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("pembayaran.index") }}?status=lunas',
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

            // Event untuk mengisi form modal
            $('#pembayaranModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var id_orang = button.data('id_orang');
                var total_transaksi = button.data('total_transaksi');
                var sisa = button.data('sisa');

                var modal = $(this);
                modal.find('#pembayaranForm').attr('action', '{{ url("pembayaran") }}/' + id);
                modal.find('#modal_id_orang').val(id_orang).trigger('change');
                modal.find('#modal_id_orang_hidden').val(id_orang);
                modal.find('#modal_total_transaksi').val(total_transaksi);
                modal.find('#modal_sisa_pembayaran').val(sisa);
                modal.find('#modal_jumlah_pembayaran').val('');
                modal.find('#modal_metode_pembayaran').val('');
            });
        });
    </script>
@endpush