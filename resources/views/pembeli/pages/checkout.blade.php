@extends('pembeli.pembeli_master')

@section('css')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Leaflet & Geocoder CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
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
                                <input type="text" id="penerima_name" value="" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                                <input type="text" id="penerima_phone" value="" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-gray-50" readonly>
                            </div>
                        </div>

                        <!-- BAGIAN ALAMAT -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-bold text-gray-700">Pilih Alamat Pengiriman</label>
                            </div>

                            <!-- Loading State Alamat -->
                            <div id="loading-address" class="p-4 text-center text-sm font-bold text-gray-400 animate-pulse border-2 border-gray-100 border-dashed rounded-2xl">
                                Memuat daftar alamat...
                            </div>

                            <div id="no-address-alert" class="hidden p-6 border-2 border-dashed border-red-200 bg-red-50/50 rounded-2xl text-center">
                                <p class="text-sm text-red-600 font-bold mb-3">Kamu belum memiliki alamat pengiriman.</p>
                                <button type="button" onclick="openAddressModal()" class="py-2.5 px-6 bg-purple-600 text-white font-bold rounded-xl text-sm shadow-md hover:bg-purple-700 transition">
                                    + Tambah Alamat Sekarang
                                </button>
                            </div>
                            <select id="alamat_select" class="hidden w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700" required></select>
                            <button type="button" id="btn-tambah-alamat-tambahan" onclick="openAddressModal()" class="hidden mt-2 text-xs font-bold text-purple-600 hover:underline">+ Tambah Alamat Lain</button>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN JADWAL PENGIRIMAN (PRE-ORDER) -->
                <!-- Diberi ID section-jadwal-pengiriman agar bisa disembunyikan jika pakai ekspedisi -->
                <div id="section-jadwal-pengiriman" class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100 relative">
                    <div id="loading-jadwal" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center rounded-2xl">
                        <div class="animate-spin w-8 h-8 border-4 border-purple-200 border-t-purple-600 rounded-full mb-2"></div>
                        <span class="text-sm font-bold text-purple-600">Mengecek Jadwal Dapur...</span>
                    </div>

                    <h2 class="text-lg font-bold text-gray-800 mb-2">Jadwal Pengiriman</h2>
                    <p class="text-xs text-gray-500 mb-4">Pilih tanggal dan jam pesananmu mau dikirim atau diambil (Min. 24 Jam Setelah Checkout).</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                            <input type="text" id="tanggal_kirim" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white cursor-pointer" placeholder="Pilih tanggal di kalender" readonly required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Jam</label>
                            <select id="jam_kirim" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium disabled:opacity-50 disabled:bg-gray-50" disabled>
                                <option value="">-- Pilih Jam --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-4 text-xs font-semibold text-gray-600 border-t border-gray-100 pt-3">
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-green-200"></span> Kuota Aman</div>
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-yellow-200"></span> Hampir Penuh</div>
                        <div class="flex items-center gap-1.5"><span class="legend-box bg-red-200"></span> Penuh / Libur</div>
                    </div>
                </div>

                <!-- BAGIAN METODE PEMBAYARAN -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Opsi Pembayaran</h2>
                    <p class="text-xs text-gray-500 mb-4">Pilih skema pembayaran via QRIS / Virtual Account.</p>

                    <div class="space-y-3">
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-purple-500 bg-purple-50 text-purple-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="transfer_full" class="accent-purple-600 w-4 h-4" checked>
                                <div>
                                    <span class="block">Bayar Lunas (100%)</span>
                                    <span class="text-xs text-gray-500 font-medium">Bayar full di awal pake QRIS/VA</span>
                                </div>
                            </div>
                        </label>
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-gray-200 bg-white text-gray-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="transfer_dp" class="accent-purple-600 w-4 h-4">
                                <div>
                                    <span class="block">DP Aja Dulu (30%)</span>
                                    <span class="text-xs text-gray-500 font-medium">Sisa 70% dibayar nanti via sistem</span>
                                </div>
                            </div>
                        </label>
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-gray-200 bg-white text-gray-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="cod" class="accent-purple-600 w-4 h-4">
                                <div>
                                    <span class="block">Cash on Delivery (COD)</span>
                                    <span class="text-xs text-amber-600 font-medium">*Wajib transfer DP 30% buat tanda jadi COD</span>
                                </div>
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
                        <div class="flex justify-between text-gray-500"><span>Total Belanja</span><span id="co-subtotal">Rp 0</span></div>
                    </div>
                    
                    <!-- Bagian Ringkasan Ongkir -->
                    <div class="flex justify-between items-center mt-4 pb-4 border-b border-gray-200">
                        <div>
                            <p class="text-sm font-bold text-gray-700">Ongkos Kirim</p>
                            <p id="teks-kurir" class="text-xs text-blue-600 font-semibold italic">Pilih alamat pengiriman dulu</p>
                        </div>
                        <p id="teks-ongkir" class="font-bold text-gray-800">Rp 0</p>
                    </div>

                    <!-- Grand Total -->
                    <div class="flex justify-between items-center mt-4">
                        <p class="text-base font-black text-gray-900">Total Pembayaran</p>
                        <p id="teks-grand-total" data-subtotal="150000" class="text-xl font-black text-purple-600">Rp 150,000</p>
                    </div>

                    <!-- Input Hidden buat nyimpen data ke backend pas form disubmit -->
                    <input type="hidden" name="shipping_cost" id="input-shipping-cost" value="0">
                    <input type="hidden" name="courier_name" id="input-courier-name" value="">
                    <input type="hidden" name="shipping_service" id="input-shipping-service" value="">

                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-purple-700" id="label-tagihan">Total Tagihan (Lunas)</span>
                            <span class="text-xl font-black text-purple-700" id="co-tagihan-sekarang">Rp 0</span>
                        </div>
                        <button type="button" id="btn-konfirmasi" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200 flex justify-center items-center gap-2">
                            <span id="btn-text-bayar">Bayar Lunas Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Alamat -->
