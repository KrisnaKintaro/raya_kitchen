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
                        
                        <!-- BAGIAN ALAMAT YANG DIGANTI -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-bold text-gray-700">Pilih Alamat Pengiriman</label>
                            </div>
                            
                            @php $addresses = auth()->user()->addresses()->orderByDesc('is_primary')->get(); @endphp
                            
                            @if($addresses->isEmpty())
                                <div id="no-address-alert" class="p-4 border-2 border-dashed border-red-200 bg-red-50 rounded-xl text-center">
                                    <p class="text-sm text-red-600 font-bold mb-3">Kamu belum memiliki alamat pengiriman.</p>
                                    <button type="button" onclick="openAddressModal()" class="py-2 px-6 bg-purple-600 text-white font-bold rounded-lg text-sm shadow-md hover:bg-purple-700 transition">
                                        + Tambah Alamat Sekarang
                                    </button>
                                </div>
                                <select id="alamat_select" class="hidden w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700" required></select>
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
                        <!-- AKHIR BAGIAN ALAMAT -->

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

<!-- BAGIAN MODAL HTML YANG DITAMBAH -->
<div id="addressModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-black text-gray-800">Tambah Alamat</h3>
            <button type="button" onclick="$('#addressModal').addClass('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
        </div>
        
        <form id="form-tambah-alamat" class="space-y-4">
            <div>
                <label class="text-xs font-bold text-gray-600">Label (Misal: Rumah / Kantor)</label>
                <input type="text" name="label" required class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-purple-400">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-600">Provinsi</label>
                    <select id="provinsi" required class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white"><option value="">Pilih Provinsi...</option></select>
                    <input type="hidden" name="province" id="province_name">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Kota / Kabupaten</label>
                    <select id="kota" required disabled class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white"><option value="">Pilih Kota...</option></select>
                    <input type="hidden" name="city" id="city_name">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-600">Kecamatan</label>
                    <select id="kecamatan" required disabled class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white"><option value="">Pilih Kecamatan...</option></select>
                    <input type="hidden" name="district" id="district_name">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Kode Pos</label>
                    <input type="number" name="postal_code" required class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white">
                </div>
            </div>

            <div>
                <label class="text-xs font-bold text-gray-600">Alamat Lengkap (Jalan, No, RT/RW)</label>
                <textarea name="street_address" required rows="3" class="w-full mt-1 p-2.5 border rounded-lg text-sm bg-gray-50 focus:bg-white resize-none"></textarea>
            </div>

            <button type="submit" id="btn-simpan-alamat" class="w-full py-3 bg-purple-600 text-white font-bold rounded-xl shadow-md hover:bg-purple-700 transition">
                Simpan & Gunakan Alamat
            </button>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    function formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); }

    // Efek UI Radio Button Pembayaran
    $('input[name="payment_method"]').change(function() {
        $('.payment-lbl').removeClass('border-purple-500 bg-purple-50 text-purple-700').addClass('border-gray-200 bg-white text-gray-700');
        $(this).closest('label').removeClass('border-gray-200 bg-white text-gray-700').addClass('border-purple-500 bg-purple-50 text-purple-700');
    });

    // Ambil Data Keranjang buat Summary
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

    // Proses Submit Checkout ke Controller
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

// BAGIAN SCRIPT EMSIFA DAN AJAX MODAL YANG DITAMBAH
function openAddressModal() {
    $('#addressModal').removeClass('hidden');
    if($('#provinsi option').length === 1) { 
        fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
            .then(response => response.json())
            .then(provinces => {
                let options = '<option value="">Pilih Provinsi...</option>';
                provinces.forEach(p => options += `<option value="${p.id}" data-name="${p.name}">${p.name}</option>`);
                $('#provinsi').html(options);
            });
    }
}

$('#provinsi').change(function() {
    let id = $(this).val();
    $('#province_name').val($(this).find(':selected').data('name'));
    $('#kota').html('<option value="">Loading...</option>').prop('disabled', true);
    $('#kecamatan').html('<option value="">Pilih Kecamatan...</option>').prop('disabled', true);
    
    if(id) {
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${id}.json`)
            .then(res => res.json())
            .then(cities => {
                let options = '<option value="">Pilih Kota...</option>';
                cities.forEach(c => options += `<option value="${c.id}" data-name="${c.name}">${c.name}</option>`);
                $('#kota').html(options).prop('disabled', false);
            });
    }
});

$('#kota').change(function() {
    let id = $(this).val();
    $('#city_name').val($(this).find(':selected').data('name'));
    $('#kecamatan').html('<option value="">Loading...</option>').prop('disabled', true);
    
    if(id) {
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${id}.json`)
            .then(res => res.json())
            .then(districts => {
                let options = '<option value="">Pilih Kecamatan...</option>';
                districts.forEach(d => options += `<option value="${d.id}" data-name="${d.name}">${d.name}</option>`);
                $('#kecamatan').html(options).prop('disabled', false);
            });
    }
});

$('#kecamatan').change(function() {
    $('#district_name').val($(this).find(':selected').data('name'));
});

$('#form-tambah-alamat').submit(function(e) {
    e.preventDefault();
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
                $('#no-address-alert').addClass('hidden');
                let select = $('#alamat_select');
                select.removeClass('hidden').append(new Option(res.data.text, res.data.value, true, true));
                
                $('#addressModal').addClass('hidden');
                $('#form-tambah-alamat')[0].reset();
                $('#kota, #kecamatan').prop('disabled', true);
            }
        },
        error: function(xhr) {
            alert('Gagal menyimpan alamat. Pastikan semua field terisi.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});
</script>
@endpush