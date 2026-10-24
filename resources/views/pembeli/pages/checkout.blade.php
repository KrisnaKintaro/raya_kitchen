@extends('pembeli.pembeli_master')

@section('css')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Styling Custom buat Heatmap Kalender */
    .flatpickr-day.is-low { background-color: #dcfce7 !important; border-color: #dcfce7 !important; color: #166534 !important; font-weight: bold; }
    .flatpickr-day.is-med { background-color: #fef08a !important; border-color: #fef08a !important; color: #854d0e !important; font-weight: bold; }
    .flatpickr-day.is-full { background-color: #fee2e2 !important; border-color: #fee2e2 !important; color: #991b1b !important; text-decoration: line-through; opacity: 0.6; }
    .flatpickr-day.is-low:hover, .flatpickr-day.is-med:hover { filter: brightness(0.95); }

    /* Legend Box */
    .legend-box { width: 14px; height: 14px; display: inline-block; border-radius: 4px; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Proses Pesanan</span>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Konfirmasi Pesanan</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Data Pengiriman, Jadwal & Metode -->
            <div class="lg:col-span-2 space-y-6">

                <!-- BAGIAN DATA PENGIRIMAN -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Data Pengiriman</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                                <input type="text" id="penerima_name" value="{{ auth()->user()->name }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                                <input type="text" id="penerima_phone" value="{{ auth()->user()->whatsapp_number }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm">
                            </div>
                        </div>

                        <!-- BAGIAN ALAMAT -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-bold text-gray-700">Pilih Alamat Pengiriman</label>
                            </div>

                            @php $addresses = auth()->user()->addresses()->orderByDesc('is_primary')->get(); @endphp

                            @if($addresses->isEmpty())
                                <div id="no-address-alert" class="p-6 border-2 border-dashed border-red-200 bg-red-50/50 rounded-2xl text-center">
                                    <p class="text-sm text-red-600 font-bold mb-3">Kamu belum memiliki alamat pengiriman.</p>
                                    <button type="button" onclick="openAddressModal()" class="py-2.5 px-6 bg-purple-600 text-white font-bold rounded-xl text-sm shadow-md hover:bg-purple-700 transition">
                                        + Tambah Alamat Sekarang
                                    </button>
                                </div>
                                <select id="alamat_select" class="hidden w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700" required></select>
                                <button type="button" id="btn-tambah-alamat-tambahan" onclick="openAddressModal()" class="hidden mt-2 text-xs font-bold text-purple-600 hover:underline">+ Tambah Alamat Lain</button>
                            @else
                                <select id="alamat_select" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700" required>
                                    @foreach($addresses as $addr)
                                        @php
                                            $teksLengkap = ($addr->is_primary ? '[Utama] ' : '') . ($addr->label ? $addr->label . ' - ' : '') . $addr->street_address . ', ' . $addr->district . ', ' . $addr->city . ', ' . $addr->province . ' ' . $addr->postal_code;
                                        @endphp
                                        <option value="{{ $teksLengkap }}" {{ $addr->is_primary ? 'selected' : '' }}>{{ $teksLengkap }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="openAddressModal()" class="mt-2 text-xs font-bold text-purple-600 hover:underline">+ Tambah Alamat Lain</button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- BAGIAN JADWAL PENGIRIMAN (PRE-ORDER) -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 relative">
                    <!-- Loading Kalender -->
                    <div id="loading-jadwal" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center rounded-2xl">
                        <div class="animate-spin w-8 h-8 border-4 border-purple-200 border-t-purple-600 rounded-full mb-2"></div>
                        <span class="text-sm font-bold text-purple-600">Mengecek Jadwal Dapur...</span>
                    </div>

                    <h2 class="text-lg font-bold text-gray-800 mb-2">Jadwal Pengiriman</h2>
                    <p class="text-xs text-gray-500 mb-4">Pilih tanggal dan jam pesananmu mau dikirim atau diambil.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                            <input type="text" id="tanggal_kirim" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white" placeholder="Pilih tanggal di kalender" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Jam</label>
                            <select id="jam_kirim" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium disabled:opacity-50 disabled:bg-gray-50" disabled>
                                <option value="">-- Pilih Tanggal Dulu --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Keterangan Warna Kalender (Legend) -->
                    <div class="mt-4 flex gap-4 text-xs font-semibold text-gray-600 border-t border-gray-100 pt-3">
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-green-200"></span> Kuota Aman</div>
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-yellow-200"></span> Hampir Penuh</div>
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-red-200"></span> Penuh / Libur</div>
                    </div>
                </div>

                <!-- BAGIAN METODE PEMBAYARAN -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Metode Pembayaran</h2>
                    <p class="text-xs text-gray-500 mb-4">Semua pesanan memerlukan Down Payment (DP) sebesar 30%.</p>

                    <div class="space-y-3">
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-purple-500 bg-purple-50 text-purple-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="online" class="accent-purple-600 w-4 h-4" checked>
                                <span>Pembayaran Online (QRIS / Virtual Account)</span>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Ringkasan Pesanan -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 sticky top-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Ringkasan Pesanan</h2>
                    <div id="checkout-items" class="space-y-3 pb-4 border-b border-gray-100 text-sm">
                        <p class="text-center text-gray-400 animate-pulse">Memuat ringkasan...</p>
                    </div>
                    <div class="py-4 space-y-2 border-b border-gray-100 text-sm">
                        <div class="flex justify-between text-gray-500"><span>Total Harga Barang</span><span id="co-subtotal">Rp 0</span></div>
                        <div class="flex justify-between text-gray-500"><span>Ongkos Kirim</span><span class="text-green-600 font-bold">Gratis</span></div>
                    </div>
                    <div class="pt-4 pb-4 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-800">Total Keseluruhan</span>
                            <span class="text-lg font-extrabold text-gray-800" id="co-total">Rp 0</span>
                        </div>
                    </div>
                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-purple-700">Wajib Bayar (DP 30%)</span>
                            <span class="text-xl font-black text-purple-700" id="co-dp">Rp 0</span>
                        </div>
                        <button type="button" id="btn-konfirmasi" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200 flex justify-center items-center gap-2">
                            <span>Bayar DP Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <div id="addressModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 md:p-8 shadow-2xl relative transform scale-95 transition-transform duration-300">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-black text-gray-800">Tambah Alamat Baru</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
        </div>

        <form id="form-tambah-alamat" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-gray-600">Label (Misal: Rumah / Kantor)</label>
                <input type="text" name="label" required class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600">Provinsi</label>
                    <input type="text" id="provinsi" name="province" required autocomplete="off" list="list-provinsi"
                        class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400" placeholder="Ketik provinsi...">
                    <datalist id="list-provinsi"></datalist>
                </div>
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600">Kota / Kabupaten</label>
                    <input type="text" id="kota" name="city" required autocomplete="off" list="list-kota" disabled
                        class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400 disabled:opacity-60" placeholder="Ketik kota...">
                    <datalist id="list-kota"></datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600">Kecamatan</label>
                    <input type="text" id="kecamatan" name="district" required autocomplete="off" list="list-kecamatan" disabled
                        class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400 disabled:opacity-60" placeholder="Ketik kecamatan...">
                    <datalist id="list-kecamatan"></datalist>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Kode Pos</label>
                    <input type="text" name="postal_code" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-600">Alamat Lengkap (Jalan, No, RT/RW)</label>
                <textarea name="street_address" required rows="3" class="w-full mt-1 p-3 border rounded-xl text-sm bg-gray-50 focus:bg-white resize-none"></textarea>
            </div>

            <button type="submit" id="btn-simpan-alamat" class="w-full py-3.5 mt-2 bg-purple-600 text-white font-bold rounded-xl shadow-md hover:bg-purple-700 transition">
                Simpan & Gunakan Alamat
            </button>
        </form>
    </div>
</div>
@endsection

@push('script')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
$(document).ready(function() {
    function formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); }

    let totalKeseluruhan = 0;
    let totalDP = 0;
    let heatmapData = {}; // Buat nyimpen data tanggal dari backend

    // 1. Fetch Cart & Hitung DP 30%
    $.get('/api/pembeli/cart', function(res) {
        if(res.status === 'success') {
            let itemsHTML = '';
            totalKeseluruhan = 0;

            res.data.items.forEach(item => {
                totalKeseluruhan += (item.price * item.qty);
                itemsHTML += `<div class="flex justify-between items-start">
                    <div><p class="font-semibold text-gray-800">${item.name} <span class="text-purple-600">x ${item.qty}</span></p></div>
                    <span class="font-medium text-gray-700">${formatRupiah(item.price * item.qty)}</span>
                </div>`;
            });

            totalDP = totalKeseluruhan * 0.3; // DP 30%

            $('#checkout-items').html(itemsHTML);
            $('#co-subtotal').text(formatRupiah(totalKeseluruhan));
            $('#co-total').text(formatRupiah(totalKeseluruhan));
            $('#co-dp').text(formatRupiah(totalDP));

            // Setelah cart ke-load, baru fetch heatmap kalender
            fetchHeatmap();
        }
    });

    // 2. Fetch Data Heatmap Kalender dari Backend
    function fetchHeatmap() {
        $.ajax({
            url: "/api/pembeli/checkout/heatmap", // Endpoint yang dibikin di PreorderController
            type: "GET",
            success: function(res) {
                if(res.status === 'success') {
                    heatmapData = res.data;
                    initFlatpickr(); // Initialize kalender setelah data dapet
                    $('#loading-jadwal').fadeOut();
                }
            },
            error: function(xhr) {
                alert('Gagal mengambil jadwal dapur.');
                $('#loading-jadwal').fadeOut();
            }
        });
    }

    // 3. Setup Flatpickr
    function initFlatpickr() {
        flatpickr("#tanggal_kirim", {
            locale: "id",
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: [
                function(date) {
                    // Disable kalau di heatmap statusnya 'full' atau 'past'
                    let dStr = flatpickr.formatDate(date, "Y-m-d");
                    return heatmapData[dStr] === 'full' || heatmapData[dStr] === 'past';
                }
            ],
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Mewarnai kotak kalender sesuai status
                let d = flatpickr.formatDate(dayElem.dateObj, "Y-m-d");
                if (heatmapData[d] === 'med') {
                    dayElem.classList.add('is-med');
                } else if (heatmapData[d] === 'low') {
                    dayElem.classList.add('is-low');
                } else if (heatmapData[d] === 'full') {
                    dayElem.classList.add('is-full');
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                // Kalau user milih tanggal, fetch jam yang available
                fetchJamTersedia(dateStr);
            }
        });
    }

    // 4. Fetch Jam Tersedia
    function fetchJamTersedia(tanggal) {
        let jamSelect = $('#jam_kirim');
        jamSelect.html('<option value="">Memuat jam...</option>').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/times",
            type: "GET",
            data: { tanggal: tanggal },
            success: function(res) {
                if(res.status === 'success') {
                    jamSelect.empty();
                    if(res.data.length === 0) {
                        jamSelect.append('<option value="">Jam Operasional Habis</option>');
                    } else {
                        jamSelect.append('<option value="">-- Pilih Jam --</option>');
                        res.data.forEach(jam => {
                            jamSelect.append(`<option value="${jam}">${jam} WIB</option>`);
                        });
                        jamSelect.prop('disabled', false);
                    }
                }
            }
        });
    }

    // 5. PROSES CHECKOUT
    $('#btn-konfirmasi').click(function() {
        let addressVal = $('#alamat_select').val();
        let tglKirim = $('#tanggal_kirim').val();
        let jamKirim = $('#jam_kirim').val();

        if(!addressVal) {
            alert('Silakan tambah atau pilih alamat pengiriman terlebih dahulu!');
            return;
        }
        if(!tglKirim || !jamKirim) {
            alert('Silakan pilih Jadwal Pengiriman (Tanggal & Jam) terlebih dahulu!');
            return;
        }

        let btn = $(this);
        let originalText = btn.html();
        btn.html('Memproses... <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full ml-2"></span>').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/process",
            type: "POST",
            data: {
                name: $('#penerima_name').val(),
                phone: $('#penerima_phone').val(),
                address: addressVal,
                delivery_date: tglKirim,
                delivery_time: jamKirim,
                dp_amount: totalDP,
                payment_method: $('input[name="payment_method"]:checked').val(), // Online
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.status === 'success') {
                    // Nanti ini diarahin ke halaman payment gateway Duitku/Midtrans
                    // Sementara arahin ke nota atau payment intent
                    window.location.href = res.data.redirect_url;
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal memproses pesanan.');
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

});
</script>
@endpush
