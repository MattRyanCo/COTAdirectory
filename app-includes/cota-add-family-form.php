<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Family Entry</title>
    <link rel="stylesheet" href="../app-assets/css/styles.css">
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
                <input type="tel" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx" pattern="\d{3}-\d{3}-\d{4}" title="Format: xxx-xxx-xxxx"><br>
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
</head>
<body>
    <h2 >Add Family Entry</h2>
    <form class="cota-family-entry" action="cota-add-family.php" method="post">
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
        <input type="text" name="zip" placeholder="xxxxx-xxxx" title="Format: xxxxx-xxxx"<br>
        <label>Home Phone</label>
        <input type="tel" name="homephone" placeholder="xxx-xxx-xxxx" title="Format: xxx-xxx-xxxx">
        <label>Anniversary of Marriage</label>
        <input type="text" name="annday" title="Wedding anniversary of primary family members." placeholder="mm/dd/yyyy"><br>

        <h3>Family Members</h3>
        <p>Enter the primary family member first, then add additional family members and information as desired.</p>
        <div id="members">
            <div>
                <label >Name</label>
                <input type="text" name="members[first_name][]" style="text-transform:capitalize;" placeholder="First"required>
                <label for="members[last_name][]">Last (only needed if different from family name)</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;" placeholder="Last"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="tel" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx" title="Format: xxx-xxx-xxxx"><br>
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

    <button class="main-menu-return" type="button" ><a href='index.php'>Return to Main Menu</a></button>

</body>
</html>