<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class LandRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $landRecords = LandRecord::latest()->get();
        return view('land-records.index', compact('landRecords'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('land-records.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
    // Forces exactly: 3 Letters, Dash, 2 Numbers, Dash, 6 Numbers (e.g., CSD-03-012345)
    'survey_no' => [
        'required',
        'string',
        'unique:land_records,survey_no',
        'regex:/^[A-Za-z]{3}-\d{2}-\d{6}$/' 
    ],
    'total_area' => 'required|numeric|min:1',
    'location' => 'required|string',
]);

        // By default, 'is_subdivided' is false in our database migration
        LandRecord::create($validated);

        return back()->with('success', 'Backbone Test: Land Record saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $landRecord = LandRecord::findOrFail($id);
        return view('land-records.show', compact('landRecord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $landRecord = LandRecord::findOrFail($id);
        return view('land-records.edit', compact('landRecord'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $landRecord = LandRecord::findOrFail($id);
        
        $validated = $request->validate([
            'survey_no' => [
                'required',
                'string',
                'unique:land_records,survey_no,' . $id,
                'regex:/^[A-Za-z]{3}-\d{2}-\d{6}$/'
            ],
            'total_area' => 'required|numeric|min:1',
            'location' => 'required|string',
        ]);

        $landRecord->update($validated);

        return redirect()->route('land-records.show', $landRecord->id)->with('success', 'Land record updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Export land records in multiple formats (CSV, Excel, PDF)
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        
        $landRecords = LandRecord::latest()->get();

        switch ($format) {
            case 'excel':
                return $this->exportExcel($landRecords);
            case 'pdf':
                return $this->exportPdf($landRecords);
            default:
                return $this->exportCsv($landRecords);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportCsv($landRecords)
    {
        $fileName = 'land-records_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($landRecords) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Survey No.', 'Total Area (sqm)', 'Location', 'Is Subdivided']);

            // Data rows
            foreach ($landRecords as $record) {
                fputcsv($file, [
                    $record->survey_no,
                    $record->total_area,
                    $record->location,
                    $record->is_subdivided ? 'Yes' : 'No'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format
     */
    private function exportExcel($landRecords)
    {
        $fileName = 'land-records_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LandRecordsExport($landRecords), $fileName);
    }

    /**
     * Export to PDF format
     */
    private function exportPdf($landRecords)
    {
        $fileName = 'land-records_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $html = view('exports.land-records-pdf', compact('landRecords'))->render();
        
        $pdf = Pdf::loadHTML($html);
        
        return $pdf->download($fileName);
    }
}
