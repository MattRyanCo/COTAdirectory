<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Family Entry</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function addMember() {
            const membersDiv = document.getElementById("members");
            const memberCount = membersDiv.children.length;

            if (memberCount < 7) {
                const newMember = document.createElement("div");
                newMember.innerHTML = `

                <label for="members[first_name][]">First Name:</label>
                <input type="text" id="members[first_name][]" name="members[first_name][]" required>
                <label for="members[last_name][]">Last Name:</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]"><br>
                <label for="members[cell_phone][]">Cell Phone:</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]"><br>
                <label for="members[email][]">email:</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">annday of Birth:</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">annday of Baptism:</label>
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
    <h2>Add Family Entry</h2>
    <form action="add_family.php" method="post">
        <label>Family Name:</label>
        <input type="text" name="familyname" required><br>

        <label>Address:</label>
        <input type="text" name="address"><br>
        <label>City:</label>
        <input type="text" name="city"><br>
        <label>State:</label>
        <input type="text" name="state"><br>
        <label>Zip Code:</label>
        <input type="text" name="zip"><br>
        <label>Home Phone:</label>
        <input type="text" name="homephone"><br>
        <label>annday of Marriage:</label>
        <input type="text" name="annday" title="Wedding annday of primary family members." placeholder="mm/dd"><br>

        <h3>Members</h3>
        <div id="members">
            <div>
                <label for="members[first_name][]">First Name:</label>
                <input type="text" id="members[first_name][]" name="members[first_name][]" required>
                <label for="members[last_name][]">Last Name: (if different from Family name)</label>
                <input type="text" id="members[last_name][]" name="members[last_name][]"><br>
                <label for="members[cell_phone][]">Cell Phone:</label>
                <input type="text" id="members[cell_phone][]" name="members[cell_phone][]"><br>
                <label for="members[email][]">email:</label>
                <input type="email" id="members[email][]" name="members[email][]"><br>
                <label for="members[birthday][]">annday of Birth:</label>
                <input type="text" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd"><br>
                <label for="members[baptism][]">annday of Baptism:</label>
                <input type="text" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd"><br><br><br>
            </div>
        </div>

        <button type="button" onclick="addMember()">Add Another Member</button>
        <br><br>
        <button type="submit">Submit</button>
    </form>

    <br><br><p><a href='index.php'>Return to main menu</a></p>
</body>
</html>