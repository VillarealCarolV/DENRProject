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
                'message' => 'Master Intake Complete: Applicant, Land, and Application successfully linked and saved!',
                'application' => $application
            ], 201);
        }

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
     * Only Land Officers can edit/assess applications.
     */
    public function edit(string $id)
    {
        // AUTHORIZATION: Only Land Officers can edit applications
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Management Officers can assess applications.');
        }

        $application = Application::with(['applicant', 'landRecord', 'statusHistories'])->findOrFail($id);
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
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized: Only Land Officers can access the Processing Queue.');
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
            \Log::info('Processing application', [
                'app_id' => $id,
                'request_data' => $request->all(),
                'user' => Auth::user()->name
            ]);

            // Validate request
            $validated = $request->validate([
                'application_id' => 'required|exists:applications,id',
                'lot_classification' => 'nullable|in:existing,subdivision',
                'subdivision_lot_number' => 'nullable|string|max:255',
                'status' => 'required|in:Pending,In Process,Approved,Rejected',
                'patent_type' => 'nullable|string|max:255',
                'patent_details' => 'nullable|string|max:1000',
                'remarks' => 'required|string|min:5'
            ]);

            $application = Application::findOrFail($validated['application_id']);

            // Prepare update data
            $updateData = [
                'land_officer_remarks' => $validated['remarks'],
                'land_officer_id' => Auth::id(),
                'assessed_at' => now(),
            ];

            // Only add patent_type if provided (not required)
            if (!empty($validated['patent_type'])) {
                $updateData['patent_type'] = $validated['patent_type'];
            }

            // Only add patent_details if provided; otherwise let DB use default 'Patent'
            if (!empty($validated['patent_details'])) {
                $updateData['patent_details'] = $validated['patent_details'];
            }

            // Handle lot classification if provided
            if ($validated['lot_classification']) {
                $updateData['lot_type'] = $validated['lot_classification'] === 'subdivision' ? 'subdivision' : 'existing_lot';
                
                if ($validated['lot_classification'] === 'subdivision') {
                    $updateData['new_lot_number'] = $validated['subdivision_lot_number'] ?? null;
                }
            }

            // Update application
            $application->update($updateData);

            // Create status history record (audit trail)
            StatusHistory::create([
                'application_id' => $application->id,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
                'updated_by' => Auth::user()->name
            ]);

            \Log::info('Application processed successfully', [
                'application_id' => $application->id,
                'tracking_no' => $application->tracking_no,
                'lot_classification' => $validated['lot_classification'] ?? 'not_set',
                'new_status' => $validated['status'],
                'land_officer' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment saved successfully!',
                'application_id' => $application->id,
                'new_status' => $validated['status']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error processing application', [
                'application_id' => $id,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Error processing application', [
                'application_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment: ' . $e->getMessage()
            ], 500);
        }
    }
}