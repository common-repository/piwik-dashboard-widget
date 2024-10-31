<?php

/*
Plugin Name: Piwik Dashboard Widget
Plugin URI: http://blog.vedstudio.com/wordpress-plugins/piwik-dashboard-widget/
Description: Creates a widget on your WordPress Dashboard which summarizes your Piwik statistics
Version: 1.0.1
Author: Rich Collier
Author URI: http://blog.vedstudio.com
*/

function piwik_dashboard_widget_function() {
	
	if(get_option('PIWIK_WIDGET_SITE_ID') == '') {
		echo 'Please <a href="options-general.php?page=piwikwidget">check your settings</a>.';
	} else {

		// Set variables
		$idsite = get_option('PIWIK_WIDGET_SITE_ID');
		$PIWIK_SERVER_ADDR = get_option('PIWIK_WIDGET_SERVER_ADDRESS');
		$PIWIK_DB_USER = get_option('PIWIK_WIDGET_DB_USER');
		$PIWIK_DB_PASSWORD = get_option('PIWIK_WIDGET_DB_PASSWORD');
		$PIWIK_DB_NAME = get_option('PIWIK_WIDGET_DB_NAME');
		$PIWIK_TABLE_PREFIX = get_option('PIWIK_WIDGET_TABLE_PREFIX');
		
		// Connect to the Piwik server and create the connection object
		$Conn = mysqli_connect($PIWIK_SERVER_ADDR, $PIWIK_DB_USER, $PIWIK_DB_PASSWORD);
		
		// Select the proper database
		mysqli_select_db($Conn, $PIWIK_DB_NAME);
		
		// Get the site name from it's ID
		$Query = "SELECT name FROM " . $PIWIK_TABLE_PREFIX . "site WHERE idsite = $idsite";
		$Result = mysqli_query($Conn, $Query);
		while($Row = mysqli_fetch_assoc($Result)) $SiteName = $Row['name'];
		
		// Extract today's date and create the Today variable
		$Query = "SELECT MAX(visit_server_date) FROM " . $PIWIK_TABLE_PREFIX . "log_visit";
		$Result = mysqli_query($Conn, $Query);
		$DateArray = mysqli_fetch_assoc($Result);
		$Today = $DateArray['MAX(visit_server_date)'];
		
		// Extract today's and yesterday's date and create the Yesterday variable
		$Query = "SELECT DISTINCT(visit_server_date) FROM " . $PIWIK_TABLE_PREFIX . "log_visit ORDER BY visit_server_date DESC LIMIT 2";
		$Result = mysqli_query($Conn, $Query);
		while($Row = mysqli_fetch_assoc($Result)) $Yesterday = $Row['visit_server_date'];
		
		// Get the total number of hits
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite";
		$Result = mysqli_query($Conn, $Query);
		$piwik_total_hits = mysqli_num_rows($Result);
		
		// Get the total number of unique visitors
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visitor_returning = 0";
		$Result = mysqli_query($Conn, $Query);
		$piwik_unique_visitors = mysqli_num_rows($Result);
		
		// Get today's hit count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = '$Today'";
		$Result = mysqli_query($Conn, $Query);
		$piwik_todays_hits = mysqli_num_rows($Result);
		
		// Get today's unique visitor count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = '$Today' AND visitor_returning = 0";
		$Result = mysqli_query($Conn, $Query);
		$piwik_todays_unique_visitors = mysqli_num_rows($Result);
		
		// Get yesterday's hit count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = '$Yesterday'";
		$Result = mysqli_query($Conn, $Query);
		$piwik_yesterdays_hits = mysqli_num_rows($Result);
		
		// Get yesterday's unique visitor count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = '$Yesterday' AND visitor_returning = 0";
		$Result = mysqli_query($Conn, $Query);
		$piwik_yesterdays_unique_visitors = mysqli_num_rows($Result);
		
		// Get today's top referer
		$Query = "SELECT DISTINCT(referer_name) FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = (SELECT MAX(visit_server_date) FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite) LIMIT 1";
		$Result = mysqli_query($Conn, $Query);
		$piwik_referer_list = '';
		while($Row = mysqli_fetch_assoc($Result)) $piwik_referer_list .= $Row['referer_name'] . ', ';
		$piwik_referer_list = substr($piwik_referer_list, 0, -2);
		if($piwik_referer_list == '') $piwik_referer_list = '(none)';
		
		// Get today's top keyword
		$Query = "SELECT DISTINCT(referer_keyword) FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND visit_server_date = (SELECT MAX(visit_server_date) FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite) LIMIT 1";
		$Result = mysqli_query($Conn, $Query);
		$piwik_keyword_list = '';
		while($Row = mysqli_fetch_assoc($Result)) $piwik_keyword_list .= $Row['referer_name'] . ', ';
		$piwik_keyword_list = substr($piwik_keyword_list, 0, -2);
		if($piwik_keyword_list == '') $piwik_keyword_list = '(none)';
		
		// Get today's visits from websites count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 3 AND visit_server_date = '$Today'";
		$Result = mysqli_query($Conn, $Query);
		$piwik_from_websites = mysqli_num_rows($Result);
		
		// Get today's visits from search engines count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 2 AND visit_server_date = '$Today'";
		$Result = mysqli_query($Conn, $Query);
		$piwik_from_searches = mysqli_num_rows($Result);
		
		// Get today's direct entry count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 1 AND visit_server_date = '$Today'";
		$Result = mysqli_query($Conn, $Query);
		$piwik_direct_entries = mysqli_num_rows($Result);
		
		// Get total visits from websites count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 3";
		$Result = mysqli_query($Conn, $Query);
		$piwik_all_from_websites = mysqli_num_rows($Result);
		
		// Get total visits from search engines count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 2";
		$Result = mysqli_query($Conn, $Query);
		$piwik_all_from_searches = mysqli_num_rows($Result);
		
		// Get total direct entry count
		$Query = "SELECT idvisit FROM " . $PIWIK_TABLE_PREFIX . "log_visit WHERE idsite = $idsite AND referer_type = 1";
		$Result = mysqli_query($Conn, $Query);
		$piwik_all_direct_entries = mysqli_num_rows($Result);
		
		// Close out the database connection
		mysqli_close($Conn);
		
		// Display the results ?>
		<style type="text/css">
			.piwik_section_header { font-family:Arial; font-size:14px; color:#888; border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom: 5px; }
			.piwik_data_value { font-family:Georgia; font-size:16px; color:#0077ff; }
			.piwik_data_string { color:#0044ff; font-style:italic; }
		</style>
		<div class="inside">
			<table border="0" cellpadding="5" cellspacing="5" width="100%">
				<tr>
					<td width="33%">
						<div class="piwik_section_header">Today's Stats</div>
						Actions (hits): <span class="piwik_data_value"><?php echo number_format($piwik_todays_hits); ?></span><br />
						Unique Visitors: <span class="piwik_data_value"><?php echo number_format($piwik_todays_unique_visitors); ?></span>				
					</td>
					<td width="33%">
						<div class="piwik_section_header">Yesterday's Stats</div>
						Actions (hits): <span class="piwik_data_value"><?php echo number_format($piwik_yesterdays_hits); ?></span><br />
						Unique Visitors: <span class="piwik_data_value"><?php echo number_format($piwik_yesterdays_unique_visitors); ?></span>
					<td width="34%">
						<div class="piwik_section_header">Total Values</div>
						Actions (hits): <span class="piwik_data_value"><?php echo number_format($piwik_total_hits); ?></span><br />
						Unique Visitors: <span class="piwik_data_value"><?php echo number_format($piwik_unique_visitors); ?></span>			
					</td>
				</tr>
				<tr>
					<td colspan="2" width="66%">
						<div class="piwik_section_header">Today's Overview</div>
						Visits From Websites: <span class="piwik_data_value"><?php echo number_format($piwik_from_websites); ?></span><br />
						Visits From Searches: <span class="piwik_data_value"><?php echo number_format($piwik_from_searches); ?></span><br />
						Direct Entries: <span class="piwik_data_value"><?php echo number_format($piwik_direct_entries); ?></span><br />
						Top Referer: <span class="piwik_data_string"><?php echo $piwik_referer_list; ?></span><br />
						Top Keyword: <span class="piwik_data_string"><?php echo $piwik_keyword_list; ?></span>
					</td>
					<td width="34%" valign="top">
						<div class="piwik_section_header">All Referers</div>
						From Websites: <span class="piwik_data_value"><?php echo number_format($piwik_all_from_websites); ?></span><br />
						From Searches: <span class="piwik_data_value"><?php echo number_format($piwik_all_from_searches); ?></span><br />
						Direct Entries: <span class="piwik_data_value"><?php echo number_format($piwik_all_direct_entries); ?></span>
					</td>
				</tr>
			</table>
		</div>
		<?php	
	}
} 

// Display the admin page
function piwik_widget_admin_page() { 

	if(isset($_POST['PIWIK_WIDGET_SERVER_ADDRESS'])) {
		update_option('PIWIK_WIDGET_SERVER_ADDRESS', $_POST['PIWIK_WIDGET_SERVER_ADDRESS']);
		update_option('PIWIK_WIDGET_DB_USER', $_POST['PIWIK_WIDGET_DB_USER']);
		update_option('PIWIK_WIDGET_DB_PASSWORD', $_POST['PIWIK_WIDGET_DB_PASSWORD']);
		update_option('PIWIK_WIDGET_DB_NAME', $_POST['PIWIK_WIDGET_DB_NAME']);
		update_option('PIWIK_WIDGET_SITE_ID', $_POST['PIWIK_WIDGET_SITE_ID']);
		update_option('PIWIK_WIDGET_TABLE_PREFIX', $_POST['PIWIK_WIDGET_TABLE_PREFIX']);
		echo '<p><em>Options saved.</em></p>';
	}

	?>
	<h2>Piwik Dashboard Widget Configuration</h2>
	<p>Set the options for your Piwik server accordingly.</p>
	<form method="POST" action="options-general.php?page=piwikwidget">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>Piwik Database Server Address:</td>
			<td><input name="PIWIK_WIDGET_SERVER_ADDRESS" type="text" value="<?php echo get_option('PIWIK_WIDGET_SERVER_ADDRESS'); ?>" /></td>
		</tr>
		<tr>
			<td>Piwik Database User:</td>
			<td><input name="PIWIK_WIDGET_DB_USER" type="text" value="<?php echo get_option('PIWIK_WIDGET_DB_USER'); ?>" /></td>
		</tr>
		<tr>
			<td>Piwik Database Password:</td>
			<td><input name="PIWIK_WIDGET_DB_PASSWORD" type="password" value="<?php echo get_option('PIWIK_WIDGET_DB_PASSWORD'); ?>" /></td>
		</tr>
		<tr>
			<td>Piwik Database Name:</td>
			<td><input name="PIWIK_WIDGET_DB_NAME" type="text" value="<?php echo get_option('PIWIK_WIDGET_DB_NAME'); ?>" /></td>
		</tr>
		<tr>
			<td>Piwik Site Id:</td>
			<td><input name="PIWIK_WIDGET_SITE_ID" type="text" value="<?php echo get_option('PIWIK_WIDGET_SITE_ID'); ?>" /></td>
		</tr>
		<tr>
			<td>Piwik Table Prefix:</td>
			<td><input name="PIWIK_WIDGET_TABLE_PREFIX" type="text" value="<?php echo get_option('PIWIK_WIDGET_TABLE_PREFIX'); ?>" /> ( Default:<em> piwik_</em> )</td>
		</tr>
		<tr>
			<td colspan="2" align="right"><input type="submit" value="Save" /></td>
		</tr>
	</table>
	</form>
	
<? }

// Add the dashboard widget
function add_piwik_dashboard_widget() {
	wp_add_dashboard_widget('piwik_dashboard_widget', 'Piwik Analytics Summary', 'piwik_dashboard_widget_function');	
}

// Add the menu entry
function add_piwik_dashboard_widget_menu() {
	add_submenu_page( 'options-general.php', 'Piwik Widget', 'Piwik Widget', 'manage_options', 'piwikwidget', 'piwik_widget_admin_page' );
}

// Add the plugin actions
add_action('wp_dashboard_setup', 'add_piwik_dashboard_widget' );
add_action('admin_menu', 'add_piwik_dashboard_widget_menu');
?>