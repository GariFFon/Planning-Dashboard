# Event Planning Application - Project Structure

This document outlines the organization of the Event Planning application codebase.

## Directory Structure

```
Event Planning/
│
├── assets/                      # Static assets
│   ├── css/                     # CSS files
│   ├── images/                  # Image files
│   └── js/                      # JavaScript files
│
├── backend/                     # Backend code
│   ├── api/                     # API endpoints
│   │   ├── calendar-events.php  # Calendar-related endpoints
│   │   ├── dashboard-stats.php  # Dashboard statistics endpoints
│   │   ├── delete-event.php     # Event deletion endpoint
│   │   ├── get-profile.php      # User profile endpoint
│   │   ├── update-event.php     # Event update endpoint
│   │   └── update-profile.php   # Profile update endpoint
│   │
│   ├── includes/                # Backend includes
│   │   ├── auth.php             # Authentication functions
│   │   ├── config.php           # Configuration settings
│   │   ├── database.php         # Database connection functions
│   │   ├── event.php            # Event-related functions
│   │   └── utils.php            # Utility functions
│   │
│   ├── add-event.php            # Add event handler
│   ├── database_setup.sql       # Database setup script
│   ├── get-events.php           # Event retrieval handler
│   ├── login.php                # Login handler
│   ├── logout.php               # Logout handler
│   └── README.md                # Backend documentation
│
├── css/                         # Additional CSS files
│   └── style.css                # Main stylesheet
│
├── docs/                        # Documentation
│   └── README.md                # This document
│
├── js/                          # JavaScript files
│   └── main.js                  # Main JavaScript file
│
└── pages/                       # Frontend HTML pages
    ├── add-event.html           # Add event page
    ├── calendar.html            # Calendar view
    ├── events.html              # Events listing page
    ├── index.html               # Homepage
    ├── login.html               # Login page
    ├── profile.html             # User profile page
    └── settings.html            # Settings page
```

## File Organization

- **Frontend Pages**: Located in the `pages` directory, containing HTML files
- **Backend Code**: Located in the `backend` directory, containing PHP files
- **API Endpoints**: Located in the `backend/api` directory
- **Backend Includes**: Located in `backend/includes`, containing database and utility functions
- **Assets**: Located in the `assets` directory, containing CSS, JavaScript, and images
- **Additional CSS**: Located in the `css` directory
- **JavaScript**: Located in the `js` directory
- **Documentation**: Located in the `docs` directory

## Routing

Each page has its own HTML file in the `pages` directory, which calls the appropriate backend API endpoints as needed.

## Migration Notes

When making changes to the application:

1. Update all internal file references to reflect the directory structure
2. Update all asset references in HTML files (CSS, JS, images)
3. Update all API endpoint URLs in JavaScript files
4. Update all backend include/require statements to use the correct file paths

# Event Planning Dashboard - Backend Setup

This document provides instructions for setting up and using the backend of your Event Planning Dashboard.

## Database Setup

1. Import the database schema and sample data:
   - Open your MySQL client (phpMyAdmin, MySQL Workbench, command line, etc.)
   - Run the SQL script found in `backend/database_setup.sql`
   - This will create the necessary database, tables, and sample data

## Configuration

1. Configure your database connection:
   - Open `/backend/includes/config.php`
   - Update the database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS) to match your environment
   - Adjust other settings as needed (timezone, app URL, etc.)

## Default User Account

The system comes with a default administrator account for testing:

- Email: admin@example.com
- Password: admin123

## API Endpoints

### Authentication
- **Login**: `/backend/login.php` (POST)
  - Parameters: email, password, remember_me (optional)
  - Returns: User data on success

- **Logout**: `/backend/logout.php` (GET)
  - Destroys session and redirects to login page

### Events
- **Get Events**: `/backend/get-events.php` (GET)
  - Parameters: 
    - type: 'all', 'upcoming', 'date-range', 'stats'
    - event_id: (optional) Get a specific event
    - limit: (optional) Number of events to return for 'upcoming'
    - start_date, end_date: (optional) Date range for 'date-range'
  
- **Add Event**: `/backend/add-event.php` (POST)
  - Parameters: name, description, event_date, event_time, location, status (optional)
  - Returns: event_id on success

- **Update Event**: `/backend/api/update-event.php` (POST)
  - Parameters: event_id, name, description, event_date, event_time, location, status
  - Returns: Updated event data

- **Delete Event**: `/backend/api/delete-event.php` (POST)
  - Parameters: event_id
  - Returns: Success message

### User Profile
- **Get Profile**: `/backend/api/get-profile.php` (GET)
  - Returns: Current user profile data

- **Update Profile**: `/backend/api/update-profile.php` (POST)
  - Parameters: name, email, password (optional), current_password (required if changing password)
  - Returns: Updated user data

### Dashboard & Calendar
- **Dashboard Stats**: `/backend/api/dashboard-stats.php` (GET)
  - Returns: Event statistics and upcoming events

- **Calendar Events**: `/backend/api/calendar-events.php` (GET)
  - Parameters: month, year
  - Returns: Events organized by day for the calendar view

## Security Notes

1. All API endpoints (except login) require user authentication
2. Sensitive data is properly sanitized before database operations
3. Passwords are securely hashed using PHP's password_hash() function
4. Session cookies have the httponly flag set for security

## Extending The System

To add more functionality:

1. Create new class methods in the appropriate files in `/backend/includes/`
2. Create new API endpoints in `/backend/api/` that use these methods
3. Update existing methods or endpoints as needed

## Troubleshooting

- If you encounter "Access denied" errors, check your database credentials in config.php
- If sessions aren't working, ensure session_start() is being called and cookies are enabled
- For debugging, you can temporarily set error reporting to E_ALL in config.php