<?php

global $plugin_name, $table_name, $wpdb;

function build_menu() {
	// Hook for adding admin menus
	add_action('admin_menu', 'auth_add_page');
}

function auth_add_page() {
	// Add a new submenu under Settings:
	add_options_page(__('Auth Settings','auth'), __('Auth Settings','auth'), 'manage_options', 'authsettings', 'settings_page');

	// Add a new top-level menu (ill-advised):
	add_menu_page(__('Auth','auth'), __('Auth','auth'), 'read', 'top-level-handle', 'toplevel_page', plugins_url( 'images/icon.png', __FILE__ ), 6  );

	// Add a submenu to the custom top-level menu:
	add_submenu_page('top-level-handle', __('[Personal] Device List','auth'), __('[Personal] Device List','auth'), 'read', 'sub-page', 'device_list_personal');

	// Add a second submenu to the custom top-level menu:
	add_submenu_page('top-level-handle', __('[Admin] Device List','auth'), __('[Admin] Device List','activate_plugins'), 'manage_options', 'sub-page2', 'device_list_admin');
}

// Setting Page Layout
function settings_page() {
	echo "<h2>" . __( 'Auth - Settings', 'auth' ) . "</h2>";
}

// Top Level Page Layout
function toplevel_page() {

	global $user_email;

	echo "<h2>" . __( 'Auth', 'auth' ) . "</h2>";

	echo "<h3>Introduction</h3>";
	echo "<p>This plugin is for Software Authication.</p>";
	echo '<p>* In order to force users login by using email, <a href="http://wordpress.org/plugins/force-email-login/">this plugin</a> is recommended.</p>';

}

// Device List for General Users Page Layout
function device_list_personal() {
	echo "<h2>" . __( 'Auth - Device List - for General Users', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	// add new form
	echo '<form action="../wp-content/plugins/auth/add.php">
		<input type="hidden" name="user_email" value="'.$user_email.'"/>
		<input placeholder="Device String" name="device_str" type="text"/>
		<input type="submit" value="add"/></form>';

	// display list
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

		// delete buttom
		echo '<td><form action="../wp-content/plugins/auth/delete.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="delete"/></form></td>';

		echo "</tr>";
	}

	//
}

// Device List for Administrators Page Layout
function device_list_admin(){
	echo "<h2>" . __( 'Auth - Device List - for Administrators', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	// insert new form
	echo '<form action="../wp-content/plugins/auth/add_admin.php">
		<input placeholder="User Email" name="user_email" type="text"/>
		<input placeholder="Device String" name="device_str" type="text"/>
		<input type="submit" value="add"/></form>';

	// display all
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

		echo '<td><form action="../wp-content/plugins/auth/delete_admin.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="delete"/></form></td>';

		echo "</tr>";
	}
}

?>
