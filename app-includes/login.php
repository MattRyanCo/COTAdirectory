<?php
/**
 * Member Login Page
 * 
 * Allows members to log in using their email address and password.
 * If they haven't set a password yet, they'll be redirected to the setup page.
 */

ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

// Load bootstrap (but skip authentication check)
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

require_once __DIR__ . '/class-app-settings.php';
$cota_app_settings = new App_Settings();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-database-functions.php';
$cota_db = new COTA_Database();
$connect = $cota_db->get_connection();

require_once $cota_app_settings->COTA_APP_INCLUDES . 'class-member-auth.php';
require_once $cota_app_settings->COTA_APP_INCLUDES . 'helper-functions.php';

$auth = new COTA_Member_Auth( $connect );

// If already authenticated, redirect to home
if ( $auth->is_authenticated() ) {
	header( 'Location: /' );
	exit;
}

// Handle login form submission
$error = '';
$success = '';
$email = '';

// Check for logout success message
if ( isset( $_GET['loggedout'] ) ) {
	$success = 'You have been logged out successfully.';
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['login'] ) ) {
	$email = trim( $_POST['email'] ?? '' );
	$password = $_POST['password'] ?? '';
	
	if ( empty( $email ) || empty( $password ) ) {
		$error = 'Please enter both email and password.';
	} else {
		// Check if member exists
		$member = $auth->get_member_by_email( $email );
		
		if ( ! $member ) {
			$error = 'Email address not found in directory.';
		} elseif ( $auth->needs_password_setup( $email ) ) {
			// Redirect to password setup
			session_start();
			$_SESSION['setup_email'] = $email;
			header( 'Location: /app-includes/setup-password.php' );
			exit;
		} elseif ( $auth->login( $email, $password ) ) {
			// Login successful - redirect
			if ( session_status() === PHP_SESSION_NONE ) {
				session_start();
			}
			$redirect = $_SESSION['redirect_after_login'] ?? '/';
			unset( $_SESSION['redirect_after_login'] );
			header( 'Location: ' . $redirect );
			exit;
		} else {
			$error = 'Invalid email or password.';
		}
	}
}

// Output page
echo cota_page_header();
?>

<div class="cota-login-container">
	<h2>Member Login</h2>
	
	<?php if ( ! empty( $success ) ) : ?>
		<div class="login-success">
			<?php echo htmlspecialchars( $success ); ?>
		</div>
	<?php endif; ?>
	
	<?php if ( ! empty( $error ) ) : ?>
		<div class="login-error">
			<?php echo htmlspecialchars( $error ); ?>
		</div>
	<?php endif; ?>
	
	<form method="post" action="">
		<div class="login-form-group">
			<label class="login-form-label" for="email">Email Address:</label>
			<input class="login-form-input"
				type="email" 
				id="email" 
				name="email" 
				value="<?php echo htmlspecialchars( $email ); ?>" 
				required 
				autofocus
			>
		</div>
		
		<div style="margin-bottom: 20px;">
			<label class="login-form-label" for="password">Password:</label>
			<input class="login-form-input"
				type="password" 
				id="password" 
				name="password" 
				required
			>
		</div>
		
		<button class="login-form-button" type="submit" name="login" value="1">Login</button>
	</form>
	
	<div class="login-form-footer">
		<p>First time user? You'll be prompted to set up your password.</p>
	</div>
</div>

</body>
</html>

<?php
$cota_db->close_connection();
?>

