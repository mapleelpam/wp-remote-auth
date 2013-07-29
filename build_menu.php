<?php

global $plugin_name, $table_name, $wpdb;

function build_menu() {
	// Hook for adding admin menus
	add_action('admin_menu', 'auth_add_page');
}

function auth_add_page() {
	// Add a new submenu under Settings:
	add_options_page(__('Auth Settings','auth'),
		__('Auth Settings','auth'),
		'manage_options',
		'authsettings',
		'settings_page'
	);

	// Add a new top-level menu (ill-advised):
	add_menu_page(__('Auth','auth'),
		__('Auth','auth'),
		'read',
		'top-level-handle',
		'toplevel_page',
		plugins_url( 'images/icon.png', __FILE__ ),
		6
	);

	// Add a submenu to the custom top-level menu:
	add_submenu_page('top-level-handle',
		__('[Personal] Device List','auth'),
		__('[Personal] Device List','auth'),
		'read',
		'sub-page',
		'device_list_personal'
	);

	// Add a second submenu to the custom top-level menu:
	add_submenu_page('top-level-handle',
		__('[Admin] Device List','auth'),
		__('[Admin] Device List','activate_plugins'),
		'manage_options',
		'sub-page2',
		'device_list_admin'
	);
}

// Setting Page Layout
function settings_page() {
	echo "<h2>" . __( 'Auth - Settings', 'auth' ) . "</h2>";
}

// Top Level Page Layout
function toplevel_page() {

	global $wpdb, $user_email;

	echo "<h2>" . __( 'Auth', 'auth' ) . "</h2>";

	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_USER_STATUS."
		WHERE user_email = '$user_email'
		"
	);
	echo "<h3>My Reseller: ";
	foreach ($rows as $row) {
		echo $row->reseller_group . "\t";
	}
	if (count($rows) == 0) {
		echo "None";
	}
	echo "</h3>";

	echo "<h3>Introduction</h3>";
	echo "<p>This plugin is for Software Authication.</p>";
	echo '<p>* In order to force users login by using email, <a href="http://wordpress.org/plugins/force-email-login/">this plugin</a> is recommended.</p>';

}

// Device List for General Users Page Layout
function device_list_personal() {
	echo "<h2>" . __( 'Auth - Device List - for General Users', 'auth' ) . "</h2>";

	global $wpdb, $user_email;

	// display list
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_DEVICE_STR."
		WHERE user_email = '$user_email'
		AND userdelete = 0
		"
	);

	echo '<table border="1" >';

	echo "<tr>";
	echo "<td>Time</td>";
	echo "<td>User Email</td>";
	echo "<td>MAC Address</td>";
	echo '<td>Disable?</td>';
	echo '<td></td>';
	echo '<td></td>';
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
		echo $row -> mac;
		echo "</td>";

		echo "<td>";
		if ($row -> disable == 1)
			echo "YES";
		else
			echo "NO";
		echo "</td>";

		echo '<td><form action="../wp-content/plugins/auth/disable.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="Disable"/></form></td>';	// userdelete
		echo '<td><form action="../wp-content/plugins/auth/userdelete.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="Delete"/></form></td>';	// userdelete

		echo "</tr>";
	}
	echo "</table>";

	//
}

// Device List for Administrators Page Layout
function device_list_admin(){
	echo "<h2>" . __( 'Auth - Device List - for Administrators', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	// insert new form
	echo '<form action="../wp-content/plugins/auth/add_reseller_group.php">
		<input type="hidden" name="disty_email" value="'.$user_email.'"/>
		<input placeholder="User Email" name="user_email" type="text"/>
		<input placeholder="Reseller Group" name="reseller_group" type="text"/>
		<input type="submit" value="add"/></form>';

	// display all
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_RESELLER_GROUPS."
		WHERE disty_email = '$user_email'
		"
	);


	echo "<h3>My Reseller Group List</h3>";
	echo "<p>number of groups: ".count($rows)."</p>";

	foreach ($rows as $row) {

		$reseller_group = $row -> reseller_group;

		echo "<h2>".$reseller_group."</h2>";

		$user_list = $wpdb->get_results( 
			"
			SELECT *
			FROM ".TABLE_USER_STATUS."
			WHERE reseller_group = '$reseller_group'
			"
		);

		// build table for single reseller group
		if (count($user_list) != 0) {
			echo '<table border="1" >';
			echo "<tr>";
			echo "<td>User Email</td>";
			echo '<td>Status</td>';
			echo "<td>MAC</td>";
			echo "<td>Disable?</td>";
			echo "<td>User Delete?</td>";
			echo "</tr>";

			foreach ($user_list as $user) {
				echo "<tr>";

				echo "<td>".$user->user_email."</td>";
				echo "<td>".$user->status."</td>";

				echo "<td>-</td><td>-</td><td>-</td></tr>";


				// single device
				$user_email = $user->user_email;
				$device_list = $wpdb->get_results( 
					"
					SELECT *
					FROM ".TABLE_DEVICE_STR."
					WHERE user_email = '$user_email'
					"
				);
				foreach ($device_list as $device) {
					echo "<tr>";
					echo "<td></td><td></td>";
					echo "<td>".$device->mac."</td>";
					if($device->disable==1)		echo "<td>YES</td>";
					else						echo "<td>NO</td>";
					if($device->userdelete==1)	echo "<td>YES</td>";
					else						echo "<td>NO</td>";
					echo "</tr>";
				}
				
			}
			echo "</table>";
		}
		else {
			echo "Empty";
		}
	}

}

?>
