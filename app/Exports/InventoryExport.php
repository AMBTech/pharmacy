<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell, WithColumnFormatting, WithEvents
{
    protected $products;
    protected $filters;
    protected $currency_symbol;
    protected $settings;

    public function __construct($products, $filters = [])
    {
        $this->products = $products;
        $this->filters = $filters;

        $this->currency_symbol = get_currency_symbol();
        $this->settings = \App\Models\SystemSetting::getSettings();
    }

    public function startCell(): string
    {
        return 'A11';
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
            "UNIT COST ({$this->currency_symbol})",
            "INVENTORY VALUE ({$this->currency_symbol})",
            'REORDER LEVEL',
            'LEAD TIME (DAYS)',
            'PHASED OUT',
            'MANUFACTURER'
        ];
    }

    public function map($product): array
    {
        // Get first active batch for cost price
        $activeBatch = $product->batches()->where('quantity', '>', 0)->first();
        $costPrice = $activeBatch ? $activeBatch->cost_price : 0;
        $inventoryValue = $product->stock * $costPrice;

        // Calculate reorder level (example: 10% of current stock or minimum 10)
        $reorderLevel = max(10, floor($product->stock * 0.1));

        // Lead time (example: 5-10 days based on stock level)
        $leadTime = $product->stock < 20 ? 5 : 10;

        // Phased out status
        $phasedOut = $product->is_active ? 'NO' : 'YES';

        return [
            (string)$product->barcode ?: 'N/A',
            $product->name,
            $product->generic_name ?: ($product->category->name ?? 'N/A'),
            (int)$product->stock,
            (float)$costPrice,
            (float)$inventoryValue,
            (int)$reorderLevel,
            (int)$leadTime,
            $phasedOut,
            $product->brand ?: 'N/A'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00, // UNIT COST
            'F' => NumberFormat::FORMAT_NUMBER_00, // INVENTORY VALUE
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ---------- Header: Clinic name ----------
        // Big clinic name centered across A1:D1

        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', $this->settings['company_name']);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 22,
                'color' => ['argb' => 'FF000000'], // black text like the scanned header
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(36);

        // ---------- Subheader: Address ----------
        $sheet->mergeCells('A2:J2');
//        $sheet->setCellValue('A2', 'Mehmoodabad Kamila road Near Tariq Nursery Jhelum');
        $sheet->setCellValue('A2', $this->settings['company_address']);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['argb' => 'FF4B5563'] // muted gray
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('2')->setRowHeight(18);

        // ---------- Divider line ----------
        // To visually mimic the thin underline present in the scanned header,
        // we add a bottom border to row 3 across A3:D3 (empty row used as divider)

        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A3:J3')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
//                    'color' => ['argb' => 'FF9CA3AF']
                ]
            ]
        ]);
        // Set row 3 height to 66 for logo placement
        $sheet->getRowDimension('3')->setRowHeight(66);

        // Make the labels bold and slightly larger
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('D4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);

        // ---------- Set column widths BEFORE placing image ----------
        $sheet->getColumnDimension('A')->setWidth(20);  // ITEM CODE
        $sheet->getColumnDimension('B')->setWidth(25);  // PRODUCT NAME
        $sheet->getColumnDimension('C')->setWidth(30);  // DESCRIPTION
        $sheet->getColumnDimension('D')->setWidth(18.57);  // AVAILABLE QTY
        $sheet->getColumnDimension('E')->setWidth(16.43);  // UNIT COST
        $sheet->getColumnDimension('F')->setWidth(20);  // INVENTORY VALUE
        $sheet->getColumnDimension('G')->setWidth(15);  // REORDER LEVEL
        $sheet->getColumnDimension('H')->setWidth(15);  // LEAD TIME
        $sheet->getColumnDimension('I')->setWidth(15);  // PHASED OUT
        $sheet->getColumnDimension('J')->setWidth(20);  // MANUFACTURER


        // Prepare image (now column widths are set)
        $this->placeImage($sheet);

        // Center align inside merged cell
        $sheet->getStyle('B3')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('@');


        // ---------- Spacer row (5) ----------
        $sheet->getRowDimension('4')->setRowHeight(18);

        // ---------- Report Title (optional) ----------
        // If you want to keep the "Sales Trends Report" title above the table, put it here.
        // Otherwise this block is harmless and can be adjusted/removed.
        $sheet->mergeCells('A6:J6');
        $sheet->setCellValue('A5', 'Inventory Report');
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->getRowDimension('6')->setRowHeight(18);

        // -------- Force Excel to Fit Sheet to A4 Page ---------
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

        // Fit all columns to 1 page width
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0); // unlimited height


        // -------- Set Margins (match physical A4 layout) ----------
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setBottom(0.5);
        $sheet->getPageMargins()->setLeft(0.3);
        $sheet->getPageMargins()->setRight(0.3);

        // -------- Merge cells ----------
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A5:J5');

        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:J2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:J5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);




        // ---------- Report info (period & generated date) ----------
        $sheet->setCellValue('A7', 'Report Period:');
        $sheet->setCellValue('B7', 'Annual Report for ' . now()->year);
        $sheet->setCellValue('A8', 'Date Generated:');
        $sheet->setCellValue('B8', now()->format('M d, Y H:i'));
        $sheet->setCellValue('A9', 'Generated by:');
        $sheet->setCellValue('B9', auth()->user()->name ?? 'System');
        $sheet->getStyle('A7:A9')->getFont()->setBold(true);
        $sheet->getRowDimension('7')->setRowHeight(16);
        $sheet->getRowDimension('8')->setRowHeight(16);

        // ---------- Header Row Styling for the table ----------
        $sheet->getStyle('A11:J11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF2563EB'] // keep blue header for the table for clarity
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
        $sheet->getRowDimension('11')->setRowHeight(20);

        // ---------- Add borders to data rows ----------
        $lastRow = 11 + $this->products->count();
        $sheet->getStyle('A12:J' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB']
                ]
            ]
        ]);

        // ---------- Alternating row colors ----------
        for ($i = 12; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':D' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFF9FAFB']
                    ]
                ]);
            }
        }

        // ---------- Summary Section ----------
        $summaryRow = $lastRow + 4;
        $sheet->setCellValue('A' . $summaryRow, 'SUMMARY');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);

        $summaryRow++;
        $totalTransactions = $this->products->sum('transactions');
        $totalRevenue = $this->products->sum('revenue');
        $avgSale = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $sheet->mergeCells("A{$summaryRow}:B{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Total Transactions:');
        $sheet->setCellValue('C' . $summaryRow, $totalTransactions);
        $summaryRow++;
        $sheet->mergeCells("A{$summaryRow}:B{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Total Revenue:');
        $sheet->setCellValue('C' . $summaryRow, "{$this->currency_symbol} " . number_format($totalRevenue, 2));
        $summaryRow++;
        $sheet->mergeCells("A{$summaryRow}:B{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", 'Overall Average Sale:');
        $sheet->setCellValue('C' . $summaryRow, "{$this->currency_symbol} " . number_format($avgSale, 2));

        return [];
    }


    private function placeImage($sheet)
    {
        // Path to image
        $imagePath = public_path('images/clinic-logo.png');
        if (file_exists($imagePath)) {
            // --- Helper: convert Excel column width units -> approximate pixels ---
            $colWidthToPx = function($width){
                // Common approximation: px ≈ width * 7 + 5
                // Ensure width fallback to Excel default 8.43 when not set
                if (!$width || $width <= 0) $width = 8.43;
                return (int) round($width * 7 + 5);
            };

            // --- Compute merged cell width (B + C) in pixels ---
            $colBWidth = $sheet->getColumnDimension('B')->getWidth();
            $colCWidth = $sheet->getColumnDimension('C')->getWidth();

            if (!$colBWidth) $colBWidth = 8.43;
            if (!$colCWidth) $colCWidth = 8.43;

            $colBWidthPx = $colWidthToPx($colBWidth);
            $colCWidthPx = $colWidthToPx($colCWidth);
            $mergedCellWidthPx = $colBWidthPx + $colCWidthPx;

            // --- Compute merged row height in pixels ---
            $rowHeightPoints = $sheet->getRowDimension(3)->getRowHeight();
            if (!$rowHeightPoints) $rowHeightPoints = 66; // fallback
            $rowHeightPx = $rowHeightPoints * 1.333333; // points -> px approx

            // --- Load original image size (px) ---
            [$origW, $origH] = getimagesize($imagePath);

            // --- Decide margins in px (space left on each side inside merged cell) ---
            $marginPx = 8; // tune this value, 8px margin each side

            // --- Compute max available width / height inside merged cell ---
            $maxWidth = max(1, $mergedCellWidthPx - ($marginPx * 2));
            $maxHeight = max(1, $rowHeightPx - ($marginPx * 2));

            // --- Compute scale to fit inside available box while preserving aspect ratio ---
            $scaleW = $maxWidth / $origW;
            $scaleH = $maxHeight / $origH;
            $scale = min($scaleW, $scaleH, 1); // don't upscale beyond 100%

            $renderedWidthPx = (int) round($origW * $scale);
            $renderedHeightPx = (int) round($origH * $scale);

            // --- Create drawing and set size using computed width/height ---
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Clinic Logo');
            $drawing->setDescription('Clinic Logo');
            $drawing->setPath($imagePath);

            // Prefer setting width (and height will follow to keep aspect ratio)
            $drawing->setWidth($renderedWidthPx);
            // You can also setHeight($renderedHeightPx) if you prefer explicit height

            // Coordinates set to the top-left cell of merged area
            $drawing->setCoordinates('E3');

            // --- Compute offsets to perfectly center the image inside merged cell ---
            $offsetX = (int) round( ($mergedCellWidthPx - $renderedWidthPx) / 2 );
            $offsetY = (int) round( ($rowHeightPx - $renderedHeightPx) / 2 );

            // Apply offsets (make sure offsets are non-negative)
//            $drawing->setOffsetX(138);
            $drawing->setOffsetY(max(0, $offsetY));

            // Optional: center text alignment in the cell (for any cell text)
            $sheet->getStyle('E3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Add the drawing to the sheet
            $drawing->setWorksheet($sheet);
        }

    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Footer: "Page X of Y"
                $event->getSheet()->getDelegate()
                    ->getHeaderFooter()
                    ->setOddFooter('&RPage &P of &N');
            },
        ];
    }

    public function title(): string
    {
        return 'Inventory Report';
    }
}
