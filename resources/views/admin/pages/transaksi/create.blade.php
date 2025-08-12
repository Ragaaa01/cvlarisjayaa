@extends('admin.layouts.base')
@section('title', 'Tambah Transaksi')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .transaksi-detail {
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .transaksi-detail .form-group {
            margin-bottom: 15px;
        }
        .transaksi-detail .form-group label {
            font-weight: 600;
            color: #4e73df;
        }
        .transaksi-detail .remove-detail {
            margin-top: 10px;
        }
        .total-transaksi {
            font-weight: bold;
            margin-top: 20px;
            color: #4e73df;
            font-size: 1.2em;
        }
        #pelanggan-details {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            background-color: #f8f9fc;
        }
        #pelanggan-details .form-group {
            margin-bottom: 15px;
        }
        .detail-peminjaman {
            display: none;
            margin-top: 15px;
            padding: 10px;
            border-left: 2px solid #4e73df;
            background-color: #f8f9fc;
            border-radius: 5px;
        }
        .detail-peminjaman.active {
            display: block;
        }
        .detail-peminjaman .form-group {
            margin-bottom: 10px;
        }
        .detail-peminjaman .form-group label {
            font-weight: 600;
            color: #1cc88a;
        }
        .btn-custom {
            background-color: #4e73df;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #2e59d9;
            color: #fff;
        }
    </style>
