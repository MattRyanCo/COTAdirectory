<?php
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);
class FamilyDirectoryApp
{
    const maxFamilyMembers = 10;

    public function render()
    {
        echo $this->getHeader();
        echo $this->getBody();
    }

    private function getHeader()
    {
        return '<!DOCTYPE html>';
    }

    private function getBody()
    {
        return '
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Family Directory Management</title>
            <!-- <link rel="icon" type="image/x-icon" href="/app-assets/images/favicon.ico"> -->
            <link rel="icon" type="image/x-icon" href="/app-assets/images/favicon-white.ico">
            <link rel="stylesheet" href="/app-assets/css/styles.css">
        </head>
        <body>
            <h2>Church of the Ascension, Parkesburg<br>Family Directory Management</h2>
            <nav>
            <ul>
                <li><a href="/app-includes/import.php">Import CSV Data</a></li>
                <li><a href="/app-includes/export.php">Export Directory as CSV</a></li>
                <li><a href="/app-includes/display.php">Display Formatted Directory</a></li>
                <li><a href="/app-includes/print_booklet.php">Print Formatted Directory</a></li>
                <li><a href="/app-includes/search.php">Search & Edit Family</a></li>
                <li><a href="/app-includes/add_family_form.php">Add New Family</a></li>
                <li><a href="/app-includes/upcoming_anniversary_dates.php">Display/Print Upcoming Anniversaries</a></li>
                <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd9ZMiaeO6btCJo2BQ7lgBlOYsJwNBC4aPLRYdr4m90pwN7wA/viewform?usp=header">Form Based Family Entry</a></li>
                <li><a href="/app-includes/reset_db.php" style="color: red;">⚠️ Reset Database ⚠️</a></li>
            </ul>
            </nav>
        </body>
        </html>';
    }
}