<div id="addressModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[95vh] overflow-y-auto p-6 md:p-7 shadow-2xl relative transform scale-95 transition-transform duration-300 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-black text-gray-800">Tambah Alamat Baru</h3>
            <button type="button" onclick="closeAddressModal()" class="text-gray-400 hover:text-red-500 font-bold text-2xl leading-none">&times;</button>
        </div>

        <form id="form-tambah-alamat" class="space-y-3">
            <div>
                <label class="text-xs font-bold text-gray-600 block mb-1">Label (Misal: Rumah / Kantor)</label>
                <input type="text" name="label" required class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600 block mb-1">Provinsi</label>
                    <input type="text" id="provinsi" name="province" required autocomplete="off" list="list-provinsi"
                        class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400" placeholder="Ketik provinsi...">
                    <datalist id="list-provinsi"></datalist>
                </div>
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600 block mb-1">Kota / Kabupaten</label>
                    <input type="text" id="kota" name="city" required autocomplete="off" list="list-kota" disabled
                        class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400 disabled:opacity-60" placeholder="Ketik kota...">
                    <datalist id="list-kota"></datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative">
                    <label class="text-xs font-bold text-gray-600 block mb-1">Kecamatan</label>
                    <input type="text" id="kecamatan" name="district" required autocomplete="off" list="list-kecamatan" disabled
                        class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400 disabled:opacity-60" placeholder="Ketik kecamatan...">
                    <datalist id="list-kecamatan"></datalist>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600 block mb-1">Kode Pos</label>
                    <input type="text" name="postal_code" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-600 block mb-1">Alamat Lengkap (Jalan, No, RT/RW)</label>
                <textarea name="street_address" required rows="2" class="w-full p-2.5 border rounded-xl text-sm bg-gray-50 focus:bg-white resize-none"></textarea>
            </div>

            <!-- PETA MODAL DI HALAMAN CHECKOUT -->
            <div class="mt-3">
                <label class="text-xs font-bold text-gray-700 block mb-1">Tandai Lokasi di Peta (Wajib untuk hitung ongkir)</label>
                <div id="map-modal" class="w-full h-[180px] rounded-xl border-2 border-gray-200 z-[10]"></div>
                <input type="hidden" name="latitude" id="modal_latitude" required>
                <input type="hidden" name="longitude" id="modal_longitude" required>
            </div>

            <button type="submit" id="btn-simpan-alamat" class="w-full py-3 mt-2 bg-purple-600 text-white font-bold rounded-xl shadow-md hover:bg-purple-700 transition">
                Simpan & Gunakan Alamat
            </button>
        </form>
    </div>
</div>

