<?php

namespace App\Exports;

use App\Models\SystemSetting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpiringProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell, WithEvents
{
    protected $expiringProducts;
    protected $filters;
    protected $settings;

    public function __construct($expiringProducts, $filters = [])
    {
        $this->expiringProducts = $expiringProducts;
        $this->filters = $filters;
        $this->settings = SystemSetting::getSettings();
    }

    public function startCell(): string
    {
        return 'A11';
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
        $daysToExpiry = $batch->days_until_expiry ?? null;
        $isExpired = $daysToExpiry !== null && $daysToExpiry < 0;

        // Determine status
        if ($isExpired) {
            $status = 'Expired (' . abs($daysToExpiry) . ' days ago)';
        } elseif ($daysToExpiry !== null && $daysToExpiry <= 7) {
            $status = 'Critical';
        } elseif ($daysToExpiry !== null && $daysToExpiry <= 30) {
            $status = 'Warning';
        } else {
            $status = 'Good';
        }

        // Stock status
        if (($batch->quantity ?? 0) == 0) {
            $stockStatus = 'Depleted';
        } elseif (($batch->product->stock ?? 0) < 10) {
            $stockStatus = 'Low Stock';
        } else {
            $stockStatus = 'In Stock';
        }

        $totalValue = ($batch->quantity ?? 0) * ($batch->cost_price ?? 0);

        return [
            $batch->product->name ?? 'N/A',
            $batch->product->category->name ?? 'Uncategorized',
            $batch->product->brand ?: 'N/A',
            $batch->product->generic_name ?: 'N/A',
            $batch->batch_number ?? '',
            isset($batch->manufacturing_date) ? $batch->manufacturing_date->format('Y-m-d') : '',
            isset($batch->expiry_date) ? $batch->expiry_date->format('Y-m-d') : '',
            $isExpired ? 'Expired' : (($daysToExpiry !== null) ? $daysToExpiry . ' days' : 'N/A'),
            (int)($batch->quantity ?? 0),
            (int)($batch->product->stock ?? 0),
            number_format((float)($batch->cost_price ?? 0), 2),
            number_format((float)$totalValue, 2),
            $status . ' - ' . $stockStatus
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // minimal styling here; main styling applied in AfterSheet where we know row counts
        return [
            // keep first row (company title area) default — we'll style specific rows in AfterSheet
            11 => [ // the header row index (A11) will be restyled in AfterSheet, but return empty here to avoid conflicts
                'font' => ['bold' => true]
            ],
        ];
    }

    /**
     * Re-used placeImage helper (same mechanics as your other export).
     * Centers logo inside merged B3:C3 area.
     */
    private function placeImage(Worksheet $sheet)
    {
        $imagePath = public_path('images/clinic-logo.png');
        if (!file_exists($imagePath)) {
            return;
        }

        $colWidthToPx = function ($width) {
            if (!$width || $width <= 0) $width = 8.43;
            return (int)round($width * 7 + 5);
        };

        $colBWidth = $sheet->getColumnDimension('B')->getWidth();
        $colCWidth = $sheet->getColumnDimension('C')->getWidth();

        if (!$colBWidth) $colBWidth = 8.43;
        if (!$colCWidth) $colCWidth = 8.43;

        $colBWidthPx = $colWidthToPx($colBWidth);
        $colCWidthPx = $colWidthToPx($colCWidth);
        $mergedCellWidthPx = $colBWidthPx + $colCWidthPx;

        $rowHeightPoints = $sheet->getRowDimension(3)->getRowHeight();
        if (!$rowHeightPoints) $rowHeightPoints = 66;
        $rowHeightPx = $rowHeightPoints * 1.333333;

        [$origW, $origH] = getimagesize($imagePath);

        $marginPx = 8;
        $maxWidth = max(1, $mergedCellWidthPx - ($marginPx * 2));
        $maxHeight = max(1, $rowHeightPx - ($marginPx * 2));

        $scaleW = $maxWidth / $origW;
        $scaleH = $maxHeight / $origH;
        $scale = min($scaleW, $scaleH, 1);

        $renderedWidthPx = (int)round($origW * $scale);
        $renderedHeightPx = (int)round($origH * $scale);

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Clinic Logo');
        $drawing->setDescription('Clinic Logo');
        $drawing->setPath($imagePath);
        $drawing->setWidth($renderedWidthPx);
        $drawing->setCoordinates('E3');

        $offsetX = (int)round(($mergedCellWidthPx - $renderedWidthPx) / 2) + 20;
        $offsetY = (int)round(($rowHeightPx - $renderedHeightPx) / 2);

        $drawing->setOffsetX(max(0, $offsetX));
        $drawing->setOffsetY(max(0, $offsetY));

        $sheet->getStyle('B3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $drawing->setWorksheet($sheet);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->getSheet()->getDelegate();

                // ---------- Header: Company name & address ----------
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', $this->settings['company_name'] ?? 'Company Name');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF000000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', $this->settings['company_address'] ?? '');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF4B5563']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Divider / logo row
                $sheet->mergeCells('A3:A3'); // keep A3 available if needed
                $sheet->mergeCells('E3:G3'); // logo sits in merged B3:C3
                $sheet->getRowDimension(3)->setRowHeight(66);

                // Title
                $sheet->mergeCells('A5:M5');
                $sheet->setCellValue('A5', 'Expiring Products Report');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                $sheet->getRowDimension(5)->setRowHeight(20);

                // Report metadata
                $sheet->setCellValue('A7', 'Report Period:');
                $sheet->setCellValue('B7', data_get($this->filters, 'period', now()->format('M Y')));
                $sheet->setCellValue('A8', 'Date Generated:');
                $sheet->setCellValue('B8', now()->format('M d, Y H:i'));
                $sheet->setCellValue('A9', 'Generated by:');
                $sheet->setCellValue('B9', auth()->user()->name ?? 'System');
                $sheet->getStyle('A7:A9')->getFont()->setBold(true);
                $sheet->getRowDimension(7)->setRowHeight(16);

                // Column widths tuned for A4 landscape (13 columns: A..M)
                $sheet->getColumnDimension('A')->setWidth(30); // Product Name
                $sheet->getColumnDimension('B')->setWidth(18); // Category
                $sheet->getColumnDimension('C')->setWidth(16); // Brand
                $sheet->getColumnDimension('D')->setWidth(16); // Generic Name
                $sheet->getColumnDimension('E')->setWidth(14); // Batch Number
                $sheet->getColumnDimension('F')->setWidth(12); // Mfg Date
                $sheet->getColumnDimension('G')->setWidth(12); // Expiry Date
                $sheet->getColumnDimension('H')->setWidth(12); // Days to Expiry
                $sheet->getColumnDimension('I')->setWidth(12); // Batch Quantity
                $sheet->getColumnDimension('J')->setWidth(12); // Product Stock
                $sheet->getColumnDimension('K')->setWidth(14); // Cost Price
                $sheet->getColumnDimension('L')->setWidth(14); // Batch Value
                $sheet->getColumnDimension('M')->setWidth(24); // Status

                // Place image (uses same logic as previous export)
                $this->placeImage($sheet);

                // Style header row (headings placed at A11:M11)
                $sheet->getStyle('A11:M11')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]
                ]);
                $sheet->getRowDimension(11)->setRowHeight(20);

                // Compute data range:
                // headings at row 11, data starts at 12
                $dataStartRow = 12;
                $rowCount = is_countable($this->expiringProducts) ? count($this->expiringProducts) : 0;
                $dataEndRow = $dataStartRow + max(0, $rowCount - 1);

                // If there are no rows, ensure at least one data row for formatting
                if ($rowCount === 0) $dataEndRow = $dataStartRow;

                // Apply borders to the whole data area
                $range = "A11:M{$dataEndRow}";
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]
                ]);

                // Align numeric columns to right and text to left
                $sheet->getStyle("I{$dataStartRow}:L{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$dataStartRow}:D{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("M{$dataStartRow}:M{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Number formats for quantity and monetary columns
                // Quantity columns (I and J) -> integer
                $sheet->getStyle("I{$dataStartRow}:J{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0');
                // Monetary columns (K,L) -> two decimals
                $sheet->getStyle("K{$dataStartRow}:L{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Highlight critical / expired rows (we can't easily loop values here unless we read cells;
                // but we can color entire row if you want to map statuses — easiest way is to post-process
                // or set conditional formatting. For brevity, we'll add conditional formatting for text 'Expired' & 'Critical'.)

                // Conditional formatting: 'Expired' in column H or 'Expired' in M -> light red fill
                $conditionalExpired = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $conditionalExpired->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
                    ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
                    ->setText('Expired')
                    ->getStyle()->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFE4E6']],
                    ]);

                // Conditional formatting: 'Critical' -> light orange fill
                $conditionalCritical = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $conditionalCritical->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT)
                    ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT)
                    ->setText('Critical')
                    ->getStyle()->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFF7ED']],
                    ]);

                $conditionalStyles = $sheet->getStyle("M{$dataStartRow}:M{$dataEndRow}")->getConditionalStyles();
                $conditionalStyles[] = $conditionalExpired;
                $conditionalStyles[] = $conditionalCritical;
                $sheet->getStyle("M{$dataStartRow}:M{$dataEndRow}")->setConditionalStyles($conditionalStyles);

                // Freeze header row for browsing (optional)
                // $sheet->freezePane('A12');

                // ---------- A4 print setup (landscape fit-to-width) ----------
                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
                $pageSetup->setFitToPage(true);

                // set print area via PageSetup
                $sheet->getPageSetup()->setPrintArea("A1:M{$dataEndRow}");

                // margins (in inches)
                $margins = $sheet->getPageMargins();
                $margins->setTop(0.4);
                $margins->setRight(0.25);
                $margins->setLeft(0.25);
                $margins->setBottom(0.4);

                // Footer page number
                $sheet->getHeaderFooter()->setOddFooter('&RPage &P of &N');
            }
        ];
    }

    public function title(): string
    {
        return 'Expiring Products';
    }
}
