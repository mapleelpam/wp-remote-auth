<?php
require_once( '../../../wp-blog-header.php');

$LIMIT_DEVICE_NUMBER = 3;

global $wpdb;
$plugin_name	= "auth";
$table_name		= $wpdb->prefix . $plugin_name;

// check if it's over 3 device so for or not
$rows = $wpdb->get_results( 
	"
	SELECT *
	FROM $table_name
	WHERE user_email = '$user_email'
	"
);

if (sizeof($rows) >= $LIMIT_DEVICE_NUMBER) {
	echo "<p>It's already over $LIMIT_DEVICE_NUMBER devices, you can't add more!</p>";
	echo '<p><a href="../../../wp-admin/admin.php?page=device-list">Go Back</a></p>';
}

else {
	$rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'user_email' => $_GET['user_email'], 'device_str' => $_GET['device_str'] ) );
	header('Location: ../../../wp-admin/admin.php?page=device-list');
}

?>
