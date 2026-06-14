<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Applicant;
use App\Models\LandRecord;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    /**
     * Display a listing of reports with tabbed interface.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only land officers get the specialized reports
        if ($user->role === 'land_officer') {
            // Fetch data for My Pending Backlog
            $pendingApplications = Application::where('land_officer_id', $user->id)
                ->with([
                    'applicant',
                    'landRecord',
                    'statusHistories' => function($query) {
                        $query->latest()->limit(1);
                    }
                ])
                ->whereHas('statusHistories', function($query) {
                    $query->where('status', 'Pending');
                })
                ->orderBy('date_received', 'asc')
                ->get();

            $totalPending = $pendingApplications->count();
            $totalAssigned = Application::where('land_officer_id', $user->id)->count();
            $totalProcessed = Application::where('land_officer_id', $user->id)
                ->whereHas('statusHistories', function($query) {
                    $query->where('status', 'Approved')
                        ->orWhere('status', 'Rejected');
                })
                ->count();

            // Fetch data for Land Subdivision Report
            // Get month from request or default to current month
            $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
            $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
            $currentMonth = $monthDate->month;
            $currentYear = $monthDate->year;

            $subdividedApplications = Application::where('land_officer_id', $user->id)
                ->with(['applicant', 'landRecord', 'statusHistories' => function($query) {
                    $query->latest()->limit(1);
                }])
                ->whereHas('statusHistories', function($query) use ($currentMonth, $currentYear) {
                    $query->where('status', 'Approved')
                        ->whereMonth('created_at', $currentMonth)
                        ->whereYear('created_at', $currentYear);
                })
                ->get();

            $totalAreaSubdivided = $subdividedApplications->sum('subdivided_area') ?? 0;
            $averageAreaPerApplication = $subdividedApplications->count() > 0 
                ? round($totalAreaSubdivided / $subdividedApplications->count(), 2) 
                : 0;
            $totalApplicationsApproved = $subdividedApplications->count();

            $classificationBreakdown = $subdividedApplications
                ->groupBy('lot_type')
                ->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total_area' => $group->sum('subdivided_area') ?? 0,
                        'average_area' => round($group->sum('subdivided_area') / $group->count(), 2) ?? 0
                    ];
                });

            $monthlyTrend = [];
            for ($month = 1; $month <= 12; $month++) {
                $areaThisMonth = Application::where('land_officer_id', $user->id)
                    ->whereHas('statusHistories', function($query) use ($month, $currentYear) {
                        $query->where('status', 'Approved')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $currentYear);
                    })
                    ->sum('subdivided_area') ?? 0;

                $monthlyTrend[Carbon::create()->month($month)->shortMonthName] = $areaThisMonth;
            }

            return view('reports.index', compact(
                'pendingApplications',
                'totalPending',
                'totalAssigned',
                'totalProcessed',
                'subdividedApplications',
                'totalAreaSubdivided',
                'averageAreaPerApplication',
                'totalApplicationsApproved',
                'classificationBreakdown',
                'monthlyTrend'
            ));
        } else {
            // General summary statistics for other roles
            $totalApplications = Application::count();
            $totalApplicants = Applicant::count();
            $totalLandRecords = LandRecord::count();
            
            $recentApplications = Application::with(['applicant', 'landRecord', 'statusHistories'])
                ->latest()
                ->limit(20)
                ->get();

            return view('reports.index', compact(
                'totalApplications',
                'totalApplicants',
                'totalLandRecords',
                'recentApplications'
            ));
        }
    }

    /**
     * Display the pending backlog for the current land officer.
     * Shows only applications assigned to them, filtered by pending status.
     */
    public function myPendingBacklog()
    {
        // Ensure user is a land officer
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized access');
        }

        // Get applications assigned to current user with pending status, ordered by oldest first
        $pendingApplications = Application::where('land_officer_id', Auth::id())
            ->with([
                'applicant',
                'landRecord',
                'statusHistories' => function($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->whereHas('statusHistories', function($query) {
                $query->where('status', 'Pending');
            })
            ->orderBy('date_received', 'asc') // Oldest first
            ->get();

        // Count statistics for this officer
        $totalPending = $pendingApplications->count();
        $totalAssigned = Application::where('land_officer_id', Auth::id())->count();
        $totalProcessed = Application::where('land_officer_id', Auth::id())
            ->whereHas('statusHistories', function($query) {
                $query->where('status', 'Approved')
                    ->orWhere('status', 'Rejected');
            })
            ->count();

        return view('reports.my-pending-backlog', compact(
            'pendingApplications',
            'totalPending',
            'totalAssigned',
            'totalProcessed'
        ));
    }

    /**
     * Display land subdivision and classification report.
     * Shows total area subdivided/approved during the selected month.
     */
    public function landSubdivisionReport(Request $request)
    {
        // Ensure user is a land officer
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized access');
        }

        // Get month from request or default to current month
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        $currentMonth = $monthDate->month;
        $currentYear = $monthDate->year;

        // Get approved applications for current officer in selected month with subdivision data
        $subdividedApplications = Application::where('land_officer_id', Auth::id())
            ->with(['applicant', 'landRecord', 'statusHistories' => function($query) {
                $query->latest()->limit(1);
            }])
            ->whereHas('statusHistories', function($query) use ($currentMonth, $currentYear) {
                $query->where('status', 'Approved')
                    ->whereMonth('created_at', $currentMonth)
                    ->whereYear('created_at', $currentYear);
            })
            ->get();

        // Calculate statistics
        $totalAreaSubdivided = $subdividedApplications->sum('subdivided_area') ?? 0;
        $averageAreaPerApplication = $subdividedApplications->count() > 0 
            ? round($totalAreaSubdivided / $subdividedApplications->count(), 2) 
            : 0;
        $totalApplicationsApproved = $subdividedApplications->count();

        // Get classification breakdown by lot type
        $classificationBreakdown = $subdividedApplications
            ->groupBy('lot_type')
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_area' => $group->sum('subdivided_area') ?? 0,
                    'average_area' => round($group->sum('subdivided_area') / $group->count(), 2) ?? 0
                ];
            });

        // Get monthly trend for selected year (all approved subdivisions)
        $monthlyTrend = [];
        for ($month = 1; $month <= 12; $month++) {
            $areaThisMonth = Application::where('land_officer_id', Auth::id())
                ->whereHas('statusHistories', function($query) use ($month, $currentYear) {
                    $query->where('status', 'Approved')
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $currentYear);
                })
                ->sum('subdivided_area') ?? 0;

            $monthlyTrend[Carbon::create()->month($month)->shortMonthName] = $areaThisMonth;
        }

        return view('reports.land-subdivision-report', compact(
            'subdividedApplications',
            'totalAreaSubdivided',
            'averageAreaPerApplication',
            'totalApplicationsApproved',
            'classificationBreakdown',
            'monthlyTrend'
        ));
    }

    /**
     * Export pending backlog report for land officer
     */
    public function exportPendingBacklog(Request $request)
    {
        // Ensure user is a land officer
        if (Auth::user()->role !== 'land_officer') {
            abort(403, 'Unauthorized access');
        }

        $format = $request->query('format', 'csv');

        // Get applications assigned to current user with pending status
        $pendingApplications = Application::where('land_officer_id', Auth::id())
            ->with([
                'applicant',
                'landRecord',
                'statusHistories' => function($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->whereHas('statusHistories', function($query) {
                $query->where('status', 'Pending');
            })
            ->orderBy('date_received', 'asc')
            ->get();

        switch ($format) {
            case 'excel':
                return $this->exportExcel($pendingApplications);
            case 'pdf':
                return $this->exportPdf($pendingApplications);
            default:
                return $this->exportCsv($pendingApplications);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportCsv($applications)
    {
        $fileName = 'pending_backlog_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Tracking No.', 'Applicant Name', 'Survey No.', 'Date Received', 'Lot Type', 'Days Pending']);

            // Data rows
            foreach ($applications as $app) {
                $daysPending = (int)$app->date_received->diffInDays(now());
                
                fputcsv($file, [
                    $app->tracking_no,
                    $app->applicant->full_name ?? 'N/A',
                    $app->landRecord->survey_no ?? 'N/A',
                    $app->date_received->format('Y-m-d'),
                    $app->lot_type ?? '-',
                    $daysPending
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
        $fileName = 'pending_backlog_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ApplicationsExport($applications), $fileName);
    }

    /**
     * Export to PDF format
     */
    private function exportPdf($applications)
    {
        $fileName = 'pending_backlog_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $html = view('exports.applications-pdf', compact('applications'))->render();
        
        return \PDF::loadHTML($html)
                    ->setPaper('a4', 'landscape')
                    ->download($fileName);
    }
}
