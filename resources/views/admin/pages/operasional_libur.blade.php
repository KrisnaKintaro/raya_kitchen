@extends('admin.admin_master')
@section('header_title', 'Operasional & Libur')

@section('content')
<!-- Tambahin items-start biar kedua kotak sejajar di atas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-7xl mx-auto items-start">
    
    <!-- Pengaturan Operasional -->
    <div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Pengaturan Operasional</h2>
        <div id="alert-op" class="hidden p-3 mb-4 rounded-xl font-bold text-sm"></div>
        <form id="form-operasional" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Batas Maksimal Roti Per Hari (Qty)</label>
                <input type="number" name="max_qty_per_day" id="max_qty_per_day" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Batas Antrean Kirim Barang (Per Jam)</label>
                <input type="number" name="max_orders_per_hour" id="max_orders_per_hour" required class="w-full p-3 border rounded-xl bg-gray-50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Buka</label>
                    <input type="time" name="operational_time_start" id="operational_time_start" required class="w-full p-3 border rounded-xl bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Tutup</label>
                    <input type="time" name="operational_time_end" id="operational_time_end" required class="w-full p-3 border rounded-xl bg-gray-50">
                </div>
            </div>
            <button type="submit" id="btn-submit-op" class="w-full py-3 bg-purple-600 text-white font-bold rounded-xl hover:bg-purple-700 transition">Simpan Pengaturan</button>
        </form>
    </div>

    <!-- Manajemen Hari Libur -->
    <div class="p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
        
        <!-- Header: Jejer pas layar gede, tumpuk pas layar cilik -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
            <h2 class="text-xl font-bold text-gray-800">Kalender Hari Libur</h2>
            <button type="button" id="btn-generate-libur" class="w-full sm:w-auto px-3 py-2 bg-blue-50 text-blue-600 font-bold rounded-lg border border-blue-200 hover:bg-blue-100 text-xs flex items-center justify-center gap-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Auto-Generate
            </button>
        </div>
        
        <!-- Form: Tumpuk (flex-col) pas mobile, Jejer (flex-row) pas layar gede (md) -->
        <form id="form-libur" class="flex flex-col md:flex-row gap-3 mb-6">
            @csrf
            <input type="date" name="holiday_date" id="holiday_date" required class="w-full md:flex-1 p-3 border rounded-xl bg-gray-50 text-sm">
            
            <input type="text" name="description" id="holiday_desc" placeholder="Keterangan Libur" required class="w-full md:flex-1 p-3 border rounded-xl bg-gray-50 text-sm">
            
            <!-- Jeneng tombol diganti kaya panjaluke -->
            <button type="submit" class="w-full md:w-auto px-6 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 text-sm whitespace-nowrap transition">
                Tambah Libur
            </button>
        </form>
        
        <!-- Tabel Area -->
        <div class="border border-gray-200 rounded-xl max-h-[300px] overflow-y-auto">
            <table class="w-full text-sm text-left relative">
                <thead class="text-gray-700 uppercase font-bold text-xs sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 bg-gray-50 shadow-sm border-b">Tanggal</th>
                        <th class="px-4 py-3 bg-gray-50 shadow-sm border-b">Keterangan</th>
                        <th class="px-4 py-3 bg-gray-50 shadow-sm border-b text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-libur" class="divide-y divide-gray-100">
                    <!-- Data libur dimuat via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    // Load Operasional
    $.get('/api/admin/operasional', function(res) {
        if(res.status === 'success' && res.data) {
            $('#max_qty_per_day').val(res.data.max_qty_per_day);
            $('#max_orders_per_hour').val(res.data.max_orders_per_hour);
            $('#operational_time_start').val(res.data.operational_time_start);
            $('#operational_time_end').val(res.data.operational_time_end);
        }
    });

    $('#form-operasional').submit(function(e) {
        e.preventDefault();
        let btn = $('#btn-submit-op');
        btn.text('Menyimpan...').prop('disabled', true);
        $.post('/api/admin/operasional', $(this).serialize(), function(res) {
            $('#alert-op').removeClass('hidden bg-red-100 text-red-700').addClass('bg-green-100 text-green-700').text(res.message);
            btn.text('Simpan Pengaturan').prop('disabled', false);
        });
    });

    // Load Libur
    function loadLibur() {
        $.get('/api/admin/libur', function(res) {
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="3" class="text-center py-4 text-gray-500 font-medium">Belum ada hari libur tersimpan.</td></tr>';
            } else {
                res.data.forEach(item => {
                    html += `<tr>
                        <td class="px-4 py-3 font-bold bg-white">${item.holiday_date}</td>
                        <td class="px-4 py-3 bg-white">${item.description}</td>
                        <td class="px-4 py-3 text-right bg-white">
                            <button onclick="hapusLibur(${item.id})" class="text-red-500 font-bold hover:underline">Hapus</button>
                        </td>
                    </tr>`;
                });
            }
            $('#table-libur').html(html);
        });
    }
    
    loadLibur();

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
            },
            error: function(xhr) { alert(xhr.responseJSON?.message || 'Gagal menambahkan libur'); }
        });
    });

    window.hapusLibur = function(id) {
        if(confirm('Hapus tanggal libur ini?')) {
            $.ajax({
                url: '/api/admin/libur/' + id,
                type: 'DELETE',
                data: { _token: $('input[name="_token"]').val() },
                success: function() { loadLibur(); }
            });
        }
    };

    // Script Auto-Generate Libur
    $('#btn-generate-libur').click(function() {
        if(!confirm('Sistem akan memasukkan seluruh hari Sabtu, Minggu, dan Libur Nasional ke kalender. Lanjutkan?')) return;
        
        let btn = $(this);
        let originalText = btn.html();
        btn.html('Memproses...').prop('disabled', true);

        $.ajax({
            url: '/api/admin/libur/generate',
            type: 'POST',
            data: { 
                _token: $('input[name="_token"]').val(),
                year: new Date().getFullYear()
            },
            success: function(res) {
                alert(res.message);
                loadLibur(); 
            },
            error: function() {
                alert('Gagal meng-generate hari libur.');
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush