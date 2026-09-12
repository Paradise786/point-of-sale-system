<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Receipt – {{ $purchase->reference_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace, -apple-system, sans-serif;
            font-size: 11px;
            color: #000;
            background: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }

        .receipt-container {
            background: #fff;
            width: 78mm;
            max-width: 320px;
            padding: 12px 10px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .brand-name {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .tagline {
            font-size: 9px;
            font-style: italic;
            color: #333;
            margin-bottom: 4px;
        }

        .branch-address {
            font-size: 10px;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .phone-numbers {
            font-size: 9.5px;
            margin-bottom: 6px;
        }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            line-height: 1.4;
        }

        .info-row {
            font-size: 10px;
            line-height: 1.4;
            margin-top: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 4px;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-weight: bold;
        }

        .items-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            font-size: 10.5px;
            margin-top: 4px;
        }

        .totals-table td {
            padding: 2px 0;
        }

        .net-total-row {
            font-size: 13px;
            font-weight: 900;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
        }

        .software-credit {
            font-size: 8px;
            color: #444;
            text-align: center;
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px dotted #888;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-print {
            background: #0f172a;
            color: #fff;
        }

        .btn-back {
            background: #e2e8f0;
            color: #1e293b;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                width: 100%;
                max-width: 100%;
                padding: 2mm;
            }
            .action-buttons {
                display: none;
            }
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt-container" id="thermalReceipt">
            <!-- Store Header -->
            <div class="text-center">
                <div class="brand-name">📦 PURCHASE INVOICE RECEIPT</div>
                <div class="tagline">Goods Receiving &amp; Supplier Voucher</div>
                <div class="branch-address">Main Commercial Market, Model Town</div>
                <div class="phone-numbers">Mob # 0300-8527070 , 0345-0876111</div>
            </div>

            <div class="dashed-line"></div>

            <!-- Invoice Meta -->
            <div class="meta-row">
                <span class="font-bold">Ref: {{ $purchase->reference_no }}</span>
                <span>{{ $purchase->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="font-bold">Vendor: </span>
                <span class="uppercase">{{ $purchase->vendor ? $purchase->vendor->name : 'N/A' }}</span>
            </div>
            <div class="meta-row">
                <span>Remarks: {{ $purchase->description ?: ($purchase->note ?: '-') }}</span>
                <span>Ref.: {{ $purchase->extra_field_one ?: '-' }}</span>
            </div>

            <div class="dashed-line"></div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-left" style="width:48%;">Item Name</th>
                        <th class="text-center" style="width:14%;">Qty</th>
                        <th class="text-right" style="width:18%;">Cost</th>
                        <th class="text-right" style="width:20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="text-left font-bold">
                                {{ $item->product->name ?? 'Deleted Item' }}
                                @if($item->unit)
                                    <span style="font-weight:normal; font-size:8.5px; color:#333;">({{ $item->unit->short_code }})</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="text-right font-bold">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="dashed-line"></div>

            <!-- Summary & Totals -->
            <div class="meta-row">
                <span class="font-bold">Total items: {{ $purchase->items->count() }} ({{ $purchase->items->sum('quantity') }} Qty)</span>
            </div>

            <table class="totals-table">
                <tr>
                    <td class="text-right font-bold" style="width:65%;">Gross Total :</td>
                    <td class="text-right font-bold">{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right">Paid to Vendor :</td>
                    <td class="text-right">{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                @if($purchase->due_amount > 0)
                <tr>
                    <td class="text-right font-bold" style="color:#b91c1c;">Payable (Ledger) :</td>
                    <td class="text-right font-bold" style="color:#b91c1c;">{{ number_format($purchase->due_amount, 2) }}</td>
                </tr>
                @endif
            </table>

            <div class="dashed-line"></div>

            <!-- Net Total & Receiver -->
            <div class="meta-row net-total-row">
                <span class="uppercase">RECEIVED BY STORE</span>
                <span class="text-right">Net Total. {{ number_format($purchase->total_amount, 2) }}</span>
            </div>

            <!-- Status & Method -->
            <div class="meta-row" style="margin-top: 4px; font-size:9.5px;">
                <span>Payment: <strong class="uppercase">{{ str_replace('_', ' ', $purchase->payment_method ?? 'cash') }}</strong></span>
                <span>Status: <strong class="uppercase">{{ $purchase->payment_status_label }}</strong></span>
            </div>

            <!-- Credit Footer -->
            <div class="software-credit">
                (Computer Software developed by SmartPOS Systems)
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-print" onclick="window.print()">🖨️ Print Receipt</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-back">← Back to Purchases</a>
            <a href="{{ route('purchases.create') }}" class="btn btn-back">+ New Purchase</a>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>