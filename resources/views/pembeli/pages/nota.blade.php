@extends('pembeli.pembeli_master')

@section('content')
<div class="min-h-screen bg-[#F7F4F9] py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">
        
        {{-- Header & Action Button --}}
        <div class="flex justify-between items-center print:hidden">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Bukti Transaksi</span>
                <h1 class="text-2xl font-extrabold text-gray-900 mt-2">Nota Digital</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-xl transition shadow-sm flex items-center gap-2">
                    🖨️ Cetak Nota
                </button>
            </div>
        </div>

        {{-- Card Struk / Nota Digital (Dokumen Desain Hal 20) --}}
        <div class="bg-white p-6 sm:p-10 rounded-2xl shadow-sm border border-purple-100 relative overflow-hidden">
            
            {{-- Watermark Status Paid --}}
            <div class="absolute top-6 right-6 border-2 border-green-500 text-green-600 font-extrabold text-xs px-3 py-1 rounded-md transform rotate-12 opacity-80">
                LUNAS / PAID
            </div>

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
                    <p class="font-bold text-gray-800 mt-0.5">#RK-20261007-01</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-medium">Tanggal</p>
                    <p class="font-semibold text-gray-700 mt-0.5">07 Okt 2026, 14:00 WIB</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">Penerima</p>
                    <p class="font-semibold text-gray-700 mt-0.5">Andi Pratama</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 uppercase font-medium">Metode Pembayaran</p>
                    <p class="font-semibold text-purple-700 mt-0.5">Transfer Bank</p>
                </div>
            </div>

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
                        <tr>
                            <td class="py-3 font-semibold text-gray-800">Roti Coklat</td>
                            <td class="py-3 text-center text-gray-600">2</td>
                            <td class="py-3 text-right text-gray-600">Rp 10.000</td>
                            <td class="py-3 text-right font-medium text-gray-800">Rp 20.000</td>
                        </tr>
                        <tr>
                            <td class="py-3 font-semibold text-gray-800">Roti Sosis</td>
                            <td class="py-3 text-center text-gray-600">1</td>
                            <td class="py-3 text-right text-gray-600">Rp 12.000</td>
                            <td class="py-3 text-right font-medium text-gray-800">Rp 12.000</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Subtotal & Grand Total --}}
            <div class="pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span>Rp 32.000</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Biaya Pengiriman</span>
                    <span>Rp 5.000</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-gray-100 font-extrabold text-base">
                    <span class="text-gray-900">Total Pembayaran</span>
                    <span class="text-xl text-purple-700">Rp 37.000</span>
                </div>
            </div>

            {{-- Footer Struk --}}
            <div class="mt-8 pt-6 border-t border-dashed border-gray-200 text-center text-xs text-gray-400">
                <p>Terima kasih telah berbelanja di Raya Kitchen! ❤️</p>
                <p class="mt-1">Simpan nota digital ini sebagai bukti transaksi yang sah.</p>
            </div>

        </div>

    </div>
</div>
@endsection