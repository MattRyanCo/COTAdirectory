# COTA Family Directory

A simple and efficient family directory management system designed specifically for churches and religious organizations. This PHP-based web application helps manage member information, print directories, and track important dates like anniversaries and baptisms.

## Overview

COTA Family Directory is a comprehensive solution for church administrators to maintain and organize family information. The system provides an intuitive web interface for adding, editing, and managing family records, with powerful features for generating printed directories and tracking important member milestones.

## Features

- **Family Management**: Add, edit, and delete family records with detailed member information
- **Directory Printing**: Generate formatted directories in both PDF and RTF formats
- **Search & Filter**: Powerful search functionality to quickly find specific families
- **Data Import/Export**: Import family data from CSV files and export for backup or analysis
- **Anniversary Tracking**: Monitor upcoming wedding anniversaries and baptism dates
- **Flexible Display**: View individual families or browse the complete directory
- **Database Management**: Built-in tools for database maintenance and reset functionality
- **Responsive Design**: Clean, modern interface that works across devices

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Composer for dependency management

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/MattRyanCo/COTAdirectory.git
   cd COTAdirectory
   ```

2. Install dependencies using Composer:
   ```bash
   composer install
   ```

3. Configure your database connection by editing the database configuration files in the `app-includes/` directory.

4. Set up your web server to point to the project directory.

5. Access the application through your web browser and follow the setup instructions.

## Usage

### Adding Families
Navigate to the "Add Family" section to input new family information including:
- Family name and contact details
- Individual member information
- Important dates (anniversaries, baptisms)
- Address and phone information

### Generating Directories
Use the print functionality to create formatted directories:
- Choose between PDF or RTF output formats
- Customize the layout and information included
- Generate booklet-style directories for distribution

### Managing Data
- **Import**: Upload CSV files to bulk import family data
- **Export**: Download family information for backup or external use
- **Search**: Find specific families using various search criteria
- **Edit**: Update family information as needed

## File Structure

- [`index.php`](index.php) - Main application entry point
- [`app-includes/`](app-includes/) - Core application files and classes
- [`app-assets/`](app-assets/) - CSS, JavaScript, and image resources
- [`composer.json`](composer.json) - PHP dependencies and project configuration
- [`CHANGELOG.md`](CHANGELOG.md) - Detailed version history and changes
- [`LICENSE`](LICENSE) - Project license information
- [`SECURITY_FIXES.md`](SECURITY_FIXES.md) - Security-related updates and fixes

## Contributing

We welcome contributions to improve COTA Family Directory! Please feel free to:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

For detailed development information, see the [CHANGELOG.md](CHANGELOG.md) for recent updates and version history.

## License

This project is licensed under the GPL-2.0-or-later license. See the [LICENSE](LICENSE) file for details.

## Support

For questions, issues, or feature requests, please visit the [GitHub repository](https://github.com/MattRyanCo/COTAdirectory) and create an issue.

## Version

Current version: 3.1.6

For a complete list of changes and version history, see [CHANGELOG.md](CHANGELOG.md).
