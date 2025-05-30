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

                <label for="members[first_name][]">First Name</label>
                <input type="text" id="members[first_name][]" name="members[first_name][]" required>
                <label for="members[last_name][]">Last Name</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]"><br>
                <label for="members[email][]">Email</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">Birthday</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">Anniversary of Baptism</label>
                <input type="text" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br><br><br>

                `;
                membersDiv.appendChild(newMember);
            } else {
                alert("Maximum of 7 members allowed. Please Add A Note if you wish to add additional family members.");
            }
        }
    </script>
</head>
<body>
    <h2 >Add Family Entry</h2>
    <form class="cota-family-entry" action="cota-cota-add-family.php" method="post">
        <label>Family Name:</label>
        <input type="text" name="familyname" required>
        <label>Address</label>
        <input type="text" name="address">
        <label>Address Line 2</label>
        <input type="text" name="address2">
        <label>City</label>
        <input type="text" name="city">
        <label>State</label>
        <input type="text" name="state">
        <label>Zip Code</label>
        <input type="text" name="zip">
        <label>Home Phone</label>
        <input type="text" name="homephone">
        <label>Anniversary of Marriage</label>
        <input type="text" name="annday" title="Wedding anniversary of primary family members." placeholder="mm/dd"><br>

        <h3>Family Members</h3>
        <div id="members">
            <!-- <div> -->
                <label >First Name</label>
                <input type="text" name="members[first_name][]" required>
                <label for="members[last_name][]">Last Name <smaller>(if different)</smaller></label>
                <input type="text" id="members[last_name][]" name="members[last_name][]"><br>
                <label for="members[cell_phone][]">Cell Phone</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]"><br>
                <label for="members[email][]">Email</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">Birthday</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">Anniversary of Baptism</label>
                <input type="text" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br><br><br>
            <!-- </div> -->
        </div>
        <button class="cota-add-another" type="button" onclick="cota_add_member()">Add Another Member</button>
        <br><br>
        <button class="submit" type="submit">Submit Updates</button>
    </form>

    <br><br><button class="main_menu" type="button" ><a href='index.php'>Return to Main Menu</a></button>

</body>
</html>