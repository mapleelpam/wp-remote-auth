<?php

include_once('../../../wp-load.php');
require_once("constants.php");

// reset password
if(!isset($_POST['submit'])) 
{ 
	echo "error, please stop hacking";
	exit();
}

global $wpdb;
$password = $_POST['password'];
$uuid = $_POST['uuid'];

$rows = $wpdb->get_results( 
	"
	SELECT *
	FROM ".TABLE_UUID."
	WHERE uuid = '$uuid'
	"
);

// check if uuid is valid in table
if (count($rows) == 0) {
	echo "Wrong URL or the account is registered.";
	exit(0);
}

// get the user email
$user_email = "";
foreach ($rows as $row) {
	$user_email = $row->user_email;
}

$user_id = email_exists($user_email);
wp_set_password( $password, $user_id );

// delete the uuid record
$wpdb->query( 
	$wpdb->prepare( 
		"
		DELETE FROM ".TABLE_UUID."
		WHERE uuid = %s
		",
		$uuid 
	)
);

//
echo "<script language=javascript> alert(\"Done. Please login.\");</scrip t>";
header('Location: ../../../wp-admin/admin.php?page=device-list');
?>
