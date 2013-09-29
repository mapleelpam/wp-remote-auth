<?php

require_once("constants.php");
require_once( '../../../wp-blog-header.php');
/* variable */
global $wpdb;

$wpdb->query( 
	$wpdb->prepare( 
		"
		UPDATE ".TABLE_DEVICE_STR."
		SET userdelete=1
		WHERE id = '%s'
		",
		$_POST['id'] 
	)
);

echo "<script>alert('Action Submitted! Please login again.'); location.href='../../../?page_id=48';</script>";
exit;
?>
