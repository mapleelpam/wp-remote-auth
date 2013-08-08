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

// *** add login IP

/* Debug */
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
//print_r($_POST);

/* Include WP code */
//require( dirname(__FILE__) . '/wp-load.php' );
require( '../../../wp-load.php' );
require_once( 'constants.php' );

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
	// turn off to clean the output
	//echo $wp_error->get_error_code()."\n";
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
		global $wpdb;
		// if sucees
		$device_str = $_POST['device_str'];
		$user_email = $_POST['log'];
		$mac = get_mac_from_device_str($device_str);

		//echo TABLE_DEVICE_STR;
		//print_r($_POST);
		$rows = $wpdb->get_results( 
			"
			SELECT *
			FROM ".TABLE_DEVICE_STR."
			WHERE user_email = '$user_email'
			AND mac = '$mac'
			AND userdelete = 0
			"
		);

		if (count($rows)==0){
			// check if is over the limit
			$existing = $wpdb->get_results( 
				"
				SELECT *
				FROM ".TABLE_DEVICE_STR."
				WHERE user_email = '$user_email'
				AND userdelete = 0
				AND disable = 0
				"
			);
			if (count($existing) >= MAX_DEVICES_NUMBER){
				// #5, if the number of devices if full already for this user
				echo json_encode(array("5", "Number of Devices is Full", ""));
			} else {
				// #1. create new device
				add_new_device($user_email, $device_str);
				$response_str = response_str($device_str);
				echo json_encode(array("1", "New Device Login", $response_str));
			}
			exit();
		}

		foreach ($rows as $row){
			$disable	= $row->disable;
			$userdelete	= $row->userdelete;

			if ($disable == 1){
				// #4, fail, device disabled
				// do nothing
				echo json_encode(array("4", "Failed, Device Disabled", ""));
			}
			else {
				// #2, success login using known device
				// just login
				$response_str = response_str($device_str);
				echo json_encode(array("2", "Succes Login with Known Device", $response_str));
			}
			exit();
		}
	}
	else {
		// #0. fail to Login
		echo json_encode(array("0", "Wrong Username or Password", ""));
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
	// login_footer();							// footer part, no need to simple c++ usage
	break;

} // end action switch


function add_new_device($user_email, $device_str){
	global $wpdb;
	$mac = get_mac_from_device_str($device_str);

	$wpdb->insert( TABLE_DEVICE_STR,
		array( 'time'	=> current_time('mysql'),
		'user_email'	=> $user_email,
		'device_str'	=> $device_str,
		'mac'			=> $mac,
		'disable'		=> 0,
		'userdelete'	=> 0
	));
}

function response_str($device_str){
	exec("./LicManager/DeviceInfoManager/device_info_manager -l $device_str", $response);
	return "$response[0]";
}

function get_mac_from_device_str($device_str){
	exec("./LicManager/DeviceInfoManager/device_info_manager -d $device_str", $response);
	return $response[1];
}

// end
?>
