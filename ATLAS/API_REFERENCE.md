# ATLAS Routes & API Reference

## Web Routes

### Application Management Routes

#### List Applications
```
GET /applications
Route Name: applications.index
Description: Display all applications with search and filter
Access: All authenticated users
Response: applications.index view
```

#### Create Application Form (Master Intake)
```
GET /applications/master/create
Route Name: applications.masterCreate
Description: Show master intake form (single form for applicant, land record, application)
Access: All authenticated users (Records Officer intended)
Response: applications.master-create view
Requirement: role = 'records_officer' (enforced in store method)
```

#### Store Master Intake Data
```
POST /applications/master/store
Route Name: applications.masterStore
Description: Save new applicant, land record, and application
Access: Records Officers only (Authorization check)
Error: 403 if user role ≠ 'records_officer'
Redirect: Back with success message
Validation:
  - full_name: required|string|max:255
  - address: nullable|string
  - contact_no: nullable|string
  - survey_no: required|unique|regex:/^[A-Za-z]{3}-\d{2}-\d{6}$/
  - total_area: required|numeric|min:1
  - location: required|string
  - tracking_no: required|unique|string
  - date_received: required|date
Database Transaction: Creates Applicant, LandRecord, Application, StatusHistory
```

#### Show Application Details
```
GET /applications/{id}
Route Name: applications.show
Description: Display application details with status history
Access: All authenticated users
Parameters: id (application ID)
Response: applications.show view
Includes:
  - Applicant information
  - Land record information
  - Application details
  - Assessment details (if completed)
  - Complete status history timeline
  - Audit trail
```

#### Edit/Assess Application
```
GET /applications/{id}/edit
Route Name: applications.edit
Description: Show assessment form for Land Officer
Access: Land Management Officers only (Authorization check)
Error: 403 if user role ≠ 'land_management_officer'
Parameters: id (application ID)
Response: applications.edit view
Form Content:
  - Lot type selection (Existing Lot vs Subdivision)
  - Subdivision details (conditional)
  - Status selection
  - Remarks textarea
  - Patent details
  - Current status history
```

#### Update Application Assessment
```
PUT /applications/{id}
Route Name: applications.update
Description: Save Land Officer's assessment and update application
Access: Land Management Officers only (Authorization check)
Error: 403 if user role ≠ 'land_management_officer'
Parameters: id (application ID)
Method: PUT (use PATCH for partial updates)
Redirect: applications.show with success message
Validation:
  - lot_type: required|in:existing_lot,subdivision
  - new_lot_number: nullable|required_if:lot_type,subdivision|string|max:255
  - subdivided_area: nullable|required_if:lot_type,subdivision|numeric|min:0.01
  - status: required|in:In Process,Approved,Rejected
  - land_officer_remarks: required|string|min:10
  - patent_details: nullable|string
  - patent_type: nullable|string
Processing:
  - Calculates remaining_area = total_area - subdivided_area
  - Records land_officer_id (Auth::id())
  - Records assessed_at timestamp
  - Creates StatusHistory entry for audit trail
```

#### Store Standard Application (Alternative)
```
POST /applications
Route Name: applications.store
Description: Create application (without master intake)
Access: All authenticated users
Validation:
  - tracking_no: required|unique|string
  - applicant_id: required|exists:applicants,id
  - land_record_id: required|exists:land_records,id
  - date_received: required|date
Note: Less commonly used, Master Intake (masterStore) is preferred
```

#### Create Standard Application Form
```
GET /applications/create
Route Name: applications.create
Description: Show standard application creation form
Access: All authenticated users
Response: applications.create view
Note: Master Intake form (masterCreate) is preferred
```

---

### Notification Routes

#### View All Notifications
```
GET /notifications
Route Name: notifications.index
Description: Display all notifications for logged-in user
Access: Authenticated users
Response: notifications.index view
Pagination: Paginated results
Features:
  - Search/filter by status
  - Mark all as read button
  - Modal for viewing details
```

#### Mark Notification as Read
```
GET /notifications/{id}/mark-as-read
Route Name: notifications.markAsRead
Description: Mark single notification as read and redirect
Access: Authenticated users
Parameters: id (notification ID)
Redirect: notifications.index
```

#### Mark All Notifications as Read
```
POST /notifications/mark-all-as-read
Route Name: notifications.markAllAsRead
Description: Mark all notifications for user as read
Access: Authenticated users
Method: POST
Redirect: notifications.index
```

---

### Land Record Routes

#### List Land Records
```
GET /land-records
Route Name: land-records.index
Description: Display all land records
Access: All authenticated users
Response: land-records.index view
```

#### Show Land Record
```
GET /land-records/{id}
Route Name: land-records.show
Description: Display land record details
Access: All authenticated users
Parameters: id (land record ID)
Response: land-records.show view
```

