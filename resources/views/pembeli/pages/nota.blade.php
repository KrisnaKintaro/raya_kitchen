@extends('pembeli.pembeli_master')

@section('content')

{{-- CSS KHUSUS PRINT --}}
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
            <button onclick="window.print()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-xl transition shadow-sm">Cetak Nota</button>
        </div>

        {{-- Card Struk / Nota Digital --}}
        <div id="nota-card" class="bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 relative overflow-hidden">
            
            {{-- Watermark Status --}}
            @if($order->transaction->payment_method === 'cod')
                <div id="status-badge" class="absolute top-6 right-6 border-2 border-amber-500 text-amber-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 bg-amber-50">COD - MENUNGGU PEMBAYARAN</div>
            @else
                <div id="status-badge" class="absolute top-6 right-6 border-2 border-purple-500 text-purple-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 bg-purple-50">TRANSFER - {{ strtoupper($order->transaction->payment_status) }}</div>
            @endif

            {{-- Header Toko --}}
            <div class="text-center border-b border-gray-100 pb-6">
                <h2 class="text-2xl font-black tracking-tight text-purple-900">RAYA KITCHEN</h2>
                <p class="text-xs text-gray-500 mt-1">Bakery & Pastry Fresh Everyday</p>
                <p class="text-xs text-gray-400">Jl. Raya Kediri No. 88, Jawa Timur | WA: +62 812 3456 7890</p>
            </div>

            {{-- Info Transaksi --}}
            <div class="grid grid-cols-2 gap-4 py-6 border-b border-gray-100 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">No. Transaksi</p>
                    <p class="font-bold text-gray-800 mt-0.5">#{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-medium">Tanggal</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }} WIB</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">Penerima</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $order->recipient_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-medium">Metode Pembayaran</p>
                    <p class="font-semibold {{ $order->transaction->payment_method === 'cod' ? 'text-amber-700' : 'text-purple-700' }} mt-0.5">
                        {{ $order->transaction->payment_method === 'cod' ? 'COD (Bayar di Tempat)' : 'Transfer Bank' }}
                    </p>
                </div>
                <div class="col-span-2 pt-2 border-t border-gray-50">
                    <p class="text-xs text-gray-400 uppercase font-medium">Alamat Pengiriman</p>
                    <p class="font-medium text-gray-700 mt-0.5">{{ $order->shipping_address }}</p>
                </div>
            </div>

            {{-- Kotak Informasi Rekening / Keterangan COD --}}
            @if($order->transaction->payment_method === 'transfer')
                            <div class="my-6 p-4 bg-purple-50 rounded-2xl border border-purple-200 text-sm">
                                <p class="font-bold text-purple-900 mb-2">Silakan Transfer Pembayaran Ke:</p>
                                <div class="space-y-2 text-gray-700 text-xs sm:text-sm font-medium">
                                    @foreach($banks as $bank)
                                        <div class="flex justify-between items-center p-2 bg-white rounded-lg border border-purple-100 shadow-sm">
                                            <span class="text-gray-500">Bank {{ $bank->bank_name }}:</span>
                                            <span class="font-bold text-gray-900 text-right">{{ $bank->account_number }}<br><span class="text-xs text-gray-400">a.n {{ $bank->account_name }}</span></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

            {{-- Table Item Roti --}}
            <div class="py-6 border-b border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 border-b border-gray-100 pb-2">
                            <th class="font-medium pb-2">Item</th>
                            <th class="font-medium text-center pb-2">Qty</th>
                            <th class="font-medium text-right pb-2">Harga</th>
                            <th class="font-medium text-right pb-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-3 font-semibold text-gray-800">{{ $item->productVariant->variant_name ?? 'Produk' }}</td>
                            <td class="py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-3 text-right text-gray-600">Rp {{ number_format($item->price_at_order, 0, ',', '.') }}</td>
                            <td class="py-3 text-right font-medium text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Subtotal & Grand Total --}}
            <div class="pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>Rp {{ number_format($order->total_amount - 10000, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Biaya Pengiriman</span><span>Rp 10.000</span></div>
                <div class="flex justify-between items-center pt-3 border-t border-gray-100 font-extrabold text-base">
                    <span class="text-gray-900">Total Pembayaran</span>
                    <span class="text-xl text-purple-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Tombol Aksi Konfirmasi --}}
            <div class="mt-6 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3 print:hidden">
                
                @if($order->transaction->payment_method === 'transfer')
                    <button type="button" onclick="$('#uploadModal').removeClass('hidden')" id="btn-upload" class="flex-1 py-3 px-4 bg-purple-600 text-white font-bold rounded-xl text-center text-sm transition shadow-md">Upload Bukti Transfer</button>
                    <div id="status-terkirim" class="hidden flex-1 py-3 px-4 bg-green-100 text-green-700 font-bold rounded-xl text-center text-sm flex items-center justify-center gap-2">✓ Bukti Transfer Terkirim</div>
                @else
                    <div class="flex-1 py-3 px-4 bg-amber-100 text-amber-800 font-bold rounded-xl text-center text-sm flex items-center justify-center gap-2">Bayar Tunai Saat Kurir Tiba</div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODAL UPLOAD BUKTI TRANSFER --}}
    @if($order->transaction->payment_method === 'transfer')
    <div id="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 print:hidden hidden">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex justify-between items-center border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">Upload Bukti Pembayaran</h3>
                <button type="button" onclick="$('#uploadModal').addClass('hidden')" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            <form onsubmit="submitBukti(event)" class="space-y-4">
                <input type="file" accept="image/*" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 cursor-pointer">
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="$('#uploadModal').addClass('hidden')" class="flex-1 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold transition">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-purple-600 text-white rounded-xl text-xs font-bold transition shadow-sm">Kirim Bukti</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

{{-- SCRIPT JAVASCRIPT MODAL & STATUS --}}
<script>
function submitBukti(event) {
    event.preventDefault();
    $('#uploadModal').addClass('hidden');
    
    let btnUpload = document.getElementById('btn-upload');
    let statusTerkirim = document.getElementById('status-terkirim');
    if (btnUpload) btnUpload.classList.add('hidden');
    if (statusTerkirim) statusTerkirim.classList.remove('hidden');
    
    let badge = document.getElementById('status-badge');
    if (badge) {
        badge.className = "absolute top-6 right-6 border-2 border-blue-500 text-blue-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 opacity-90 bg-blue-50";
        badge.innerText = "TRANSFER - MENUNGGU VERIFIKASI";
    }
}
</script>
@endsection