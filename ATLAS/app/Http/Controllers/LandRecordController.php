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
    // Accepts 3 Letters, Dash, 2+ Numbers, Dash, 4+ Numbers (e.g., CSD-03-012345 or PSU-123-0001)
    'survey_no' => [
        'required',
        'string',
        'unique:land_records,survey_no',
        'regex:/^[A-Za-z]{3}-\d{2,}-\d{4,}$/' 
    ],
    'total_area' => 'required|numeric|min:1',
    'location' => 'required|string',
]);

        // By default, 'is_subdivided' is false in our database migration
        $landRecord = LandRecord::create($validated);

        // Return JSON response for AJAX requests, redirect for normal requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Land Record saved successfully!',
                'landRecord' => $landRecord
            ], 201);
        }

        return back()->with('success', 'Land Record saved successfully!');
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
     * Bulk delete land records
     */
    public function bulkDelete(Request $request)
    {
        // Validate that IDs array is provided
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:land_records,id'
        ]);

        try {
            $ids = $request->input('ids');
            $count = count($ids);

            // Log bulk deletion attempt
            \Log::info('Bulk deleting land records', [
                'count' => $count,
                'ids' => $ids,
                'deleted_by' => auth()->user()->name,
                'deleted_by_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Delete land records
            $deleted = LandRecord::whereIn('id', $ids)->delete();

            \Log::info('Land records successfully bulk deleted', [
                'count' => $deleted,
                'ids' => $ids
            ]);

            return response()->json([
                'success' => true,
                'message' => "$deleted land record(s) deleted successfully",
                'count' => $deleted
            ]);

        } catch (\Exception $e) {
            \Log::error('Error bulk deleting land records', [
                'ids' => $request->input('ids'),
                'error' => $e->getMessage(),
                'deleted_by' => auth()->user()->name
            ]);

            $message = 'An error occurred while deleting land records: ' . $e->getMessage();

            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
        }
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
