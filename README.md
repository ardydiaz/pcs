# Post-Class Survey System

A comprehensive Laravel-based faculty evaluation management system for educational institutions. This system streamlines the collection, management, and analysis of post-class student feedback on faculty performance.

---

## Table of Contents

- [Project Overview](#project-overview)
- [System Requirements](#system-requirements)
- [Installation & Setup](#installation--setup)
- [Key Features](#key-features)
- [Development Timeline & Changes](#development-timeline--changes)
- [Architecture Overview](#architecture-overview)
- [Database Schema](#database-schema)
- [External Integrations](#external-integrations)
- [Troubleshooting](#troubleshooting)

---

## Project Overview

The Post-Class Survey System is a Laravel-based application designed to facilitate:
- **Faculty Management**: Import and manage faculty records with HR system integration
- **Course & Schedule Management**: Organize courses, faculty assignments, and class schedules
- **Evaluation Forms**: Generate and distribute student evaluation forms for faculty
- **Response Collection**: Collect and track student feedback responses
- **Reporting & Analytics**: Generate comprehensive reports on faculty performance
- **Audit Logging**: Track all system activities for security and compliance
- **User Management**: Admin controls for system users and access levels

---

## System Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 11.x
- **MySQL**: 5.7 or higher (8.0 recommended)
- **Node.js**: 16.x or higher (for npm dependencies)
- **Composer**: Latest version
- **Web Server**: Apache with mod_rewrite or Nginx

---

## Installation & Setup

### 1. Clone & Install Dependencies

```bash
cd c:\laragon\www\postclasssurvey
composer install
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Migration

```bash
php artisan migrate
php artisan db:seed
```

### 4. Build Frontend Assets

```bash
npm run build
# or for development with watch
npm run dev
```

### 5. Start Development Server

```bash
php artisan serve
```

Access the application at: `http://localhost:8000`

---

## Key Features

### 1. **Faculty Management**
- Import faculty records from Excel files
- Search employee numbers via MCU HRNet API integration
- Manage departments and job titles
- Track faculty creation with audit logs

### 2. **Course & Schedule Management**
- Create and manage courses with unique course codes
- Assign faculty to courses with academic year and semester tracking
- Manage class schedules with day, time, and section information
- Bulk import capability for efficient data entry

### 3. **Evaluation Forms**
- Generate evaluation forms linked to faculty-course-schedule combinations
- Support for multiple semesters and academic years
- QR code generation for mobile access
- URL shortening for easy sharing

### 4. **Response Management**
- Collect student effectiveness ratings (1-4 scale)
- Gather detailed feedback comments
- IP-based cooldown periods to prevent duplicate submissions
- Automatic snapshots of course and schedule details

### 5. **Dashboard & Reports**
- Department-filtered views for scoped access
- Top-rated and low-rated faculty identification
- Faculty evaluation frequency tracking
- Export capabilities for data analysis

### 6. **Audit Logging**
- Track user registrations, logins, and logouts
- Monitor faculty record creation events
- Admin-only access to activity logs
- Advanced filtering by action, date range, and user

### 7. **User Management**
- Role-based access control (Admin, Faculty, Department Head, Student)
- Microsoft OAuth integration for SSO
- Access level management by module
- Admin dashboard for user administration

---

## Development Timeline & Changes

### 04/06/2026 - Performance & UI Optimization

#### UserController - Pagination Enhancement
- **File**: `app/Http/Controllers/user_management/UserController.php`
- **Change**: Added pagination to index function to balance performance for large user datasets
- **Impact**: Improved page load time and reduced memory usage

#### Schedule Model Enhancement
- **File**: `app/Models/Schedule.php`
- **Change**: Added `facultyCourseWithDetails()` function for efficient faculty relationship retrieval
- **Benefit**: Optimized query performance for schedule-faculty associations

#### UI Improvements
- **File**: `resources/views/layouts/sections/navbar/navbar.blade.php`
- **Change**: Disabled search bar in navigation layout
- **Reason**: Simplified navbar UX

---

### 04/12/2026 - Component Refactoring & Import Enhancement

#### Blade Template Refactoring
- **Files**: 
  - `resources/views/content/data-management/dm-faculties.blade.php`
  - `resources/views/content/data-management/dm-courses.blade.php`
  - `resources/views/content/data-management/partials/`

- **Changes**:
  - Extracted inline HTML and CSS into reusable partial components
  - Created 12 new component templates for modular structure
  - Reduced dm-courses file size by 36%
  - Established clean separation of concerns

- **Deliverables**:
  ✅ dm-courses.blade.php: 4 modal/style components extracted  
  ✅ dm-faculties.blade.php: 8 components organized into modular structure  
  ✅ Total Code Reduction: 1,300+ lines  
  ✅ Improved maintainability and scalability

#### Excel Import Enhancement
- **Files**:
  - `app/Imports/FacultyUserImport.php`
  - `app/Http/Controllers/data_management/FacultyController.php` (import function)
  - `resources/views/content/data-management/partials/faculties/excel-import-modal.blade.php`

- **Frontend Improvements**:
  - 🎨 Animated progress bar (0-100%)
  - 📊 Real-time duplicate tracking with skipped employee numbers
  - ⚠️ Comprehensive error alerts with auto-dismiss
  - ✅ Success messages with import statistics
  - 🔒 Proper AJAX headers validation

- **Backend Improvements**:
  - 📋 Added state tracking properties: `$skippedRecords`, `$importedCount`
  - 🔍 Duplicate detection for existing `employee_no` values
  - ✔️ Validation-aware importing (only counts successful imports)
  - 🎯 Public getter methods for import reporting

- **Controller Enhancements**:
  - Dual AJAX detection (X-Requested-With header + wantsJson)
  - Comprehensive try-catch error handling
  - JSON responses for AJAX with success flag, message, counts
  - Fallback redirect for non-AJAX requests

---

### 04/12/2026 - MCU HRNet API Integration

#### Search Employee Number Feature
- **Files**:
  - `app/Http/Controllers/data_management/FacultyController.php` (searchEmployeeNo)
  - `resources/views/content/data-management/partials/faculties/add-faculty-form-modal.blade.php`

- **Integration Details**:
  ✅ POST request to MCU HRNet API endpoint  
  ✅ Form-encoded parameters: C (jsrs1), F (myRSList), P0 ([surname||5])  
  ✅ Required headers: Content-Type, Origin, Referer, User-Agent  
  ✅ HTML response parsing with textarea payload extraction  
  ✅ Response format: array of objects with `employee_no` and `name` fields

- **Authentication Setup**:
  - Requires PHPSESSID and mfa_session cookies from MCU HRNet
  - Cookies passed via environment variables: `MCU_PHPSESSID`, `MCU_MFA_SESSION`
  - Enhanced error handling for expired sessions

⚠️ **IMPORTANT REMINDER**: 
- Cookies expire regularly and need manual refresh
- Admin must login to https://admin.mcu.edu.ph/MCU/HRNet/HRFindEmployee.html.php
- Update `.env` with new cookie values from browser DevTools when integration fails
- Look for PHPSESSID and mfa_session in Network tab cookies

---

### 04/14/2026 - Bulk Import & Evaluation Generation

#### ImportAll Implementation
- **File**: `app/Imports/ImportAll.php`

- **Schema Validation**:
  - ✅ Subject code validation (NOT NULL constraint)
  - ✅ Status enum validation (scheduled, completed, cancelled)
  - ✅ Academic year normalization: "2025 - 2026" → "2025-2026"
  - ✅ Semester normalization: "1st semester" → "1st"
  - ✅ Day format validation (M, T, W, TH, F, S, SU)
  - ✅ Time format normalization: "07:00a to 08:30a" → "07:00a - 08:30a"

- **Import Pipeline**:
  1. Course creation/lookup
  2. Faculty verification
  3. FacultyCourse record creation with section matching
  4. Schedule record creation with all validations

- **Tracking & Reporting**:
  - Protected properties: `$skippedRecords[]`, `$importedCount`, `$debugLog[]`
  - Row-level error tracking with specific failure reasons
  - Public getter methods for post-import reporting

- **Batch Import Statistics**:
  📊 Total Faculty Courses: 2,471 records  
  📊 Total Schedules: 3,344 records  
  📊 Academic Year: 2025-2026 (2nd Semester)  
  📊 Status: All schedules marked as 'scheduled'

#### Evaluation Controller Enhancement
- **File**: `app/Http/Controllers/data_management/EvaluationController.php`

- **Fix**: Added `normalizeAcademicYear()` helper
- **Issue Fixed**: "Selected faculty does not have an assigned course" error
- **Root Cause**: Academic year format mismatch ("2025 - 2026" vs "2025-2026")
- **Solution**: Normalize format before all database queries

---

### 04/14/2026 - Dashboard UI Enhancements

#### Department Display Filtering
- **File**: `resources/views/content/dashboard/dashboard-reports.blade.php`

- **Improvements**:
  ✅ Top Rated Faculties: Display only selected department (if filtering) or first dept  
  ✅ Low Rated Faculties: Context-aware department filtering  
  ✅ Most Evaluated Faculties: Department-filtered display

- **UX Benefits**:
  - Reduced visual clutter with single department badge
  - Filter-aware context for department-scoped users
  - Consistent badge display across all rating sections

---

### 04/14/2026 - Faculty Model Metadata

#### Created By Tracking
- **File**: `app/Models/Faculty.php`
- **Field**: `created_by` column (VARCHAR, nullable)
- **Purpose**: Store authenticated user name who created faculty record
- **Implementation**: Added to `$fillable` array in Faculty model

#### Controller Integration
- **File**: `app/Http/Controllers/data_management/FacultyController.php` (store function)
- **Logic**: Automatically captures `auth()->user()->name` on record creation

---

### 04/15/2026 - Comprehensive Audit Logging System

#### Files Modified/Created:
✅ `database/migrations/2026_04_14_185925_create_audit_logs_table.php`  
✅ `app/Models/AuditLogs.php`  
✅ `app/Http/Controllers/user_management/AuditLogsController.php`  
✅ `app/Providers/AppServiceProvider.php`  
✅ `routes/web.php`  
✅ `resources/views/content/audit-logs/audit-logs.blade.php`  
✅ `resources/views/layouts/sections/menu/verticalMenu.blade.php`  

#### Database Schema
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(255) -- User name who performed action
    action VARCHAR(255) -- Action type: login, logout, user_registration, faculty_created
    method VARCHAR(255) -- HTTP method: GET, POST, PUT, DELETE
    ipAddress VARCHAR(255) -- Client IP address
    userAgent LONGTEXT -- Browser/client user agent
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Tracked Activities
🔐 **User Registration**: Triggered on User::created event  
🔐 **Login Events**: Tracked via \Illuminate\Auth\Events\Login  
🔐 **Logout Events**: Tracked via \Illuminate\Auth\Events\Logout  
🔐 **Faculty Creation**: Tracked on Faculty::created event

#### Event Listeners (AppServiceProvider)
```php
User::created(function ($user) { /* Log user registration */ });
Event::listen(Login::class, function ($event) { /* Log login */ });
Event::listen(Logout::class, function ($event) { /* Log logout */ });
Faculty::created(function ($faculty) { /* Log faculty creation */ });
```

#### Frontend Interface
✅ Search by user name  
✅ Filter by action type (Login, Logout, Faculty Created, User Registration)  
✅ Date range filters (Today, Week, Month, All Time)  
✅ Per-page controls (10, 20, 50, 100 entries)  
✅ Responsive data table with color-coded badges  
✅ Export functionality  
✅ Auto-refresh capability  
✅ Keyboard shortcuts (Ctrl+F search, Ctrl+R refresh)

#### Access Control
🔒 **Admin Only**: Route protected with `middleware('admin')`  
🔒 **Menu Visibility**: Only visible to users with role === 'Admin'  
🔒 **Route Name**: `um.audit-logs`  
🔒 **URL**: `/audit-logs/audit-logs`

#### Controller Methods
- `logActivity($user, $action, $method, $ipAddress, $userAgent)`: Creates audit log record
- `index(Request $request)`: Displays logs with search, filtering, and pagination

#### Filtering Capabilities
- **Search**: Filter logs by user name
- **Action Type**: Filter by Login, Logout, Faculty Creation, User Registration
- **Date Range**: Today, This Week, This Month, All Time
- **Pagination**: 10, 20, 50, 100 entries per page

---

## Architecture Overview

### MVC Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── dashboard/          # Dashboard and reports
│   │   ├── data_management/    # Faculty, courses, schedules
│   │   ├── user_management/    # Users and audit logs
│   │   └── msauth/             # Microsoft OAuth
│   ├── Middleware/             # Custom middleware (AccessLevel)
│   └── Requests/               # Form requests/validation
├── Models/                      # Eloquent models
├── Imports/                     # Excel import handlers
├── Providers/                   # Service providers
└── Observers/                   # Model observers
```

### Key Design Patterns

1. **Event-Driven Logging**: Uses Laravel events for automatic audit trail
2. **Role-Based Access Control**: Middleware checks for admin/access levels
3. **Component-Based Views**: Reusable Blade partials for maintainability
4. **Import Pipeline**: Multi-step validation for bulk data imports
5. **API Integration**: External MCU HRNet API calls for employee lookup

---

## Database Schema

### Core Tables

#### `users`
- User accounts with role and access level management
- Fields: name, email, password, role, access_level, department

#### `faculties`
- Faculty records linked to users
- Fields: user_id, employee_no, department, job_title, created_by

#### `courses`
- Course definitions
- Fields: class_code, subject_code

#### `faculty_courses`
- Faculty-Course assignments
- Fields: faculty_id, course_id, section, academic_year, semester

#### `schedules`
- Class schedule information
- Fields: faculty_course_id, day, time, academic_year, semester, status

#### `evaluations`
- Evaluation form records
- Fields: faculty_id, form_link, academic_year, semester, is_active

#### `evaluation_responses`
- Student responses to evaluations
- Fields: evaluation_id, schedule_id, effectiveness_rating, feedback_comments, ip_address

#### `audit_logs`
- Activity tracking for compliance
- Fields: name, action, method, ipAddress, userAgent, created_at

---

## External Integrations

### Microsoft OAuth
- **Purpose**: Single Sign-On (SSO) integration
- **Provider**: Microsoft Azure
- **Endpoints**:
  - `/auth/microsoft/redirect` - Initiate OAuth flow
  - `/auth/microsoft/callback` - Handle OAuth callback
- **Config**: `config/services.php`

### MCU HRNet API
- **Endpoint**: https://admin.mcu.edu.ph/MCU/HRNet/FindEmployee.rs.php
- **Method**: POST (form-encoded)
- **Parameters**:
  - `C=jsrs1` - Session indicator
  - `F=myRSList` - Function identifier
  - `P0=[surname||5]` - Search pattern
- **Authentication**: PHPSESSID and mfa_session cookies
- **Response**: HTML textarea with "employee_no|name" format

⚠️ **Important**: Cookies expire daily and require manual refresh from browser DevTools

---

## Troubleshooting

### Common Issues & Solutions

#### 1. MCU HRNet API Integration Fails
**Symptom**: searchEmployeeNo returns empty results or "session expired" error

**Solution**:
```bash
1. Open https://admin.mcu.edu.ph/MCU/HRNet/HRFindEmployee.html.php in browser
2. Login with MCU credentials
3. Open DevTools → Network tab
4. Perform a search
5. Find request headers for cookies: PHPSESSID, mfa_session
6. Update .env file:
   MCU_PHPSESSID=<new_value>
   MCU_MFA_SESSION=<new_value>
7. Test searchEmployeeNo again
```

#### 2. ImportAll Fails with "Faculty Does Not Have Course"
**Symptom**: Evaluation creation error despite successful faculty-course import

**Solution**:
- Ensure academic year format is normalized: "2025-2026" (not "2025 - 2026")
- Check that FacultyCourse records exist with matching course_id and section
- Verify semester format matches enum: "1st", "2nd", "Summer"

#### 3. Audit Logs Not Appearing
**Symptom**: logActivity called but no records in audit_logs table

**Troubleshooting Steps**:
1. Verify event listeners registered in AppServiceProvider
2. Check that User::created event is firing on registration
3. Confirm AuditLogsController is properly imported
4. Check database for audit_logs table existence: `SHOW TABLES LIKE 'audit_logs';`
5. Review Laravel logs: `storage/logs/`

#### 4. Permission Denied on Audit Logs
**Symptom**: 403/404 error accessing audit logs

**Solution**:
- Verify user has role === 'Admin'
- Check route middleware: `middleware('admin')`
- Confirm menu visibility: `$canAccessAuditLogs = $isAdmin;`
- Test direct URL: `/audit-logs/audit-logs`

#### 5. Large Import Performance Issues
**Symptom**: ImportAll times out with large datasets

**Solutions**:
- Increase PHP timeout: `php_value max_execution_time 300`
- Use batch processing in separate chunks
- Index database columns: `employee_no`, `course_id`, `facultyCourse_id`
- Consider splitting imports into smaller files

---

## Important Reminders

### Before Production Deployment

⚠️ **Session Cookies Expiration**
- MCU HRNet cookies expire daily
- Document process for admin to refresh cookies
- Set calendar reminder for regular cookie refresh

⚠️ **Database Backups**
- Regular backups before bulk imports
- Test restore procedures
- Backup location: `database/backup/`

⚠️ **Access Control**
- Verify all role-based access controls are working
- Test middleware with different user roles
- Audit admin user count and remove unnecessary admins

⚠️ **Performance Optimization**
- Index frequently searched columns
- Monitor audit_logs table growth
- Implement log retention policy

⚠️ **Email Configuration**
- Ensure SMTP credentials in `.env` are correct
- Test email sending for notifications
- Verify FROM address is recognized

---

## Performance Optimization Tips

1. **Database Indexing**
   - Index `audit_logs.created_at` for date range queries
   - Index `faculties.employee_no` for uniqueness
   - Index `faculty_courses.academic_year` for filtering

2. **Query Optimization**
   - Use pagination for large datasets (already implemented)
   - Eager load relationships in controllers
   - Consider query caching for frequently accessed data

3. **Frontend Optimization**
   - Minify CSS/JavaScript in production
   - Implement browser caching headers
   - Use CDN for static assets

---

## Support & Contact

For issues or questions:
1. Check Troubleshooting section above
2. Review changes.txt for recent modifications
3. Contact system administrator
4. Check Laravel logs: `storage/logs/laravel.log`

---

## License

This project is proprietary and confidential.

---

**Last Updated**: April 15, 2026, review changes.txt
**Project Version**: 1.0.0
