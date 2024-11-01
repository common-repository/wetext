<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


wetext_chkAuth();
include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'header.php';
$chk_optval = $fetch_settings->wetext_fetch_data( "option_id,option_value", "options", "option_name='wetext_api_details'" );	  
$msg='';
if(isset($_POST['btnsubmitintegration'])){
	
		$integration_array = Array();
		
		if((isset($_POST['txtmailchimpapikey']) && $_POST['txtmailchimpapikey']!='') && (isset($_POST['txtmailchimplistid']) && $_POST['txtmailchimplistid']!='')){						
			$mailchimp = Array("mail_chimp_api_key" => sanitize_text_field(stripslashes_deep($_POST["txtmailchimpapikey"])), "mail_chimp_list_id" => sanitize_text_field(stripslashes_deep($_POST["txtmailchimplistid"])));			
			$integration_array = array_merge($integration_array,$mailchimp);	
		}
		
		if((isset($_POST['txtccapikey']) && $_POST['txtccapikey']!='') && (isset($_POST['txtccaccesstoken']) && $_POST['txtccaccesstoken']!='')){			
			$constant_contact = Array("constant_contact_api_key" => sanitize_text_field(stripslashes_deep($_POST["txtccapikey"])), "constant_contact_access_token" => sanitize_text_field(stripslashes_deep($_POST["txtccaccesstoken"])));			
			$integration_array = array_merge($integration_array,$constant_contact);				
		}
		
		/*if((isset($_POST['txtsfusername']) && $_POST['txtsfusername']!='') && (isset($_POST['txtsfpass']) && $_POST['txtsfpass']!='') && (isset($_POST['txtsfsectiken']) && $_POST['txtsfsectiken']!='')){			
			$sales_force = Array("sales_force_username" => $_POST["txtsfusername"], "sales_force_password" => $_POST["txtsfpass"], "sales_force_security_token" => $_POST["txtsfsectiken"]);	
			$integration_array = array_merge($integration_array,$sales_force);				
		}*/
		
		$integration_serialize = serialize($integration_array);
				
		if(!empty($chk_optval)){			
			$update_data  = array( 'option_value' => $integration_serialize );			
			if($fetch_settings->wetext_update_table( "options",$update_data,"option_id",$chk_optval->option_id )){
				echo "<script>window.location = '".esc_url_raw($base_path.'&wtab=integration&message=success')."';</script>";			
			}
		}else{			
			$insert_data  = array( 'option_name' => 'wetext_api_details', 'option_value' => $integration_serialize, 'autoload' => 'no' );			
			if($fetch_settings->wetext_insert_data( "options", $insert_data )){
				echo "<script>window.location = '".esc_url_raw($base_path.'&wtab=integration&message=success')."';</script>";				
			}else{
				$msg = '<p class="error_text">Error!! Please try after some time.</p>';
			}
		}
}	

