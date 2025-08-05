@extends('admin.layouts.base')
@section('title', 'Edit Jenis Tabung')

@section('styles')
<style>
    .form-group label {
        font-weight: 500;
        color: #333;
    }
    .form-control {
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }
    .form-control:focus {
        border-color: #014A7F;
        box-shadow: 0 0 0 0.2rem rgba(1, 74, 127, 0.25);
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
    .btn-cancel {
        background-color: #6c757d !important;
        color: white !important;
        border: none;
    }
    .btn-cancel:hover {
        background-color: #5a6268 !important;
        color: white !important;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e3e6f0;
    }
    .input-rupiah {
        position: relative;
    }
    .rupiah-prefix {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        pointer-events: none;
    }
    .form-control.rupiah {
        padding-left: 40px;
    }
</style>
@endsection

@section('content')
<h2>Edit Jenis Tabung</h2>
<div class="card shadow mt-4 mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Jenis Tabung</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <form action="{{ route('admin.jenis_tabung.update', $jenisTabung->id_jenis_tabung) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nama_jenis">Nama Jenis</label>
                <input type="text" name="nama_jenis" id="nama_jenis" class="form-control" value="{{ old('nama_jenis', $jenisTabung->nama_jenis) }}" required>
            </div>
            <div class="form-group input-rupiah">
                <label for="harga_pinjam">Harga Pinjam (Rp)</label>
                <span class="rupiah-prefix">Rp</span>
                <input type="text" name="harga_pinjam" id="harga_pinjam" class="form-control rupiah" value="{{ old('harga_pinjam', number_format($jenisTabung->harga_pinjam, 2, ',', '.')) }}" required>
                <input type="hidden" name="harga_pinjam_value" id="harga_pinjam_value" value="{{ old('harga_pinjam_value', $jenisTabung->harga_pinjam) }}">
            </div>
            <div class="form-group input-rupiah">
                <label for="harga_isi_ulang">Harga Isi Ulang (Rp)</label>
                <span class="rupiah-prefix">Rp</span>
                <input type="text" name="harga_isi_ulang" id="harga_isi_ulang" class="form-control rupiah" value="{{ old('harga_isi_ulang', number_format($jenisTabung->harga_isi_ulang, 2, ',', '.')) }}" required>
                <input type="hidden" name="harga_isi_ulang_value" id="harga_isi_ulang_value" value="{{ old('harga_isi_ulang_value', $jenisTabung->harga_isi_ulang) }}">
            </div>
            <div class="form-group input-rupiah">
                <label for="nilai_deposit">Nilai Deposit (Rp)</label>
                <span class="rupiah-prefix">Rp</span>
                <input type="text" name="nilai_deposit" id="nilai_deposit" class="form-control rupiah" value="{{ old('nilai_deposit', number_format($jenisTabung->nilai_deposit, 2, ',', '.')) }}" required>
                <input type="hidden" name="nilai_deposit_value" id="nilai_deposit_value" value="{{ old('nilai_deposit_value', $jenisTabung->nilai_deposit) }}">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-custom">Simpan</button>
                <a href="{{ route('admin.jenis_tabung.index') }}" class="btn btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatRupiah(angka, prefix = 'Rp') {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1].padEnd(2, '0') : rupiah + ',00';
        return prefix + ' ' + rupiah;
    }

    function cleanNumber(angka) {
        return parseFloat(angka.replace(/[^0-9,]/g, '').replace(',', '.')) || 0;
    }

    document.querySelectorAll('.rupiah').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value;
            this.value = formatRupiah(value);
            let cleanValue = cleanNumber(this.value).toFixed(2);
            document.getElementById(this.id + '_value').value = cleanValue;
        });

        // Format awal saat halaman dimuat
        this.value = formatRupiah(this.value);
        let cleanValue = cleanNumber(this.value).toFixed(2);
        document.getElementById(this.id + '_value').value = cleanValue;
    });
</script>
@endpush
@endsection