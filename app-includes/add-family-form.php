<?php

require_once __DIR__ . '/bootstrap.php';

global $cota_db, $connect, $cota_app_settings;

require_once $cota_app_settings->COTA_APP_INCLUDES . 'headers.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'format-family-listing.php';

// Echo page header
echo cota_page_header();

echo cota_add_member_script();

// Dump out remainder of import page.
?>
	<div class="cota-add-entry-container">
	<h2 >Add Family</h2>
	<form class="cota-family-entry" action="add-family.php" method="post">
		<div class="row g-3">
			<div class="col-12 col-md-6">
				<label class="form-label fw-bold">Family Name</label>
				<input class="form-control" type="text" name="familyname" style="text-transform:capitalize;" maxlength="50" required>
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-bold">Address</label>
				<input class="form-control" type="text" name="address" style="text-transform:capitalize;" maxlength="50">
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-bold">Address 2</label>
				<input class="form-control" type="text" name="address2" style="text-transform:capitalize;" maxlength="20">
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-bold">City</label>
				<input class="form-control" type="text" name="city" style="text-transform:capitalize;" maxlength="20">
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-bold">State</label>
				<input class="form-control" type="text" name="state" value="PA" maxlength="10" style="text-transform:uppercase">
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-bold">Zip Code</label>
				<input class="form-control" type="text" name="zip" placeholder="xxxxx-xxxx" maxlength="10">
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-bold">Home Phone</label>
				<input class="form-control" type="text" name="homephone" placeholder="xxx-xxx-xxxx" maxlength="20">
			</div>
		</div>

		<h3 class="mt-4">Family Members</h3>
		<p>Enter the primary adult family member(s) first, with their anniversary date, then add additional family members and information as desired.</p>
		<div id="members" class="member-list">
			<div class="member-row">
				<label class="form-label fw-bold">Name</label>
				<input class="form-control" type="text" name="members[first_name][]" style="text-transform:capitalize;" placeholder="First" maxlength="50" required>
				<label class="form-label fw-bold" for="members[last_name][]">Last (only needed if different from family name)</label>
				<input class="form-control" type="text" id="members[last_name][]" name="members[last_name][]" style="text-transform:capitalize;" placeholder="Last" maxlength="50">
				<label class="form-label fw-bold" for="members[cell_phone][]">Cell Phone</label>
				<input class="form-control" type="text" id="members[cell_phone][]" name="members[cell_phone][]" placeholder="xxx-xxx-xxxx" maxlength="20">
				<label class="form-label fw-bold" for="members[email][]">Email</label>
				<input class="form-control" type="email" id="members[email][]" name="members[email][]" maxlength="100">
				<label class="form-label fw-bold" for="members[birthday][]">Birthday</label>
				<input class="form-control" type="date" id="members[birthday][]" name="members[birthday][]" placeholder="mm/dd">
				<label class="form-label fw-bold" for="members[baptism][]">Anniversary of Baptism</label>
				<input class="form-control" type="date" id="members[baptism][]" name="members[baptism][]" placeholder="mm/dd">
				<label class="form-label fw-bold" for="members[anniversary][]">Anniversary of Marriage (if applicable)</label>
				<input class="form-control" type="date" id="members[anniversary][]" name="members[anniversary][]" placeholder="mm/dd">
			</div>
		</div>

		<div class="two-button-grid mt-3">
			<div><button class="cota-add-another" type="button" onclick="cota_add_member()">Add Another Family Member</button></div>
			<div><button class="cota-submit-family" type="submit">Submit Family Update</button></div>
		</div>
	</form>


</body>
</html>
