<?php
global $cotadb, $conn, $cota_constants;

require_once $cota_constants->COTA_APP_INCLUDES . 'database-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'format-family-listing.php';
require_once $cota_constants->COTA_APP_INCLUDES . 'settings.php';

// Echo page header
echo cota_page_header();

// Dump out remainder of import page. 
?>
    <div class="cota-add-entry-container">

    <script>
        function cota_add_member() {
            const membersDiv = document.getElementById("members");
            const memberCount = membersDiv.children.length;

            if (memberCount < 7) {
                const newMember = document.createElement("div");
                newMember.innerHTML = `

                <label >First Name</label>
                <input type="text" name="members[first_name][]" style="text-transform:capitalize;" required>
                <label for="members[last_name][]">Last Name (if different than family name)</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx" ><br>
                <label for="members[email][]">Email</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">Birthday</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">Anniversary of Baptism</label>
                <input type="text" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br><br><br>
                `;
                membersDiv.appendChild(newMember);
            } else {
                alert("Maximum of 7 members allowed. Please send us a note if you wish to add additional family members.");
            }
        }
    </script>

    <h2 >Add Family Entry</h2>
    <form class="cota-family-entry" action="add-family.php" method="post">
        <label>Family Name</label>
        <input type="text" name="familyname" style="text-transform:capitalize;" required>
        <label>Address</label>
        <input type="text" name="address"style="text-transform:capitalize;">
        <label>Address 2</label>
        <input type="text" name="address2" style="text-transform:capitalize;">
        <label>City</label>
        <input type="text" name="city" style="text-transform:capitalize;">
        <label>State</label>
        <input type="text" name="state" value="PA" maxlength="2" style="text-transform:uppercase">
        <label>Zip Code</label>
        <input type="text" name="zip" placeholder="xxxxx-xxxx"><br>
        <label>Home Phone</label>
        <input type="text" name="homephone" placeholder="xxx-xxx-xxxx"><br>
        <label>Anniversary of Marriage</label>
        <input type="text" name="annday" title="Wedding anniversary of primary family members." placeholder="mm/dd/yyyy"><br>

        <h3>Family Members</h3>
        <p>Enter the primary family member first, then add additional family members and information as desired.</p>
        <div id="members">
            <div>
                <label >Name</label>
                <input type="text" name="members[first_name][]" style="text-transform:capitalize;" placeholder="First" required>
                <label for="members[last_name][]">Last (only needed if different from family name)</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;" placeholder="Last"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx"><br>
                <label for="members[email][]">Email</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">Birthday</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd/yyyy"><br>
                <label for="members[baptism][]">Anniversary of Baptism</label>
                <input type="text" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd/yyyy"><br><br><br>
            </div>
        </div>
        <button class="cota-add-another" type="button" onclick="cota_add_member()">Add Another Family Member</button>
        <br><br>
        <button class="cota-submit-family" type="submit">Submit Family Update</button>
    </form>


</body>
</html>