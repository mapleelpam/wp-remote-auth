<?php

/**
 * Custom Login Pages Modified From Original WordPress Login Page
 *
 * This code is simplified from the original wordpress "wp-login.php"
 * for custom usage. Receives the username(log) and password(pwd) using
 * POST, and print out SUCCESS or other error messages.
 *
 * heron 2013
 */

/* Debug */
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//print_r($_POST);

/* Include WP code */
//require( dirname(__FILE__) . '/wp-load.php' );
require( '../../../wp-load.php' );

/* Redirect to https login if forced to use SSL */
if ( force_ssl_admin() && ! is_ssl() ) {
	if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
		wp_redirect( set_url_scheme( $_SERVER['REQUEST_URI'], 'https' ) );
		exit();
	} else {
		wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		exit();
	}
}

/* Header Part */
function login_header($title = 'Log In', $message = '', $wp_error = '') {
	global $error, $current_site, $action;

	// error
	if ( empty($wp_error) )	$wp_error = new WP_Error();

	do_action( 'login_enqueue_scripts' );
	do_action( 'login_head' );

	// output message, error, wp_error
	echo $wp_error->get_error_code()."\n";
}

/* Footer Part */
function login_footer($input_id = '') {
	do_action('login_footer');
}

/*
 * Main
 */

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
$errors = new WP_Error();

// validate action so as to default to the login screen
if ( !in_array( $action, array( 'logout', 'login' ), true ) && false === has_filter( 'login_form_' . $action ) )
	$action = 'login';

nocache_headers();

header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));

if ( defined( 'RELOCATE' ) && RELOCATE ) { // Move flag is set
	if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

	$url = dirname( set_url_scheme( 'http://' .  $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ) );
	if ( $url != get_option( 'siteurl' ) )
		update_option( 'siteurl', $url );
}

// allow plugins to override the default actions, and to add extra actions if they want
do_action( 'login_init' );
do_action( 'login_form_' . $action );

$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
switch ($action) {

case 'logout' :
	wp_logout();
	echo "Logged out\n";
	exit();
	break;

case 'login' :
default:
	$secure_cookie = '';
	$customize_login = isset( $_REQUEST['customize-login'] );
	if ( $customize_login )
		wp_enqueue_script( 'customize-base' );

	// If the user wants ssl but the session is not ssl, force a secure cookie.
	if ( !empty($_POST['log']) && !force_ssl_admin() ) {
		$user_name = sanitize_user($_POST['log']);
		if ( $user = get_user_by('login', $user_name) ) {
			if ( get_user_option('use_ssl', $user->ID) ) {
				$secure_cookie = true;
				force_ssl_admin(true);
			}
		}
	}

	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = $_REQUEST['redirect_to'];
		// Redirect to https if user wants ssl
		if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
			$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
	} else {
		$redirect_to = admin_url();
	}

	$reauth = empty($_REQUEST['reauth']) ? false : true;

	// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
	// cookie and redirect back to the referring non-secure admin page. This allows logins to always be POSTed over SSL while allowing the user to choose visiting
	// the admin via http or https.
	if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
		$secure_cookie = false;

	$user = wp_signon('', $secure_cookie);

	if ( !is_wp_error($user) && !$reauth ) {
		// if sucees
		echo "SUCCESS\n";		// *********************** //
		exit();
	}

	$errors = $user;
	// Clear errors if loggedout is set.
	if ( !empty($_GET['loggedout']) || $reauth )
		$errors = new WP_Error();

	// If cookies are disabled we can't log in even with a valid user+pass
	if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
		$errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

	// Some parts of this script use the main login form to display a message
	if ( isset($_GET['loggedout']) && true == $_GET['loggedout'] ){
		$errors->add('loggedout', __('Logged out'), 'message');
	}

	// Clear any stale cookies.
	if ( $reauth )	wp_clear_auth_cookie();


	// output result
	login_header(__('Log In'), '', $errors);	// header part
	login_footer();								// footer part
	break;

} // end action switch

// end
?>
