<?php
/*
 * Plugin Name: Remote Auth
 * Plugin URI: http://auth.nctucs.net/
 * Description: Wordpress Plugin for Software Authication
 * Version: 1.0
 * Author: Heron Yang
 * Author URI: http://heron.nctucs.net
 * License: *
 * */

/*
 * The plugin build a table under wordpress, which stores
 * the user's device strings. Then, build the menus on the
 * admin page for users to check their devices strings.
 */


/* includes */
require_once("constants.php");
include("build_menu.php");

/* variable */
global $wpdb, $db_ver;

$db_ver			= "1.0";						// version controll not completed yet
$installed_ver	= get_option( "db_ver" );

// sql code for creating table in database
$create_table_device_str = "CREATE TABLE ".TABLE_DEVICE_STR." (
	`id`				MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
	`time`				DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
	`user_email`		TEXT NOT NULL,
	`device_str`		TEXT NOT NULL,
	`mac`				TEXT NOT NULL,
	`disable`			TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`userdelete`		TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	UNIQUE KEY id (id)	
);";
$create_table_reseller_groups = "CREATE TABLE ".TABLE_RESELLER_GROUPS." (
	`time`				DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
	`disty_email`		TEXT NOT NULL,
	`reseller_group`	TEXT NOT NULL
);";
$create_table_user_status = "CREATE TABLE ".TABLE_USER_STATUS." (
	`id`				MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
	`time`				DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
	`user_email`		TEXT NOT NULL,
	`reseller_group`	TEXT NOT NULL,
	`status`			TINYINT UNSIGNED NOT NULL,
	UNIQUE KEY id (id)
);";


/* functions */
// build the table
function build_table() {
	global $wpdb, $create_table_device_str, $create_table_reseller_groups, $create_table_user_status, $db_ver;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// call mysql comands
	dbDelta( $create_table_device_str );
	dbDelta( $create_table_reseller_groups );
	dbDelta( $create_table_user_status );

	// version
	add_option( "db_ver", $db_ver );
}

// hook up
register_activation_hook( __FILE__, 'build_table' );

// whenever loaded
//add_action( 'plugins_loaded', 'build_table' );

// build the menu in admin page
build_menu();

?>
