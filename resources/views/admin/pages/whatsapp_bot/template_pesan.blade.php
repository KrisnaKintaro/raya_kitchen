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

    /* Basic Markdown untuk preview wa */
    .wa-bold { font-weight: 800; }
    .wa-italic { font-style: italic; }
    .wa-strikethrough { text-decoration: line-through; }
</style>
@endsection

@section('content')
<!-- Toast Notifikasi Pojok Kanan Atas -->
<div id="toast-notif" class="fixed top-6 right-6 z-[200] hidden items-center gap-3 px-5 py-3.5 rounded-2xl bg-white border border-admin-accent shadow-2xl transition-all">
    <div id="toast-icon" class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
        <i class="ph ph-check-circle text-lg"></i>
    </div>
    <p id="toast-msg" class="text-xs font-black text-admin-textMain">Berhasil disimpan!</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:h-[calc(100vh-160px)]">

    <!-- LEFT PANEL: Daftar Template (Span 4) -->
    <div class="lg:col-span-4 xl:col-span-3 bg-white rounded-[2rem] shadow-[0_4px_20px_rgba(147,104,183,0.05)] border border-admin-border flex flex-col overflow-hidden h-[50vh] lg:h-auto">
        <div class="p-5 border-b border-admin-border bg-gray-50/50 shrink-0">
            <h2 class="font-black text-admin-textMain text-base">Skenario Percakapan</h2>
            <p class="text-xs font-medium text-admin-textMuted mt-1">Pilih state bot yang ingin diedit.</p>
        </div>

        <!-- List Scrollable -->
        <div class="flex-1 overflow-y-auto hide-scrollbar p-3 space-y-2" id="template-list">
            <p class="text-xs font-bold text-admin-textMuted text-center py-10 animate-pulse">Memuat skenario pesan...</p>
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
            <div class="flex justify-between items-start mb-4 shrink-0 gap-4">
                <div>
                    <h3 class="font-black text-lg text-admin-textMain" id="edit-title">Nama State</h3>
                    <p class="text-xs font-bold text-admin-textMuted mt-1" id="edit-desc">Deskripsi skenario</p>
                </div>

                <!-- Tombol Kembalikan ke Default -->
                <button type="button" id="btn-reset-default" class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-admin-bg hover:bg-admin-navBg text-admin-sideBg font-black text-xs border border-admin-accent/60 transition-all shadow-sm hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-40 disabled:pointer-events-none" title="Kembalikan teks ke bawaan sistem">
                    <i class="ph ph-arrow-counter-clockwise text-sm"></i>
                    <span id="btn-reset-text">Kembalikan ke Default</span>
                </button>
            </div>

            <!-- Form Edit -->
            <form id="form-template" class="flex flex-col flex-1">
                <input type="hidden" id="edit-session-state">

                <div class="flex-1 flex flex-col relative mb-4">
                    <div class="flex justify-between items-center mb-2 shrink-0">
                        <label class="text-[11px] font-black text-admin-accent uppercase tracking-widest opacity-90 drop-shadow-sm">Isi Pesan (Format WhatsApp)</label>
                        <span id="badge-status-default" class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">Default Sistem</span>
                    </div>

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
        let templatesData = [];
        let currentActiveState = null;
        let currentRequiredVars = [];

        // --- 1. DATA CONTOH UNTUK LIVE PREVIEW VARIABEL ---
        const dummyVars = {
            "{nama}": "Budi Santoso",
            "{nama_penerima}": "Budi Santoso",
            "{daftar_kategori}": "1. Roti Manis\n2. Roti Gurih\n3. Kue Kering",
            "{nama_kategori}": "Roti Manis",
            "{daftar_varian}": "1. Coklat Lumer (Rp 12.000)\n2. Keju Susu (Rp 13.000)",
            "{nama_produk}": "Coklat Lumer",
            "{kategori_produk}": "Roti Manis",
            "{harga_produk}": "12.000",
            "{deskripsi_produk}": "Roti lembut dengan isian coklat lumer premium.",
            "{isi_keranjang}": "- Roti Manis - Coklat Lumer (2x) = Rp 24.000\n- Roti Gurih - Abon Sapi (1x) = Rp 15.000",
            "{total_harga}": "39.000",
            "{daftar_alamat}": "Alamat Tersimpan:\n1. [Rumah] Jl. Mawar No 5, Jatiasih, Kota Bekasi (Patokan: Pagar Hitam)",
            "{form_alamat}": "--- Form Alamat Baru ---\nLabel Alamat : \nProvinsi : \nKota / Kabupaten : \nKecamatan : \nKode Pos : \nAlamat Jalan : \nPatokan : ",
            "{link_maps}": "https://rayakitchen.com/pin-lokasi/1?wa=628123456789",
            "{jarak}": "4.5",
            "{daftar_ekspedisi}": "1. JNE - REG (Rp 12.000) | Est: 2-3 hr\n2. SICEPAT - REG (Rp 11.500) | Est: 2-3 hr",
            "{contoh_tanggal}": "2026-11-16",
            "{tanggal}": "2026-11-16",
            "{sisa_kuota}": "5",
            "{alasan_penolakan}": "Waduh, kuota produksi dapur pada tanggal tersebut sudah penuh.",
            "{daftar_tanggal}": "1. Senin, 16 November 2026 (2026-11-16)\n2. Selasa, 17 November 2026 (2026-11-17)",
            "{jam_buka}": "08:00",
            "{jam_tutup}": "17:00",
            "{jam}": "10:00",
            "{daftar_jam}": "1. Jam 09:00 WIB (Sisa 4 slot)\n2. Jam 11:00 WIB (Sisa 5 slot)",
            "{ringkasan_pesanan}": "Pemesan: Budi Santoso\nAlamat: Jl. Mawar No 5, Bekasi\nKurir: Kurir Internal Raya Kitchen (Gratis)\nJadwal: 2026-11-16 Jam 10:00 WIB\n\nItem:\n- Roti Manis - Coklat Lumer (2x) = Rp 24.000\n\nTotal Pesanan: Rp 24.000\nMetode: DP 30% (QRIS)",
            "{total_tagihan}": "7.200",
            "{order_number}": "RK-20261114-8899",
            "{link_pembayaran}": "https://rayakitchen.com/pembayaran/RK-20261114-8899",
            "{link_nota}": "https://rayakitchen.com/nota?order_number=RK-20261114-8899",
            "{label_filter}": "SEMUA PESANAN",
            "{halaman_sekarang}": "1",
            "{total_halaman}": "2",
            "{total_pesanan}": "8",
            "{daftar_pesanan}": "*1. #RK-20261114-8899*\n📅 Tgl: 14 Nov 2026, 10:00\n📌 Status: *DALAM ANTREAN* (TRANSFER - DP 30%)\n💳 Status Bayar: *PAID_DP*\n💰 Total: Rp 24.000\n⏳ Sisa Tagihan: *Rp 16.800*\n🧾 Lihat Nota: https://rayakitchen.com/nota?order_number=RK-20261114-8899",
            "{navigasi_halaman}": "⏭️ Ketik *lanjut* untuk melihat 5 pesanan berikutnya\n",
            "{status_pesanan}": "DIPROSES",
            "{info_refund}": "Dana refund akan segera diproses oleh Admin ke rekening *BCA - 1234567890 a/n Budi Santoso* maksimal 1x24 jam.",
            "{total_pesanan}": "Rp 24.000",
            "{nominal_dp}": "Rp 7.200",
            "{sisa_tagihan}": "Rp 16.800",
            "{info_status}": "Status pesananmu kini telah *SELESAI*.",
            "{info_pelunasan}": "⚠️ *Belum Lunas (Sisa Rp 16.800):*\nSilakan lunasi sisa tagihanmu sebelum pesanan diselesaikan melalui link berikut:\n👉 https://rayakitchen.com/pembayaran/RK-20261114-8899"
        };

        function showToast(message, isSuccess = true) {
            const toast = $('#toast-notif');
            const icon = $('#toast-icon');
            $('#toast-msg').text(message);

            if (isSuccess) {
                icon.removeClass('bg-rose-100 text-rose-600').addClass('bg-emerald-100 text-emerald-600')
                    .html('<i class="ph ph-check-circle text-lg"></i>');
            } else {
                icon.removeClass('bg-emerald-100 text-emerald-600').addClass('bg-rose-100 text-rose-600')
                    .html('<i class="ph ph-warning-circle text-lg"></i>');
            }

            toast.removeClass('hidden').addClass('flex');
            setTimeout(() => {
                toast.removeClass('flex').addClass('hidden');
            }, 3000);
        }

        // --- 2. FETCH DATA DARI DATABASE VIA API ---
        function loadTemplates() {
            $.ajax({
                url: '/api/admin/bot-templates',
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        templatesData = res.data || [];
                        renderList();
                    }
                },
                error: function() {
                    $('#template-list').html('<p class="text-xs font-bold text-rose-500 text-center py-10">Gagal memuat data template.</p>');
                }
            });
        }

        // --- 3. RENDER LIST SIDEBAR ---
        function renderList() {
            let html = '';
            templatesData.forEach(t => {
                let title = t.session_state.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                let activeClass = currentActiveState === t.session_state ? 'list-active' : 'border-transparent hover:bg-admin-bg';
                let isModified = (t.message_text || '').trim() !== (t.default_message_text || '').trim();

                html += `
                    <div class="template-item p-3 border-2 rounded-2xl cursor-pointer transition-all ${activeClass}" data-state="${t.session_state}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-sm text-admin-textMain">${title}</span>
                            ${isModified
                                ? '<span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-purple-100 text-admin-sideBg">Diubah</span>'
                                : '<div class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_5px_#10B981]" title="Default"></div>'}
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
                parsedText = parsedText.replaceAll(key, dummyVars[key]);
            });
            parsedText = parsedText.replace(/\*(.*?)\*/g, '<span class="wa-bold">$1</span>');
            parsedText = parsedText.replace(/_(.*?)_/g, '<span class="wa-italic">$1</span>');
            parsedText = parsedText.replace(/~(.*?)~/g, '<span class="wa-strikethrough">$1</span>');
            return parsedText;
        }

        function updateDefaultBadgeAndButton() {
            const data = templatesData.find(t => t.session_state === currentActiveState);
            if (!data) return;

            const currentText = $('#edit-message').val().trim();
            const defaultText = (data.default_message_text || '').trim();
            const badge = $('#badge-status-default');
            const btnReset = $('#btn-reset-default');

            if (currentText === defaultText) {
                badge.text('Default Sistem')
                     .removeClass('bg-purple-100 text-admin-sideBg border-purple-200')
                     .addClass('bg-emerald-50 text-emerald-600 border-emerald-200');
                btnReset.prop('disabled', true);
            } else {
                badge.text('Telah Dikustomisasi')
                     .removeClass('bg-emerald-50 text-emerald-600 border-emerald-200')
                     .addClass('bg-purple-100 text-admin-sideBg border-purple-200');
                btnReset.prop('disabled', false);
            }
        }

        function validateVariables() {
            const currentText = $('#edit-message').val();
            let missingVars = [];

            currentRequiredVars.forEach(v => {
                if (!currentText.includes(v)) {
                    missingVars.push(v);
                }
            });

            const btnSave = $('#btn-save');
            const errorBox = $('#var-error-msg');
            const errorText = $('#var-error-text');

            if (missingVars.length > 0) {
                errorText.html(`Anda menghapus variabel wajib: <b>${missingVars.join(', ')}</b>. Silakan masukkan kembali!`);
                errorBox.removeClass('hidden');
                btnSave.prop('disabled', true);
            } else {
                errorBox.addClass('hidden');
                btnSave.prop('disabled', false);
            }
        }

        function updatePreview() {
            const rawText = $('#edit-message').val();
            const formattedHTML = parseWaFormat(rawText);
            $('#preview-text').html(formattedHTML);

            validateVariables();
            updateDefaultBadgeAndButton();
        }

        // --- 5. EVENT LISTENERS ---
        $(document).on('click', '.template-item', function() {
            const state = $(this).data('state');
            currentActiveState = state;
            renderList();

            const data = templatesData.find(t => t.session_state === state);
            if(data) {
                $('#editor-empty').fadeOut(200);

                let title = data.session_state.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                $('#edit-title').text(title);
                $('#edit-desc').text(data.description);
                $('#edit-session-state').val(data.session_state);
                $('#edit-message').val(data.message_text);

                // Ekstrak variabel wajib dari default_message_text (Opsi 1)
                let foundVars = (data.default_message_text || '').match(/\{[a-zA-Z0-9_]+\}/g);
                if (foundVars) {
                    currentRequiredVars = [...new Set(foundVars)];

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

        // Klik Kembalikan ke Default (Langsung Reset ke DB via API)
        $('#btn-reset-default').on('click', function() {
            const data = templatesData.find(t => t.session_state === currentActiveState);
            if (!data) return;

            if (!confirm('Kembalikan isi pesan ini ke teks default bawaan sistem?')) return;

            const btn = $(this);
            const ogText = $('#btn-reset-text').text();
            $('#btn-reset-text').text('Mereset...');
            btn.prop('disabled', true);

            $.ajax({
                url: '/api/admin/bot-templates/reset',
                type: 'POST',
                data: {
                    session_state: currentActiveState
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const idx = templatesData.findIndex(t => t.session_state === currentActiveState);
                        if (idx !== -1) {
                            templatesData[idx].message_text = res.data.message_text;
                        }
                        $('#edit-message').val(res.data.message_text);
                        updatePreview();
                        renderList();
                        showToast(res.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Gagal mereset template.', false);
                    btn.prop('disabled', false);
                },
                complete: function() {
                    $('#btn-reset-text').text(ogText);
                }
            });
        });

        // Insert Variabel ke Cursor Position
        $(document).on('click', '.btn-insert-var', function() {
            const varText = $(this).data('var');
            const textarea = $('#edit-message')[0];

            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const text = textarea.value;

            textarea.value = text.substring(0, startPos) + varText + text.substring(endPos, text.length);

            textarea.selectionStart = startPos + varText.length;
            textarea.selectionEnd = startPos + varText.length;
            textarea.focus();

            updatePreview();
        });

        $('#edit-message').on('input', updatePreview);

        // Submit Simpan Perubahan ke DB via API
        $('#form-template').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btn-save');
            const ogHtml = btn.html();

            btn.html('<i class="ph ph-spinner-gap animate-spin text-xl"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '/api/admin/bot-templates/update',
                type: 'POST',
                data: {
                    session_state: $('#edit-session-state').val(),
                    message_text: $('#edit-message').val()
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const idx = templatesData.findIndex(t => t.session_state === currentActiveState);
                        if (idx !== -1) {
                            templatesData[idx].message_text = res.data.message_text;
                        }
                        updateDefaultBadgeAndButton();
                        renderList();
                        showToast(res.message, true);
                    }
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Gagal menyimpan template.', false);
                },
                complete: function() {
                    btn.html(ogHtml).prop('disabled', false);
                    validateVariables();
                }
            });
        });

        loadTemplates();
    });
</script>
@endpush
