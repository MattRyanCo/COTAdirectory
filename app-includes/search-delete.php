<?php
/**
 * 
 */
require_once '../app-includes/settings.php';

// Echo header
echo cota_page_header();

// Dump out remainder of import page. 
?>
    <h2>Search / Edit / Delete Family</h2>
    <form class="cota-search" action="../app-includes/delete-family.php" method="get">
        <label>Enter Family Name:</label>
        <input type="text" name="familyname" required>
        <button type="submit">Search Family to Delete</button>
    </form>
</body>
</html>