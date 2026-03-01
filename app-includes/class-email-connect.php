<?php 

class COTA_Email_Connect {

$api_key = $_ENV['EMAIL_CONNECT_API_KEY'] ?? '';
$api_url = $_ENV['EMAIL_CONNECT_API_URL'] ?? '';

function __construct() {
	// Constructor code can go here if needed
}

function get_email_address_of( $member_id ) {
	global $cota_db, $connect;
	$result = $connect->query( "SELECT email FROM members WHERE id = " . intval( $member_id ) );
	if ( $row = $result->fetch_assoc() ) {
		return $row['email'] ?? '';
	}
	return '';
}

function send_email_to( $member_id, $subject, $body ) {
	$email = $this->get_email_address_of( $member_id );
	if ( empty( $email ) ) {
		return false;
	}

	$payload = [
		'to'      => $email,
		'subject' => $subject,
		'body'    => $body,
	];

	$ch = curl_init( $this->api_url . '/send' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $this->api_key,
	] );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );

	$response = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	return ( $http_code >= 200 && $http_code < 300 );
}

function send_campaign_to_group( $group_id, $subject, $body ) {
	$payload = [
		'group_id' => $group_id,
		'subject'  => $subject,
		'body'     => $body,
	];

	$ch = curl_init( $this->api_url . '/campaigns/send' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Authorization: Bearer ' . $this->api_key,
	] );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );

	$response = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	return ( $http_code >= 200 && $http_code < 300 );
}
function get_all_subscribers() {
	$ch = curl_init( $this->api_url . '/subscribers' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, [
		'Authorization: Bearer ' . $this->api_key,
	] );

	$response = curl_exec( $ch );
	$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );

	if ( $http_code >= 200 && $http_code < 300 ) {
		return json_decode( $response, true );
	}

	return [];

}