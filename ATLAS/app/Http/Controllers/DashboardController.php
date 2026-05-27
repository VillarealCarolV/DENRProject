<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\Applicant;
use App\Models\LandRecord;
use App\Models\StatusHistory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Records Officers do not have access to the Dashboard
        // They use the Intake Workstation instead
        if (Auth::user()->role === 'records_officer') {
            return redirect()->route('applications.index');
        }

        // Land Officers' home page is the Processing Queue
        if (Auth::user()->role === 'land_officer') {
            return redirect()->route('processing-queue');
        }
        // Get dashboard stat cards with single queries
        $pendingCount = StatusHistory::where('status', 'Pending')->count();
        $currentMonth = Carbon::now()->month;
        $approvedThisMonth = StatusHistory::where('status', 'Approved')->whereMonth('created_at', $currentMonth)->count();
        $landRecordsCount = LandRecord::count();
        $activeApplicants = Applicant::count();

        // --- OPTIMIZED CHART DATA (Reduced from 24 queries to 2!) ---
        $months = [];
        $submittedData = [];
        $approvedData = [];

        $currentYear = Carbon::now()->year;

        // Build month names
        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->shortMonthName;
        }

        // OPTIMIZATION: Get all submitted applications by month in ONE query
        $submittedByMonth = Application::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month');

        // OPTIMIZATION: Get all approved applications by month in ONE query
        $approvedByMonth = StatusHistory::where('status', 'Approved')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('count', 'month');

        // Build data arrays with proper month mapping (0 for months with no data)
        for ($i = 1; $i <= 12; $i++) {
            $submittedData[] = $submittedByMonth->get($i, 0);
            $approvedData[] = $approvedByMonth->get($i, 0);
        }

        // OPTIMIZATION: Eager load only the latest status for each application
        $recentApplications = Application::with([
            'applicant',
            'landRecord',
            'statusHistories' => function($query) {
                $query->latest()->limit(1);
            }
        ])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('dashboard', compact(
            'pendingCount', 'approvedThisMonth', 'landRecordsCount', 'activeApplicants',
            'months', 'submittedData', 'approvedData', 'recentApplications'
        ));
    }
}