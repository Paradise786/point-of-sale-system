<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt – {{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }

        .receipt {
            background: white;
            width: 300px;
            padding: 20px 16px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #ccc;
        }

        .shop-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .shop-tagline {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .receipt-info {
            font-size: 11px;
            color: #555;
            margin-top: 8px;
        }

        .receipt-info p { line-height: 1.6; }

        .section-divider {
            border-top: 1px dashed #ccc;
            margin: 12px 0;
        }

        .items-table {
            width: 100%;
            font-size: 11px;
        }

        .items-table thead th {
            font-weight: bold;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .item-name { max-width: 160px; word-break: break-word; }

        .totals-section {
            margin-top: 10px;
            font-size: 11px;
        }

        .totals-section .row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            color: #444;
        }

        .totals-section .total-row {
            font-size: 13px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 6px;
            margin-top: 4px;
            color: #000;
        }

        .payment-badge {
            display: inline-block;
            padding: 2px 6px;
            background: #e8f5e9;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            line-height: 1.8;
        }

        .thank-you {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #333;
            margin-bottom: 2px;
        }

        .print-btn {
            display: block;
            margin: 16px auto 0;
            padding: 8px 20px;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            font-family: inherit;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                border-radius: 0;
                padding: 10px 8px;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div>
        <div class="receipt" id="receipt">
            <!-- Header -->
            <div class="receipt-header">
                <div class="shop-name">🏪 SmartPOS</div>
                <div class="shop-tagline">Point of Sale & Inventory System</div>
                <div class="receipt-info" style="margin-top:10px;">
                    <p>{{ $sale->created_at->format('d M Y, h:i A') }}</p>
                    <p style="margin-top:4px; font-weight:bold;">Invoice: {{ $sale->invoice_number }}</p>
                </div>
            </div>

            <!-- Customer Info -->
            @if ($sale->customer)
                <div style="font-size:11px; margin-bottom:8px;">
                    <p>Customer: <strong>{{ $sale->customer->name }}</strong></p>
                    @if ($sale->customer->phone)
                        <p>Phone: {{ $sale->customer->phone }}</p>
                    @endif
                </div>
                <div class="section-divider"></div>
            @endif

            <!-- Items -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="item-name">Item</th>
                        <th style="text-align:center; padding: 0 6px;">Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td class="item-name">
                                {{ $item->product->name ?? 'Deleted Product' }}
                                <br>
                                <span style="color:#888; font-size:9px;">@ Rs.{{ number_format($item->price, 2) }}</span>
                            </td>
                            <td style="text-align:center; padding: 0 6px;">{{ $item->quantity }}</td>
                            <td style="text-align:right;">Rs.{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <div class="row">
                    <span>Subtotal</span>
                    <span>Rs.{{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div class="row">
                    <span>Paid</span>
                    <span>Rs.{{ number_format($sale->paid_amount, 2) }}</span>
                </div>
                <div class="row">
                    <span>Change</span>
                    <span>Rs.{{ number_format($sale->change_amount, 2) }}</span>
                </div>
                <div class="row total-row">
                    <span>TOTAL</span>
                    <span>Rs.{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>

            <div class="section-divider"></div>

            <!-- Payment Method -->
            <div style="text-align:center; margin:8px 0;">
                <span class="payment-badge">{{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</span>
            </div>

            @if ($sale->note)
                <div style="font-size:10px; color:#555; margin-top:8px; font-style:italic; text-align:center;">
                    Note: {{ $sale->note }}
                </div>
            @endif

            <!-- Footer -->
            <div class="receipt-footer">
                <div class="thank-you">Thank You!</div>
                <p>Please come again 🙏</p>
                <p style="margin-top:4px; font-size:9px; color:#aaa;">Powered by SmartPOS</p>
            </div>
        </div>

        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
    </div>

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>
