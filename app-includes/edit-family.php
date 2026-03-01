<?php

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

// Centralized search / fuzzy lookup
$familyname     = trim( $_GET['familyname'] ?? '' );
$family_matches = array();
$fuzzy_matches  = array();
$family         = null;

if ( $_SERVER['REQUEST_METHOD'] === 'GET' && '' !== $familyname ) {
	$address   = $_GET['address'] ?? '';
	$address2  = $_GET['address2'] ?? '';
	$results   = cota_search_families( $connect, $familyname, $address, $address2 );
	$family_matches = $results['matches'];
	$fuzzy_matches  = $results['fuzzy'];
	$family         = $family_matches[0] ?? null;
}

// Echo header
echo cota_page_header();
if ( ! $family ) {

	?>
	<div id="edit-family" class="cota-edit-container">
		<h2>Search / Edit Family</h2>
		<div class="container error-message"><?php echo htmlspecialchars( ucfirst( $familyname ) ); ?> family not found<br>
		<?php if ( ! empty( $fuzzy_matches ) ) : ?>
			<p>We could not find an exact match. Please choose from these nearby families:</p>
			<?php echo cota_render_family_suggestions( $fuzzy_matches, 'edit-family.php' ); ?>
		<?php endif; ?>
		<a href="../app-includes/search-edit.php">Try again with a different spelling</a></div>
	<?php
	die();
}

$match_count = count( $family_matches );
if ( $match_count > 1 ) {
	// More than 1 result, need to refine.
	?>
	<div id="edit-family" class="cota-delete-container">
		<h2>Search / Edit Family</h2>
		<div class="container error-message">
		<?php echo htmlspecialchars( $familyname ); ?> family search returned multiple results.<br>
		<p>Select the matching family from the list below or refine your search.</p>
		<?php echo cota_render_family_suggestions( $family_matches, 'edit-family.php' ); ?>
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
		<input type="text" name="homephone" value="<?php echo htmlspecialchars( $family['homephone'] ?? '' ); ?>">

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
					<span>Anniversary</span>
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
				<input type="date" name="members[anniversary][]" value="<?php echo htmlspecialchars( $member['anniversary'] ); ?>">
				<input type="hidden" name="members[id][]" value="<?php echo $member['id']; ?>">
			</div>
			<?php endwhile; ?>
		</div><br><br>

		<h3>Add New Family Member(s)</h3>
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
				<input type="text" name="members[first_name][]" value="<?php echo htmlspecialchars( $member['first_name'] ?? '' ); ?>">
				<input type="text" name="members[last_name][]" value="<?php echo ! empty( $member['last_name'] ) ? htmlspecialchars( $member['last_name'] ) : htmlspecialchars( $family['familyname'] ?? '' ); ?>">
				<input type="text" name="members[cell_phone][]" value="<?php echo htmlspecialchars( $member['cell_phone'] ?? '' ); ?>">
				<input type="email" name="members[email][]" value="<?php echo htmlspecialchars( $member['email'] ?? '' ); ?>">
				<input type="date" name="members[birthday][]" value="<?php echo htmlspecialchars( $member['birthday'] ?? '' ); ?>">
				<input type="date" name="members[baptism][]" value="<?php echo htmlspecialchars( $member['baptism'] ?? '' ); ?>">
				<input type="hidden" name="members[id][]" value="-1">
			</div>
		<div class="three-button-grid">
			<div><button class="cota-add-another" type="button" onclick="cota_add_member()">Add Another Family Member</button></div>
			<div><button class="cota-submit-family" type="submit">Submit Update</button></div>
			<div><button class="cota-cancel-family" type="reset">Cancel</button></div>
		</div>
	</form>
</body>
</html>
