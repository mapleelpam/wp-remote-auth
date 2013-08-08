<?php

require_once("constants.php");
require_once( '../../../wp-blog-header.php');
/* variable */
global $wpdb;

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
	echo "<p>The number of the devices is full. You are not able to add/enable any more.</p>";
	echo "<p>Please disable or delete any device in your list then enble another device.</p>";
	echo '<p><a href="../../../wp-admin/admin.php?page=sub-page">Go Back</a></p>';
} else {

	$wpdb->query( 
		$wpdb->prepare( 
			"
			UPDATE ".TABLE_DEVICE_STR."
			SET disable=0
			WHERE id = '%s'
			",
			$_GET['id'] 
		)
	);

	header('Location: ../../../wp-admin/admin.php?page=sub-page');
}
?>
