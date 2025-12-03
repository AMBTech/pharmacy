<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $profitLossData;
    protected $profitByCategory;
    protected $startDate;
    protected $endDate;

    public function __construct($profitLossData, $profitByCategory, $startDate, $endDate)
    {
        $this->profitLossData = $profitLossData;
        $this->profitByCategory = $profitByCategory;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Create a collection that includes summary and category breakdown
        $data = collect();
        
        // Add summary section
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Total Revenue',
            'revenue' => $this->profitLossData['revenue'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Cost of Goods Sold',
            'revenue' => $this->profitLossData['cost_of_goods'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Gross Profit',
            'revenue' => $this->profitLossData['gross_profit'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Discounts Given',
            'revenue' => $this->profitLossData['discount_given'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Tax Collected',
            'revenue' => $this->profitLossData['tax_collected'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        $data->push((object)[
            'type' => 'SUMMARY',
            'category' => 'Net Profit',
            'revenue' => $this->profitLossData['net_profit'],
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        // Add empty row
        $data->push((object)[
            'type' => 'SPACER',
            'category' => '',
            'revenue' => null,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        // Add category breakdown header
        $data->push((object)[
            'type' => 'HEADER',
            'category' => 'PROFIT BY CATEGORY',
            'revenue' => null,
            'cost' => null,
            'profit' => null,
            'margin' => null
        ]);
        
        // Add category data
        foreach ($this->profitByCategory as $category) {
            $data->push($category);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Category',
            'Revenue (Rs.)',
            'Cost (Rs.)',
            'Profit (Rs.)',
            'Margin (%)'
        ];
    }

    public function map($row): array
    {
        if (isset($row->type)) {
            if ($row->type === 'SUMMARY') {
                return [
                    $row->category,
                    $row->revenue !== null ? number_format($row->revenue, 2) : '',
                    '',
                    '',
                    ''
                ];
            } elseif ($row->type === 'SPACER') {
                return ['', '', '', '', ''];
            } elseif ($row->type === 'HEADER') {
                return [$row->category, '', '', '', ''];
            }
        }
        
        // Category breakdown
        $margin = $row->revenue > 0 ? ($row->profit / $row->revenue) * 100 : 0;
        
        return [
            $row->category ?? 'Uncategorized',
            number_format($row->revenue, 2),
            number_format($row->cost, 2),
            number_format($row->profit, 2),
            number_format($margin, 2)
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
                    'color' => ['argb' => 'FF2563EB']
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
        return 'Profit & Loss Report';
    }
}