#### Other Land Record Operations
```
GET /land-records/create - Show create form
POST /land-records - Store new land record
GET /land-records/{id}/edit - Show edit form
PUT /land-records/{id} - Update land record
DELETE /land-records/{id} - Delete land record
```

---

### Applicant Routes

#### List Applicants
```
GET /applicants
Route Name: applicants.index
Description: Display all applicants
Access: All authenticated users
Response: applicants.index view
```

#### Show Applicant
```
GET /applicants/{id}
Route Name: applicants.show
Description: Display applicant details
Access: All authenticated users
Parameters: id (applicant ID)
Response: applicants.show view
```

#### Export Applicants
```
GET /applicants/export
Route Name: applicants.export
Description: Export applicants to CSV/Excel/PDF
Access: Authenticated users
Query Parameters: 
  - format: csv|excel|pdf
Response: File download
```

---

### Export Routes

#### Export Applications
```
GET /applications/export
Route Name: applications.export
Description: Export applications to CSV/Excel/PDF
Access: Authenticated users
Query Parameters:
  - format: csv|excel|pdf
Response: File download
```

#### Export Land Records
```
GET /land-records/export
Route Name: land-records.export
Description: Export land records to CSV/Excel/PDF
Access: Authenticated users
Query Parameters:
  - format: csv|excel|pdf
Response: File download
```

---

### Search & Reports

#### Search
```
GET /search
Route Name: search
Description: Search across applications, applicants, and land records
Access: Authenticated users
Query Parameters:
  - q: search query string
Response: search results view
```

#### Reports
```
GET /reports
Route Name: reports.index
Description: View various reports and analytics
Access: Authenticated users
Response: reports.index view
```

---

### Dashboard & Profile

#### Dashboard
```
GET /dashboard
Route Name: dashboard
Description: Main application dashboard
Access: Authenticated, verified users
Middleware: auth, verified
Response: dashboard view
```

#### Edit Profile
```
GET /profile
Route Name: profile.edit
Description: Show profile edit form
Access: Authenticated users
Response: profile edit view
```

#### Update Profile
```
PATCH /profile
Route Name: profile.update
Description: Update user profile information
Access: Authenticated users
```

#### Delete Profile
```
DELETE /profile
Route Name: profile.destroy
Description: Delete user account
Access: Authenticated users
```

---

### Status Update Route

#### Update Application Status
```
POST /applications/{id}/updateStatus
Route Name: applications.updateStatus
Description: Update application status and remarks
Access: All authenticated users
Method: POST
Parameters:
  - id: application ID
  - status: new status (In Process, Approved, Rejected)
  - remarks: status update remarks
Processing:
  - Updates application status
  - Creates StatusHistory entry
  - Returns success response
```

---

## Middleware

### Applied Middleware

```php
// Authentication middleware
middleware('auth')           // User must be logged in
middleware('auth:sanctum')   // API authentication

// Verification middleware
middleware('verified')       // User email must be verified

// Authorization middleware (Custom/Policy-based)
// Implemented in controllers via Auth::user()->role checks
```

### Middleware Groups

```php
// Web middleware group (applied to all web routes)
- \App\Http\Middleware\EncryptCookies
- \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse
- \Illuminate\Session\Middleware\StartSession
- \Illuminate\View\Middleware\ShareErrorsFromSession
- \App\Http\Middleware\VerifyCsrfToken
- \Illuminate\Routing\Middleware\SubstituteBindings

// Protected routes (require 'auth' middleware)
- Route::middleware('auth')->group(function() { ... })

// Email verified routes (require 'verified' middleware)
- Route::middleware(['auth', 'verified'])->get('/dashboard')
```

---

## HTTP Methods

### Standard REST Conventions

```
GET     - Retrieve resource(s)
POST    - Create new resource
PUT     - Replace entire resource
PATCH   - Partial update to resource
DELETE  - Remove resource
```

### ATLAS Usage

- **GET** - Listing, showing, form displays
- **POST** - Creating records (applications, assessments via master store)
- **PUT** - Full update of application (assessment submission)
- **PATCH** - Profile updates
- **DELETE** - Deleting records

---

## Response Codes

### Successful Responses
```
200 OK           - Request successful, returning data
201 Created      - Resource created successfully
204 No Content   - Successful, no content to return
```

### Redirection
```
302 Found        - Temporary redirect (after POST, use redirect()->to())
307 Temp Redirect - Temporary, preserves method
```

### Client Errors
```
400 Bad Request  - Invalid input data
403 Forbidden    - Insufficient permissions (abort(403))
404 Not Found    - Resource not found (findOrFail)
422 Unprocessable Entity - Validation failed
```

