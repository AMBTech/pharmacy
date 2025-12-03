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

class SalesTrendsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $salesData;
    protected $topProducts;
    protected $paymentMethods;
    protected $period;
    protected $days;

    public function __construct($salesData, $topProducts, $paymentMethods, $period, $days)
    {
        $this->salesData = $salesData;
        $this->topProducts = $topProducts;
        $this->paymentMethods = $paymentMethods;
        $this->period = $period;
        $this->days = $days;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function collection()
    {
        return $this->salesData;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Transactions',
            'Revenue (Rs.)',
            'Average Sale (Rs.)'
        ];
    }

    public function map($sale): array
    {
        return [
            date('M d, Y', strtotime($sale->date)),
            $sale->transactions,
            number_format($sale->revenue, 2),
            number_format($sale->average_sale, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add title
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'Sales Trends Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF2C5F2D']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(35);

        // Add report info
        $sheet->setCellValue('A3', 'Report Period:');
        $sheet->setCellValue('B3', ucfirst($this->period) . ' - Last ' . $this->days . ' days');
        $sheet->setCellValue('C3', 'Date Generated:');
        $sheet->setCellValue('D3', now()->format('M d, Y H:i'));
        
        $sheet->getStyle('A3:A3')->getFont()->setBold(true);
        $sheet->getStyle('C3:C3')->getFont()->setBold(true);

        // Style header row
        $sheet->getStyle('A5:D5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF2563EB']
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

        // Auto-size columns
        foreach(range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to data
        $lastRow = 5 + $this->salesData->count();
        $sheet->getStyle('A5:D' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB']
                ]
            ]
        ]);

        // Add alternating row colors
        for ($i = 6; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':D' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFF9FAFB']
                    ]
                ]);
            }
        }

        // Add summary section
        $summaryRow = $lastRow + 2;
        $sheet->setCellValue('A' . $summaryRow, 'SUMMARY');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);
        
        $summaryRow++;
        $totalTransactions = $this->salesData->sum('transactions');
        $totalRevenue = $this->salesData->sum('revenue');
        $avgSale = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        
        $sheet->setCellValue('A' . $summaryRow, 'Total Transactions:');
        $sheet->setCellValue('B' . $summaryRow, $totalTransactions);
        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Total Revenue:');
        $sheet->setCellValue('B' . $summaryRow, 'Rs. ' . number_format($totalRevenue, 2));
        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, 'Overall Average Sale:');
        $sheet->setCellValue('B' . $summaryRow, 'Rs. ' . number_format($avgSale, 2));

        return [];
    }

    public function title(): string
    {
        return 'Sales Trends';
    }
}
