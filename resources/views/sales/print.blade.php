@php
    // $items is assumed to be an array or collection of sale item arrays/objects
    $itemsCollection = collect($sale->items);

    $aggregated = $itemsCollection
        ->groupBy(function($item) {
            $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : (float)$item->unit_price;
            $productId = isset($item['product_id']) ? $item['product_id'] : $item->product_id;
            return $productId . '|' . number_format($unitPrice, 2, '.', '');
        })
        ->map(function($group) {
            $first = $group->first();
            $totalQty = $group->sum(fn($i) => (int) (is_array($i) ? $i['quantity'] : $i->quantity));
            $totalPrice = $group->sum(fn($i) => (float) (is_array($i) ? $i['total_price'] : $i->total_price));
            return [
                'product_id' => is_array($first) ? $first['product_id'] : $first->product_id,
                'product_name' => is_array($first) ? $first['product_name'] : $first->product_name,
                'unit_price' => number_format((float)(is_array($first) ? $first['unit_price'] : $first->unit_price), 2, '.', ''),
                'quantity' => $totalQty,
                'total_price' => number_format($totalPrice, 2, '.', ''),
                'batch_number' => $first->batch->batch_number ?? ''
            ];
        })
        ->values()
        ->all();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        @media print {
            @page {
                margin: 0;
                size: 80mm 297mm; /* Thermal printer size */
            }

            body {
                margin: 0;
                padding: 0;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.2;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
            max-width: 80mm;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .business-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .business-address {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .business-contact {
            font-size: 10px;
            margin-bottom: 10px;
        }

        .invoice-info {
            margin-bottom: 10px;
        }

        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .line-items th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 3px 0;
            font-weight: bold;
        }

        .line-items td {
            padding: 2px 0;
            vertical-align: top;
        }

        .line-items .item-name {
            width: 40%;
        }

        .line-items .item-qty {
            width: 10%;
            text-align: center;
        }

        .line-items .item-unitPrice {
            width: 15%;
            text-align: center;
        }

        .line-items .item-price {
            width: 30%;
            text-align: right;
        }

        .totals {
            width: 100%;
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-bottom: 10px;
        }

        .totals td {
            padding: 2px 0;
        }

        .totals .label {
            text-align: left;
        }

        .totals .amount {
            text-align: right;
            font-weight: bold;
        }

        .grand-total {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }

        .barcode {
            text-align: center;
            margin: 10px 0;
            font-family: 'Libre Barcode 39', monospace;
            font-size: 24px;
        }

        .thank-you {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }

        .customer-info {
            margin-bottom: 10px;
            padding: 5px;
            background: #f5f5f5;
            border-radius: 3px;
        }
    </style>

    <!-- Barcode font -->
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>
<!-- Print Button (Hidden when printing) -->
<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" style="
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        ">
        🖨️ Print Receipt
    </button>
    <button onclick="window.close()" style="
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        ">
        ❌ Close
    </button>
</div>

<!-- Receipt Content -->
<div class="header">
    <div class="business-name">{{$settings['company_name']}}</div>
    <img src="{{ asset('images/clinic-logo.png') }}" alt="company logo" style="width: 70px; height: auto;">
    <div class="business-address">{{$settings['company_address']}}</div>
    <div class="business-contact">Tel: {{$settings['company_phone']}} {{$settings['license_number'] ? '| License:' . $settings['license_number'] : ''}}</div>
</div>

<!-- Invoice Information -->
<div class="invoice-info">
    <strong>INVOICE: {{ $sale->invoice_number }}</strong><br>
    Date: {{ $sale->created_at->format('M j, Y h:i A') }}<br>
    Cashier: {{ $sale->cashier->name ?? 'System' }}
</div>

<!-- Customer Information -->
@if($sale->customer_name || $sale->customer_phone)
    <div class="customer-info">
        <strong>Customer:</strong> {{ $sale->customer_name ?: 'Walk-in Customer' }}<br>
        @if($sale->customer_phone)
            <strong>Phone:</strong> {{ $sale->customer_phone }}
        @endif
    </div>
@endif

<!-- Line Items -->
<table class="line-items">
    <thead>
    <tr>
        <th class="item-name">ITEM</th>
        <th class="item-qty">QTY</th>
        <th class="item-unitPrice">Price</th>
        <th class="item-price">AMOUNT</th>
    </tr>
    </thead>
    <tbody>
    @foreach($aggregated as $item)
        <tr>
            <td class="item-name">
                {{ $item['product_name'] }}<br>
                <small style="font-size: 9px;">
                    @if($item['batch_number'])
                        Batch: {{ $item['batch_number'] }}
                    @endif
                </small>
            </td>
            <td class="item-qty">{{ $item['quantity'] }}</td>
            <td class="item-unitPrice">{{ $item['unit_price'] }}</td>
            <td class="item-price">{{ number_format($item['total_price'], 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Totals -->
<table class="totals">
    <tr>
        <td class="label">Subtotal:</td>
        <td class="amount">{{$currency_symbol}} {{ number_format($sale->subtotal, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Tax (8%):</td>
        <td class="amount">{{$currency_symbol}} {{ number_format($sale->tax_amount, 2) }}</td>
    </tr>
    @if($sale->discount_amount > 0)
        <tr>
            <td class="label">Discount:</td>
            <td class="amount" style="color: #dc3545;">- {{$currency_symbol}} {{ number_format($sale->discount_amount, 2) }}</td>
        </tr>
    @endif
    <tr class="grand-total">
        <td class="label">TOTAL:</td>
        <td class="amount">{{$currency_symbol}} {{ number_format($sale->total_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Payment:</td>
        <td class="amount" style="text-transform: uppercase;">{{ $sale->payment_method }}</td>
    </tr>
</table>

<!-- Barcode -->
<div class="barcode">
    *{{ $sale->invoice_number }}*
</div>

<!-- Footer -->
<div class="footer">
    <strong>Thank you for your purchase!</strong><br>
    Please retain this receipt for your records<br>
    Returns accepted within 7 days with receipt<br>
    For medical advice, consult your physician<br>
    <br>
    {{ now()->format('M j, Y g:i A') }}
</div>

<script>
    // Auto-print when page loads (optional)
    window.onload = function () {
        // Uncomment the line below if you want auto-print
        // window.print();
    };

    // Close window after printing
    window.onafterprint = function () {
        // Optional: auto-close after printing
        // setTimeout(() => window.close(), 1000);
    };
</script>
</body>
</html>
