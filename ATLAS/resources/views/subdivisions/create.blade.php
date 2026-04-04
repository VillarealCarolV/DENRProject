<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test System - Subdivide Land</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            @if(session('success'))
                <div class="alert alert-success fw-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white fw-bold">
                    <h5 class="mb-0">Test Functionality: The Subdivision Engine (Mother to Child Lots)</h5>
                </div>
                <div class="card-body">
                    
                    <form action="{{ route('subdivisions.store') }}" method="POST">
                        @csrf 

                        <h6 class="text-danger border-bottom pb-2">1. The Mother Lot</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Mother Lot ID</label>
                                <input type="number" name="parent_lot_id" class="form-control" required placeholder="Type '1'">
                                <div class="form-text">Type <b>1</b> to select the Land Record you made earlier.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Subdivision (Split Date)</label>
                                <input type="date" name="split_date" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2">2. The New Child Lots</h6>
                        
                        <div class="row mb-3 bg-light p-3 border rounded">
                            <p class="mb-1 fw-bold text-secondary">Child Lot A</p>
                            <div class="col-md-6">
                                <label class="form-label">New Survey Number</label>
                                <input type="text" name="child_lots[0][survey_no]" class="form-control" required placeholder="e.g., CSD-03-000001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Area</label>
                                <input type="number" step="0.01" name="child_lots[0][total_area]" class="form-control" required placeholder="e.g., 500">
                            </div>
                        </div>

                        <div class="row mb-4 bg-light p-3 border rounded">
                            <p class="mb-1 fw-bold text-secondary">Child Lot B</p>
                            <div class="col-md-6">
                                <label class="form-label">New Survey Number</label>
                                <input type="text" name="child_lots[1][survey_no]" class="form-control" required placeholder="e.g., CSD-03-000002">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Total Area</label>
                                <input type="number" step="0.01" name="child_lots[1][total_area]" class="form-control" required placeholder="e.g., 500.50">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold fs-5">
                            Execute Subdivision Logic
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>