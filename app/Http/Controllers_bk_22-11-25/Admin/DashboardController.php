<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Counts
        $totalDiamonds = DB::table('diamond_master')->count();
        $totalProducts = DB::table('products')->count();
        $totalVariations = DB::table('product_variations')->count();
        $totalOrders = DB::table('orders')->count();

        // Sales percentage
        $today = Carbon::today()->toDateString();
        $todaySales = DB::table('orders')
            ->whereDate('created_at', $today)
            ->sum('total_price');
        $totalSales = DB::table('orders')->sum('total_price');

        $salesPercentage = $totalSales > 0
            ? round(($todaySales / $totalSales) * 100, 2)
            : 0;

        // Order-based dynamic stats
        $orderSales = DB::table('orders')
            ->select(
                DB::raw("SUM(CASE WHEN product_type = 'diamond' THEN total_price ELSE 0 END) as diamond_sales"),
                DB::raw("SUM(CASE WHEN product_type = 'jewelry' THEN total_price ELSE 0 END) as jewelry_sales")
            )
            ->first();

        $transactions = DB::table('orders')
        ->select('payment_mode', DB::raw('SUM(total_price) as total_amount'))
        ->groupBy('payment_mode')
        ->orderByDesc('total_amount')
        ->limit(6)
        ->get();

        $yearlyRevenue = [];
        $currentYear = Carbon::now()->year;

        for ($i = 0; $i < 4; $i++) {
            $year = $currentYear - $i;
            $total = DB::table('orders')
                ->whereYear('created_at', $year)
                ->sum('total_price');
            $yearlyRevenue[$year] = $total;
        }

        $profileYear = 2025;
        $profileIncome = DB::table('orders')
            ->whereYear('created_at', $profileYear)
            ->sum('total_price');

        // Expenses (for demo, assume fixed percent of income — you can replace with real data if available)
        $profileExpenses = $profileIncome * 0.35; // Assume 35% expense
        $profileProfit = $profileIncome - $profileExpenses;

        // Income/Expenses/Profit for current week
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyIncome = DB::table('orders')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->sum('total_price');

        $weeklyExpenses = $weeklyIncome * 0.35;
        $weeklyProfit = $weeklyIncome - $weeklyExpenses;

        return view('admin.dashboard', compact(
            'totalDiamonds',
            'totalProducts',
            'totalVariations',
            'totalOrders',
            'salesPercentage',
            'totalSales',
            'orderSales',
            'transactions',
            'yearlyRevenue',
            'profileYear',
            'profileIncome',
            'profileExpenses',
            'profileProfit',
            'weeklyIncome',
            'weeklyExpenses',
            'weeklyProfit'
        ));
    }
}
