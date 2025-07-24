<?php
require_once __DIR__ . '/bootstrap.php';

// $address_like = $address_2_like = '';
if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['familyname'] ) ) {
	$family_name = cota_sanitize( $_GET['familyname'] );
	// Check for optional fields
	if ( ! isset( $_GET['address'] ) || trim( $_GET['address'] ) === '' ) {
		// Address was not entered
	}

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
		$statement->bind_param( 'sss', $family_name, $address_like, $address_2_like );
	}

	// Execute search
	$statement->execute();
	$result = $statement->get_result();
	$family = $result->fetch_assoc();
	$statement->close();
}
		// Echo header
	echo cota_page_header();
if ( ! $family ) {

	?>
		<div id="edit-family" class="cota-edit-container">
			<h2>Search / Edit Family</h2>
			<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $family_name ) ); ?> family not found<br>
			<a href="../app-includes/search-edit.php">Try again with a different spelling</a></div>
		<?php
			die();
}

if ( $result->num_rows > 1 ) {
	// More than 1 result, need to refine.
	// echo cota_page_header();
	?>
		<div id="edit-family" class="cota-delete-container">
			<h2>Search / Edit Family</h2>
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

	echo cota_add_member_script();
// Dump out remainder of import page.
?>

	<h2>Edit Family</h2>
	<form class="cota-family-edit" action="update-family.php" method="post">
		<input type="hidden" name="family_id" value="<?php echo $family['id']; ?>">
		<label>Family Name</label>
		<input type="text" name="familyname" value="<?php echo htmlspecialchars( $family['familyname'] ); ?>" required>
		<label>Address</label>
		<input type="text" name="address" value="<?php echo htmlspecialchars( $family['address'] ); ?>">
		<label>City</label>
		<input type="text" name="city" value="<?php echo htmlspecialchars( $family['city'] ); ?>">
		<label>State</label>
		<input type="text" name="state" value="<?php echo htmlspecialchars( $family['state'] ); ?>">
		<label>Zip</label>
		<input type="text" name="zip" value="<?php echo htmlspecialchars( $family['zip'] ); ?>">
		<label>Home Phone</label>
		<input type="text" name="homephone" value="<?php echo htmlspecialchars( $family['homephone'] ); ?>">
		<label>Anniversary</label>
		<input type="date" name="annday" value="<?php echo htmlspecialchars( $family['annday'] ); ?>">

		<h3>Family Members</h3>

		<div id="members">
			<?php
			$first = true;
			while ( $member = $members->fetch_assoc() ) :
				?>
				<?php if ( $first ) : ?>
				<div class="member-header">
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

				<input type="text" name="members[first_name][]" value="<?php echo htmlspecialchars( $member['first_name'] ); ?>">
				<input type="text" name="members[last_name][]" value="<?php echo ! empty( $member['last_name'] ) ? htmlspecialchars( $member['last_name'] ) : htmlspecialchars( $family['familyname'] ?? '' ); ?>">
				<input type="text" name="members[cell_phone][]" value="<?php echo htmlspecialchars( $member['cell_phone'] ); ?>">
				<input type="email" name="members[email][]" value="<?php echo htmlspecialchars( $member['email'] ); ?>">
				<input type="date" name="members[birthday][]" value="<?php echo htmlspecialchars( $member['birthday'] ); ?>">
				<input type="date" name="members[baptism][]" value="<?php echo htmlspecialchars( $member['baptism'] ); ?>">
				<input type="hidden" name="members[id][]" value="<?php echo $member['id']; ?>">
			</div>
			<?php endwhile; ?>
		</div><br><br>

		<h3>Add NEW Family Members</h3>
		<div id="add-members">
			<div class="member-header">
				<span>First</span>
				<span>Last</span>
				<span>Cell</span>
				<span>Email</span>
				<span>Birthday</span>
				<span>Baptism</span>
			</div>
			<div class="member-row">
				<input type="text" name="members[first_name][]" value="<?php echo htmlspecialchars( $member['first_name'] ); ?>">
				<input type="text" name="members[last_name][]" value="<?php echo ! empty( $member['last_name'] ) ? htmlspecialchars( $member['last_name'] ) : htmlspecialchars( $family['familyname'] ?? '' ); ?>">
				<input type="text" name="members[cell_phone][]" value="<?php echo htmlspecialchars( $member['cell_phone'] ); ?>">
				<input type="email" name="members[email][]" value="<?php echo htmlspecialchars( $member['email'] ); ?>">
				<input type="date" name="members[birthday][]" value="<?php echo htmlspecialchars( $member['birthday'] ); ?>">
				<input type="date" name="members[baptism][]" value="<?php echo htmlspecialchars( $member['baptism'] ); ?>">
				<input type="hidden" name="members[id][]" value="-1">
			</div>
			<!-- <div>
				<label >Name</label>
				<input type="text" name="members[first_name][]" style="text-transform:capitalize;" placeholder="First" >
				<label for="members[last_name][]">Last (only needed if different from family name)</label>
				<input type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;" placeholder="Last"><br>
				<label for="members[cell_phone][]">Cell Phone</label>
				<input type="text" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx"><br>
				<label for="members[email][]">Email</label>
				<input type="email" id="members[email][]" name="members[email][]"><br>
				<label for="members[birthday][]">Birthday</label>
				<input type="date" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd/yyyy"><br>
				<label for="members[baptism][]">Anniversary of Baptism</label>
				<input type="date" id="members[baptism]" name="members[baptism][]" placeholder="mm/dd/yyyy"><br><br><br>
				<input type="hidden" name="members[id][]" value="-1">
			</div> -->
		<!-- </div> -->

		<div class="three-button-grid">
			<div><button class="cota-add-another" type="button" onclick="cota_add_member()">Add Another Family Member</button></div>
			<div><button class="cota-submit-family" type="submit">Submit Update</button></div>
			<div><button class="cota-cancel-family" type="reset">Cancel</button></div>
		</div>
	</form>
</body>
</html>
