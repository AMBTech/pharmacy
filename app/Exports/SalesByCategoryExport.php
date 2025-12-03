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

class SalesByCategoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $categorySales;
    protected $startDate;
    protected $endDate;

    public function __construct($categorySales, $startDate, $endDate)
    {
        $this->categorySales = $categorySales;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        return $this->categorySales->sortByDesc('revenue')->values();
    }

    public function headings(): array
    {
        return [
            'Category',
            'Quantity Sold',
            'Revenue (Rs.)',
            'Transactions',
            'Avg/Transaction (Rs.)',
            'Revenue %'
        ];
    }

    public function map($category): array
    {
        static $totalRevenue = null;
        
        if ($totalRevenue === null) {
            $totalRevenue = $this->categorySales->sum('revenue');
        }
        
        $avgPerTransaction = $category->transaction_count > 0 ? $category->revenue / $category->transaction_count : 0;
        $revenuePercentage = $totalRevenue > 0 ? ($category->revenue / $totalRevenue) * 100 : 0;
        
        return [
            $category->category ?? 'Uncategorized',
            number_format($category->quantity_sold),
            number_format($category->revenue, 2),
            number_format($category->transaction_count),
            number_format($avgPerTransaction, 2),
            number_format($revenuePercentage, 2) . '%'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalRevenue = $this->categorySales->sum('revenue');
        $totalQuantity = $this->categorySales->sum('quantity_sold');
        $totalTransactions = $this->categorySales->sum('transaction_count');

        // Add title with dark green background
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Sales by Category Report');
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
        $sheet->setCellValue('A2', 'Report Period:');
        $sheet->mergeCells('B2:C2');
        $sheet->setCellValue('B2', date('F d, Y', strtotime($this->startDate)) . ' to ' . date('F d, Y', strtotime($this->endDate)));
        
        $sheet->setCellValue('D2', 'Generated:');
        $sheet->mergeCells('E2:F2');
        $sheet->setCellValue('E2', now()->format('F d, Y H:i'));
        
        $sheet->setCellValue('A3', 'Total Categories:');
        $sheet->setCellValue('B3', $this->categorySales->count());
        $sheet->setCellValue('C3', 'Total Quantity:');
        $sheet->setCellValue('D3', number_format($totalQuantity));
        
        $sheet->setCellValue('A4', 'Total Revenue:');
        $sheet->mergeCells('B4:C4');
        $sheet->setCellValue('B4', 'Rs. ' . number_format($totalRevenue, 2));
        $sheet->setCellValue('D4', 'Total Transactions:');
        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E4', number_format($totalTransactions));
        
        // Style metadata section
        $sheet->getStyle('A2:F4')->applyFromArray([
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
        $sheet->getStyle('C3:C3')->getFont()->setBold(true);
        $sheet->getStyle('D2')->getFont()->setBold(true);
        $sheet->getStyle('D4')->getFont()->setBold(true);

        // Style header row with blue background
        $sheet->getStyle('A6:F6')->applyFromArray([
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
        foreach(range('A','F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to data rows
        $lastRow = 6 + $this->categorySales->count();
        $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
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
                $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFD9E1F2']
                    ]
                ]);
            }
            
            // Highlight top 3 categories
            if (($i - 6) <= 3) {
                $colors = ['FFFFD700', 'FFC0C0C0', 'FFCD7F32']; // Gold, Silver, Bronze
                $sheet->getStyle('A' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => $colors[($i - 7)] ?? 'FFFFFFFF']
                    ],
                    'font' => ['bold' => true]
                ]);
            }
        }

        // Right align numbers
        $sheet->getStyle('B7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        return 'Sales by Category';
    }
}
