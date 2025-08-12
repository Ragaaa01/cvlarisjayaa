@extends('admin.layouts.base')
@section('title', 'Data Pengembalian')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .table th, .table td {
            vertical-align: middle;
            font-size: 0.9rem;
            text-align: center;
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
            min-width: 100px;
            text-align: center;
        }
        .action-header {
            position: sticky;
            right: 0;
            background-color: #014A7F !important;
            color: white;
            z-index: 2;
            box-shadow: -2px 0 2px rgba(0,0,0,0.1);
            min-width: 100px;
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
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-in;
        }
        .modal-header {
            background-color: #014A7F;
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 1.5rem;
        }
        .modal-footer {
            border-top: none;
            padding: 1rem 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .form-group label i {
            color: #014A7F;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            border-color: #ced4da;
            color: #333;
        }
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .radio-group input[type="radio"] {
            margin-right: 8px;
        }
        .radio-group input[type="radio"]:checked + span {
            font-weight: bold;
        }
        .radio-group label:hover {
            background-color: #f8f9fa;
        }
        .radio-group input[type="radio"]:checked + span {
            color: #28a745;
        }
        .radio-group input[type="radio"]:checked + span::before {
            content: '\f058';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 8px;
            color: #28a745;
        }
        #denda_kondisi_tabung_group {
            display: none;
        }
        .error-container {
            display: none;
        }
        .success-container {
            display: none;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-in;
        }
        .alert-success .alert-icon {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .alert-success .close {
            margin-left: auto;
            font-size: 1rem;
            color: #155724;
            opacity: 0.6;
        }
        .alert-success .close:hover {
            opacity: 1;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success.fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }
        .btn-primary {
            background-color: #014A7F;
            border-color: #014A7F;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #001B36;
            border-color: #001B36;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .form-control.currency {
            position: relative;
            padding-left: 30px;
        }
        .form-control.currency::before {
            content: 'Rp';
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
        }
    </style>
@endsection

@section('content')
    <h2>Data Pengembalian</h2>
    <div class="card shadow mt-4 mb-4">
        <div class="card-body">
            <div class="success-container"></div>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle alert-icon"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover" id="dataTableBerlangsung" width="100%" cellspacing="0">
                    <thead class="background">
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Kode Tabung</th>
                            <th>Jenis Tabung</th>
                            <th>Tanggal Pinjam / Aktivitas Terakhir</th>
                            <th>Sisa Deposit</th>
                            <th>Total Denda</th>
                            <th class="action-header">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal untuk pengembalian -->
    <div class="modal fade" id="pengembalianModal" tabindex="-1" role="dialog" aria-labelledby="pengembalianModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengembalianModalLabel"><i class="fas fa-undo mr-2"></i>Proses Pengembalian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="pengembalianForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="modal-body">
                        <div class="error-container"></div>
                        <div class="form-group">
                            <label for="modal_kode_tabung"><i class="fas fa-barcode"></i> Kode Tabung</label>
                            <input type="text" id="modal_kode_tabung" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label for="modal_deposit"><i class="fas fa-money-bill-wave"></i> Deposit</label>
                            <input type="text" id="modal_deposit" class="form-control currency" readonly>
                            <input type="hidden" id="modal_deposit_hidden" name="deposit">
                        </div>
                        <div class="form-group">
                            <label for="modal_biaya_admin"><i class="fas fa-coins"></i> Biaya Admin</label>
                            <input type="text" id="modal_biaya_admin" class="form-control currency" value="Rp 50.000,00" readonly>
                            <input type="hidden" id="modal_biaya_admin_hidden" name="biaya_admin" value="50000.00">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-check-circle"></i> Kondisi Tabung</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="kondisi_tabung" value="Baik" checked>
                                    <span>Baik</span>
                                </label>
                                <label>
                                    <input type="radio" name="kondisi_tabung" value="Rusak">
                                    <span>Rusak</span>
                                </label>
                                <label>
                                    <input type="radio" name="kondisi_tabung" value="Hilang">
                                    <span>Hilang</span>
                                </label>
                            </div>
                            @error('kondisi_tabung')
                                <span class="text-danger">{{ $message }}</span>
                            @endif
                        </div>
                        <div class="form-group" id="denda_kondisi_tabung_group">
                            <label for="modal_denda_kondisi_tabung"><i class="fas fa-coins"></i> Denda Kondisi Tabung</label>
                            <input type="text" id="modal_denda_kondisi_tabung" class="form-control currency" value="">
                            <input type="hidden" name="denda_kondisi_tabung" id="modal_denda_kondisi_tabung_hidden">
                            @error('denda_kondisi_tabung')
                                <span class="text-danger">{{ $message }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kembalikan</button>
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
            // Fungsi untuk memformat angka ke format Rupiah
            function formatRupiah(angka) {
                if (!angka || isNaN(angka) || angka === '') return 'Rp 0,00';
                let number = parseFloat(angka).toFixed(2);
                let [integer, decimal] = number.split('.');
                let number_string = integer.replace(/\D/g, '');
                if (number_string === '') return 'Rp 0,00';
                let sisa = number_string.length % 3;
                let rupiah = number_string.substr(0, sisa);
                let ribuan = number_string.substr(sisa).match(/\d{3}/g);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                return 'Rp ' + rupiah + ',' + decimal;
            }

            // Fungsi untuk mengubah format Rupiah ke angka
            function parseRupiah(rupiah) {
                if (!rupiah || rupiah === 'Rp 0,00' || rupiah === '') return '0.00';
                let clean = rupiah.replace(/[^0-9,.]/g, '').replace(',', '.');
                return parseFloat(clean || 0).toFixed(2);
            }

            // Inisialisasi DataTable untuk pengembalian berlangsung
            var tableBerlangsung = $('#dataTableBerlangsung').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("pengembalian.index") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama_pelanggan', name: 'transaksiDetail.transaksi.orang.nama_lengkap' },
                    { data: 'kode_tabung', name: 'tabung.kode_tabung' },
                    { data: 'nama_jenis_tabung', name: 'tabung.jenisTabung.nama_jenis' },
                    { data: 'tanggal_pinjam', name: 'tanggal_pinjam' },
                    { data: 'sisa_deposit', name: 'sisa_deposit' },
                    { data: 'total_denda', name: 'total_denda' },
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

            // Event untuk mengisi form modal saat dibuka
            $('#pengembalianModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var kodeTabung = button.data('kode-tabung');
                var deposit = button.data('deposit');

                var modal = $(this);
                modal.find('#pengembalianForm').attr('action', '{{ route("pengembalian.update", ":id") }}'.replace(':id', id));
                modal.find('#modal_kode_tabung').val(kodeTabung);
                modal.find('#modal_deposit').val(formatRupiah(deposit));
                modal.find('#modal_deposit_hidden').val(parseFloat(deposit).toFixed(2));
                modal.find('#modal_biaya_admin').val(formatRupiah(50000));
                modal.find('#modal_biaya_admin_hidden').val('50000.00');
                modal.find('input[name="kondisi_tabung"][value="Baik"]').prop('checked', true);
                modal.find('#modal_denda_kondisi_tabung').val('').prop('readonly', false);
                modal.find('#modal_denda_kondisi_tabung_hidden').val('0.00');
                modal.find('#denda_kondisi_tabung_group').hide();
                modal.find('.error-container').empty().hide();
                $('.success-container').empty().hide();
            });

            // Event untuk menampilkan/menyembunyikan input denda berdasarkan kondisi tabung
            $('input[name="kondisi_tabung"]').on('change', function() {
                var kondisi = $(this).val();
                var $dendaGroup = $('#denda_kondisi_tabung_group');
                var $dendaInput = $('#modal_denda_kondisi_tabung');
                var $dendaHidden = $('#modal_denda_kondisi_tabung_hidden');
                var deposit = parseFloat($('#modal_deposit_hidden').val());

                if (kondisi === 'Rusak') {
                    $dendaGroup.show();
                    $dendaInput.prop('required', true).prop('readonly', false).val('');
                    $dendaHidden.val('0.00');
                } else if (kondisi === 'Hilang') {
                    $dendaGroup.show();
                    $dendaInput.prop('required', true).prop('readonly', true).val(formatRupiah(deposit));
                    $dendaHidden.val(deposit.toFixed(2));
                    $('#modal_biaya_admin').val(formatRupiah(0));
                    $('#modal_biaya_admin_hidden').val('0.00');
                } else {
                    $dendaGroup.hide();
                    $dendaInput.prop('required', false).prop('readonly', false).val('');
                    $dendaHidden.val('0.00');
                    $('#modal_biaya_admin').val(formatRupiah(50000));
                    $('#modal_biaya_admin_hidden').val('50000.00');
                }
            });

            // Event untuk menangani input denda kondisi tabung
            $('#modal_denda_kondisi_tabung').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(value);
            }).on('blur', function() {
                let value = $(this).val();
                if (value === '') {
                    $(this).val('');
                    $('#modal_denda_kondisi_tabung_hidden').val('0.00');
                } else {
                    let number = parseFloat(value || 0);
                    $(this).val(formatRupiah(number));
                    $('#modal_denda_kondisi_tabung_hidden').val(number.toFixed(2));
                }
            });

            // Event untuk mengirim form pengembalian tanpa reload halaman
            $('#pengembalianForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var kodeTabung = $('#modal_kode_tabung').val();
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize() + '&_method=PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#pengembalianModal').modal('hide');
                        // Refresh tabel tanpa reload halaman
                        tableBerlangsung.ajax.reload(null, false);
                        // Tampilkan pesan sukses
                        var successHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-check-circle alert-icon"></i>' +
                            response.success +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button></div>';
                        $('.success-container').html(successHtml).show();
                        setTimeout(function() {
                            $('.success-container').addClass('fade-out');
                            setTimeout(function() {
                                $('.success-container').empty().hide().removeClass('fade-out');
                            }, 500);
                        }, 10000);
                    },
                    error: function(xhr) {
                        var errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-exclamation-circle alert-icon"></i>' +
                            '<ul>';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorHtml += '<li>' + value + '</li>';
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorHtml += '<li>' + xhr.responseJSON.error + '</li>';
                        } else {
                            errorHtml += '<li>Terjadi kesalahan saat memproses pengembalian.</li>';
                        }
                        errorHtml += '</ul></div>';
                        $('#pengembalianModal .modal-body .error-container').html(errorHtml).show();
                    }
                });
            });
        });
    </script>
@endpush