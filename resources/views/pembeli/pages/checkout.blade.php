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
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium text-gray-700">Pilih Alamat Pengiriman</label>
                                <a href="{{ url('/alamat') }}" class="text-xs text-purple-600 hover:underline font-semibold">+ Tambah / Kelola Alamat</a>
                            </div>
                            <select id="alamat_select" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-purple-400 text-sm bg-white font-medium text-gray-700">
                                @forelse(auth()->user()->addresses()->orderByDesc('is_primary')->get() as $addr)
                                    @php
                                        // Menyusun teks alamat
                                        $tandaUtama = $addr->is_primary ? '[Utama] ' : '';
                                        $labelAlamat = $addr->label ? $addr->label . ' - ' : '';
                                        $detailJalan = $addr->street_address . ', ' . $addr->district . ', ' . $addr->city . ', ' . $addr->province . ' ' . $addr->postal_code;
                                        
                                        $teksLengkap = $tandaUtama . $labelAlamat . $detailJalan;
                                    @endphp
                                    <option value="{{ $teksLengkap }}" {{ $addr->is_primary ? 'selected' : '' }}>
                                        {{ $teksLengkap }}
                                    </option>
                                @empty
                                    <option value="" disabled selected>Belum ada alamat, silakan tambah di profil.</option>
                                @endforelse
                            </select>
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

            <!-- Ringkasan Pesanan (Ditarik JS) -->
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
        let btn = $(this);
        btn.text('Memproses...').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/process",
            type: "POST",
            data: {
                name: $('#penerima_name').val(),
                phone: $('#penerima_phone').val(),
                address: $('#alamat_select').val(),
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