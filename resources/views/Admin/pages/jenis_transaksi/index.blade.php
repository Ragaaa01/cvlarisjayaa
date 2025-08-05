@extends('admin.layouts.base')
@section('title', 'Data Jenis Transaksi')

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
        min-width: 600px;
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
</style>
@endsection

@section('content')
<h2>Data Jenis Transaksi</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('admin.jenis_transaksi.create') }}" class="btn btn-custom">
                <i class="fas fa-plus mr-1"></i> Tambah Data
            </a>
        </div>
        <div>
            <a href="{{ route('admin.jenis_transaksi.export.excel') }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="{{ route('admin.jenis_transaksi.export.pdf') }}" class="btn btn-danger">
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
                        <th>Jenis Transaksi</th>
                        <th class="action-header">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.jenis_transaksi.index") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'jenis_transaksi', name: 'jenis_transaksi' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            responsive: true,
            scrollX: true,
            paging: false,
            ordering: false,
            lengthChange: false,
            info: false,
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            }
        });
    });
</script>
@endpush
@endsection