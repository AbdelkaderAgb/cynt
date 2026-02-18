# CYN Tourism Management System

A comprehensive tourism management system for handling transfers, hotels, tours, invoicing, and more.

## Features

- **Transfer Management**: Book and manage airport transfers, city transfers
- **Hotel Management**: Hotel bookings, vouchers, and invoicing
- **Tour Management**: Tour bookings with guides and scheduling
- **Invoice & Receipt System**: Complete financial management
- **Partner Management**: Manage suppliers and customers
- **Vehicle & Driver Management**: Fleet management
- **Multi-language Support**: English and Turkish
- **User Management**: Role-based access control
- **Backup & Restore**: Automated backup system
- **Activity Logging**: Comprehensive audit trail

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

1. **Upload files** to your web server
2. **Create database** and import `database-schema-real.sql`
3. **Configure database** in `config.php`
4. **Set permissions**:
   ```bash
   chmod 755 logs/ backups/ uploads/
   chmod 644 config.php
   ```
5. **Access the application** at `https://your-domain.com/`

### Default Login
- Email: admin@cyntourism.com
- Password: (set during installation)

## Configuration

### Database Settings (`config.php`)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Security Settings
```php
define('DEBUG_MODE', false);        // Set to false in production
define('SESSION_LIFETIME', 7200);   // Session timeout in seconds
define('MAX_LOGIN_ATTEMPTS', 5);    // Max failed login attempts
define('LOCKOUT_DURATION', 900);    // Lockout duration in seconds
```

### Maintenance Mode
```php
define('MAINTENANCE_MODE', true);
define('MAINTENANCE_ALLOW_ADMIN', true); // Allow admin access during maintenance
```

## Directory Structure

```
/
├── assets/
│   ├── css/          # Stylesheets
│   └── js/           # JavaScript files
├── backups/          # Backup files (protected)
├── errors/           # Error pages (404, 403, 500, maintenance)
├── languages/        # Language files (en.php, tr.php)
├── logs/             # Log files (protected)
├── uploads/          # User uploads (protected)
│   ├── documents/
│   └── images/
├── auth.php          # Authentication class
├── Backup.php        # Backup functionality
├── config.php        # Configuration
├── database.php      # Database connection
├── FileUploader.php  # File upload handler
├── functions.php     # Helper functions
├── language.php      # Language system
├── Logger.php        # Logging system
├── Validator.php     # Form validation
└── .htaccess         # Apache configuration
```

## API Documentation

### Authentication

All API requests require authentication via session or API key.

### Common Endpoints

#### Get Dashboard Stats
```
GET /api/dashboard.php
```

#### Create Transfer
```
POST /api/transfers.php
Content-Type: application/json

{
    "pickup_location": "Airport",
    "dropoff_location": "Hotel",
    "pickup_date": "2024-01-15",
    "pickup_time": "14:00",
    "passengers": 2
}
```

## Form Validation

### Server-side Validation

```php
use Validator;

$validator = Validator::make($_POST, [
    'email' => 'required|email',
    'password' => 'required|min:8|password',
    'phone' => 'phone'
], [
    'email.required' => 'Email is required',
    'password.min' => 'Password must be at least 8 characters'
]);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### Client-side Validation

Add `data-validate` attribute to forms:

```html
<form data-validate>
    <input type="email" name="email" required data-validate="email">
    <input type="password" name="password" required data-min="8">
    <input type="tel" name="phone" data-validate="phone">
</form>
```

## Logging

### Log Levels
- `EMERGENCY` - System is unusable
- `ALERT` - Action must be taken immediately
- `CRITICAL` - Critical conditions
- `ERROR` - Error conditions
- `WARNING` - Warning conditions
- `NOTICE` - Normal but significant
- `INFO` - Informational
- `DEBUG` - Debug-level messages

### Usage

```php
Logger::error('Something went wrong', ['context' => 'details']);
Logger::info('User logged in', ['user_id' => 123]);
Logger::activity('created', 'transfer', $transferId);
```

## Backup & Restore

### Create Backup
```php
$backup = new Backup();
$result = $backup->createFullBackup();
```

### Restore Backup
```php
$backup = new Backup();
$result = $backup->restoreDatabase('backup_file.sql.zip');
```

### Automated Backups
Set up a cron job:
```bash
0 2 * * * /usr/bin/php /path/to/backup-cron.php
```

## File Uploads

### Secure Upload
```php
$uploader = new FileUploader();
$result = $uploader->upload($_FILES['document'], 'documents');

if ($result['success']) {
    $filename = $result['filename'];
    $url = $result['url'];
}
```

### Allowed File Types
- Images: jpg, jpeg, png, gif, svg, webp
- Documents: pdf, doc, docx, xls, xlsx, txt
- Archives: zip, rar, 7z

## Language System

### Add Translation
Edit `languages/en.php` or `languages/tr.php`:

```php
return [
    'new_key' => 'Translation text',
    'with_params' => 'Hello :name'
];
```

### Use Translation
```php
echo __('new_key');
echo __('with_params', ['name' => 'John']);
```

## Security

### CSRF Protection
All forms include CSRF token:
```php
echo csrf_field();
```

### XSS Protection
Always escape output:
```php
echo e($userInput);
```

### SQL Injection Protection
Use prepared statements:
```php
$result = Database::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
```

## Troubleshooting

### Common Issues

#### 500 Internal Server Error
- Check `logs/php-errors.log`
- Ensure `logs/` directory is writable
- Verify database connection

#### Session Issues
- Check PHP session configuration
- Ensure cookies are enabled
- Clear browser cookies

#### Upload Failures
- Check `uploads/` directory permissions
- Verify `upload_max_filesize` in php.ini
- Check available disk space

### Debug Mode
Enable debug mode in `config.php`:
```php
define('DEBUG_MODE', true);
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is proprietary software. All rights reserved.

## Support

For support, contact:
- Email: support@cyntourism.com
- Phone: +90 531 817 6770

## Changelog

### Version 2.0.0
- Added comprehensive logging system
- Added backup and restore functionality
- Added maintenance mode
- Improved form validation
- Added file upload security
- Added error pages (404, 403, 500)
- Multi-language support improvements
- Security enhancements

### Version 1.0.0
- Initial release
- Basic transfer, hotel, tour management
- Invoice and receipt system
- User management
# cynt
