<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if(!isset($_SESSION["SESSIGNUP"]) || $_SESSION["SESSIGNUP"]['email']=='' || $_SESSION["SESSIGNUP"]['sms_phno']=='' || $_SESSION["SESSIGNUP"]['keyword']==''){
	echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings&tab=verify_info')."';</script>";	
	exit;
}
$msg='';
include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
$fetch_settings = new WP_WETEXT_Settings();
$get_subscribe = $fetch_settings->wetext_get_settings();
if( !empty($get_subscribe->wetext_info) ){
	echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings')."';</script>";
	exit;
	
}else{
	
	/* // asign auth code after validate authentication code */	
	if((isset($_GET['authstatus']) && $_GET['authstatus']=='success') && (isset($_GET['authcodeval']) && $_GET['authcodeval']!='')){		
		$authcodeVal = Array("authcode" => sanitize_text_field($_GET['authcodeval']));
		$_SESSION["SESSIGNUP"] = array_merge($_SESSION["SESSIGNUP"],$authcodeVal);
		echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=referral_code')."';</script>";
		exit;		
	}			
	/* asign auth code after validate authentication code // */
	
	// for resend code
	if(isset($_POST['btnsubmitresendcode'])){
		
		$wetext_resendcode_url = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/send_code.php');			
			
		if(($_POST['country_code']!='-000') || ($_POST['country_code']!='-111')){
			$data = array('country_code' => sanitize_text_field($_POST['country_code']), 'sms_phno' => sanitize_text_field($_POST['sms_phno_resend']));
		}else{
			$data = array('country_code' => '', 'sms_phno' => sanitize_text_field($_POST['sms_phno_resend']));
		}
		
		$post_resendcode_info = wp_remote_post( $wetext_resendcode_url, array(
						'method'      => 'POST',
						'body'        => $data,							
						'headers'     => array('api_key' => WETEXTAPIKEY)							
					));		
					
		$post_resend_code = wp_remote_retrieve_body( $post_resendcode_info );							
		$response_val = json_decode($post_resend_code,true);
					
		if($response_val[0]['responseType']=='Success'){
			
			unset($_SESSION["SESSIGNUP"]['country_code']);	
			unset($_SESSION["SESSIGNUP"]['sms_phno']);
			
			$country_code_phno = Array("country_code" => sanitize_text_field($_POST['country_code']), "sms_phno" => sanitize_text_field($_POST['sms_phno_resend']));	
			
			$_SESSION["SESSIGNUP"] = array_merge($_SESSION["SESSIGNUP"],$country_code_phno);
			
			echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=cell_verification&status=success')."';</script>";
			
					
		}else{			
			$msg = $response_val[0]['responseMessage'];			
		}			
	}
	
	if(isset($_SESSION["SESSIGNUP"]) && $_SESSION["SESSIGNUP"]['authcode']!=''){
		$authcodeval = $_SESSION["SESSIGNUP"]['authcode'];		
	}else{
		$authcodeval = '';		
	}
	
?>
<div class="stapsSection">
	<ol class="cd-multi-steps text-bottom">
		<li class="completed">Verify your info</li>
		<li class="completed">Pick your Keyword</li>
		<li class="completed">Cell number verification</li>
		<li class="current">Referral Code</li>                           
		<li class="current">Let's Go</li>
	</ol>
</div>

<script>
jQuery( document ).ready(function() {
		
	jQuery('#cell_verification_submit').click(function() {			
		if(jQuery.trim(jQuery("#authcode").val())==''){
			
			jQuery("#authcode").addClass("invalid");
			jQuery("#verification_notification").text('This field is required.');
			jQuery("#authcode").focus();
			
		}else{
			var wetext_authentication_data;
			jQuery("#authcode").removeClass("invalid");
			var cellnumber = '<?php echo sanitize_text_field($_SESSION["SESSIGNUP"]['sms_phno']); ?>';
			var countrycodeval = '<?php echo !empty($_SESSION["SESSIGNUP"]['country_code']) ? sanitize_text_field($_SESSION["SESSIGNUP"]['country_code']) : ''; ?>';
						
			var authCode = jQuery.trim(jQuery("#authcode").val());
			
			jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/ajax-loader.gif'); ?>");	
			jQuery("#verification_notification").text('');	
			
			if((countrycodeval!='-000') || (countrycodeval!='-111')){
				wetext_authentication_data = {'country_code': countrycodeval,'sms_phno':cellnumber,'authcode':authCode};				
			}else{
				wetext_authentication_data = {'country_code': '','sms_phno':cellnumber,'authcode':authCode};
			}
			
			var wetext_authentication_ajaxurl = "<?php echo esc_url_raw(WETEXTDOMAIN.'/api/wordpress/verify_authentication_code.php'); ?>";
			
			jQuery.ajax({
				 type: "POST",
				 url: wetext_authentication_ajaxurl,
				 headers: {"api_key": "<?php echo WETEXTAPIKEY; ?>"},
				 data: wetext_authentication_data,					 
				 dataType: "json",
				 success: function(wetext_authentication_response) {						
					wetext_authentication_data_response = wetext_authentication_response[0]['responseType'];						
					if(wetext_authentication_data_response=="Success"){
						
						
						
						jQuery("#authcode").removeClass("invalid");
						jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");
						
						var direction_link = '<?php echo esc_url_raw($base_path.'&mode=settings&tab=cell_verification&authstatus=success'); ?>';						
						window.location.href = direction_link+"&authcodeval="+authCode;
												
					}else{	
					
						jQuery("#authcode").addClass("invalid");
						jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");
						jQuery("#verification_notification").text('Incorrect Authentication Code Entered.');	
						
					}				
				 }
			});						
		}		
	});	
	
	jQuery('#cell_verification_submit_back').click(function() {		
		window.location.href = "<?php echo esc_url_raw($base_path.'&mode=settings&tab=pick_keyword'); ?>";
	});		
	
});
</script>

<h2><span>Enter WeText Trial Verification Code</span></h2>

<form name="frmcellverification" id="frmcellverification" action="" method="post">
<table class="form-table form-table1">
<tr>
	<td colspan="2">Please enter the WeText Trial Verification Code we sent to your mobile.</td>
</tr>
<tr>
	<td colspan="2">
		<input type="text" class="regular-text" placeholder="Enter WeText Trial Verification Code" id="authcode" name="authcode" value="<?php echo $authcodeval; ?>">
		<div style="width:16px; float:left;">
		<img src="<?php echo WETEXT_PLUGIN_URL; ?>images/trans.png" id="loader">
		</div>
		<div id="verification_notification" class="error_text">&nbsp;</div>
	</td>
</tr>
<tr>
	<td align="center" colspan="2">
		<input type="button" value="Back" class="button" id="cell_verification_submit_back" name="submit">
		<input type="button" value="Next" class="button button-primary" id="cell_verification_submit" name="cell_verification_submit">
	</td>
</tr>
</table>
</form>

<form name="frmcellresend" id="frmcellresend" action="" method="post">
<table class="form-table form-table1">
<tr>
	<td>
	<h4><span>Did not receive the code? Click the RESEND CODE button below.</span></h4>
	<?php
		if(isset($msg) && $msg!=''){
			echo '<span class="error_msg_box"> '.$msg.' </span>';
		}
		
		if(isset($_GET['status']) && $_GET['status']=='success'){
			echo '<span class="message_text"> We have resent the verification code. </span>';
		}
	?>
	</td>
</tr>
<tr>
	<td></td>
</tr>
<tr>
	<td>
	<?php

		$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/get_countries_list.php");
		$contries_response = wp_remote_post( $url, array(    
			'headers' => array('api_key' => WETEXTAPIKEY),
		) );
		$fetch_contries_val = wp_remote_retrieve_body( $contries_response );
		$contries_val = json_decode($fetch_contries_val);
		
	?>
	<select name="country_code" id="country_code" class="selectDesign">
		<option value="-000">U.S. or Canada</option>
		<?php
			foreach ($contries_val->countries as $key=>$key_val){
				$key = ($key=='_empty_') ? '-111' : $key;
				if($_SESSION["SESSIGNUP"]['country_code']==$key){
					$selectedval = 'selected';
				}else{
					$selectedval = '';
				}				
				echo '<option value="'.$key.'" '. $selectedval .'>'.$key_val.'</option>';
			}
		?>	
	</select>
	</td>	
	<td>
		<input type="text" class="regular-text" placeholder="Code will be resend via text" id="sms_phno_resend" name="sms_phno_resend" value="<?php echo $_SESSION["SESSIGNUP"]['sms_phno']; ?>">
		<p id="resendcode_notification">&nbsp;</p>	
	</td>
</tr>
<tr>
	<td align="center" colspan="2">
		<input type="submit" value="Resend Code" class="button" id="btnsubmitresendcode" name="btnsubmitresendcode">
	</td>
</tr>
</form>
<tr>
	<td>Having trouble? Please email <a href="mailto:support@wetext.co">support@wetext.co</a>.</td>
</tr>

</table>


<div class="clear"></div>

<div class="footer_outer">
<div class="footer_box">
<div class="lft">
<img src="<?php echo WETEXT_PLUGIN_URL; ?>images/logo_big.png" width="107" alt="wetext logo" style="height:auto;" />  
</div>
<div class="rgt"><strong>Opting Out: </strong>Reply "stop" via Text and wait for the confirmation reply. Carrier message and data rates may apply. By clicking "Submit" you agree to the <a href="https://wetext.co/wp-content/uploads/2016/11/Privi-Terms-and-Conditions.pdf" target="_blank">Terms & Conditions.</a></div>
</div></div>

<?php } ?>