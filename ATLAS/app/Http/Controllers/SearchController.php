<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;

class SearchController extends Controller
{
    /**
     * Handle search queries for tracking numbers.
     */
    public function __invoke(Request $request)
    {
        $tracking_no = $request->input('tracking_no');
        $results = [];

        if ($tracking_no) {
            $results = Application::with(['applicant', 'landRecord', 'statusHistories'])
                ->where('tracking_no', 'like', '%' . $tracking_no . '%')
                ->get();
        }

        return view('search.results', compact('tracking_no', 'results'));
    }
}
