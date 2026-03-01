<?php

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Get ful URL with query string
$full_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Centralized search
$familyname     = trim( $_GET['familyname'] ?? '' );
$family_matches = array();
$fuzzy_matches  = array();
$family         = null;

if ( $_SERVER['REQUEST_METHOD'] === 'GET' && '' !== $familyname ) {
	$address  = $_GET['address'] ?? '';
	$address2 = $_GET['address2'] ?? '';
	$results  = cota_search_families( $connect, $familyname, $address, $address2 );
	$family_matches = $results['matches'];
	$fuzzy_matches  = $results['fuzzy'];
	$family         = $family_matches[0] ?? null;
}

if ( ! $family ) {
	echo cota_page_header();
	?>
		<div id="delete-family" class="cota-delete-container">
			<h2>Delete Family</h2>
			<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $familyname ) ); ?> family not found<br>
			<?php if ( ! empty( $fuzzy_matches ) ) : ?>
			<p>Here are nearby names you can select:</p>
			<?php echo cota_render_family_suggestions( $fuzzy_matches, 'delete-family.php' ); ?>
			<?php endif; ?>
			<a href="../app-includes/search-delete.php">Try again with a different spelling</a></div>
		<?php
			die();
}

$match_count = count( $family_matches );
if ( $match_count > 1 ) {
	echo cota_page_header();
	?>
		<div id="delete-family" class="cota-delete-container">
			<h2>Delete Family</h2>
			<div class="container error-message">
			<?php echo htmlspecialchars( $familyname ); ?> family search returned multiple results.<br>
			<p>Select the family to delete from the list below or refine your search.</p>
			<?php echo cota_render_family_suggestions( $family_matches, 'delete-family.php' ); ?>
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
	