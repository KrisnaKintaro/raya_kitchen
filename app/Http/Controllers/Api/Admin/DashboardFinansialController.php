<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardFinansialController extends Controller
{
    /**
     * Endpoint Utama Dashboard Finansial Terpadu
     * GET /api/admin/dashboard-finansial
     */
    public function index(Request $request)
    {
        $endDefault   = now()->format('Y-m-d');
        $startDefault = now()->subDays(6)->format('Y-m-d');

        $startStr    = $request->query('start_date', $startDefault);
        $endStr      = $request->query('end_date', $endDefault);
        $granularity = $request->query('granularity', 'daily'); // daily, weekly, monthly, yearly
        $source      = $request->query('order_source', 'all');  // all, web, whatsapp

        $startDate = Carbon::parse($startStr)->startOfDay();
        $endDate   = Carbon::parse($endStr)->endOfDay();
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->endOfDay();
        }

        // Hitung periode pembanding dengan durasi hari yang sama persis
        $diffDays  = max(1, $startDate->diffInDays($endDate) + 1);
        $prevStart = $startDate->copy()->subDays($diffDays)->startOfDay();
        $prevEnd   = $startDate->copy()->subSecond()->endOfDay();

        // =========================================================================
        // 1. GRAND TOTAL SEMUA LINI MASA (ALL-TIME / KEBAL FILTER APAPUN)
        // =========================================================================
        $allOrders = Order::with('transaction')->get();
        $allTimeSummary = $this->calculateMetrics($allOrders);

        // =========================================================================
        // 2. QUERY UTAMA TERFILTER (TANGGAL UNIVERSAL + CHANNEL ORDER)
        // =========================================================================
        $filteredQuery = Order::with(['transaction', 'items.productVariant.product'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $prevQuery = Order::with('transaction')
            ->whereBetween('created_at', [$prevStart, $prevEnd]);

        if (in_array($source, ['web', 'whatsapp'])) {
            $filteredQuery->where('order_source', $source);
            $prevQuery->where('order_source', $source);
        }

        $filteredOrders = $filteredQuery->orderBy('created_at', 'desc')->get();
        $prevOrders     = $prevQuery->get();

        $filteredSummary = $this->calculateMetrics($filteredOrders);
        $prevSummary     = $this->calculateMetrics($prevOrders);

        $revGrowth = $prevSummary['revenue_total'] > 0
            ? round((($filteredSummary['revenue_total'] - $prevSummary['revenue_total']) / $prevSummary['revenue_total']) * 100, 1)
            : ($filteredSummary['revenue_total'] > 0 ? 100 : 0);

        $unpaidGrowth = $prevSummary['unpaid_total'] > 0
            ? round((($filteredSummary['unpaid_total'] - $prevSummary['unpaid_total']) / $prevSummary['unpaid_total']) * 100, 1)
            : ($filteredSummary['unpaid_total'] > 0 ? 100 : 0);

        $filteredSummary['revenue_growth_pct'] = $revGrowth;
        $filteredSummary['unpaid_growth_pct']  = $unpaidGrowth;
        $filteredSummary['period_label']       = $startDate->locale('id')->isoFormat('D MMM Y') . ' - ' . $endDate->locale('id')->isoFormat('D MMM Y');

        // =========================================================================
        // 3. GRAFIK RINCIAN KAS MASUK & TAGIHAN (MENGIKUTI FILTER UNIVERSAL)
        // =========================================================================
        $charts = $this->buildChartSeries($filteredOrders, $granularity, $startDate, $endDate);

        // =========================================================================
        // 4. TOP 5 VARIAN PRODUK & DISTRIBUSI CHANNEL (MENGIKUTI FILTER UNIVERSAL)
        // =========================================================================
        $topProducts = $this->calculateTopProducts($filteredOrders);

        $channelBreakdown = [
            'web_orders'      => $filteredOrders->where('order_source', 'web')->count(),
            'whatsapp_orders' => $filteredOrders->where('order_source', 'whatsapp')->count(),
        ];

        // =========================================================================
        // 5. TABEL RINCIAN TRANSAKSI (MENGIKUTI FILTER UNIVERSAL)
        // =========================================================================
        $transactionsList = $filteredOrders->map(function ($o) {
            $pStatus = strtolower($o->transaction->payment_status ?? 'unpaid');
            $oStatus = strtolower($o->status ?? 'pending');

            $paidAmount   = 0;
            $unpaidAmount = 0;

            if ($oStatus !== 'dibatalkan') {
                if ($pStatus === 'paid_full') {
                    $paidAmount = (int) $o->total_amount;
                } elseif ($pStatus === 'paid_dp') {
                    $paidAmount   = (int) ($o->total_amount - $o->remaining_amount);
                    $unpaidAmount = (int) $o->remaining_amount;
                } else {
                    $unpaidAmount = (int) $o->total_amount;
                }
            }

            return [
                'order_number'       => $o->order_number,
                'created_at'         => $o->created_at->format('Y-m-d H:i:s'),
                'recipient_name'     => $o->recipient_name,
                'recipient_whatsapp' => $o->recipient_whatsapp,
                'order_source'       => $o->order_source ?? 'web',
                'payment_method'     => $o->transaction->payment_method ?? 'transfer',
                'payment_status'     => $pStatus,
                'order_status'       => $oStatus,
                'total_amount'       => (int) $o->total_amount,
                'paid_amount'        => $paidAmount,
                'remaining_amount'   => $unpaidAmount,
                'items_count'        => (int) $o->items->sum('quantity'),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'all_time_stats'    => $allTimeSummary,
                'filtered_stats'    => $filteredSummary,
                'charts'            => $charts,
                'top_products'      => $topProducts,
                'channel_breakdown' => $channelBreakdown,
                'transactions'      => $transactionsList,
            ]
        ]);
    }

    /**
     * Menghitung metrik Kas Masuk vs Tagihan Gantung
     */
    private function calculateMetrics($orders): array
    {
        $revenueLunas  = 0;
        $revenueDpPaid = 0;
        $unpaidFull    = 0;
        $unpaidDpSisa  = 0;

        $paidFullCount  = 0;
        $paidDpCount    = 0;
        $unpaidCount    = 0;
        $cancelledCount = 0;

        foreach ($orders as $o) {
            $oStatus = strtolower($o->status ?? 'pending');
            $pStatus = strtolower($o->transaction->payment_status ?? 'unpaid');

            if ($oStatus === 'dibatalkan') {
                $cancelledCount++;
                continue;
            }

            if ($pStatus === 'paid_full') {
                $revenueLunas += (int) $o->total_amount;
                $paidFullCount++;
            } elseif ($pStatus === 'paid_dp') {
                $dpMasuk = (int) ($o->total_amount - $o->remaining_amount);
                $sisa    = (int) $o->remaining_amount;
                $revenueDpPaid += $dpMasuk;
                $unpaidDpSisa  += $sisa;
                $paidDpCount++;
            } else {
                $unpaidFull += (int) $o->total_amount;
                $unpaidCount++;
            }
        }

        $revenueTotal  = $revenueLunas + $revenueDpPaid;
        $unpaidTotal   = $unpaidFull + $unpaidDpSisa;
        $successOrders = $paidFullCount + $paidDpCount;

        return [
            'revenue_total'   => $revenueTotal,
            'revenue_lunas'   => $revenueLunas,
            'revenue_dp_paid' => $revenueDpPaid,
            'unpaid_total'    => $unpaidTotal,
            'unpaid_full'     => $unpaidFull,
            'unpaid_dp_sisa'  => $unpaidDpSisa,
            'success_orders'  => $successOrders,
            'unpaid_count'    => $unpaidCount,
            'cancelled_count' => $cancelledCount,
            'avg_order_value' => $successOrders > 0 ? (int) round($revenueTotal / $successOrders) : 0,
        ];
    }

    /**
     * Mengelompokkan data grafik berdasarkan $filteredOrders dan rentang [$startDate, $endDate]
     */
    private function buildChartSeries($filteredOrders, string $granularity, Carbon $startDate, Carbon $endDate): array
    {
        $buckets = [];

        if ($granularity === 'daily') {
            $period = CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->endOfDay());
            foreach ($period as $dt) {
                $buckets[] = [
                    'label' => $dt->locale('id')->isoFormat('dd, D MMM'),
                    'start' => $dt->copy()->startOfDay(),
                    'end'   => $dt->copy()->endOfDay(),
                ];
            }
        } elseif ($granularity === 'weekly') {
            $cursor = $startDate->copy()->startOfDay();
            $idx = 1;
            while ($cursor->lte($endDate)) {
                $wStart = $cursor->copy()->startOfDay();
                $wEnd   = $cursor->copy()->addDays(6)->endOfDay();
                if ($wEnd->gt($endDate)) {
                    $wEnd = $endDate->copy()->endOfDay();
                }
                $buckets[] = [
                    'label' => 'Mgg ' . $idx . ' (' . $wStart->format('d/m') . '-' . $wEnd->format('d/m') . ')',
                    'start' => $wStart,
                    'end'   => $wEnd,
                ];
                $cursor->addDays(7);
                $idx++;
            }
        } elseif ($granularity === 'monthly') {
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $mStart = $cursor->copy()->startOfMonth();
                $mEnd   = $cursor->copy()->endOfMonth();
                $buckets[] = [
                    'label' => $cursor->locale('id')->isoFormat('MMM YYYY'),
                    'start' => $mStart->lt($startDate) ? $startDate->copy() : $mStart,
                    'end'   => $mEnd->gt($endDate) ? $endDate->copy() : $mEnd,
                ];
                $cursor->addMonth();
            }
        } else {
            // yearly
            $cursor = $startDate->copy()->startOfYear();
            while ($cursor->lte($endDate)) {
                $yStart = $cursor->copy()->startOfYear();
                $yEnd   = $cursor->copy()->endOfYear();
                $buckets[] = [
                    'label' => $cursor->format('Y'),
                    'start' => $yStart->lt($startDate) ? $startDate->copy() : $yStart,
                    'end'   => $yEnd->gt($endDate) ? $endDate->copy() : $yEnd,
                ];
                $cursor->addYear();
            }
        }

        $labels        = [];
        $revCurrent    = [];
        $revLunas      = [];
        $revDp         = [];
        $unpaidCurrent = [];
        $unpaidDpRem   = [];
        $unpaidOnly    = [];

        foreach ($buckets as $b) {
            $slice = $filteredOrders->filter(function ($o) use ($b) {
                return $o->created_at >= $b['start'] && $o->created_at <= $b['end'];
            });

            $m = $this->calculateMetrics($slice);

            $labels[]        = $b['label'];
            $revCurrent[]    = $m['revenue_total'];
            $revLunas[]      = $m['revenue_lunas'];
            $revDp[]         = $m['revenue_dp_paid'];
            $unpaidCurrent[] = $m['unpaid_total'];
            $unpaidDpRem[]   = $m['unpaid_dp_sisa'];
            $unpaidOnly[]    = $m['unpaid_full'];
        }

        return [
            'labels'          => $labels,
            'revenue_current' => $revCurrent,
            'revenue_lunas'   => $revLunas,
            'revenue_dp'      => $revDp,
            'unpaid_current'  => $unpaidCurrent,
            'unpaid_dp_sisa'  => $unpaidDpRem,
            'unpaid_only'     => $unpaidOnly,
        ];
    }

    /**
     * Menghitung Top 5 Varian Produk dari $filteredOrders
     */
    private function calculateTopProducts($filteredOrders): array
    {
        $validOrders = $filteredOrders->where('status', '!=', 'dibatalkan');
        $map = [];
        $grandOmzetProduk = 0;

        foreach ($validOrders as $order) {
            foreach ($order->items as $item) {
                $vid = $item->product_variant_id;
                $namaProduk = ($item->productVariant->product->name ?? 'Roti') . ' - ' . ($item->productVariant->variant_name ?? 'Varian');
                if (!isset($map[$vid])) {
                    $map[$vid] = [
                        'name'    => $namaProduk,
                        'qty'     => 0,
                        'revenue' => 0,
                    ];
                }
                $map[$vid]['qty']     += (int) $item->quantity;
                $map[$vid]['revenue'] += (int) $item->subtotal;
                $grandOmzetProduk     += (int) $item->subtotal;
            }
        }

        usort($map, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        $top5 = array_slice($map, 0, 5);

        foreach ($top5 as &$prod) {
            $prod['percentage'] = $grandOmzetProduk > 0 ? round(($prod['revenue'] / $grandOmzetProduk) * 100, 1) : 0;
        }

        return $top5;
    }
}
