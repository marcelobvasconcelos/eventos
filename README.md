# Event Management System

A PHP-based web application for managing events, user requests, assets, and loans.

## Features

- Public event browsing and calendar view
- User registration and authentication
- Event request submission
- Asset management and loan system
- Admin dashboard for managing events, users, and requests
- Email notifications

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (recommended with XAMPP)
- Composer (for dependency management, if needed)

## Installation

1. Clone or download the project to your web server's document root (e.g., `htdocs` in XAMPP).

2. Ensure the project is in a directory accessible via web, e.g., `http://localhost/eventos`.

## Database Setup

1. Create a MySQL database named `eventos`.

2. Run the `schema.sql` file to create the necessary tables:
   - Open phpMyAdmin (if using XAMPP) or your MySQL client.
   - Select the `eventos` database.
   - Import or execute the `schema.sql` file.

   This will create the following tables:
   - users
   - events
   - event_requests
   - assets
   - loans
   - notifications

## Configuration

### Database Configuration

The database connection is configured in `config/database.php`. Default settings assume:
- Host: localhost
- Database: eventos
- User: root
- Password: (empty)

Update these values if your MySQL setup differs.

### SMTP Configuration

For email notifications, configure `config/email.php` with your SMTP settings:

```php
return [
    'host' => 'your_smtp_host.com',
    'port' => 587, // or 465 for SSL
    'username' => 'your_email@domain.com',
    'password' => 'your_email_password',
    'from_email' => 'noreply@yourdomain.com',
    'from_name' => 'Eventos System'
];
```

Replace the placeholders with your actual SMTP server details.

## Running the Application

1. Start your web server (Apache) and MySQL.

2. Open your browser and navigate to `http://localhost/eventos` (adjust path as needed).

3. The application will load the public events page by default.

## Usage

- **Public Users**: Browse events, view calendar, register/login.
- **Registered Users**: Submit event requests, view/manage assets and loans.
- **Admins**: Access admin dashboard to approve events, manage users, and oversee the system.

## File Structure

- `controllers/`: Application controllers
- `models/`: Data models
- `views/`: View templates
- `config/`: Configuration files
- `lib/`: Libraries (e.g., PHPMailer)
- `public/`: Static assets (CSS, JS, etc.)

## Security Notes

- Ensure proper file permissions.
- Use HTTPS in production.
- Regularly update dependencies.
- Validate and sanitize all user inputs.

## Troubleshooting

- If you encounter database connection errors, verify your MySQL credentials in `config/database.php`.
- For email issues, check SMTP settings and ensure your server allows outgoing connections on the specified port.
- Enable PHP error reporting for debugging: add `ini_set('display_errors', 1);` to `index.php` temporarily.