@endsection

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Tambah Transaksi</h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('transaksi.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="id_orang">Pelanggan</label>
                    <select name="id_orang" id="id_orang" class="form-control select2">
                        <option value="">Pilih Pelanggan</option>
                        @foreach ($orangs as $orang)
                            <option value="{{ $orang->id_orang }}"
                                    data-nama="{{ $orang->nama_lengkap }}"
                                    data-nik="{{ $orang->nik ?? 'Tanpa NIK' }}"
                                    data-alamat="{{ $orang->alamat ?? 'Tanpa Alamat' }}"
                                    data-mitra="{{ $orang->mitras->isNotEmpty() ? $orang->mitras->first()->nama_mitra : '' }}"
                                    {{ old('id_orang') == $orang->id_orang ? 'selected' : '' }}>
                                {{ $orang->nama_lengkap }}
                                @if ($orang->mitras->isNotEmpty())
                                    ({{ $orang->mitras->first()->nama_mitra }})
                                @else
                                    ({{ $orang->nik ?? 'Tanpa NIK' }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('id_orang')
                        <span class="text-danger">Pelanggan wajib dipilih.</span>
                    @enderror
                </div>

                <div id="pelanggan-details">
                    <div id="non-mitra-details" style="display: none;">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" readonly>
                        </div>
                        <div class="form-group">
                            <label>NIK</label>
                            <input type="text" class="form-control" id="nik" readonly>
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <input type="text" class="form-control" id="alamat" readonly>
                        </div>
                    </div>
                    <div id="mitra-details" style="display: none;">
                        <div class="form-group">
                            <label>Penanggung Jawab</label>
                            <input type="text" class="form-control" id="penanggung_jawab" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" id="nama_perusahaan" readonly>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4">Detail Transaksi</h4>
                <div id="transaksi-details">
                    @php
                        $oldDetails = old('transaksi_details', [[]]);
                        $detailCount = count($oldDetails);
                    @endphp
                    @foreach ($oldDetails as $index => $oldDetail)
                        <div class="transaksi-detail">
                            <div class="form-group">
                                <label for="jenis-transaksi-{{ $index }}">Jenis Transaksi</label>
                                <select name="transaksi_details[{{ $index }}][id_jenis_transaksi_detail]" class="form-control select2 jenis-transaksi" id="jenis-transaksi-{{ $index }}" data-index="{{ $index }}">
                                    <option value="">Pilih Jenis Transaksi</option>
                                    @foreach ($jenisTransaksis as $jenis)
                                        @if (in_array(strtolower($jenis->jenis_transaksi), ['peminjaman', 'isi ulang']))
                                            <option value="{{ $jenis->id_jenis_transaksi_detail }}"
                                                    data-jenis="{{ $jenis->jenis_transaksi }}"
                                                    {{ old("transaksi_details.{$index}.id_jenis_transaksi_detail") == $jenis->id_jenis_transaksi_detail ? 'selected' : '' }}>
                                                {{ $jenis->jenis_transaksi }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error("transaksi_details.{$index}.id_jenis_transaksi_detail")
                                    <span class="text-danger">Jenis transaksi wajib dipilih.</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="tabung-select-{{ $index }}">Tabung</label>
                                <select name="transaksi_details[{{ $index }}][id_tabung]" class="form-control select2 tabung-select" id="tabung-select-{{ $index }}" {{ old("transaksi_details.{$index}.id_jenis_transaksi_detail") ? '' : 'disabled' }} data-index="{{ $index }}">
                                    <option value="">Pilih Tabung</option>
                                    @foreach ($tabungs as $tabung)
                                        @if ($tabung->jenisTabung)
                                            <option value="{{ $tabung->id_tabung }}"
                                                    data-harga-pinjam="{{ $tabung->jenisTabung->harga_pinjam ?? 0 }}"
                                                    data-harga-isi-ulang="{{ $tabung->jenisTabung->harga_isi_ulang ?? 0 }}"
                                                    data-nilai-deposit="{{ $tabung->jenisTabung->nilai_deposit ?? 0 }}"
                                                    data-jenis-tabung-id="{{ $tabung->id_jenis_tabung }}"
                                                    data-status-tabung="{{ $tabung->statusTabung->status_tabung ?? '' }}"
                                                    {{ old("transaksi_details.{$index}.id_tabung") == $tabung->id_tabung ? 'selected' : '' }}>
                                                {{ $tabung->kode_tabung }} ({{ $tabung->jenisTabung->nama_jenis }} - {{ $tabung->statusTabung->status_tabung ?? 'Unknown' }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error("transaksi_details.{$index}.id_tabung")
                                    <span class="text-danger">Tabung wajib dipilih.</span>
                                @enderror
                            </div>
                            <div class="form-group harga-section" data-index="{{ $index }}">
                                <label for="harga-{{ $index }}">Harga Total</label>
                                <input type="text" class="form-control harga" id="harga-{{ $index }}" readonly value="{{ old("transaksi_details.{$index}.harga") ? 'Rp ' . number_format(old("transaksi_details.{$index}.harga"), 2, ',', '.') : '' }}">
                                <input type="hidden" name="transaksi_details[{{ $index }}][harga]" class="harga-hidden" value="{{ old("transaksi_details.{$index}.harga") }}">
                                @error("transaksi_details.{$index}.harga")
                                    <span class="text-danger">Harga wajib diisi.</span>
                                @enderror
                            </div>
                            <div class="detail-peminjaman" id="detail-peminjaman-{{ $index }}" data-index="{{ $index }}">
                                <div class="form-group">
                                    <label for="harga-pinjam-{{ $index }}">Harga Pinjam</label>
                                    <input type="text" class="form-control harga-pinjam" id="harga-pinjam-{{ $index }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="harga-isi-ulang-{{ $index }}">Harga Isi Ulang</label>
                                    <input type="text" class="form-control harga-isi-ulang" id="harga-isi-ulang-{{ $index }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="nilai-deposit-{{ $index }}">Nilai Deposit</label>
                                    <input type="text" class="form-control nilai-deposit" id="nilai-deposit-{{ $index }}" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-danger btn-sm remove-detail"><i class="fas fa-trash"></i> Hapus</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-custom btn-sm mt-2" id="add-detail"><i class="fas fa-plus"></i> Tambah Detail</button>

                <div class="form-group total-transaksi">
                    <label for="total-transaksi">Total Transaksi</label>
                    <input type="text" class="form-control" id="total-transaksi" readonly>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-custom">Simpan</button>
                    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 untuk semua dropdown
            $('.select2').select2({
                placeholder: function() {
                    return $(this).attr('id') === 'id_orang' ? 'Pilih Pelanggan' : 'Pilih';
                },
                allowClear: true
            });

            // Fungsi untuk menampilkan detail pelanggan
            function showPelangganDetails() {
                const selectedOption = $('#id_orang option:selected');
                const nama = selectedOption.data('nama') || '';
                const nik = selectedOption.data('nik') || 'Tanpa NIK';
                const alamat = selectedOption.data('alamat') || 'Tanpa Alamat';
                const mitra = selectedOption.data('mitra') || '';

                if (!selectedOption.val()) {
                    $('#pelanggan-details').hide();
                    return;
                }

                $('#pelanggan-details').show();

                if (mitra) {
                    $('#non-mitra-details').hide();
                    $('#mitra-details').show();
                    $('#penanggung_jawab').val(nama);
                    $('#nama_perusahaan').val(mitra);
                } else {
                    $('#mitra-details').hide();
                    $('#non-mitra-details').show();
                    $('#nama_lengkap').val(nama);
                    $('#nik').val(nik);
                    $('#alamat').val(alamat);
                }
            }

            // Event handler untuk perubahan dropdown pelanggan
            $('#id_orang').on('select2:select select2:unselect', function() {
                showPelangganDetails();
            });

            // Inisialisasi detail pelanggan saat halaman dimuat
            showPelangganDetails();

            // Array untuk melacak semua tabung yang sudah dipilih
            let selectedTabungs = [];

            function updateHarga(index) {
                console.log(`Updating harga for index ${index}`);
                const $detail = $('.transaksi-detail').eq(index);
                const jenisTransaksiSelect = $detail.find('select.jenis-transaksi option:selected');
                const tabungSelect = $detail.find('select.tabung-select option:selected');
                const hargaInput = $detail.find('input.harga-hidden');
                const hargaDisplay = $detail.find(`#harga-${index}`);
                const $detailPeminjaman = $detail.find('.detail-peminjaman');
                const hargaPinjamInput = $detail.find(`#harga-pinjam-${index}`);
                const hargaIsiUlangInput = $detail.find(`#harga-isi-ulang-${index}`);
                const nilaiDepositInput = $detail.find(`#nilai-deposit-${index}`);
                const $hargaSection = $detail.find('.harga-section');

                let harga = 0;
                let hargaPinjam = 0;
                let hargaIsiUlang = 0;
                let nilaiDeposit = 0;

                const jenisTransaksiRaw = jenisTransaksiSelect.data('jenis') || '';
                const jenisTransaksi = String(jenisTransaksiRaw).toLowerCase().trim();
                if (tabungSelect.length) {
                    hargaPinjam = parseFloat(tabungSelect.data('harga-pinjam')) || 0;
                    hargaIsiUlang = parseFloat(tabungSelect.data('harga-isi-ulang')) || 0;
                    nilaiDeposit = parseFloat(tabungSelect.data('nilai-deposit')) || 0;
                }

                console.log(`Jenis Transaksi (raw): "${jenisTransaksiRaw}"`);
                console.log(`Jenis Transaksi (normalized): "${jenisTransaksi}"`);
                console.log(`Harga Pinjam: ${hargaPinjam}, Harga Isi Ulang: ${hargaIsiUlang}, Nilai Deposit: ${nilaiDeposit}`);

                if (jenisTransaksi.includes('peminjaman')) {
                    harga = hargaPinjam + hargaIsiUlang + nilaiDeposit;
                    $detailPeminjaman.addClass('active');
                    $hargaSection.find('label').text('Harga Total');
                    hargaPinjamInput.val(hargaPinjam > 0 ? 'Rp ' + hargaPinjam.toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '');
                    hargaIsiUlangInput.val(hargaIsiUlang > 0 ? 'Rp ' + hargaIsiUlang.toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '');
                    nilaiDepositInput.val(nilaiDeposit > 0 ? 'Rp ' + nilaiDeposit.toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '');
                } else if (jenisTransaksi.includes('isi') || jenisTransaksi.includes('ulang')) {
                    harga = hargaIsiUlang;
                    $detailPeminjaman.removeClass('active');
                    $hargaSection.find('label').text('Harga Isi Ulang');
                    hargaPinjamInput.val('');
                    hargaIsiUlangInput.val('');
                    nilaiDepositInput.val('');
                } else {
                    $detailPeminjaman.removeClass('active');
                    $hargaSection.find('label').text('Harga Total');
                    hargaPinjamInput.val('');
                    hargaIsiUlangInput.val('');
                    nilaiDepositInput.val('');
                }

                console.log(`Harga calculated: ${harga}`);
                hargaDisplay.val(harga > 0 ? 'Rp ' + harga.toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '');
                hargaInput.val(harga > 0 ? harga : '');
                updateTotalTransaksi();
            }

            function updateTotalTransaksi() {
                let total = 0;
                $('.harga-hidden').each(function() {
                    const harga = parseFloat($(this).val()) || 0;
                    total += harga;
                });
                console.log(`Total Transaksi: ${total}`);
                $('#total-transaksi').val(total > 0 ? 'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2 }) : '');
            }

            // Fungsi untuk mengelola status dropdown tabung berdasarkan jenis transaksi
            function toggleTabungSelect($detail) {
                const jenisTransaksiSelect = $detail.find('select.jenis-transaksi');
                const tabungSelect = $detail.find('select.tabung-select');
                const index = $detail.index();

                if (jenisTransaksiSelect.val()) {
                    tabungSelect.prop('disabled', false);
                    updateTabungOptions($detail);
                    console.log(`Tabung select enabled for index ${index}`);
                } else {
                    tabungSelect.prop('disabled', true).val('').trigger('change.select2');
                    $detail.find('input.harga').val('');
                    $detail.find('input.harga-hidden').val('');
                    $detail.find('.detail-peminjaman').removeClass('active');
                    $detail.find('.harga-pinjam').val('');
                    $detail.find('.harga-isi-ulang').val('');
                    $detail.find('.nilai-deposit').val('');
                    console.log(`Tabung select disabled and reset for index ${index}`);
                    updateTotalTransaksi();
                }
            }

            // Fungsi untuk memperbarui opsi tabung berdasarkan jenis transaksi dan tabung yang sudah dipilih
            function updateTabungOptions($detail) {
                const tabungSelect = $detail.find('select.tabung-select');
                const jenisTransaksiSelect = $detail.find('select.jenis-transaksi option:selected');
                const currentTabungId = tabungSelect.val();
                const jenisTransaksiRaw = jenisTransaksiSelect.data('jenis') || '';
                const jenisTransaksi = String(jenisTransaksiRaw).toLowerCase().trim();

                // Simpan opsi tabung asli
                if (!tabungSelect.data('original-options')) {
                    tabungSelect.data('original-options', tabungSelect.html());
                }

                // Filter tabung berdasarkan jenis transaksi
                const originalOptions = $(tabungSelect.data('original-options')).filter('option');
                let filteredOptions = '<option value="">Pilih Tabung</option>';

                originalOptions.each(function() {
                    const tabungId = $(this).val();
                    const statusTabung = $(this).data('status-tabung') || '';

                    // Jika jenis transaksi adalah peminjaman, hanya tampilkan tabung dengan status Tersedia
                    // Jika jenis transaksi adalah isi ulang, tampilkan tabung dengan status Tersedia atau Dipinjam
                    if (!tabungId || !selectedTabungs.includes(tabungId) || tabungId === currentTabungId) {
                        if (jenisTransaksi.includes('peminjaman')) {
                            if (statusTabung.toLowerCase() === 'tersedia') {
                                filteredOptions += $(this).prop('outerHTML');
                            }
                        } else if (jenisTransaksi.includes('isi') || jenisTransaksi.includes('ulang')) {
                            if (statusTabung.toLowerCase() === 'tersedia' || statusTabung.toLowerCase() === 'dipinjam') {
                                filteredOptions += $(this).prop('outerHTML');
                            }
                        }
                    }
                });

                tabungSelect.html(filteredOptions);
                tabungSelect.val(currentTabungId).trigger('change.select2');
            }

            // Fungsi untuk memperbarui daftar tabung yang sudah dipilih
            function updateSelectedTabungs() {
                selectedTabungs = [];

                $('.transaksi-detail').each(function() {
                    const tabungSelect = $(this).find('select.tabung-select option:selected');
                    const tabungId = tabungSelect.val();

                    if (tabungId) {
                        selectedTabungs.push(tabungId);
                    }
                });

                console.log('Selected Tabungs:', selectedTabungs);

                // Perbarui opsi di semua dropdown tabung
                $('.transaksi-detail').each(function() {
                    updateTabungOptions($(this));
                });
            }

            // Inisialisasi: Nonaktifkan semua dropdown tabung secara default dan set nilai dari old()
            $('.transaksi-detail').each(function() {
                console.log('Inisialisasi transaksi-detail index: ' + $(this).index());
                toggleTabungSelect($(this));
                const index = $(this).index();
                const jenisTransaksiSelect = $(this).find('select.jenis-transaksi');
                const tabungSelect = $(this).find('select.tabung-select');
                if (jenisTransaksiSelect.val() && tabungSelect.val()) {
                    console.log('Mengupdate harga untuk index: ' + index);
                    updateHarga(index);
                }
            });

            // Perbarui daftar tabung yang sudah dipilih berdasarkan old()
            updateSelectedTabungs();

            // Delegasi event untuk perubahan jenis transaksi
            $(document).on('select2:select select2:unselect', '.jenis-transaksi', function() {
                const $detail = $(this).closest('.transaksi-detail');
                const index = $detail.index();
                console.log(`Jenis Transaksi changed for index ${index}`);
                toggleTabungSelect($detail);
                updateSelectedTabungs();
                updateHarga(index);
            });

            // Delegasi event untuk perubahan tabung
            $(document).on('select2:select select2:unselect', '.tabung-select', function() {
                const $detail = $(this).closest('.transaksi-detail');
                const index = $detail.index();
                console.log(`Tabung changed for index ${index}`);
                updateSelectedTabungs();
                updateHarga(index);
            });

            let detailCount = {{ $detailCount }};
            $('#add-detail').click(function() {
                console.log(`Adding new detail, index: ${detailCount}`);
                const detailHtml = `
                    <div class="transaksi-detail">
                        <div class="form-group">
                            <label for="jenis-transaksi-${detailCount}">Jenis Transaksi</label>
                            <select name="transaksi_details[${detailCount}][id_jenis_transaksi_detail]" class="form-control select2 jenis-transaksi" id="jenis-transaksi-${detailCount}" data-index="${detailCount}">
                                <option value="">Pilih Jenis Transaksi</option>
                                @foreach ($jenisTransaksis as $jenis)
                                    @if (in_array(strtolower($jenis->jenis_transaksi), ['peminjaman', 'isi ulang']))
                                        <option value="{{ $jenis->id_jenis_transaksi_detail }}" data-jenis="{{ $jenis->jenis_transaksi }}">{{ $jenis->jenis_transaksi }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tabung-select-${detailCount}">Tabung</label>
                            <select name="transaksi_details[${detailCount}][id_tabung]" class="form-control select2 tabung-select" id="tabung-select-${detailCount}" disabled data-index="${detailCount}">
                                <option value="">Pilih Tabung</option>
                                @foreach ($tabungs as $tabung)
                                    @if ($tabung->jenisTabung)
                                        <option value="{{ $tabung->id_tabung }}"
                                                data-harga-pinjam="{{ $tabung->jenisTabung->harga_pinjam ?? 0 }}"
                                                data-harga-isi-ulang="{{ $tabung->jenisTabung->harga_isi_ulang ?? 0 }}"
                                                data-nilai-deposit="{{ $tabung->jenisTabung->nilai_deposit ?? 0 }}"
                                                data-jenis-tabung-id="{{ $tabung->id_jenis_tabung }}"
                                                data-status-tabung="{{ $tabung->statusTabung->status_tabung ?? '' }}">
                                            {{ $tabung->kode_tabung }} ({{ $tabung->jenisTabung->nama_jenis }} - {{ $tabung->statusTabung->status_tabung ?? 'Unknown' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group harga-section" data-index="${detailCount}">
                            <label for="harga-${detailCount}">Harga Total</label>
                            <input type="text" class="form-control harga" id="harga-${detailCount}" readonly>
                            <input type="hidden" name="transaksi_details[${detailCount}][harga]" class="harga-hidden">
                        </div>
                        <div class="detail-peminjaman" id="detail-peminjaman-${detailCount}" data-index="${detailCount}">
                            <div class="form-group">
                                <label for="harga-pinjam-${detailCount}">Harga Pinjam</label>
                                <input type="text" class="form-control harga-pinjam" id="harga-pinjam-${detailCount}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="harga-isi-ulang-${detailCount}">Harga Isi Ulang</label>
                                <input type="text" class="form-control harga-isi-ulang" id="harga-isi-ulang-${detailCount}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="nilai-deposit-${detailCount}">Nilai Deposit</label>
                                <input type="text" class="form-control nilai-deposit" id="nilai-deposit-${detailCount}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-danger btn-sm remove-detail"><i class="fas fa-trash"></i> Hapus</button>
                        </div>
                    </div>
                `;
                $('#transaksi-details').append(detailHtml);

                // Inisialisasi Select2 untuk elemen baru
                const $newDetail = $('#transaksi-details .transaksi-detail').last();
                $newDetail.find('.select2').select2({
                    placeholder: 'Pilih',
                    allowClear: true
                });

                // Nonaktifkan dropdown tabung untuk detail baru
                toggleTabungSelect($newDetail);
                updateSelectedTabungs();

                detailCount++;
                updateTotalTransaksi();
            });

            $(document).on('click', '.remove-detail', function() {
                console.log('Menghapus detail');
                $(this).closest('.transaksi-detail').remove();
                updateSelectedTabungs();
                updateTotalTransaksi();
            });

            // Inisialisasi total transaksi saat halaman dimuat
            updateTotalTransaksi();
        });
    </script>
@endpush