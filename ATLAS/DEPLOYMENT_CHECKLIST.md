# ATLAS Land Officer Workflow - Implementation Checklist

## ✅ Pre-Deployment Checklist

### Database Preparation
- [ ] Backup current production database
- [ ] Verify database structure (atlas_db)
- [ ] Check database user permissions for migrations
- [ ] Verify no conflicts with existing applications data

### Code Deployment
- [ ] Pull latest code from repository
- [ ] Verify all new files are present:
  - [ ] `database/migrations/2026_04_16_000000_add_subdivision_fields_to_applications_table.php`
  - [ ] `resources/views/applications/edit.blade.php`
  - [ ] `resources/views/components/notification-modal.blade.php`
  - [ ] Documentation files (3 files)
- [ ] Clear Laravel cache:
  ```bash
  php artisan cache:clear
  php artisan view:clear
  php artisan config:clear
  ```

### Migration Execution
- [ ] Run migration:
  ```bash
  php artisan migrate
  ```
- [ ] Verify migration status:
  ```bash
  php artisan migrate:status
  ```
- [ ] Confirm tables updated:
  ```sql
  DESCRIBE applications;
  ```

### User Role Updates
- [ ] Update Records Officer role:
  ```sql
  UPDATE users SET role = 'records_officer' WHERE email = 'records@denr.gov.ph';
  ```
- [ ] Update Land Officer role:
  ```sql
  UPDATE users SET role = 'land_management_officer' WHERE email = 'land@denr.gov.ph';
  ```
- [ ] Verify roles updated:
  ```sql
  SELECT id, name, email, role FROM users;
  ```

---

## ✅ Post-Deployment Testing

### Authentication & Authorization
- [ ] Records Officer can login
- [ ] Land Officer can login
- [ ] Admin can login
- [ ] Unauthorized users redirected to login

