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

class SalesByCashierExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $cashierPerformance;
    protected $startDate;
    protected $endDate;
    protected $cashierName;

    public function __construct($cashierPerformance, $startDate, $endDate, $cashierName = null)
    {
        $this->cashierPerformance = $cashierPerformance;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->cashierName = $cashierName;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        return $this->cashierPerformance->sortByDesc('total_revenue')->values();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Cashier Name',
            'Role',
            'Total Sales',
            'Total Revenue (Rs.)',
            'Average Sale (Rs.)'
        ];
    }

    public function map($cashier): array
    {
        static $rank = 0;
        $rank++;

        return [
            $rank,
            $cashier->name,
            $cashier->role->display_name ?? 'Cashier',
            number_format($cashier->total_sales),
            number_format($cashier->total_revenue, 2),
            number_format($cashier->average_sale, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $totalSales = $this->cashierPerformance->sum('total_sales');
        $totalRevenue = $this->cashierPerformance->sum('total_revenue');
        $avgSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Add title with dark green background
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Sales by Cashier Report');
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
        
        if ($this->cashierName) {
            $sheet->setCellValue('D2', 'Cashier:');
            $sheet->mergeCells('E2:F2');
            $sheet->setCellValue('E2', $this->cashierName);
        }
        
        $sheet->setCellValue('A3', 'Generated:');
        $sheet->mergeCells('B3:C3');
        $sheet->setCellValue('B3', now()->format('F d, Y H:i'));
        
        $sheet->setCellValue('A4', 'Total Cashiers:');
        $sheet->setCellValue('B4', $this->cashierPerformance->count());
        $sheet->setCellValue('C4', 'Total Sales:');
        $sheet->setCellValue('D4', number_format($totalSales));
        $sheet->setCellValue('E4', 'Total Revenue:');
        $sheet->setCellValue('F4', 'Rs. ' . number_format($totalRevenue, 2));
        
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
        $sheet->getStyle('C4')->getFont()->setBold(true);
        $sheet->getStyle('E4')->getFont()->setBold(true);

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
        $lastRow = 6 + $this->cashierPerformance->count();
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
            
            // Highlight top 3 cashiers
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

        // Center align rank and numbers
        $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function title(): string
    {
        return 'Sales by Cashier';
    }
}
