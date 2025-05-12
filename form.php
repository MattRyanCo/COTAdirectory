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
                    <label>First Name:</label>
                    <input type="text" name="members[first_name][]" required>
                    <label>Cell Phone:</label>
                    <input type="text" name="members[cell_phone][]">
                    <label>Email:</label>
                    <input type="email" name="members[email][]">
                    <label>Birthday (MM/DD):</label>
                    <input type="text" name="members[birthday][]"><br>
                `;
                membersDiv.appendChild(newMember);
            } else {
                alert("Maximum of 7 members allowed.");
            }
        }
    </script>
</head>
<body>
    <h2>Add Family Entry</h2>
    <form action="add_family.php" method="post">
        <label>Family Name:</label>
        <input type="text" name="family_name" required><br>

        <label>Address:</label>
        <input type="text" name="address"><br>
        <label>City:</label>
        <input type="text" name="city"><br>
        <label>State:</label>
        <input type="text" name="state"><br>
        <label>Zip Code:</label>
        <input type="text" name="zip"><br>
        <label>Home Phone:</label>
        <input type="text" name="home_phone"><br>
        <label>Anniversary (MM/DD):</label>
        <input type="text" name="anniversary"><br>

        <h3>Members</h3>
        <div id="members">
            <div>
                <label>First Name:</label>
                <input type="text" name="members[first_name][]" required>
                <label>Cell Phone:</label>
                <input type="text" name="members[cell_phone][]">
                <label>Email:</label>
                <input type="email" name="members[email][]">
                <label>Birthday (MM/DD):</label>
                <input type="text" name="members[birthday][]"><br>
            </div>
        </div>

        <button type="button" onclick="addMember()">Add Another Member</button>
        <br><br>
        <button type="submit">Submit</button>
    </form>

    <p><a href="index.php">Return to Home</a></p>
</body>
</html>