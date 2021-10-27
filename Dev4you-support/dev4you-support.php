<?php
/* 
Plugin Name: Dev4you.lt Pagalbos įskiepis
Plugin URI: https://www.dev4you.lt
Description: Šis įskiepis skirtas <strong>Dev4you.lt</strong> klientams. Įskiepio pagalba paslaugos tiekėjas administruoja bei palaiko sistemos darbą. Esant problemoms registruokite jas įskiepio nustatymų lange.
Requires at least 5.0
Requires PHP: 5.4
Version: 0.0.1 beta
Author: Dev4you.lt
Author URI: https://www.dev4you.lt
*/
if(!defined('ABSPATH')){
	exit;
}

// Load the helper file to increase protection you can include some core functions of your plugin like innit() functions etc. in this file before you obfuscate it.
require 'includes/lb_helper.php';

// Create a new LicenseBoxExternalAPI helper class.
$lbapi = new LicenseBoxExternalAPI();

// Performs background license check, pass TRUE as 1st parameter to perform periodic verifications only.
$lb_verify_res = $lbapi->verify_license();

// Performs update check, you can easily change the duration of update checks.
if(false === ($lb_update_res = get_transient('licensebox_next_update_check'))){
	$lb_update_res = $lbapi->check_update();
	set_transient('licensebox_next_update_check', $lb_update_res, 12*HOUR_IN_SECONDS);
}



// If user has a valid license and new updates are available show the update notification in plugins page.
if(($lb_update_res['status'])&&($lb_verify_res['status'])&&(version_compare($lbapi->get_current_version(), $lb_update_res['version']) < 0)){
	function licensebox_show_update_notice(){
		global $lb_update_res;
		$lb_update_message = esc_html($lb_update_res['message']);
		$update_notification = <<<LB_UPDATE
<tr class="active">
	<td colspan="4">
		<div class="update-message notice inline notice-warning notice-alt" style="margin: 5px 20px 10px 20px">
			<p>
				<b>$lb_update_message</b>
				<a href="options-general.php?page=dev4you_support" style="text-decoration: underline;">Update now</a>.
			</p>
		</div>
	</td>
</tr>
LB_UPDATE;
  		echo $update_notification;
	}
	add_action("after_plugin_row_".plugin_basename(__FILE__), 'licensebox_show_update_notice', 10, 3);
}

// If user doesn't have a valid license show the activation pending notification in plugins page.
if(!$lb_verify_res['status']){
	function licensebox_show_license_notice(){
		$license_notification = <<<LB_LICENSE
<tr class="active">
	<td colspan="4">
		<div class="notice notice-error inline notice-alt" style="margin: 5px 20px 10px 20px">
			<p>
				<b>Techninė pagalba neaktyuota, Prašome aktyvuoti licenziją norint naudotis šiuo įskiepių.</b>
				<a href="options-general.php?page=dev4you_support" style="text-decoration: underline;">Įveskite licenzijos raktą</a>.
			</p>
		</div>
	</td>
</tr>
LB_LICENSE;
  		echo $license_notification;
	}
	add_action("after_plugin_row_".plugin_basename(__FILE__), 'licensebox_show_license_notice', 10, 3);
}

// Add plugin settings page link.
function licensebox_add_settings_link($links){
	$settings_link = '<a href="admin.php?page=dev4you_support">Nustatymai</a>';
	array_push($links, $settings_link);
	return $links;
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'licensebox_add_settings_link');



include 'dev4you-support-options.php'; // Load the settings page.
