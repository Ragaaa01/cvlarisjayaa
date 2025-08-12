@extends('admin.layouts.base')
@section('title', 'Data Transaksi')

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
            min-width: 900px;
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
        .filter-container {
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }
        .filter-container select, .filter-container input {
            max-width: 200px;
        }
        .filter-inputs {
            display: none;
        }
        .filter-inputs.active {
            display: flex;
            gap: 1rem;
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
            border-radius: 2px !important;
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
        .status-label {
            font-size: 0.9rem;
            font-weight: bold;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .status-belum-dibayar {
            background-color: #dc3545;
        }
        .status-belum-lunas {
            background-color: #ffc107;
        }
        .status-lunas {
            background-color: #28a745;
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
    </style>
@endsection

@section('content')
<h2>Data Transaksi</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('transaksi.create') }}" class="btn btn-custom">
                <i class="fas fa-plus mr-1"></i> Tambah Data
            </a>
        </div>
        <div>
            <a href="{{ route('transaksi.export.excel') }}?filter_type={{ request('filter_type') }}&specific_date={{ request('specific_date') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&month={{ request('month') }}&jenis_transaksi={{ request('jenis_transaksi') }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="{{ route('transaksi.export.pdf') }}?filter_type={{ request('filter_type') }}&specific_date={{ request('specific_date') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&month={{ request('month') }}&jenis_transaksi={{ request('jenis_transaksi') }}" class="btn btn-danger">
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
        <div class="filter-container">
            <label for="filter_type">Filter Waktu:</label>
            <select class="form-control d-inline-block" id="filter_type" name="filter_type">
                <option value="all" {{ request('filter_type') == 'all' || empty(request('filter_type')) ? 'selected' : '' }}>Semua Data</option>
                <option value="specific_date" {{ request('filter_type') == 'specific_date' ? 'selected' : '' }}>Pilih Tanggal</option>
                <option value="date_range" {{ request('filter_type') == 'date_range' ? 'selected' : '' }}>Pilih Rentang Waktu</option>
                <option value="month" {{ request('filter_type') == 'month' ? 'selected' : '' }}>Pilih Bulan</option>
                <option value="belum_lunas" {{ request('filter_type') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
            </select>
            <label for="jenis_transaksi">Jenis Transaksi:</label>
            <select class="form-control d-inline-block" id="jenis_transaksi" name="jenis_transaksi">
                <option value="all" {{ request('jenis_transaksi') == 'all' || empty(request('jenis_transaksi')) ? 'selected' : '' }}>Semua Jenis</option>
                <option value="peminjaman" {{ request('jenis_transaksi') == 'peminjaman' ? 'selected' : '' }}>Peminjaman</option>
                <option value="isi ulang" {{ request('jenis_transaksi') == 'isi ulang' ? 'selected' : '' }}>Isi Ulang</option>
            </select>
            <div class="filter-inputs" id="specific_date_input">
                <label for="specific_date">Tanggal:</label>
                <input type="date" class="form-control d-inline-block" id="specific_date" name="specific_date" value="{{ request('specific_date') }}">
            </div>
            <div class="filter-inputs" id="date_range_input">
                <label for="start_date">Dari:</label>
                <input type="date" class="form-control d-inline-block" id="start_date" name="start_date" value="{{ request('start_date') }}">
                <label for="end_date">Sampai:</label>
                <input type="date" class="form-control d-inline-block" id="end_date" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="filter-inputs" id="month_input">
                <label for="month_select">Bulan:</label>
                <select class="form-control d-inline-block" id="month_select" name="month">
                    <option value="" {{ empty(request('month')) ? 'selected' : '' }}>Pilih Bulan</option>
                    @for ($i = 11; $i >= 0; $i--)
                        @php
                            $month = \Carbon\Carbon::now()->subMonths($i);
                        @endphp
                        <option value="{{ $month->format('Y-m') }}" {{ request('month') == $month->format('Y-m') ? 'selected' : '' }}>{{ $month->translatedFormat('F Y') }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead class="background">
                    <tr>
                        <th>No</th>
                        <th>Nama Pelanggan</th>
                        <th>Total Transaksi</th>
                        <th class="tanggal-column">Tanggal</th>
                        <th>Waktu</th>
                        <th>Status Pembayaran</th>
                        <th class="action-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
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
                    <form id="pembayaranForm" method="POST" action="">
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
                                <input type="hidden" name="id_orang" id="modal_id_orang_hidden" value="">
                                <input type="hidden" name="id_transaksi" id="modal_id_transaksi" value="">
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
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="modal_metode_pembayaran">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="modal_metode_pembayaran" class="form-control" required>
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                </select>
                                @error('metode_pembayaran')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
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
                ajax: {
                    url: '{{ route("transaksi.index") }}',
                    data: function(d) {
                        d.filter_type = $('#filter_type').val();
                        d.specific_date = $('#specific_date').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.month = $('#month_select').val();
                        d.jenis_transaksi = $('#jenis_transaksi').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_orang', name: 'orang.nama_lengkap' },
                    { 
                        data: 'total_transaksi', 
                        name: 'total_transaksi', 
                        render: function(data) {
                            return 'Rp ' + parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 2 });
                        }
                    },
                    { data: 'tanggal_transaksi', name: 'tanggal_transaksi', className: 'tanggal-column' },
                    { data: 'waktu_transaksi', name: 'waktu_transaksi' },
                    { 
                        data: 'status_pembayaran', 
                        name: 'status_pembayaran',
                        render: function(data) {
                            let className = '';
                            if (data === 'Lunas') {
                                className = 'status-lunas';
                            } else if (data === 'Belum Lunas') {
                                className = 'status-belum-lunas';
                            } else {
                                className = 'status-belum-dibayar';
                            }
                            return '<span class="status-label ' + className + '">' + data + '</span>';
                        }
                    },
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
                    var totalPages = pageInfo.pages;
                    var maxPagesToShow = 3;

                    var $pagination = $('.dataTables_paginate .pagination');
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

            function toggleFilterInputs() {
                var filterType = $('#filter_type').val();
                $('.filter-inputs').removeClass('active').hide();
                
                if (filterType === 'specific_date') {
                    $('#specific_date_input').show().addClass('active');
                } else if (filterType === 'date_range') {
                    $('#date_range_input').show().addClass('active');
                } else if (filterType === 'month') {
                    $('#month_input').show().addClass('active');
                }
            }

            $('#filter_type, #jenis_transaksi').on('change', function() {
                toggleFilterInputs();
                table.ajax.reload();
            });

            $('#specific_date, #start_date, #end_date, #month_select').on('change', function() {
                table.ajax.reload();
            });

            toggleFilterInputs();

            $('#pembayaranModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var id_orang = button.data('id_orang');
                var total_transaksi = button.data('total_transaksi');
                var sisa = button.data('sisa');
                var id_transaksi = button.data('id_transaksi');

                console.log('Modal opened with data:', {
                    id: id,
                    id_orang: id_orang,
                    total_transaksi: total_transaksi,
                    sisa: sisa,
                    id_transaksi: id_transaksi
                });

                var modal = $(this);
                modal.find('#pembayaranForm').attr('action', '{{ route("pembayaran.update", ":id") }}'.replace(':id', id || 0));
                modal.find('#modal_id_orang').val(id_orang).trigger('change');
                modal.find('#modal_id_orang_hidden').val(id_orang);
                modal.find('#modal_id_transaksi').val(id_transaksi);
                modal.find('#modal_total_transaksi').val(total_transaksi);
                modal.find('#modal_sisa_pembayaran').val(sisa);
                modal.find('#modal_jumlah_pembayaran').val('');
                modal.find('#modal_metode_pembayaran').val('');
            });

            $('#pembayaranForm').on('submit', function(e) {
                var formData = $(this).serialize();
                console.log('Form submitted with data:', formData);
            });
        });
    </script>
@endpush