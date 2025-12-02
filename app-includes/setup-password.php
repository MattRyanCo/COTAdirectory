<?php
/**
 * Password Setup Page
 * 
 * Allows members to set up their password on first visit.
 * Email must exist in the members table.
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

session_start();

// Get email from session or form
$email = $_SESSION['setup_email'] ?? $_GET['email'] ?? '';

// If no email provided, redirect to login
if ( empty( $email ) ) {
	header( 'Location: /app-includes/login.php' );
	exit;
}

// Check if member exists
$member = $auth->get_member_by_email( $email );
if ( ! $member ) {
	session_destroy();
	header( 'Location: /app-includes/login.php?error=notfound' );
	exit;
}

// Check if password already set (if so, redirect to login)
if ( ! $auth->needs_password_setup( $email ) ) {
	unset( $_SESSION['setup_email'] );
	header( 'Location: /app-includes/login.php' );
	exit;
}

// Handle password setup form submission
$error = '';
$success = false;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['setup_password'] ) ) {
	$password = $_POST['password'] ?? '';
	$confirm_password = $_POST['confirm_password'] ?? '';
	
	if ( empty( $password ) ) {
		$error = 'Please enter a password.';
	} elseif ( strlen( $password ) < 8 ) {
		$error = 'Password must be at least 8 characters long.';
	} elseif ( $password !== $confirm_password ) {
		$error = 'Passwords do not match.';
	} else {
		// Set password
		if ( $auth->set_password( $email, $password ) ) {
			$success = true;
			unset( $_SESSION['setup_email'] );
			
			// Auto-login after password setup
			$auth->login( $email, $password );
			
			// Redirect after 2 seconds
			$redirect = $_SESSION['redirect_after_login'] ?? '/';
			unset( $_SESSION['redirect_after_login'] );
		} else {
			$error = 'Failed to set password. Please try again.';
		}
	}
}

// Output page
echo cota_page_header();
?>

<div class="cota-setup-password-container" style="max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
	<h2 style="text-align: center; margin-bottom: 20px;">Set Up Your Password</h2>
	
	<?php if ( $success ) : ?>
		<div style="padding: 12px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
			<strong>Success!</strong> Your password has been set. Redirecting...
		</div>
		<script>
			setTimeout(function() {
				window.location.href = '<?php echo htmlspecialchars( $redirect ?? '/' ); ?>';
			}, 2000);
		</script>
	<?php else : ?>
		
		<div style="margin-bottom: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
			<p style="margin: 0;"><strong>Welcome!</strong></p>
			<p style="margin: 5px 0 0 0; font-size: 14px;">
				Setting up password for: <strong><?php echo htmlspecialchars( $email ); ?></strong>
			</p>
			<?php if ( isset( $member['first_name'] ) ) : ?>
				<p style="margin: 5px 0 0 0; font-size: 14px;">
					Member: <?php echo htmlspecialchars( $member['first_name'] . ' ' . ( $member['last_name'] ?? '' ) ); ?>
				</p>
			<?php endif; ?>
		</div>
		
		<?php if ( ! empty( $error ) ) : ?>
			<div style="padding: 12px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">
				<?php echo htmlspecialchars( $error ); ?>
			</div>
		<?php endif; ?>
		
		<form method="post" action="">
			<input type="hidden" name="email" value="<?php echo htmlspecialchars( $email ); ?>">
			
			<div style="margin-bottom: 15px;">
				<label for="password" style="display: block; margin-bottom: 5px; font-weight: bold;">Password:</label>
				<input 
					type="password" 
					id="password" 
					name="password" 
					required
					minlength="8"
					autofocus
					style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
				>
				<small style="color: #666; font-size: 12px;">Must be at least 8 characters long</small>
			</div>
			
			<div style="margin-bottom: 20px;">
				<label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: bold;">Confirm Password:</label>
				<input 
					type="password" 
					id="confirm_password" 
					name="confirm_password" 
					required
					minlength="8"
					style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;"
				>
			</div>
			
			<button 
				type="submit" 
				name="setup_password" 
				value="1"
				style="width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;"
			>
				Set Password
			</button>
		</form>
		
		<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;">
			<a href="/app-includes/login.php" style="color: #007bff; text-decoration: none;">Back to Login</a>
		</div>
		
	<?php endif; ?>
</div>

</body>
</html>

<?php
$cota_db->close_connection();
?>

