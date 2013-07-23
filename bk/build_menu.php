<?php

global $plugin_name, $table_name, $wpdb;
$plugin_name	= "auth";
$table_name		= $wpdb->prefix . $plugin_name;

function build_menu() {
	// Hook for adding admin menus
	add_action('admin_menu', 'auth_add_page');
}

// action function for above hook
function auth_add_page() {
	// Add a new submenu under Settings:
	add_options_page(__('Auth Settings','auth'), __('Auth Settings','auth'), 'manage_options', 'authsettings', 'settings_page');

	// Add a new submenu under Tools:
	//add_management_page( __('Test Tools','auth'), __('Test Tools','auth'), 'manage_options', 'testtools', 'tools_page');

	// Add a new top-level menu (ill-advised):
	//add_menu_page(__('Auth','auth'), __('Auth','auth'), 'manage_options', 'top-level-handle', 'toplevel_page', '', '', 7 );
	add_menu_page(__('Auth','auth'), __('Auth','auth'), 'read', 'top-level-handle', 'toplevel_page', plugins_url( 'images/icon.png', __FILE__ ), 6  );

	// Add a submenu to the custom top-level menu:
	add_submenu_page('top-level-handle', __('[Personal] Device List','auth'), __('[Personal] Device List','auth'), 'read', 'sub-page', 'device_list_personal');

	// Add a second submenu to the custom top-level menu:
	add_submenu_page('top-level-handle', __('[Admin] Device List','auth'), __('[Admin] Device List','activate_plugins'), 'manage_options', 'sub-page2', 'device_list_admin');
}

// settings_page() displays the page content for the Test settings submenu
function settings_page() {
	echo "<h2>" . __( 'Auth - Settings', 'auth' ) . "</h2>";
}

// tools_page() displays the page content for the Test Tools submenu
/*
function tools_page() {
	echo "<h2>" . __( 'Test Tools', 'auth' ) . "</h2>";
}
 */

// toplevel_page() displays the page content for the custom Test Toplevel menu
function toplevel_page() {

	global $user_email;

	echo "<h2>" . __( 'Auth - Introduction', 'auth' ) . "</h2>";
	echo "<p>In this page, we can write some introductions.</p>";

	echo "hi, $user_email";
}

// device_list() displays the page content for the first submenu
// of the custom Test Toplevel menu
function device_list_personal() {
	echo "<h2>" . __( 'Auth - Device List', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	echo '<form action="../wp-content/plugins/auth/add.php">
		<input type="hidden" name="user_email" value="'.$user_email.'"/>
		<input placeholder="Device String" name="device_str" type="text"/>
		<input type="submit" value="add"/></form>';

	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM $table_name
		WHERE user_email = '$user_email'
		"
	);

	echo '<table border="1" >';

	echo "<tr>";
	echo "<td>Time</td>";
	echo "<td>User Email</td>";
	echo "<td>Device String</td>";
	echo '<td>Delete</td>';
	echo "</tr>";

	foreach ($rows as $row) {
		echo "<tr>";

		echo "<td>";
		echo $row -> time;
		echo "</td>";

		echo "<td>";
		echo $row -> user_email;
		echo "</td>";

		echo "<td>";
		echo $row -> device_str;
		echo "</td>";

		echo '<td><form action="../wp-content/plugins/auth/delete.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="delete"/></form></td>';

		echo "</tr>";
	}

}

function device_list_admin(){
	echo "<h2>" . __( 'Auth - Device List', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	echo '<form action="../wp-content/plugins/auth/add_admin.php">
		<input placeholder="user email" name="user_email" type="text"/>
		<input placeholder="Device String" name="device_str" type="text"/>
		<input type="submit" value="add"/></form>';

	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM $table_name
		"
	);

	echo '<table border="1" >';

	echo "<tr>";
	echo "<td>Time</td>";
	echo "<td>User Email</td>";
	echo "<td>Device String</td>";
	echo '<td>Delete</td>';
	echo "</tr>";

	foreach ($rows as $row) {
		echo "<tr>";

		echo "<td>";
		echo $row -> time;
		echo "</td>";

		echo "<td>";
		echo $row -> user_email;
		echo "</td>";

		echo "<td>";
		echo $row -> device_str;
		echo "</td>";

		echo '<td><form action="../wp-content/plugins/auth/delete.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="delete"/></form></td>';

		echo "</tr>";
	}
}

?>
