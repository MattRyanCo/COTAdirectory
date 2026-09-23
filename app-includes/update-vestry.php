<?php
require_once __DIR__ . '/bootstrap.php';
global $cota_db, $connect,  $cota_app_settings;
require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-vestry-listing.php';

// The Name field must always resolve to an existing entry in the primary member db.
$member_directory = $cota_db->read_member_directory();
$name_lookup       = cota_build_member_name_lookup( $member_directory );

$errors      = array();
$vestry_rows = array();
$updated     = false;

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	$ids      = $_POST['vestry']['id'] ?? array();
	$names    = $_POST['vestry']['name'] ?? array();
	$classes  = $_POST['vestry']['class'] ?? array();
	$vroles   = $_POST['vestry']['vrole'] ?? array();
	$liaisons = $_POST['vestry']['liaison'] ?? array();

	for ( $i = 0; $i < count( $ids ); $i++ ) {
		$vid     = intval( $ids[ $i ] );
		$name    = trim( $names[ $i ] ?? '' );
		$class   = trim( $classes[ $i ] ?? '' );
		$vrole   = trim( $vroles[ $i ] ?? '' );
		$liaison = trim( $liaisons[ $i ] ?? '' );

		// Skip an untouched blank "add new" row.
		if ( -1 === $vid && '' === $name && '' === $class && '' === $vrole && '' === $liaison ) {
			continue;
		}

		$row_index                = count( $vestry_rows );
		$resolved_member_id       = cota_resolve_member_id_by_name( $name_lookup, $name );
		$vestry_rows[ $row_index ] = array(
			'id'        => $vid,
			'member_id' => is_int( $resolved_member_id ) ? $resolved_member_id : null,
			'full_name' => $name,
			'class'     => $class,
			'vrole'     => $vrole,
			'liaison'   => $liaison,
		);

		if ( '' === $name ) {
			$errors[ $row_index ] = 'Name is required.';
		} elseif ( null === $resolved_member_id ) {
			$errors[ $row_index ] = 'No matching member found in the member database for "' . $name . '".';
		} elseif ( false === $resolved_member_id ) {
			$errors[ $row_index ] = 'More than one member matches this name. Please ask an administrator to resolve the ambiguity.';
		}
	}

	if ( empty( $errors ) ) {
		foreach ( $vestry_rows as $row ) {
			if ( -1 === $row['id'] ) {
				$stmt = $connect->prepare( 'INSERT INTO vestry (member_id, class, vrole, liaison) VALUES (?, ?, ?, ?)' );
				$stmt->bind_param( 'isss', $row['member_id'], $row['class'], $row['vrole'], $row['liaison'] );
			} else {
				$stmt = $connect->prepare( 'UPDATE vestry SET member_id = ?, class = ?, vrole = ?, liaison = ? WHERE id = ?' );
				$stmt->bind_param( 'isssi', $row['member_id'], $row['class'], $row['vrole'], $row['liaison'], $row['id'] );
			}
			$stmt->execute();
			$stmt->close();
		}
		$updated = true;
	}
}

// Echo page header
echo cota_page_header();

if ( $updated ) {
	?>
	<div class="cota-update-container">
		<h2>Vestry updated!</h2>
		<br>
		<div class="two-button-grid">
			<div><button class="cota-edit-family" type="button"><a href="update-vestry.php">Edit Vestry Again</a></button></div>
			<div><button class="cota-search-family" type="button"><a href="display-vestry.php">View Vestry Listing</a></button></div>
		</div>
	</div>
	<?php
	$cota_db->close_connection();
	die();
}

// On GET (or a failed POST), rebuild the current vestry rows for display.
if ( empty( $vestry_rows ) ) {
	$vestry_members = $cota_db->read_members_of_vestry();
	if ( 0 === $vestry_members->num_rows ) {
		empty_database_alert( 'Update Vestry' );
	}
	while ( $vestry_member = $vestry_members->fetch_assoc() ) {
		$member_info    = $cota_db->read_member_by_id_extended( $vestry_member['member_id'] );
		$vestry_rows[]  = array(
			'id'        => (int) $vestry_member['id'],
			'member_id' => (int) $vestry_member['member_id'],
			'full_name' => trim( ( $member_info['first_name'] ?? '' ) . ' ' . ( $member_info['last_name'] ?? '' ) ),
			'class'     => $vestry_member['class'] ?? '',
			'vrole'     => $vestry_member['vrole'] ?? '',
			'liaison'   => $vestry_member['liaison'] ?? '',
		);
	}
}
?>
<div id="edit-vestry" class="cota-edit-container">
	<h2>Update Vestry</h2>
	<?php echo cota_add_vestry_member_script(); ?>
	<?php echo cota_render_vestry_edit_form( $vestry_rows, $member_directory, $errors ); ?>
</div>
</body>
</html>
<?php
$cota_db->close_connection();
