<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicantsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $applicants;

    public function __construct($applicants)
    {
        $this->applicants = $applicants;
    }

    /**
     * Return the collection of data to export
     */
    public function collection()
    {
        return $this->applicants->map(function ($applicant) {
            return [
                'full_name' => $applicant->full_name,
                'address' => $applicant->address,
                'contact_no' => $applicant->contact_no,
            ];
        });
    }

    /**
     * Set the headings for the Excel file
     */
    public function headings(): array
    {
        return ['Full Name', 'Address', 'Contact No.'];
    }

    /**
     * Style the header row
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d6efd']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
