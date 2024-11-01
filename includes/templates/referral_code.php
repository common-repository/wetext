<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if(!isset($_SESSION["SESSIGNUP"]) || $_SESSION["SESSIGNUP"]['email']=='' || $_SESSION["SESSIGNUP"]['sms_phno']=='' ||  $_SESSION["SESSIGNUP"]['keyword']=='' || $_SESSION["SESSIGNUP"]['authcode']==''){	
	echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings&tab=verify_info')."';</script>";	
	exit;
}

include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
$fetch_settings = new WP_WETEXT_Settings();
$get_subscribe = $fetch_settings->wetext_get_settings();

if( !empty($get_subscribe->wetext_info) ){
	echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings')."';</script>";
	exit;
}else{
	$msg='';
	if(isset($_POST['referral_code_submit'])){	
				
		if(isset($_POST['promo_code']) && $_POST['promo_code']!=''){
			$promocode = Array("promo_code" => sanitize_text_field(stripslashes_deep($_POST['promo_code'])));
			$_SESSION["SESSIGNUP"] = array_merge($_SESSION["SESSIGNUP"],$promocode);
		}	
				
		if(($_SESSION["SESSIGNUP"]['country_code']=='-000') || ($_SESSION["SESSIGNUP"]['country_code']=='-111')){
			unset($_SESSION["SESSIGNUP"]['country_code']);
		}
		
		$url     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/create_account.php');
		$data    = $_SESSION["SESSIGNUP"];
			
		$create_account_info = wp_remote_post( $url, array(
									'method'      => 'POST',
									'body'        => $data,							
									'headers'     => array('api_key' => WETEXTAPIKEY)							
								));						
		
		$post_account_creation = wp_remote_retrieve_body( $create_account_info );	
				
		$response_val = json_decode($post_account_creation,true);
										
		if($response_val[0]['responseType']=="Success"){
												
			$username 		= $response_val[0]['user_name'];
			$keyword  		= $_SESSION["SESSIGNUP"]['keyword'];
			$apikey   		= $response_val[0]['api_key'];
			$signup_api_id 	= $response_val[0]['signup_api_id'];
			
			$wetext_user_info = Array("wetext_username" => $username, "wetext_keyword" => $keyword, "wetext_api_key" => $apikey, "wetext_signup_api_id" => $signup_api_id);	
			
			$wetext_user_info_serialize = serialize($wetext_user_info);
												
			if($fetch_settings->wetext_insert_settings($wetext_user_info_serialize)){
				unset($_SESSION["SESSIGNUP"]);
				$_SESSION["SESLETSGO"] = true;
				echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings&tab=lets_go')."';</script>";
				exit;	
			}else{
				$msg = 'Error!! We are unable to save your records. Please try again.';
			}
						
		}else{
			$msg = 'Error!! '.esc_html($response_val[0]['responseMessage']);
		}		
	}
		
?>
<div class="stapsSection">
<ol class="cd-multi-steps text-bottom">
<li class="completed">Verify your info</li>
<li class="completed">Pick your Keyword</li>
<li class="completed">Cell number verification</li>
<li class="completed">Referral Code</li>                           
<li class="current">Let's Go</li>
</ol>
</div>

<script>
jQuery( document ).ready(function() {	
	
	jQuery('#referral_code_submit_back').click(function() {		
		window.location.href = "<?php echo esc_url_raw($base_path.'&mode=settings&tab=cell_verification'); ?>";
	});	
	
	jQuery('#referral_code_submit').click(function() {	
		
		jQuery("#promo_code").removeClass("invalid");
		jQuery("#keywordmsg").html('');
		
		var currentText = jQuery.trim(jQuery('#promo_code').val());
		var statusval   = jQuery.trim(jQuery('#promo_code_chk').val());
		
		if((currentText)!='' && (statusval)!='ok'){
			jQuery("#promo_code").addClass("invalid");
			jQuery("#keywordmsg").html('Invalid Referral Code');
			return false;
		}		
	});
		
	var typingTimer;                //timer identifier
	var doneTypingInterval = 2000;  //time in ms, 5 second for example
	var $input = jQuery('#promo_code');
	
	//on keyup, start the countdown
	$input.on('keyup', function () {
	  clearTimeout(typingTimer);
	  typingTimer = setTimeout(doneTyping, doneTypingInterval);	  
	});

	//on keydown, clear the countdown 
	$input.on('keydown', function () {
	  jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/ajax-loader.gif'); ?>");
	  clearTimeout(typingTimer);
	});
	
	
	//user is "finished typing," do something
	function doneTyping () {	  
		  var currentText = jQuery.trim(jQuery('#promo_code').val());
		  jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL); ?>images/ajax-loader.gif");	  
		  if((currentText)!=''){											
				var wetext_data = {'promo_code': currentText};			
				var wetext_ajaxurl = "<?php echo esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_promo_details.php'); ?>";
				jQuery.ajax({
					 type: "POST",
					 url: wetext_ajaxurl,
					 headers: {"api_key": "<?php echo WETEXTAPIKEY; ?>"},
					 data: wetext_data,					 
					 dataType: "json",
					 success: function(wetext_response) {						
						wetext_data_response = wetext_response['responseType'];						
						if(wetext_data_response=="Success"){
							
							jQuery("#promo_code_chk").val('ok');
							jQuery("#promo_code").removeClass("invalid");
							jQuery("#keywordmsg").html('<span class="promocode_message">'+wetext_response['promo_code_description']+'</span>');
							jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");			
														
						}else{
							
							jQuery("#promo_code").addClass("invalid");
							jQuery("#promo_code_chk").val('notok');
							jQuery("#keywordmsg").html('Invalid Referral Code');
							jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");	
							
						}			
					 }
				});				
				
		  }else{
			  
			jQuery("#promo_code").removeClass("invalid");
			jQuery("#keywordmsg").html('');
			jQuery("#promo_code").val('');
			jQuery("#promo_code_chk").val('');			
			jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");	
			
		  }			  
	}	
});	
</script>

