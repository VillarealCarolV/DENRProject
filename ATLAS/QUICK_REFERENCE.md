# ATLAS Land Officer Workflow - Quick Reference Guide

## Quick Setup

1. **Create test user with correct role:**
```sql
UPDATE users SET role = 'land_management_officer' WHERE email = 'land@denr.gov.ph';
```

2. **Run database migration:**
```bash
php artisan migrate
```

3. **Verify role assignments:**
```sql
SELECT id, name, email, role FROM users;
```

## Role Permissions Summary

| Action | Records Officer | Land Officer | Admin |
|--------|-----------------|--------------|-------|
| Create Application | ✅ | ❌ | ✅ |
| View Applications | ✅ | ✅ | ✅ |
| Edit/Assess | ❌ | ✅ | ✅ |
| Approve/Reject | ❌ | ✅ | ✅ |
| View Reports | ✅ | ✅ | ✅ |

## Key Features

### 1. Master Intake Form (Records Officer)
- **Route**: `/applications/master/create`
- **Action**: Create Applicant, Land Record, and Application in one form
- **Validation**: Requires survey_no pattern (XXX-##-######)

### 2. Assessment Form (Land Officer)
- **Route**: `/applications/{id}/edit`
- **Features**:
  - Lot classification (Existing or Subdivision)
  - Conditional subdivision fields
  - Real-time remaining area calculation
  - Status dropdown (In Process, Approved, Rejected)
  - Required remarks (min 10 chars)
  - Audit trail display

### 3. Application Status Flow
```
Created (Pending) 
    → Land Officer Reviews 
    → Approved OR Rejected
    → Status History Updated
```

## Common Developer Tasks

### Add New Authorization Check
```php
// In controller method:
if (Auth::user()->role !== 'land_management_officer') {
    abort(403, 'Unauthorized: Only Land Officers can perform this action.');
}
```

### Update Application with Assessment
```php
$application->update([
    'lot_type' => 'subdivision',
    'new_lot_number' => '001-A',
    'subdivided_area' => 5000.50,
    'remaining_area' => 4999.50, // auto-calculated
    'status' => 'Approved',
    'land_officer_remarks' => 'Approved based on physical verification.',
    'land_officer_id' => Auth::id(),
    'assessed_at' => now(),
]);

// Create audit trail
StatusHistory::create([
    'application_id' => $application->id,
    'status' => 'Approved',
    'remarks' => 'Approved based on physical verification.',
    'updated_by' => Auth::user()->name,
]);
```

### Query Applications by Status
```php
// Pending applications for Land Officer
$pending = Application::whereHas('statusHistories', function($q) {
    $q->where('status', 'Pending');
})->get();

// Applications assessed by specific officer
$assessed = Application::where('land_officer_id', Auth::id())->get();

// Approved applications
$approved = Application::where('status', 'Approved')->get();
```

## File Structure

```
app/
├── Http/Controllers/ApplicationController.php (edit, update methods)
├── Models/
│   ├── Application.php (lot_type, subdivision fields)
│   ├── User.php (hasMany applications as land_officer)
│   └── StatusHistory.php (audit trail)

database/
├── migrations/
│   └── 2026_04_16_000000_add_subdivision_fields_to_applications_table.php
└── seeders/UserSeeder.php (updated roles)

resources/views/applications/
├── index.blade.php (edit button for land officers)
├── edit.blade.php (assessment form - NEW)
└── show.blade.php (detail view)
```

## Routes

```
GET    /applications              - List all applications
POST   /applications              - Create application (Records Officer)
GET    /applications/create       - Create form
GET    /applications/master/create - Master intake form (Records Officer)
POST   /applications/master/store  - Store master intake data
GET    /applications/{id}         - Show application details
GET    /applications/{id}/edit    - Edit/assess form (Land Officer ONLY)
PUT    /applications/{id}         - Update assessment (Land Officer ONLY)
DELETE /applications/{id}         - Delete application
```

## Validation Examples

### Subdivision Assessment
```php
// Valid submission
{
    "lot_type": "subdivision",
    "new_lot_number": "001-A",
    "subdivided_area": "5000.50",
    "status": "Approved",
    "land_officer_remarks": "Approved based on cadastral survey and physical verification."
}

// Invalid: area exceeds mother lot
{
    "lot_type": "subdivision",
    "new_lot_number": "001-A",
    "subdivided_area": "15000", // Mother lot is 10000, this will error
    "status": "Approved",
    "land_officer_remarks": "Assessment notes"
}
```

### Existing Lot Assessment
```php
{
    "lot_type": "existing_lot",
    "status": "Approved",
    "land_officer_remarks": "Approved - existing lot confirmed by cadastral data."
}
```

## Troubleshooting

### Issue: Edit button not showing
- **Check**: User role is `land_management_officer` in database
- **Fix**: `UPDATE users SET role = 'land_management_officer' WHERE id = ?;`

### Issue: "Unauthorized" error on edit form
- **Check**: Controller checks for `land_management_officer` role
- **Check**: Middleware `auth` and `verified` are applied

### Issue: Subdivided area validation fails
- **Check**: Ensure subdivided_area is numeric and positive
- **Check**: Subdivided area must be less than mother lot total area

### Issue: Remaining area not calculating
- **Check**: JavaScript `calculateRemainingArea()` function in edit.blade.php
- **Check**: Ensure `total_area` from landRecord is numeric

## Testing Commands

```bash
# Run migration
php artisan migrate

# Seed test data
php artisan db:seed --class=UserSeeder

# Check database
mysql -u root atlas_db -e "SELECT id, tracking_no, lot_type, status FROM applications LIMIT 5;"

# View user roles
mysql -u root atlas_db -e "SELECT email, role FROM users;"
```

## Notes

- All assessments are recorded in `status_histories` for audit trail
- Land Officer ID and assessment timestamp are automatically recorded
- Subdivision calculations are performed server-side for security
- All form inputs are validated on both client and server
- Remarks are required (min 10 chars) to ensure proper documentation

---

**Version**: 1.0  
**Last Updated**: April 16, 2026
