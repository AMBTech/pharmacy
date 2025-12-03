<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpiringProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $expiringProducts;
    protected $filters;

    public function __construct($expiringProducts, $filters = [])
    {
        $this->expiringProducts = $expiringProducts;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->expiringProducts;
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category',
            'Brand',
            'Generic Name',
            'Batch Number',
            'Manufacturing Date',
            'Expiry Date',
            'Days to Expiry',
            'Batch Quantity',
            'Product Stock',
            'Cost Price (Rs.)',
            'Batch Value (Rs.)',
            'Status'
        ];
    }

    public function map($batch): array
    {
        $daysToExpiry = $batch->days_until_expiry;
        $isExpired = $daysToExpiry < 0;
        
        // Determine status
        if ($isExpired) {
            $status = 'Expired (' . abs($daysToExpiry) . ' days ago)';
        } elseif ($daysToExpiry <= 7) {
            $status = 'Critical';
        } elseif ($daysToExpiry <= 30) {
            $status = 'Warning';
        } else {
            $status = 'Good';
        }
        
        // Stock status
        if ($batch->quantity == 0) {
            $stockStatus = 'Depleted';
        } elseif ($batch->product->stock < 10) {
            $stockStatus = 'Low Stock';
        } else {
            $stockStatus = 'In Stock';
        }
        
        $totalValue = $batch->quantity * $batch->cost_price;

        return [
            $batch->product->name,
            $batch->product->category->name ?? 'Uncategorized',
            $batch->product->brand ?: 'N/A',
            $batch->product->generic_name ?: 'N/A',
            $batch->batch_number,
            $batch->manufacturing_date->format('Y-m-d'),
            $batch->expiry_date->format('Y-m-d'),
            $isExpired ? 'Expired' : $daysToExpiry . ' days',
            number_format($batch->quantity),
            number_format($batch->product->stock),
            number_format($batch->cost_price, 2),
            number_format($totalValue, 2),
            $status . ' - ' . $stockStatus
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFEF4444']
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Expiring Products';
    }
}
