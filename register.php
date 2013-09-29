<?php
include_once('../../../wp-load.php');
require_once("constants.php");

global $wpdb, $user_email;
$uuid = $_GET['uuid'];

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

echo "reset your password:";

?>
<form method="post" action="set_password.php">
    <label>New Password</label>
	<input type="password" name="password"><br>
    <label>Retype Password</label>
	<input type="password" name="confirm_password"><br>
	<input type="hidden" name="uuid" value="<?php echo $uuid; ?>"><br>
	<input type="submit" name="submit" value="Submit"><br>
</form>
<?
?>
