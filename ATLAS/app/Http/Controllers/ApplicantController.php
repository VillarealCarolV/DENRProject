<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Applicant;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $applicants = Applicant::latest()->get();
        return view('applicants.index', compact('applicants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('applicants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
        ]);

        // Save to the database
        $applicant = Applicant::create($validated);

        // Return JSON response for AJAX requests, redirect for normal requests
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Applicant created successfully!',
                'applicant' => $applicant
            ], 201);
        }

        // Send the user back with a success message
        return redirect()->route('applicants.index')->with('success', 'Applicant saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $applicant = Applicant::findOrFail($id);
        return view('applicants.show', compact('applicant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $applicant = Applicant::findOrFail($id);
        return view('applicants.edit', compact('applicant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $applicant = Applicant::findOrFail($id);
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string',
        ]);

        $applicant->update($validated);

        return redirect()->route('applicants.show', $applicant->id)->with('success', 'Applicant updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // AUTHORIZATION: Only admin and records_officer roles can delete applicants
        $allowedRoles = ['admin', 'records_officer'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            $message = 'Unauthorized: You do not have permission to delete applicants.';
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $message], 403);
            }
            abort(403, $message);
        }

        try {
            $applicant = Applicant::findOrFail($id);
            $applicantName = $applicant->full_name;

            // Log deletion attempt
            \Log::info('Deleting applicant', [
                'applicant_id' => $id,
                'applicant_name' => $applicantName,
                'deleted_by' => Auth::user()->name,
                'deleted_by_id' => Auth::id(),
                'timestamp' => now()
            ]);

            // Delete the applicant (cascade delete will handle related applications)
            $applicant->delete();

            \Log::info('Applicant successfully deleted', [
                'applicant_id' => $id,
                'applicant_name' => $applicantName
            ]);

            // Return JSON response for AJAX requests
            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Applicant deleted successfully',
                    'applicant_id' => $id
                ]);
            }

            // Redirect for normal requests
            return redirect()->route('applicants.index')->with('success', 'Applicant deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Error deleting applicant', [
                'applicant_id' => $id,
                'error' => $e->getMessage(),
                'deleted_by' => Auth::user()->name
            ]);

            $message = 'An error occurred while deleting the applicant: ' . $e->getMessage();

            if (request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json(['success' => false, 'message' => $message, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Export applicants in multiple formats (CSV, Excel, PDF)
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        
        $applicants = Applicant::latest()->get();

        switch ($format) {
            case 'excel':
                return $this->exportExcel($applicants);
            case 'pdf':
                return $this->exportPdf($applicants);
            default:
                return $this->exportCsv($applicants);
        }
    }

    /**
     * Export to CSV format
     */
    private function exportCsv($applicants)
    {
        $fileName = 'applicants_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function() use ($applicants) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Full Name', 'Address', 'Contact No.']);

            // Data rows
            foreach ($applicants as $applicant) {
                fputcsv($file, [
                    $applicant->full_name,
                    $applicant->address,
                    $applicant->contact_no
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel format
     */
    private function exportExcel($applicants)
    {
        $fileName = 'applicants_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ApplicantsExport($applicants), $fileName);
    }

    /**
     * Export to PDF format
     */
    private function exportPdf($applicants)
    {
        $fileName = 'applicants_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $html = view('exports.applicants-pdf', compact('applicants'))->render();
        
        $pdf = Pdf::loadHTML($html);
        
        return $pdf->download($fileName);
    }
}
