@extends('admin.layouts.base')
@section('title', 'Data Perusahaan')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.perusahaan.create') }}" class="btn btn-primary btn-icon-split btn-sm mr-2">
                <span class="icon text-white-50">
                    <i class="fas fa-plus"></i>
                </span>
                <span class="text">Tambah Perusahaan</span>
            </a>
        </div>
        <h6 class="m-0 font-weight-bold text-primary">Data Perusahaan</h6>
        <div class="d-flex align-items-center position-relative">
            <input type="text" id="liveSearch" class="form-control bg-light border-0 small w-auto pl-3 pr-5" placeholder="Cari nama atau alamat..." value="{{ request('search') }}">
            <i class="fas fa-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
        </div>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Perusahaan</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perusahaans as $index => $perusahaan)
                        <tr>
                            <td>{{ $perusahaans->firstItem() + $index }}</td>
                            <td>{{ $perusahaan->nama_perusahaan }}</td>
                            <td>{{ $perusahaan->alamat_perusahaan ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.perusahaan.show', $perusahaan->id_perusahaan) }}" class="btn btn-info btn-circle btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.perusahaan.edit', $perusahaan->id_perusahaan) }}" class="btn btn-warning btn-circle btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.perusahaan.destroy', $perusahaan->id_perusahaan) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-circle btn-sm btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-end">
                {{ $perusahaans->appends(['search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
    (function($) {
        $.noConflict();
        $(document).ready(function() {
            // CSRF Token setup for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var dataTable = $('#dataTable').DataTable({
                "searching": false, // Disable DataTables' built-in search
                "paging": false, // Disable DataTables' paging since Laravel pagination is used
                "language": {
                    "emptyTable": "Tidak ada data yang tersedia",
                    "info": "Halaman _PAGE_ dari _PAGES_ halaman",
                    "infoEmpty": "Halaman 0 dari 0 halaman",
                    "lengthMenu": "Tampilkan _MENU_ entri",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });

            // LiveSearch functionality
            $('#liveSearch').on('keyup', function() {
                var searchValue = $(this).val();
                $.ajax({
                    url: "{{ route('admin.perusahaan.index') }}",
                    type: 'GET',
                    data: { search: searchValue },
                    success: function(response) {
                        // Update table body with new data
                        var tbody = $('#dataTable tbody');
                        tbody.empty();
                        var data = $(response).find('#dataTable tbody').html();
                        tbody.html(data);

                        // Update pagination links
                        var pagination = $(response).find('.d-flex.justify-content-end').html();
                        $('.d-flex.justify-content-end').html(pagination);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal memuat data perusahaan. Silakan coba lagi.'
                        });
                    }
                });
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Yakin ingin menghapus data ini?',
                    text: 'Data perusahaan akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    })(jQuery);
</script>
@endpush
@endsection