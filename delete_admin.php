<?php
print_r($_GET);
echo $_GET["id"]."</br>";

require_once( '../../../wp-blog-header.php');
/* variable */
global $wpdb;
$plugin_name	= "auth";
$table_name		= $wpdb->prefix . $plugin_name;

$sql_command = "DELETE FROM $table_name WHERE id = ".$_GET['id'].";";
echo $sql_command;
$wpdb->query( 
	$wpdb->prepare( 
		"
		DELETE FROM $table_name
		WHERE id = '%s'
		",
		$_GET['id'] 
	)
);
header('Location: ../../../wp-admin/admin.php?page=sub-page2');
?>
