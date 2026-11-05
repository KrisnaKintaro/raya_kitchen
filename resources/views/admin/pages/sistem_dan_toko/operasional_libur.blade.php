@extends('admin.admin_master')
@section('header_title', 'Operasional & Libur')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-7xl mx-auto items-start">

    <!-- Pengaturan Operasional -->
    <div class="p-6 md:p-8 bg-admin-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(147,104,183,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_15px_40px_rgba(147,104,183,0.15)] border border-white">
        <h2 class="text-2xl font-black text-admin-textMain mb-6 flex items-center gap-3 drop-shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-[inset_0_-2px_4px_rgba(147,104,183,0.2),0_4px_8px_rgba(147,104,183,0.2)]">
                <i class="ph ph-gear-six text-2xl text-admin-sideBg"></i>
            </div>
            Pengaturan Operasional
        </h2>

        <form id="form-operasional" class="space-y-5">
            @csrf
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-2">Batas Maksimal Roti Per Hari (Qty)</label>
                <input type="number" name="max_qty_per_day" id="max_qty_per_day" required class="w-full p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg/50 text-sm font-bold text-admin-textMain transition-all appearance-none">
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-2">Batas Antrean Kirim Barang (Per Jam)</label>
                <input type="number" name="max_orders_per_hour" id="max_orders_per_hour" required class="w-full p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg/50 text-sm font-bold text-admin-textMain transition-all appearance-none">
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-2">Jam Buka</label>
                    <input type="time" name="operational_time_start" id="operational_time_start" required class="w-full p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg/50 text-sm font-bold text-admin-textMain transition-all appearance-none">
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-2">Jam Tutup</label>
                    <input type="time" name="operational_time_end" id="operational_time_end" required class="w-full p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg/50 text-sm font-bold text-admin-textMain transition-all appearance-none">
                </div>
            </div>

            <hr class="border-white/50 my-2 shadow-[0_1px_0_rgba(147,104,183,0.1)]">

            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-1.5 ml-2">Batas Jarak Gratis Ongkir (KM)</label>
                <div class="relative">
                    <input type="number" step="0.1" name="free_delivery_max_distance" id="free_delivery_max_distance" required class="w-full p-3.5 pr-12 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-admin-sideBg/50 text-sm font-bold text-admin-textMain transition-all appearance-none">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-admin-textMuted font-black text-xs pointer-events-none">KM</span>
                </div>
            </div>

            <!-- UX UI Chips Kurir -->
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-admin-textMuted mb-2 ml-2">Kurir RajaOngkir yang Aktif</label>
                <!-- Input hidden penyimpan data buat disubmit -->
                <input type="hidden" name="active_couriers" id="active_couriers_input">

                <div class="flex flex-wrap gap-2.5" id="courier-container">
                    @php
                        $availableCouriers = ['jne', 'sicepat', 'ide', 'sap', 'jnt', 'ninja', 'tiki', 'lion', 'anteraja', 'pos', 'ncs', 'rex', 'rpx', 'sentral', 'star', 'wahana', 'spx'];
                    @endphp
                    @foreach($availableCouriers as $c)
                        <button type="button" data-courier="{{ $c }}" class="courier-chip px-4 py-2.5 rounded-[1rem] text-[11px] font-black uppercase tracking-wide transition-all duration-300 ease-out select-none flex items-center justify-center min-w-[70px]">
                            {{ $c }}
                        </button>
                    @endforeach
                </div>
                <p class="text-[10px] text-admin-textMuted font-semibold ml-2 mt-2.5">Klik nama kurir untuk mengaktifkan/menonaktifkan opsi ekspedisi.</p>
            </div>

            <button type="submit" id="btn-submit-op" class="w-full mt-5 py-4 bg-admin-sideBg text-white font-black text-sm rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_8px_20px_rgba(147,104,183,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] transition-all flex justify-center items-center gap-2">
                <i class="ph ph-floppy-disk text-lg"></i> Simpan Pengaturan
            </button>
        </form>
    </div>

    <!-- Manajemen Hari Libur -->
    <div class="p-6 md:p-8 bg-admin-bg rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(147,104,183,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_15px_40px_rgba(147,104,183,0.15)] border border-white">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b-2 border-white">
            <h2 class="text-2xl font-black text-admin-textMain flex items-center gap-3 drop-shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-[inset_0_-2px_4px_rgba(244,63,94,0.2),0_4px_8px_rgba(244,63,94,0.2)]">
                    <i class="ph ph-calendar-x text-2xl text-rose-500"></i>
                </div>
                Kalender Libur
            </h2>
            <button type="button" id="btn-generate-libur" class="w-full sm:w-auto px-4 py-2.5 bg-blue-500 text-white font-bold rounded-2xl shadow-[inset_0_-2px_4px_rgba(0,0,0,0.1),0_4px_10px_rgba(59,130,246,0.3)] hover:-translate-y-1 active:translate-y-0 text-xs flex items-center justify-center gap-2 transition-all">
                <i class="ph ph-magic-wand text-base"></i> Auto-Generate
            </button>
        </div>

        <!-- Form Libur -->
        <form id="form-libur" class="flex flex-col xl:flex-row gap-3 mb-6">
            @csrf
            <input type="date" name="holiday_date" id="holiday_date" required class="w-full xl:flex-1 p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-rose-400 text-sm font-bold text-admin-textMain transition-all cursor-pointer">

            <input type="text" name="description" id="holiday_desc" placeholder="Keterangan Libur..." required class="w-full xl:flex-[1.5] p-3.5 border border-transparent rounded-2xl bg-white shadow-[inset_0_2px_4px_rgba(147,104,183,0.1),inset_0_-2px_4px_rgba(255,255,255,0.8)] focus:outline-none focus:ring-2 focus:ring-rose-400 text-sm font-bold text-admin-textMain transition-all">

            <button type="submit" class="w-full xl:w-auto px-6 py-3.5 bg-rose-500 text-white font-black rounded-2xl shadow-[inset_0_-4px_4px_rgba(0,0,0,0.15),inset_0_4px_4px_rgba(255,255,255,0.4),0_6px_15px_rgba(244,63,94,0.4)] hover:-translate-y-1 active:translate-y-0.5 active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)] text-sm whitespace-nowrap transition-all flex items-center justify-center gap-2">
                <i class="ph ph-plus-circle text-lg"></i> Tambah Libur
            </button>
        </form>

        <!-- Tabel Area -->
        <div class="rounded-3xl max-h-[320px] overflow-y-auto hide-scrollbar relative bg-white shadow-[inset_0_2px_6px_rgba(147,104,183,0.15)] border-2 border-white">
            <table class="w-full text-sm text-left">
                <thead class="text-admin-textMuted uppercase font-black text-[10px] tracking-wider sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b-2 border-admin-bg shadow-sm">
                    <tr>
                        <th class="px-5 py-4 pl-6">Tanggal</th>
                        <th class="px-5 py-4">Keterangan</th>
                        <th class="px-5 py-4 text-right pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-libur" class="divide-y divide-gray-50/50">
                    <!-- Data libur dimuat via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
