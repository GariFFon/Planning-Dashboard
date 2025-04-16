# Event Dashboard

A modern, responsive web application for managing events and tasks with an elegant user interface.

![Event Dashboard](https://via.placeholder.com/1200x600?text=Event+Dashboard)

## Features

- **User Authentication**: Secure registration and login system
- **Event Management**: Create, view and manage events with customizable details
- **Task Tracking**: Organize tasks with priorities, deadlines, and completion tracking
- **Visual Dashboard**: Intuitive interface to monitor events and tasks
- **Reminders**: Stay updated on upcoming events and pending tasks
- **Modern UI**: Beautiful dark mode interface with glassmorphism and gradient effects
- **Responsive Design**: Works seamlessly across desktop and mobile devices

## Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**:
  - HTML/CSS
  - TailwindCSS for styling
  - GSAP for animations
  - Framer Motion for modern UI effects

## Installation

### Prerequisites

- XAMPP (or similar PHP development environment)
- MySQL database
- Web browser

### Setup Instructions

1. **Clone the repository**

```bash
git clone https://github.com/yourusername/event_dashboard.git
```

2. **Place in XAMPP htdocs folder**

Move the project folder to your XAMPP htdocs directory:
```
/Applications/XAMPP/xamppfiles/htdocs/
```

3. **Start XAMPP services**

Start the Apache and MySQL services from the XAMPP control panel.

4. **Create the database**

Navigate to http://localhost/phpmyadmin/ and create a new database named `event_dashboard`.

Alternatively, run the database setup script:
```
http://localhost/event_dashboard/db/setup.php
```

5. **Configure database connection**

Open `/includes/db_connect.php` and update the database credentials if necessary:

```php
$servername = "localhost";
$username = "root";  // Your MySQL username
$password = "";      // Your MySQL password
$dbname = "event_dashboard";
```

6. **Access the application**

Open your web browser and navigate to:
```
http://localhost/event_dashboard/
```

## Database Schema

The application uses 4 main tables:

- **users**: Stores user account information
- **events**: Manages event details and metadata
- **tasks**: Tracks tasks, their status, priority and associations
- **participants**: Links users to events they're attending

## Usage

### Registration & Login

1. Navigate to the homepage and click "Register" to create a new account.
2. Fill in your details and submit the form.
3. Use your credentials to log in to the dashboard.

### Creating Events

1. From the dashboard, click "Create Event" in the navigation bar.
2. Fill in event details including title, description, location, dates, and times.
3. Select a color for easy visual identification.
4. Submit the form to create your event.

### Managing Tasks

1. From the dashboard, click "Create Task" in the navigation bar.
2. Fill in task details including title, description, due date, and priority.
3. Optionally, associate the task with an existing event.
4. Use the task completion button on the dashboard to mark tasks as completed.

## Screenshots

![Login Page](https://via.placeholder.com/500x300?text=Login+Page)
![Dashboard](https://via.placeholder.com/500x300?text=Dashboard)
![Create Event](https://via.placeholder.com/500x300?text=Create+Event)
![Create Task](https://via.placeholder.com/500x300?text=Create+Task)

## Customization

### Styling

You can customize the application's appearance by modifying:

- **CSS Variables**: Edit the root variables in `css/styles.css` to change colors and effects
- **Tailwind Configuration**: Modify the Tailwind configuration in the script tags

### Adding Features

The modular structure makes it easy to add new features:

1. Create a new PHP file for your feature
2. Set up necessary database tables or modify existing ones
3. Link to your new feature from the navigation menu

## Security

- Passwords are hashed using PHP's password_hash() function
- Prepared statements prevent SQL injection
- Input sanitization is implemented throughout
- Sessions are used for secure user authentication

## Maintenance

### Database Backup

Regularly backup your MySQL database using phpMyAdmin or the MySQL command line:

```bash
mysqldump -u username -p event_dashboard > backup.sql
```

### Updates

1. Pull the latest code from the repository
2. Check for any database schema changes in `db/setup.php`
3. Apply any necessary updates to your database structure

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For support, email support@example.com or open an issue in the repository.