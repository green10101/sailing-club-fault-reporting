<?php
// Application configuration settings

// Environment settings
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') ?: false);
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Database settings
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'sailing_club');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Email settings
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'noreply@sailingclub.com');
define('MAIL_TO', getenv('MAIL_TO') ?: 'bosun@sailingclub.com');

// Other application constants
define('REPORT_STATUS_PENDING', 'pending');
define('REPORT_STATUS_IN_PROGRESS', 'in_progress');
define('REPORT_STATUS_COMPLETED', 'completed');
?>