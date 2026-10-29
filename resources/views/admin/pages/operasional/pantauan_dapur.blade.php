@extends('admin.admin_master')

@section('title', 'Pantauan Dapur (KDS) - Admin Raya Kitchen')
@section('header_title', 'Pantauan Dapur (Kitchen Display System)')

@section('css')
<style>
    /* Custom Scrollbar Dinamis (Di atas & Bawah) */
    .custom-scrollbar::-webkit-scrollbar { height: 10px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #F5F2F8; border-radius: 10px; box-shadow: inset 0 0 5px rgba(0,0,0,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #D8B4E2; border-radius: 10px; border: 2px solid #F5F2F8; cursor: pointer; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9368B7; }

    /* Ergonomic Drag & Drop */
    .kanban-card { cursor: grab; touch-action: none; user-select: none; transition: all 0.2s ease; border: 2px solid transparent; }
    .kanban-card:active { cursor: grabbing; }

    .kanban-card.is-dragging {
        opacity: 0.9;
        transform: scale(0.95) rotate(2deg);
        box-shadow: 0 15px 30px rgba(147,104,183,0.3);
        border-color: #9368B7 !important;
        z-index: 100;
    }

    /* Efek Glow Rombongan (Batch Selection) - DIJAMIN KELIATAN SEKARANG */
    .batch-highlight {
        background-color: #FAF5FF !important;
        border-color: #B38CDA !important;
        transform: scale(0.98);
        box-shadow: 0 0 0 3px rgba(147,104,183,0.3), 0 10px 15px -3px rgba(0,0,0,0.1) !important;
        opacity: 1 !important;
    }

    /* Animasi denyut buat highlight */
    @keyframes pulse-glow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    .batch-highlight { animation: pulse-glow 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

    .kanban-col { transition: background-color 0.2s ease, border-color 0.2s ease; border: 2px solid transparent; }
    .kanban-col.drag-over {
        background-color: #F5F2F8;
        border-style: dashed;
        border-color: #9368B7;
    }
</style>
@endsection

@section('content')
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-admin-textMain mb-1">Manajemen Produksi Dapur</h2>
            <div class="text-sm font-medium text-admin-textMuted max-w-4xl leading-relaxed bg-white p-3 rounded-xl border border-admin-border shadow-sm">
                <p><b class="text-admin-sideBg">📱 Mode HP:</b> Gunakan tombol di bagian bawah kartu untuk memindahkan pesanan.</p>
                <p><b class="text-admin-sideBg">💻 Mode PC:</b> Bisa Drag & Drop. Memindahkan item antara <b class="text-admin-textMain">Pool ↔ Antrean Masak</b> otomatis memindahkan <b class="text-rose-500">satu nota sekaligus (Rombongan)</b>. Memindahkan ke <b class="text-admin-textMain">Diproses/Selesai</b> berlaku <b class="text-blue-500">1 per 1</b>.</p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0 self-start md:self-center">
            <button onclick="renderKanban()" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-admin-border text-admin-textMuted hover:text-admin-sideHover rounded-xl font-black text-sm transition-all interactive-element focus-visible:outline-none shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
                <i class="ph ph-arrows-clockwise text-lg"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Peringatan UI (Toast) -->
    <div id="toast-alert" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 bg-rose-600 text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3 font-bold text-sm transform -translate-y-10 opacity-0 pointer-events-none transition-all duration-300">
        <i class="ph ph-warning-circle text-xl"></i><span id="toast-msg">Pesan Error</span>
    </div>
    <div id="toast-success" class="fixed top-24 left-1/2 -translate-x-1/2 z-50 bg-emerald-600 text-white px-6 py-3 rounded-2xl shadow-xl flex items-center gap-3 font-bold text-sm transform -translate-y-10 opacity-0 pointer-events-none transition-all duration-300">
        <i class="ph ph-check-circle text-xl"></i><span id="toast-success-msg">Berhasil disimpan</span>
    </div>

    <!-- SCROLLBAR ATAS (Dummy Controller) -->
    <div id="top-scrollbar-container" class="w-full overflow-x-auto custom-scrollbar mb-3">
        <div id="top-scrollbar-content" class="h-[1px]"></div>
    </div>

    <!-- KANBAN BOARD WRAPPER -->
    <div class="flex gap-5 h-[calc(100vh-270px)] min-h-[550px] overflow-x-auto hide-scrollbar pb-4" id="kanban-board">

        <!-- KOLOM 1: POOL PESANAN -->
        <div class="kanban-col flex flex-col bg-gray-50/80 rounded-[2rem] border-admin-border shadow-sm min-w-[300px] w-[320px] shrink-0" data-status="pool" id="col-pool">
            <div class="p-4 lg:p-5 border-b border-admin-border shrink-0">
                <div class="flex justify-between items-center mb-1">
                    <h3 class="font-black text-admin-textMain text-sm uppercase tracking-wider flex items-center gap-2">
                        <i class="ph ph-tray text-lg text-admin-textMuted"></i> Pool Pesanan
                    </h3>
                    <span class="px-2 py-0.5 bg-gray-200 text-admin-textMuted rounded-md text-xs font-black" id="count-pool">0</span>
                </div>
                <p class="text-[11px] font-bold text-admin-textMuted">Pesanan menunggu dijadwalkan</p>
            </div>
            <div class="kanban-items flex-1 p-3 space-y-3 overflow-y-auto hide-scrollbar custom-scrollbar" id="items-pool"></div>
        </div>

        <!-- KOLOM 2: STAGING (Antrean Masak) -->
        <div class="kanban-col flex flex-col bg-admin-bg/60 rounded-[2rem] border-admin-accent/40 shadow-sm min-w-[300px] w-[320px] shrink-0" data-status="staging" id="col-staging">
            <div class="p-4 lg:p-5 border-b border-admin-border shrink-0 bg-admin-sideBg/10 rounded-t-[2rem]">
                <div class="flex justify-between items-center mb-1">
                    <h3 class="font-black text-admin-sideBg text-sm uppercase tracking-wider flex items-center gap-2">
                        <i class="ph ph-fire text-lg"></i> Antrean Masak
                    </h3>
                    <span class="px-2 py-0.5 bg-admin-sideBg text-white rounded-md text-xs font-black shadow-sm" id="count-staging">0</span>
                </div>
                <p class="text-[11px] font-bold text-admin-sideBg/70">Atur prioritas (atas-bawah)</p>
            </div>
            <div class="kanban-items flex-1 p-3 space-y-3 overflow-y-auto hide-scrollbar custom-scrollbar" id="items-staging"></div>
        </div>

        <!-- KOLOM 3: DIPROSES -->
        <div class="kanban-col flex flex-col bg-blue-50/50 rounded-[2rem] border-blue-100 shadow-sm min-w-[300px] w-[320px] shrink-0" data-status="diproses" id="col-diproses">
            <div class="p-4 lg:p-5 border-b border-blue-100 shrink-0">
                <div class="flex justify-between items-center mb-1">
                    <h3 class="font-black text-blue-700 text-sm uppercase tracking-wider flex items-center gap-2">
                        <i class="ph ph-cooking-pot text-lg"></i> Diproses
                    </h3>
                    <span class="px-2 py-0.5 bg-blue-500 text-white rounded-md text-xs font-black shadow-sm" id="count-diproses">0</span>
                </div>
                <p class="text-[11px] font-bold text-blue-500/80">Item sedang dibuat (1 per 1)</p>
            </div>
            <div class="kanban-items flex-1 p-3 space-y-3 overflow-y-auto hide-scrollbar custom-scrollbar" id="items-diproses"></div>
        </div>

        <!-- KOLOM 4: SELESAI -->
        <div class="kanban-col flex flex-col bg-emerald-50/50 rounded-[2rem] border-emerald-100 shadow-sm min-w-[300px] w-[320px] shrink-0" data-status="selesai" id="col-selesai">
            <div class="p-4 lg:p-5 border-b border-emerald-100 shrink-0">
                <div class="flex justify-between items-center mb-1">
                    <h3 class="font-black text-emerald-700 text-sm uppercase tracking-wider flex items-center gap-2">
                        <i class="ph ph-package text-lg"></i> Siap Diantar
                    </h3>
                    <span class="px-2 py-0.5 bg-emerald-500 text-white rounded-md text-xs font-black shadow-sm" id="count-selesai">0</span>
                </div>
                <p class="text-[11px] font-bold text-emerald-600/80">Item siap diserahkan</p>
            </div>
            <div class="kanban-items flex-1 p-3 space-y-3 overflow-y-auto hide-scrollbar custom-scrollbar" id="items-selesai"></div>
        </div>

    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {

        // --- 1. SETUP TANGGAL & SCROLLBAR ---
        const today = new Date();
        const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1);
        const lusa = new Date(today); lusa.setDate(lusa.getDate() + 2);
        const formatStr = (d) => d.toISOString().split('T')[0];

        const strToday = formatStr(today);
        const strTomorrow = formatStr(tomorrow);

        // Sync Scrollbar Atas & Bawah
        const board = document.getElementById('kanban-board');
        const topScroll = document.getElementById('top-scrollbar-container');
        const topContent = document.getElementById('top-scrollbar-content');

        function syncScrollbars() {
            topContent.style.width = board.scrollWidth + 'px';
        }

        topScroll.addEventListener('scroll', () => { board.scrollLeft = topScroll.scrollLeft; });
        board.addEventListener('scroll', () => { topScroll.scrollLeft = board.scrollLeft; });

        // --- 2. DATA DUMMY ORDER ITEMS (MIRIP DB ASLI) ---
        let kitchenItems = [
            { order_item_id: 101, order_id: 10, order_number: 'RK-1001', customer_name: 'Budi Santoso', product_name: 'Roti Sisir Mentega', quantity: 2, deadline_date: strToday, deadline_time: '15:00', item_status: 'staging', is_staged: true, kitchen_priority: 1, is_locked: true },
            { order_item_id: 102, order_id: 10, order_number: 'RK-1001', customer_name: 'Budi Santoso', product_name: 'Roti Tawar Gandum', quantity: 1, deadline_date: strToday, deadline_time: '15:00', item_status: 'staging', is_staged: true, kitchen_priority: 2, is_locked: true },

            { order_item_id: 103, order_id: 11, order_number: 'RK-1002', customer_name: 'Siti Aminah', product_name: 'Roti Coklat Keju', quantity: 5, deadline_date: formatStr(lusa), deadline_time: '10:00', item_status: 'pool', is_staged: false, kitchen_priority: 0, is_locked: false },
            { order_item_id: 104, order_id: 11, order_number: 'RK-1002', customer_name: 'Siti Aminah', product_name: 'Kue Tart', quantity: 1, deadline_date: formatStr(lusa), deadline_time: '10:00', item_status: 'pool', is_staged: false, kitchen_priority: 0, is_locked: false },

            { order_item_id: 105, order_id: 12, order_number: 'RK-1003', customer_name: 'Joko Anwar', product_name: 'Croissant Coklat', quantity: 3, deadline_date: strTomorrow, deadline_time: '09:00', item_status: 'diproses', is_staged: true, kitchen_priority: 0, is_locked: true }
        ];

        // --- FUNGSI TOAST ---
        function showToast(msg, isError = true) {
            const toast = isError ? $('#toast-alert') : $('#toast-success');
            const targetMsg = isError ? $('#toast-msg') : $('#toast-success-msg');
            targetMsg.text(msg);
            toast.removeClass('-translate-y-10 opacity-0 pointer-events-none').addClass('translate-y-0 opacity-100');
            setTimeout(() => { toast.removeClass('translate-y-0 opacity-100').addClass('-translate-y-10 opacity-0 pointer-events-none'); }, 3000);
        }

        // --- 3. RENDER KANBAN CARDS ---
        window.renderKanban = function() {
            $('.kanban-items').empty();
            let counts = { pool: 0, staging: 0, diproses: 0, selesai: 0 };

            kitchenItems.sort((a, b) => a.kitchen_priority - b.kitchen_priority);

            kitchenItems.forEach(item => {
                let badge = '';
                if (item.deadline_date === strToday) {
                    badge = `<span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 rounded text-[9px] font-black uppercase tracking-widest">Hari Ini</span>`;
                } else if (item.deadline_date === strTomorrow) {
                    badge = `<span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 border border-amber-200 rounded text-[9px] font-black uppercase tracking-widest">Besok</span>`;
                } else {
                    badge = `<span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 border border-gray-200 rounded text-[9px] font-black uppercase tracking-widest">${item.deadline_date}</span>`;
                }

                let lockIcon = item.is_locked && item.item_status === 'staging' ? `<i class="ph ph-lock-key text-rose-500" title="Wajib hari ini/besok"></i>` : '';

                // Bikin Action Button Kecil & Kompak
                let actionButtons = '';
                if (item.item_status === 'pool') {
                    actionButtons = `<button type="button" onclick="moveItemViaButton(${item.order_item_id}, 'staging')" class="w-full px-2 py-1.5 mt-1.5 bg-admin-sideBg text-white rounded-lg text-[10px] font-black hover:bg-admin-sideHover transition-colors flex items-center gap-1 justify-center shadow-sm"><i class="ph ph-fire"></i> Tarik Antrean</button>`;
                } else if (item.item_status === 'staging') {
                    let btnBackToPool = !item.is_locked ? `<button type="button" onclick="moveItemViaButton(${item.order_item_id}, 'pool')" class="px-3 py-1.5 bg-gray-200 text-gray-700 border border-gray-300 rounded-lg text-[12px] font-black hover:bg-gray-300 hover:text-gray-900 transition-colors shadow-sm flex items-center justify-center" title="Kembalikan ke Pool"><i class="ph ph-arrow-left font-bold text-sm"></i></button>` : '';
                    actionButtons = `
                        <div class="flex gap-1.5 mt-1.5">
                            ${btnBackToPool}
                            <button type="button" onclick="moveItemViaButton(${item.order_item_id}, 'diproses')" class="flex-1 px-2 py-1.5 bg-blue-500 text-white rounded-lg text-[10px] font-black hover:bg-blue-600 transition-colors flex items-center gap-1 justify-center shadow-sm"><i class="ph ph-cooking-pot"></i> Proses</button>
                        </div>
                    `;
                } else if (item.item_status === 'diproses') {
                    actionButtons = `<button type="button" onclick="moveItemViaButton(${item.order_item_id}, 'selesai')" class="w-full px-2 py-1.5 mt-1.5 bg-emerald-500 text-white rounded-lg text-[10px] font-black hover:bg-emerald-600 transition-colors flex items-center gap-1 justify-center shadow-sm"><i class="ph ph-check-circle"></i> Selesai</button>`;
                }

                let cardHtml = `
                    <div class="kanban-card bg-white p-3 rounded-2xl border border-admin-border shadow-sm flex flex-col gap-1" draggable="true" id="card-${item.order_item_id}" data-id="${item.order_item_id}" data-order="${item.order_number}" data-locked="${item.is_locked}">

                        <div class="flex justify-between items-center mb-0.5">
                            <span class="text-[10px] font-black text-admin-textMuted uppercase tracking-wider flex items-center gap-1">${item.order_number} ${lockIcon}</span>
                            ${badge}
                        </div>

                        <h4 class="font-black text-admin-sideBg text-[13px] leading-tight mb-1">
                            ${item.quantity}x ${item.product_name}
                        </h4>

                        <!-- Gabungin Nama Pelanggan & Jam Kirim biar hemat ruang vertikal -->
                        <div class="flex justify-between items-center text-[10px] font-bold text-admin-textMuted border-t border-dashed border-admin-border pt-1.5">
                            <span class="flex items-center gap-1 truncate max-w-[120px]"><i class="ph ph-user"></i> ${item.customer_name}</span>
                            <span class="flex items-center gap-1 text-admin-textMain"><i class="ph ph-clock text-xs"></i> ${item.deadline_time}</span>
                        </div>

                        <!-- Action Buttons -->
                        ${actionButtons}
                    </div>
                `;

                $(`#items-${item.item_status}`).append(cardHtml);
                counts[item.item_status]++;
            });

            $('#count-pool').text(counts.pool);
            $('#count-staging').text(counts.staging);
            $('#count-diproses').text(counts.diproses);
            $('#count-selesai').text(counts.selesai);

            attachDragEvents();
            setTimeout(syncScrollbars, 100);
        };

        // --- 4. ENGINE DRAG & DROP ---
        let draggedElement = null;
        let dragSourceStatus = null;
        let dragOrderNum = null;

        function attachDragEvents() {
            const cards = document.querySelectorAll('.kanban-card');
            const columns = document.querySelectorAll('.kanban-items');

            cards.forEach(card => {
                card.removeEventListener('dragstart', handleDragStart);
                card.removeEventListener('dragend', handleDragEnd);
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            columns.forEach(col => {
                col.removeEventListener('dragover', handleDragOver);
                col.removeEventListener('dragleave', handleDragLeave);
                col.removeEventListener('drop', handleDrop);
                col.addEventListener('dragover', handleDragOver);
                col.addEventListener('dragleave', handleDragLeave);
                col.addEventListener('drop', handleDrop);
            });
        }

        function handleDragStart(e) {
            draggedElement = this;
            dragSourceStatus = this.parentElement.parentElement.getAttribute('data-status');
            dragOrderNum = this.getAttribute('data-order');

            this.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.id);

            // HIGHLIGHT BATCH LANGSUNG NYALA PAS DIKLIK/DRAG
            if (dragSourceStatus === 'pool' || dragSourceStatus === 'staging') {
                $(`.kanban-card[data-order="${dragOrderNum}"]`).not(this).addClass('batch-highlight');
            }
        }

        function handleDragEnd(e) {
            this.classList.remove('is-dragging');
            $('.kanban-col').removeClass('drag-over');
            $('.kanban-card').removeClass('batch-highlight'); // Bersihin glow
            draggedElement = null;
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.kanban-card:not(.is-dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.parentElement.classList.add('drag-over');

            const afterElement = getDragAfterElement(this, e.clientY);
            if (afterElement == null) {
                this.appendChild(draggedElement);
            } else {
                this.insertBefore(draggedElement, afterElement);
            }
        }

        function handleDragLeave(e) {
            this.parentElement.classList.remove('drag-over');
        }

        function handleDrop(e) {
            e.preventDefault();
            this.parentElement.classList.remove('drag-over');
            $('.kanban-card').removeClass('batch-highlight'); // Bersihin Glow

            const newStatus = this.parentElement.getAttribute('data-status');
            const dataId = parseInt(draggedElement.getAttribute('data-id'));

            processItemMove(dataId, newStatus, this);
        }

        // --- 5. LOGIKA UTAMA PEMINDAHAN (Bisa via Drag atau Tombol) ---
        window.moveItemViaButton = function(itemId, newStatus) {
            // Kita cari elemen DOM nya biar bisa direkalkulasi prioritasnya
            const container = document.getElementById(`items-${newStatus}`);
            processItemMove(itemId, newStatus, container);
        }

        function processItemMove(itemId, newStatus, containerEl) {
            const itemIndex = kitchenItems.findIndex(o => o.order_item_id === itemId);
            if(itemIndex === -1) return;

            const oldStatus = kitchenItems[itemIndex].item_status;
            const orderNum = kitchenItems[itemIndex].order_number;
            const isLocked = kitchenItems[itemIndex].is_locked;

            // VALIDASI: Kalau locked TIDAK BOLEH dikembalikan ke Pool
            if (isLocked && newStatus === 'pool') {
                showToast('Akses Ditolak! Item yang tenggatnya hari ini/besok wajib masuk Antrean.', true);
                renderKanban();
                return;
            }

            $('#global-loader').removeClass('opacity-0 pointer-events-none');
            $('#loader-bar').css('transform', 'scaleX(0.7)');

            setTimeout(() => {
                // LOGIKA BATCHING (Pool ↔ Staging)
                if ((oldStatus === 'pool' && newStatus === 'staging') || (oldStatus === 'staging' && newStatus === 'pool')) {
                    kitchenItems.forEach(i => {
                        if (i.order_number === orderNum && i.item_status === oldStatus) {
                            i.item_status = newStatus;
                            i.is_staged = (newStatus === 'staging');
                        }
                    });
                    showToast(newStatus === 'staging' ? `Semua item nota #${orderNum} masuk Antrean!` : `Semua item nota #${orderNum} dikembalikan ke Pool.`, false);
                }
                // LOGIKA INDIVIDU
                else {
                    kitchenItems[itemIndex].item_status = newStatus;
                    if(oldStatus !== newStatus) {
                        if (newStatus === 'diproses') showToast(`Memproses 1 item...`, false);
                        else if (newStatus === 'selesai') showToast(`1 item selesai!`, false);
                    }
                }

                // Kalkulasi ulang Priority (berdasarkan DOM container)
                if(containerEl) {
                    const currentCards = [...containerEl.querySelectorAll('.kanban-card')];
                    currentCards.forEach((cardEl, idx) => {
                        const cardDataId = parseInt(cardEl.getAttribute('data-id'));
                        let findIdx = kitchenItems.findIndex(o => o.order_item_id === cardDataId);
                        if(findIdx > -1) kitchenItems[findIdx].kitchen_priority = idx + 1;
                    });
                }

                renderKanban();

                $('#loader-bar').css('transform', 'scaleX(1)');
                setTimeout(() => {
                    $('#global-loader').addClass('opacity-0 pointer-events-none');
                    setTimeout(() => $('#loader-bar').css('transform', 'scaleX(0)'), 200);
                }, 300);

            }, 400); // Simulasi delay AJAX API Controller
        }

        // Init
        renderKanban();
    });
</script>
@endpush
