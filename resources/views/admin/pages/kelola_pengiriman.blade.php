@extends('admin.admin_master')
@section('header_title', 'Kelola Pengiriman')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    
    <!-- Header & Tab Navigasi -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6 border-b border-gray-200 pb-4">
        <button id="tab-internal" class="px-6 py-3 bg-admin-sideBg text-white font-bold rounded-xl shadow-[0_4px_12px_rgba(147,104,183,0.3)] transition flex items-center justify-center gap-2">
            <i class="ph ph-moped text-xl"></i> Kurir Internal (Gratis Ongkir)
        </button>
        <button id="tab-ekspedisi" class="px-6 py-3 bg-white text-gray-600 font-bold rounded-xl border border-gray-200 hover:bg-gray-50 transition flex items-center justify-center gap-2">
            <i class="ph ph-truck text-xl"></i> Ekspedisi (Jasa Kirim)
        </button>
    </div>

    <!-- KONTEN: Kurir Internal -->
    <div id="content-internal" class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 animate-[fadeInUp_0.3s_ease-out]">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                <i class="ph ph-package text-admin-sideBg"></i> Siap Kirim (Internal)
            </h2>
        </div>
        
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left relative">
                <thead class="text-gray-700 uppercase font-bold text-xs bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Order ID</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Jarak (KM)</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-internal" class="divide-y divide-gray-100">
                    <!-- Data AJAX render ke sini -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- KONTEN: Ekspedisi -->
    <div id="content-ekspedisi" class="hidden bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 animate-[fadeInUp_0.3s_ease-out]">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                <i class="ph ph-paper-plane-right text-blue-500"></i> Siap Kirim (Ekspedisi)
            </h2>
        </div>
        
        <div class="border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-left relative">
                <thead class="text-gray-700 uppercase font-bold text-xs bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Order ID</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Kurir & Layanan</th>
                        <th class="px-4 py-3">Jarak (KM)</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-ekspedisi" class="divide-y divide-gray-100">
                    <!-- Data AJAX render ke sini -->
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    // ----------------------------------------------------
    // LOGIKA GANTI TAB
    // ----------------------------------------------------
    $('#tab-internal').click(function() {
        $(this).removeClass('bg-white text-gray-600 border border-gray-200 hover:bg-gray-50').addClass('bg-admin-sideBg text-white shadow-[0_4px_12px_rgba(147,104,183,0.3)]');
        $('#tab-ekspedisi').removeClass('bg-admin-sideBg text-white shadow-[0_4px_12px_rgba(147,104,183,0.3)]').addClass('bg-white text-gray-600 border border-gray-200 hover:bg-gray-50');
        
        $('#content-internal').removeClass('hidden');
        $('#content-ekspedisi').addClass('hidden');
    });

    $('#tab-ekspedisi').click(function() {
        $(this).removeClass('bg-white text-gray-600 border border-gray-200 hover:bg-gray-50').addClass('bg-admin-sideBg text-white shadow-[0_4px_12px_rgba(147,104,183,0.3)]');
        $('#tab-internal').removeClass('bg-admin-sideBg text-white shadow-[0_4px_12px_rgba(147,104,183,0.3)]').addClass('bg-white text-gray-600 border border-gray-200 hover:bg-gray-50');
        
        $('#content-ekspedisi').removeClass('hidden');
        $('#content-internal').addClass('hidden');
    });


    // ----------------------------------------------------
    // LOGIKA RENDER DATA AJAX
    // ----------------------------------------------------
    function loadPengiriman() {
        $.get('/api/admin/pengiriman', function(res) {
            if(res.status === 'success') {
                let htmlInternal = '';
                let htmlEkspedisi = '';

                // Render Internal
                if(res.data.internal.length === 0) {
                    htmlInternal = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Tidak ada pesanan internal yang siap kirim.</td></tr>';
                } else {
                    res.data.internal.forEach(item => {
                        let jarak = item.user_address ? item.user_address.distance_to_store : '0';
                        
                        htmlInternal += `<tr>
                            <td class="px-4 py-4 font-bold text-admin-sideBg">#${item.id}</td>
                            <td class="px-4 py-4 font-bold">${item.customer_name || 'Pelanggan'}</td>
                            <td class="px-4 py-4 font-bold text-green-600">${jarak} KM</td>
                            <td class="px-4 py-4"><span class="px-3 py-1 bg-yellow-100 text-yellow-700 font-bold rounded-lg text-xs">${item.status}</span></td>
                            <td class="px-4 py-4 text-right">
                                <button onclick="kirimPesanan(${item.id})" class="px-4 py-2 bg-admin-sideBg text-white font-bold rounded-lg text-xs hover:bg-admin-sideHover transition">Kirim Sekarang</button>
                            </td>
                        </tr>`;
                    });
                }
                $('#table-internal').html(htmlInternal);

                // Render Ekspedisi
                if(res.data.ekspedisi.length === 0) {
                    htmlEkspedisi = '<tr><td colspan="5" class="text-center py-6 text-gray-500">Tidak ada pesanan ekspedisi yang siap kirim.</td></tr>';
                } else {
                    res.data.ekspedisi.forEach(item => {
                        let jarak = item.user_address ? item.user_address.distance_to_store : '0';
                        
                        htmlEkspedisi += `<tr>
                            <td class="px-4 py-4 font-bold text-admin-sideBg">#${item.id}</td>
                            <td class="px-4 py-4 font-bold">${item.customer_name || 'Pelanggan'}</td>
                            <td class="px-4 py-4"><span class="font-bold block">${item.courier_name}</span><span class="text-xs text-gray-500">${item.shipping_service}</span></td>
                            <td class="px-4 py-4 font-bold text-red-500">${jarak} KM</td>
                            <td class="px-4 py-4 text-right">
                                <button onclick="kirimPesanan(${item.id}, true)" class="px-4 py-2 bg-blue-500 text-white font-bold rounded-lg text-xs hover:bg-blue-600 transition">Serahkan ke Kurir</button>
                            </td>
                        </tr>`;
                    });
                }
                $('#table-ekspedisi').html(htmlEkspedisi);
            }
        });
    }

    // Panggil fungsi get data saat halaman dimuat
    loadPengiriman();

    // ----------------------------------------------------
    // LOGIKA UPDATE STATUS (TOMBOL KIRIM)
    // ----------------------------------------------------
    window.kirimPesanan = function(id, isEkspedisi = false) {
        let resi = '';
        if(isEkspedisi) {
            resi = prompt('Masukkan Nomor Resi Ekspedisi (Opsional):');
            if(resi === null) return; // Batal jika tekan cancel
        } else {
            if(!confirm('Tandai pesanan ini sedang diantar oleh kurir internal?')) return;
        }

        $.ajax({
            url: `/api/admin/pengiriman/${id}/kirim`,
            type: 'POST',
            data: { 
                _token: $('meta[name="csrf-token"]').attr('content'),
                tracking_number: resi
            },
            success: function(res) {
                alert(res.message);
                loadPengiriman(); // Refresh tabel otomatis setelah berhasil dikirim
            },
            error: function(xhr) {
                alert('Terjadi kesalahan sistem.');
            }
        });
    };
});
</script>
@endpush