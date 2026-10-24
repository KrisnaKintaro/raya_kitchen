@extends('pembeli.pembeli_master')

@section('content')
<style>
@media print {
    body * { visibility: hidden !important; }
    #nota-card, #nota-card * { visibility: visible !important; }
    #nota-card { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; margin: 0 !important; box-shadow: none !important; border: none !important; }
    .print\:hidden { display: none !important; }
}
</style>

<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Header & Action Button --}}
        <div class="flex justify-between items-center print:hidden">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Bukti Transaksi</span>
                <h1 class="text-2xl font-extrabold text-gray-900 mt-2">Nota Digital</h1>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('riwayat-pesanan') }}" class="px-4 py-2.5 bg-white border-2 border-purple-200 hover:bg-purple-50 text-purple-700 font-semibold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>

                <button onclick="window.print()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Nota
                </button>
            </div>
        </div>

        {{-- Card Struk / Nota Digital --}}
        <div id="nota-card" class="bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 relative overflow-hidden">

            {{-- Watermark Status --}}
            @if($order->status === 'dibatalkan')
                <div class="absolute top-6 right-6 border-2 border-red-500 text-red-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 bg-red-50 uppercase">
                    DIBATALKAN
                </div>
            @elseif($order->remaining_amount == 0)
                <div class="absolute top-6 right-6 border-2 border-green-500 text-green-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 bg-green-50 uppercase">
                    LUNAS
                </div>
            @else
                <div class="absolute top-6 right-6 border-2 border-amber-500 text-amber-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 bg-amber-50 uppercase">
                    DP 30% - Sisa Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}
                </div>
            @endif

            {{-- Header Toko --}}
            <div class="text-center border-b border-gray-100 pb-6">
                <h2 class="text-2xl font-black tracking-tight text-purple-900 uppercase">
                    {{ $company->company_name ?? 'RAYA KITCHEN' }}
                </h2>
                <p class="text-xs text-gray-500 mt-1 font-bold">
                    {{ $company->short_description ?? 'Bakery & Pastry Fresh Everyday' }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ $company->address ?? 'Alamat Belum Diatur' }} | WA: +{{ $company->whatsapp_number ?? '-' }}
                </p>
            </div>

            {{-- Info Transaksi --}}
            <div class="grid grid-cols-2 gap-y-4 gap-x-6 py-6 border-b border-gray-100 text-sm">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">No. Transaksi</p>
                    <p class="font-black text-purple-700 mt-0.5 text-base">#{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Tgl Transaksi</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }} WIB</p>
                </div>

                <div class="col-span-2 p-3 bg-purple-50 rounded-xl border border-purple-100">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-[10px] text-purple-500 uppercase font-extrabold tracking-wider">Jadwal Pengiriman / Pengambilan</p>
                    </div>
                    <p class="font-black text-gray-800 text-base">
                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('d F Y') }} - {{ \Carbon\Carbon::parse($order->delivery_time)->format('H:i') }} WIB
                    </p>
                </div>

                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Penerima</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $order->recipient_name }}</p>
                    <p class="text-xs text-gray-500">{{ $order->recipient_whatsapp }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider">Alamat Kirim</p>
                    <p class="font-semibold text-gray-700 mt-0.5 leading-snug">{{ $order->shipping_address }}</p>
                </div>
            </div>

            {{-- Table Item Roti --}}
            <div class="py-6 border-b border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] text-gray-400 border-b border-gray-100 pb-2 uppercase tracking-wider font-extrabold">
                            <th class="pb-3">Produk</th>
                            <th class="text-center pb-3">Qty</th>
                            <th class="text-right pb-3">Harga</th>
                            <th class="text-right pb-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                        <tr class="group">
                            <td class="py-3 pr-2">
                                <p class="font-bold text-gray-800">{{ $item->productVariant->variant_name ?? 'Produk' }}</p>
                                <p class="text-xs text-gray-400">{{ $item->productVariant->product->name ?? 'Kategori' }}</p>
                            </td>
                            <td class="py-3 text-center text-gray-600 font-medium">{{ $item->quantity }}</td>
                            <td class="py-3 text-right text-gray-600">Rp {{ number_format($item->price_at_order, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-bold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Subtotal & Grand Total --}}
            <div class="pt-6 space-y-3 text-sm">
                <div class="flex justify-between text-gray-500 font-medium">
                    <span>Total Harga Barang</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>

                @if($order->remaining_amount > 0)
                    {{-- Kalau Bayar DP --}}
                    <div class="flex justify-between text-gray-500 font-medium">
                        <span>DP Dibayarkan (30%)</span>
                        <span class="text-green-600">Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <span class="font-extrabold text-gray-900 text-base">Sisa Tagihan</span>
                        <span class="text-xl font-black text-red-600">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</span>
                    </div>
                @else
                    {{-- Kalau Lunas --}}
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                        <span class="font-extrabold text-gray-900 text-base">Total Lunas</span>
                        <span class="text-xl font-black text-purple-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Tampilan Bawah (Dibatalkan / Belum Lunas / Lunas) --}}
            @if($order->status === 'dibatalkan')
                <div class="mt-8 pt-6 border-t border-gray-100 print:hidden">
                    <div class="p-5 bg-red-50 rounded-2xl border border-red-100 mb-4">
                        <div class="flex gap-3 items-start mb-3">
                            <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <p class="font-bold text-red-800 text-sm mb-1">Pesanan Dibatalkan</p>
                                <p class="text-xs text-red-600 font-medium">Alasan: {{ $order->cancellation_reason }}</p>
                            </div>
                        </div>

                        {{-- Cek form rekening kalau user udah pernah transfer --}}
                        @if($order->refund_account_number)
                            <div class="mt-4 pt-4 border-t border-red-100">
                                <p class="text-xs font-bold text-red-800 mb-2">Status Pengembalian Dana (Refund)</p>

                                {{-- CEK APAKAH ADMIN SUDAH UPLOAD BUKTI REFUND --}}
                                @if($order->refund_proof)
                                    @php
                                        $refundUrl = $order->refund_proof;
                                        if ($refundUrl && !str_starts_with($refundUrl, 'http')) {
                                            $refundUrl = asset('storage/bukti_refund/' . ltrim($refundUrl, '/'));
                                        }
                                    @endphp
                                    <div class="bg-white p-3 rounded-xl border border-red-100">
                                        <span class="text-xs font-bold text-green-600 block mb-3">✓ Dana berhasil dikembalikan oleh Admin</span>

                                        {{-- Kotak Gambar (Sekarang bisa diklik buat zoom) --}}
                                        <div class="rounded-lg overflow-hidden border border-gray-100 mb-3 bg-gray-50 flex justify-center p-2 cursor-pointer hover:scale-[1.02] transition-transform relative group" onclick="openRefundZoomModal()">
                                            <img src="{{ $refundUrl }}" alt="Bukti Refund" class="max-h-48 w-auto object-contain rounded-md shadow-sm">

                                            {{-- Animasi icon zoom pas di-hover --}}
                                            <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 rounded-lg flex flex-col items-center justify-center transition-opacity">
                                                <div class="bg-white p-2 rounded-full shadow-md text-red-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" onclick="openRefundZoomModal()" class="w-full text-xs font-bold text-red-600 hover:text-red-700 underline block text-center">Lihat Bukti Transfer Ukuran Penuh</button>
                                    </div>

                                    {{-- Modal Zoom Bukti Refund --}}
                                    <div id="refundZoomModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300 print:hidden" onclick="closeRefundZoomModal()">
                                        <div class="relative bg-white p-4 rounded-3xl max-w-[90vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
                                            <button type="button" onclick="closeRefundZoomModal()" class="absolute -top-4 -right-4 w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center shadow-lg font-bold hover:scale-105 active:scale-95 transition-transform">
                                                &times;
                                            </button>
                                            <img src="{{ $refundUrl }}" alt="Bukti Refund Besar" class="w-full h-full object-contain max-h-[80vh] rounded-2xl">
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-xs font-medium text-amber-600 bg-amber-50 p-3 rounded-xl border border-amber-100">
                                        <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Refund sedang diproses admin ke rekening ({{ $order->refund_account_number }}). Maksimal 1x24 jam.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($order->remaining_amount > 0)
                <div class="mt-8 pt-6 border-t border-gray-100 print:hidden">
                    <div class="p-4 bg-red-50 rounded-2xl border border-red-100 mb-4 flex gap-3 items-start">
                        <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold text-red-800 text-sm mb-1">Tagihan Belum Lunas</p>
                            <p class="text-xs text-red-600 font-medium">Pesananmu baru dibayar DP. Silakan lunasi sisa tagihan sebelum pesanan diambil/dikirim.</p>
                        </div>
                    </div>

                    <a href="{{ url('/pembayaran/'.$order->order_number) }}" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl text-center text-sm transition shadow-[0_4px_10px_rgba(239,68,68,0.3)]">
                        Bayar Sisa Tagihan Sekarang
                    </a>
                </div>
            @else
                <div class="mt-8 pt-6 border-t border-gray-100 print:hidden text-center">
                    <p class="text-sm font-bold text-green-600">Pesanan telah lunas. Terima kasih telah berbelanja di Raya Kitchen!</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Fungsi Zoom Bukti Refund
    function openRefundZoomModal() {
        $('#refundZoomModal').removeClass('hidden');
        setTimeout(() => {
            $('#refundZoomModal').removeClass('opacity-0');
            $('#refundZoomModal > div').removeClass('scale-95');
        }, 10);
    }

    function closeRefundZoomModal() {
        $('#refundZoomModal').addClass('opacity-0');
        $('#refundZoomModal > div').addClass('scale-95');
        setTimeout(() => { $('#refundZoomModal').addClass('hidden'); }, 300);
    }
</script>
@endpush
