<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test System - Add Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
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

            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <h5 class="mb-0">Test Functionality: The Application Bridge</h5>
                </div>
                <div class="card-body">
                    
                    <form action="{{ route('applications.store') }}" method="POST">
                        @csrf 

                        <div class="mb-3">
                            <label class="form-label">Tracking Number</label>
                            <input type="text" name="tracking_no" class="form-control" required placeholder="e.g., CENRO-2026-001">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-primary">Applicant ID</label>
                            <input type="number" name="applicant_id" class="form-control" required placeholder="Type '1'">
                            <div class="form-text">Type <b>1</b> to link the applicant (Juan) you created earlier. In the final UI, this will be a search bar!</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-success">Land Record ID</label>
                            <input type="number" name="land_record_id" class="form-control" required placeholder="Type '1'">
                            <div class="form-text">Type <b>1</b> to link the Mother Lot you created earlier.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date Received</label>
                            <input type="date" name="date_received" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            Create Application Bridge
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>