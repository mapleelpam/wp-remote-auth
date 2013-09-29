<?php
require_once( '../../../wp-blog-header.php');

$LIMIT_DEVICE_NUMBER = 3;

global $wpdb;
$plugin_name	= "auth";
$table_name		= $wpdb->prefix . $plugin_name;
$user_table		= $wpdb->users;

// check if the user email exists
$input_user_email = $_GET['user_email'];
$rows = $wpdb->get_results( 
	"
	SELECT *
	FROM $user_table
	WHERE user_email = '$input_user_email'
	"
);

//
if (sizeof($rows) == 0) {
	echo "<p>The user(".$_GET['user_email'].") is not exist in our user list.</p>";
	echo '<p><a href="../../../wp-admin/admin.php?page=reseller">Go Back</a></p>';
}
else {

	// check if it's over 3 device so for or not
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM $table_name
		WHERE user_email = '$input_user_email'
		"
	);

	if (sizeof($rows) >= $LIMIT_DEVICE_NUMBER) {
		echo "<p>It's already over $LIMIT_DEVICE_NUMBER devices, you can't add more!</p>";
		echo '<p><a href="../../../wp-admin/admin.php?page=reseller">Go Back</a></p>';
	}

	else {
		$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'user_email' => $_GET['user_email'], 'device_str' => $_GET['device_str'] ) );
		header('Location: ../../../wp-admin/admin.php?page=reseller');
	}

}

?>
