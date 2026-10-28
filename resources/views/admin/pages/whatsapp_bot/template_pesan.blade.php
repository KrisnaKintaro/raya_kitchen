@extends('admin.admin_master')

@section('title', 'Template Pesan WA - Admin Raya Kitchen')
@section('header_title', 'Template Pesan Bot')

@section('css')
<style>
    /* WhatsApp Background Pattern (Simulated) */
    .wa-bg {
        background-color: #efeae2;
        background-image: url('https://w0.peakpx.com/wallpaper/818/148/HD-wallpaper-whatsapp-background-solid-color-whatsapp-background-theme-whatsapp-patterns-flat-color.jpg');
        background-blend-mode: multiply;
        background-size: cover;
    }

    /* WhatsApp Bubble Chat */
    .wa-bubble {
        background-color: #ffffff;
        border-radius: 0.5rem 0.5rem 0.5rem 0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        position: relative;
    }
    .wa-bubble::after {
        content: ''; position: absolute; bottom: 0; left: -8px;
        width: 0; height: 0;
        border-top: 10px solid transparent;
        border-bottom: 0px solid transparent;
        border-right: 12px solid #ffffff;
    }

    /* Active List Item */
    .list-active {
        background-color: #F5F2F8;
        border-color: #B38CDA;
    }

    /* Checkbox Toggle CSS */
    input:checked ~ .dot { transform: translateX(100%); background-color: #9368B7; }
    input:checked ~ .block { background-color: #E4CCFF; }

    /* Basic Markdown untuk preview wa */
    .wa-bold { font-weight: 800; }
    .wa-italic { font-style: italic; }
    .wa-strikethrough { text-decoration: line-through; }
</style>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:h-[calc(100vh-160px)]">

    <!-- LEFT PANEL: Daftar Template (Span 4) -->
    <div class="lg:col-span-4 xl:col-span-3 bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col overflow-hidden h-[50vh] lg:h-auto">
        <div class="p-5 border-b border-admin-border bg-gray-50/50 shrink-0">
            <h2 class="font-black text-admin-textMain text-base">Skenario Percakapan</h2>
            <p class="text-xs font-medium text-admin-textMuted mt-1">Pilih state bot yang ingin diedit.</p>
        </div>

        <!-- List Scrollable -->
        <div class="flex-1 overflow-y-auto hide-scrollbar p-3 space-y-2" id="template-list">
            <!-- Di-inject via JS -->
        </div>
    </div>

    <!-- RIGHT PANEL: Editor & Preview (Span 8) -->
    <div class="lg:col-span-8 xl:col-span-9 bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col xl:flex-row overflow-hidden relative min-h-[600px] lg:h-auto">

        <!-- Empty State (Saat belum ada yang dipilih) -->
        <div id="editor-empty" class="absolute inset-0 bg-white z-10 flex flex-col items-center justify-center text-center p-8">
            <div class="w-20 h-20 bg-admin-bg rounded-3xl flex items-center justify-center mb-5 border border-admin-border shadow-inner">
                <i class="ph ph-chat-centered-text text-4xl text-admin-textMuted/50"></i>
            </div>
            <h3 class="text-xl font-black text-admin-textMain mb-2">Pilih Template Pesan</h3>
            <p class="text-sm font-medium text-admin-textMuted max-w-sm">Pilih salah satu skenario dari daftar di sebelah kiri untuk mulai mengubah balasan otomatis Bot WhatsApp.</p>
        </div>

        <!-- Kolom Editor Text -->
        <div class="flex-1 flex flex-col border-b xl:border-b-0 xl:border-r border-admin-border h-full p-5 lg:p-6 overflow-y-auto hide-scrollbar">
            <div class="flex justify-between items-start mb-4 shrink-0">
                <div>
                    <h3 class="font-black text-lg text-admin-textMain" id="edit-title">Nama State</h3>
                    <p class="text-xs font-bold text-admin-textMuted mt-1" id="edit-desc">Deskripsi skenario</p>
                </div>
                <!-- Toggle Aktif/Nonaktif -->
                <label class="flex items-center cursor-pointer group shrink-0 ml-4">
                    <div class="relative">
                        <input type="checkbox" id="edit-is-active" class="sr-only">
                        <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors group-hover:bg-gray-300"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform shadow-sm"></div>
                    </div>
                    <span class="ml-2 text-xs font-bold text-admin-textMuted" id="edit-status-text">Aktif</span>
                </label>
            </div>

            <!-- Form Edit -->
            <form id="form-template" class="flex flex-col flex-1">
                <input type="hidden" id="edit-session-state">

                <div class="flex-1 flex flex-col relative mb-4">
                    <label class="text-[11px] font-black text-admin-accent uppercase tracking-widest mb-2 opacity-90 drop-shadow-sm shrink-0">Isi Pesan (Format WhatsApp)</label>
                    <textarea id="edit-message" class="flex-1 w-full p-4 bg-admin-bg/50 border border-admin-border rounded-2xl text-sm font-medium text-admin-textMain focus:outline-none focus:ring-2 focus:ring-admin-sideHover resize-none shadow-inner min-h-[200px]" placeholder="Ketik pesan di sini..."></textarea>

                    <!-- Bantuan Variabel (STRICT MODE) -->
                    <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl shrink-0">
                        <p class="text-xs font-bold text-amber-800 mb-2 flex items-center gap-1.5">
                            <i class="ph ph-warning-circle text-lg"></i> Aturan Variabel Dinamis
                        </p>
                        <p class="text-[11px] text-amber-700 font-medium leading-relaxed mb-3">
                            Teks di dalam kurung kurawal <code class="font-black bg-white/60 px-1.5 py-0.5 rounded shadow-sm">{}</code> adalah data otomatis dari sistem. <b>Dilarang keras mengubah nama atau menghapusnya.</b> Anda hanya diizinkan memindahkan posisinya.<br>
                            <i>(Klik tombol di bawah untuk menyisipkan variabel ke posisi kursor)</i>
                        </p>

                        <div id="edit-vars-container" class="flex flex-wrap gap-2">
                            <!-- Tombol variabel di-inject via JS -->
                        </div>

                        <!-- Pesan Error Validasi -->
                        <div id="var-error-msg" class="hidden mt-3 p-2 bg-rose-100 border border-rose-200 rounded-lg text-xs font-bold text-rose-600 flex items-center gap-2">
                            <i class="ph ph-x-circle text-lg shrink-0"></i>
                            <span id="var-error-text">Error text</span>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-save" class="w-full xl:w-auto self-end flex justify-center items-center gap-2 bg-admin-sideBg hover:bg-admin-sideHover text-white px-8 py-3.5 rounded-2xl font-black text-sm transition-all shadow-[0_4px_10px_rgba(147,104,183,0.2)] hover:-translate-y-0.5 active:translate-y-0 focus-visible:outline-none disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                    <i class="ph ph-floppy-disk text-xl"></i>
                    Simpan Template
                </button>
            </form>
        </div>

        <!-- Kolom Preview (Tampilan HP) -->
        <div class="w-full xl:w-[350px] shrink-0 bg-gray-50 flex flex-col p-6 items-center justify-center border-t xl:border-t-0">
            <h4 class="text-xs font-black text-admin-textMuted uppercase tracking-widest mb-4">Live Preview</h4>

            <!-- Mockup HP -->
            <div class="w-[280px] sm:w-[300px] h-[500px] bg-white rounded-[2.5rem] border-[8px] border-gray-800 shadow-2xl relative overflow-hidden flex flex-col">
                <!-- Header WA -->
                <div class="bg-[#005C4B] text-white p-3 flex items-center gap-3 shrink-0 shadow-sm z-10">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                        <i class="ph ph-robot text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold leading-tight">Raya Kitchen Bot</p>
                        <p class="text-[10px] opacity-80">Online</p>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="wa-bg flex-1 p-4 overflow-y-auto flex flex-col gap-3 hide-scrollbar">
                    <div class="wa-bubble p-2.5 max-w-[85%] self-start text-[13px] leading-relaxed text-[#111B21]">
                        <div id="preview-text" class="whitespace-pre-wrap font-sans break-words">Pesan loading...</div>
                        <div class="text-[10px] text-right text-gray-400 mt-1 uppercase">12:00</div>
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
        // --- 1. DATA DUMMY TEMPLATE ---
        const dummyTemplates = [
            { session_state: "sapaan", description: "Pesan sapaan awal saat pelanggan baru chat", is_active: true, message_text: "Halo Kak {nama}! Selamat datang di Raya Kitchen. 🍞✨\n\nKetik angka dari menu di bawah ini untuk mulai:\n1. 📖 Lihat Katalog Produk\n2. 🛒 Cek Keranjang Saya\n3. 📦 Cek Status Pesanan\n4. ℹ️ Cara Pemesanan" },
            { session_state: "instruksi", description: "Pesan panduan cara menggunakan bot", is_active: true, message_text: "Cara pesannya gampang banget Kak!\n\n1. Pilih 'Lihat Katalog' lalu ketik angka produk yang mau dibeli.\n2. Masukkan jumlah produk.\n3. Ikuti langkah pengisian data pengiriman.\n4. Pilih metode pembayaran (Lunas, DP, atau COD).\n5. Lakukan pembayaran via QRIS dan pesanan otomatis diproses!\n\n0. 🔙 Kembali ke Menu Utama" },
            { session_state: "lihat_kategori", description: "Menampilkan daftar kategori produk yang aktif", is_active: true, message_text: "Silakan pilih kategori produk yang mau dilihat:\n\n{daftar_kategori}\n0. 🔙 Kembali ke Menu Utama" },
            { session_state: "detail_varian", description: "Menampilkan detail produk dan opsi tambah ke keranjang", is_active: true, message_text: "Detail Produk:\n\n🍞 *{nama_produk}*\n🏷️ Kategori: {kategori_produk}\n💰 Harga: Rp {harga_produk}\n\n📝 Deskripsi:\n{deskripsi_produk}\n\nKetik angka berikut:\n1. 🛒 Tambah ke Keranjang\n0. 🔙 Kembali ke Daftar Varian" },
            { session_state: "keranjang", description: "Menampilkan isi keranjang belanja dan opsi checkout", is_active: true, message_text: "🛒 *Isi Keranjang Kamu:*\n\n{isi_keranjang}\nTotal Belanja: Rp {total_harga}\n\nPilih aksi:\n1. ✅ Lanjut Checkout\n2. ➕ Tambah Produk Lain\n3. 🗑️ Kosongkan Keranjang\n0. 🔙 Kembali ke Menu Utama" },
            { session_state: "checkout_konfirmasi", description: "Menampilkan ringkasan final sebelum generate QRIS", is_active: true, message_text: "🧾 *Ringkasan Pesanan*\n{ringkasan_pesanan}\n\n*Total Tagihan (Sesuai Skema): Rp {total_tagihan}*\n\nApakah data pesanan sudah benar?\n1. ✅ Ya, Buat Pesanan & Bayar\n2. ❌ Tidak, Batal Checkout" },
            { session_state: "pembayaran_qris", description: "Pesan instruksi pembayaran beserta gambar QRIS", is_active: true, message_text: "🎉 Pesanan *#{order_number}* berhasil dibuat!\n\nSilakan scan QRIS pada gambar di atas untuk membayar tagihan sebesar *Rp {total_tagihan}*.\nBatas waktu pembayaran adalah 1 jam dari sekarang.\n\nSistem akan otomatis mengirimkan notifikasi jika pembayaran sudah berhasil diterima.\n\n0. 🔙 Kembali ke Menu Utama" },
        ];

        // --- 2. DATA DUMMY UNTUK PREVIEW VARIABEL ---
        const dummyVars = {
            "{nama}": "Budi Santoso",
            "{daftar_kategori}": "1. Roti Tawar\n2. Roti Manis\n3. Kue Kering",
            "{nama_kategori}": "Roti Manis",
            "{daftar_varian}": "1. Roti Coklat\n2. Roti Keju\n3. Roti Srikaya",
            "{nama_produk}": "Roti Sisir Mentega",
            "{kategori_produk}": "Roti Manis",
            "{harga_produk}": "15.000",
            "{deskripsi_produk}": "Roti sisir lembut dengan olesan mentega manis yang lumer di mulut.",
            "{isi_keranjang}": "- 2x Roti Sisir Mentega\n- 1x Roti Tawar Gandum",
            "{total_harga}": "55.000",
            "{daftar_alamat}": "1. Rumah (Jl. Melati No 5)\n2. Kantor (Gedung Sudirman)",
            "{ringkasan_pesanan}": "Penerima: Budi Santoso\nAlamat: Jl. Melati No 5\nTgl Kirim: 2026-10-30 09:00\nItem: 3 Roti",
            "{total_tagihan}": "16.500",
            "{order_number}": "RK-20261025-9988"
        };

        let currentActiveState = null;
        let currentRequiredVars = []; // Simpan list variabel asli dari database

        // --- 3. RENDER LIST SIDEBAR ---
        function renderList() {
            let html = '';
            dummyTemplates.forEach(t => {
                let title = t.session_state.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                let activeClass = currentActiveState === t.session_state ? 'list-active' : 'border-transparent hover:bg-admin-bg';

                html += `
                    <div class="template-item p-3 border-2 rounded-2xl cursor-pointer transition-all ${activeClass}" data-state="${t.session_state}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-sm text-admin-textMain">${title}</span>
                            ${t.is_active ? '<div class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_5px_#10B981]"></div>' : '<div class="w-2 h-2 bg-gray-300 rounded-full"></div>'}
                        </div>
                        <p class="text-[11px] font-medium text-admin-textMuted line-clamp-1">${t.description}</p>
                    </div>
                `;
            });
            $('#template-list').html(html);
        }

        // --- 4. ENGINE PARSER PESAN WA & VALIDATOR ---
        function parseWaFormat(text) {
            let parsedText = text;
            Object.keys(dummyVars).forEach(key => {
                let regex = new RegExp(key, "gi");
                parsedText = parsedText.replace(regex, dummyVars[key]);
            });
            parsedText = parsedText.replace(/\*(.*?)\*/g, '<span class="wa-bold">$1</span>');
            parsedText = parsedText.replace(/_(.*?)_/g, '<span class="wa-italic">$1</span>');
            parsedText = parsedText.replace(/~(.*?)~/g, '<span class="wa-strikethrough">$1</span>');
            return parsedText;
        }

        function validateVariables() {
            const currentText = $('#edit-message').val();
            let missingVars = [];

            // Cek apakah semua variabel wajib masih ada di textarea
            currentRequiredVars.forEach(v => {
                if (!currentText.includes(v)) {
                    missingVars.push(v);
                }
            });

            const btnSave = $('#btn-save');
            const errorBox = $('#var-error-msg');
            const errorText = $('#var-error-text');

            if (missingVars.length > 0) {
                // Ada yang terhapus! Disable tombol simpan
                errorText.html(`Anda menghapus variabel wajib: <b>${missingVars.join(', ')}</b>. Silakan masukkan kembali!`);
                errorBox.removeClass('hidden');
                btnSave.prop('disabled', true);
            } else {
                // Aman
                errorBox.addClass('hidden');
                btnSave.prop('disabled', false);
            }
        }

        function updatePreview() {
            const rawText = $('#edit-message').val();
            const formattedHTML = parseWaFormat(rawText);
            $('#preview-text').html(formattedHTML);

            // Setiap ada perubahan teks, jalankan validasi
            validateVariables();
        }

        // --- 5. EVENT LISTENERS ---

        // Pilih Template
        $(document).on('click', '.template-item', function() {
            const state = $(this).data('state');
            currentActiveState = state;
            renderList();

            const data = dummyTemplates.find(t => t.session_state === state);
            if(data) {
                $('#editor-empty').fadeOut(200);

                let title = data.session_state.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                $('#edit-title').text(title);
                $('#edit-desc').text(data.description);
                $('#edit-session-state').val(data.session_state);
                $('#edit-is-active').prop('checked', data.is_active);
                $('#edit-status-text').text(data.is_active ? 'Aktif' : 'Nonaktif');
                $('#edit-message').val(data.message_text);

                // Ekstrak variabel wajib dari DB (Hanya pas awal load)
                let foundVars = data.message_text.match(/\{.*?\}/g);
                if (foundVars) {
                    currentRequiredVars = [...new Set(foundVars)];

                    // Bikin tombol insert untuk tiap variabel
                    let buttonsHtml = currentRequiredVars.map(v =>
                        `<button type="button" class="btn-insert-var px-2.5 py-1.5 bg-white border border-amber-300 text-amber-700 text-[11px] font-black rounded-lg shadow-sm hover:bg-amber-100 hover:-translate-y-0.5 transition-transform focus:outline-none" data-var="${v}">+ ${v}</button>`
                    ).join('');
                    $('#edit-vars-container').html(buttonsHtml);
                } else {
                    currentRequiredVars = [];
                    $('#edit-vars-container').html('<span class="text-xs font-bold text-amber-600/70">Tidak ada variabel sistem di skenario ini.</span>');
                }

                updatePreview();
            }
        });

        // Insert Variabel ke Cursor Position
        $(document).on('click', '.btn-insert-var', function() {
            const varText = $(this).data('var');
            const textarea = $('#edit-message')[0];

            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const text = textarea.value;

            // Suntik teks di posisi kursor
            textarea.value = text.substring(0, startPos) + varText + text.substring(endPos, text.length);

            // Kembalikan fokus dan geser kursor ke ujung variabel yang baru dimasukkan
            textarea.selectionStart = startPos + varText.length;
            textarea.selectionEnd = startPos + varText.length;
            textarea.focus();

            updatePreview();
        });

        $('#edit-message').on('input', updatePreview);

        $('#edit-is-active').on('change', function() {
            $('#edit-status-text').text($(this).is(':checked') ? 'Aktif' : 'Nonaktif');
        });

        // Submit
        $('#form-template').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-save');
            const ogHtml = btn.html();

            btn.html('<i class="ph ph-spinner-gap animate-spin text-xl"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '#', // Nanti ganti endpoint api update
                type: 'POST',
                data: {
                    session_state: $('#edit-session-state').val(),
                    message_text: $('#edit-message').val(),
                    is_active: $('#edit-is-active').is(':checked')
                },
                success: function() {
                    const idx = dummyTemplates.findIndex(t => t.session_state === currentActiveState);
                    if (idx !== -1) {
                        dummyTemplates[idx].message_text = $('#edit-message').val();
                        dummyTemplates[idx].is_active = $('#edit-is-active').is(':checked');
                    }

                    btn.html(ogHtml).prop('disabled', false);
                    renderList();

                    alert('Template WA berhasil diperbarui!');
                }
            });
        });

        renderList();
    });
</script>
@endpush
