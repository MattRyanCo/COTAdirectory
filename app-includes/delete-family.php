<?php

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Get ful URL with query string
$full_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// $addresslike = $address2like = '';
if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['familyname'] ) ) {
	$familyname = $_GET['familyname'];

	// Check optional search fields
	$addressEntered  = ! empty( trim( $_GET['address'] ?? '' ) );
	$address2Entered = ! empty( trim( $_GET['address2'] ?? '' ) );

	// Fetch family record
	if ( ! $addressEntered && ! $address2Entered ) {
		// No extra search fields
		$stmt = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ?'
		);
		$stmt->bind_param( 's', $familyname );
	} elseif ( $addressEntered && ! $address2Entered ) {
		// Extra search field address only entered
		$addresslike = '%' . $_GET['address'] . '%';
		$stmt        = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address LIKE ?'
		);
		$stmt->bind_param( 'ss', $familyname, $addresslike );
	} elseif ( ! $addressEntered && $address2Entered ) {
		// Extra search field address2 only entered
		$address2like = '%' . $_GET['address2'] . '%';
		$stmt         = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address2 LIKE ?'
		);
		$stmt->bind_param( 'ss', $familyname, $address2like );
	} elseif ( $addressEntered && $address2Entered ) {
		// Extra search field address and address2 entered
		$addresslike  = '%' . $_GET['address'] . '%';
		$address2like = '%' . $_GET['address2'] . '%';
		$stmt         = $connect->prepare(
			'SELECT * FROM families 
        WHERE familyname = ? 
        AND ( address LIKE ? OR address2 LIKE ?) '
		);
		$stmt->bind_param(
			'sss',
			$familyname,
			$addresslike,
			$address2like
		);
	}

	// Execute search
	$stmt->execute();
	$result = $stmt->get_result();

	$family = $result->fetch_assoc();
	$stmt->close();
}
if ( ! $family ) {
	// Echo header
	echo cota_page_header();
	?>
		<div id="delete-family" class="cota-delete-container">
			<h2>Delete Family</h2>
			<div class="container error-message"><?php echo ucfirst( $familyname ); ?> family not found<br>
			<a href="../app-includes/search-delete.php">Try again with a different spelling</a></div>
		<?php
			die();
}
if ( $result->num_rows > 1 ) {
	// More than 1 result, need to refine.
	echo cota_page_header();
	?>
		<div id="delete-family" class="cota-delete-container">
			<h2>Delete Family</h2>
			<div class="container error-message">
			<?php echo $familyname; ?> family search returned multiple results.<br><br> 
				<a href="../app-includes/search-delete.php?familyname=<?php echo $familyname; ?>&address=&address2=">Please refine your search with the address fields.</a>
			</div>
		<?php
			die();
}

	// Fetch members
	$stmt = $connect->prepare( 'SELECT * FROM members WHERE family_id = ?' );
	$stmt->bind_param( 'i', $family['id'] );
	$stmt->execute();
	$members = $stmt->get_result();
	$stmt->close();

// Echo header
echo cota_page_header();

// Dump out remainder of page.
?>

	<div id="delete-family" class="cota-import-container">
	<h2>Delete Family</h2>
	<form class="cota-family-delete" action="delete-family-form-handler.php" method="post">
		<input type="hidden" name="family_id" value="<?php echo $family['id']; ?>">
		<label>Family Name</label>
		<input type="text" name="familyname" value="<?php echo htmlspecialchars( $family['familyname'] ); ?>" readonly>
		<label>Address</label>
		<input type="text" name="address" value="<?php echo htmlspecialchars( $family['address'] ); ?>" readonly>
		<label>City</label>
		<input type="text" name="city" value="<?php echo htmlspecialchars( $family['city'] ); ?>" readonly>
		<label>State</label>
		<input type="text" name="state" value="<?php echo htmlspecialchars( $family['state'] ); ?>" readonly>
		<label>Zip</label>
		<input type="text" name="zip" value="<?php echo htmlspecialchars( $family['zip'] ); ?>" readonly>
		<label>Home Phone</label>
		<input type="text" name="homephone" value="<?php echo htmlspecialchars( $family['homephone'] ); ?>" readonly>

		<button class="delall" type="submit" name="delall" >Delete Entire Family From Directory</button>

		<h3>Family Members</h3>
		<div id="members-delete">
			<?php
			$first = true;
			while ( $member = $members->fetch_assoc() ) :
				?>
				<?php if ( $first ) : ?>
				<div class="member-header" >
				<span>Select</span>
				<span>First</span>
				<span>Last</span>
				<span>Cell</span>
				<span>Email</span>
				<span>Birthday</span>
				<span>Baptism</span>
				</div>
					<?php
					$first = false;
endif;
				?>
			<div class="member-row">
				<input type="checkbox" name="delete_member[]" value="<?php echo $member['id']; ?>">
				<input type="text" name="members[first_name][]" value="<?php echo htmlspecialchars( $member['first_name'] ); ?>" readonly>
				<input type="text" name="members[last_name][]" value="<?php echo ! empty( $member['last_name'] ) ? htmlspecialchars( $member['last_name'] ) : htmlspecialchars( $family['familyname'] ?? '' ); ?>" readonly>
				<input type="text" name="members[cell_phone][]" value="<?php echo htmlspecialchars( $member['cell_phone'] ); ?>" readonly>
				<input type="email" name="members[email][]" value="<?php echo htmlspecialchars( $member['email'] ); ?>" readonly>
				<input type="date" name="members[birthday][]" value="<?php echo htmlspecialchars( $member['birthday'] ); ?>" readonly>
				<input type="date" name="members[baptism][]" value="<?php echo htmlspecialchars( $member['baptism'] ); ?>" readonly>
				<input type="hidden" name="members[id][]" value="<?php echo $member['id']; ?>">

			</div>
			<?php endwhile; ?>
		</div>

		<button class="delselected" type="submit" name="delselected">Delete Selected Members From Family</button>
	</form>
	</div>
</body>
</html>
	