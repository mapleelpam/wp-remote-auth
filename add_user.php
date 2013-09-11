<?php

include_once('../../../wp-load.php');
require_once("constants.php");

global $wpdb;

/*
$user_name		= $_POST['user_name'];
$user_email		= $_POST['user_email'];
$user_id		= username_exists( $user_name );

$website		= $_POST['website'];
$first_name		= $_POST['first_name'];
$last_name		= $_POST['last_name'];
 */

function send_email($user_email, $uuid){
	$to      = $user_email;
	$subject = 'VMap - First Login and Setup Password';
	$message = "hello, please login and setup password here: http://auth.nctucs.net/wp-content/plugins/auth/register.php?uuid=$uuid";
	$headers = 'From: webmaster@vmap.com' . "\r\n" .
		    'Reply-To: webmaster@vmap.com' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();

	mail($to, $subject, $message, $headers);

	echo "mail send and done.";
	echo "<script language=javascript> alert(\"Done.\");</scrip t>";
}

function insert_uuid_to_table($user_email, $uuid){
	// add user
	global $wpdb;

	$current_time = current_time('mysql');
	$expire_time_date = date_add(date_create($current_time),date_interval_create_from_date_string(LENGTH_OF_EXIPRE_TIME));
	$expire_time = date_format($expire_time_date,"Y-m-d H:i:s");

	echo "current_time >> $current_time</br>";
	echo "expire_time >> $expire_time</br>";

	$wpdb->insert( TABLE_UUID,
		array( 'time'	=> $current_time,
		'expire_time'	=> $expire_time,
		'user_email'	=> $user_email,
		'uuid'			=> $uuid
	) );
}

//
function custom_add_user($user_name, $user_email, $website, $first_name, $last_name, $email_notification){

	$user_id = username_exists( $user_name );

	if ( !$user_id and email_exists($user_email) == false ) {
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		$user_id = wp_create_user( $user_name, $random_password, $user_email );
	} else {
		$random_password = __('User already exists.  Password inherited.');
	}

	// update more detail settings
	wp_update_user( array ('ID'			=> $user_id,
						   'first_name'	=> $first_name,
						   'last_name'	=> $last_name,
						   'user_url'	=> $website) ) ;

	// email
	$uuid = uniqid();
	echo "uuid >> " . $uuid . "</br>";
	insert_uuid_to_table($user_email, $uuid);
	if ($email_notification == "true")	send_email($user_email, $uuid);

	// output result
	echo "user_name >> " . $user_name . "<br/>";
	echo "user_mail >> " . $user_email . "<br/>";
	echo "user_id >> " . $user_id . "<br/>";
	echo "random_password >> " . $random_password . "</br>";

}

?>
