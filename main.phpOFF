<?php
function showMenu() {
    echo "\n==============================\n";
    echo " Family Directory Management \n";
    echo "==============================\n";
    echo "1. Import CSV Data\n";
    echo "2. Export Directory as CSV\n";
    echo "3. Search for a Family\n";
    echo "4. Update Family Information\n";
    echo "5. Add New Family\n";
    echo "6. Quit\n";
    echo "==============================\n";
}

// Loop until user chooses to quit
while (true) {
    showMenu();
    $choice = readline("Select an option (1-6): ");

    switch ($choice) {
        case "1":
            echo "Importing CSV data...\n";
            require_once 'import.php';
            break;

        case "2":
            echo "Exporting directory as CSV...\n";
            require_once 'export.php';
            break;

        case "3":
            echo "Searching for a family...\n";
            require_once 'search.php';
            break;

        case "4":
            echo "Updating family information...\n";
            require_once 'edit_family.php';
            break;

        case "5":
            echo "Adding new family entry...\n";
            require_once 'form.php';
            break;

        case "6":
            echo "Exiting...\n";
            exit;

        default:
            echo "Invalid selection. Please choose a valid option.\n";
    }
}
?>