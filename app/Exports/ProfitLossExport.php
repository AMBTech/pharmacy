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

class ProfitLossExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithCustomStartCell, WithEvents
{
    protected $profitLossData;
    protected $profitByCategory;
    protected $startDate;
    protected $endDate;
    protected $currency_symbol;
    protected $settings;

    public function __construct($profitLossData, $profitByCategory, $startDate, $endDate)
    {
        $this->profitLossData = $profitLossData;
        $this->profitByCategory = $profitByCategory;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        $this->currency_symbol = get_currency_symbol();
        $this->settings = SystemSetting::getSettings();
    }

    public function startCell(): string
    {
        // Headings are placed on this cell, data starts next row
        return 'A11';
    }

    public function collection()
    {
        // Create a collection that includes summary and category breakdown
        $data = collect();

        // SUMMARY rows - include relevant values in appropriate columns
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Total Revenue',
            'revenue' => $this->profitLossData['revenue'] ?? 0,
            'cost' => $this->profitLossData['cost_of_goods'] ?? 0,
            'profit' => $this->profitLossData['gross_profit'] ?? 0,
            'margin' => isset($this->profitLossData['revenue']) && $this->profitLossData['revenue'] > 0
                ? ($this->profitLossData['gross_profit'] / max(1, $this->profitLossData['revenue'])) * 100 : 0
        ]);

        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Cost of Goods Sold',
            'revenue' => null,
            'cost' => $this->profitLossData['cost_of_goods'] ?? 0,
            'profit' => null,
            'margin' => null
        ]);

        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Gross Profit',
            'revenue' => null,
            'cost' => null,
            'profit' => $this->profitLossData['gross_profit'] ?? 0,
            'margin' => isset($this->profitLossData['revenue']) && $this->profitLossData['revenue'] > 0
                ? ($this->profitLossData['gross_profit'] / max(1, $this->profitLossData['revenue'])) * 100 : 0
        ]);

        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Discounts Given',
            'revenue' => $this->profitLossData['discount_given'] ?? 0,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);

        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Tax Collected',
            'revenue' => $this->profitLossData['tax_collected'] ?? 0,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);

        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Net Profit',
            'revenue' => $this->profitLossData['net_profit'] ?? 0,
            'cost' => null,
            'profit' => null,
            'margin' => isset($this->profitLossData['revenue']) && $this->profitLossData['revenue'] > 0
                ? ($this->profitLossData['net_profit'] / max(1, $this->profitLossData['revenue'])) * 100 : 0
        ]);

        // Spacer row
        $data->push((object)[
            'type' => 'SPACER',
            'category' => '',
            'revenue' => null,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);

        // Category header sub-title
        $data->push((object)[
            'type' => 'HEADER',
            'category' => 'PROFIT BY CATEGORY',
            'revenue' => null,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);

        // Add the category rows (expected objects with category, revenue, cost, profit)
        foreach ($this->profitByCategory as $category) {
            $row = is_array($category) ? (object)$category : $category;
            $row->revenue = $row->revenue ?? 0;
            $row->cost = $row->cost ?? 0;
            $row->profit = $row->profit ?? ($row->revenue - $row->cost);
            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Category',
            "Revenue ({$this->currency_symbol})",
            "Cost ({$this->currency_symbol})",
            "Profit ({$this->currency_symbol})",
            'Margin (%)'
        ];
    }

    public function map($row): array
    {
        if (isset($row->type)) {
            if ($row->type === 'SUMMARY') {
                switch ($row->category) {
                    case 'Total Revenue':
                        return [
                            $row->category,
                            $row->revenue !== null ? number_format((float)$row->revenue, 2) : '',
                            $row->cost !== null ? number_format((float)$row->cost, 2) : '',
                            $row->profit !== null ? number_format((float)$row->profit, 2) : '',
                            $row->margin !== null ? number_format((float)$row->margin, 2) : ''
                        ];
                    case 'Cost of Goods Sold':
                        return [
                            $row->category,
                            '',
                            $row->cost !== null ? number_format((float)$row->cost, 2) : '',
                            '',
                            ''
                        ];
                    case 'Gross Profit':
                        return [
                            $row->category,
                            '',
                            '',
                            $row->profit !== null ? number_format((float)$row->profit, 2) : '',
                            $row->margin !== null ? number_format((float)$row->margin, 2) : ''
                        ];
                    case 'Discounts Given':
                    case 'Tax Collected':
                    case 'Net Profit':
                        return [
                            $row->category,
                            $row->revenue !== null ? number_format((float)$row->revenue, 2) : '',
                            '',
                            '',
                            $row->margin !== null ? number_format((float)$row->margin, 2) : ''
                        ];
                    default:
                        return [$row->category, '', '', '', ''];
                }
            } elseif ($row->type === 'SPACER') {
                return ['', '', '', '', ''];
            } elseif ($row->type === 'HEADER') {
                return [$row->category, '', '', '', ''];
            }
        }

        $revenue = (float)($row->revenue ?? 0);
        $cost = (float)($row->cost ?? 0);
        $profit = (float)($row->profit ?? ($revenue - $cost));
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return [
            $row->category ?? 'Uncategorized',
            number_format($revenue, 2),
            number_format($cost, 2),
            number_format($profit, 2),
            number_format($margin, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // ---------- Header: Clinic name ----------
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', $this->settings['company_name']);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 22,
                'color' => ['argb' => 'FF000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('1')->setRowHeight(36);

        // Subheader: Address
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', $this->settings['company_address']);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['argb' => 'FF4B5563']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension('2')->setRowHeight(18);

        // Divider row
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A3:E3')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ],
        ]);
        $sheet->getRowDimension('3')->setRowHeight(66);

        // Title row
        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', 'Profit & Loss Report');
        $sheet->getRowDimension('5')->setRowHeight(20);
        $sheet->getStyle('A5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Basic report info
        $sheet->setCellValue('A7', 'Report Period:');
        $sheet->setCellValue('B7', now()->month);
        $sheet->setCellValue('A8', 'Date Generated:');
        $sheet->setCellValue('B8', now()->format('M d, Y H:i'));
        $sheet->setCellValue('A9', 'Generated by:');
        $sheet->setCellValue('B9', auth()->user()->name ?? 'System');
        $sheet->getStyle('A6:A9')->getFont()->setBold(true);
        $sheet->getRowDimension('6')->setRowHeight(16);
        $sheet->getRowDimension('7')->setRowHeight(16);

        // Column widths (reduced to better fit A4 landscape)
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(13.5);

        // Place image (unchanged)
        $this->placeImage($sheet);

        // Header row styling (table headings at A11:E11)
        $sheet->getStyle('A11:E11')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
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
        $sheet->getRowDimension('11')->setRowHeight(20);

        return [];
    }

    private function placeImage($sheet)
    {
        $imagePath = public_path('images/clinic-logo.png');
        if (file_exists($imagePath)) {
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
            $drawing->setCoordinates('B3');

            $offsetX = (int)round(($mergedCellWidthPx - $renderedWidthPx) / 2);
            $offsetY = (int)round(($rowHeightPx - $renderedHeightPx) / 2);

            $drawing->setOffsetX(max(0, $offsetX));
            $drawing->setOffsetY(max(0, $offsetY));

            $sheet->getStyle('B3')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $drawing->setWorksheet($sheet);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->getSheet()->getDelegate();

                // Footer: "Page X of Y"
                $sheet->getHeaderFooter()->setOddFooter('&RPage &P of &N');

                // Compute row positions:
                // headings at row 11, data starts at row 12
                $dataStartRow = 12;

                // We push 6 summary rows + 1 spacer + 1 header before categories
                $summaryCount = 6;
                $spacerCount = 1;
                $headerRow = $dataStartRow + $summaryCount + $spacerCount; // row index of "PROFIT BY CATEGORY"

                $categoryCount = is_countable($this->profitByCategory) ? count($this->profitByCategory) : 0;
                $dataEndRow = $headerRow + 1 + max(0, $categoryCount);

                // Merge & style the Profit by Category header row:
                $sheet->mergeCells("A{$headerRow}:E{$headerRow}");
                $sheet->setCellValue("A{$headerRow}", 'PROFIT BY CATEGORY');
                $sheet->getStyle("A{$headerRow}:E{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFEEEEEE']
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(18);

                // Apply borders to the whole data area (heading row -> end)
                $rangeBorders = "A11:E{$dataEndRow}";
                $sheet->getStyle($rangeBorders)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // Right align numeric columns
                $sheet->getStyle("B11:E{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Number formats
                $sheet->getStyle("B12:D{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("E12:E{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Bold summary rows
                for ($i = 0; $i < $summaryCount; $i++) {
                    $r = $dataStartRow + $i;
                    $sheet->getStyle("A{$r}:E{$r}")->getFont()->setBold(true);
                }

                // Highlight Net Profit row
                $netProfitRow = $dataStartRow + ($summaryCount - 1);
                $sheet->getStyle("A{$netProfitRow}:E{$netProfitRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFD1FAE5']
                    ],
                ]);

                // --------- A4 print settings (fit to page) ----------
                // Set A4 paper, landscape orientation, fit-to-width
                $pageSetup = $sheet->getPageSetup();
                $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // Fit to width (1 page wide), allow multiple pages vertically if needed
                $pageSetup->setFitToWidth(1);
                $pageSetup->setFitToHeight(0);
                $pageSetup->setFitToPage(true);

                // Set print area so Excel knows what to scale
                $sheet->getParent()->getActiveSheet()->getPageSetup();
                $sheet->getPageSetup()->setPrintArea("A1:E{$dataEndRow}");

                // Set margins (in inches)
                $margins = $sheet->getPageMargins();
                $margins->setTop(0.4);
                $margins->setRight(0.25);
                $margins->setLeft(0.25);
                $margins->setBottom(0.4);

                // Optional: scale down slightly if still large (tweak as needed)
                // $pageSetup->setScale(90);

                // Optionally freeze header row so it's visible while scrolling in Excel (not for print)
                // $sheet->freezePane('A12');

            }
        ];
    }

    public function title(): string
    {
        return 'Profit & Loss Report';
    }
}