### Records Officer Workflow
- [ ] Navigate to Applications → New Master Intake
- [ ] Form displays correctly with all required fields
- [ ] Survey number format validation works (ABC-##-######)
- [ ] Form validation rejects invalid data
- [ ] Successfully create test application
- [ ] Application appears in applications list
- [ ] Status history shows "Pending" for new application

### Land Officer Assessment Workflow
- [ ] Login as Land Officer
- [ ] See applications list with Edit button
- [ ] Edit button (pen-to-square icon) visible for pending apps
- [ ] Click Edit button opens assessment form
- [ ] Assessment form displays all sections:
  - [ ] Applicant Information (read-only)
  - [ ] Land Record Information (read-only)
  - [ ] Lot Classification section
  - [ ] Subdivision Details section (hidden initially)
  - [ ] Final Decision section
  - [ ] Status History timeline

### Lot Type Selection Testing

#### Test: Existing Lot Selection
- [ ] Select "Existing Lot" radio button
- [ ] Subdivision fields hide
- [ ] New Lot Number field becomes optional
- [ ] Subdivided Area field becomes optional
- [ ] Can save with existing lot selected
- [ ] Status history shows "Existing Lot" classification

#### Test: Subdivision Selection
- [ ] Select "Subdivision" radio button
- [ ] Subdivision fields appear
- [ ] New Lot Number becomes required
- [ ] Subdivided Area becomes required
- [ ] Cannot submit without new lot number
- [ ] Cannot submit without subdivided area

### Subdivision Calculation Testing
- [ ] Existing mother lot total area displays (e.g., 10000 sqm)
- [ ] Enter subdivided area (e.g., 5000)
- [ ] Remaining area calculates (shows 5000)
- [ ] Real-time updates on area change
- [ ] If subdivided area > mother lot total:
  - [ ] Error displays
  - [ ] Field highlights as invalid
  - [ ] Cannot submit form

### Assessment Completion Testing
- [ ] Select status: "In Process", "Approved", or "Rejected"
- [ ] Add remarks (minimum 10 characters required):
  - [ ] Less than 10 chars shows error
  - [ ] 10+ characters accepted
- [ ] Optional patent details can be filled
- [ ] Form validates all required fields
- [ ] Click "Save Assessment" submits successfully
- [ ] Redirects to application details view
- [ ] Success message displays

### Database Updates Verification
After saving assessment, verify in database:
```sql
SELECT 
  id, 
  tracking_no, 
  lot_type, 
  new_lot_number, 
  subdivided_area, 
  remaining_area, 
  status, 
  land_officer_remarks, 
  land_officer_id, 
  assessed_at 
FROM applications 
WHERE id = 123;
```
- [ ] lot_type updated correctly
- [ ] new_lot_number populated (if subdivision)
- [ ] subdivided_area populated (if subdivision)
- [ ] remaining_area calculated correctly
- [ ] land_officer_remarks recorded
- [ ] land_officer_id set to current user
- [ ] assessed_at timestamp recorded

### Audit Trail Verification
- [ ] Status history shows new entry
- [ ] Status updated (In Process/Approved/Rejected)
- [ ] Remarks appear in history
- [ ] Land Officer name displayed
- [ ] Timestamp correct

### Application Details View Testing
- [ ] Navigate to application show page
- [ ] Assessment card displays with:
  - [ ] Lot Type badge
  - [ ] Land Officer name (if assessed)
  - [ ] Subdivision details (if subdivision)
  - [ ] Official remarks
  - [ ] Assessment date/time
- [ ] History timeline shows all status changes

### Notification System Testing
- [ ] Navigate to Notifications
- [ ] Click on a notification
- [ ] Modal opens with notification details
- [ ] Modal displays:
  - [ ] Notification message
  - [ ] Tracking number
  - [ ] Applicant name
  - [ ] Survey number
  - [ ] Lot type (if applicable)
  - [ ] Status badge
  - [ ] Timestamp
- [ ] "View Application" button navigates correctly
- [ ] Close button dismisses modal

### Error Handling Testing

#### Authorization Errors
- [ ] Records Officer tries to edit: 403 error
- [ ] Other users cannot access edit form
- [ ] Correct error message displays

#### Validation Errors
- [ ] Missing status shows error
- [ ] Insufficient remarks shows error
- [ ] Invalid subdivided area shows error
- [ ] Form highlights invalid fields

#### Data Integrity
- [ ] Subdivided area cannot exceed mother lot
- [ ] All required fields enforced
- [ ] No NULL values in critical fields after save

---

## ✅ Performance Testing

### Page Load Times
- [ ] Applications list loads within 2 seconds
- [ ] Assessment form loads within 2 seconds
- [ ] Status history renders quickly (< 1 second)

### Database Queries
- [ ] No N+1 queries in application list
- [ ] Eager loading working (applicant, landRecord, statusHistories)
- [ ] Status history queries optimized

### Real-Time Calculations
- [ ] Remaining area calculation instant (< 100ms)
- [ ] Form validation real-time
- [ ] No lag on field changes

---

## ✅ Browser Compatibility Testing

Test on following browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

Check for:
- [ ] Form displays correctly
- [ ] Modal functions properly
- [ ] Conditional fields show/hide
- [ ] Calculations work
- [ ] All buttons clickable
- [ ] Responsive on mobile

---

## ✅ Accessibility Testing

- [ ] Form labels associated with inputs
- [ ] ARIA labels present where needed
- [ ] Color not sole indicator of status
- [ ] Keyboard navigation works
- [ ] Screen reader friendly
- [ ] Sufficient color contrast

---

## ✅ Security Testing

### Authorization Checks
- [ ] SQL Injection prevention: use Laravel bindings ✓ (Built-in)
- [ ] CSRF token required: all forms checked ✓ (Built-in)
- [ ] XSS prevention: all outputs escaped ✓ (Blade default)
- [ ] Rate limiting: check if needed for forms

### Data Validation
- [ ] Server-side validation enforced
- [ ] Client-side validation (user-friendly)
- [ ] No sensitive data in logs
- [ ] No hardcoded credentials

### User Roles
- [ ] Records Officer cannot assess
- [ ] Land Officer cannot create applications
- [ ] Admin has appropriate access

---

## ✅ Documentation Review

- [ ] LAND_OFFICER_WORKFLOW.md reviewed
- [ ] QUICK_REFERENCE.md accurate
- [ ] IMPLEMENTATION_SUMMARY.md complete
- [ ] API_REFERENCE.md comprehensive
- [ ] Code comments present and clear

---

## ✅ Training & Support

### Developer Training
- [ ] Developers review QUICK_REFERENCE.md
- [ ] Developers understand authorization checks
- [ ] Developers know how to query by lot_type
- [ ] Error handling procedures documented

### User Training
- [ ] Records Officers trained on Master Intake
- [ ] Land Officers trained on assessment workflow
- [ ] Support team briefed on new features
- [ ] Documentation accessible to users

### Support Resources
- [ ] Troubleshooting guide available
- [ ] Contact info for support
- [ ] FAQs documented
- [ ] Common issues documented

---

## ✅ Deployment Rollback Plan

In case of issues:
- [ ] Backup created before migration
- [ ] Rollback script prepared:
  ```bash
  php artisan migrate:rollback
  ```
- [ ] Know how to restore from backup
- [ ] Communication plan if rollback needed

---

## ✅ Post-Implementation Monitoring

### First Week
- [ ] Monitor error logs daily
- [ ] Check for 403 authorization errors
- [ ] Verify all assessments creating proper history
- [ ] User feedback collection

### First Month
- [ ] Performance monitoring
- [ ] Data integrity checks
- [ ] User adoption metrics
- [ ] Bug tracking and fixes

### Ongoing
- [ ] Monthly data integrity verification
- [ ] Performance optimization if needed
- [ ] User feedback incorporation
- [ ] Documentation updates

---

## Test Data Script

### Create Test User (if needed)
```sql
INSERT INTO users (name, email, password, email_verified_at, role, created_at, updated_at) VALUES
('Land Officer Test', 'landtest@denr.gov.ph', '[hashed_password]', NOW(), 'land_management_officer', NOW(), NOW());
```

### Create Test Application Data
```sql
-- 1. Create Applicant
INSERT INTO applicants (full_name, address, contact_no, created_at, updated_at) VALUES
('Test Applicant', '123 Test Street', '09171234567', NOW(), NOW());

-- 2. Create Land Record
INSERT INTO land_records (survey_no, total_area, location, created_at, updated_at) VALUES
('TST-12-123456', 10000.00, 'Test City', NOW(), NOW());

-- 3. Create Application
INSERT INTO applications (tracking_no, applicant_id, land_record_id, date_received, created_at, updated_at) VALUES
('TEST-2026-0001', 1, 1, NOW(), NOW(), NOW());

-- 4. Create Initial Status History
INSERT INTO status_histories (application_id, status, remarks, updated_by, created_at, updated_at) VALUES
(1, 'Pending', 'Test application received', 'System', NOW(), NOW());
```

---

## Issue Tracking Template

When issues arise, track them:

```
Issue: [Brief description]
Severity: [Critical/High/Medium/Low]
Component: [Forms/Auth/Database/UI/etc]
Reproduction Steps:
1. [Step 1]
2. [Step 2]
3. [Step 3]
Expected Result: [What should happen]
Actual Result: [What actually happened]
Error Message: [If applicable]
Browser/OS: [Environment details]
Assigned To: [Developer]
Status: [Open/In Progress/Resolved]
Resolution: [How it was fixed]
```

---

## Sign-Off

### System Administrator
- [ ] Code review completed
- [ ] Migration executed successfully
- [ ] User roles configured
- [ ] System ready for testing

Date: _________________ Signature: _________________

### QA Lead
- [ ] All tests passed
- [ ] Performance acceptable
- [ ] Security verified
- [ ] Ready for production

Date: _________________ Signature: _________________

### Deployment Lead
- [ ] Deployment completed
- [ ] Monitoring established
- [ ] Support briefed
- [ ] Rollback plan ready

Date: _________________ Signature: _________________

---

**Checklist Version**: 1.0  
**Last Updated**: April 16, 2026  
**Next Review**: After first month in production
