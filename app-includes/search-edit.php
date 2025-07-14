<?php
/**
 * 
 */

global $cotadb, $conn, $cota_constants;
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Echo header
echo cota_page_header();

// Dump out remainder of import page.
?>
    <h2>Search / Edit Family</h2>
    <form class="cota-search" action="../app-includes/edit-family.php" method="get">
        <label>Enter Family Name:</label>
        <input type="text" name="familyname" required>
        <button type="submit">Search</button>
    </form>
</body>
</html>