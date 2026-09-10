<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    /**
     * Display Customer Ledger (Customer Khata statement).
     */
    public function customerLedger(Request $request): View
    {
        $customerId = $request->query('customer_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $customers = Customer::orderBy('name')->get();
        $selectedCustomer = $customerId ? Customer::find($customerId) : null;

        $ledgerEntries = collect();
        $totalDebit = 0;
        $totalCredit = 0;
        $closingBalance = 0;

        if ($selectedCustomer) {
            // 1. Sales (Invoices)
            $sales = Sale::where('customer_id', $selectedCustomer->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->get()
                ->flatMap(function ($sale) {
                    $records = [];
                    // Billed amount (Customer owes this -> Debit)
                    $records[] = [
                        'date' => $sale->created_at,
                        'type' => 'Sale Invoice',
                        'type_badge' => 'bg-emerald-100 text-emerald-800',
                        'reference' => $sale->invoice_number,
                        'url' => route('sales.show', $sale),
                        'description' => 'Sale Invoice #'.$sale->invoice_number,
                        'debit' => (float) $sale->total_amount,
                        'credit' => 0.0,
                    ];

                    // Payment received at sale (Payment reduces debt -> Credit)
                    if ($sale->paid_amount > 0) {
                        $records[] = [
                            'date' => $sale->created_at,
                            'type' => 'Payment Received',
                            'type_badge' => 'bg-blue-100 text-blue-800',
                            'reference' => $sale->invoice_number,
                            'url' => route('sales.show', $sale),
                            'description' => 'Payment received via '.ucfirst($sale->payment_method),
                            'debit' => 0.0,
                            'credit' => (float) $sale->paid_amount,
                        ];
                    }

                    return $records;
                });

            // 2. Sale Returns (Goods returned -> Credit)
            $returns = SaleReturn::where('customer_id', $selectedCustomer->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
                ->get()
                ->map(function ($ret) {
                    return [
                        'date' => $ret->return_date,
                        'type' => 'Sale Return',
                        'type_badge' => 'bg-amber-100 text-amber-800',
                        'reference' => $ret->return_number,
                        'url' => route('sale-returns.show', $ret),
                        'description' => 'Return items for '.($ret->sale ? 'Inv #'.$ret->sale->invoice_number : 'Customer return'),
                        'debit' => 0.0,
                        'credit' => (float) $ret->total_amount,
                    ];
                });

            // Merge & sort chronologically
            $ledgerEntries = $sales->concat($returns)->sortBy('date')->values();

            // Calculate running balance
            $runningBalance = 0;
            $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
                $totalDebit += $entry['debit'];
                $totalCredit += $entry['credit'];
                $runningBalance += ($entry['debit'] - $entry['credit']);
                $entry['running_balance'] = $runningBalance;

                return $entry;
            });

            $closingBalance = $runningBalance;
        }

        return view('ledgers.customer', compact('customers', 'selectedCustomer', 'customerId', 'dateFrom', 'dateTo', 'ledgerEntries', 'totalDebit', 'totalCredit', 'closingBalance'));
    }

    /**
     * Display Vendor Ledger (Supplier Khata statement).
     */
    public function vendorLedger(Request $request): View
    {
        $vendorId = $request->query('vendor_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $vendors = Vendor::orderBy('name')->get();
        $selectedVendor = $vendorId ? Vendor::find($vendorId) : null;

        $ledgerEntries = collect();
        $totalDebit = 0;
        $totalCredit = 0;
        $closingBalance = 0;

        if ($selectedVendor) {
            // 1. Purchases (Invoices -> Credit, we owe vendor)
            $purchases = Purchase::where('vendor_id', $selectedVendor->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
                ->get()
                ->map(function ($purchase) {
                    return [
                        'date' => $purchase->purchase_date,
                        'type' => 'Purchase Invoice',
                        'type_badge' => 'bg-emerald-100 text-emerald-800',
                        'reference' => $purchase->reference_no,
                        'url' => route('purchases.show', $purchase),
                        'description' => 'Purchase Invoice #'.$purchase->reference_no,
                        'debit' => 0.0,
                        'credit' => (float) $purchase->total_amount,
                    ];
                });

            // 2. Purchase Returns (Goods returned to vendor -> Debit, reduces what we owe)
            $returns = PurchaseReturn::where('vendor_id', $selectedVendor->id)
                ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
                ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
                ->get()
                ->map(function ($ret) {
                    return [
                        'date' => $ret->return_date,
                        'type' => 'Purchase Return',
                        'type_badge' => 'bg-amber-100 text-amber-800',
                        'reference' => $ret->return_number,
                        'url' => route('purchase-returns.show', $ret),
                        'description' => 'Returned items to vendor ('.$ret->return_number.')',
                        'debit' => (float) $ret->total_amount,
                        'credit' => 0.0,
                    ];
                });

            // Merge & sort chronologically
            $ledgerEntries = $purchases->concat($returns)->sortBy('date')->values();

            // Calculate running balance (Credit is payable, Debit reduces payable)
            $runningBalance = 0;
            $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
                $totalDebit += $entry['debit'];
                $totalCredit += $entry['credit'];
                $runningBalance += ($entry['credit'] - $entry['debit']);
                $entry['running_balance'] = $runningBalance;

                return $entry;
            });

            $closingBalance = $runningBalance;
        }

        return view('ledgers.vendor', compact('vendors', 'selectedVendor', 'vendorId', 'dateFrom', 'dateTo', 'ledgerEntries', 'totalDebit', 'totalCredit', 'closingBalance'));
    }
}
