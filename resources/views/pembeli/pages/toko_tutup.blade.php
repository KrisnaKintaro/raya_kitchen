@extends('pembeli.pembeli_master')
@section('title', 'Toko Sedang Tutup - Raya Kitchen')
@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center py-10 px-4 text-center">
    <div class="bg-buyer-bg p-8 sm:p-10 rounded-[2.5rem] shadow-[inset_0_-4px_6px_rgba(192,153,206,0.1),inset_0_4px_6px_rgba(255,255,255,1),0_20px_40px_rgba(192,153,206,0.2)] border border-white w-full max-w-lg relative overflow-hidden">
        
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-[inset_0_4px_10px_rgba(192,153,206,0.2),0_4px_10px_rgba(192,153,206,0.15)] border-4 border-buyer-bg">
            <svg class="w-10 h-10 text-red-500 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        
        <h2 class="text-2xl font-black text-buyer-textPrimary drop-shadow-sm mb-2">Mohon Maaf, Dapur Sedang Tutup</h2>
        
        <p class="text-buyer-textSecondary text-sm font-semibold mb-8 leading-relaxed">
            <!-- Ini akan menampilkan pesan dinamis dari Middleware (alasan libur / jam tutup) -->
            {{ session('close_reason', 'Saat ini kami sedang di luar jam operasional atau libur.') }}
            <br>Silakan kembali lagi nanti ya cuy!
        </p>
        
        <a href="{{ route('tentang-kami') }}" class="w-full inline-flex justify-center py-3.5 bg-buyer-primary text-white font-black rounded-2xl shadow-[inset_0_-4px_6px_rgba(0,0,0,0.15),inset_0_4px_6px_rgba(255,255,255,0.4),0_8px_20px_rgba(192,153,206,0.4)] hover:-translate-y-1 active:translate-y-0.5 transition-all">
            Lihat Info Raya Kitchen
        </a>
    </div>
</div>
@endsection