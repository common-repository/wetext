<?php 
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

wetext_chkAuth();	

	if(isset($_SESSION["SESLETSGO"])){
		unset($_SESSION["SESLETSGO"]);
	}	
	
	include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'header.php';
	$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/get_user_details.php");
		
	$getAdminDetails_info = wp_remote_post( $url, array(
							'method'   => 'GET',												
							'headers'  => array('token' => $fetch_rec['wetext_api_key'])							
						));						
	$getAdminDetails = wp_remote_retrieve_body( $getAdminDetails_info );			
	$admin_details = json_decode($getAdminDetails,true);
?>

<div class="stapsTabs">
	<ol class="cd-multi-tabs text-bottom">
		<li class="active_tab"><a href="<?php echo esc_url_raw($base_path.'&wtab=admin_profile');?>">Admin Profile</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=message_usage');?>">Message Usage</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=manage_api');?>">Manage API</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=configuration');?>">Configuration</a></li>                           
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=invite_people');?>">Invite People</a></li>
	</ol>
</div>

<h2><span>Account Details</span></h2>

<table class="form-table">
<tbody>
<tr>
	<td>First Name: <input type="text" class="regular-text" readonly value="<?php echo $admin_details[0]['first_name']; ?>"></td>
	<td>Last Name: <input type="text" class="regular-text" readonly value="<?php echo $admin_details[0]['last_name']; ?>"></td>
</tr>
<tr>
	<td>Email: <input type="text" class="regular-text" readonly value="<?php echo $admin_details[0]['email']; ?>"></td>
	<td>Phone Number: <input type="text" class="regular-text" readonly value="<?php echo $admin_details[0]['phone']; ?>"></td>	
</tr>
</tbody>
</table>