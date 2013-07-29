<?php
require_once("../../../wp-blog-header.php");
require_once("constants.php");

global $wpdb;

$reseller_group	= $_GET['reseller_group'];
$disty_email	= $_GET['disty_email'];
$user_email		= $_GET['user_email'];

//
$rows = $wpdb->get_results( 
	"
	SELECT *
	FROM ".TABLE_RESELLER_GROUPS."
	WHERE reseller_group = '$reseller_group'
	"
);
if (count($rows) != 0) {
	//
	$rows2 = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_RESELLER_GROUPS."
		WHERE reseller_group = '$reseller_group'
		AND disty_email != '$disty_email'
		"
	);
	if (count($rows2) == 0) {
		// add user
		$wpdb->insert( TABLE_USER_STATUS,
			array( 'time'		=> current_time('mysql'),
			'user_email'		=> $user_email,
			'reseller_group'	=> $reseller_group,
			'status'			=> DEFAULT_STATUS
		) );
		header('Location: ../../../wp-admin/admin.php?page=sub-page2');
	}
	else {
		// error
		echo "<p>$reseller_group is already exsisted in other disty's reseller group</p>";
		echo '<p><a href="../../../wp-admin/admin.php?page=sub-page2">Go Back</a></p>';
	}
}
else {
	// add group
	$wpdb->insert( TABLE_RESELLER_GROUPS,
		array( 'time'		=> current_time('mysql'),
		'disty_email'		=> $disty_email,
		'reseller_group'	=> $reseller_group
	) );

	// add user
	$wpdb->insert( TABLE_USER_STATUS,
		array( 'time'		=> current_time('mysql'),
		'user_email'		=> $user_email,
		'reseller_group'	=> $reseller_group,
		'status'			=> DEFAULT_STATUS
	) );
	header('Location: ../../../wp-admin/admin.php?page=sub-page2');
}

?>
