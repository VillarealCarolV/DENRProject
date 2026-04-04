<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LandRecordsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $landRecords;

    public function __construct($landRecords)
    {
        $this->landRecords = $landRecords;
    }

    /**
     * Return the collection of data to export
     */
    public function collection()
    {
        return $this->landRecords->map(function ($record) {
            return [
                'survey_no' => $record->survey_no,
                'total_area' => $record->total_area,
                'location' => $record->location,
                'is_subdivided' => $record->is_subdivided ? 'Yes' : 'No',
            ];
        });
    }

    /**
     * Set the headings for the Excel file
     */
    public function headings(): array
    {
        return ['Survey No.', 'Total Area (sqm)', 'Location', 'Is Subdivided'];
    }

    /**
     * Style the header row
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
