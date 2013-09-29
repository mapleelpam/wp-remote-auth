<?php
require_once("../../../wp-blog-header.php");
require_once("constants.php");

// user_email check
// 1. if the user is in other reseller group or not?
// 		yes: do nothing
// 		no:	 continue
// 2. if the user is in the wordpress user list or not
// 		yes: send email notify update	
// 		no:  send email ask to regist

global $wpdb;
$reseller_group	= $_GET['reseller_group'];
$disty_email	= $_GET['disty_email'];
$user_email		= $_GET['user_email'];
$email_notification = $_GET['email_notification'];

function user_exist_by_email($user_email){
	global $wpdb;
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE user_email = '$user_email'"));
	return $count;
}

function user_exist_in_reseller_group_check(){
	global $wpdb, $user_email;
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_USER_STATUS."
		WHERE user_email = '$user_email'
		"
	);
	if (count($rows) != 0) {
		// error
		echo "<p>$user_email is already exsisted in other reseller group</p>";
		echo '<p><a href="../../../wp-admin/admin.php?page=reseller">Go Back</a></p>';
		exit(0);
	}
}

function email_updated($user_email){
	$to      = $user_email;
	$subject = 'VMap - Your Account Is Updated With Limited Number of Authorized Devices';
	$message = 'hello, please visit this page to setup devices: http://auth.nctucs.net/wp-admin/admin.php?page=device-list';
	$headers = 'From: webmaster@vmap.com' . "\r\n" .
		    'Reply-To: webmaster@vmap.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);
}

function email_to_register($user_email){
	$to      = $user_email;
	$subject = 'VMap - Please Regist You Account Here';
	$message = 'hello, please visit this page to register: http://auth.nctucs.net/wp-login.php?action=register';
	$headers = 'From: webmaster@vmap.com' . "\r\n" .
		    'Reply-To: webmaster@vmap.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);
}

function email_noti($user_email, $email_notification) {
	if ($email_notification != "true")	return;
	echo "send";
	if (user_exist_by_email($user_email)) {
		email_updated($user_email);
	}
	else {
		email_to_register($user_email);
	}
}

user_exist_in_reseller_group_check();

// check if the reseller group exists
$rows = $wpdb->get_results( 
	"
	SELECT *
	FROM ".TABLE_RESELLER_GROUPS."
	WHERE reseller_group = '$reseller_group'
	"
);
if (count($rows) != 0) {
	//
	$rows2 = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_RESELLER_GROUPS."
		WHERE reseller_group = '$reseller_group'
		AND disty_email != '$disty_email'
		"
	);
	if (count($rows2) == 0) {
		// add user
		$wpdb->insert( TABLE_USER_STATUS,
			array( 'time'		=> current_time('mysql'),
			'user_email'		=> $user_email,
			'reseller_group'	=> $reseller_group,
			'status'			=> DEFAULT_STATUS
		) );
		email_noti($user_email, $email_notification);
		header('Location: ../../../wp-admin/admin.php?page=reseller');
	}
	else {
		// error
		echo "<p>$reseller_group is already exsisted in other disty's reseller group</p>";
		echo '<p><a href="../../../wp-admin/admin.php?page=reseller">Go Back</a></p>';
	}
}
else {
	// add group
	$wpdb->insert( TABLE_RESELLER_GROUPS,
		array( 'time'		=> current_time('mysql'),
		'disty_email'		=> $disty_email,
		'reseller_group'	=> $reseller_group
	) );

	// add user
	$wpdb->insert( TABLE_USER_STATUS,
		array( 'time'		=> current_time('mysql'),
		'user_email'		=> $user_email,
		'reseller_group'	=> $reseller_group,
		'status'			=> DEFAULT_STATUS
	) );
	email_noti($user_email, $email_notification);
	header('Location: ../../../wp-admin/admin.php?page=reseller');
}

?>
