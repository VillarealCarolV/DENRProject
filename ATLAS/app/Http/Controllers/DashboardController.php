<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Applicant;
use App\Models\LandRecord;
use App\Models\StatusHistory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        
        $pendingCount = StatusHistory::where('status', 'Pending')->count();
        $currentMonth = Carbon::now()->month;
        $approvedThisMonth = StatusHistory::where('status', 'Approved')->whereMonth('created_at', $currentMonth)->count();
        $landRecordsCount = LandRecord::count();
        $activeApplicants = Applicant::count();

        // --- THE CHART DATA (New!) ---
        $months = [];
        $submittedData = [];
        $approvedData = [];

        // Loop through all 12 months of the current year
        $currentYear = Carbon::now()->year;
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->shortMonthName; // Jan, Feb, Mar...

            // Count Applications submitted this month
            $submittedData[] = Application::whereYear('created_at', $currentYear)
                                          ->whereMonth('created_at', $i)
                                          ->count();

            // Count Applications Approved this month
            $approvedData[] = StatusHistory::where('status', 'Approved')
                                           ->whereYear('created_at', $currentYear)
                                           ->whereMonth('created_at', $i)
                                           ->count();
        }

        // Send EVERYTHING to the view!
        $recentApplications = Application::with(['applicant', 'landRecord', 'statusHistories'])
                                         ->latest()
                                         ->limit(10)
                                         ->get();
        
        return view('dashboard', compact(
            'pendingCount', 'approvedThisMonth', 'landRecordsCount', 'activeApplicants',
            'months', 'submittedData', 'approvedData', 'recentApplications'
        ));
    }
}