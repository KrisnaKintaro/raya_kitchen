@extends('pembeli.pembeli_master')
@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Proses Pesanan</span>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Konfirmasi Pesanan</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Data Pengiriman & Metode -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Data Pengiriman</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                            <input type="text" id="penerima_name" value="{{ auth()->user()->name }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                            <input type="text" id="penerima_phone" value="{{ auth()->user()->whatsapp_number }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm">
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

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Metode Pembayaran</h2>
                    <div class="space-y-3">
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-purple-500 bg-purple-50 text-purple-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="transfer" class="accent-purple-600 w-4 h-4" checked>
                                <span>Transfer Bank (BCA / BRI / Mandiri)</span>
                            </div>
                        </label>
                        <label class="payment-lbl flex items-center justify-between p-4 rounded-xl border-2 cursor-pointer font-semibold text-sm transition border-gray-200 bg-white text-gray-700">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="cod" class="accent-purple-600 w-4 h-4">
                                <span>COD (Bayar di Tempat)</span>
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
                        <div class="flex justify-between text-gray-500"><span>Subtotal</span><span id="co-subtotal">Rp 0</span></div>
                        <div class="flex justify-between text-gray-500"><span>Ongkir</span><span>Rp 10.000</span></div>
                    </div>
                    <div class="pt-4">
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-xl font-extrabold text-purple-700" id="co-total">Rp 0</span>
                        </div>
                        <button type="button" id="btn-konfirmasi" class="w-full py-3.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition duration-200">
                            Konfirmasi Pesanan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH ALAMAT AUTOCOMPLETE -->
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
<script>
// Variabel Global Data Wilayah
let provincesData = [];
let citiesData = [];
let districtsData = [];

// Buka Tutup Modal
function openAddressModal() {
    $('#addressModal').removeClass('hidden');
    setTimeout(() => {
        $('#addressModal').removeClass('opacity-0');
        $('#addressModal > div').removeClass('scale-95');
    }, 10);
    
    // Tarik data provinsi jika belum ada
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

$(document).ready(function() {
    function formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); }

    // UI Payment Radio
    $('input[name="payment_method"]').change(function() {
        $('.payment-lbl').removeClass('border-purple-500 bg-purple-50 text-purple-700').addClass('border-gray-200 bg-white text-gray-700');
        $(this).closest('label').removeClass('border-gray-200 bg-white text-gray-700').addClass('border-purple-500 bg-purple-50 text-purple-700');
    });

    // Fetch Cart
    $.get('/api/pembeli/cart', function(res) {
        if(res.status === 'success') {
            let itemsHTML = ''; let subtotal = 0;
            res.data.items.forEach(item => {
                subtotal += (item.price * item.qty);
                itemsHTML += `<div class="flex justify-between items-start">
                    <div><p class="font-semibold text-gray-800">${item.name} <span class="text-purple-600">x ${item.qty}</span></p></div>
                    <span class="font-medium text-gray-700">${formatRupiah(item.price * item.qty)}</span>
                </div>`;
            });
            $('#checkout-items').html(itemsHTML);
            $('#co-subtotal').text(formatRupiah(subtotal));
            $('#co-total').text(formatRupiah(subtotal + 10000));
        }
    });

    // --- AUTOCOMPLETE LOGIC ---
    $('#provinsi').on('input', function() {
        let selectedVal = $(this).val();
        let selectedProv = provincesData.find(p => p.name === selectedVal);
        
        $('#kota').val('').prop('disabled', true);
        $('#list-kota').empty();
        $('#kecamatan').val('').prop('disabled', true);
        $('#list-kecamatan').empty();

        if (selectedProv) {
            $('#kota').attr('placeholder', 'Memuat data kota...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`)
                .then(res => res.json())
                .then(data => {
                    citiesData = data;
                    let options = '';
                    data.forEach(c => options += `<option value="${c.name}">`);
                    $('#list-kota').html(options);
                    $('#kota').attr('placeholder', 'Ketik nama kota...');
                });
        }
    });

    $('#kota').on('input', function() {
        let selectedVal = $(this).val();
        let selectedCity = citiesData.find(c => c.name === selectedVal);
        
        $('#kecamatan').val('').prop('disabled', true);
        $('#list-kecamatan').empty();

        if (selectedCity) {
            $('#kecamatan').attr('placeholder', 'Memuat data kecamatan...').prop('disabled', false);
            fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedCity.id}.json`)
                .then(res => res.json())
                .then(data => {
                    districtsData = data;
                    let options = '';
                    data.forEach(d => options += `<option value="${d.name}">`);
                    $('#list-kecamatan').html(options);
                    $('#kecamatan').attr('placeholder', 'Ketik nama kecamatan...');
                });
        }
    });

    // --- SUBMIT ALAMAT BARU ---
    $('#form-tambah-alamat').submit(function(e) {
        e.preventDefault();
        
        if(!provincesData.find(p => p.name === $('#provinsi').val())) {
            alert('Silakan pilih Provinsi dari daftar yang muncul saat mengetik.'); return;
        }

        let btn = $('#btn-simpan-alamat');
        let originalText = btn.html();
        btn.html('Menyimpan...').prop('disabled', true);

        $.ajax({
            url: "{{ route('alamat.ajax') }}",
            type: "POST",
            data: $(this).serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                if(res.status === 'success') {
                    // Update UI Dropdown Checkout
                    $('#no-address-alert').addClass('hidden');
                    $('#btn-tambah-alamat-tambahan').removeClass('hidden');
                    let select = $('#alamat_select');
                    select.removeClass('hidden').append(new Option(res.data.text, res.data.value, true, true));
                    
                    closeAddressModal();
                    $('#form-tambah-alamat')[0].reset();
                    $('#kota, #kecamatan').prop('disabled', true);
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || 'Pastikan semua form terisi dengan benar.';
                alert('Gagal: ' + errorMsg);
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // --- PROSES CHECKOUT ---
    $('#btn-konfirmasi').click(function() {
        let addressVal = $('#alamat_select').val();
        if(!addressVal) {
            alert('Silakan tambah atau pilih alamat pengiriman terlebih dahulu!');
            return;
        }

        let btn = $(this);
        btn.text('Memproses...').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/process",
            type: "POST",
            data: {
                name: $('#penerima_name').val(),
                phone: $('#penerima_phone').val(),
                address: addressVal,
                payment_method: $('input[name="payment_method"]:checked').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = res.data.redirect_url;
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal memproses pesanan.');
                btn.text('Konfirmasi Pesanan').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush