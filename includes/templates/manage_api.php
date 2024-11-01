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

if(isset($_POST['btnSaveSettings'])){
		
	$wetext_user_info = Array("wetext_username" => sanitize_text_field(stripslashes_deep($_POST['txtwetextusername'])), "wetext_keyword" => sanitize_text_field(stripslashes_deep($_POST['txtwetextkeyword'])), "wetext_api_key" => sanitize_text_field(stripslashes_deep($_POST['txtwetextapitoken'])), "wetext_signup_api_id" => sanitize_text_field(stripslashes_deep($_POST['txtsignupapiid'])));			
	$data = serialize($wetext_user_info);	
	if($fetch_settings->wetext_update_settings($data)){		
		echo "<script>window.location = '".esc_url_raw($base_path.'&wtab=manage_api&message=success')."';</script>";		
	}
}
	
?>
<script>
jQuery( document ).ready(function() {
	jQuery("#btnSaveSettings").click(function() {
		
		jQuery(".error_text").remove();
		jQuery('input').removeClass("invalid");
		
		if(jQuery.trim(jQuery("#txtwetextusername").val())==''){
			jQuery("#txtwetextusername").addClass("invalid");
			jQuery("#txtwetextusername").after('<p class="error_text">This field is required</p>');
			jQuery("#txtwetextusername").focus();
			return false;
		}else if(jQuery.trim(jQuery("#txtwetextkeyword").val())==''){
			jQuery("#txtwetextkeyword").addClass("invalid");
			jQuery("#txtwetextkeyword").after('<p class="error_text">This field is required</p>');
			jQuery("#txtwetextkeyword").focus();
			return false;
		}else if(jQuery.trim(jQuery("#txtwetextapitoken").val())==''){
			jQuery("#txtwetextapitoken").addClass("invalid");
			jQuery("#txtwetextapitoken").after('<p class="error_text">This field is required</p>');
			jQuery("#txtwetextapitoken").focus();
			return false;
		}
		
	});	
});	
</script>
<div class="stapsTabs">
	<ol class="cd-multi-tabs text-bottom">
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=admin_profile');?>">Admin Profile</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=message_usage');?>">Message Usage</a></li>
		<li class="active_tab"><a href="<?php echo esc_url_raw($base_path.'&wtab=manage_api');?>">Manage API</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=configuration');?>">Configuration</a></li>      
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=invite_people');?>">Invite People</a></li>
	</ol>
</div>
<h2><span>WeText Account Settings</span></h2>

<form name="frmmanage_api" action="" method="post">
<table class="form-table">
<tbody>
<tr>
	<td><?php if(isset($_GET['message']) && $_GET['message']=='success'){ echo '<span class="message_text">Record updated successfully.</span>'; } ?></td>
</tr>
<tr>
	<td>
		<strong>Wetext Username:</strong><input type="text" class="regular-text" placeholder="Enter WeText Username you received via email" id="txtwetextusername" name="txtwetextusername" value="<?php echo $fetch_rec['wetext_username']; ?>">
	</td>
</tr>
<tr>
	<td>
		<strong>WeText Keyword:</strong><input type="text" class="regular-text" placeholder="Enter WeText Keyword you created during signup process" id="txtwetextkeyword" name="txtwetextkeyword" value="<?php echo $fetch_rec['wetext_keyword']; ?>">		
	</td>
</tr>
<tr>
	<td>
		<strong>WeText API Token:</strong><input type="text" class="regular-text" placeholder="Enter the WeText token you received via email" id="txtwetextapitoken" name="txtwetextapitoken" value="<?php echo $fetch_rec['wetext_api_key']; ?>">	
		<input type="hidden" name="txtsignupapiid" value="<?php echo $fetch_rec['wetext_signup_api_id']; ?>">
	</td>
</tr>
<tr>
	<td>
		<input type="submit" value="Save Changes" class="button button-primary" id="btnSaveSettings" name="btnSaveSettings">		
	</td>
</tr>
</tbody>
</table>
</form>