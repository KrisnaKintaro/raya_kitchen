@extends('pembeli.pembeli_master')

@section('content')
<style>
@media print {
    body * { visibility: hidden !important; }
    #nota-card, #nota-card * { visibility: visible !important; }
    #nota-card { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; margin: 0 !important; box-shadow: none !important; border: none !important; }
    .print\:hidden { display: none !important; }
}
</style>

<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header & Action Button --}}
        <div class="flex justify-between items-center print:hidden">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Bukti Transaksi</span>
                <h1 class="text-2xl font-extrabold text-gray-900 mt-2">Nota Digital & Pelacakan</h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ url('/riwayat-pesanan') }}" class="px-4 py-2.5 bg-white border-2 border-purple-200 hover:bg-purple-50 text-purple-700 font-semibold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
                <button onclick="window.print()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Nota
                </button>
            </div>
        </div>

        {{-- Loading Skeleton --}}
        <div id="loading-nota" class="animate-pulse bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 flex flex-col gap-6">
            <div class="h-10 bg-gray-200 rounded w-1/3 mx-auto"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
            <div class="h-32 bg-gray-100 rounded w-full"></div>
            <div class="h-10 bg-gray-200 rounded w-1/4 ml-auto"></div>
        </div>

        {{-- Card Struk / Nota Digital --}}
        <div id="nota-card" class="hidden bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 relative overflow-hidden">

            {{-- Watermark Status --}}
            <div id="watermark-status" class="absolute top-6 right-6 border-2 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 uppercase"></div>

            {{-- Header Toko --}}
            <div class="text-center border-b border-gray-100 pb-6">
                <h2 id="toko-nama" class="text-2xl font-black tracking-tight text-purple-900 uppercase">-</h2>
                <p id="toko-desc" class="text-xs text-gray-500 mt-1 font-bold">-</p>
                <p id="toko-kontak" class="text-xs text-gray-400">-</p>
            </div>

            {{-- Info Transaksi & Pengiriman --}}
            <div class="grid grid-cols-2 gap-y-4 gap-x-6 py-6 border-b border-gray-100 text-sm">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">No. Transaksi</p>
                    <p id="info-no-trx" class="font-black text-purple-700 mt-0.5 text-base">-</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Tgl Transaksi</p>
                    <p id="info-tgl-trx" class="font-semibold text-gray-700 mt-0.5">-</p>
                </div>

                <div class="col-span-2 p-3.5 bg-purple-50 rounded-xl border border-purple-100 space-y-2">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <p id="info-jadwal-label" class="text-[10px] text-purple-600 uppercase font-extrabold tracking-wider">Jadwal Pengiriman / Estimasi Sampai</p>
                        <span id="info-kurir-badge" class="text-[10px] font-black px-2.5 py-0.5 rounded-full bg-white text-purple-700 border border-purple-200">-</span>
                    </div>
                    <p id="info-jadwal" class="font-black text-gray-800 text-base">-</p>

                    {{-- Baris Nomor Resi + Tombol Salin --}}
                    <div id="info-resi-row" class="hidden pt-2 border-t border-purple-200/60 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-indigo-800">
                            <span>Nomor Resi:</span>
                            <span id="info-resi" class="font-mono font-black text-sm bg-white px-2.5 py-1 rounded-lg border border-indigo-200 tracking-wider">-</span>
                        </div>
                        <button type="button" onclick="copyResiNota()" class="print:hidden px-3 py-1 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-[11px] font-extrabold rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span class="btn-copy-resi-text">Salin Resi</span>
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Penerima</p>
                    <p id="info-penerima-nama" class="font-semibold text-gray-700 mt-0.5">-</p>
                    <p id="info-penerima-wa" class="text-xs text-gray-500">-</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Alamat Kirim</p>
                    <p id="info-alamat" class="font-semibold text-gray-700 mt-0.5 leading-snug">-</p>
                </div>
            </div>

            {{-- Table Item Roti --}}
            <div class="py-6 border-b border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] text-gray-400 border-b border-gray-100 pb-2 uppercase tracking-wider font-extrabold">
                            <th class="pb-3">Produk</th>
                            <th class="text-center pb-3">Qty</th>
                            <th class="text-right pb-3">Harga</th>
                            <th class="text-right pb-3">Total</th>
                        </tr>
                    </thead>
                    <tbody id="table-items" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>

            {{-- Subtotal, Ongkir & Grand Total --}}
            <div class="pt-6 space-y-3 text-sm">
                <div class="flex justify-between text-gray-500 font-medium">
                    <span>Subtotal Harga Produk</span>
                    <span id="summary-subtotal-produk">Rp 0</span>
                </div>
                <div class="flex justify-between text-gray-500 font-medium">
                    <span id="summary-ongkir-label">Ongkos Kirim</span>
                    <span id="summary-ongkir">Rp 0</span>
                </div>
                <div class="flex justify-between text-gray-700 font-bold">
                    <span>Total Keseluruhan</span>
                    <span id="summary-total-barang">Rp 0</span>
                </div>
                <div id="summary-dp-container" class="hidden flex justify-between text-gray-500 font-medium">
                    <span>DP Dibayarkan (30%)</span>
                    <span id="summary-dp" class="text-green-600 font-bold">Rp 0</span>
                </div>
                <div id="summary-sisa-container" class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span id="summary-label" class="font-extrabold text-gray-900 text-base">Total Tagihan</span>
                    <span id="summary-value" class="text-xl font-black text-purple-700">Rp 0</span>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- BAGIAN LIVE TRACKING PAKET & TIMELINE MANIFEST KURIR              --}}
            {{-- ================================================================= --}}
            <div id="live-tracking-section" class="hidden mt-8 pt-6 border-t border-gray-100">
                <div class="p-5 bg-[#F8F6FC] rounded-2xl border-2 border-purple-100">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-purple-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wide">Pelacakan Pengiriman Paket</h3>
                                <p id="tracking-courier-subtitle" class="text-xs font-bold text-purple-700">-</p>
                            </div>
                        </div>
                        <span id="tracking-status-badge" class="text-[10px] font-black uppercase px-3 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                            Menunggu Resi
                        </span>
                    </div>

                    {{-- Box Resi & Salin Cepat --}}
                    <div id="tracking-resi-card" class="hidden mb-4 p-3.5 bg-white rounded-xl border border-purple-100 flex flex-wrap items-center justify-between gap-3 shadow-sm">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block">Nomor Resi Ekspedisi</span>
                            <span id="tracking-resi-number" class="font-mono text-base font-black text-indigo-700 tracking-wider">-</span>
                        </div>
                        <button type="button" onclick="copyResiNota()" class="print:hidden px-3.5 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 text-xs font-black rounded-xl transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span class="btn-copy-resi-text">Salin Nomor Resi</span>
                        </button>
                    </div>

                    {{-- Stepper 4 Tahap --}}
                    <div id="tracking-stepper" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-5"></div>

                    {{-- Timeline Riwayat Manifest Kurir --}}
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 mb-3">Riwayat Perjalanan Paket</p>
                        <div id="tracking-manifest-list" class="space-y-3 pl-2"></div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN BUKTI PENGIRIMAN KURIR (MUNCUL JIKA BARANG SUDAH SAMPAI) --}}
            <div id="delivery-proof-section" class="hidden mt-8 pt-6 border-t border-gray-100">
                <div class="p-5 bg-emerald-50/70 rounded-2xl border border-emerald-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-emerald-900">Bukti Pengiriman Kurir (Barang Sampai)</h3>
                                <p id="proof-courier-subtitle" class="text-[11px] font-semibold text-emerald-700">-</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">Terkirim</span>
                    </div>

                    <div id="proof-images-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                        <div id="card-arrival-proof" class="hidden bg-white p-3 rounded-xl border border-emerald-100 shadow-sm">
                            <p class="text-xs font-extrabold text-gray-700 mb-2">Foto Barang Sampai (POD)</p>
                            <div class="rounded-lg overflow-hidden bg-gray-50 border border-gray-100 flex justify-center p-1 cursor-pointer hover:opacity-90 transition" onclick="openImageZoomModal($('#img-arrival-proof').attr('src'), 'Bukti Barang Sampai (POD)')">
                                <img id="img-arrival-proof" src="" alt="Bukti Barang Sampai" class="max-h-48 w-auto object-contain rounded">
                            </div>
                            <button type="button" onclick="openImageZoomModal($('#img-arrival-proof').attr('src'), 'Bukti Barang Sampai (POD)')" class="mt-2 w-full text-[11px] font-bold text-emerald-700 hover:underline text-center block print:hidden">
                                Klik untuk memperbesar
                            </button>
                        </div>

                        <div id="card-payment-proof" class="hidden bg-white p-3 rounded-xl border border-emerald-100 shadow-sm">
                            <p class="text-xs font-extrabold text-gray-700 mb-2">Foto Bukti Pelunasan Kurir</p>
                            <div class="rounded-lg overflow-hidden bg-gray-50 border border-gray-100 flex justify-center p-1 cursor-pointer hover:opacity-90 transition" onclick="openImageZoomModal($('#img-payment-proof').attr('src'), 'Bukti Pelunasan Kurir')">
                                <img id="img-payment-proof" src="" alt="Bukti Pelunasan" class="max-h-48 w-auto object-contain rounded">
                            </div>
                            <button type="button" onclick="openImageZoomModal($('#img-payment-proof').attr('src'), 'Bukti Pelunasan Kurir')" class="mt-2 w-full text-[11px] font-bold text-emerald-700 hover:underline text-center block print:hidden">
                                Klik untuk memperbesar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tampilan Bawah (Kondisional Status Pembayaran/Batal) --}}
            <div id="bottom-section" class="mt-8 pt-6 border-t border-gray-100 print:hidden hidden"></div>

        </div>
    </div>
