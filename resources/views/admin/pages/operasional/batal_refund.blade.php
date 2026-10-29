@extends('admin.admin_master')

@section('title', 'Batal & Refund - Admin Raya Kitchen')
@section('header_title', 'Batal & Refund')

@section('css')
<style>
    .order-card { cursor: pointer; transition: all 0.2s ease; border: 2px solid transparent; }
    .order-card:hover { background-color: #F5F2F8; border-color: #E4CCFF; }
    .order-card.active {
        background-color: #F5F2F8; border-color: #9368B7;
        box-shadow: 0 4px 15px rgba(147,104,183,0.15);
    }
    .thin-scroll::-webkit-scrollbar { width: 6px; }
    .thin-scroll::-webkit-scrollbar-track { background: transparent; }
    .thin-scroll::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
    .thin-scroll::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }

    /* File Upload Box */
    .upload-box { border: 2px dashed #CBD5E1; transition: all 0.2s; }
    .upload-box:hover, .upload-box.dragover { border-color: #9368B7; background-color: #FAF5FF; }
</style>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-140px)] min-h-[650px]">

    <!-- LEFT PANEL: LIST REFUND (Span 4) -->
    <div id="left-panel" class="lg:col-span-5 xl:col-span-4 bg-white rounded-[2rem] shadow-sm border border-admin-border flex flex-col overflow-hidden h-full">

        <!-- Search & Filter Area -->
        <div class="p-5 border-b border-admin-border bg-gray-50/50 shrink-0">
            <div class="relative mb-4">
                <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted"></i>
                <input type="text" id="search-refund" placeholder="Cari No. Pesanan atau Nama..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-admin-border rounded-xl text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover shadow-inner transition-shadow">
            </div>

            <div class="flex gap-2" id="filter-chips">
                <button class="chip-filter active flex-1 px-3 py-2 bg-amber-500 text-white rounded-lg text-xs font-black shadow-sm transition-colors" data-status="pending">
                    Belum Refund (<span id="count-pending">0</span>)
                </button>
                <button class="chip-filter flex-1 px-3 py-2 bg-white border border-admin-border text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs font-black transition-colors" data-status="refunded">
                    Selesai (<span id="count-refunded">0</span>)
                </button>
            </div>
        </div>

        <!-- List Scrollable -->
        <div class="flex-1 overflow-y-auto thin-scroll p-3 space-y-2 relative" id="refund-list"></div>
    </div>

    <!-- RIGHT PANEL: DETAIL REFUND (Span 8) -->
    <div id="right-panel" class="hidden lg:flex lg:col-span-7 xl:col-span-8 bg-white rounded-[2rem] shadow-sm border border-admin-border flex-col relative overflow-hidden h-full">

        <!-- Empty State -->
        <div id="detail-empty" class="absolute inset-0 bg-white z-20 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 bg-admin-bg rounded-3xl flex items-center justify-center mb-5 border border-admin-border shadow-inner">
                <i class="ph ph-bank text-4xl text-admin-textMuted/50"></i>
            </div>
            <h3 class="text-xl font-black text-admin-textMain mb-2">Pusat Resolusi Refund</h3>
            <p class="text-sm font-medium text-admin-textMuted max-w-sm">Pilih pesanan di sebelah kiri untuk melihat rincian rekening pelanggan dan mengunggah bukti transfer pengembalian dana.</p>
        </div>

        <!-- Detail Konten -->
        <div id="detail-content" class="hidden flex-col h-full z-10 relative">

            <!-- Header -->
            <div class="p-4 lg:p-6 border-b border-admin-border flex justify-between items-center bg-white shrink-0">
                <div class="flex items-center gap-3">
                    <button id="btn-back-mobile" class="lg:hidden w-10 h-10 flex items-center justify-center bg-gray-100 text-admin-textMain rounded-xl hover:bg-gray-200 transition-colors">
                        <i class="ph ph-arrow-left text-lg font-bold"></i>
                    </button>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-xl lg:text-2xl font-black text-rose-600 tracking-tight" id="det-order-id">RK-0000</h2>
                            <div id="det-status-badge"></div>
                        </div>
                        <p class="text-[11px] font-bold text-admin-textMuted flex items-center gap-1.5">
                            <i class="ph ph-warning-circle text-rose-500"></i> Alasan: <span id="det-reason" class="text-admin-textMain"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Scrollable Info Area -->
            <div class="flex-1 overflow-y-auto thin-scroll p-4 lg:p-6 bg-gray-50/30">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <!-- Info Pelanggan & Nominal -->
                    <div class="bg-white p-5 rounded-2xl border border-admin-border shadow-sm flex flex-col justify-between">
                        <div>
                            <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Informasi Pembatalan</h4>
                            <p class="font-black text-admin-textMain text-sm mb-1" id="det-customer">Nama Pelanggan</p>
                            <p class="text-xs font-medium text-admin-textMuted mb-4" id="det-phone">08123456789</p>

                            <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                                <p class="text-[10px] font-black text-rose-600 uppercase tracking-wider mb-1">Nominal Harus Dikembalikan</p>
                                <p class="text-xl font-black text-rose-700" id="det-refund-amount">Rp 0</p>
                                <p class="text-[10px] font-bold text-rose-500 mt-1" id="det-paid-info">Dari total bayar (DP/Full)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rekening Tujuan -->
                    <div class="bg-admin-sideBg p-5 rounded-2xl shadow-md flex flex-col justify-center relative overflow-hidden">
                        <!-- Dekorasi background -->
                        <i class="ph ph-credit-card absolute -right-4 -bottom-4 text-7xl text-white opacity-10"></i>

                        <h4 class="text-[11px] font-black text-white/70 uppercase tracking-widest mb-3">Transfer Ke Rekening</h4>
                        <div class="mb-4">
                            <p class="text-xs font-medium text-white/80 mb-1">Bank / E-Wallet</p>
                            <p class="font-black text-white text-lg tracking-wider" id="det-bank-name">BCA</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-white/80 mb-1">Nomor Rekening</p>
                            <div class="flex items-center gap-3">
                                <p class="font-black text-white text-2xl tracking-widest" id="det-account-num">1234567890</p>
                                <button id="btn-copy" class="w-8 h-8 flex items-center justify-center bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors" title="Copy Rekening">
                                    <i class="ph ph-copy text-sm font-bold"></i>
                                </button>
                            </div>
                            <p class="font-bold text-white/90 text-sm mt-2 uppercase" id="det-account-name">A.N Budi Santoso</p>
                        </div>
                    </div>
                </div>

                <!-- Form Upload / Bukti Refund -->
                <div class="bg-white rounded-2xl border border-admin-border shadow-sm overflow-hidden mb-6 p-5">
                    <h4 class="text-[11px] font-black text-admin-textMuted uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Bukti Pengembalian Dana</h4>

                    <!-- Area Upload (Muncul kalau pending) -->
                    <div id="area-upload" class="hidden">
                        <form id="form-refund" class="flex flex-col gap-4">
                            <div class="upload-box relative w-full h-40 rounded-2xl flex flex-col items-center justify-center bg-gray-50 cursor-pointer">
                                <input type="file" id="refund_file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                                <i class="ph ph-upload-simple text-3xl text-admin-sideBg mb-2"></i>
                                <p class="text-sm font-black text-admin-textMain mb-1">Klik atau seret gambar ke sini</p>
                                <p class="text-xs font-medium text-admin-textMuted">Format JPG/PNG (Maks 2MB)</p>
                                <!-- Preview Image -->
                                <img id="preview-image" class="absolute inset-0 w-full h-full object-cover rounded-2xl hidden z-20 pointer-events-none" src="" alt="Preview">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" id="btn-submit-refund" class="px-6 py-3 bg-emerald-500 text-white rounded-xl font-black text-sm hover:bg-emerald-600 transition-colors shadow-sm flex items-center gap-2">
                                    <i class="ph ph-check-circle text-lg"></i> Konfirmasi Selesai Refund
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Area Bukti (Muncul kalau refunded) -->
                    <div id="area-proof" class="hidden">
                        <div class="flex flex-col md:flex-row gap-5 items-start">
                            <a id="link-proof" href="#" target="_blank" class="block w-full md:w-48 h-48 rounded-2xl overflow-hidden border border-gray-200 shadow-sm shrink-0 relative group">
                                <img id="img-proof" src="" alt="Bukti Refund" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="ph ph-magnifying-glass-plus text-white text-2xl"></i>
                                </div>
                            </a>
                            <div class="flex-1 bg-emerald-50 p-4 rounded-xl border border-emerald-100 w-full">
                                <p class="text-emerald-700 font-black flex items-center gap-2 mb-2"><i class="ph ph-check-circle text-xl"></i> Dana Berhasil Dikembalikan</p>
                                <p class="text-xs font-bold text-emerald-600">Pesanan ini sudah ditutup. Tidak ada aksi lanjutan yang diperlukan.</p>
                            </div>
                        </div>
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
        const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

        // --- 1. DATA DUMMY (CUMA STATUS DIBATALKAN & REFUND != NONE) ---
        let refundOrders = [
            {
                order_number: 'RK-2610-088', recipient_name: 'Budi Santoso', recipient_whatsapp: '081234567890',
                dp_amount: 100000, total_amount: 100000, cancellation_reason: 'Pesanan salah pilih varian, minta batal manual.',
                refund_account_number: 'BCA - 8920192831 - A.N Budi Santoso', refund_status: 'pending', refund_proof: null,
                transaction: { payment_status: 'paid_full' } // Kalo paid_full, berarti yg direfund totalnya
            },
            {
                order_number: 'RK-2610-092', recipient_name: 'Siti Aminah', recipient_whatsapp: '085712345678',
                dp_amount: 45000, total_amount: 150000, cancellation_reason: 'Acara mendadak batal, maaf admin.',
                refund_account_number: 'Mandiri - 142000123456 - A.N Siti Aminah', refund_status: 'pending', refund_proof: null,
                transaction: { payment_status: 'paid_dp' } // Kalo paid_dp, yg direfund cuma DP nya
            },
            {
                order_number: 'RK-2610-045', recipient_name: 'Joko Anwar', recipient_whatsapp: '081998877665',
                dp_amount: 250000, total_amount: 250000, cancellation_reason: 'Toko tutup saat pengiriman.',
                refund_account_number: 'GoPay - 081998877665 - A.N Joko', refund_status: 'refunded',
                refund_proof: 'https://images.unsplash.com/photo-1620714223084-8fcacc6dfd8d?w=500&auto=format&fit=crop', // Dummy img bukti transfer
                transaction: { payment_status: 'paid_full' }
            }
        ];

        let activeFilter = 'pending'; // Default tab
        let searchQuery = '';
        let activeOrderNum = null;

        // --- 2. RENDER LIST ---
        function renderList() {
            let html = '';
            let counts = { pending: 0, refunded: 0 };

            let filtered = refundOrders.filter(o => {
                const matchSearch = o.order_number.toLowerCase().includes(searchQuery) || o.recipient_name.toLowerCase().includes(searchQuery);
                const matchStatus = o.refund_status === activeFilter;

                if (matchSearch) {
                    counts[o.refund_status]++;
                }
                return matchSearch && matchStatus;
            });

            if (filtered.length === 0) {
                html = `<div class="p-6 text-center text-admin-textMuted font-bold text-xs">Semua aman! Tidak ada antrean.</div>`;
            } else {
                filtered.forEach(o => {
                    let activeClass = activeOrderNum === o.order_number ? 'active' : '';
                    let badge = o.refund_status === 'pending'
                        ? `<span class="px-2 py-0.5 text-[9px] rounded-lg font-black uppercase tracking-widest bg-amber-100 text-amber-700 border border-amber-200">Pending</span>`
                        : `<span class="px-2 py-0.5 text-[9px] rounded-lg font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Selesai</span>`;

                    // Hitung nominal yg harus dibalikin
                    let nominal = o.transaction.payment_status === 'paid_full' ? o.total_amount : o.dp_amount;

                    html += `
                        <div class="order-card p-3.5 bg-white rounded-2xl ${activeClass}" data-id="${o.order_number}">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-black text-admin-textMain text-sm">${o.order_number}</span>
                                ${badge}
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="font-bold text-admin-textMuted text-xs mb-0.5"><i class="ph ph-user"></i> ${o.recipient_name}</p>
                                </div>
                                <span class="font-black text-rose-600 text-xs">${formatRupiah(nominal)}</span>
                            </div>
                        </div>
                    `;
                });
            }

            $('#refund-list').html(html);

            // Update Counts
            $('#count-pending').text(counts.pending);
            $('#count-refunded').text(counts.refunded);
        }

        // --- 3. TAMPILKAN DETAIL (Kanan) ---
        function showDetail(orderNum) {
            activeOrderNum = orderNum;
            renderList();

            const order = refundOrders.find(o => o.order_number === orderNum);
            if (!order) return;

            if(window.innerWidth < 1024) {
                $('#left-panel').addClass('hidden');
                $('#right-panel').removeClass('hidden').addClass('flex');
            }

            $('#detail-empty').fadeOut(100);
            $('#detail-content').removeClass('hidden').hide().fadeIn(300);

            $('#det-order-id').text(order.order_number);
            $('#det-reason').text(order.cancellation_reason);
            $('#det-customer').text(order.recipient_name);
            $('#det-phone').text(order.recipient_whatsapp);

            // Hitung Refund
            let nominal = order.transaction.payment_status === 'paid_full' ? order.total_amount : order.dp_amount;
            let tipeBayar = order.transaction.payment_status === 'paid_full' ? 'Lunas (Full)' : 'DP 30%';
            $('#det-refund-amount').text(formatRupiah(nominal));
            $('#det-paid-info').text(`Berasal dari pembayaran: ${tipeBayar}`);

            // Parse Bank Account (Contoh format string: "BCA - 123 - A.N Budi")
            let bankInfo = order.refund_account_number.split(' - ');
            $('#det-bank-name').text(bankInfo[0] || '-');
            $('#det-account-num').text(bankInfo[1] || '-');
            $('#det-account-name').text(bankInfo[2] || '-');

            // Logic Area Bawah (Upload vs Bukti)
            if (order.refund_status === 'pending') {
                $('#area-upload').removeClass('hidden');
                $('#area-proof').addClass('hidden');
                // Reset form
                $('#form-refund')[0].reset();
                $('#preview-image').addClass('hidden').attr('src', '');
            } else {
                $('#area-upload').addClass('hidden');
                $('#area-proof').removeClass('hidden');
                $('#img-proof').attr('src', order.refund_proof);
                $('#link-proof').attr('href', order.refund_proof);
            }
        }

        // --- 4. EVENT LISTENERS ---
        $('#btn-back-mobile').on('click', function() {
            $('#right-panel').removeClass('flex').addClass('hidden');
            $('#left-panel').removeClass('hidden');
        });

        $('#search-refund').on('input', function() {
            searchQuery = $(this).val().toLowerCase();
            renderList();
        });

        $('.chip-filter').on('click', function() {
            $('.chip-filter').removeClass('bg-amber-500 bg-emerald-600 text-white border-transparent').addClass('bg-white text-admin-textMuted border-admin-border');

            if($(this).data('status') === 'pending') {
                $(this).removeClass('bg-white text-admin-textMuted border-admin-border').addClass('bg-amber-500 text-white border-transparent');
            } else {
                $(this).removeClass('bg-white text-admin-textMuted border-admin-border').addClass('bg-emerald-600 text-white border-transparent');
            }

            activeFilter = $(this).data('status');
            renderList();
        });

        $(document).on('click', '.order-card', function() {
            showDetail($(this).data('id'));
        });

        // Copy Rekening Button
        $('#btn-copy').on('click', function() {
            const rek = $('#det-account-num').text();
            navigator.clipboard.writeText(rek).then(() => {
                const oriHtml = $(this).html();
                $(this).html('<i class="ph ph-check text-sm font-bold text-emerald-400"></i>');
                setTimeout(() => $(this).html(oriHtml), 2000);
            });
        });

        // Image Preview Handler
        $('#refund_file').on('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-image').attr('src', e.target.result).removeClass('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Form Submit Simulasi
        $('#form-refund').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-submit-refund');
            const oriText = btn.html();

            btn.html('<i class="ph ph-spinner-gap animate-spin"></i> Memproses...').prop('disabled', true);
            $('#global-loader').removeClass('opacity-0 pointer-events-none');
            $('#loader-bar').css('transform', 'scaleX(0.7)');

            setTimeout(() => {
                let idx = refundOrders.findIndex(o => o.order_number === activeOrderNum);
                if (idx > -1) {
                    refundOrders[idx].refund_status = 'refunded';
                    // Ambil src dari preview sbg dummy file
                    refundOrders[idx].refund_proof = $('#preview-image').attr('src');

                    activeFilter = 'refunded'; // Otomatis pindah tab ke Selesai

                    // Trigger klik tab Selesai biar UI ganti warna
                    $('.chip-filter[data-status="refunded"]').click();

                    showDetail(activeOrderNum);
                }

                btn.html(oriText).prop('disabled', false);
                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);

            }, 1000);
        });

        // Init
        renderList();
    });
</script>
@endpush