<h2><span>Referral Code</span></h2>

<form name="frmreferralcode" id="frmreferralcode" action="" method="post">
<table class="form-table form-table1">
<tbody>
<tr>
	<td style="color:red; font-weight:bold;"><?php if(isset($msg) && $msg!=''){ echo $msg;}?></td>
</tr>
<tr>
	<td>Did someone refer you to WeText and give you a Referral Code? If so, input it now or click next to move on. Please do not enter your WeText Trial Verification Code we sent to your mobile.</td>
</tr>
<tr>
	<td>
		<input type="text" class="regular-text" placeholder="Referral Code (if any, optional)" id="promo_code" name="promo_code">
		<div style="width:16px; float:left;"><img src="<?php echo WETEXT_PLUGIN_URL; ?>images/trans.png" id="loader"></div>
		<div id="keywordmsg">&nbsp;</div>
		<input type="hidden" name="promo_code_chk" id="promo_code_chk" value="" />
	</td>
</tr>
<tr>
	<td align="center">
		<input type="button" value="Back" class="button" id="referral_code_submit_back" name="submit">
		<input type="submit" value="Next" class="button button-primary" id="referral_code_submit" name="referral_code_submit">
	</td>
</tr>
</table>
</form>
<div class="clear"></div>

<div class="footer_outer">
<div class="footer_box">
<div class="lft">
<img src="<?php echo WETEXT_PLUGIN_URL; ?>images/logo_big.png" width="107" alt="wetext logo" style="height:auto;" />  
</div>
<div class="rgt"><strong>Opting Out: </strong>Reply "stop" via Text and wait for the confirmation reply. Carrier message and data rates may apply. By clicking "Submit" you agree to the <a href="https://wetext.co/wp-content/uploads/2016/11/Privi-Terms-and-Conditions.pdf" target="_blank">Terms & Conditions.</a></div>
</div></div>

<?php } ?>