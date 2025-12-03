<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Sale::with(['items', 'cashier']);

        // Apply the same filters as the index page
        if (isset($this->filters['start_date']) && $this->filters['start_date']) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date']) && $this->filters['end_date']) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['payment_method']) && $this->filters['payment_method']) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (isset($this->filters['search']) && $this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date & Time',
            'Customer Name',
            'Customer Phone',
            'Items Count',
            'Subtotal (Rs.)',
            'Tax (Rs.)',
            'Discount (Rs.)',
            'Total Amount (Rs.)',
            'Payment Method',
            'Cashier',
            'Notes'
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            $sale->created_at->format('Y-m-d H:i:s'),
            $sale->customer_name ?: 'Walk-in Customer',
            $sale->customer_phone ?: 'N/A',
            $sale->items->count(),
            number_format($sale->subtotal, 2),
            number_format($sale->tax_amount, 2),
            number_format($sale->discount_amount, 2),
            number_format($sale->total_amount, 2),
            ucfirst($sale->payment_method),
            $sale->cashier->name ?? 'System',
            $sale->notes ?: 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],

            // Style the header row
            'A1:L1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFE6E6FA']
                ]
            ],
        ];
    }
}
