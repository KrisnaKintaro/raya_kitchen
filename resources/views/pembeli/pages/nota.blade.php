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
                <h1 class="text-2xl font-extrabold text-gray-900 mt-2">Nota Digital</h1>
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

        {{-- Card Struk / Nota Digital (Hidden by default) --}}
        <div id="nota-card" class="hidden bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 relative overflow-hidden">

            {{-- Watermark Status --}}
            <div id="watermark-status" class="absolute top-6 right-6 border-2 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 uppercase">
                <!-- Injected via JS -->
            </div>

            {{-- Header Toko --}}
            <div class="text-center border-b border-gray-100 pb-6">
                <h2 id="toko-nama" class="text-2xl font-black tracking-tight text-purple-900 uppercase">-</h2>
                <p id="toko-desc" class="text-xs text-gray-500 mt-1 font-bold">-</p>
                <p id="toko-kontak" class="text-xs text-gray-400">-</p>
            </div>

            {{-- Info Transaksi --}}
            <div class="grid grid-cols-2 gap-y-4 gap-x-6 py-6 border-b border-gray-100 text-sm">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">No. Transaksi</p>
                    <p id="info-no-trx" class="font-black text-purple-700 mt-0.5 text-base">-</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Tgl Transaksi</p>
                    <p id="info-tgl-trx" class="font-semibold text-gray-700 mt-0.5">-</p>
                </div>

                <div class="col-span-2 p-3 bg-purple-50 rounded-xl border border-purple-100">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-[10px] text-purple-500 uppercase font-extrabold tracking-wider">Jadwal Pengiriman / Pengambilan</p>
                    </div>
                    <p id="info-jadwal" class="font-black text-gray-800 text-base">-</p>
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
                    <tbody id="table-items" class="divide-y divide-gray-50">
                        <!-- Injected via JS -->
                    </tbody>
                </table>
            </div>

            {{-- Subtotal & Grand Total --}}
            <div class="pt-6 space-y-3 text-sm">
                <div class="flex justify-between text-gray-500 font-medium">
                    <span>Total Harga Barang</span>
                    <span id="summary-total-barang">Rp 0</span>
                </div>

                <div id="summary-dp-container" class="hidden flex justify-between text-gray-500 font-medium">
                    <span>DP Dibayarkan (30%)</span>
                    <span id="summary-dp" class="text-green-600">Rp 0</span>
                </div>
                <div id="summary-sisa-container" class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span id="summary-label" class="font-extrabold text-gray-900 text-base">Total Tagihan</span>
                    <span id="summary-value" class="text-xl font-black text-purple-700">Rp 0</span>
                </div>
            </div>

            {{-- Tampilan Bawah (Kondisional) --}}
            <div id="bottom-section" class="mt-8 pt-6 border-t border-gray-100 print:hidden hidden">
                <!-- Diisi via JS (Batal/COD/Transfer/Lunas) -->
            </div>

        </div>
    </div>
</div>

<!-- Modal Zoom Bukti Refund -->
<div id="refundZoomModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 print:hidden" onclick="closeRefundZoomModal()">
    <div class="relative bg-white p-4 rounded-3xl max-w-[90vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
        <button type="button" onclick="closeRefundZoomModal()" class="absolute -top-4 -right-4 w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg font-bold hover:scale-105 active:scale-95 transition-transform">
            &times;
        </button>
        <img id="zoom-refund-img" src="" alt="Bukti Refund Besar" class="w-full h-full object-contain max-h-[80vh] rounded-2xl">
    </div>
</div>
@endsection

