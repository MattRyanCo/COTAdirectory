<?php
// echo nl2br(__FILE__ . ' loaded' . PHP_EOL);
class COTA_Family_Directory_App
{
    const maxFamilyMembers = 10;

    public function cota_render()
    {
        echo $this->cota_get_header();
        echo $this->cota_get_body();
    }

    private function cota_get_header()
    {
        return '<!DOCTYPE html>';
    }

    private function cota_get_body()
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
                <li><a href="/app-includes/cota-import.php">Import CSV Data</a></li>
                <li><a href="/app-includes/cota-export.php">Export Directory as CSV</a></li>
                <li><a href="/app-includes/cota-export-sample.php" target="_blank">Export Sample Directory as CSV</a></li>
                <li><a href="/app-includes/cota-display.php">Display Formatted Directory on Screen</a></li>
                <li><a href="/app-includes/cota-print-booklet.php">cota-print-booklet - Download</a></li>
                <li><a href="/app-includes/cota-print-booklet-to-pdf.php" target="_blank"><strong>cota-print-booklet-to-pdf</strong></a></li>
                <li><a href="/app-includes/cota-build-booklet.php" target="_blank">cota-build-booklet - Build Booklet Sample for Print</a></li>
                <li><a href="/app-includes/cota-add-family-form.php">Add New Family</a></li>
                <li><a href="/app-includes/cota-search-edit.php">Search & Edit Family</a></li>
                <li><a href="/app-includes/cota-search-delete.php">Delete Family or Family Member</a></li>
                <li><a href="/app-includes/cota-upcoming-anniversary-dates.php">Display/Print Upcoming Anniversaries</a></li>
                <li><a href="https://docs.google.com/forms/d/e/1FAIpQLSd9ZMiaeO6btCJo2BQ7lgBlOYsJwNBC4aPLRYdr4m90pwN7wA/viewform?usp=header" target="_blank">Google Form Based Family Entry</a></li>
                <li><a href="https://docs.google.com/spreadsheets/d/1anupShYGmySUjrA16yGC5HQ3uucfi6HdMm-CujOqHxc/edit?usp=sharing" target="_blank">Google Form Sheet</a></li>
                <li><a href="/app-includes/cota-reset-db.php" style="color: red;">⚠️ Reset Database ⚠️</a></li>
            </ul>
            </nav>
        </body>
        </html>';
    }

    private function cota_get_head_only()
    {
        return '
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Family Directory Management</title>
            <!-- <link rel="icon" type="image/x-icon" href="/app-assets/images/favicon.ico"> -->
            <link rel="icon" type="image/x-icon" href="/app-assets/images/favicon-white.ico">
            <link rel="stylesheet" href="/app-assets/css/styles.css">
        </head>';
    }
}