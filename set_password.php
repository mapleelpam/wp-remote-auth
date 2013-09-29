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

// not matching
if ($_POST["password"] != $_POST["confirm_password"]) {
    print '<script type="text/javascript">'; 
    print 'window.alert("Passwords don\'t match, please check again.");';
    print 'window.location.href="../../../wp-content/plugins/auth/register.php?uuid='.$uuid.'";';
    print '</script>';  
    exit(0);
}

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
$expire_time = "";
foreach ($rows as $row) {
	$user_email = $row->user_email;
    $expire_time = $row->expire_time;
}

// check if it's valid
//echo "expire time >> ".$expire_time;
//exit();


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
echo "<script language=javascript> alert(\"Done. Welcome.\");";
echo 'window.location.href="../../../?page_id=8";</script>';
//header('Location: ../../../?page_id=8');
?>
