# Changes Summary: User Details and Fault Reporter Information

## Overview

Updated the system to include user name and email fields in the users table, and to capture reporter contact information when a fault is reported.

## Database Changes

### Migrations Created

- **migrations/008_add_user_details.sql** - Adds `name` and `email` columns to users table
- **migrations/009_add_reporter_details.sql** - Adds `reporter_name` and `reporter_email` columns to reports table

### Database Schema Updates

- **users table**:
  - Added `name VARCHAR(255)` column
  - Updated `email VARCHAR(255)` column (already existed)
- **reports table**:
  - Added `reporter_name VARCHAR(255)` column
  - Added `reporter_email VARCHAR(255)` column

## Code Changes

### User Model (`src/Models/User.php`)

- Added `private $name` property
- Added `getName()` getter method
- Updated `getAllUsers()` to select name field
- Updated `getUserById()` to select name field
- Updated `createUser()` to accept name parameter: `createUser($username, $password, $name, $email, $role)`
- Updated `updateUser()` to accept name parameter: `updateUser($id, $username, $password, $name, $email, $role)`

### Report Model (`src/Models/Report.php`)

- Updated `create()` method to accept reporter details: `create($boatId, $faultDescription, $reporterName, $reporterEmail)`
- Now inserts `reporter_name` and `reporter_email` into the reports table

### Admin Controller (`src/Controllers/AdminController.php`)

- Updated `createUser()` to capture name from form
- Updated `updateUser()` to capture and save name field
- Both methods now pass name to User model methods

### Public Controller (`src/Controllers/PublicController.php`)

- Updated `submitReport()` to capture `reporter_name` and `reporter_email` from form
- Passes these values to the Report model's create method

### Views

#### User Management Views

- **src/Views/admin/users.php** - Added Name column to user list table
- **src/Views/admin/user_new.php** - Added Name input field to new user form
- **src/Views/admin/user_edit.php** - Added Name input field to edit user form

#### Fault Reporting

- **src/Views/public/report_form.php** - Added two new fields:
  - Reporter Name (auto-filled from `$_SESSION['user']['name']` if logged in)
  - Reporter Email (auto-filled from `$_SESSION['user']['email']` if logged in)
  - Both fields are required

#### Fault Dashboard

- **src/Views/bosun/dashboard.php** - Updated table to display:
  - "Reported By" column (shows reporter_name)
  - "Contact Email" column (shows reporter_email)

### Setup Script (`setup_db.php`)

- Added checks to conditionally add name, email (for users), and reporter_name, reporter_email (for reports) columns
- Ensures idempotent execution - safe to run multiple times

### Admin Creation Script (`create_admin.php`)

- Updated to include name field when creating admin user
- Creates admin with Name: "Administrator"
- Displays all user details including name when showing existing admin

## Usage

### Creating a New User (Admin Panel)

1. Admin navigates to User Management
2. Clicks "Add User"
3. Fills in: Username, Name, Password, Email, Role
4. Submits to create the user
5. Name and email are stored in the users table

### Reporting a Fault (Public Form)

1. User fills out the report form with:
   - Boat Name (dropdown)
   - Reporter Name (auto-filled if logged in, editable)
   - Reporter Email (auto-filled if logged in, editable)
   - Fault Description
2. Form validates that all fields are complete
3. Report is saved with reporter contact information

### Viewing Fault Reports (Bosun Dashboard)

1. Bosun logs in and views the Fault Reports page
2. Table displays reporter's name and contact email for each fault
3. Bosun can use this information to contact the person who reported the fault

## Admin User Details

- **Username:** admin
- **Name:** Administrator
- **Email:** admin@example.com
- **Password:** admin123
- **Role:** admin

## Testing the Changes

1. **Add a new user:**
   - Log in as admin
   - Go to User Management
   - Create a new bosun with name and email
   - Verify name appears in users list

2. **Report a fault:**
   - Public form now shows Name and Email fields
   - Fields auto-fill if you log in as a bosun first
   - Submit a fault and verify reporter info is saved

3. **View reports:**
   - Log in as bosun
   - View Fault Reports page
   - Verify reporter name and email columns display correctly

## Migration Application

Migrations are automatically applied by running `php setup_db.php` if columns don't already exist. This ensures the system remains compatible whether starting fresh or upgrading existing installations.
