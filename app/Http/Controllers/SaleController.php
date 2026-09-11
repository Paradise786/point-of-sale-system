<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status');
        $paymentMethod = $request->query('payment_method');
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();

        $sales = Sale::with(['customer', 'items.product'])
            ->when($search, function ($query, $search) {
                return $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($customerId, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', $paymentStatus);
            })
            ->when($paymentMethod, function ($query, $paymentMethod) {
                return $query->where('payment_method', $paymentMethod);
            })
            ->when($dateFrom, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalRevenue = Sale::sum('total_amount');
        $totalPaid = Sale::sum('paid_amount');
        $totalDue = Sale::sum('due_amount');
        $totalOrders = Sale::count();

        return view('sales.index', compact('sales', 'customers', 'search', 'customerId', 'paymentStatus', 'paymentMethod', 'dateFrom', 'dateTo', 'totalRevenue', 'totalPaid', 'totalDue', 'totalOrders'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['customer', 'items.product.category']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['customer', 'items.product']);

        return view('sales.receipt', compact('sale'));
    }
}
