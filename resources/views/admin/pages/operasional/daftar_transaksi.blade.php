@extends('admin.admin_master')

@section('title', 'Daftar Transaksi - Admin Raya Kitchen')
@section('header_title', 'Daftar Transaksi')

@section('css')
<style>
    /* Styling List Aktif */
    .order-card { cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
    .order-card:hover { background-color: #F5F2F8; border-color: #E4CCFF; }
    .order-card.active {
        background-color: #F5F2F8;
        border-color: #9368B7;
        box-shadow: 0 4px 15px rgba(147,104,183,0.15);
    }

    /* Scrollbar Tipis untuk List Kiri */
    .thin-scroll::-webkit-scrollbar { width: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    .thin-scroll::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }

    /* Custom Scrollbar & Drag-to-Scroll untuk Chips Filter */
    .drag-scroll {
        cursor: grab;
        overflow-x: auto;
        padding-bottom: 8px; /* Memberi ruang untuk scrollbar */
    }
    .drag-scroll:active {
        cursor: grabbing;
    }
    .drag-scroll::-webkit-scrollbar {
        height: 8px; /* Lebih tebal agar mudah diklik/dilihat */
    }
    .drag-scroll::-webkit-scrollbar-track {
        background: #F3E8F0;
        border-radius: 10px;
    }
    .drag-scroll::-webkit-scrollbar-thumb {
        background: #B38CDA; /* Ungu terang mencolok */
        border-radius: 10px;
    }
    .drag-scroll::-webkit-scrollbar-thumb:hover {
        background: #9368B7;
    }
</style>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-140px)] min-h-[650px]">

    <!-- LEFT PANEL: LIST PESANAN (Span 4) -->
    <div id="left-panel" class="lg:col-span-5 xl:col-span-4 bg-white rounded-[2rem] shadow-sm border border-admin-border flex flex-col overflow-hidden h-full">

        <!-- Search & Filter Area (Sticky Top) -->
        <div class="p-5 border-b border-admin-border bg-gray-50/50 shrink-0">
            <!-- Search -->
            <div class="relative mb-4">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted"></i>
                <input type="text" id="search-order" placeholder="Cari No. Pesanan atau Nama..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-admin-border rounded-xl text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover shadow-inner transition-shadow">
            </div>

            <!-- Filter Chips (Draggable & Scrollable) -->
            <div class="flex flex-nowrap gap-2 drag-scroll select-none" id="filter-chips">
                <button class="chip-filter active shrink-0 px-3 py-1.5 bg-admin-sideBg text-white rounded-lg text-xs font-black shadow-sm transition-colors" data-status="all">
                    Semua (<span id="count-all">0</span>)
                </button>
                <button class="chip-filter shrink-0 px-3 py-1.5 bg-white border border-admin-border text-admin-textMuted hover:bg-gray-50 rounded-lg text-xs font-black transition-colors" data-status="pending">
                    Menunggu (<span id="count-pending">0</span>)
                </button>
                <button class="chip-filter shrink-0 px-3 py-1.5 bg-white border border-admin-border text-blue-600 hover:bg-blue-50 rounded-lg text-xs font-black transition-colors" data-status="diproses">
                    Diproses (<span id="count-diproses">0</span>)
                </button>
                <button class="chip-filter shrink-0 px-3 py-1.5 bg-white border border-admin-border text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs font-black transition-colors" data-status="siap_diambil">
                    Siap Diambil (<span id="count-siap">0</span>)
                </button>
                <button class="chip-filter shrink-0 px-3 py-1.5 bg-white border border-admin-border text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-black transition-colors" data-status="selesai">
                    Selesai (<span id="count-selesai">0</span>)
                </button>
                <button class="chip-filter shrink-0 px-3 py-1.5 bg-white border border-admin-border text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-black transition-colors" data-status="dibatalkan">
                    Dibatalkan (<span id="count-batal">0</span>)
                </button>
            </div>
        </div>

        <!-- List Scrollable -->
        <div class="flex-1 overflow-y-auto thin-scroll p-3 space-y-2 relative" id="order-list">
            <!-- Data di-inject via JS -->
        </div>

        <!-- Indikator Lazy Load -->
        <div id="lazy-loader" class="hidden py-3 text-center border-t border-admin-border bg-gray-50 shrink-0">
            <i class="ph ph-spinner-gap animate-spin text-xl text-admin-sideBg inline-block"></i>
            <span class="text-xs font-bold text-admin-textMuted ml-2">Memuat data lama...</span>
        </div>
    </div>

    <!-- RIGHT PANEL: ORDER DETAIL (Span 8) -->
    <div id="right-panel" class="hidden lg:flex lg:col-span-7 xl:col-span-8 bg-white rounded-[2rem] shadow-sm border border-admin-border flex-col relative overflow-hidden h-full">

        <!-- Empty State -->
        <div id="detail-empty" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 bg-admin-bg rounded-3xl flex items-center justify-center mb-5 border border-admin-border shadow-inner">
                <i class="ph ph-receipt text-4xl text-admin-textMuted/50"></i>
            </div>
            <h3 class="text-xl font-black text-admin-textMain mb-2">Detail Transaksi</h3>
            <p class="text-sm font-medium text-admin-textMuted max-w-sm">Klik salah satu transaksi di daftar sebelah kiri untuk melihat rincian item, pelanggan, dan pembayaran.</p>
        </div>

        <!-- Detail Konten (Hidden by default) -->
        <div id="detail-content" class="hidden flex-col h-full z-10 relative">

            <!-- Header Detail -->
            <div class="p-4 lg:p-6 border-b border-admin-border flex justify-between items-center bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <button id="btn-back-mobile" class="lg:hidden w-10 h-10 flex items-center justify-center bg-gray-100 text-admin-textMain rounded-xl hover:bg-gray-200 transition-colors">
                        <i class="ph ph-arrow-left text-lg font-bold"></i>
                    </button>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-xl lg:text-2xl font-black text-admin-textMain tracking-tight" id="det-order-id">RK-0000</h2>
                            <div id="det-status-badge"></div>
                        </div>
                        <p class="text-[11px] font-bold text-admin-textMuted flex items-center gap-1.5">
                            <i class="ph ph-globe"></i> Sumber: <span id="det-source" class="uppercase">WEB</span>
                        </p>
                    </div>
                </div>
                <button class="w-10 h-10 flex items-center justify-center bg-admin-bg border border-admin-border text-admin-sideBg rounded-xl hover:bg-admin-sideBg hover:text-white transition-colors focus-visible:outline-none shadow-sm" title="Cetak Struk">
                    <i class="ph ph-printer text-lg font-bold"></i>
                </button>
            </div>

            <!-- Scrollable Info Area -->
            <div class="flex-1 overflow-y-auto thin-scroll p-4 lg:p-6 bg-gray-50/30">

                <!-- Info Grid (Pelanggan & Pengiriman) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

                    <!-- Pelanggan -->
                    <div class="bg-white p-4 rounded-2xl border border-admin-border shadow-sm">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Data Pelanggan</h4>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-black text-admin-textMain text-sm mb-1" id="det-customer">Nama Pelanggan</p>
                                <p class="text-xs font-medium text-admin-textMuted mb-2" id="det-phone">08123456789</p>
                            </div>
                            <a href="#" id="det-wa-link" target="_blank" class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-500 hover:text-white transition-colors" title="Chat WhatsApp">
                                <i class="ph ph-whatsapp-logo text-lg font-bold"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Info Pengiriman / Waktu -->
                    <div class="bg-white p-4 rounded-2xl border border-admin-border shadow-sm">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Informasi Pengiriman</h4>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <i class="ph ph-map-pin text-admin-textMuted mt-0.5"></i>
                                <p class="text-xs font-medium text-admin-textMain leading-relaxed" id="det-address">Alamat lengkap diketik di sini...</p>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <i class="ph ph-clock text-rose-500 font-bold"></i>
                                <p class="text-xs font-black text-rose-600" id="det-deadline">Tenggat: 2026-10-30 | 15:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rincian Produk -->
                <div class="bg-white rounded-2xl border border-admin-border shadow-sm overflow-hidden mb-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-admin-border">
                        <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest">Rincian Item</h4>
                    </div>
                    <ul class="divide-y divide-gray-100" id="det-items-list">
                        <!-- Item list di-inject via JS -->
                    </ul>
                </div>

                <!-- Ringkasan Pembayaran (Menyesuaikan tabel transactions & orders) -->
                <div class="bg-white p-4 rounded-2xl border border-admin-border shadow-sm w-full md:w-2/3 ml-auto">
                    <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Pembayaran</h4>

                    <div class="flex justify-between items-center mb-2 text-xs">
                        <span class="font-medium text-admin-textMuted">Metode (Source)</span>
                        <span class="font-black text-admin-textMain uppercase" id="det-pay-method">TRANSFER</span>
                    </div>

                    <div class="flex justify-between items-center mb-2 text-xs">
                        <span class="font-medium text-admin-textMuted">Status Transaksi</span>
                        <span class="font-black text-emerald-600 uppercase" id="det-pay-status">PAID FULL</span>
                    </div>

                    <div class="flex justify-between items-center mb-2 text-xs">
                        <span class="font-medium text-admin-textMuted">Terbayar (DP)</span>
                        <span class="font-bold text-admin-textMain" id="det-pay-dp">Rp 0</span>
                    </div>

                    <div class="flex justify-between items-center mb-2 text-xs">
                        <span class="font-medium text-admin-textMuted">Sisa Tagihan</span>
                        <span class="font-bold text-rose-500" id="det-pay-rem">Rp 0</span>
                    </div>

                    <div class="h-px bg-dashed bg-gray-200 my-3"></div>

                    <div class="flex justify-between items-center text-sm">
                        <span class="font-black text-admin-textMuted uppercase">Total Pesanan</span>
                        <span class="font-black text-admin-sideBg text-lg" id="det-total">Rp 0</span>
                    </div>
                </div>

            </div>

            <!-- Sticky Action Footer -->
            <div class="p-4 border-t border-admin-border bg-white flex flex-wrap justify-end gap-3 shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
                <div id="det-action-area" class="w-full flex justify-end gap-3"></div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

        // --- 1. DATA DUMMY (Sesuai Schema Real) ---
        let masterOrders = [
            {
                order_number: 'RK-2610-001', recipient_name: 'Budi Santoso', recipient_whatsapp: '081234567890',
                shipping_address: 'Jl. Merdeka No 45, Surabaya', delivery_date: '2026-10-29', delivery_time: '15:00:00',
                total_amount: 155000, dp_amount: 155000, remaining_amount: 0, status: 'pending', order_source: 'whatsapp',
                transaction: { payment_method: 'transfer', payment_status: 'paid_full' },
                items: [
                    { name: 'Roti Sisir Mentega', quantity: 2, price_at_order: 50000, subtotal: 100000 },
                    { name: 'Roti Tawar Gandum', quantity: 1, price_at_order: 55000, subtotal: 55000 }
                ]
            },
            {
                order_number: 'RK-2610-002', recipient_name: 'Siti Aminah', recipient_whatsapp: '085712345678',
                shipping_address: 'Perumahan Indah Blok C2', delivery_date: '2026-10-30', delivery_time: '10:00:00',
                total_amount: 250000, dp_amount: 75000, remaining_amount: 175000, status: 'diproses', order_source: 'web',
                transaction: { payment_method: 'transfer', payment_status: 'paid_dp' },
                items: [
                    { name: 'Roti Coklat Keju', quantity: 5, price_at_order: 50000, subtotal: 250000 }
                ]
            },
            {
                order_number: 'RK-2610-003', recipient_name: 'Joko Anwar', recipient_whatsapp: '081998877665',
                shipping_address: 'Ambil di Toko', delivery_date: '2026-10-29', delivery_time: '12:00:00',
                total_amount: 75000, dp_amount: 0, remaining_amount: 75000, status: 'siap_diambil', order_source: 'web',
                transaction: { payment_method: 'cod', payment_status: 'unpaid' },
                items: [
                    { name: 'Croissant Coklat', quantity: 3, price_at_order: 25000, subtotal: 75000 }
                ]
            },
            {
                order_number: 'RK-2610-004', recipient_name: 'Andi Wijaya', recipient_whatsapp: '082233445566',
                shipping_address: 'Jl. Mawar 12', delivery_date: '2026-10-27', delivery_time: '11:20:00',
                total_amount: 120000, dp_amount: 120000, remaining_amount: 0, status: 'selesai', order_source: 'whatsapp',
                transaction: { payment_method: 'transfer', payment_status: 'paid_full' },
                items: [
                    { name: 'Kue Tart Mini', quantity: 1, price_at_order: 120000, subtotal: 120000 }
                ]
            },
            {
                order_number: 'RK-2610-005', recipient_name: 'Lestari', recipient_whatsapp: '08111222333',
                shipping_address: 'Kirim by Gojek', delivery_date: '2026-10-28', delivery_time: '09:00:00',
                total_amount: 50000, dp_amount: 0, remaining_amount: 50000, status: 'dibatalkan', order_source: 'web',
                transaction: { payment_method: 'transfer', payment_status: 'failed' },
                items: [
                    { name: 'Donut Gula', quantity: 5, price_at_order: 10000, subtotal: 50000 }
                ]
            }
        ];

        let activeFilter = 'all';
        let searchQuery = '';
        let activeOrderNum = null;

        // --- 2. RENDER LIST & CHIPS ---
        function renderList() {
            let html = '';
            let counts = { all: 0, pending: 0, diproses: 0, siap_diambil: 0, selesai: 0, dibatalkan: 0 };

            let filteredOrders = masterOrders.filter(o => {
                const matchSearch = o.order_number.toLowerCase().includes(searchQuery) || o.recipient_name.toLowerCase().includes(searchQuery);
                const matchStatus = activeFilter === 'all' ? true : o.status === activeFilter;

                if (matchSearch) {
                    counts.all++;
                    if (counts[o.status] !== undefined) counts[o.status]++;
                }

                return matchSearch && matchStatus;
            });

            if (filteredOrders.length === 0) {
                html = `<div class="p-6 text-center text-admin-textMuted font-bold text-xs">Tidak ada data pesanan.</div>`;
            } else {
                filteredOrders.forEach(o => {
                    let activeClass = activeOrderNum === o.order_number ? 'active' : '';
                    let badge = getStatusBadge(o.status, true);
                    // Ambil string jam:menit aja
                    let timeShort = o.delivery_time ? o.delivery_time.substring(0,5) : '';

                    html += `
                        <div class="order-card p-3.5 bg-white rounded-2xl ${activeClass}" data-id="${o.order_number}">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-black text-admin-textMain text-sm">${o.order_number}</span>
                                ${badge}
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="font-bold text-admin-textMuted text-xs mb-0.5"><i class="ph ph-user"></i> ${o.recipient_name}</p>
                                    <p class="font-medium text-gray-400 text-[10px]"><i class="ph ph-clock"></i> ${o.delivery_date} | ${timeShort}</p>
                                </div>
                                <span class="font-black text-admin-sideBg text-xs">${formatRupiah(o.total_amount)}</span>
                            </div>
                        </div>
                    `;
                });
            }

            $('#order-list').html(html);

            // Update Angka di Chips
            $('#count-all').text(counts.all);
            $('#count-pending').text(counts.pending);
            $('#count-diproses').text(counts.diproses);
            $('#count-siap').text(counts.siap_diambil);
            $('#count-selesai').text(counts.selesai);
            $('#count-batal').text(counts.dibatalkan);
        }

        // --- 3. BADGE GENERATOR ---
        function getStatusBadge(status, isSmall = false) {
            let classes = isSmall ? "px-2 py-0.5 text-[9px]" : "px-3 py-1 text-[10px]";
            let base = `${classes} rounded-lg font-black uppercase tracking-widest `;

            switch(status) {
                case 'pending': return `<span class="${base} bg-gray-100 text-gray-600 border border-gray-200">Menunggu</span>`;
                case 'diproses': return `<span class="${base} bg-blue-50 text-blue-600 border border-blue-200">Diproses</span>`;
                case 'siap_diambil': return `<span class="${base} bg-amber-50 text-amber-600 border border-amber-200">Siap Ambil</span>`;
                case 'selesai': return `<span class="${base} bg-emerald-50 text-emerald-600 border border-emerald-200">Selesai</span>`;
                case 'dibatalkan': return `<span class="${base} bg-rose-50 text-rose-600 border border-rose-200">Batal</span>`;
                default: return '';
            }
        }

        function getTransactionBadge(payStatus) {
            if(payStatus === 'paid_full') return 'text-emerald-600';
            if(payStatus === 'paid_dp') return 'text-amber-500';
            if(payStatus === 'unpaid') return 'text-gray-500';
            return 'text-rose-500'; // failed
        }

        // --- 4. TAMPILKAN DETAIL (PANEL KANAN) ---
        function showDetail(orderNum) {
            activeOrderNum = orderNum;
            renderList();

            const order = masterOrders.find(o => o.order_number === orderNum);
            if (!order) return;

            // Mobile Switch Logic
            if(window.innerWidth < 1024) {
                $('#left-panel').addClass('hidden');
                $('#right-panel').removeClass('hidden').addClass('flex');
            }

            $('#editor-empty, #detail-empty').fadeOut(100);
            $('#detail-content').removeClass('hidden').hide().fadeIn(300);

            // Populate Data
            $('#det-order-id').text(order.order_number);
            $('#det-status-badge').html(getStatusBadge(order.status));
            $('#det-source').text(order.order_source);
            $('#det-customer').text(order.recipient_name);
            $('#det-phone').text(order.recipient_whatsapp);
            $('#det-address').text(order.shipping_address || '-');

            let timeShort = order.delivery_time ? order.delivery_time.substring(0,5) : '';
            $('#det-deadline').text(`Tenggat: ${order.delivery_date} | ${timeShort} WIB`);
            $('#det-wa-link').attr('href', `https://wa.me/${order.recipient_whatsapp.replace(/^0/, '62')}`);

            // Populate Keuangan
            $('#det-pay-method').text(order.transaction.payment_method);

            let payColor = getTransactionBadge(order.transaction.payment_status);
            $('#det-pay-status').removeClass().addClass(`font-black uppercase ${payColor}`).text(order.transaction.payment_status.replace('_', ' '));

            $('#det-pay-dp').text(formatRupiah(order.dp_amount));
            $('#det-pay-rem').text(formatRupiah(order.remaining_amount));
            $('#det-total').text(formatRupiah(order.total_amount));

            // Populate Items
            let itemsHtml = '';
            order.items.forEach(i => {
                itemsHtml += `
                    <li class="px-4 py-3 flex justify-between items-center">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-admin-bg text-admin-sideBg rounded-lg flex items-center justify-center font-black text-xs shrink-0">${i.quantity}x</div>
                            <div>
                                <p class="font-bold text-admin-textMain text-sm">${i.name}</p>
                                <p class="text-[10px] font-medium text-admin-textMuted">@ ${formatRupiah(i.price_at_order)}</p>
                            </div>
                        </div>
                        <span class="font-black text-admin-textMain text-xs">${formatRupiah(i.subtotal)}</span>
                    </li>
                `;
            });
            $('#det-items-list').html(itemsHtml);

            // Action Buttons Generator berdasar status
            let actionHtml = '';
            if (order.status === 'pending') {
                actionHtml = `
                    <button class="px-4 py-2 border-2 border-rose-100 text-rose-500 rounded-xl font-bold text-xs hover:bg-rose-50 transition-colors">Batalkan Pesanan</button>
                    <button onclick="changeStatus('${order.order_number}', 'diproses')" class="px-6 py-2 bg-blue-500 text-white rounded-xl font-black text-sm hover:bg-blue-600 transition-colors shadow-sm">Ubah ke Diproses</button>
                `;
            } else if (order.status === 'diproses') {
                actionHtml = `<button onclick="changeStatus('${order.order_number}', 'siap_diambil')" class="px-6 py-2 bg-amber-500 text-white rounded-xl font-black text-sm hover:bg-amber-600 transition-colors shadow-sm">Ubah ke Siap Ambil / Selesai Masak</button>`;
            } else if (order.status === 'siap_diambil') {
                actionHtml = `<button onclick="changeStatus('${order.order_number}', 'selesai')" class="px-6 py-2 bg-emerald-500 text-white rounded-xl font-black text-sm hover:bg-emerald-600 transition-colors shadow-sm">Selesaikan Pesanan (Diserahkan)</button>`;
            } else {
                actionHtml = `<span class="text-xs font-bold text-admin-textMuted py-2 px-4 bg-gray-50 rounded-lg">Tidak ada aksi untuk status ini</span>`;
            }
            $('#det-action-area').html(actionHtml);
        }

        // --- 5. EVENT LISTENERS ---

        // Mobile Back Button
        $('#btn-back-mobile').on('click', function() {
            $('#right-panel').removeClass('flex').addClass('hidden');
            $('#left-panel').removeClass('hidden');
        });

        $('#search-order').on('input', function() {
            searchQuery = $(this).val().toLowerCase();
            renderList();
        });

        $('.chip-filter').on('click', function() {
            $('.chip-filter').removeClass('bg-admin-sideBg text-white border-transparent').addClass('bg-white text-admin-textMuted border-admin-border');

            // Re-apply special colors based on the data-status if needed, but for simplicity we override all to sideBg
            $('.chip-filter[data-status="diproses"]').removeClass('text-admin-textMuted').addClass('text-blue-600');
            $('.chip-filter[data-status="siap_diambil"]').removeClass('text-admin-textMuted').addClass('text-emerald-600');
            $('.chip-filter[data-status="selesai"]').removeClass('text-admin-textMuted').addClass('text-gray-700');
            $('.chip-filter[data-status="dibatalkan"]').removeClass('text-admin-textMuted').addClass('text-rose-600');

            // Set active
            $(this).removeClass('bg-white text-admin-textMuted text-blue-600 text-emerald-600 text-gray-700 text-rose-600 border-admin-border').addClass('bg-admin-sideBg text-white border-transparent');

            activeFilter = $(this).data('status');
            renderList();
        });

        $(document).on('click', '.order-card', function() {
            showDetail($(this).data('id'));
        });

        // Fitur MOUSE DRAG TO SCROLL untuk Chips Filter
        const slider = document.querySelector('.drag-scroll');
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => { isDown = false; });
        slider.addEventListener('mouseup', () => { isDown = false; });
        slider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 1.5; // Scroll-fast multiplier
            slider.scrollLeft = scrollLeft - walk;
        });

        // Fitur Infinite Scroll
        $('#order-list').on('scroll', function() {
            if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 5) {
                if($('#lazy-loader').is(':visible')) return;
                $('#lazy-loader').slideDown(150);
                setTimeout(() => {
                    $('#lazy-loader').slideUp(150);
                }, 1000);
            }
        });

        window.changeStatus = function(orderNum, newStatus) {
            $('#global-loader').removeClass('opacity-0 pointer-events-none');
            $('#loader-bar').css('transform', 'scaleX(0.7)');

            setTimeout(() => {
                let idx = masterOrders.findIndex(o => o.order_number === orderNum);
                if (idx > -1) {
                    masterOrders[idx].status = newStatus;
                    renderList();
                    showDetail(orderNum);
                }

                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);
            }, 500);
        };

        renderList();
    });
</script>
@endpush