$(document).ready(function() {
    // --- SETUP TOAST ALERTS ---
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#fff',
        color: '#2D2A32',
        iconColor: '#9368B7',
        customClass: { popup: 'rounded-[1rem] shadow-xl border border-admin-accent/50 p-2' },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // --- LOGIKA CHIPS KURIR ---
    let activeCouriersArray = [];

    // Fungsi Render Tampilan Chips
    function updateCourierUI() {
        $('.courier-chip').each(function() {
            let courier = $(this).data('courier');
            if (activeCouriersArray.includes(courier)) {
                // Style Aktif (Mendelep ke dalam + Warna Primer)
                $(this).removeClass('bg-white text-gray-400 shadow-[inset_0_-2px_4px_rgba(147,104,183,0.1),0_2px_5px_rgba(147,104,183,0.1)] border-transparent hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(147,104,183,0.15)]')
                       .addClass('bg-admin-sideBg text-white shadow-[inset_0_2px_6px_rgba(0,0,0,0.3)] border-admin-sideHover');
            } else {
                // Style Tidak Aktif (Timbul keluar + Putih)
                $(this).addClass('bg-white text-gray-400 shadow-[inset_0_-2px_4px_rgba(147,104,183,0.1),0_2px_5px_rgba(147,104,183,0.1)] border-transparent hover:-translate-y-0.5 hover:shadow-[0_4px_8px_rgba(147,104,183,0.15)]')
                       .removeClass('bg-admin-sideBg text-white shadow-[inset_0_2px_6px_rgba(0,0,0,0.3)] border-admin-sideHover');
            }
        });
        // Update hidden input buat dikirim via form submit
        $('#active_couriers_input').val(activeCouriersArray.join(','));
    }

    // Toggle saat chip diklik
    $('.courier-chip').click(function() {
        let courier = $(this).data('courier');
        if (activeCouriersArray.includes(courier)) {
            // Hapus dari array jika sudah ada
            activeCouriersArray = activeCouriersArray.filter(c => c !== courier);
        } else {
            // Tambahkan ke array jika belum ada
            activeCouriersArray.push(courier);
        }
        updateCourierUI();
    });

    // --- 1. LOAD OPERASIONAL ---
    $.get('/api/admin/operasional', function(res) {
        if(res.status === 'success' && res.data) {
            $('#max_qty_per_day').val(res.data.max_qty_per_day);
            $('#max_orders_per_hour').val(res.data.max_orders_per_hour);
            $('#operational_time_start').val(res.data.operational_time_start);
            $('#operational_time_end').val(res.data.operational_time_end);
            $('#free_delivery_max_distance').val(res.data.free_delivery_max_distance);

            // Inisialisasi Chips Kurir dari DB
            let loadedCouriers = res.data.active_couriers || 'jne,sicepat,jnt';
            activeCouriersArray = loadedCouriers.split(',').map(s => s.trim().toLowerCase()).filter(s => s);
            updateCourierUI();
        }
    });

    // --- 2. SUBMIT PENGATURAN OPERASIONAL ---
    $('#form-operasional').submit(function(e) {
        e.preventDefault();

        let btn = $('#btn-submit-op');
        let ogHtml = btn.html();
        btn.html('<i class="ph ph-spinner animate-spin text-lg"></i> Menyimpan...').prop('disabled', true);

        $.post('/api/admin/operasional', $(this).serialize(), function(res) {
            Toast.fire({ icon: 'success', title: res.message });
            btn.html(ogHtml).prop('disabled', false);
        }).fail(function() {
            Toast.fire({ icon: 'error', title: 'Gagal menyimpan pengaturan.' });
            btn.html(ogHtml).prop('disabled', false);
        });
    });

    // --- 3. LOAD DATA LIBUR ---
    function loadLibur() {
        $.get('/api/admin/libur', function(res) {
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="3" class="text-center py-10 text-admin-textMuted font-bold bg-white/50">Belum ada hari libur tersimpan.</td></tr>';
            } else {
                res.data.forEach(item => {
                    html += `
                    <tr class="group hover:bg-admin-bg/60 transition-colors bg-transparent">
                        <td class="px-5 py-4 pl-6 font-black text-admin-textMain whitespace-nowrap">${item.holiday_date}</td>
                        <td class="px-5 py-4 font-bold text-admin-textSecondary">${item.description}</td>
                        <td class="px-5 py-4 pr-6 text-right">
                            <button onclick="hapusLibur(${item.id})" class="w-9 h-9 inline-flex items-center justify-center bg-white border border-gray-100 shadow-sm text-gray-400 rounded-xl hover:bg-rose-500 hover:text-white hover:border-transparent hover:shadow-[0_4px_10px_rgba(244,63,94,0.3)] transition-all font-bold">
                                <i class="ph ph-trash text-lg"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            $('#table-libur').html(html);
        });
    }

    loadLibur();

    // --- 4. SUBMIT TAMBAH LIBUR ---
    $('#form-libur').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/admin/libur',
            type: 'POST',
            data: $(this).serialize(),
            success: function() {
                $('#holiday_date').val('');
                $('#holiday_desc').val('');
                loadLibur();
                Toast.fire({ icon: 'success', title: 'Libur berhasil ditambahkan!' });
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Waduh, Gagal!',
                    text: xhr.responseJSON?.message || 'Gagal menambahkan libur',
                    icon: 'error',
                    confirmButtonColor: '#9368B7',
                    customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold shadow-md' }
                });
            }
        });
    });

    // --- 5. FUNGSI HAPUS LIBUR ---
    window.hapusLibur = function(id) {
        Swal.fire({
            title: "Hapus Tanggal Libur?",
            text: "Tanggal ini akan dihapus dari kalender toko.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f43f5e",
            cancelButtonColor: "#f3f4f6",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "<span class='text-gray-600'>Batal</span>",
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl font-bold shadow-[0_4px_10px_rgba(244,63,94,0.3)] border-0',
                cancelButton: 'rounded-xl font-bold border-0 shadow-sm hover:bg-gray-200'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/admin/libur/' + id,
                    type: 'DELETE',
                    data: { _token: $('input[name="_token"]').val() },
                    success: function() {
                        Toast.fire({ icon: 'success', title: 'Libur dihapus.' });
                        loadLibur();
                    }
                });
            }
        });
    };

    // --- 6. AUTO-GENERATE LIBUR ---
    $('#btn-generate-libur').click(function() {
        Swal.fire({
            title: "Generate Kalender?",
            html: "Sistem akan mendeteksi dan memasukkan seluruh hari <b>Sabtu, Minggu, dan Libur Nasional</b> ke dalam kalender. Proses ini memakan waktu beberapa detik.",
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3b82f6",
            cancelButtonColor: "#f3f4f6",
            confirmButtonText: "Ya, Generate",
            cancelButtonText: "<span class='text-gray-600'>Batal</span>",
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl font-bold shadow-[0_4px_10px_rgba(59,130,246,0.3)] border-0',
                cancelButton: 'rounded-xl font-bold border-0 shadow-sm hover:bg-gray-200'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let btn = $('#btn-generate-libur');
                let ogHtml = btn.html();
                btn.html('<i class="ph ph-spinner animate-spin text-base"></i> Loading...').prop('disabled', true);

                $.ajax({
                    url: '/api/admin/libur/generate',
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        year: new Date().getFullYear()
                    },
                    success: function(res) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#9368B7',
                            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold shadow-md' }
                        });
                        loadLibur();
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Gagal meng-generate hari libur otomatis.',
                            icon: 'error',
                            confirmButtonColor: '#9368B7',
                            customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl font-bold shadow-md' }
                        });
                    },
                    complete: function() {
                        btn.html(ogHtml).prop('disabled', false);
                    }
                });
            }
        });
    });
});
</script>
@endpush
