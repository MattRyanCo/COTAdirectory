# Member Authentication Setup

This application includes a database-backed member authentication system. Members can log in using their email address (which must exist in the members table) and a password they set on their first visit.

## How It Works

1. **Email Verification**: Users must have an email address in the `members` table
2. **First Visit**: When a member logs in for the first time, they're prompted to set a password
3. **Subsequent Visits**: Members use their email and password to log in
4. **Session-Based**: Authentication uses PHP sessions (no HTTP Basic Auth)
5. **Auto-Disable**: Authentication is automatically disabled on Windows (local development)

## Initial Setup

### 1. Create the Password Table

First, you need to create the `member_passwords` table. Visit this URL once:

```
https://your-site.com/app-includes/setup-member-auth-table.php
```

This will create the table needed to store password hashes. You only need to run this once.

### 2. Configure Environment Variables

Add the following to your `.env` file:

```env
# Enable/Disable Authentication
# Set to 'true' to enable, 'false' to disable
# Authentication is automatically disabled on Windows
AUTH_ENABLED=true
```

### 3. Ensure Members Have Email Addresses

Make sure all members who need to log in have email addresses in the `members` table. The email field is used as the username for login.

## User Flow

### First-Time User

1. User visits the site
2. They're redirected to `/app-includes/login.php`
3. User enters their email address (must exist in members table)
4. System detects they don't have a password yet
5. User is redirected to `/app-includes/setup-password.php`
6. User sets their password (minimum 8 characters)
7. User is automatically logged in and redirected to the site

### Returning User

1. User visits the site
2. They're redirected to `/app-includes/login.php` (if not authenticated)
3. User enters email and password
4. If correct, they're logged in and redirected to the page they were trying to access

### Logout

Users can log out by clicking the "Logout" link in the header (visible when authenticated).

## Database Structure

The system creates a `member_passwords` table with the following structure:

- `id` - Primary key
- `member_id` - Foreign key to `members.id`
- `email` - Email address (unique, indexed)
- `password_hash` - Bcrypt hash of the password
- `created_at` - Timestamp when password was created
- `updated_at` - Timestamp when password was last updated

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt (PASSWORD_DEFAULT)
- **Secure Comparison**: Uses `password_verify()` for password checking
- **Session Security**: Uses PHP sessions with proper session management
- **Email Validation**: Verifies email exists in members table before allowing password setup
- **Password Requirements**: Minimum 8 characters
- **Case-Insensitive Email**: Email matching is case-insensitive

## Files Created

- `app-includes/class-member-auth.php` - Authentication class
- `app-includes/login.php` - Login page
- `app-includes/setup-password.php` - Password setup page
- `app-includes/logout.php` - Logout handler
- `app-includes/setup-member-auth-table.php` - Database table creation script

## Modified Files

- `app-includes/bootstrap.php` - Now uses member authentication instead of HTTP Basic Auth
- `app-includes/helper-functions.php` - Added logout link to header when authenticated

## Troubleshooting

### "Email address not found in directory"

- Ensure the member's email address exists in the `members` table
- Email matching is case-insensitive
- Check for extra spaces or typos

### "Password must be at least 8 characters long"

- The password must be at least 8 characters
- There's no maximum length limit

### Authentication not working?

1. **Check environment variables**: Ensure `AUTH_ENABLED=true` in your `.env` file
2. **Verify table exists**: Make sure you've run `setup-member-auth-table.php`
3. **Check session configuration**: Ensure PHP sessions are working on your server
4. **Clear browser cookies**: Sometimes clearing cookies helps

### Can't access login page?

- The login page (`/app-includes/login.php`) is exempt from authentication
- If you're getting redirected in a loop, check your `.htaccess` or server configuration

## Disabling Authentication

To disable authentication:

1. Set `AUTH_ENABLED=false` in your `.env` file, OR
2. Remove/comment out the `AUTH_ENABLED` variable (defaults to enabled on Linux)

Authentication is automatically disabled on Windows for local development.

## Gridpane-Specific Notes

- Ensure your Gridpane site has SSL/HTTPS enabled (recommended for security)
- Sessions should work automatically with Gridpane's PHP configuration
- If you encounter session issues, check Gridpane's PHP session settings

## Migration from HTTP Basic Auth

If you previously used HTTP Basic Authentication:
- The old `class-http-auth.php` is no longer used
- The new system uses sessions instead of HTTP Basic Auth
- All existing HTTP Basic Auth credentials are ignored
- Each member must set up their own password

