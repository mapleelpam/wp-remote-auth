<?php

global $plugin_name, $table_name, $wpdb;

function build_menu() {
	// Hook for adding admin menus
	add_action('admin_menu', 'auth_add_page');
}

function auth_add_page() {
	// Add a new submenu under Settings:
	/*
	add_options_page(__('Auth Settings','auth'),
		__('Auth Settings','auth'),
		'edit_posts',
		'authsettings',
		'settings_page'
	);
	 */

	// Add a new top-level menu (ill-advised):
	add_menu_page(__('VMap','auth'),
		__('VMap','auth'),
		'read',
		'vmap-auth-intro',
		'vmap_auth_intro_layout',
		plugins_url( 'images/icon.png', __FILE__ ),
		6
	);

	add_submenu_page('vmap-auth-intro',
		__('Device List','auth'),
		__('Device List','auth'),
		'read',
		'device-list',
		'device_list_layout'
	);

	add_submenu_page('vmap-auth-intro',
		__('Management Page','auth'),
		__('Management Page','activate_plugins'),
		'edit_posts',
		'reseller',
		'reseller_layout'
	);

	add_submenu_page('vmap-auth-intro',
		__('Add New Customer','auth'),
		__('Add New Customer','activate_plugins'),
		'edit_posts',
		'add_new_customer',
		'add_new_customer'
	);

	// 
	add_submenu_page('vmap-auth-intro',
		__('Administration','auth'),
		__('Administration','activate_plugins'),
		'update_core',
		'admin',
		'admin_layout'
	);
}

// Setting Page Layout
/*
function settings_page() {
	echo "<h2>" . __( 'Auth - Settings', 'auth' ) . "</h2>";
}
 */

// Top Level Page Layout
function vmap_auth_intro_layout() {

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

}

// Device List for General Users Page Layout
function device_list_layout() {
	echo "<h2>" . __( 'Auth - Device List - for Customers', 'auth' ) . "</h2>";

	global $wpdb, $user_email;

	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_USER_STATUS."
		WHERE user_email = '$user_email'
		"
	);
	if (count($rows) == 0) {
		// error
		echo "<p>your account is invalid yet</p>";
		return;
	}

	// display list
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_DEVICE_STR."
		WHERE user_email = '$user_email'
		AND userdelete = 0
		"
	);

	if(count($rows) == 0) {
		echo "<p>No Device Info / Never Register any machine</p>";
	}
	else {

		echo '<table border="1" >';

		echo "<tr>";
		echo "<td>Time</td>";
		echo "<td>User Email</td>";
		echo "<td>MAC Address</td>";
		echo '<td>Disable?</td>';
		echo '<td></td>';
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

			echo '<td><form action="../wp-content/plugins/auth/disable.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="Disable"/></form></td>';
			echo '<td><form action="../wp-content/plugins/auth/enable.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="Enable"/></form></td>';
			echo '<td><form action="../wp-content/plugins/auth/userdelete.php"><input type="hidden" name="id" value="'.$row->id.'"/><input type="submit" value="Delete"/></form></td>';

			echo "</tr>";
		}
		echo "</table>";
	}

	//
}

// Device List for Administrators Page Layout
function reseller_layout(){
	echo "<h2>" . __( 'Auth - Device List - for Resellers', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	// insert new form
	echo '<form action="../wp-content/plugins/auth/add_reseller_group.php">
		<input type="hidden" name="disty_email" value="'.$user_email.'"/>
		<input placeholder="User Email" name="user_email" type="text"/>
		<input placeholder="Reseller Group" name="reseller_group" type="text"/>
		<label><input type="checkbox" name="email_notification" checked value="true" />Email Notification?</label>
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
			echo "<td>Password Set?</td>";
			echo "</tr>";

			foreach ($user_list as $user) {
				echo "<tr>";

				echo "<td>".$user->user_email."</td>";
				echo "<td>".$user->status."</td>";

                echo "<td>-</td><td>-</td><td>-</td>";
                $uuid_list = $wpdb->get_results( 
                    "
                    SELECT *
                    FROM ".TABLE_UUID."
                    WHERE user_email = '$user->user_email'
                    "
                );
                if(count($uuid_list) == 0)  echo "<td>YES</td>";
                else                        echo "<td>NO</td>";
                echo "</tr>";


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
                    echo "<td></td>";
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



function admin_layout(){
	echo "<h2>" . __( 'Auth - Device List - for Admins', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;

	// display all
	$rows = $wpdb->get_results( 
		"
		SELECT *
		FROM ".TABLE_RESELLER_GROUPS."
		"
	);


	echo "<p>number of all groups: ".count($rows)."</p>";

	foreach ($rows as $row) {

		$reseller_group = $row -> reseller_group;
		$disty_email = $row -> disty_email;

		echo "<h2>".$reseller_group."</h2><h3>(".$disty_email.")</h3>";

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

                echo "<td>-</td><td>-</td><td>-</td>";

                echo "</tr>";


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

function add_new_customer(){
	echo "<h2>" . __( 'Auth - Add New Customer - for Reseller', 'auth' ) . "</h2>";

	global $table_name, $wpdb, $user_email;
	// insert new form
?>
	<form action="../wp-content/plugins/auth/add_reseller_user.php" method="POST">
		<input type="hidden" name="disty_email" value="<? echo $user_email;?>"/>
		<table border="1">
			<tr>
				<td>Reseller Group</td>
				<td><input placeholder="Reseller Group" name="reseller_group" type="text"/></td>
			</tr>
			<tr>
				<td>User Email</td>
				<td><input placeholder="example@example.com" name="user_email" type="text"/></td>
			</tr>
			<tr>
				<td>User Name</td>
				<td><input placeholder="example1234" name="user_name" type="text"/></td>
			</tr>
			<tr>
				<td>First Name</td>
				<td><input placeholder="Steve" name="first_name" type="text"/></td>
			</tr>
			<tr>
				<td>Last Name</td>
				<td><input placeholder="Jobs" name="last_name" type="text"/></td>
			</tr>
			<tr>
				<td>Website</td>
				<td><input placeholder="example.com" name="website" type="text"/></td>
			</tr>
			<tr>
				<td>Email Notification?</td>
				<td><input type="checkbox" name="email_notification" checked value="true" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="add"/></td>
			</tr>

		</table>
	</form>
<?php

}
?>
