<?php

require_once("constants.php");
require_once( '../../../wp-blog-header.php');
/* variable */
global $wpdb;

$wpdb->query( 
	$wpdb->prepare( 
		"
		UPDATE ".TABLE_DEVICE_STR."
		SET disable=1
		WHERE id = '%s'
		",
		$_GET['id'] 
	)
);

header('Location: ../../../wp-admin/admin.php?page=device-list');
?>
