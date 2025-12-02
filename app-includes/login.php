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

<div class="cota-login-container" style="max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
	<h2 style="text-align: center; margin-bottom: 20px;">Member Login</h2>
	
	<?php if ( ! empty( $success ) ) : ?>
		<div style="padding: 12px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
			<?php echo htmlspecialchars( $success ); ?>
		</div>
	<?php endif; ?>
	
	<?php if ( ! empty( $error ) ) : ?>
		<div style="padding: 12px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">
			<?php echo htmlspecialchars( $error ); ?>
		</div>
	<?php endif; ?>
	
	<form method="post" action="">
		<div style="margin-bottom: 15px;">
			<label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email Address:</label>
			<input 
				type="email" 
				id="email" 
				name="email" 
				value="<?php echo htmlspecialchars( $email ); ?>" 
				required 
				autofocus
				style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
			>
		</div>
		
		<div style="margin-bottom: 20px;">
			<label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
			<input 
				type="password" 
				id="password" 
				name="password" 
				required
				style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
			>
		</div>
		
		<button 
			type="submit" 
			name="login" 
			value="1"
			style="width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;"
		>
			Login
		</button>
	</form>
	
	<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px;">
		<p>First time logging in? You'll be prompted to set up your password.</p>
	</div>
</div>

</body>
</html>

<?php
$cota_db->close_connection();
?>

