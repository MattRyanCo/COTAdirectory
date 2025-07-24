<?php
require_once __DIR__ . '/bootstrap.php';

// GEt ful URL with query string
$full_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['family_id'] ) ) {
	$family_id = intval( $_POST['family_id'] );

	if ( isset( $_POST['delall'] ) ) {
		// Delete family members
		$statement = $connect->prepare( 'DELETE FROM members WHERE family_id = ?' );
		$statement->bind_param( 'i', $family_id );
		$statement->execute();
		$statement->close();

		// Delete family record
		$statement = $connect->prepare( 'DELETE FROM families WHERE id = ?' );
		$statement->bind_param( 'i', $family_id );
		$statement->execute();
		$statement->close();

		// Echo header
		echo cota_page_header();
		// Dump out remainder of import page.
		echo "<div class='cota-delete-container'>";
		echo '<h2>Family deleted successfully!</h2>';
		echo '</div>';

	}
	if ( isset( $_POST['delselected'] ) && ! empty( $_POST['delete_member'] ) ) {
		// Delete only selected members
		foreach ( $_POST['delete_member'] as $member_id ) {
			$member_id = intval( $member_id );
			$statement = $connect->prepare( 'DELETE FROM members WHERE id = ? AND family_id = ?' );
			$statement->bind_param( 'ii', $member_id, $family_id );
			$statement->execute();
			$statement->close();
		}

		// Echo header
		echo cota_page_header();
		// Dump out remainder of import page.
		echo "<div class='cota-delete-container'>";
		echo '<h2>Selected member(s) deleted successfully!</h2>';
		echo '</div>';

	} elseif ( isset( $_POST['delselected'] ) ) {
		echo '<h2>No members selected for deletion.</h2>';
	}
}

$cota_db->close_connection();
