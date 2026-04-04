<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicationsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $applications;

    public function __construct($applications)
    {
        $this->applications = $applications;
    }

    /**
     * Return the collection of data to export
     */
    public function collection()
    {
        return $this->applications->map(function ($app) {
            $latestStatus = $app->statusHistories()->latest()->first();
            $statusText = $latestStatus ? $latestStatus->status : 'Pending';

            return [
                'tracking_no' => $app->tracking_no,
                'applicant_name' => $app->applicant->full_name,
                'survey_no' => $app->landRecord->survey_no,
                'total_area' => $app->landRecord->total_area,
                'date_received' => $app->date_received->format('Y-m-d'),
                'status' => $statusText,
            ];
        });
    }

    /**
     * Set the headings for the Excel file
     */
    public function headings(): array
    {
        return ['Tracking No.', 'Applicant Name', 'Survey No.', 'Total Area (sqm)', 'Date Received', 'Status'];
    }

    /**
     * Style the header row
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
