<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ now()->format('M j, Y') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .business-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .filters {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .summary {
            background: #e9ecef;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="business-name">PHARMACARE PHARMACY</div>
    <div class="report-title">Sales Report</div>
    <div>Generated on: {{ now()->format('F j, Y g:i A') }}</div>
</div>

<!-- Filters Applied -->
@if(!empty(array_filter($filters)))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['start_date']) && $filters['start_date'])
            From: {{ $filters['start_date'] }}
        @endif
        @if(isset($filters['end_date']) && $filters['end_date'])
            To: {{ $filters['end_date'] }}
        @endif
        @if(isset($filters['payment_method']) && $filters['payment_method'])
            | Payment: {{ ucfirst($filters['payment_method']) }}
        @endif
        @if(isset($filters['search']) && $filters['search'])
            | Search: "{{ $filters['search'] }}"
        @endif
    </div>
@endif

<!-- Summary -->
<div class="summary">
    <strong>Summary:</strong>
    Total Sales: {{ $totalSales }} |
    Total Revenue: Rs. {{ number_format($totalRevenue, 2) }} |
    Total Tax: Rs. {{ number_format($totalTax, 2) }} |
    Total Discount: Rs. {{ number_format($totalDiscount, 2) }}
</div>

<!-- Sales Table -->
<table>
    <thead>
    <tr>
        <th>Invoice #</th>
        <th>Date & Time</th>
        <th>Customer</th>
        <th>Items</th>
        <th>Subtotal</th>
        <th>Tax</th>
        <th>Discount</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Cashier</th>
    </tr>
    </thead>
    <tbody>
    @foreach($sales as $sale)
        <tr>
            <td>{{ $sale->invoice_number }}</td>
            <td>{{ $sale->created_at->format('M j, Y H:i') }}</td>
            <td>{{ $sale->customer_name ?: 'Walk-in' }}</td>
            <td>{{ $sale->items->count() }}</td>
            <td>Rs. {{ number_format($sale->subtotal, 2) }}</td>
            <td>Rs. {{ number_format($sale->tax_amount, 2) }}</td>
            <td>Rs. {{ number_format($sale->discount_amount, 2) }}</td>
            <td>Rs. {{ number_format($sale->total_amount, 2) }}</td>
            <td>{{ ucfirst($sale->payment_method) }}</td>
            <td>{{ $sale->cashier->name ?? 'System' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr class="total-row">
        <td colspan="4" style="text-align: right;"><strong>Totals:</strong></td>
        <td><strong>Rs. {{ number_format($sales->sum('subtotal'), 2) }}</strong></td>
        <td><strong>Rs. {{ number_format($sales->sum('tax_amount'), 2) }}</strong></td>
        <td><strong>Rs. {{ number_format($sales->sum('discount_amount'), 2) }}</strong></td>
        <td><strong>Rs. {{ number_format($sales->sum('total_amount'), 2) }}</strong></td>
        <td colspan="2"></td>
    </tr>
    </tfoot>
</table>

<div class="footer">
    This report was generated automatically by PharmaCare Pharmacy Management System.
</div>
</body>
</html>