</div>

<!-- Modal Zoom Gambar Universal (Bukti Pengiriman & Bukti Refund) -->
<div id="imageZoomModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 print:hidden p-4" onclick="closeImageZoomModal()">
    <div class="relative bg-white p-4 rounded-3xl max-w-[90vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-2 px-1">
            <p id="zoom-modal-caption" class="text-xs font-black text-gray-700 uppercase tracking-wider">Preview Gambar</p>
            <button type="button" onclick="closeImageZoomModal()" class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center shadow font-bold hover:scale-105 transition-transform">&times;</button>
        </div>
        <img id="zoom-modal-img" src="" alt="Preview Besar" class="w-full h-full object-contain max-h-[78vh] rounded-2xl">
    </div>
</div>

<!-- POP-UP MODAL PENGINGAT PELUNASAN SAAT STATUS SIAP DIKIRIM -->
<div id="pelunasanReminderModal" class="fixed inset-0 z-[65] flex items-center justify-center bg-buyer-textPrimary/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 print:hidden p-4">
    <div class="bg-buyer-bg border-2 border-white rounded-[2.5rem] max-w-md w-full p-7 text-center shadow-[inset_0_-4px_8px_rgba(192,153,206,0.3),inset_0_4px_8px_rgba(255,255,255,1),0_20px_40px_rgba(0,0,0,0.25)] transform scale-95 transition-transform duration-300 relative">
        <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner border-4 border-white">
            <svg class="w-10 h-10 text-amber-500 animate-bounce" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700">Pesanan Siap Dikirim!</span>
        <h3 class="text-xl font-black text-buyer-textPrimary mt-2 mb-2">Segera Selesaikan Pelunasan</h3>
        <p id="reminder-modal-desc" class="text-xs text-buyer-textSecondary font-semibold leading-relaxed mb-4">
            Pesananmu sudah selesai disiapkan dapur dan siap dikirim.
        </p>
        <div class="p-4 bg-white rounded-2xl border border-purple-100 mb-6">
            <p class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Sisa Tagihan Pelunasan (70%)</p>
            <p id="reminder-modal-amount" class="text-2xl font-black text-red-600 mt-0.5">Rp 0</p>
            <p id="reminder-modal-method" class="text-[11px] font-bold text-purple-700 mt-1">-</p>
        </div>
        <div class="flex flex-col gap-2.5">
            <button type="button" id="btn-modal-lunasi-qris" class="w-full py-3.5 bg-red-500 hover:bg-red-600 text-white font-black text-sm rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2">
                <span>Bayar Pelunasan QRIS Sekarang</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
            <button type="button" onclick="closePelunasanReminderModal()" class="w-full py-3 bg-white hover:bg-gray-50 text-gray-600 font-extrabold text-xs rounded-2xl border border-gray-200 transition-all">
                Lihat Detail Nota
            </button>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    let currentNotaResi = '';

    function copyResiNota() {
        if (!currentNotaResi) return;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(currentNotaResi).then(() => {
                $('.btn-copy-resi-text').text('Resi Tersalin!');
                setTimeout(() => {
                    $('.btn-copy-resi-text').text('Salin Resi');
                }, 2000);
            });
        }
    }

    function openImageZoomModal(imgUrl, captionText = 'Preview Gambar') {
        if (!imgUrl) return;
        $('#zoom-modal-img').attr('src', imgUrl);
        $('#zoom-modal-caption').text(captionText);
        $('#imageZoomModal').removeClass('hidden');
        setTimeout(() => {
            $('#imageZoomModal').removeClass('opacity-0');
            $('#imageZoomModal > div').removeClass('scale-95');
        }, 10);
    }

    function closeImageZoomModal() {
        $('#imageZoomModal').addClass('opacity-0');
        $('#imageZoomModal > div').addClass('scale-95');
        setTimeout(() => { $('#imageZoomModal').addClass('hidden'); }, 300);
    }

    function openPelunasanReminderModal() {
        $('#pelunasanReminderModal').removeClass('hidden');
        setTimeout(() => {
            $('#pelunasanReminderModal').removeClass('opacity-0');
            $('#pelunasanReminderModal > div').removeClass('scale-95');
        }, 50);
    }

    function closePelunasanReminderModal() {
        $('#pelunasanReminderModal').addClass('opacity-0');
        $('#pelunasanReminderModal > div').addClass('scale-95');
        setTimeout(() => { $('#pelunasanReminderModal').addClass('hidden'); }, 300);
    }

    function kirimRekeningRefund(e, orderNumber) {
        e.preventDefault();
        let bank = $('#input-refund-bank').val().trim();
        let norek = $('#input-refund-norek').val().trim();
        let nama = $('#input-refund-nama').val().trim();

        if (!bank || !norek || !nama) {
            alert('Harap lengkapi Nama Bank/E-Wallet, Nomor Rekening, dan Atas Nama.');
            return;
        }

        let btn = $('#btn-submit-rekening-refund');
        let oriText = btn.html();
        btn.html('<span class="animate-pulse">Menyimpan Rekening...</span>').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/refund-account",
            type: "POST",
            data: {
                order_number: orderNumber,
                refund_bank: bank,
                refund_norek: norek,
                refund_nama: nama,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status === 'success') {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menyimpan nomor rekening refund.');
                btn.html(oriText).prop('disabled', false);
            }
        });
    }

    function lunasiTagihan(orderNumber) {
        let btn = $('#btn-lunasi, #btn-modal-lunasi-qris');
        let originalText = btn.first().html();
        btn.html('<span class="animate-pulse">Menyiapkan QRIS...</span>').prop('disabled', true);

        $.ajax({
            url: "/api/pembeli/checkout/pay-remaining",
            type: "POST",
            data: {
                order_number: orderNumber,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if(res.status === 'success') {
                    window.location.href = res.data.redirect_url;
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal memproses pelunasan.');
                btn.html(originalText).prop('disabled', false);
            }
        });
    }

    function renderLiveTrackingSection(order, meta, rawOrderStatus, paymentStatus) {
        if (rawOrderStatus === 'dibatalkan' || paymentStatus === 'unpaid' || paymentStatus === 'failed') {
            return;
        }

        const isInternal = Boolean(meta.is_internal);
        const trackingNumber = meta.tracking_number || order.tracking_number || '';
        const courierStatus = (meta.courier_status || order.courier_status || (trackingNumber ? 'in_transit' : 'waiting_resi')).toLowerCase();
        const manifest = Array.isArray(meta.courier_manifest) ? meta.courier_manifest : [];
        const isSiapDikirim = ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar', 'selesai'].includes(rawOrderStatus);

        if (isInternal && !isSiapDikirim) {
            return;
        }

        $('#live-tracking-section').removeClass('hidden');
        $('#tracking-courier-subtitle').text(`${meta.courier_name || 'Kurir'} — ${meta.shipping_service || '-'}`);

        if (!isInternal && trackingNumber) {
            currentNotaResi = trackingNumber;
            $('#tracking-resi-number').text(trackingNumber);
            $('#tracking-resi-card').removeClass('hidden');
        }

        const statusMetaMap = {
            waiting_resi: {
                label: isInternal ? 'Pesanan Disiapkan' : 'Menunggu Input Resi Kurir',
                badgeClass: 'bg-gray-100 text-gray-700 border-gray-300',
                step: 1
            },
            in_transit: {
                label: 'Paket Dikirim (Dalam Perjalanan)',
                badgeClass: 'bg-blue-100 text-blue-800 border-blue-300',
                step: 2
            },
            on_delivery: {
                label: 'Kurir Menuju Lokasi Penerima',
                badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-300',
                step: 3
            },
            delivered: {
                label: 'Paket Telah Sampai di Tujuan',
                badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300',
                step: 4
            },
            problem: {
                label: 'Kendala Pengiriman Kurir',
                badgeClass: 'bg-red-100 text-red-800 border-red-300',
                step: 2
            }
        };

        let activeKey = courierStatus;
        if (rawOrderStatus === 'selesai' || order.arrival_proof_url) {
            activeKey = 'delivered';
        } else if (isInternal && isSiapDikirim) {
            activeKey = 'on_delivery';
        }

        const currentMeta = statusMetaMap[activeKey] || statusMetaMap.waiting_resi;

        $('#tracking-status-badge')
            .attr('class', `text-[10px] font-black uppercase px-3 py-1 rounded-full border ${currentMeta.badgeClass}`)
            .text(currentMeta.label);

        const steps = isInternal ? [
            { num: 1, title: 'Pesanan Dibuat' },
            { num: 2, title: 'Selesai Dimasak' },
            { num: 3, title: 'Diantar Kurir Toko' },
            { num: 4, title: 'Pesanan Sampai' }
        ] : [
            { num: 1, title: 'Siap Dikirim' },
            { num: 2, title: 'Diserahkan ke Kurir' },
            { num: 3, title: 'Menuju Lokasi' },
            { num: 4, title: 'Paket Diterima' }
        ];

        const stepperHtml = steps.map(s => {
            const isDone = currentMeta.step >= s.num;
            const isCurrent = currentMeta.step === s.num;
            const boxStyle = isDone
                ? (isCurrent ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-purple-50 text-purple-800 border-purple-200')
                : 'bg-white text-gray-400 border-gray-200 opacity-60';
            const circleStyle = isDone
                ? (isCurrent ? 'bg-white text-purple-700' : 'bg-purple-600 text-white')
                : 'bg-gray-100 text-gray-400';

            return `
                <div class="p-2.5 rounded-xl border ${boxStyle} flex items-center gap-2.5 transition-all">
                    <span class="w-6 h-6 rounded-full text-[11px] font-black flex items-center justify-center shrink-0 ${circleStyle}">
                        ${isDone && !isCurrent ? '&#10003;' : s.num}
                    </span>
                    <span class="text-[11px] font-extrabold leading-tight">${s.title}</span>
                </div>
            `;
        }).join('');

        $('#tracking-stepper').html(stepperHtml);

        let manifestHtml = '';
        if (manifest.length > 0) {
            manifestHtml = manifest.map((m, idx) => {
                const isLatest = idx === 0;
                const dotClass = isLatest ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-purple-300';
                const textClass = isLatest ? 'text-gray-900 font-bold' : 'text-gray-500 font-medium';
                const badgeClass = isLatest ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600';

                return `
                    <div class="relative pl-6 pb-3 last:pb-0 border-l-2 ${isLatest ? 'border-emerald-400' : 'border-purple-100'}">
                        <span class="absolute -left-[7px] top-1 w-3 h-3 rounded-full ${dotClass}"></span>
                        <div class="bg-white p-3 rounded-xl border ${isLatest ? 'border-emerald-200 shadow-sm' : 'border-gray-100'}">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded ${badgeClass}">${m.status || 'UPDATE'}</span>
                                <span class="text-[10px] font-bold text-gray-400">${m.time || '-'}</span>
                            </div>
                            <p class="text-xs leading-relaxed ${textClass}">${m.description || '-'}</p>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            const defaultMsg = !isInternal && !trackingNumber
                ? 'Pesanan sedang disiapkan untuk diserahkan ke pihak ekspedisi. Nomor resi akan otomatis muncul di halaman ini setelah diinput oleh Admin.'
                : `Pesanan sedang diproses pengirimannya melalui ${meta.courier_name || 'Kurir'}.`;

            manifestHtml = `
                <div class="p-3.5 bg-white rounded-xl border border-purple-100 text-xs font-semibold text-gray-500">
                    ${defaultMsg}
                </div>
            `;
        }

        $('#tracking-manifest-list').html(manifestHtml);
    }

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const orderNumber = urlParams.get('order_number');

        if (!orderNumber) {
            window.location.href = '/riwayat-pesanan';
            return;
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        function formatDate(dateString) {
            const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
            return new window.Date(dateString).toLocaleDateString('id-ID', options).replace(',', '') + ' WIB';
        }

        $.ajax({
            url: `/api/pembeli/nota?order_number=${orderNumber}`,
            type: "GET",
            success: function(res) {
                if(res.status === 'success') {
                    const order = res.data.order;
                    const company = res.data.company;
                    const meta = order.shipping_meta || {};

                    // Header Toko
                    $('#toko-nama').text(company?.company_name || 'RAYA KITCHEN');
                    $('#toko-desc').text(company?.short_description || 'Bakery & Pastry Fresh Everyday');
                    $('#toko-kontak').text(`${company?.address || 'Alamat Belum Diatur'} | WA: +${company?.whatsapp_number || '-'}`);

                    // Info Transaksi
                    $('#info-no-trx').text('#' + order.order_number);
                    $('#info-tgl-trx').text(formatDate(order.created_at));

                    // Jadwal / Estimasi Dinamis
                    $('#info-jadwal-label').text(meta.schedule_label || 'Jadwal Pengiriman / Pengambilan');
                    $('#info-jadwal').text(meta.schedule_text || '-');
                    $('#info-kurir-badge').text(`${meta.courier_name || 'Kurir'} (${meta.shipping_service || '-'})`);

                    if (!meta.is_internal && meta.tracking_number) {
                        currentNotaResi = meta.tracking_number;
                        $('#info-resi').text(meta.tracking_number);
                        $('#info-resi-row').removeClass('hidden');
                    }

                    $('#info-penerima-nama').text(order.recipient_name);
                    $('#info-penerima-wa').text(order.recipient_whatsapp);
                    $('#info-alamat').text(order.shipping_address);

                    // Table Items
                    let itemsHtml = '';
                    let subtotalProduk = 0;
                    order.items.forEach(item => {
                        let productName = item.product_variant?.variant_name || 'Produk';
                        let categoryName = item.product_variant?.product?.name || 'Kategori';
                        subtotalProduk += parseFloat(item.subtotal || 0);

                        itemsHtml += `
                            <tr class="group">
                                <td class="py-3 pr-2">
                                    <p class="font-bold text-gray-800">${productName}</p>
                                    <p class="text-xs text-gray-400">${categoryName}</p>
                                </td>
                                <td class="py-3 text-center text-gray-600 font-medium">${item.quantity}</td>
                                <td class="py-3 text-right text-gray-600">${formatRupiah(item.price_at_order)}</td>
                                <td class="py-3 text-right font-bold text-gray-800">${formatRupiah(item.subtotal)}</td>
                            </tr>
                        `;
                    });
                    $('#table-items').html(itemsHtml);

                    // Financials
                    let shippingCost = parseFloat(order.shipping_cost || 0);
                    $('#summary-subtotal-produk').text(formatRupiah(subtotalProduk));
                    $('#summary-ongkir-label').text(`Ongkos Kirim (${meta.courier_name || 'Kurir'})`);
                    $('#summary-ongkir').text(shippingCost > 0 ? formatRupiah(shippingCost) : 'GRATIS');
                    $('#summary-total-barang').text(formatRupiah(order.total_amount));

                    let remainingAmount = parseFloat(order.remaining_amount || 0);
                    let paymentStatus = (order.transaction?.payment_status || 'unpaid').toLowerCase();
                    let paymentMethod = (order.transaction?.payment_method || 'transfer').toLowerCase();
                    let rawOrderStatus = (order.status || 'pending').toLowerCase().replace(/\s+/g, '_');
                    let isSiapDikirim = ['siap_diambil', 'siap_diantar', 'siap_dikirim', 'dikirim', 'diantar'].includes(rawOrderStatus);

                    // Render Komponen Live Tracking Paket & Riwayat Manifest Kurir
                    renderLiveTrackingSection(order, meta, rawOrderStatus, paymentStatus);

                    // Render Bukti Pengiriman Kurir (Internal maupun Eksternal) jika sudah ada
                    if (order.arrival_proof_url || order.payment_proof_url) {
                        $('#delivery-proof-section').removeClass('hidden');
                        $('#proof-courier-subtitle').text(`Dikirim oleh: ${meta.courier_name || 'Kurir'}${meta.tracking_number ? ' (Resi: ' + meta.tracking_number + ')' : ''}`);

                        if (order.arrival_proof_url) {
                            $('#img-arrival-proof').attr('src', order.arrival_proof_url);
                            $('#card-arrival-proof').removeClass('hidden');
                        }
                        if (order.payment_proof_url) {
                            $('#img-payment-proof').attr('src', order.payment_proof_url);
                            $('#card-payment-proof').removeClass('hidden');
                        }
                    }

                    let watermarkHtml = '';
                    let bottomHtml = '';

                    if (rawOrderStatus === 'dibatalkan') {
                        watermarkHtml = 'DIBATALKAN';
                        $('#watermark-status').addClass('border-red-500 text-red-600 bg-red-50').text(watermarkHtml);
                        $('#summary-label').text('Total Dibatalkan');
                        $('#summary-value').text(formatRupiah(order.total_amount)).removeClass('text-purple-700 text-red-600').addClass('text-gray-500');

                        bottomHtml += `
                            <div class="p-5 bg-red-50 rounded-2xl border border-red-100 mb-4">
                                <div class="flex gap-3 items-start mb-3">
                                    <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-red-800 text-sm mb-1">Pesanan Dibatalkan</p>
                                        <p class="text-xs text-red-600 font-medium">Alasan: ${order.cancellation_reason || '-'}</p>
                                    </div>
                                </div>
                        `;

                        if (order.refund_status === 'pending' && !order.refund_account_number) {
                            bottomHtml += `
                                <div id="form-rekening-refund" class="mt-4 pt-4 border-t border-red-200">
                                    <div class="bg-white p-4 rounded-2xl border-2 border-amber-300 shadow-sm">
                                        <p class="text-xs font-black text-amber-800 uppercase tracking-wider mb-1">Pengajuan Rekening Pengembalian Dana (Refund)</p>
                                        <p class="text-xs text-gray-600 font-medium mb-3">Pesanan ini memiliki dana masuk yang perlu dikembalikan. Silakan isi rekening / E-Wallet tujuan transfer refund Anda di bawah ini:</p>
                                        <form onsubmit="kirimRekeningRefund(event, '${order.order_number}')" class="space-y-3">
                                            <input type="text" id="input-refund-bank" required placeholder="Nama Bank / E-Wallet (Cth: BCA, Mandiri, GoPay)" class="w-full p-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400">
                                            <input type="text" id="input-refund-norek" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Nomor Rekening / Nomor E-Wallet" class="w-full p-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400">
                                            <input type="text" id="input-refund-nama" required placeholder="Atas Nama Pemilik Rekening (A.N)" class="w-full p-2.5 text-xs font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-400">
                                            <button type="submit" id="btn-submit-rekening-refund" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-black text-xs rounded-xl shadow-md transition">
                                                Kirim Nomor Rekening Refund
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            `;
                        } else if (order.refund_account_number || order.refund_status === 'refunded') {
                            bottomHtml += `<div class="mt-4 pt-4 border-t border-red-100"><p class="text-xs font-bold text-red-800 mb-2">Status Pengembalian Dana (Refund)</p>`;
                            if (order.refund_proof) {
                                let refundUrl = order.refund_proof_url || (order.refund_proof.startsWith('http')
                                    ? order.refund_proof
                                    : '/storage/bukti_refund/' + order.refund_proof.replace(/^\/?(storage\/)?(bukti_refund\/)?/i, ''));
                                bottomHtml += `
                                    <div class="bg-white p-3 rounded-xl border border-red-100">
                                        <span class="text-xs font-bold text-green-600 block mb-3">Dana berhasil dikembalikan oleh Admin ke (${order.refund_account_number || '-'})</span>
                                        <div class="rounded-lg overflow-hidden border border-gray-100 mb-3 bg-gray-50 flex justify-center p-2 cursor-pointer" onclick="openImageZoomModal('${refundUrl}', 'Bukti Refund Admin')">
                                            <img src="${refundUrl}" alt="Bukti Refund" class="max-h-48 w-auto object-contain rounded-md shadow-sm">
                                        </div>
                                        <button type="button" onclick="openImageZoomModal('${refundUrl}', 'Bukti Refund Admin')" class="w-full text-xs font-bold text-red-600 hover:text-red-700 underline block text-center">Lihat Bukti Transfer Ukuran Penuh</button>
                                    </div>
                                `;
                            } else {
                                bottomHtml += `
                                    <div class="flex items-center gap-2 text-xs font-medium text-amber-700 bg-amber-50 p-3 rounded-xl border border-amber-200">
                                        Refund sedang diproses Admin ke rekening (${order.refund_account_number}). Maksimal 1x24 jam.
                                    </div>
                                `;
                            }
                            bottomHtml += `</div>`;
                        }

                        bottomHtml += `</div>`;
                        $('#bottom-section').html(bottomHtml).removeClass('hidden');
                    } else if (paymentStatus === 'unpaid' || paymentStatus === 'failed') {
                        watermarkHtml = 'BELUM DIBAYAR';
                        $('#watermark-status').addClass('border-red-500 text-red-600 bg-red-50').text(watermarkHtml);
                        $('#summary-label').text('Tagihan Saat Ini');
                        $('#summary-value').text(formatRupiah(order.transaction?.amount || order.total_amount)).removeClass('text-purple-700').addClass('text-red-600');

                        $('#bottom-section').html(`
                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 mb-4 flex gap-3 items-start">
                                <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="font-bold text-amber-800 text-sm mb-1">Menunggu Pembayaran QRIS</p>
                                    <p class="text-xs text-amber-700 font-medium">Pesananmu belum dibayar. Silakan selesaikan pembayaran QRIS agar pesanan segera diproses dapur.</p>
                                </div>
                            </div>
                            <a href="/pembayaran/${order.order_number}" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-center text-sm transition shadow-md">
                                Bayar Sekarang via QRIS
                            </a>
                        `).removeClass('hidden');
                    } else if (paymentStatus === 'paid_full' || remainingAmount <= 0) {
                        watermarkHtml = 'LUNAS';
                        $('#watermark-status').addClass('border-green-500 text-green-600 bg-green-50').text(watermarkHtml);
                        $('#summary-label').text('Total Lunas');
                        $('#summary-value').text(formatRupiah(order.total_amount));
                        $('#bottom-section').html('<p class="text-sm font-bold text-green-600 text-center">Pesanan telah lunas. Terima kasih telah berbelanja di Raya Kitchen!</p>').removeClass('hidden');
                    } else {
                        watermarkHtml = `DP 30% - Sisa ${formatRupiah(remainingAmount)}`;
                        $('#watermark-status').addClass('border-amber-500 text-amber-600 bg-amber-50').text(watermarkHtml);
                        $('#summary-dp-container').removeClass('hidden');
                        $('#summary-dp').text(formatRupiah(order.dp_amount));
                        $('#summary-label').text('Sisa Tagihan');
                        $('#summary-value').text(formatRupiah(remainingAmount)).removeClass('text-purple-700').addClass('text-red-600');

                        if (paymentMethod === 'cod') {
                            $('#bottom-section').html(`
                                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 mb-4 flex gap-3 items-start">
                                    <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-amber-800 text-sm mb-1">Tagihan Belum Lunas (COD - DP 30%)</p>
                                        <p class="text-xs text-amber-700 font-medium">Pesananmu baru dibayar DP. Harap siapkan uang tunai sebesar <strong>${formatRupiah(remainingAmount)}</strong> saat barang sampai, atau lunasi langsung via QRIS di bawah ini.</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-lunasi" onclick="lunasiTagihan('${order.order_number}')" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-center text-sm transition shadow-md">
                                    Lunasi Sisa Tagihan via QRIS Sekarang
                                </button>
                            `).removeClass('hidden');
                        } else {
                            $('#bottom-section').html(`
                                <div class="p-4 bg-red-50 rounded-2xl border border-red-100 mb-4 flex gap-3 items-start">
                                    <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-red-800 text-sm mb-1">Tagihan Belum Lunas (Transfer - DP 30%)</p>
                                        <p class="text-xs text-red-600 font-medium">Pesananmu baru dibayar DP. Silakan segera lunasi sisa tagihan sebesar <strong>${formatRupiah(remainingAmount)}</strong> melalui QRIS.</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-lunasi" onclick="lunasiTagihan('${order.order_number}')" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl text-center text-sm transition shadow-[0_4px_10px_rgba(239,68,68,0.3)]">
                                    Bayar Sisa Tagihan Sekarang
                                </button>
                            `).removeClass('hidden');
                        }

                        if (isSiapDikirim) {
                            $('#reminder-modal-amount').text(formatRupiah(remainingAmount));
                            $('#btn-modal-lunasi-qris').attr('onclick', `lunasiTagihan('${order.order_number}')`);
                            if (paymentMethod === 'cod') {
                                $('#reminder-modal-method').text('Metode: COD (Wajib Pelunasan 70%)');
                                $('#reminder-modal-desc').html(`Pesanan <strong>#${order.order_number}</strong> sudah <strong>SIAP DIKIRIM</strong>! Mohon segera siapkan uang tunai sebesar <strong>${formatRupiah(remainingAmount)}</strong> untuk diserahkan ke kurir saat barang sampai, atau lunasi praktis via QRIS sekarang.`);
                            } else {
                                $('#reminder-modal-method').text('Metode: Transfer QRIS (DP 30%)');
                                $('#reminder-modal-desc').html(`Pesanan <strong>#${order.order_number}</strong> sudah <strong>SIAP DIKIRIM</strong>! Yuk segera selesaikan pelunasan sisa tagihan sebesar <strong>${formatRupiah(remainingAmount)}</strong> agar pesananmu bisa langsung diselesaikan.`);
                            }
                            openPelunasanReminderModal();
                        }
                    }

                    $('#loading-nota').addClass('hidden');
                    $('#nota-card').removeClass('hidden');
                }
            },
            error: function() {
                alert('Gagal mengambil data nota. Pastikan nomor order benar.');
                window.location.href = '/riwayat-pesanan';
            }
        });
    });
</script>
@endpush