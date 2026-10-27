@extends('admin.admin_master')

@section('title', 'Ringkasan Finansial - Admin Raya Kitchen')
@section('header_title', 'Ringkasan Finansial')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar { font-family: inherit; box-shadow: 0 10px 25px -5px rgba(147,104,183,0.2); border: 2px solid #F3E8F0; border-radius: 1.5rem; }
    .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected.inRange, .flatpickr-day.startRange.inRange, .flatpickr-day.endRange.inRange, .flatpickr-day.selected:focus, .flatpickr-day.startRange:focus, .flatpickr-day.endRange:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.endRange.nextMonthDay {
        background: #9368B7; border-color: #9368B7;
    }
</style>
@endsection

@section('content')
    <!-- Filter Rentang Tanggal Dasbor -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-black text-admin-textMain">Performa Keuangan</h2>
        <div class="relative w-[280px]">
            <i class="ph ph-calendar-blank absolute left-4 top-1/2 -translate-y-1/2 text-lg text-admin-textMuted z-10"></i>
            <input type="text" id="filter-date-dashboard" placeholder="Pilih Rentang Tanggal" class="w-full pl-11 pr-4 py-3 bg-white border border-admin-border rounded-2xl text-sm font-bold text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover transition-all cursor-pointer shadow-[0_4px_15px_rgba(147,104,183,0.05)]" readonly>
        </div>
    </div>

    <!-- HEADER STATS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <!-- Card: Pendapatan -->
        <div class="bg-white p-6 lg:p-8 rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0 border border-emerald-100 shadow-inner">
                    <i class="ph ph-wallet text-3xl"></i>
                </div>
                <span class="px-3 py-1 bg-admin-bg text-admin-textMuted font-bold text-[10px] uppercase tracking-wider rounded-lg border border-admin-border" id="lbl-pendapatan">Rentang Waktu</span>
            </div>
            <div>
                <p class="text-xs font-black text-admin-textMuted uppercase tracking-widest mb-1 opacity-80">Total Pendapatan</p>
                <h3 class="text-3xl font-black text-admin-textMain tracking-tight" id="stat-pendapatan">Rp 0</h3>
            </div>
        </div>

        <!-- Card: Total Transaksi Sukses -->
        <div class="bg-white p-6 lg:p-8 rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="w-14 h-14 bg-admin-sideBg/10 text-admin-sideBg rounded-2xl flex items-center justify-center shrink-0 border border-admin-sideBg/20 shadow-inner">
                    <i class="ph ph-receipt text-3xl"></i>
                </div>
                <span class="px-3 py-1 bg-admin-bg text-admin-textMuted font-bold text-[10px] uppercase tracking-wider rounded-lg border border-admin-border">Rentang Waktu</span>
            </div>
            <div>
                <p class="text-xs font-black text-admin-textMuted uppercase tracking-widest mb-1 opacity-80">Transaksi Berhasil</p>
                <h3 class="text-3xl font-black text-admin-textMain tracking-tight" id="stat-transaksi">0 <span class="text-sm font-bold text-admin-textMuted">Order</span></h3>
            </div>
        </div>

        <!-- Card: Piutang / Gantung -->
        <div class="bg-white p-6 lg:p-8 rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center shrink-0 border border-amber-100 shadow-inner">
                    <i class="ph ph-hand-coins text-3xl"></i>
                </div>
                <!-- URL Awal (Diupdate otomatis via JS saat tanggal berubah) -->
                <a href="/admin/riwayat_penjualan?status=paid_dp,unpaid" id="btn-detail-piutang" class="w-8 h-8 flex items-center justify-center bg-admin-bg text-admin-textMuted hover:text-admin-sideBg hover:bg-admin-accent/20 rounded-xl border border-admin-border transition-colors focus-visible:outline-none" title="Lihat Detail Piutang di Riwayat Penjualan">
                    <i class="ph ph-arrow-right font-bold text-lg"></i>
                </a>
            </div>
            <div>
                <p class="text-xs font-black text-admin-textMuted uppercase tracking-widest mb-1 opacity-80">Tagihan Gantung (DP/Unpaid)</p>
                <h3 class="text-3xl font-black text-admin-textMain tracking-tight" id="stat-piutang">Rp 0</h3>
            </div>
        </div>
    </div>

    <!-- AREA CHART & LAPORAN -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Grafik Utama (Span 2) -->
        <div class="xl:col-span-2 bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border p-6 lg:p-8 flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-admin-textMain">Grafik Status Pesanan</h2>
            </div>

            <div class="flex-1 w-full min-h-[300px] relative">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <!-- Quick Report Panel -->
        <div class="bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border p-6 lg:p-8 flex flex-col justify-between">
            <div>
                <div class="w-16 h-16 bg-admin-sideBg/10 text-admin-sideBg rounded-2xl flex items-center justify-center border border-admin-sideBg/20 shadow-inner mb-5">
                    <i class="ph ph-printer text-3xl"></i>
                </div>
                <h2 class="text-xl font-black text-admin-textMain mb-2">Cetak Laporan Dasbor</h2>
                <p class="text-sm font-medium text-admin-textMuted leading-relaxed">Download grafik dan ringkasan performa berdasarkan filter tanggal terpilih.</p>
            </div>

            <div class="mt-8 space-y-3">
                <button class="w-full flex justify-between items-center bg-white border-2 border-admin-border hover:border-admin-sideBg hover:text-admin-sideBg text-admin-textMain px-5 py-4 rounded-2xl font-black text-sm transition-all interactive-element focus-visible:outline-none group shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-file-pdf text-xl text-rose-500 group-hover:text-admin-sideBg transition-colors"></i>
                        Download Laporan PDF
                    </div>
                    <i class="ph ph-download-simple font-bold"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    $(document).ready(function() {
        let financeChartInstance = null;
        let selectedStart = '';
        let selectedEnd = '';

        const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

        // 1. INIT FLATPICKR
        flatpickr("#filter-date-dashboard", {
            mode: "range",
            dateFormat: "Y-m-d",
            locale: "id",
            defaultDate: [new Date(new Date().setDate(new Date().getDate() - 7)), new Date()], // Default 7 hari terakhir
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    selectedStart = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    selectedEnd = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                    updateDashboard();
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    selectedStart = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    selectedEnd = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                    updateDashboard();
                }
            }
        });

        // 2. FUNGSI UPDATE DASHBOARD & LINK
        function updateDashboard() {
            // Update Link Tombol Detail Piutang
            const piutangUrl = `/admin/riwayat_penjualan?status=paid_dp,unpaid&start=${selectedStart}&end=${selectedEnd}`;
            $('#btn-detail-piutang').attr('href', piutangUrl);

            // Simulasi Load AJAX
            $('#global-loader').removeClass('opacity-0 pointer-events-none');
            $('#loader-bar').css('transform', 'scaleX(0.7)');

            setTimeout(() => {
                // Dummy Data Update
                $('#stat-pendapatan').text(formatRupiah(24500000));
                $('#stat-transaksi').html(`128 <span class="text-sm font-bold text-admin-textMuted">Order</span>`);
                $('#stat-piutang').text(formatRupiah(3150000));

                renderChart();

                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);
            }, 600);
        }

        // 3. ENGINE CHART.JS
        function renderChart() {
            const ctx = document.getElementById('financeChart').getContext('2d');

            // Hapus chart lama biar Zero Memory Leak
            if (financeChartInstance) {
                financeChartInstance.destroy();
            }

            // Dummy Labels (Nyesuain rentang)
            const labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

            financeChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Lunas',
                            data: [15, 20, 18, 25, 30, 45, 40],
                            borderColor: '#10B981', // Emerald 500
                            backgroundColor: '#10B981',
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Bayar DP',
                            data: [5, 8, 4, 10, 15, 12, 18],
                            borderColor: '#F59E0B', // Amber 500
                            backgroundColor: '#F59E0B',
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Belum Bayar',
                            data: [2, 1, 3, 2, 4, 5, 2],
                            borderColor: '#64748B', // Slate 500
                            backgroundColor: '#64748B',
                            borderDash: [5, 5], // Garis putus-putus buat unpaid
                            tension: 0.4,
                            borderWidth: 2
                        },
                        {
                            label: 'Dibatalkan',
                            data: [0, 2, 1, 0, 1, 3, 1],
                            borderColor: '#EF4444', // Rose 500
                            backgroundColor: '#EF4444',
                            tension: 0.4,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'Inter', weight: 'bold' } } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#F3E8F0' } },
                        x: { grid: { display: false } }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }
    });
</script>
@endpush