### Server Errors
```
500 Internal Error - Unexpected server error
503 Unavailable  - Service temporarily unavailable
```

---

## Query Parameters & Filters

### Search Endpoint
```
/search?q=tracking_number
/search?q=applicant_name
/search?q=survey_no
```

### Export Endpoint
```
/applications/export?format=csv
/applications/export?format=excel
/applications/export?format=pdf
/applicants/export?format=csv
/applicants/export?format=excel
/applicants/export?format=pdf
```

### Status Update Endpoint
```
POST /applications/{id}/updateStatus
Parameters:
  - status: "In Process" | "Approved" | "Rejected"
  - remarks: string (assessment notes)
```

---

## Form Data

### Master Intake Form (Create Application)
```
POST /applications/master/store
Content-Type: application/x-www-form-urlencoded

Fields:
  full_name: string (applicant name)
  address: string (applicant address)
  contact_no: string (applicant phone)
  survey_no: string (format: ABC-12-123456)
  total_area: float (square meters)
  location: string (land location)
  tracking_no: string (unique identifier)
  date_received: date (YYYY-MM-DD)
  _token: CSRF token (auto-included by Blade)
```

### Assessment Form (Update Application)
```
PUT /applications/{id}
Content-Type: application/x-www-form-urlencoded

Fields:
  lot_type: "existing_lot" | "subdivision"
  new_lot_number: string (required if subdivision)
  subdivided_area: float (required if subdivision)
  status: "In Process" | "Approved" | "Rejected"
  land_officer_remarks: string (min 10 chars)
  patent_details: string (optional)
  patent_type: string (optional)
  _method: "PUT" (method spoofing)
  _token: CSRF token (auto-included by Blade)
```

---

## Error Handling

### Common Errors & Routes

#### 403 Unauthorized - Assessment Form
```
User tries: GET /applications/{id}/edit
User role: NOT 'land_management_officer'
Response: 403 error "Only Land Officers can assess applications"
Fix: Update user role in database
```

#### 422 Validation Error - Assessment Submit
```
User submits: PUT /applications/{id}
Missing: land_officer_remarks (less than 10 chars)
Response: 422 with validation errors
Validation Rules:
  - lot_type: required|in:existing_lot,subdivision
  - new_lot_number: required_if:lot_type,subdivision
  - subdivided_area: required_if:lot_type,subdivision|numeric|min:0.01
  - status: required|in:In Process,Approved,Rejected
  - land_officer_remarks: required|string|min:10
```

#### 404 Not Found
```
User tries: GET /applications/9999
Application doesn't exist
Response: 404 error "Application not found"
```

---

## Authentication & Authorization

### Role-Based Access Control

```
Roles:
  - admin (full access)
  - records_officer (create applications)
  - land_management_officer (assess applications)
  - user (default/no special access)

Authorization Checks:
  - ApplicationController::masterStore() checks role = 'records_officer'
  - ApplicationController::edit() checks role = 'land_management_officer'
  - ApplicationController::update() checks role = 'land_management_officer'
```

### Session Management

```
Login: /login (provided by Laravel Breeze)
Register: /register (provided by Laravel Breeze)
Logout: POST /logout (provided by Laravel Breeze)
Reset Password: /forgot-password (provided by Laravel Breeze)
```

---

## API Documentation

### Creating an Application (Records Officer)

**Request:**
```
POST /applications/master/store HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
Authorization: Session cookie

full_name=John+Doe&
address=123+Main+St&
contact_no=09171234567&
survey_no=ABC-12-123456&
total_area=10000.00&
location=Quezon+City&
tracking_no=APP-2026-0001&
date_received=2026-04-16&
_token=CSRF_TOKEN
```

**Response:**
```
HTTP/1.1 302 Found
Location: /applications
Set-Cookie: session=...

[After redirect to GET /applications]
Flash Message: "Application created successfully!"
```

---

### Assessing an Application (Land Officer)

**Step 1: Get Assessment Form**
```
GET /applications/123/edit HTTP/1.1
Host: localhost
Authorization: Session cookie

Response:
HTTP/1.1 200 OK
Content-Type: text/html
[HTML form content]
```

**Step 2: Submit Assessment**
```
PUT /applications/123 HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
Authorization: Session cookie

lot_type=subdivision&
new_lot_number=001-A&
subdivided_area=5000.00&
status=Approved&
land_officer_remarks=Approved+based+on+physical+verification&
patent_details=Agricultural+Free+Patent&
patent_type=Residential&
_method=PUT&
_token=CSRF_TOKEN

Response:
HTTP/1.1 302 Found
Location: /applications/123
Set-Cookie: session=...

Flash Message: "Assessment completed successfully!"
```

---

**Last Updated**: April 16, 2026  
**Version**: 1.0  
**Maintainer**: DENR ATLAS Development Team