if($chk_optval->option_value!=''){
	
	$api_settings_value = unserialize($chk_optval->option_value);
	
	$mail_chimp_api_key = $api_settings_value['mail_chimp_api_key'];
	$mail_chimp_list_id = $api_settings_value['mail_chimp_list_id'];
	$constant_contact_api_key = $api_settings_value['constant_contact_api_key'];
	$constant_contact_access_token = $api_settings_value['constant_contact_access_token'];
	$sales_force_username = $api_settings_value['sales_force_username'];
	$sales_force_password = $api_settings_value['sales_force_password'];
	$sales_force_security_token = $api_settings_value['sales_force_security_token'];
		
}else{
	
	$mail_chimp_api_key = '';
	$mail_chimp_list_id = '';
	$constant_contact_api_key = '';
	$constant_contact_access_token = '';
	$sales_force_username = '';
	$sales_force_password = '';
	$sales_force_security_token = '';
	
}
?>
<script>
jQuery( document ).ready(function() {
	jQuery('#btnsubmitintegration').click(function() {
		
		jQuery(".error_text").remove();
		jQuery('input').removeClass("invalid");
		
		if((jQuery.trim(jQuery("#txtmailchimpapikey").val())!='') && (jQuery.trim(jQuery("#txtmailchimplistid").val())=='')){
			jQuery("#txtmailchimplistid").addClass("invalid");
			jQuery("#txtmailchimplistid").after('<p class="error_text">This field is required</p>');			
			return false;
		}else if((jQuery.trim(jQuery("#txtmailchimplistid").val())!='') && (jQuery.trim(jQuery("#txtmailchimpapikey").val())=='')){
			jQuery("#txtmailchimpapikey").addClass("invalid");
			jQuery("#txtmailchimpapikey").after('<p class="error_text">This field is required</p>');			
			return false;
		}else if((jQuery.trim(jQuery("#txtccapikey").val())!='') && (jQuery.trim(jQuery("#txtccaccesstoken").val())=='')){
			jQuery("#txtccaccesstoken").addClass("invalid");
			jQuery("#txtccaccesstoken").after('<p class="error_text">This field is required</p>');			
			return false;
		}else if((jQuery.trim(jQuery("#txtccaccesstoken").val())!='') && (jQuery.trim(jQuery("#txtccapikey").val())=='')){
			jQuery("#txtccapikey").addClass("invalid");
			jQuery("#txtccapikey").after('<p class="error_text">This field is required</p>');			
			return false;
		}else if((jQuery.trim(jQuery("#txtsfusername").val())!='') && (jQuery.trim(jQuery("#txtsfpass").val())=='') && (jQuery.trim(jQuery("#txtsfsectiken").val())=='')){
			jQuery("#txtsfpass").addClass("invalid");
			jQuery("#txtsfpass").after('<p class="error_text">This field is required</p>');	
			jQuery("#txtsfsectiken").addClass("invalid");
			jQuery("#txtsfsectiken").after('<p class="error_text">This field is required</p>');				
			return false;
		}else if((jQuery.trim(jQuery("#txtsfusername").val())!='') && (jQuery.trim(jQuery("#txtsfpass").val())!='') && (jQuery.trim(jQuery("#txtsfsectiken").val())=='')){			
			jQuery("#txtsfsectiken").addClass("invalid");
			jQuery("#txtsfsectiken").after('<p class="error_text">This field is required</p>');				
			return false;
		}else if((jQuery.trim(jQuery("#txtsfpass").val())!='') && (jQuery.trim(jQuery("#txtsfusername").val())=='') && (jQuery.trim(jQuery("#txtsfsectiken").val())=='')){
			jQuery("#txtsfusername").addClass("invalid");
			jQuery("#txtsfusername").after('<p class="error_text">This field is required</p>');	
			jQuery("#txtsfsectiken").addClass("invalid");
			jQuery("#txtsfsectiken").after('<p class="error_text">This field is required</p>');				
			return false;
		}else if((jQuery.trim(jQuery("#txtsfsectiken").val())!='') && (jQuery.trim(jQuery("#txtsfusername").val())=='') && (jQuery.trim(jQuery("#txtsfpass").val())=='')){
			jQuery("#txtsfusername").addClass("invalid");
			jQuery("#txtsfusername").after('<p class="error_text">This field is required</p>');				
			jQuery("#txtsfpass").addClass("invalid");
			jQuery("#txtsfpass").after('<p class="error_text">This field is required</p>');				
			return false;
		}else if((jQuery.trim(jQuery("#txtsfsectiken").val())!='') && (jQuery.trim(jQuery("#txtsfpass").val())!='') && (jQuery.trim(jQuery("#txtsfusername").val())=='')){
			jQuery("#txtsfusername").addClass("invalid");
			jQuery("#txtsfusername").after('<p class="error_text">This field is required</p>');				
			return false;
		}else if((jQuery.trim(jQuery("#txtsfusername").val())!='') && (jQuery.trim(jQuery("#txtsfsectiken").val())!='') && (jQuery.trim(jQuery("#txtsfpass").val())=='')){
			jQuery("#txtsfpass").addClass("invalid");
			jQuery("#txtsfpass").after('<p class="error_text">This field is required</p>');				
			return false;
		}
		
	});	
});			
</script>
<div class="stapsTabs">
	<ol class="cd-multi-tabs text-bottom">
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=admin_profile');?>">Admin Profile</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=message_usage');?>">Message Usage</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=manage_api');?>">Manage API</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=configuration');?>">Configuration</a></li>
		<li class="active_tab"><a href="<?php echo esc_url_raw($base_path.'&wtab=integration');?>">Integration</a></li>
	</ol>
</div>
<form name="frmintegration" action="" method="post">
<table class="form-table">
<tbody>
<tr>
	<td colspan="2">
		<?php 
			if(isset($msg) && $msg!=''){ echo $msg; } 
			if(isset($_GET['message']) && $_GET['message']=='success'){ echo '<p class="message_text">Your data has been successfully saved.</p>';}
		?>
	</td>
</tr>
<tr>
<td colspan="2">To sync your addressbook from Mail-Chimp, Constant Contact, Sales Force account. Please insert the API key/List ID/Access Token/Security Token from Mail-Chimp/Constant Contact/Sales Force account and save the settings.</td>
</tr>
<tr>
<td colspan="2"><h2><span>Mail-Chimp</span></h2></td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Mail-Chimp API Key:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Mail-Chimp API Key" id="txtmailchimpapikey" name="txtmailchimpapikey" value="<?php echo $mail_chimp_api_key; ?>">
	</td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Mail-Chimp List ID:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Mail-Chimp List ID" id="txtmailchimplistid" name="txtmailchimplistid" value="<?php echo $mail_chimp_list_id; ?>">
	</td>
</tr>
<tr>
<td colspan="2"><h2><span>Constant Contact</span></h2></td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Constant Contact API Key:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Constant Contact API Key" id="txtccapikey" name="txtccapikey" value="<?php echo $constant_contact_api_key; ?>">	
	</td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Constant Contact Access Token:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Constant Contact Access Token" id="txtccaccesstoken" name="txtccaccesstoken" value="<?php echo $constant_contact_access_token; ?>">
	</td>
</tr>
<tr>
<td colspan="2"><h2><span>Sales Force</span></h2></td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Sales Force Username:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Sales Force Username" id="txtsfusername" name="txtsfusername" value="<?php echo $sales_force_username; ?>">	
	</td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Sales Force Password:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Sales Force Password" id="txtsfpass" name="txtsfpass" value="<?php echo $sales_force_password; ?>">
	</td>
</tr>
<tr>
	<td width="30%" valign="top">
		<strong>Sales Force Security Token:</strong>
	</td>
	<td valign="middle">
		<input type="text" class="regular-text" placeholder="Sales Force Security Token" id="txtsfsectiken" name="txtsfsectiken" value="<?php echo $sales_force_security_token; ?>">
	</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" value="Save Changes" class="button button-primary" id="btnsubmitintegration" name="btnsubmitintegration">		
	</td>
</tr>
</tbody>
</table>
</form>