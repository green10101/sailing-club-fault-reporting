# Sailing Club Fault Reporting Application

This project is a web application designed for reporting faults in a sailing club's fleet of hire boats. It allows users to submit fault reports, which can be managed by bosuns. The application includes features for public reporting, bosun management, and email notifications for repair jobs.

## Features

- **Public Reporting**: Users can report faults through a user-friendly form.
- **Bosun Management**: Bosuns can log in to manage reported faults, update statuses, and add notes.
- **Email Notifications**: Automatic email notifications are sent for repair job assignments and completions.

## Project Structure

```
sailing-club-fault-reporting
├── public
│   ├── index.php          # Entry point of the application
│   ├── report.php         # Handles fault report submissions
│   ├── thanks.php         # Thank you page after report submission
│   ├── login.php          # Login interface for bosuns
│   └── assets
│       ├── css
│       │   └── app.css    # Custom styles for the application
│       └── js
│           └── app.js     # JavaScript for client-side functionality
├── src
│   ├── Controllers
│   │   ├── PublicController.php  # Handles public actions
│   │   ├── BosunController.php    # Manages bosun actions
│   │   └── AuthController.php     # User authentication
│   ├── Models
│   │   ├── Report.php             # Represents a fault report
│   │   └── User.php               # Represents a user (bosun)
│   ├── Services
│   │   ├── MailService.php        # Handles email notifications
│   │   └── NotificationService.php # Manages notifications
│   ├── Views
│   │   ├── layouts
│   │   │   └── main.php           # Main layout template
│   │   ├── public
│   │   │   └── report_form.php    # HTML form for reporting faults
│   │   └── bosun
│   │       └── dashboard.php       # Dashboard for bosuns
│   └── config
│       ├── database.php           # Database connection settings
│       └── app.php                # Application configuration
├── sql
│   └── schema.sql                 # SQL schema for database tables
├── migrations
│   └── 001_create_reports.sql      # SQL migration for reports table
├── resources
│   └── emails
│       ├── repair_assigned.html    # Email template for assigned repairs
│       └── repair_completed.html    # Email template for completed repairs
├── tests
│   └── ExampleTest.php             # Example test case
├── composer.json                   # Composer configuration file
├── phpunit.xml                    # PHPUnit configuration file
├── .env.example                    # Template for environment variables
├── docker-compose.yml              # Docker configuration
└── README.md                       # Project documentation
```

## Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   cd sailing-club-fault-reporting
   ```

2. Install dependencies using Composer:
   ```
   composer install
   ```

3. Set up the database:
   - Create a new database and import the `sql/schema.sql` file.
   - Update the database connection settings in `src/config/database.php`.

4. Configure environment variables:
   - Copy `.env.example` to `.env` and update the values as needed.

5. Run the application:
   - Start a local server or use Docker to run the application.

## Usage

- Navigate to the application in your web browser.
- Users can report faults using the public form.
- Bosuns can log in to manage reports and receive notifications via email.

## Contributing

Contributions are welcome! Please submit a pull request or open an issue for any suggestions or improvements.