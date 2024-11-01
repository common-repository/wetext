<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
$fetch_settings = new WP_WETEXT_Settings();
$get_subscribe = $fetch_settings->wetext_get_settings();

if( !empty($get_subscribe->wetext_info) ){
	echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings')."';</script>";	
	exit;
}else{
	
	$msg = '';
	if(isset($_POST['submit_verify_info'])){
		
		$url = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/send_code.php');	
				
		if(isset($_SESSION["SESSIGNUP"]) && $_SESSION["SESSIGNUP"]!=''){
			
			if($_SESSION["SESSIGNUP"]['sms_phno']!=$_POST["sms_phno"]){
							
				$post_verify_info = wp_remote_post( $url, array(
							'method'      => 'POST',
							'body'        => array(
								'fullname'     => sanitize_text_field($_POST['fullname']),
								'tr_email'     => sanitize_email($_POST['tr_email']),
								'country_code' => sanitize_text_field($_POST['country_code']),
								'sms_phno'     => sanitize_text_field($_POST['sms_phno'])
							 ),							
							'headers'     => array('api_key' => WETEXTAPIKEY)							
						));	
					
				$fetch_verify_info = wp_remote_retrieve_body( $post_verify_info );						
				$response_val = json_decode($fetch_verify_info,true);
								
				if($response_val[0]['responseType']=='Success'){
					
					unset($_SESSION["SESSIGNUP"]['fullname']);
					unset($_SESSION["SESSIGNUP"]['email']);
					unset($_SESSION["SESSIGNUP"]['country_code']);
					unset($_SESSION["SESSIGNUP"]['sms_phno']);
															
					$verify_info_step1 = Array("fullname" => sanitize_text_field($_POST["fullname"]), "email" => sanitize_email($_POST["tr_email"]), "country_code" => sanitize_text_field($_POST["country_code"]), "sms_phno" => sanitize_text_field($_POST["sms_phno"]));	
					
					$_SESSION["SESSIGNUP"] = array_merge($verify_info_step1,$_SESSION["SESSIGNUP"]);
					
					echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=pick_keyword')."';</script>";	
					exit;				
					
				}else{
					$msg = $response_val[0]['responseMessage'];
				}
		
			}else{							
			
					unset($_SESSION["SESSIGNUP"]['fullname']);
					unset($_SESSION["SESSIGNUP"]['email']);
					unset($_SESSION["SESSIGNUP"]['country_code']);
					unset($_SESSION["SESSIGNUP"]['sms_phno']);
					
					$verify_info_step1 = Array("fullname" => sanitize_text_field($_POST["fullname"]), "email" => sanitize_email($_POST["tr_email"]), "country_code" => sanitize_text_field($_POST["country_code"]), "sms_phno" => sanitize_text_field($_POST["sms_phno"]));	
					
					$_SESSION["SESSIGNUP"] = array_merge($verify_info_step1,$_SESSION["SESSIGNUP"]);
						
					echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=pick_keyword')."';</script>";
					exit;				
			}
		}else{		
			
			$post_verify_info = wp_remote_post( $url, array(
							'method'      => 'POST',
							'body'        => array(
								'fullname'     => sanitize_text_field($_POST['fullname']),
								'tr_email'     => sanitize_email($_POST['tr_email']),
								'country_code' => sanitize_text_field($_POST['country_code']),
								'sms_phno'     => sanitize_text_field($_POST['sms_phno'])
							 ),							
							'headers'     => array('api_key' => WETEXTAPIKEY)							
						));	
					
			$fetch_verify_info = wp_remote_retrieve_body( $post_verify_info );						
			$response_val = json_decode($fetch_verify_info,true);			
						
			if($response_val[0]['responseType']=='Success'){
				
				$verify_info_step1 = Array("fullname" => sanitize_text_field($_POST["fullname"]), "email" => sanitize_email($_POST["tr_email"]), "country_code" => sanitize_text_field($_POST["country_code"]), "sms_phno" => sanitize_text_field($_POST["sms_phno"]));				
				$_SESSION['SESSIGNUP'] = $verify_info_step1;
				
				echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=pick_keyword')."';</script>";
				exit;
				
			}else{
				$msg = $response_val[0]['responseMessage'];
			}
		}	
	}
		
	if(isset($_SESSION["SESSIGNUP"]) && $_SESSION["SESSIGNUP"]!=''){
		
		$fullname = $_SESSION["SESSIGNUP"]['fullname'];
		$tr_email = $_SESSION["SESSIGNUP"]['email'];
		$country_code = $_SESSION["SESSIGNUP"]['country_code'];
		$sms_phno = $_SESSION["SESSIGNUP"]['sms_phno'];	
		
	}else{
		
		$fullname = '';
		$tr_email = '';
		$country_code = '';
		$sms_phno = '';
		
	}
	
	
?>
<div class="stapsSection">
<ol class="cd-multi-steps text-bottom">
<li class="completed">Verify your info</li>
<li class="current">Pick your Keyword</li>
<li class="current">Cell number verification</li>
<li class="current">Referral Code</li>                           
<li class="current">Let's Go</li>
</ol>
</div>

<script>
jQuery( document ).ready(function() {
	jQuery('#verify_info_submit').click(function() {
		
		jQuery(".error_text").remove();
		jQuery('input').removeClass("invalid");
		var ss = jQuery("#fullname").val();
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
		
		if(jQuery.trim(jQuery("#fullname").val())==''){
			jQuery("#fullname").addClass("invalid");
			jQuery("#fullname").after('<p class="error_text">This field is required</p>');
			jQuery("#fullname").focus();
			return false;
		}else if(checkfnamelname(ss)==false){			
			jQuery("#fullname").addClass("invalid");
			jQuery("#fullname").after('<p class="error_text">Please Include First and Last Name.</p>');
			jQuery("#fullname").focus();
			return false;		
		}else if(jQuery.trim(jQuery("#tr_email").val())==''){
			jQuery("#tr_email").addClass("invalid");
			jQuery("#tr_email").after('<p class="error_text">This field is required</p>');
			jQuery("#tr_email").focus();
			return false;
		}else if(!regex.test(jQuery("#tr_email").val())){	
			jQuery("#tr_email").addClass("invalid");
			jQuery("#tr_email").after('<p class="error_text">Please enter a valid email address.</p>');
			jQuery("#tr_email").focus();
			return false;
		}else if(jQuery.trim(jQuery("#sms_phno").val())==''){
			jQuery("#sms_phno").addClass("invalid");
			jQuery("#sms_phno").after('<p class="error_text">This field is required</p>');
			jQuery("#sms_phno").focus();
			return false;
		}else if(isNaN(jQuery("#sms_phno").val())){
			jQuery("#sms_phno").addClass("invalid");
			jQuery("#sms_phno").after('<p class="error_text">Please enter a valid number.</p>');		
			jQuery("#sms_phno").focus();
			return false;
		}else if((jQuery("#sms_phno").val().length > 10) || (jQuery("#sms_phno").val().length < 10)){
			jQuery("#sms_phno").addClass("invalid");
			jQuery("#sms_phno").after('<p class="error_text">Please enter at least 10 characters.</p>');	
			jQuery("#sms_phno").focus();
			return false;
		}				
		
	});	
});	

function checkfnamelname(strval){
	if (/\w+\s+\w+/.test(strval)) {
		return true;
	} else {
		return false;
	}
}		
</script>
<form name="wetextform_verigy_info" action="" method="post">
<table class="form-table">
<tbody>
<tr>
	<td>
		<?php if(isset($msg) && $msg!=''){ echo '<span class="error_text">'.$msg.'<span>'; } ?>
	</td>
</tr>
<tr>
	<td><input type="text" class="regular-text" placeholder="Full Name. (e.g. John Doe)" id="fullname" name="fullname" value="<?php echo $fullname; ?>"></td>
</tr>
<tr>
	<td><input type="text" class="regular-text" placeholder="Valid Email. Your account credentials will be sent to this email." id="tr_email" name="tr_email" value="<?php echo $tr_email; ?>"></td>
</tr>
<tr>
<td>
<?php

	$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/get_countries_list.php");
	$contries_response = wp_remote_post( $url, array(
							'method'      => 'GET',													
							'headers'     => array('api_key' => WETEXTAPIKEY)							
						));	
	$fetch_contries_val = wp_remote_retrieve_body( $contries_response );
	$contries_val = json_decode($fetch_contries_val);
	
?>
<select name="country_code" id="country_code" class="selectDesign">
<option value="-000">U.S. or Canada</option>
<?php
	foreach ($contries_val->countries as $key=>$key_val){
		$key = ($key=='_empty_') ? '-111' : $key;
		if($country_code==$key){
			$selectedval = 'selected';
		}else{			
			$selectedval = '';
		}
		echo '<option value="'.$key.'" '.$selectedval.'>'.$key_val.'</option>';
	}
?>	
</select>

<?php if($_SESSION["SESSIGNUP"]['country_code']==''){ ?>
<script>
jQuery("#country_code").val(jQuery("#country_code option:first").val());
</script>
<?php } ?>
</td>
</tr>
<tr>
<td><input type="text" class="regular-text" placeholder="Mobile Number. (e.g. 5519981234) Code will be sent to mobile via text." id="sms_phno" name="sms_phno" value="<?php echo $sms_phno; ?>">
</td>
</tr>
<tr>
<td><input type="submit" value="Next" class="button button-primary" id="verify_info_submit" name="submit_verify_info"></td>
</tr>
</tbody>
</table>
</form>

<div class="footer_outer">
<div class="footer_box">
<div class="lft">
<img src="<?php echo WETEXT_PLUGIN_URL; ?>images/logo_big.png" width="107" alt="wetext logo" style="height:auto;" />  
</div>
<div class="rgt"><strong>Opting Out: </strong>Reply "stop" via Text and wait for the confirmation reply. Carrier message and data rates may apply. By clicking "Submit" you agree to the <a href="https://wetext.co/wp-content/uploads/2016/11/Privi-Terms-and-Conditions.pdf" target="_blank">Terms & Conditions.</a></div>
</div></div>
<?php } ?>