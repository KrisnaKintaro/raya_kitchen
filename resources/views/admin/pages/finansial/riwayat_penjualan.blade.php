@extends('admin.admin_master')

@section('title', 'Riwayat Penjualan - Admin Raya Kitchen')
@section('header_title', 'Riwayat Penjualan')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { font-family: inherit; box-shadow: 0 10px 25px -5px rgba(147,104,183,0.2); border: 2px solid #F3E8F0; border-radius: 1.5rem; }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #9368B7; border-color: #9368B7;
    }

    /* Custom Checkbox Style */
    .custom-cb { appearance: none; width: 1.25rem; height: 1.25rem; border: 2px solid #E2E8F0; border-radius: 0.375rem; transition: all 0.2s; cursor: pointer; position: relative; }
    .custom-cb:checked { background-color: #9368B7; border-color: #9368B7; }
    .custom-cb:checked::after { content: ''; position: absolute; left: 6px; top: 2px; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }
</style>
@endsection

@section('content')
    <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border overflow-hidden flex flex-col">

        <!-- Toolbar & Filter -->
        <div class="p-6 border-b border-admin-border flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-admin-bg/30">
            <!-- Search -->
            <div class="flex-1 w-full xl:max-w-md">
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted"></i>
                    <input type="text" id="filter-search" placeholder="Cari no. pesanan atau pelanggan..." class="w-full pl-11 pr-4 py-3 bg-white border border-admin-border rounded-2xl text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover transition-all shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">

                <!-- Filter Rentang Tanggal -->
                <div class="relative flex-1 sm:flex-none">
                    <i class="ph ph-calendar-blank absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted z-10"></i>
                    <input type="text" id="filter-date-range" placeholder="Pilih Rentang Tanggal" class="w-full sm:w-[260px] pl-11 pr-4 py-3 bg-white border border-admin-border rounded-2xl text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover transition-all cursor-pointer shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]" readonly>
                </div>

                <!-- Custom Multi-Select Dropdown Status -->
                <div class="relative flex-1 sm:flex-none min-w-[170px]" id="wrapper-status">
                    <button type="button" id="btn-status" class="flex items-center justify-between w-full bg-white border border-admin-border text-admin-textMain text-sm font-bold rounded-2xl pl-4 pr-3 py-3 focus:outline-none focus:ring-2 focus:ring-admin-sideHover shadow-[inset_0_2px_4px_rgba(0,0,0,0.02)]">
                        <span id="lbl-status" class="truncate">Semua Status</span>
                        <i class="ph ph-caret-down text-admin-textMuted ml-2"></i>
                    </button>
                    <!-- Dropdown List -->
                    <div id="menu-status" class="escape-target absolute right-0 sm:left-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-admin-border p-2 hidden opacity-0 translate-y-2 transition-all duration-200 z-50 origin-top">
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" value="paid_full" class="cb-status custom-cb">
                            <span class="text-sm font-bold text-admin-textMain">Lunas</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" value="paid_dp" class="cb-status custom-cb">
                            <span class="text-sm font-bold text-admin-textMain">Bayar DP</span>
                        </label>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" value="unpaid" class="cb-status custom-cb">
                            <span class="text-sm font-bold text-admin-textMain">Belum Bayar</span>
                        </label>
                        <div class="h-px bg-admin-border my-1"></div>
                        <label class="flex items-center gap-3 p-2.5 hover:bg-admin-bg rounded-xl cursor-pointer transition-colors">
                            <input type="checkbox" value="dibatalkan" class="cb-status custom-cb">
                            <span class="text-sm font-bold text-rose-500">Dibatalkan</span>
                        </label>
                    </div>
                </div>

                <button id="btn-export" class="flex-1 sm:flex-none flex justify-center items-center gap-2 bg-admin-sideBg hover:bg-admin-sideHover text-white px-6 py-3 rounded-2xl font-black text-sm transition-all shadow-[0_4px_10px_rgba(147,104,183,0.2)] hover:-translate-y-0.5 active:translate-y-0 focus-visible:outline-none">
                    <i class="ph ph-file-csv text-xl"></i>
                    Export
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto hide-scrollbar w-full">
            <table class="w-full text-sm text-left">
                <thead class="text-[11px] text-admin-textMuted uppercase bg-admin-bg/50 border-b border-admin-border">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em]">No. Pesanan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em]">Waktu Checkout</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em]">Pelanggan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em] text-center">Metode</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em] text-right">Total Tagihan</th>
                        <th scope="col" class="px-6 py-4 font-black tracking-[0.1em] text-center">Status Bayar</th>
                    </tr>
                </thead>
                <tbody id="table-body" class="divide-y divide-admin-border">
                    <!-- Data AJAX masuk ke sini -->
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="hidden flex-col items-center justify-center py-20 px-4 text-center">
            <div class="w-20 h-20 bg-admin-bg rounded-3xl flex items-center justify-center mb-5 border border-admin-border shadow-inner">
                <i class="ph ph-receipt-x text-4xl text-admin-textMuted/50"></i>
            </div>
            <h3 class="text-xl font-black text-admin-textMain mb-1">Tidak Ada Transaksi</h3>
            <p class="text-sm font-bold text-admin-textMuted">Ubah filter status atau tanggal untuk menemukan data.</p>
        </div>

        <!-- Pagination Bar -->
        <div class="p-5 border-t border-admin-border flex items-center justify-between bg-admin-bg/30">
            <span class="text-sm font-bold text-admin-textMuted" id="pagination-info">Memuat data...</span>
            <div class="flex items-center gap-2">
                <button class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-admin-border bg-white text-admin-textMuted hover:border-admin-sideBg hover:text-admin-sideBg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ph ph-caret-left font-bold text-lg"></i>
                </button>
                <button class="w-10 h-10 flex items-center justify-center rounded-xl border-2 border-admin-border bg-white text-admin-textMuted hover:border-admin-sideBg hover:text-admin-sideBg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="ph ph-caret-right font-bold text-lg"></i>
                </button>
            </div>
        </div>

    </div>
