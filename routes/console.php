<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Transaction;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule untuk membatalkan pesanan unpaid > 60 menit
Schedule::call(function () {
    $expiredTransactions = Transaction::with('order')
        ->where('payment_status', 'unpaid')
        ->where('created_at', '<=', Carbon::now()->subMinutes(60))
        ->get();

    foreach ($expiredTransactions as $trx) {
        $trx->update(['payment_status' => 'failed']);

        if ($trx->order && $trx->order->status === 'pending') {
            $trx->order->update([
                'status' => 'dibatalkan',
                'cancellation_reason' => 'Otomatis dibatalkan sistem: Melewati batas waktu pembayaran QRIS 60 menit.'
            ]);
        }
    }
})->everyMinute();
