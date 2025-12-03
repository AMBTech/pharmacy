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

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    protected $products;
    protected $filters;

    public function __construct($products, $filters = [])
    {
        $this->products = $products;
        $this->filters = $filters;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'ITEM CODE',
            'PRODUCT NAME',
            'DESCRIPTION',
            'AVAILABLE QTY',
            'UNIT COST',
            'INVENTORY VALUE',
            'REORDER LEVEL',
            'LEAD TIME (DAYS)',
            'PHASED OUT',
            'MANUFACTURER'
        ];
    }

    public function map($product): array
    {
        $settings = \App\Models\SystemSetting::getSettings();
        
        // Get first active batch for cost price
        $activeBatch = $product->batches()->where('quantity', '>', 0)->first();
        $costPrice = $activeBatch ? $activeBatch->cost_price : 0;
        
        // Calculate reorder level (example: 10% of current stock or minimum 10)
        $reorderLevel = max(10, floor($product->stock * 0.1));
        
        // Lead time (example: 5-10 days based on stock level)
        $leadTime = $product->stock < 20 ? 5 : 10;
        
        // Phased out status
        $phasedOut = $product->is_active ? 'NO' : 'YES';

        return [
            $product->barcode ?: 'N/A',
            $product->name,
            $product->generic_name ?: ($product->category->name ?? 'N/A'),
            number_format($product->stock),
            number_format($costPrice, 2),
            number_format($product->stock * $costPrice, 2),
            $reorderLevel,
            $leadTime,
            $phasedOut,
            $product->brand ?: 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $settings = \App\Models\SystemSetting::getSettings();
        $totalProducts = $this->products->count();
        $totalQty = $this->products->sum('stock');
        $totalValue = $this->products->sum(function($product) {
            $activeBatch = $product->batches()->where('quantity', '>', 0)->first();
            $costPrice = $activeBatch ? $activeBatch->cost_price : 0;
            return $product->stock * $costPrice;
        });

        // Add title with dark green background
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'Inventory Report Sample Template');
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

        // Add report metadata section with beige/tan background
        // Row 2: Title, Inventory Period, Total values
        $sheet->setCellValue('A2', 'Title:');
        $sheet->mergeCells('B2:D2');
        $sheet->setCellValue('B2', 'Annual Report for the year of ' . now()->year);
        
        $sheet->mergeCells('E2:G2');
        $sheet->setCellValue('E2', 'Inventory Period:');
        $sheet->getStyle('E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('H2:I2');
        $sheet->setCellValue('H2', 'Total available Quantity');
        $sheet->getStyle('H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('J2', 'Total Inventory Value');
        $sheet->getStyle('J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Row 3: Date, From/To headers, Total Qty value, Total Value
        $sheet->setCellValue('A3', 'Date:');
        $sheet->mergeCells('B3:D3');
        $sheet->setCellValue('B3', now()->format('F d, Y'));
        
        $sheet->setCellValue('E3', 'From');
        $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3')->getFont()->setBold(true);
        
        $sheet->setCellValue('F3', 'To');
        $sheet->getStyle('F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3')->getFont()->setBold(true);
        
        $sheet->mergeCells('G3:I3');
        
        $sheet->setCellValue('H3', number_format($totalQty));
        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H3')->getFont()->setBold(true);
        $sheet->getStyle('H3')->getFont()->setSize(12);
        
        $sheet->setCellValue('J3', 'Rs. ' . number_format($totalValue, 2));
        $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J3')->getFont()->setBold(true);
        $sheet->getStyle('J3')->getFont()->setSize(12);
        
        // Row 4: Prepared by, Date values
        $sheet->setCellValue('A4', 'Prepared by:');
        $sheet->mergeCells('B4:D4');
        $sheet->setCellValue('B4', auth()->user()->name ?? 'System');
        
        $sheet->setCellValue('E4', now()->subMonth()->format('m-d-Y'));
        $sheet->getStyle('E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('F4', now()->format('m-d-Y'));
        $sheet->getStyle('F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Style metadata section with tan/beige background
        $sheet->getStyle('A2:J4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFD9D2E9']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);
        
        // Bold labels
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->getStyle('E2')->getFont()->setBold(true);
        $sheet->getStyle('H2')->getFont()->setBold(true);
        $sheet->getStyle('J2')->getFont()->setBold(true);

        // Add row 5 as empty/spacing row
        $sheet->getRowDimension('5')->setRowHeight(5);
        
        // Add row 6 (header row) with blue background
        $sheet->getStyle('A6:J6')->applyFromArray([
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
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
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
        foreach(range('A','J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to data rows
        $lastRow = 6 + $this->products->count();
        $sheet->getStyle('A6:J' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);

        // Add alternating row colors (light blue and white) for data rows
        for ($i = 7; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':J' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFD9E1F2']
                    ]
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Inventory Report';
    }
}
