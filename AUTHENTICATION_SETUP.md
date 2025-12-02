# HTTP Basic Authentication Setup

This application includes HTTP Basic Authentication to protect access to the site. When enabled, visitors will be prompted for a username and password before they can access any pages.

## How It Works

The authentication system:
- Automatically disables on Windows (local development)
- Uses environment variables for credentials (secure)
- Works with both Apache and Nginx servers
- Can be enabled/disabled via environment variable

## Setup Instructions

### 1. Configure Environment Variables

Add the following variables to your `.env` file (create it if it doesn't exist in the root directory):

```env
# Enable/Disable Authentication
# Set to 'true' to enable, 'false' to disable
# Authentication is automatically disabled on Windows
AUTH_ENABLED=true

# Authentication Credentials
# These are the username and password required to access the site
AUTH_USER=your_username
AUTH_PASSWORD=your_secure_password

# Optional: Custom realm name (shown in browser dialog)
# Defaults to "COTA Family Directory" if not set
AUTH_REALM=COTA Family Directory
```

### 2. Set Strong Credentials

**Important**: Use strong, unique credentials for production:
- Username: Choose something unique (not "admin" or obvious names)
- Password: Use a strong password (at least 12 characters, mix of letters, numbers, and symbols)

### 3. File Permissions (Gridpane/Linux)

On your Gridpane server, ensure the `.env` file has restricted permissions:

```bash
chmod 600 .env
```

This ensures only the file owner can read/write the file.

### 4. Testing

1. Visit your site - you should see a browser authentication dialog
2. Enter the username and password you configured
3. If correct, you'll be granted access to the site
4. If incorrect, you'll see a 401 Unauthorized page

## Disabling Authentication

To temporarily disable authentication (not recommended for production):

```env
AUTH_ENABLED=false
```

Or simply remove/comment out the `AUTH_USER` and `AUTH_PASSWORD` variables.

## Local Development

Authentication is **automatically disabled** when running on Windows, so you won't be prompted during local development. This allows you to work without authentication locally while keeping it enabled on production.

## Security Notes

- **Never commit your `.env` file** to version control (it's already in `.gitignore`)
- Use different credentials for each environment (staging, production)
- Change passwords regularly
- Consider using a password manager to generate and store strong passwords
- HTTP Basic Authentication sends credentials in base64 encoding (not encrypted). For additional security, ensure your site uses HTTPS/SSL

## Troubleshooting

### Authentication not working?

1. **Check environment variables**: Ensure `AUTH_USER` and `AUTH_PASSWORD` are set in your `.env` file
2. **Verify `.env` is loaded**: Check that `bootstrap.php` successfully loads the `.env` file
3. **Check server configuration**: Ensure your web server (Apache/Nginx) is properly configured
4. **Clear browser cache**: Sometimes browsers cache authentication failures

### Getting 401 errors even with correct credentials?

- Verify the credentials in your `.env` file match exactly (case-sensitive)
- Check for extra spaces or special characters
- Ensure the `.env` file is in the root directory of your application

## Gridpane-Specific Notes

Gridpane servers typically use Nginx. The authentication system handles both Apache and Nginx automatically. If you encounter issues:

1. Ensure your Gridpane site has SSL/HTTPS enabled (recommended)
2. Check Gridpane's environment variable configuration if you're setting variables through their panel
3. Verify file permissions on the `.env` file

