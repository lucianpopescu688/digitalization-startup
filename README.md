# Digital Archive Management System

A comprehensive web application for digitalization startups that manage old format archives (VHS, Betamax, DV Cassettes).

## Features

### Authentication & Security
- User registration and login system
- Session-based authentication
- Role-based access control (Admin, Manager, User)
- Password hashing and security

### Video Management
- Video upload with file validation
- Responsive video grid display
- Advanced search and filtering
- Video editing capabilities
- Modal video viewer
- Delete confirmation dialogs

### User Management
- Personal account management
- Company-based organization
- Admin user management interface
- Role assignment and permissions

### Additional Features
- "The Box" promotional page
- Contact form with validation
- Professional responsive design
- File upload progress tracking
- Error handling and user feedback

## Technology Stack

- **Backend**: PHP with MVC architecture
- **Database**: MySQL with proper relationships
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with flexbox/grid
- **Security**: Password hashing, input validation, session management

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/lucianpopescu688/digitalization-startup.git
   cd digitalization-startup
   ```

2. **Configure the database**
   - Update database settings in `config/config.php`
   - Default settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'digitalization_startup');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

3. **Set up the database**
   - Visit `http://yoursite.com/setup/` to run the automated setup
   - Or manually import `database/schema.sql` into your MySQL database

4. **Configure file permissions**
   ```bash
   chmod 755 public/uploads/
   ```

5. **Access the application**
   - Visit your website URL
   - Login with default admin account: `admin` / `admin123`

## Default Login

- **Username**: admin
- **Password**: admin123

## File Structure

```
digitalization-startup/
├── config/
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection class
├── controllers/
│   ├── get-video.php       # Video retrieval API
│   └── delete-video.php    # Video deletion API
├── database/
│   └── schema.sql          # Database schema
├── includes/
│   └── functions.php       # Helper functions
├── models/
│   ├── User.php           # User model
│   ├── Company.php        # Company model
│   ├── Video.php          # Video model
│   └── Session.php        # Session model
├── public/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── app.js         # JavaScript functionality
│   └── uploads/           # File upload directory
├── setup/
│   └── index.php          # Database setup script
└── views/
    ├── auth/
    │   ├── login.php      # Login page
    │   ├── register.php   # Registration page
    │   └── logout.php     # Logout handler
    ├── admin/
    │   ├── users.php      # User management
    │   └── companies.php  # Company management
    ├── pages/
    │   ├── the-box.php    # Promotional page
    │   └── contact.php    # Contact form
    ├── dashboard.php      # Main dashboard
    ├── upload.php         # Video upload
    ├── account.php        # User account management
    └── edit-video.php     # Video editing
```

## Database Schema

The application uses four main tables:

- **companies**: Organization management
- **users**: User accounts with role-based permissions
- **videos**: Video metadata and file information
- **sessions**: Session management for authentication

## Security Features

- Password hashing using PHP's password_hash()
- Input sanitization and validation
- SQL injection prevention with prepared statements
- Session-based authentication
- Role-based access control
- File upload validation and restrictions

## Supported Video Formats

- MP4, AVI, MOV, WMV, FLV, MKV
- Maximum file size: 500MB per video
- Automatic file validation and metadata extraction

## User Roles

- **Admin**: Full system access, user/company management
- **Manager**: Video management and reporting access
- **User**: Upload and manage own videos

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Minimum 1GB storage space
- Modern web browser

## Support

For support and questions:
- Email: support@digitalarchive.com
- Phone: +1 (555) 123-4568
- Business Hours: Monday-Friday, 9 AM - 6 PM EST

## License

© 2024 Digital Archive Management System. All rights reserved.

## Contributing

This is a production-ready application for digitalization startups. For enterprise features or custom implementations, please contact our sales team.
