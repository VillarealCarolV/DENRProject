<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subdivision;
use App\Models\LandRecord;
use Illuminate\Support\Facades\DB;

class SubdivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('subdivisions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. VALIDATE INCOMING DATA
        $validated = $request->validate([
            'parent_lot_id' => 'required|exists:land_records,id',
            'child_lots' => 'required|array', // Array of new child lot data
            'split_date' => 'required|date'
        ]);


        // Check if the math makes sense!
$parentLot = LandRecord::findOrFail($validated['parent_lot_id']);
$totalChildArea = collect($validated['child_lots'])->sum('total_area');

if ($totalChildArea != $parentLot->total_area) {
    return back()->withErrors(['mat     h_error' => 'Wait! The total area of the child lots (' . $totalChildArea . ') does not equal the mother lot area (' . $parentLot->total_area . ').']);
}

        // USE A DATABASE TRANSACTION (If one step fails, it all rolls back!)
        DB::transaction(function () use ($validated, $request) {
            
            // 2. FLAG THE MOTHER LOT
            $parentLot = LandRecord::findOrFail($validated['parent_lot_id']);
            $parentLot->update(['is_subdivided' => true]);

            // 3. CREATE CHILD LOTS AND RECORD THE SUBDIVISION
            foreach ($validated['child_lots'] as $childData) {
                // A. Create the new child record in land_records table
                $newChildLot = LandRecord::create([
                    'survey_no' => $childData['survey_no'],
                    'total_area' => $childData['total_area'],
                    'location' => $parentLot->location, // Usually same as parent
                    'is_subdivided' => false
                ]);

                // B. Create the connection in the subdivisions table
                Subdivision::create([
                    'parent_lot_id' => $parentLot->id,
                    'child_lot_id' => $newChildLot->id,
                    'split_date' => $validated['split_date']
                ]);
            }
        });

        return redirect()->route('land-records.index')->with('success', 'Subdivision completed successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
