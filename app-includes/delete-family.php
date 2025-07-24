<?php
require_once __DIR__ . '/bootstrap.php';

// Get ful URL with query string
$full_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// $address_like = $address_2_like = '';
if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['familyname'] ) ) {
	$family_name = cota_sanitize( $_GET['familyname'] );

	// Check optional search fields
	$address_entered   = ! empty( trim( $_GET['address'] ?? '' ) );
	$address_2_entered = ! empty( trim( $_GET['address2'] ?? '' ) );

	// Fetch family record
	if ( ! $address_entered && ! $address_2_entered ) {
		// No extra search fields
		$statement = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ?'
		);
		$statement->bind_param( 's', $family_name );
	} elseif ( $address_entered && ! $address_2_entered ) {
		// Extra search field address only entered
		$address_like = '%' . $_GET['address'] . '%';
		$statement    = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address LIKE ?'
		);
		$statement->bind_param( 'ss', $family_name, $address_like );
	} elseif ( ! $address_entered && $address_2_entered ) {
		// Extra search field address2 only entered
		$address_2_like = '%' . $_GET['address2'] . '%';
		$statement      = $connect->prepare(
			'SELECT * FROM families 
            WHERE familyname = ? AND address2 LIKE ?'
		);
		$statement->bind_param( 'ss', $family_name, $address_2_like );
	} elseif ( $address_entered && $address_2_entered ) {
		// Extra search field address and address2 entered
		$address_like   = '%' . $_GET['address'] . '%';
		$address_2_like = '%' . $_GET['address2'] . '%';
		$statement      = $connect->prepare(
			'SELECT * FROM families 
        WHERE familyname = ? 
        AND ( address LIKE ? OR address2 LIKE ?) '
		);
		$statement->bind_param(
			'sss',
			$family_name,
			$address_like,
			$address_2_like
		);
	}

	// Execute search
	$statement->execute();
	$result = $statement->get_result();

	$family = $result->fetch_assoc();
	$statement->close();
}
if ( ! $family ) {
	// Echo header
	echo cota_page_header();
	?>
		<div id="delete-family" class="cota-delete-container">
			<h2>Delete Family</h2>
			<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $family_name ) ); ?> family not found<br>
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
						<?php echo htmlspecialchars( $family_name ); ?> family search returned multiple results.<br><br>
			<a href="../app-includes/search-delete.php?familyname=<?php echo urlencode( $family_name ); ?>&address=&address2=">Please refine your search with the address fields.</a>
			</div>
		<?php
			die();
}

	// Fetch members
	$statement = $connect->prepare( 'SELECT * FROM members WHERE family_id = ?' );
	$statement->bind_param( 'i', $family['id'] );
	$statement->execute();
	$members = $statement->get_result();
	$statement->close();

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
		<label>Anniversary</label>
		<input type="date" name="annday" value="<?php echo htmlspecialchars( $family['annday'] ); ?>" readonly>

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
