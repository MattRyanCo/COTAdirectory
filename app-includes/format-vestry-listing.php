<?php
/**
 * cota_format_vestry_listing
 *
 * @param mysqli_result $vestry_members - Result of database query of all vestry members
 * @return array $formatted_vestry_array - Array formatted for printing - 1 row per vestry member
 */

function cota_format_vestry_listing( $vestry_member, $mode = 'display', $position = 'upper' ) {

	$placeholder = ' ';
	if ( 'display' === $mode ) {
		$format_string = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
		if ( 'lower' === $position ) {
			$format_string = "<tr class='format_string'><td>%s</td><td>%s</td><td>%s</td></tr>";
		}
	} elseif ( 'print' === $mode ) {
		$format_string = "%-7s%-20s%-18s\n";
		if ( 'lower' === $position ) {
			$format_string = "%-22s%s\n";
		}
	} else {
		die( 'Error: Invalid mode passed to cota_format_vestry_listing. Must be "display" or "print".' );
	}

	// Get first & last name of vestry member from the members table using member_id
	global $cota_db;
	$member_info = $cota_db->read_member_by_id_extended( $vestry_member['member_id'] );

	$formatted_vestry_member = sprintf(
		$format_string,
		$vestry_member['class'] . ' ',
		$member_info['first_name'] . ' ' . $member_info['last_name'],
		ucwords( '' === trim( $vestry_member['vrole'] ) ? $placeholder : $vestry_member['vrole'] ),
		ucwords($vestry_member['liaison'])
	);
	if ( 'lower' === $position ) {
		$formatted_vestry_member = sprintf(
			$format_string,
			$member_info['first_name'] . ' ' . $member_info['last_name'],
			ucwords($vestry_member['liaison'])
		);
	}

	if ( 'display' === $mode ) {
		// Add blank line after each vestry member for readability
		$blank = "\r\n";
		$formatted_vestry_member .= sprintf(
			$format_string,
			nl2br( $blank )
		);
	}

	return $formatted_vestry_member;
}

function cota_generate_vestry_listing_for_print() {
	global $cota_db;
	// Create the printed output for the vestry listing and return as
	// content string for cota_parse_intro_content.
	$vestry     = $cota_db->read_vestry_database();
	$num_vestry = $vestry->num_rows;
	$content_replace = '';
	$mode = 'print';

	// Print Out Table Headings for the upper Vestry Listing
	if ( 'print' === $mode ) {
		// %-7s means left align Class within 7 character width 
		// %-20s means left align Name within 20 character width
		// %-18s means left align Role within 18 character width
		$content_replace .= sprintf(
			"%-7s%-20s%-18s\n",
			'Class',
			'Name',
			'Role'
			);
	}

	if ( 0 === $num_vestry ) {
		empty_database_alert( 'Vestry Listing Display' );
	} else {
		$vestry_individuals = $cota_db->read_members_of_vestry();
		while ( $vestry_individual = $vestry_individuals->fetch_assoc() ) {
			$content_replace .= sprintf( '%s', cota_format_vestry_listing( $vestry_individual, $mode, 'upper' ) );
		}
	}

		// Print Out Table Headings for the lower Vestry Liaison Listing
	if ( 'print' === $mode ) {
		// %-25s means left align Name within 25 character width,
		// %s means Area Liaison with no specific width (will take remaining space).
		$content_replace .= sprintf(
			"\n%-22s%s\n",
			'Name',
			'Area Liaison'
			);
		$vestry_individuals = $cota_db->read_members_of_vestry();
		while ( $vestry_individual = $vestry_individuals->fetch_assoc() ) {
			$content_replace .= sprintf( '%s', cota_format_vestry_listing( $vestry_individual, $mode, 'lower' ) );
		}
	}
	return $content_replace;
}

/**
 * Render the single-screen editable vestry form (mirrors the edit-family.php layout).
 * Every vestry entry is shown at once since the vestry db is always small.
 *
 * @param array $vestry_rows      Each row: id, full_name, class, vrole, liaison. id === -1 marks a not-yet-saved row.
 * @param array $member_directory Full member directory (id, full_name) used to populate the Name datalist.
 * @param array $errors           Optional map of row index => error message, for redisplay after a failed submit.
 * @return string
 */
function cota_render_vestry_edit_form( $vestry_rows, $member_directory, $errors = array() ) {
	ob_start();

	$member_names = array();
	foreach ( $member_directory as $member ) {
		$member_names[ $member['full_name'] ] = true;
	}
	?>
	<datalist id="vestry-member-names">
		<?php foreach ( array_keys( $member_names ) as $name ) : ?>
			<option value="<?php echo htmlspecialchars( $name, ENT_QUOTES ); ?>">
		<?php endforeach; ?>
	</datalist>

	<form class="cota-vestry-edit" action="update-vestry.php" method="post">
		<?php foreach ( $vestry_rows as $index => $row ) : ?>
			<div class="vestry-row">
				<div class="vestry-field vestry-field-name">
					<label>Name</label>
					<input class="form-control" type="text" name="vestry[name][]" list="vestry-member-names" value="<?php echo htmlspecialchars( $row['full_name'] ); ?>" required>
					<?php if ( ! empty( $errors[ $index ] ) ) : ?>
						<div class="error-message"><?php echo htmlspecialchars( $errors[ $index ] ); ?></div>
					<?php endif; ?>
				</div>
				<div class="vestry-field vestry-field-class">
					<label>Class</label>
					<input class="form-control" type="text" name="vestry[class][]" value="<?php echo htmlspecialchars( $row['class'] ); ?>" maxlength="4">
				</div>
				<div class="vestry-field vestry-field-role">
					<label>Role</label>
					<input class="form-control" type="text" name="vestry[vrole][]" value="<?php echo htmlspecialchars( $row['vrole'] ); ?>" maxlength="25">
				</div>
				<div class="vestry-field vestry-field-liaison">
					<label>Area Liaison</label>
					<input class="form-control" type="text" name="vestry[liaison][]" value="<?php echo htmlspecialchars( $row['liaison'] ); ?>" maxlength="50">
				</div>
				<input type="hidden" name="vestry[id][]" value="<?php echo (int) $row['id']; ?>">
			</div>
		<?php endforeach; ?>

		<h3 class="mt-4">Add New Vestry Member</h3>
		<div id="vestry-add-members">
			<div class="vestry-row">
				<div class="vestry-field vestry-field-name">
					<label>Name</label>
					<input class="form-control" type="text" name="vestry[name][]" list="vestry-member-names" placeholder="Member name">
				</div>
				<div class="vestry-field vestry-field-class">
					<label>Class</label>
					<input class="form-control" type="text" name="vestry[class][]" placeholder="Class" maxlength="4">
				</div>
				<div class="vestry-field vestry-field-role">
					<label>Role</label>
					<input class="form-control" type="text" name="vestry[vrole][]" placeholder="Role" maxlength="25">
				</div>
				<div class="vestry-field vestry-field-liaison">
					<label>Area Liaison</label>
					<input class="form-control" type="text" name="vestry[liaison][]" placeholder="Area Liaison" maxlength="50">
				</div>
				<input type="hidden" name="vestry[id][]" value="-1">
			</div>
		</div>

		<div class="three-button-grid mt-3">
			<div><button class="cota-add-another" type="button" onclick="cota_add_vestry_member()">Add Another Vestry Member</button></div>
			<div><button class="cota-submit-family" type="submit">Submit Update</button></div>
			<div><button class="cota-cancel-family" type="reset">Cancel</button></div>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
