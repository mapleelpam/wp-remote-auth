<?php
/*
 * Plugin Name: Remote Auth
 * Plugin URI: http://auth.nctucs.net/
 * Description: Wordpress plugin for Software Authication
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
include('build_menu.php');

/* variable */
global $wpdb, $db_ver, $table_name;

$plugin_name	= "auth";
$db_ver			= "1.0";
$table_name		= $wpdb->prefix . $plugin_name;
$installed_ver	= get_option( "db_ver" );

// sql code for creating table in database
$create_table_sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	user_email text NOT NULL,
	device_str text NOT NULL,
	UNIQUE KEY id (id)
);";


/* functions */
// build the table
function build_table() {
	global $wpdb, $create_table_sql, $db_ver;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $create_table_sql );

	add_option( "db_ver", $db_ver );
}

// hook up
register_activation_hook( __FILE__, 'build_table' );

// whenever loaded
add_action( 'plugins_loaded', 'build_table' );

// build the menu in admin page
build_menu();

?>
