<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Application;
use App\Models\StatusHistory;
use App\Models\Applicant;
use App\Models\LandRecord;
use App\Models\User;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // We use "with()" to grab the Applicant and Land Record at the same time!
        // "latest()" puts the newest applications at the top of the list.
        $applications = Application::with(['applicant', 'landRecord'])->latest()->get();

        return view('applications.index', compact('applications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('applications.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tracking_no' => 'required|string|unique:applications,tracking_no',
            'applicant_id' => 'required|exists:applicants,id',
            'land_record_id' => 'required|exists:land_records,id',
            'date_received' => 'required|date',
        ]);

        // 1. Create the Main Application
        $application = Application::create($validated);

        // 2. Auto-generate the very first Audit Trail entry
        StatusHistory::create([
            'application_id' => $application->id,
            'status' => 'Pending',
            'remarks' => 'Application officially received and logged into system.',
            'updated_by' => 'System Auto-Log'
        ]);

        return back()->with('success', 'Backbone Test: Application created and Initial Status Logged!');
    }

    /**
     * Master Intake Form - Create Applicant, Land Record, and Application in one transaction
     */
    public function masterStore(Request $request)
    {
        // 1. Validate EVERYTHING from the single form
        $validated = $request->validate([
            // Applicant Data
            'full_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
            
            // Land Data
            'survey_no' => ['required', 'string', 'unique:land_records,survey_no', 'regex:/^[A-Za-z]{3}-\d{2}-\d{6}$/'],
            'total_area' => 'required|numeric|min:1',
            'location' => 'required|string',
            
            // Application Data
            'tracking_no' => 'required|string|unique:applications,tracking_no',
            'date_received' => 'required|date',
        ]);

        // 2. Use a Database Transaction (Save all or save nothing!)
        DB::transaction(function () use ($validated) {
            
            // A. Save the Applicant
            $applicant = Applicant::create([
                'full_name' => $validated['full_name'],
                'address' => $validated['address'],
                'contact_no' => $validated['contact_no'],
            ]);

            // B. Save the Land Record
            $land = LandRecord::create([
                'survey_no' => $validated['survey_no'],
                'total_area' => $validated['total_area'],
                'location' => $validated['location'],
            ]);

            // C. Build the Bridge (Application)
            $application = Application::create([
                'tracking_no' => $validated['tracking_no'],
                'applicant_id' => $applicant->id, // Instantly grabs the ID from step A
                'land_record_id' => $land->id,    // Instantly grabs the ID from step B
                'date_received' => $validated['date_received'],
            ]);

            // D. Auto-Log the Initial Status
            StatusHistory::create([
                'application_id' => $application->id,
                'status' => 'Pending',
                'remarks' => 'Application officially received via Master Intake Form.',
                'updated_by' => 'Records Officer'
            ]);
        });

        return back()->with('success', 'Master Intake Complete: Applicant, Land, and Application successfully linked and saved!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $application = Application::with(['applicant', 'landRecord', 'statusHistories'])->findOrFail($id);
        return view('applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Application $application)
    {
        // 1. VALIDATE
        $validated = $request->validate([
            'status' => 'required|in:Pending,In Process,Approved,Rejected',
            'remarks' => 'nullable|string',
            'patent_details' => 'nullable|string', // Nullable here, but database handles default
            'patent_type' => 'nullable|string',
        ]);

        // 2. HANDLE MENTOR'S PATENT RULE
        $updateData = [];
        if ($validated['status'] === 'Approved') {
            // If they typed something, use it. Otherwise, let it be null so DB defaults to 'Patent'
            $updateData['patent_details'] = $request->filled('patent_details') ? $validated['patent_details'] : null;
            $updateData['patent_type'] = $validated['patent_type'] ?? null;
        }

        // Update the application record
        $application->update($updateData);

        // 3. HANDLE THE AUDIT TRAIL (STATUS HISTORY)
        // Check if the status actually changed from what was previously recorded
        $latestStatus = $application->statusHistories()->latest()->first();

        if (!$latestStatus || $latestStatus->status !== $validated['status']) {
            StatusHistory::create([
                'application_id' => $application->id,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
                'updated_by' => Auth::user()?->name ?? 'System User',
            ]);
        }

        return redirect()->route('applications.show', $application->id)->with('success', 'Application updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Update status and create audit trail
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,In Process,Approved,Rejected',
            'remarks' => 'nullable|string'
        ]);

        // Save the new status to the Audit Trail!
        StatusHistory::create([
            'application_id' => $id,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? 'Status updated to ' . $validated['status'],
            'updated_by' => 'LMO Officer'
        ]);

        return back()->with('success', 'Application status successfully updated to ' . $validated['status'] . '!');
    }

    /**
     * Export applications in multiple formats (CSV, Excel, PDF)
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        
        $applications = Application::with(['applicant', 'landRecord', 'statusHistories'])
                                   ->latest()
                                   ->get();

        switch ($format) {
            case 'excel':
                return $this->exportExcel($applications);
            case 'pdf':
                return $this->exportPdf($applications);
            default:
                return $this->exportCsv($applications);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportCsv($applications)
    {
        $fileName = 'applications_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Tracking No.', 'Applicant Name', 'Survey No.', 'Total Area', 'Date Received', 'Status']);

            // Data rows
            foreach ($applications as $app) {
                $latestStatus = $app->statusHistories()->latest()->first();
                $statusText = $latestStatus ? $latestStatus->status : 'Pending';
                
                fputcsv($file, [
                    $app->tracking_no,
                    $app->applicant->full_name,
                    $app->landRecord->survey_no,
                    $app->landRecord->total_area,
                    $app->date_received->format('Y-m-d'),
                    $statusText
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format
     */
    private function exportExcel($applications)
    {
        $fileName = 'applications_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        // Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headings
        $headings = ['Tracking No.', 'Applicant Name', 'Survey No.', 'Total Area (sqm)', 'Date Received', 'Status'];
        foreach ($headings as $col => $heading) {
            $sheet->setCellValue([($col + 1), 1], $heading);
        }

       // Style header row
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
];

for ($col = 1; $col <= count($headings); $col++) {
    // FIX: Just pass the integers directly as an array: [$col, 1]
    $sheet->getStyle([$col, 1])->applyFromArray($headerStyle);
}

        // Add data rows
        $row = 2;
        foreach ($applications as $app) {
            $latestStatus = $app->statusHistories()->latest()->first();
            $statusText = $latestStatus ? $latestStatus->status : 'Pending';

            $sheet->setCellValue([1, $row], $app->tracking_no);
            $sheet->setCellValue([2, $row], $app->applicant->full_name);
            $sheet->setCellValue([3, $row], $app->landRecord->survey_no);
            $sheet->setCellValue([4, $row], $app->landRecord->total_area);
            $sheet->setCellValue([5, $row], $app->date_received->format('Y-m-d'));
            $sheet->setCellValue([6, $row], $statusText);
            $row++;
        }

        // Auto-fit columns
        foreach (range(1, 6) as $col) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // Write the file to output
        $writer = new Xlsx($spreadsheet);
        $temp = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp);

        return response()->download($temp, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Export to PDF format
     */
    private function exportPdf($applications)
    {
        $fileName = 'applications_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $html = view('exports.applications-pdf', compact('applications'))->render();
        
        $pdf = Pdf::loadHTML($html);
        
        return $pdf->download($fileName);
    }
}