<!-- Modal Custom Alert -->
<div id="customAlertModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-buyer-textPrimary/40 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
    <div class="bg-buyer-bg border-2 border-white rounded-[2.5rem] max-w-sm w-full p-8 text-center shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_-10px_rgba(192,153,206,0.4)] transform scale-95 transition-transform duration-300 relative mx-4">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-[inset_0_4px_10px_rgba(192,153,206,0.2),0_4px_10px_rgba(192,153,206,0.15)] border-4 border-buyer-bg">
            <svg class="w-10 h-10 text-amber-500 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h3 id="alertModalTitle" class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Peringatan</h3>
        <p id="alertModalMessage" class="text-buyer-textSecondary font-medium text-sm mb-8 leading-relaxed">Pesan error di sini.</p>
        <button type="button" onclick="closeCustomAlert()" class="w-full py-3.5 bg-buyer-primary text-white font-black rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all">
            Oke, Paham
        </button>
    </div>
</div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    <!-- Leaflet & Geocoder JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

    <script>
    // Deklarasi Variabel Global
    let provincesData = [];
    let citiesData = [];
    let districtsData = [];
    window.subtotalBelanja = 0;
    window.ongkirSaatIni = 0;
    let heatmapData = {};

    let modalMap = null;
    let modalMarker = null;

    function initModalMap() {
        if(modalMap !== null) {
            modalMap.invalidateSize();
            return;
        }

        let defaultLat = -6.3473242; 
        let defaultLng = 106.9481003;

        modalMap = L.map('map-modal').setView([defaultLat, defaultLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(modalMap);

        modalMarker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(modalMap);

        function updateModalLokasi(lat, lng) {
            $('#modal_latitude').val(lat);
            $('#modal_longitude').val(lng);
        }

        updateModalLokasi(defaultLat, defaultLng);

        modalMarker.on('dragend', function (e) {
            updateModalLokasi(modalMarker.getLatLng().lat, modalMarker.getLatLng().lng);
        });

        modalMap.on('click', function(e) {
            modalMarker.setLatLng(e.latlng);
            updateModalLokasi(e.latlng.lat, e.latlng.lng);
        });

        L.Control.geocoder({
            defaultMarkGeocoder: false, 
            placeholder: 'Cari kota/jalan...',
            geocoder: L.Control.Geocoder.nominatim({
                geocodingQueryParams: { countrycodes: 'id', addressdetails: 1, limit: 5 }
            })
        }).on('markgeocode', function(e) {
            let center = e.geocode.center;
            modalMap.setView(center, 16); 
            modalMarker.setLatLng(center);
            updateModalLokasi(center.lat, center.lng);
        }).addTo(modalMap);
    }

    function formatRupiah(number) { 
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); 
    }

    function openAddressModal() {
        $('#addressModal').removeClass('hidden');
        setTimeout(() => {
            $('#addressModal').removeClass('opacity-0');
            $('#addressModal > div').removeClass('scale-95');
            
            initModalMap();
            setTimeout(() => { modalMap.invalidateSize(); }, 200);
        }, 10);

        if(provincesData.length === 0) {
            fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
                .then(res => res.json())
                .then(data => {
                    provincesData = data;
                    let options = '';
                    data.forEach(p => options += `<option value="${p.name}">`);
                    $('#list-provinsi').html(options);
                });
        }
    }

    function closeAddressModal() {
        $('#addressModal').addClass('opacity-0');
        $('#addressModal > div').addClass('scale-95');
        setTimeout(() => { $('#addressModal').addClass('hidden'); }, 300);
    }

    function showCustomAlert(title, message) {
        $('#alertModalTitle').text(title);
        $('#alertModalMessage').text(message);
        $('#customAlertModal').removeClass('hidden');
        setTimeout(() => {
            $('#customAlertModal').removeClass('opacity-0');
            $('#customAlertModal > div').removeClass('scale-95');
        }, 10);
    }

    function closeCustomAlert() {
        $('#customAlertModal').addClass('opacity-0');
        $('#customAlertModal > div').addClass('scale-95');
        setTimeout(() => { $('#customAlertModal').addClass('hidden'); }, 300);
    }

    $(document).ready(function() {
        
        window.updateGrandTotal = function() {
            let grandTotal = window.subtotalBelanja + window.ongkirSaatIni;
            $('#teks-grand-total').text(formatRupiah(grandTotal));

            let selectedMethod = $('input[name="payment_method"]:checked').val();
            
            if (selectedMethod === 'transfer_full') {
                $('#label-tagihan').text('Total Tagihan (Lunas)');
                $('#co-tagihan-sekarang').text(formatRupiah(grandTotal));
                $('#btn-text-bayar').text('Bayar Lunas Sekarang');
            } else {
                $('#label-tagihan').text('Wajib Bayar (DP 30%)');
                $('#co-tagihan-sekarang').text(formatRupiah(grandTotal * 0.3));
                $('#btn-text-bayar').text('Bayar DP Sekarang');
            }
        };

        $('input[name="payment_method"]').change(function() {
            $('.payment-lbl').removeClass('border-purple-500 bg-purple-50 text-purple-700').addClass('border-gray-200 bg-white text-gray-700');
            $(this).closest('label').removeClass('border-gray-200 bg-white text-gray-700').addClass('border-purple-500 bg-purple-50 text-purple-700');
            
            updateGrandTotal();
        });

        // FETCH CART
        $.get('/api/pembeli/cart', function(res) {
            if(res.status === 'success') {
                let itemsHTML = '';
                let totalKeseluruhan = 0;

                res.data.items.forEach(item => {
                    totalKeseluruhan += (item.price * item.qty);
                    itemsHTML += `<div class="flex justify-between items-start">
                        <div><p class="font-semibold text-gray-800">${item.name} <span class="text-purple-600">x ${item.qty}</span></p></div>
                        <span class="font-medium text-gray-700">${formatRupiah(item.price * item.qty)}</span>
                    </div>`;
                });

                $('#checkout-items').html(itemsHTML);
                $('#co-subtotal').text(formatRupiah(totalKeseluruhan));
                
                window.subtotalBelanja = totalKeseluruhan;
                updateGrandTotal();

                let now = new Date();
                fetchHeatmap(now.getMonth() + 1, now.getFullYear());
            }
        });

        // FETCH PROFIL & ALAMAT
        $.ajax({
            url: '/api/pembeli/profil-data',
            type: 'GET',
            success: function(res) {
                if(res.status === 'success') {
                    $('#penerima_name').val(res.data.user.name);
                    $('#penerima_phone').val(res.data.user.whatsapp_number);

                    let addresses = res.data.all_addresses || [];
                    $('#loading-address').addClass('hidden');

                    if(addresses.length === 0) {
                        $('#no-address-alert').removeClass('hidden');
                    } else {
                        let select = $('#alamat_select');
                        select.empty();
                        addresses.forEach(addr => {
                            let prefix = addr.is_primary == 1 ? '[Utama] ' : '';
                            let label = addr.label ? addr.label + ' - ' : '';
                            let textLengkap = `${prefix}${label}${addr.street_address}, ${addr.district}, ${addr.city}, ${addr.province} ${addr.postal_code}`;
                            let isSelected = addr.is_primary == 1 ? 'selected' : '';
                            select.append(`<option value="${textLengkap}" data-id="${addr.id}" ${isSelected}>${textLengkap}</option>`);
                        });
                        select.removeClass('hidden');
                        $('#btn-tambah-alamat-tambahan').removeClass('hidden');
                        
                        cekOngkir($('#alamat_select').find(':selected').data('id'));
                    }
                }
            },
            error: function() { showCustomAlert('Error', 'Gagal memuat data profil.'); }
        });

        // CEK ONGKIR & KONTROL TAMPILAN JADWAL
        function cekOngkir(addressId) {
            if (!addressId) return;

            $('#teks-kurir').text('Menghitung jarak...');
            $('#teks-ongkir').text('...');

            $.ajax({
                url: '/api/pembeli/checkout/cek-ongkir',
                type: 'POST',
                data: { 
                    address_id: addressId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if(res.status === 'success') {
                        $('#teks-kurir').html(`${res.courier_name} <br> <span class="text-[10px] text-gray-500">${res.shipping_service}</span>`);
                        $('#teks-ongkir').text(res.shipping_cost === 0 ? 'GRATIS' : formatRupiah(res.shipping_cost));

                        $('#input-shipping-cost').val(res.shipping_cost);
                        $('#input-courier-name').val(res.courier_name);
                        $('#input-shipping-service').val(res.shipping_service);

                        window.ongkirSaatIni = res.shipping_cost;
                        updateGrandTotal();

                        // =========================================================
                        // LOGIKA DINAMIS: SEMBUNYIKAN/TAMPILKAN JADWAL PENGIRIMAN
                        // =========================================================
                        if (res.is_internal) {
                            // Jika kurir internal (dekat): Tampilkan kalender & wajib diisi
                            $('#section-jadwal-pengiriman').slideDown();
                            $('#tanggal_kirim').prop('required', true);
                            $('#jam_kirim').prop('required', true);
                            
                            // Panggil ulang inisialisasi kalender supaya popup kalendernya muncul saat diklik
                            setTimeout(() => { initFlatpickr(); }, 200);
                        } else {
                            // Jika kurir ekspedisi (jauh/luar kota): Sembunyikan kalender
                            $('#section-jadwal-pengiriman').slideUp();
                            $('#tanggal_kirim').prop('required', false).val('');
                            $('#jam_kirim').prop('required', false).val('');
                        }
                    }
                },
                error: function(xhr) {
                    showCustomAlert('Ongkir Error', xhr.responseJSON?.message || 'Gagal menghitung ongkir.');
                    $('#teks-kurir').text('Gagal memuat kurir');
                    $('#teks-ongkir').text('Rp 0');
                }
            });
        }

        $('#alamat_select').change(function() {
            let addressId = $(this).find(':selected').data('id');
            cekOngkir(addressId);
        });

        // KALENDER HEATMAP
        function fetchHeatmap(month, year, fpInstance = null) {
            $.ajax({
                url: "/api/pembeli/checkout/heatmap",
                type: "GET",
                data: { bulan: month, tahun: year },
                success: function(res) {
                    if(res.status === 'success') {
                        heatmapData = { ...heatmapData, ...res.data };
                        if (fpInstance) { fpInstance.redraw(); } else { initFlatpickr(); }
                        $('#loading-jadwal').fadeOut();
                    }
                },
                error: function() { $('#loading-jadwal').fadeOut(); }
            });
        }

        // Deklarasikan variabel instance Flatpickr secara global di atas
    let fpCalendar = null;

    function initFlatpickr() {
        if (fpCalendar) {
            fpCalendar.destroy();
        }

        fpCalendar = flatpickr("#tanggal_kirim", {
            locale: "id",
            minDate: new Date().fp_incr(1), // <-- Mengatur minimal tanggal pengiriman adalah H+1 (besok)
            dateFormat: "Y-m-d",
            disable: [
                function(date) {
                    let dateStr = flatpickr.formatDate(date, "Y-m-d");
                    let tomorrowStr = flatpickr.formatDate(new Date().fp_incr(1), "Y-m-d");
                    
                    // Matikan jika tanggalnya kurang dari besok (hari ini / masa lalu) ATAU kuotanya penuh
                    return dateStr < tomorrowStr || heatmapData[dateStr] === 'full';
                }
            ],
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                let d = flatpickr.formatDate(dayElem.dateObj, "Y-m-d");
                let tomorrowStr = flatpickr.formatDate(new Date().fp_incr(1), "Y-m-d");
                
                // Berikan style nonaktif jika tanggal hari ini atau sebelumnya
                if (d < tomorrowStr) {
                    dayElem.classList.add('flatpickr-disabled');
                } else if (heatmapData[d] === 'med') {
                    dayElem.classList.add('is-med');
                } else if (heatmapData[d] === 'low') {
                    dayElem.classList.add('is-low');
                } else if (heatmapData[d] === 'full') {
                    dayElem.classList.add('is-full');
                }
            },
            onMonthChange: function(s, d, ins) { $('#loading-jadwal').fadeIn(); fetchHeatmap(ins.currentMonth + 1, ins.currentYear, ins); },
            onYearChange: function(s, d, ins) { $('#loading-jadwal').fadeIn(); fetchHeatmap(ins.currentMonth + 1, ins.currentYear, ins); },
            onChange: function(s, dateStr) { fetchJamTersedia(dateStr); }
        });
    }

    function fetchJamTersedia(tanggal) {
            let jamSelect = $('#jam_kirim');
            jamSelect.html('<option value="">Memuat jam...</option>').prop('disabled', true);
            $.ajax({
                url: "/api/pembeli/checkout/times", // <-- Pastikan ada '/pembeli/' di sini!
                type: "GET", 
                data: { tanggal: tanggal },
                success: function(res) {
                    if(res.status === 'success') {
                        jamSelect.empty();
                        if(res.data.length === 0) { 
                            jamSelect.append('<option value="">Jam Operasional Penuh/Habis</option>'); 
                        } else {
                            jamSelect.append('<option value="">-- Pilih Jam --</option>');
                            res.data.forEach(jam => { 
                                jamSelect.append(`<option value="${jam}">${jam} WIB</option>`); 
                            });
                            jamSelect.prop('disabled', false);
                        }
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText); // Cek apakah ada error di console F12
                    jamSelect.html('<option value="">Gagal memuat jam</option>');
                }
            });
        }

        // AUTOCOMPLETE ALAMAT BARU & SUBMIT
        $('#provinsi').on('input', function() {
            let selectedProv = provincesData.find(p => p.name === $(this).val());
            $('#kota, #kecamatan').val('').prop('disabled', true); $('#list-kota, #list-kecamatan').empty();
            if (selectedProv) {
                $('#kota').attr('placeholder', 'Memuat data...').prop('disabled', false);
                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`)
                    .then(res => res.json()).then(data => {
                        citiesData = data; let opts = ''; data.forEach(c => opts += `<option value="${c.name}">`);
                        $('#list-kota').html(opts); $('#kota').attr('placeholder', 'Ketik kota...');
                    });
            }
        });

        $('#kota').on('input', function() {
            let selectedCity = citiesData.find(c => c.name === $(this).val());
            $('#kecamatan').val('').prop('disabled', true); $('#list-kecamatan').empty();
            if (selectedCity) {
                $('#kecamatan').attr('placeholder', 'Memuat data...').prop('disabled', false);
                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedCity.id}.json`)
                    .then(res => res.json()).then(data => {
                        districtsData = data; let opts = ''; data.forEach(d => opts += `<option value="${d.name}">`);
                        $('#list-kecamatan').html(opts); $('#kecamatan').attr('placeholder', 'Ketik kecamatan...');
                    });
            }
        });

        $('#form-tambah-alamat').submit(function(e) {
            e.preventDefault();
            if(!provincesData.find(p => p.name === $('#provinsi').val())) {
                showCustomAlert('Provinsi Belum Sesuai', 'Silakan pilih Provinsi dari daftar otomatis.'); return;
            }
            let btn = $('#btn-simpan-alamat'); let originalText = btn.html();
            btn.html('Menyimpan...').prop('disabled', true);

            $.ajax({
                url: "{{ route('alamat.ajax') }}", type: "POST", data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if(res.status === 'success') {
                        $('#no-address-alert').addClass('hidden');
                        $('#btn-tambah-alamat-tambahan').removeClass('hidden');
                        let select = $('#alamat_select');
                        
                        select.removeClass('hidden').append(`<option value="${res.data.text}" data-id="${res.data.id}" selected>${res.data.text}</option>`);
                        
                        cekOngkir(res.data.id);
                        
                        closeAddressModal();
                        $('#form-tambah-alamat')[0].reset();
                        $('#kota, #kecamatan').prop('disabled', true);
                    }
                },
                error: function(xhr) { showCustomAlert('Gagal Menyimpan', xhr.responseJSON?.message || 'Pastikan semua form terisi dengan benar.'); },
                complete: function() { btn.html(originalText).prop('disabled', false); }
            });
        });

        // SUBMIT CHECKOUT
        $('#btn-konfirmasi').click(function() {
            let addressVal = $('#alamat_select').val();
            let isInternal = ($('#input-shipping-cost').val() == 0);
            
            // Validasi tanggal hanya jika pakai kurir internal
            let tglKirim = isInternal ? $('#tanggal_kirim').val() : 'auto';
            let jamKirim = isInternal ? $('#jam_kirim').val() : '10:00:00';

            if(!addressVal) { showCustomAlert('Alamat Kosong', 'Alamat pengiriman belum diatur cuy!'); return; }
            if(isInternal && (!tglKirim || !jamKirim)) { showCustomAlert('Jadwal Belum Diisi', 'Jadwal kirim wajib diisi cuy!'); return; }
            if($('#teks-ongkir').text() === '...') { showCustomAlert('Ongkir Diproses', 'Tunggu ongkir selesai dihitung dulu ya!'); return; }

            let btn = $(this); let originalText = btn.html();
            btn.html('Memproses... <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full ml-2"></span>').prop('disabled', true);

            $.ajax({
                url: "/api/pembeli/checkout/process", type: "POST",
                data: {
                    name: $('#penerima_name').val(),
                    phone: $('#penerima_phone').val(),
                    address: addressVal, 
                    delivery_date: tglKirim,
                    delivery_time: jamKirim,
                    payment_method: $('input[name="payment_method"]:checked').val(),
                    shipping_cost: $('#input-shipping-cost').val(),
                    courier_name: $('#input-courier-name').val(),
                    shipping_service: $('#input-shipping-service').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if(res.status === 'success') { window.location.href = res.data.redirect_url; }
                },
                error: function(xhr) {
                    showCustomAlert('Checkout Gagal', xhr.responseJSON?.message || 'Gagal memproses pesanan kamu.');
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
    });
    </script>
@endpush