@endsection

@push('script')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<script>
    $(document).ready(function() {

        const tbody = $('#table-body');
        const emptyState = $('#empty-state');
        const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

        // --- DUMMY DATA ---
        const dummyData = [
            { order_number: 'RK-20261025-1001', created_at: '2026-10-25 08:15:00', recipient_name: 'Budi Santoso', payment_method: 'transfer', payment_status: 'paid_full', total_amount: 150000, order_status: 'selesai' },
            { order_number: 'RK-20261024-1002', created_at: '2026-10-24 14:30:00', recipient_name: 'Siti Aminah', payment_method: 'transfer', payment_status: 'paid_dp', total_amount: 250000, order_status: 'siap_diambil' },
            { order_number: 'RK-20261023-1003', created_at: '2026-10-23 09:00:00', recipient_name: 'Andi Wijaya', payment_method: 'cod', payment_status: 'paid_dp', total_amount: 75000, order_status: 'diproses' },
            { order_number: 'RK-20260915-1004', created_at: '2026-09-15 16:45:00', recipient_name: 'Lestari', payment_method: 'transfer', payment_status: 'failed', total_amount: 320000, order_status: 'dibatalkan' },
            { order_number: 'RK-20260910-1005', created_at: '2026-09-10 11:20:00', recipient_name: 'Joko Anwar', payment_method: 'transfer', payment_status: 'unpaid', total_amount: 125000, order_status: 'pending' },
        ];

        let filteredData = [...dummyData];
        let dateRange = { start: null, end: null };
        let selectedStatuses = [];

        // --- CEK URL PARAMETER (Untuk auto-apply filter dari Dasbor) ---
        const urlParams = new URLSearchParams(window.location.search);
        const urlStart = urlParams.get('start');
        const urlEnd = urlParams.get('end');
        const urlStatus = urlParams.get('status');

        let defaultDates = [];
        if (urlStart) {
            defaultDates.push(urlStart);
            if (urlEnd) defaultDates.push(urlEnd);
        }

        if (urlStatus) {
            const statArray = urlStatus.split(',');
            $('.cb-status').each(function() {
                if (statArray.includes($(this).val())) {
                    $(this).prop('checked', true);
                }
            });
            updateStatusLabel();
        }

        // --- INISIALISASI FLATPICKR ---
        flatpickr("#filter-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            locale: "id",
            defaultDate: defaultDates,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    dateRange.start = selectedDates[0];
                    dateRange.end = selectedDates[1];
                    dateRange.end.setHours(23, 59, 59, 999);
                    applyFilters();
                } else if (selectedDates.length === 0) {
                    dateRange = { start: null, end: null };
                    applyFilters();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    dateRange.start = selectedDates[0];
                    dateRange.end = selectedDates[1];
                    dateRange.end.setHours(23, 59, 59, 999);
                }
            }
        });

        // --- LOGIC MULTI-SELECT DROPDOWN STATUS ---
        $('#btn-status').on('click', function(e) {
            e.stopPropagation();
            $('.escape-target').not('#menu-status').addClass('hidden opacity-0 translate-y-2'); // Tutup yg lain
            $('#menu-status').toggleClass('hidden opacity-0 translate-y-2');
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#wrapper-status').length) {
                $('#menu-status').addClass('hidden opacity-0 translate-y-2');
            }
        });

        $('.cb-status').on('change', function() {
            updateStatusLabel();
            applyFilters();
        });

        function updateStatusLabel() {
            selectedStatuses = [];
            $('.cb-status:checked').each(function() {
                selectedStatuses.push($(this).val());
            });

            if (selectedStatuses.length === 0) {
                $('#lbl-status').text('Semua Status');
            } else if (selectedStatuses.length === 1) {
                let lbl = $('.cb-status:checked').next('span').text();
                $('#lbl-status').text(lbl);
            } else {
                $('#lbl-status').text(`${selectedStatuses.length} Status Dipilih`);
            }
        }

        // --- ENGINE FILTER LOKAL ---
        function applyFilters() {
            tbody.empty();
            emptyState.addClass('hidden');
            $('#global-loader').removeClass('opacity-0 pointer-events-none');
            $('#loader-bar').css('transform', 'scaleX(0.7)');

            setTimeout(() => {
                const keyword = $('#filter-search').val().toLowerCase();

                filteredData = dummyData.filter(item => {
                    // 1. Filter Search
                    const matchSearch = item.order_number.toLowerCase().includes(keyword) ||
                                        item.recipient_name.toLowerCase().includes(keyword);

                    // 2. Filter Status Multiple (OR Logic)
                    let matchStatus = true;
                    if (selectedStatuses.length > 0) {
                        // Khusus dibatalkan, cek ke order_status, sisanya payment_status
                        const actualStatus = item.order_status === 'dibatalkan' ? 'dibatalkan' : item.payment_status;
                        // Kalau gagal (failed), kita anggap sama dengan unpaid di logika ini
                        const normalizedStatus = actualStatus === 'failed' ? 'unpaid' : actualStatus;

                        matchStatus = selectedStatuses.includes(normalizedStatus);
                    }

                    // 3. Filter Tanggal
                    let matchDate = true;
                    if (dateRange.start && dateRange.end) {
                        const itemDate = new Date(item.created_at);
                        matchDate = itemDate >= dateRange.start && itemDate <= dateRange.end;
                    }

                    return matchSearch && matchStatus && matchDate;
                });

                renderTable();

                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);

            }, 400);
        }

        // --- ENGINE RENDER TABEL ---
        function renderTable() {
            let html = '';

            if (filteredData.length === 0) {
                emptyState.removeClass('hidden');
            } else {
                filteredData.forEach(item => {
                    let badge = '';
                    if (item.order_status === 'dibatalkan') {
                        badge = '<span class="px-3 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[10px] font-black uppercase tracking-wider">Batal</span>';
                    } else if (item.payment_status === 'paid_full') {
                        badge = '<span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-wider">Lunas</span>';
                    } else if (item.payment_status === 'paid_dp') {
                        badge = '<span class="px-3 py-1.5 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg text-[10px] font-black uppercase tracking-wider">DP 30%</span>';
                    } else {
                        badge = '<span class="px-3 py-1.5 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-wider">Unpaid</span>';
                    }

                    const dateObj = new Date(item.created_at);
                    const formattedDate = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                    const method = item.payment_method.toUpperCase();

                    html += `
                        <tr class="hover:bg-admin-bg/50 transition-colors">
                            <td class="px-6 py-4 font-black text-admin-sideBg whitespace-nowrap">${item.order_number}</td>
                            <td class="px-6 py-4 font-bold text-admin-textMuted whitespace-nowrap">${formattedDate} WIB</td>
                            <td class="px-6 py-4 font-bold text-admin-textMain">${item.recipient_name}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-black text-admin-textMuted bg-white border-2 border-admin-border px-2 py-1 rounded-md shadow-sm">${method}</span>
                            </td>
                            <td class="px-6 py-4 font-black text-admin-textMain text-right">${formatRupiah(item.total_amount)}</td>
                            <td class="px-6 py-4 text-center">${badge}</td>
                        </tr>
                    `;
                });
                tbody.html(html);
            }
            $('#pagination-info').text(`Menampilkan ${filteredData.length} transaksi`);
        }

        $('#filter-search').on('keyup', function(e) {
            if (e.key === 'Enter' || $(this).val() === '') applyFilters();
        });

        // Trigger filter di load pertama (jika ada param URL)
        applyFilters();
    });
</script>
@endpush
