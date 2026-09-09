<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::today()->toDateString());

        // 1. Sales Report
        $salesQuery = Sale::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
        $totalSalesAmount = (float) $salesQuery->sum('total_amount');
        $totalOrdersCount = $salesQuery->count();

        // 2. Purchases Report
        $purchasesQuery = Purchase::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
        $totalPurchasesAmount = (float) $purchasesQuery->sum('total_amount');
        $totalPurchasesCount = $purchasesQuery->count();

        // 3. Stock Summary
        $totalStockUnits = Product::sum('quantity');
        $totalStockCost = Product::selectRaw('SUM(quantity * purchase_price) as val')->value('val') ?? 0;
        $totalStockRetail = Product::selectRaw('SUM(quantity * selling_price) as val')->value('val') ?? 0;
        $potentialProfit = $totalStockRetail - $totalStockCost;
        $lowStockCount = Product::lowStock()->count();

        // 4. Payment breakdown
        $cashSales = (clone $salesQuery)->where('payment_method', 'cash')->sum('total_amount');
        $cardSales = (clone $salesQuery)->where('payment_method', 'card')->sum('total_amount');
        $bankSales = (clone $salesQuery)->where('payment_method', 'bank_transfer')->sum('total_amount');

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalSalesAmount',
            'totalOrdersCount',
            'totalPurchasesAmount',
            'totalPurchasesCount',
            'totalStockUnits',
            'totalStockCost',
            'totalStockRetail',
            'potentialProfit',
            'lowStockCount',
            'cashSales',
            'cardSales',
            'bankSales'
        ));
    }
}
