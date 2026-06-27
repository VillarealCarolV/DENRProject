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
use App\Models\Subdivision;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Illuminate\Support\Facades\Notification;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     * Records Officers see the Intake Workstation, others see the standard table.
     */
    public function index()
    {
        // Records Officers get the Intake Workstation
        if (Auth::user()->role === 'records_officer') {
            return $this->workstation();
        }

        // Others get the standard applications table
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
     * Show the Master Intake form for creating applicant, land record, and application
     */
    public function masterCreate()
    {
        return view('applications.master-create');
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
        // AUTHORIZATION: Only Records Officers can create applications
        if (Auth::user()->role !== 'records_officer') {
            abort(403, 'Unauthorized: Only Records Officers can create applications.');
        }

        // 1. Validate EVERYTHING from the single form
        $validated = $request->validate([
            // Applicant Data
            'full_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
            'existing_applicant_id' => 'nullable|exists:applicants,id',
            
            // Land Data
            'survey_no' => ['required', 'string', 'unique:land_records,survey_no', 'regex:/^[A-Za-z]{3}-\d{2,}-\d{4,}$/'],
            'total_area' => 'required|numeric|min:1',
            'location' => 'required|string',
            
            // Application Data
            'tracking_no' => 'required|string|unique:applications,tracking_no',
            'date_received' => 'required|date',
        ]);

        // 2. Use a Database Transaction (Save all or save nothing!)
        $application = null;
        DB::transaction(function () use ($validated, &$application) {
            
            // A. Get or create the Applicant
            if (!empty($validated['existing_applicant_id'])) {
                // Link to existing applicant
                $applicant = Applicant::findOrFail($validated['existing_applicant_id']);
            } else {
                // Create new applicant
                $applicant = Applicant::create([
                    'full_name' => $validated['full_name'],
                    'address' => $validated['address'],
                    'contact_no' => $validated['contact_no'],
                ]);
            }

            // B. Determine lot letter only if linking to existing applicant
            $surveyNo = $validated['survey_no'];
            if (!empty($validated['existing_applicant_id'])) {
                // Add letter when linking to existing applicant (existing account scenario)
                $applicantLandCount = $applicant->applications()->count();
                // Convert count to letter: 0->A, 1->B, 2->C, etc.
                $lotLetter = chr(65 + $applicantLandCount);
                $surveyNo = $validated['survey_no'] . '(' . $lotLetter . ')';
            }
            // For new applicants, survey_no remains clean without any letter
            
            // Save the Land Record
            $land = LandRecord::create([
                'survey_no' => $surveyNo,
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

        // 3. Send notifications to all Land Management Officers
        if ($application) {
            $landOfficers = User::whereIn('role', ['processing', 'land_officer'])->get();
            if ($landOfficers->isNotEmpty()) {
                Notification::send($landOfficers, new NewApplicationNotification($application));
            }
        }

        // Return JSON response for AJAX requests, redirect for normal requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Applicant, Land, and Application successfully linked and saved!',
                'application' => $application
            ], 201);
        }

        return back()->with('success', 'Applicant, Land, and Application successfully linked and saved!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $application = Application::with(['applicant', 'landRecord.children', 'statusHistories'])->findOrFail($id);
        return view('applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified resource.
     * Only Land Officers can edit/assess applications.
     */
    public function edit(string $id)
    {
        // AUTHORIZATION: Only Land Officers can edit applications
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Management Officers can assess applications.');
        }

        $application = Application::with(['applicant', 'landRecord.children', 'statusHistories'])->findOrFail($id);
        return view('applications.edit', compact('application'));
    }

    /**
     * Update the specified resource in storage.
     * Land Officers assess the application and make final decisions.
     */
    public function update(Request $request, Application $application)
    {
        // AUTHORIZATION: Only Land Officers can update applications
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Management Officers can update applications.');
        }

        // Log the start of the assessment
        \Log::info('🔧 LAND OFFICER ASSESSMENT STARTED', [
            'application_id' => $application->id,
            'tracking_no' => $application->tracking_no,
            'land_officer_id' => Auth::id(),
            'land_officer_name' => Auth::user()->name,
            'timestamp' => now()
        ]);

        // 1. VALIDATE
        $validated = $request->validate([
            'lot_type' => 'required|in:existing_lot,subdivision',
            'new_lot_number' => 'nullable|required_if:lot_type,subdivision|string|max:255',
            'subdivided_area' => 'nullable|required_if:lot_type,subdivision|numeric|min:0.01',
            'status' => 'required|in:In Process,Approved,Rejected',
            'land_officer_remarks' => 'required|string',
            'patent_details' => 'nullable|string',
            'patent_type' => 'nullable|string',
        ]);

        \Log::info('✓ VALIDATION PASSED', [
            'lot_type' => $validated['lot_type'],
            'status' => $validated['status'],
            'remarks_length' => strlen($validated['land_officer_remarks'])
        ]);

        // 2. HANDLE SUBDIVISION LOGIC
        $updateData = [
            'lot_type' => $validated['lot_type'],
            'land_officer_remarks' => $validated['land_officer_remarks'],
            'land_officer_id' => Auth::id(),
            'assessed_at' => now(),
            'patent_details' => $validated['patent_details'] ?? null,
            'patent_type' => $validated['patent_type'] ?? null,
        ];

        // If it's a subdivision, calculate remaining area
        if ($validated['lot_type'] === 'subdivision') {
            $updateData['new_lot_number'] = $validated['new_lot_number'];
            $updateData['subdivided_area'] = $validated['subdivided_area'];
            // Calculate remaining area of mother lot
            $updateData['remaining_area'] = $application->landRecord->total_area - $validated['subdivided_area'];

            \Log::info('🔨 SUBDIVISION DETAILS PROCESSED', [
                'new_lot_number' => $validated['new_lot_number'],
                'subdivided_area' => $validated['subdivided_area'],
                'total_area' => $application->landRecord->total_area,
                'remaining_area' => $updateData['remaining_area']
            ]);
        } else {
            // For existing lots, no subdivision fields needed
            $updateData['new_lot_number'] = null;
            $updateData['subdivided_area'] = null;
            $updateData['remaining_area'] = null;

            \Log::info('✓ EXISTING LOT - No subdivision fields required');
        }

        // 3. UPDATE THE APPLICATION
        $application->update($updateData);
        \Log::info('✓ APPLICATION DATA UPDATED', [
            'lot_type' => $updateData['lot_type'],
            'assessed_by' => Auth::user()->name
        ]);

        // 4. CREATE AUDIT TRAIL (STATUS HISTORY)
        $statusHistory = StatusHistory::create([
            'application_id' => $application->id,
            'status' => $validated['status'],
            'remarks' => $validated['land_officer_remarks'],
            'updated_by' => Auth::user()->name ?? 'System User',
        ]);

        \Log::info('✓ AUDIT TRAIL CREATED', [
            'status_history_id' => $statusHistory->id,
            'application_id' => $application->id,
            'new_status' => $validated['status']
        ]);

        // 5. MARK RELATED NOTIFICATIONS AS READ & UPDATE NOTIFICATION DATA
        // When status is updated, mark any pending notifications for this application as read
        // AND update the notification data with the new status for real-time sync
        $relatedNotifications = Auth::user()->notifications()
            ->where('data->application_id', '=', $application->id)
            ->whereNull('read_at')
            ->get();
        
        foreach ($relatedNotifications as $notification) {
            // Update notification data with new status
            $data = $notification->data;
            $data['status'] = $validated['status'];
            $notification->data = $data;
            $notification->read_at = now();
            $notification->save();
        }

        \Log::info('✓ NOTIFICATIONS MARKED AS READ & SYNCED', [
            'application_id' => $application->id,
            'marked_by' => Auth::user()->name,
            'count' => count($relatedNotifications),
            'new_status' => $validated['status']
        ]);

        \Log::info('✅ LAND OFFICER ASSESSMENT COMPLETED SUCCESSFULLY', [
            'application_id' => $application->id,
            'tracking_no' => $application->tracking_no,
            'final_status' => $validated['status'],
            'timestamp' => now()
        ]);

        // Check if this is an AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Application assessment completed successfully!',
                'application_id' => $application->id,
                'new_status' => $validated['status']
            ]);
        }

        return redirect()->route('applications.show', $application->id)->with('success', 'Application assessment completed successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // AUTHORIZATION: Only admin, processing, and land_officer roles can delete applications
        $allowedRoles = ['admin', 'processing', 'land_officer'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            $message = 'Unauthorized: You do not have permission to delete applications.';
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $message], 403);
            }
            abort(403, $message);
        }

        try {
            $application = Application::findOrFail($id);

            // Log deletion attempt
            \Log::info('Deleting application', [
                'application_id' => $id,
                'tracking_no' => $application->tracking_no,
                'deleted_by' => Auth::user()->name,
                'deleted_by_id' => Auth::id(),
                'timestamp' => now()
            ]);

            // Delete the application (this will cascade delete status histories due to foreign key constraint)
            $application->delete();

            \Log::info('Application successfully deleted', [
                'application_id' => $id,
                'tracking_no' => $application->tracking_no
            ]);

            // Return JSON response for AJAX requests
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Application deleted successfully',
                    'application_id' => $id
                ]);
            }

            // Redirect for normal requests
            return redirect()->route('processing-queue')->with('success', 'Application deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Error deleting application', [
                'application_id' => $id,
                'error' => $e->getMessage(),
                'deleted_by' => Auth::user()->name
            ]);

            $message = 'An error occurred while deleting the application: ' . $e->getMessage();

            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Update status and create audit trail
     */
    public function updateStatus(Request $request, $id)
    {
        // AUTHORIZATION: Only 'processing' role users can update application status
        if (Auth::user()->role !== 'processing') {
            abort(403, 'Unauthorized: Only Land Management Officers can update application status.');
        }

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
     * Quick approve an existing lot from notification modal
     */
    public function quickApprove(Request $request, Application $application)
    {
        // AUTHORIZATION: Only Land Officers can approve applications
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Officers can approve applications.');
        }

        // VALIDATION: Only existing lots can be quick approved
        $validated = $request->validate([
            'lot_type' => 'required|in:existing_lot',
            'status' => 'required|in:Approved,Rejected',
            'land_officer_remarks' => 'required|string'
        ]);

        \Log::info('🚀 QUICK APPROVE INITIATED', [
            'application_id' => $application->id,
            'tracking_no' => $application->tracking_no,
            'lot_type' => $validated['lot_type'],
            'status' => $validated['status'],
            'land_officer' => Auth::user()->name
        ]);

        // Update application
        $application->update([
            'lot_type' => $validated['lot_type'],
            'land_officer_remarks' => $validated['land_officer_remarks'],
            'land_officer_id' => Auth::id(),
            'assessed_at' => now()
        ]);

        // Create audit trail
        StatusHistory::create([
            'application_id' => $application->id,
            'status' => $validated['status'],
            'remarks' => $validated['land_officer_remarks'],
            'updated_by' => Auth::user()->name ?? 'System User'
        ]);

        \Log::info('✅ QUICK APPROVE COMPLETED', [
            'application_id' => $application->id,
            'final_status' => $validated['status'],
            'timestamp' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application ' . strtolower($validated['status']) . ' successfully!',
            'application_id' => $application->id,
            'status' => $validated['status']
        ]);
    }

    /**
     * Get application details as JSON (for notifications modal)
     */
    public function getDetails(Application $application)
    {
        $application->load(['applicant', 'landRecord', 'statusHistories']);
        
        // Get latest status from status_histories
        $latestStatus = $application->statusHistories()->latest()->first();
        $status = $latestStatus?->status ?? 'Pending';

        return response()->json([
            'tracking_no' => $application->tracking_no,
            'applicant_name' => $application->applicant?->full_name ?? 'N/A',
            'location' => $application->landRecord?->location ?? 'N/A',
            'survey_no' => $application->landRecord?->survey_no ?? 'N/A',
            'status' => $status,
            'remarks' => $application->land_officer_remarks ?? '',
            'application_id' => $application->id,
            'url' => route('applications.show', $application->id)
        ]);
    }

    /**
     * Get application details by tracking number as JSON (for notifications modal)
     */
    public function getDetailsByTracking($trackingNo)
    {
        $application = Application::where('tracking_no', $trackingNo)
                                  ->with(['applicant', 'landRecord', 'statusHistories'])
                                  ->firstOrFail();
        
        return $this->getDetailsResponse($application);
    }

    /**
     * Helper method to return application details as JSON
     */
    private function getDetailsResponse(Application $application)
    {
        // Get latest status from status_histories
        $latestStatus = $application->statusHistories()->latest()->first();
        $status = $latestStatus?->status ?? 'Pending';

        return response()->json([
            'tracking_no' => $application->tracking_no,
            'applicant_name' => $application->applicant?->full_name ?? 'N/A',
            'location' => $application->landRecord?->location ?? 'N/A',
            'survey_no' => $application->landRecord?->survey_no ?? 'N/A',
            'status' => $status,
            'remarks' => $application->land_officer_remarks ?? '',
            'application_id' => $application->id,
            'lot_type' => $application->lot_type ?? null,
            'url' => route('applications.show', $application->id)
        ]);
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

    /**
     * Get the next tracking number
     */
    public function getNextTrackingNumber()
    {
        // Get the last tracking number from database
        $lastApp = Application::orderBy('created_at', 'desc')->first();
        
        if (!$lastApp) {
            $nextNumber = 'CENRO-2026-001';
        } else {
            // Extract the numeric part and increment
            preg_match('/(\d+)$/', $lastApp->tracking_no, $matches);
            if ($matches) {
                $lastNum = intval($matches[1]);
                $nextNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
                $nextNumber = 'CENRO-' . date('Y') . '-' . $nextNum;
            } else {
                $nextNumber = 'CENRO-' . date('Y') . '-001';
            }
        }

        return response()->json(['tracking_no' => $nextNumber]);
    }

    /**
     * Show the Intake Workstation view
     */
    public function workstation()
    {
        // Get all applications with related data
        $applications = Application::with(['applicant', 'landRecord', 'statusHistories'])
                                   ->latest()
                                   ->get();

        return view('applications.workstation', compact('applications'));
    }

    /**
     * Get applications table data as HTML (for AJAX refresh)
     */
    public function getTableData()
    {
        // Get all applications with related data
        $applications = Application::with(['applicant', 'landRecord', 'statusHistories'])
                                   ->latest()
                                   ->get();

        // If no applications, return empty state
        if ($applications->isEmpty()) {
            $html = '
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                        <p class="mb-0">No applications yet. Use the form above to create one.</p>
                    </td>
                </tr>
            ';
            return response()->json(['html' => $html]);
        }

        // Build the table rows HTML
        $html = '';
        foreach ($applications as $app) {
            $latestStatus = $app->statusHistories()->latest()->first();
            $statusText = $latestStatus ? $latestStatus->status : 'Pending';
            $statusClass = match($statusText) {
                'Approved' => 'status-approved',
                'Rejected' => 'status-rejected',
                'In Process' => 'status-in-process',
                default => 'status-pending',
            };

            $html .= '
                <tr class="application-row">
                    <td class="fw-bold">' . htmlspecialchars($app->tracking_no) . '</td>
                    <td>' . htmlspecialchars($app->applicant->full_name) . '</td>
                    <td>' . htmlspecialchars($app->landRecord->survey_no) . '</td>
                    <td>' . number_format($app->landRecord->total_area, 2) . ' sqm</td>
                    <td>' . \Carbon\Carbon::parse($app->date_received)->format('M d, Y') . '</td>
                    <td class="text-center">
                        <span class="status-badge ' . $statusClass . '">' . htmlspecialchars($statusText) . '</span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="' . route('applications.show', $app->id) . '" class="action-link" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="' . route('applications.edit', $app->id) . '" class="action-link" title="Edit">
                                <i class="fas fa-pen-to-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            ';
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Display the Processing Queue page - shows pending and in-process applications
     * Used as a workstation for reviewing and processing records (Land Officers Only)
     */
    public function processingQueue(Request $request)
    {
        // AUTHORIZATION: Only Land Officers can access the Processing Queue
        $userRole = strtolower(trim(Auth::user()->role ?? ''));
        if ($userRole !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Officers can access the Processing Queue. Your role: ' . $userRole);
        }

        $query = Application::with(['applicant', 'landRecord', 'statusHistories']);

        // Filter by status if provided
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            // Get latest status for each application
            $query->whereHas('statusHistories', function ($q) use ($status) {
                $q->where('status', $status)
                  ->where('id', function ($subquery) {
                      $subquery->selectRaw('MAX(id)')
                              ->from('status_histories')
                              ->whereColumn('status_histories.application_id', 'applications.id');
                  });
            });
        } else {
            // Default: show pending and in-process applications
            $query->whereHas('statusHistories', function ($q) {
                $q->where('status', '!=', 'Approved')
                  ->where('status', '!=', 'Rejected')
                  ->where('id', function ($subquery) {
                      $subquery->selectRaw('MAX(id)')
                              ->from('status_histories')
                              ->whereColumn('status_histories.application_id', 'applications.id');
                  });
            });
        }

        // Sort by oldest first (date_received)
        if ($request->has('sort') && $request->sort === 'newest') {
            $query->latest('date_received');
        } else {
            $query->oldest('date_received');
        }

        $applications = $query->paginate(25);

        return view('applications.processing-queue', compact('applications'));
    }

    /**
     * Export processing queue applications (filtered data)
     */
    public function exportProcessingQueue(Request $request)
    {
        // AUTHORIZATION: Only Land Officers can access the Processing Queue
        $userRole = strtolower(trim(Auth::user()->role ?? ''));
        if ($userRole !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Officers can access the Processing Queue.');
        }

        $format = $request->query('format', 'csv');

        $query = Application::with(['applicant', 'landRecord', 'statusHistories']);

        // Apply same filters as processingQueue method
        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            $query->whereHas('statusHistories', function ($q) use ($status) {
                $q->where('status', $status)
                  ->where('id', function ($subquery) {
                      $subquery->selectRaw('MAX(id)')
                              ->from('status_histories')
                              ->whereColumn('status_histories.application_id', 'applications.id');
                  });
            });
        } else {
            // Default: show pending and in-process applications
            $query->whereHas('statusHistories', function ($q) {
                $q->where('status', '!=', 'Approved')
                  ->where('status', '!=', 'Rejected')
                  ->where('id', function ($subquery) {
                      $subquery->selectRaw('MAX(id)')
                              ->from('status_histories')
                              ->whereColumn('status_histories.application_id', 'applications.id');
                  });
            });
        }

        // Sort by oldest first (date_received)
        if ($request->has('sort') && $request->sort === 'newest') {
            $query->latest('date_received');
        } else {
            $query->oldest('date_received');
        }

        $applications = $query->get(); // Get all matching records without pagination for export

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
     * Get count of pending applications for the processing queue
     * Used in navbar to show pending task count (Land Officers Only)
     */
    public function getPendingCount()
    {
        // Only Land Officers can see pending count
        if (Auth::user()->role !== 'land_officer') {
            return response()->json(['count' => 0]);
        }

        $count = Application::whereHas('statusHistories', function ($q) {
            $q->where('status', '!=', 'Approved')
              ->where('status', '!=', 'Rejected')
              ->where('id', function ($subquery) {
                  $subquery->selectRaw('MAX(id)')
                          ->from('status_histories')
                          ->whereColumn('status_histories.application_id', 'applications.id');
              });
        })->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Update application status via Processing Modal (AJAX)
     * Land Officers use this endpoint to quickly assess applications
     */
    public function updateStatusFromModal(Request $request)
    {
        // AUTHORIZATION: Only Land Officers can use this endpoint
        if (Auth::user()->role !== 'land_officer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        // Validate request
        $validated = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'status' => 'required|in:Pending,In Process,Approved,Rejected',
            'land_officer_remarks' => 'required|string|min:10'
        ]);

        try {
            $application = Application::findOrFail($validated['application_id']);

            // Create status history record (audit trail)
            StatusHistory::create([
                'application_id' => $application->id,
                'status' => $validated['status'],
                'remarks' => $validated['land_officer_remarks'],
                'updated_by' => Auth::user()->name
            ]);

            // Update application's remarks field (for quick reference)
            $application->update([
                'land_officer_remarks' => $validated['land_officer_remarks'],
                'land_officer_id' => Auth::id(),
                'assessed_at' => now()
            ]);

            \Log::info('✓ Application status updated via Processing Modal', [
                'application_id' => $application->id,
                'tracking_no' => $application->tracking_no,
                'new_status' => $validated['status'],
                'land_officer' => Auth::user()->name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully!',
                'application_id' => $application->id,
                'new_status' => $validated['status']
            ]);

        } catch (\Exception $e) {
            \Log::error('✗ Error updating status via Processing Modal', [
                'application_id' => $validated['application_id'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment. Please try again.'
            ], 500);
        }
    }

    /**
     * Process Application - Handle lot classification and patent details from Processing Modal
     * Comprehensive endpoint for Land Officers to process applications with full details
     */
    public function processApplication(Request $request, $id)
    {
        // AUTHORIZATION: Only Land Officers can process applications
        if (Auth::user()->role !== 'land_officer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        try {
            \Log::info('=== PROCESSING APPLICATION STARTED ===', [
                'application_id' => $id,
                'raw_request_data' => $request->all(),
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]);

            // Extract input values
            $lotClassification = $request->input('lot_classification');
            $subdivisionLots = $request->input('subdivision_lots', []);
            
            \Log::info('Step 1: Input values extracted', [
                'lot_classification_received' => $lotClassification,
                'subdivision_lots_count' => count($subdivisionLots),
                'subdivision_lots_data' => json_encode($subdivisionLots)
            ]);

            // Build validation rules - lot_classification can be null, but must be in list if provided
            $rules = [
                'application_id' => 'required|exists:applications,id',
                'lot_classification' => 'nullable|in:existing,subdivision',
                'number_of_lots' => 'nullable|integer|min:2|max:10',
                'status' => 'required|in:Pending,In Process,Approved,Rejected',
                'patent_type' => 'nullable|string|max:255',
                'patent_details' => 'nullable|string|max:1000',
                'remarks' => 'required|string|min:5'
            ];

            // If subdivision is selected, subdivision_lots is REQUIRED and must be a valid array
            if ($lotClassification === 'subdivision') {
                $rules['subdivision_lots'] = 'required|array|min:1';
                $rules['subdivision_lots.*.designation'] = 'required|string|max:1';
                $rules['subdivision_lots.*.area'] = 'required|numeric|min:0.01';
                
                \Log::info('Step 2a: Validation rules set to REQUIRE subdivision_lots', [
                    'lot_classification' => 'subdivision'
                ]);
            } else {
                $rules['subdivision_lots'] = 'nullable|array';
                $rules['subdivision_lots.*.designation'] = 'nullable|string|max:1';
                $rules['subdivision_lots.*.area'] = 'nullable|numeric|min:0.01';
                
                \Log::info('Step 2b: Validation rules set to NULLABLE subdivision_lots', [
                    'lot_classification' => $lotClassification
                ]);
            }

            // VALIDATE
            $validated = $request->validate($rules);

            \Log::info('Step 3: Validation PASSED', [
                'validated_lot_classification' => $validated['lot_classification'] ?? 'null',
                'validated_subdivision_lots_count' => count($validated['subdivision_lots'] ?? []),
                'validated_subdivision_lots' => json_encode($validated['subdivision_lots'] ?? [])
            ]);

            // Find the application
            $application = Application::with('landRecord')->findOrFail($validated['application_id']);

            \Log::info('Step 4: Application found', [
                'application_id' => $application->id,
                'tracking_no' => $application->tracking_no,
                'land_record_id' => $application->land_record_id,
                'parent_survey_no' => $application->landRecord->survey_no ?? 'NULL'
            ]);

            // Prepare update data for application
            $updateData = [
                'land_officer_remarks' => $validated['remarks'],
                'land_officer_id' => Auth::id(),
                'assessed_at' => now(),
            ];

            if (!empty($validated['patent_type'])) {
                $updateData['patent_type'] = $validated['patent_type'];
            }

            if (!empty($validated['patent_details'])) {
                $updateData['patent_details'] = $validated['patent_details'];
            }

            // CRITICAL: Handle subdivision processing BEFORE updating application
            if ($validated['lot_classification'] === 'subdivision') {
                \Log::info('Step 5: SUBDIVISION PROCESSING STARTED', [
                    'parent_lot_id' => $application->land_record_id,
                    'parent_survey_no' => $application->landRecord->survey_no,
                    'subdivision_lots_array_count' => count($validated['subdivision_lots'] ?? [])
                ]);

                // Get the subdivision lots array
                $subdivisionLotsArray = $validated['subdivision_lots'] ?? [];

                // LOG THE EXACT ARRAY STRUCTURE
                \Log::info('Step 5-Debug: Full subdivision lots array received', [
                    'array_dump' => json_encode($subdivisionLotsArray),
                    'array_keys' => array_keys($subdivisionLotsArray),
                    'item_count' => count($subdivisionLotsArray)
                ]);

                if (!empty($subdivisionLotsArray)) {
                    // Delete existing subdivisions for this parent lot
                    $deletedCount = Subdivision::where('parent_lot_id', $application->land_record_id)->delete();
                    
                    \Log::info('Step 5a: Deleted existing subdivisions', [
                        'parent_lot_id' => $application->land_record_id,
                        'records_deleted' => $deletedCount
                    ]);

                    // Use database transaction to ensure atomicity
                    DB::beginTransaction();
                    
                    try {
                        // Loop through each child lot and create records
                        $lotsCreatedCount = 0;
                        $linksCreatedCount = 0;
                        
                        foreach ($subdivisionLotsArray as $index => $childData) {
                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': STARTING ITERATION', [
                                'index' => $index,
                                'childData_dump' => json_encode($childData),
                                'designation_key_exists' => isset($childData['designation']),
                                'area_key_exists' => isset($childData['area']),
                                'designation_value' => $childData['designation'] ?? 'NOT_SET',
                                'area_value' => $childData['area'] ?? 'NOT_SET'
                            ]);

                            $designation = trim($childData['designation'] ?? chr(65 + $index));
                            $area = floatval($childData['area'] ?? 0);

                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': Values extracted', [
                                'array_index' => $index,
                                'designation_extracted' => $designation,
                                'area_extracted' => $area
                            ]);

                            // CREATE CHILD LAND RECORD
                            $newSurveyNo = $application->landRecord->survey_no . '(' . $designation . ')';
                            
                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': About to create LandRecord', [
                                'new_survey_no' => $newSurveyNo,
                                'total_area' => $area,
                                'location' => $application->landRecord->location ?? 'NULL'
                            ]);
                            
                            $childLot = LandRecord::create([
                                'survey_no' => $newSurveyNo,
                                'total_area' => $area,
                                'location' => $application->landRecord->location ?? '',
                                'is_subdivided' => false
                            ]);

                            $lotsCreatedCount++;

                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': Child LandRecord CREATED', [
                                'new_land_record_id' => $childLot->id,
                                'new_survey_no' => $childLot->survey_no,
                                'new_total_area' => $childLot->total_area,
                                'lots_created_so_far' => $lotsCreatedCount
                            ]);

                            // CREATE SUBDIVISION LINK using raw INSERT
                            $now = now();
                            
                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': About to insert subdivision link', [
                                'parent_lot_id' => $application->land_record_id,
                                'child_lot_id' => $childLot->id,
                                'split_date' => $now->toDateString()
                            ]);

                            $insertResult = DB::table('subdivisions')->insert([
                                'parent_lot_id' => $application->land_record_id,
                                'child_lot_id' => $childLot->id,
                                'split_date' => $now->toDateString(),
                                'created_at' => $now,
                                'updated_at' => $now
                            ]);

                            \Log::info('Step 5b-Iteration-' . ($index + 1) . ': Insert query result', [
                                'insert_result' => $insertResult,
                                'insert_result_type' => gettype($insertResult)
                            ]);

                            if ($insertResult === true) {
                                $linksCreatedCount++;
                                \Log::info('Step 5b-Iteration-' . ($index + 1) . ': Subdivision LINK CREATED', [
                                    'subdivision_link_id' => 'just_inserted',
                                    'parent_lot_id' => $application->land_record_id,
                                    'child_lot_id' => $childLot->id,
                                    'links_created_so_far' => $linksCreatedCount
                                ]);
                            } else {
                                \Log::error('Step 5b-Iteration-' . ($index + 1) . ': Subdivision LINK INSERT RETURNED FALSE', [
                                    'parent_lot_id' => $application->land_record_id,
                                    'child_lot_id' => $childLot->id,
                                    'insert_result' => $insertResult
                                ]);
                                throw new \Exception('Subdivision insert returned false for index ' . $index);
                            }
                        }

                        \Log::info('Step 5b-Final: Loop completed successfully', [
                            'total_lots_created' => $lotsCreatedCount,
                            'total_links_created' => $linksCreatedCount,
                            'array_count' => count($subdivisionLotsArray)
                        ]);

                        // Mark parent lot as subdivided
                        $application->landRecord->update(['is_subdivided' => true]);

                        \Log::info('Step 5c: Parent lot marked as subdivided', [
                            'parent_lot_id' => $application->land_record_id,
                            'is_subdivided' => true
                        ]);

                        // Set lot_type in application update
                        $updateData['lot_type'] = 'subdivision';

                        \Log::info('Step 5d: SUBDIVISION PROCESSING COMPLETED', [
                            'total_children_created' => $lotsCreatedCount,
                            'parent_lot_id' => $application->land_record_id
                        ]);

                        // Commit transaction
                        DB::commit();

                    } catch (\Exception $e) {
                        DB::rollback();
                        
                        \Log::error('Step 5-Exception: Error during subdivision processing loop', [
                            'exception_message' => $e->getMessage(),
                            'exception_class' => get_class($e),
                            'exception_file' => $e->getFile(),
                            'exception_line' => $e->getLine(),
                            'exception_trace' => $e->getTraceAsString()
                        ]);
                        
                        throw $e;
                    }
                } else {
                    \Log::warning('Step 5X: Subdivision selected but subdivision_lots array is EMPTY', [
                        'subdivision_lots_array' => $subdivisionLotsArray
                    ]);
                }
            } elseif ($validated['lot_classification'] === 'existing') {
                $updateData['lot_type'] = 'existing_lot';
                
                \Log::info('Step 5: Lot classification set to EXISTING', [
                    'lot_type' => 'existing_lot'
                ]);
            }

            // UPDATE APPLICATION
            $application->update($updateData);

            \Log::info('Step 6: Application updated', [
                'application_id' => $application->id,
                'tracking_no' => $application->tracking_no,
                'lot_type_set' => $updateData['lot_type'] ?? 'not_set',
                'status' => $validated['status']
            ]);

            // Create status history record (audit trail)
            StatusHistory::create([
                'application_id' => $application->id,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
                'updated_by' => Auth::user()->name
            ]);

            \Log::info('Step 7: Status history record created', [
                'application_id' => $application->id,
                'new_status' => $validated['status']
            ]);

            \Log::info('=== APPLICATION PROCESSED SUCCESSFULLY ===', [
                'application_id' => $application->id,
                'tracking_no' => $application->tracking_no,
                'lot_classification' => $validated['lot_classification'] ?? 'null',
                'subdivisions_created' => $validated['lot_classification'] === 'subdivision' ? count($validated['subdivision_lots'] ?? []) : 0,
                'application_status' => $validated['status'],
                'land_officer' => Auth::user()->name,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully!',
                'application_id' => $application->id,
                'new_status' => $validated['status']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('=== VALIDATION ERROR DURING PROCESSING ===', [
                'application_id' => $id,
                'validation_errors' => $e->errors(),
                'request_data_received' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(' | ', array_map(fn($msgs) => implode(', ', $msgs), $e->errors())),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('=== EXCEPTION DURING APPLICATION PROCESSING ===', [
                'application_id' => $id,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for duplicate applicants by name
     * Used to prevent creating duplicate applicant profiles
     */
    public function checkDuplicateApplicant(Request $request)
    {
        $fullName = trim($request->input('full_name', ''));
        
        if (empty($fullName)) {
            return response()->json(['exists' => false]);
        }

        // Search for applicants with similar names (case-insensitive, exact match)
        $duplicates = Applicant::whereRaw('LOWER(full_name) = ?', [strtolower($fullName)])
            ->select('id', 'full_name', 'address', 'contact_no')
            ->get();

        if ($duplicates->isNotEmpty()) {
            return response()->json([
                'exists' => true,
                'duplicates' => $duplicates,
                'count' => $duplicates->count()
            ]);
        }

        return response()->json(['exists' => false]);
    }
}