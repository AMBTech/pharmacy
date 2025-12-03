<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DailySalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $dailySales;
    protected $date;

    public function __construct($dailySales, $date)
    {
        $this->dailySales = $dailySales;
        $this->date = $date;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        return $this->dailySales;
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Time',
            'Cashier',
            'Payment Method',
            'Items',
            'Subtotal (Rs.)',
            'Discount (Rs.)',
            'Tax (Rs.)',
            'Total (Rs.)'
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_number,
            \Carbon\Carbon::parse($sale->created_at)->format('h:i A'),
            $sale->cashier->name ?? 'N/A',
            ucfirst($sale->payment_method),
            $sale->items->count(),
            number_format($sale->subtotal, 2),
            number_format($sale->discount_amount, 2),
            number_format($sale->tax_amount, 2),
            number_format($sale->total_amount, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalTransactions = $this->dailySales->count();
        $totalRevenue = $this->dailySales->sum('total_amount');
        $totalDiscount = $this->dailySales->sum('discount_amount');
        $totalTax = $this->dailySales->sum('tax_amount');

        // Add title with dark green background
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Daily Sales Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF1F4E3D']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(25);

        // Add report metadata section
        $sheet->setCellValue('A2', 'Report Date:');
        $sheet->mergeCells('B2:C2');
        $sheet->setCellValue('B2', \Carbon\Carbon::parse($this->date)->format('F d, Y'));
        
        $sheet->setCellValue('D2', 'Generated:');
        $sheet->mergeCells('E2:F2');
        $sheet->setCellValue('E2', now()->format('F d, Y H:i'));
        
        $sheet->setCellValue('A3', 'Total Transactions:');
        $sheet->setCellValue('B3', $totalTransactions);
        $sheet->setCellValue('C3', 'Total Revenue:');
        $sheet->mergeCells('D3:E3');
        $sheet->setCellValue('D3', 'Rs. ' . number_format($totalRevenue, 2));
        
        $sheet->setCellValue('A4', 'Total Discount:');
        $sheet->setCellValue('B4', 'Rs. ' . number_format($totalDiscount, 2));
        $sheet->setCellValue('C4', 'Total Tax:');
        $sheet->mergeCells('D4:E4');
        $sheet->setCellValue('D4', 'Rs. ' . number_format($totalTax, 2));
        
        // Style metadata section
        $sheet->getStyle('A2:I4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFD9E1F2']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);
        
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);
        $sheet->getStyle('C3:C4')->getFont()->setBold(true);
        $sheet->getStyle('D2')->getFont()->setBold(true);

        // Style header row with blue background
        $sheet->getStyle('A6:I6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);
        $sheet->getRowDimension('6')->setRowHeight(25);

        // Auto-size columns
        foreach(range('A','I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to data rows
        $lastRow = 6 + $this->dailySales->count();
        $sheet->getStyle('A6:I' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);

        // Add alternating row colors
        for ($i = 7; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':I' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFD9E1F2']
                    ]
                ]);
            }
        }

        // Right align numbers
        $sheet->getStyle('E7:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        return 'Daily Sales';
    }
}