@push('script')
<script>
    function openRefundZoomModal() {
        $('#refundZoomModal').removeClass('hidden');
        setTimeout(() => {
            $('#refundZoomModal').removeClass('opacity-0');
            $('#refundZoomModal > div').removeClass('scale-95');
        }, 10);
    }

    function closeRefundZoomModal() {
        $('#refundZoomModal').addClass('opacity-0');
        $('#refundZoomModal > div').addClass('scale-95');
        setTimeout(() => { $('#refundZoomModal').addClass('hidden'); }, 300);
    }

    function lunasiTagihan(orderNumber) {
        let btn = $('#btn-lunasi');
        let originalText = btn.html();
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

        function formatSimpleDate(dateString) {
            const options = { day: '2-digit', month: 'long', year: 'numeric' };
            return new window.Date(dateString).toLocaleDateString('id-ID', options);
        }

        // Fetch Data Nota via AJAX
        $.ajax({
            url: `/api/pembeli/nota?order_number=${orderNumber}`,
            type: "GET",
            success: function(res) {
                if(res.status === 'success') {
                    const order = res.data.order;
                    const company = res.data.company;

                    // Header Toko
                    $('#toko-nama').text(company?.company_name || 'RAYA KITCHEN');
                    $('#toko-desc').text(company?.short_description || 'Bakery & Pastry Fresh Everyday');
                    $('#toko-kontak').text(`${company?.address || 'Alamat Belum Diatur'} | WA: +${company?.whatsapp_number || '-'}`);

                    // Info Transaksi
                    $('#info-no-trx').text('#' + order.order_number);
                    $('#info-tgl-trx').text(formatDate(order.created_at));

                    let jadwalDate = formatSimpleDate(order.delivery_date);
                    let jadwalTime = order.delivery_time.substring(0, 5);
                    $('#info-jadwal').text(`${jadwalDate} - ${jadwalTime} WIB`);

                    $('#info-penerima-nama').text(order.recipient_name);
                    $('#info-penerima-wa').text(order.recipient_whatsapp);
                    $('#info-alamat').text(order.shipping_address);

                    // Table Items
                    let itemsHtml = '';
                    order.items.forEach(item => {
                        let productName = item.product_variant?.variant_name || 'Produk';
                        let categoryName = item.product_variant?.product?.name || 'Kategori';
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
                    $('#summary-total-barang').text(formatRupiah(order.total_amount));

                    let remainingAmount = parseFloat(order.remaining_amount);
                    let watermarkHtml = '';
                    let bottomHtml = '';

                    // Watermark & Subtotal Logic
                    if (order.status === 'dibatalkan') {
                        watermarkHtml = 'DIBATALKAN';
                        $('#watermark-status').addClass('border-red-500 text-red-600 bg-red-50').text(watermarkHtml);
                        $('#summary-label').text('Total Dibatalkan');
                        $('#summary-value').text(formatRupiah(order.total_amount)).removeClass('text-purple-700 text-red-600').addClass('text-gray-500');

                        // Bottom Section: Dibatalkan
                        bottomHtml += `
                            <div class="p-5 bg-red-50 rounded-2xl border border-red-100 mb-4">
                                <div class="flex gap-3 items-start mb-3">
                                    <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-red-800 text-sm mb-1">Pesanan Dibatalkan</p>
                                        <p class="text-xs text-red-600 font-medium">Alasan: ${order.cancellation_reason}</p>
                                    </div>
                                </div>
                        `;

                        if (order.refund_account_number) {
                            bottomHtml += `<div class="mt-4 pt-4 border-t border-red-100"><p class="text-xs font-bold text-red-800 mb-2">Status Pengembalian Dana (Refund)</p>`;
                            if (order.refund_proof) {
                                let refundUrl = order.refund_proof.startsWith('http') ? order.refund_proof : '/storage/bukti_refund/' + order.refund_proof.replace(/^\//, '');
                                $('#zoom-refund-img').attr('src', refundUrl);

                                bottomHtml += `
                                    <div class="bg-white p-3 rounded-xl border border-red-100">
                                        <span class="text-xs font-bold text-green-600 block mb-3">✓ Dana berhasil dikembalikan oleh Admin</span>
                                        <div class="rounded-lg overflow-hidden border border-gray-100 mb-3 bg-gray-50 flex justify-center p-2 cursor-pointer hover:scale-[1.02] transition-transform relative group" onclick="openRefundZoomModal()">
                                            <img src="${refundUrl}" alt="Bukti Refund" class="max-h-48 w-auto object-contain rounded-md shadow-sm">
                                            <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 rounded-lg flex flex-col items-center justify-center transition-opacity">
                                                <div class="bg-white p-2 rounded-full shadow-md text-red-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="openRefundZoomModal()" class="w-full text-xs font-bold text-red-600 hover:text-red-700 underline block text-center">Lihat Bukti Transfer Ukuran Penuh</button>
                                    </div>
                                `;
                            } else {
                                bottomHtml += `
                                    <div class="flex items-center gap-2 text-xs font-medium text-amber-600 bg-amber-50 p-3 rounded-xl border border-amber-100">
                                        <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Refund sedang diproses admin ke rekening (${order.refund_account_number}). Maksimal 1x24 jam.
                                    </div>
                                `;
                            }
                            bottomHtml += `</div>`;
                        }
                        bottomHtml += `</div>`;
                        $('#bottom-section').html(bottomHtml).removeClass('hidden');

                    } else if (remainingAmount === 0) {
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

                        // Bottom Section: Belum Lunas
                        if (order.transaction?.payment_method === 'cod') {
                            $('#bottom-section').html(`
                                <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 mb-4 flex gap-3 items-start">
                                    <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-amber-800 text-sm mb-1">Tagihan Belum Lunas (COD)</p>
                                        <p class="text-xs text-amber-700 font-medium">Pesananmu baru dibayar DP. Silakan bayar sisa tagihan pesananmu ketika barang sudah sampai ya.</p>
                                    </div>
                                </div>
                            `).removeClass('hidden');
                        } else {
                            $('#bottom-section').html(`
                                <div class="p-4 bg-red-50 rounded-2xl border border-red-100 mb-4 flex gap-3 items-start">
                                    <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <p class="font-bold text-red-800 text-sm mb-1">Tagihan Belum Lunas</p>
                                        <p class="text-xs text-red-600 font-medium">Pesananmu baru dibayar DP. Silakan lunasi sisa tagihan sebelum pesanan dapat diambil/dikirim.</p>
                                    </div>
                                </div>
                                <button type="button" id="btn-lunasi" onclick="lunasiTagihan('${order.order_number}')" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl text-center text-sm transition shadow-[0_4px_10px_rgba(239,68,68,0.3)]">
                                    Bayar Sisa Tagihan Sekarang
                                </button>
                            `).removeClass('hidden');
                        }
                    }

                    // Show content
                    $('#loading-nota').addClass('hidden');
                    $('#nota-card').removeClass('hidden');
                }
            },
            error: function() {
                alert('Gagal mengambil data nota cuy. Pastikan nomor order benar.');
                window.location.href = '/riwayat-pesanan';
            }
        });
    });
</script>
@endpush
