<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly



if(!isset($_SESSION["SESSIGNUP"]) || $_SESSION["SESSIGNUP"]['email']=='' || $_SESSION["SESSIGNUP"]['sms_phno']==''){
	
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
	$msg = '';
	$inputcss = '';
	if(isset($_POST['pick_keyword_submit'])){	
		
		$url     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/keyword_availability.php');				
		$post_keyword_info = wp_remote_post( $url, array(
							'method'  => 'POST',
							'body'    => array(
								'keyword' => sanitize_text_field($_POST['keyword'])								
							 ),							
							'headers' => array('api_key' => WETEXTAPIKEY)							
						));	
					
		$fetch_verify_info = wp_remote_retrieve_body( $post_keyword_info );
				
		$response_val = json_decode($fetch_verify_info,true);
		
		if($response_val[0]['responseType']=='Success'){
			$chktype = 'Ok';
		}else{		
			$chktype = 'notok';
		}
		
		if($chktype!='Ok'){
			$msg = 'This keyword already exists.';
			$inputcss = 'invalid';
		}else{
			if(isset($_SESSION["SESSIGNUP"]) && $_SESSION["SESSIGNUP"]['keyword']!=''){
				
				unset($_SESSION["SESSIGNUP"]['keyword']);
				
				$keywordVal = Array("keyword" => sanitize_text_field($_POST["keyword"]));
				$_SESSION["SESSIGNUP"] = array_merge($_SESSION["SESSIGNUP"],$keywordVal);
				
			}else{
				
				$keywordVal = Array("keyword" => sanitize_text_field($_POST["keyword"]));
				$_SESSION["SESSIGNUP"] = array_merge($_SESSION["SESSIGNUP"],$keywordVal);
			}
			
			echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=cell_verification')."';</script>";				
			exit;		
		}
	}
	
	if(isset($_SESSION["SESSIGNUP"]) && $_SESSION["SESSIGNUP"]['keyword']!=''){
		$keywordselectedval = $_SESSION["SESSIGNUP"]['keyword'];
		$hidval='';
	}else{
		$keywordselectedval = '';
		$hidval='1';
	}
?>
<div class="stapsSection">
<ol class="cd-multi-steps text-bottom">
<li class="completed">Verify your info</li>
<li class="completed">Pick your Keyword</li>
<li class="current">Cell number verification</li>
<li class="current">Referral Code</li>                           
<li class="current">Let's Go</li>
</ol>
</div>


<script>
jQuery( document ).ready(function() {
	jQuery('#pick_keyword_submit').click(function() {
		jQuery(".error_text").remove();
		jQuery('input').removeClass("invalid");		
		if(jQuery.trim(jQuery("#keyword").val())==''){
			jQuery("#keyword").addClass("invalid");			
			jQuery("#keywordmsg").html("This field is required.");
			jQuery("#keyword").focus();
			return false;
		}else if(jQuery.trim(jQuery("#hidkeywordval").val())!=''){			
			return false;
		}		
	});	
		
	/* // (Start) While typing on text box // */
	
	var typingTimer;                //timer identifier
	var doneTypingInterval = 2000;  //time in ms, 5 second for example
	var $input = jQuery('#keyword');
	
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
		var currentText = jQuery('#keyword').val(); 
		if(checkforNospace(currentText)==true){
		  jQuery("#keyword").addClass("invalid");
		  jQuery("#keywordmsg").html("No space please and don't leave it empty.");
		  jQuery("#keyword").focus();
		}else{
		  //do something		  
		  jQuery("#show_keyword_in_mob").text(currentText);
		  jQuery("#keywordmsg").html('&nbsp;');
		  jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/ajax-loader.gif'); ?>");	  
		  if((currentText)!=''){		  
			var wetext_data = {'keyword': currentText};			
			var wetext_ajaxurl = "<?php echo esc_url_raw(WETEXTDOMAIN.'/api/wordpress/keyword_availability.php'); ?>";
				jQuery.ajax({
					 type: "POST",
					 url: wetext_ajaxurl,
					 headers: {"api_key": "<?php echo WETEXTAPIKEY; ?>"},
					 data: wetext_data,					 
					 dataType: "json",
					 success: function(wetext_response) {						
						wetext_data_response = wetext_response[0]['responseType'];						
						if(wetext_data_response=="Success"){
						
							jQuery("#hidkeywordval").val('');
							jQuery("#keyword").removeClass("invalid");
							jQuery("#keywordmsg").html("&nbsp;");
							jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");
							
						}else{
							
							jQuery("#hidkeywordval").val('1');
							jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");
							jQuery("#keyword").addClass("invalid");
							jQuery("#keywordmsg").html("This keyword already exists.");
							//jQuery("#keywordmsg").html(data);
							jQuery("#keyword").focus();
							
						}				
					 }
				});
				
		  }else{
			jQuery("#hidkeywordval").val('');
			jQuery("#keywordmsg").html('&nbsp;');
			jQuery("#loader").attr("src","<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/trans.png'); ?>");
			jQuery("#keyword").focus();
		  }	  
		}	  
	}

	/* // (EOF) While typing on text box  // */
		
	jQuery('#pick_keyword_submit_back').click(function() {		
		window.location.href = "<?php echo esc_url_raw($base_path.'&mode=settings&tab=verify_info'); ?>";
	});	
	
});	


function checkforNospace(strval){
	if (/\w+\s+\w+/.test(strval)) {
		return true;
	} else {
		return false;
	}
}

</script>



<h2><span>Pick Your Keyword</span></h2>

<form name="wetextform_pick_keyword" id="wetextform_pick_keyword" action="" method="post">
<table class="form-table form-table1">
<tbody>
<tr>
	<td width="80%"><input type="text" class="regular-text <?php echo $inputcss; ?>" placeholder="Create a short keyword (short is best) 4 ur followers e.g. pizza, lulaluv, spa, etc" id="keyword" name="keyword" maxlength="12" value="<?php echo $keywordselectedval;?>">	
	<div style="width:16px; float:left;"><img src="<?php echo WETEXT_PLUGIN_URL; ?>images/trans.png" id="loader"></div><div id="keywordmsg"><?php if(isset($msg) && $msg!=''){ echo $msg; } ?></div>	
	<p>Select a keyword that is unique to your business. Keep it short so it's easy for your customers.</p>
	<br>
		For example: 
		<ol>
         <li> Amore Pizza - Keyword should be Amore</li>
         <li>Zumba with JoAnne - Keyword should be ZumbaJo</li>
		</ol>
        <input type="button" value="Back" class="button" id="pick_keyword_submit_back" name="submit">
		<input type="submit" value="Next" class="button button-primary" id="pick_keyword_submit" name="pick_keyword_submit">
		
	</td>
	<td colspan="2" width="20%">
		<input type="hidden" id="hidkeywordval" value="<?php echo $hidval; ?>">
		<div style="position: relative;">
			<img src="<?php echo WETEXT_PLUGIN_URL; ?>images/ticMobile.png" alt="mobile">
			<div class="posTextArea" style="position: absolute; border-radius: 5px; top: 110px; left: 25px; font-size: 20px; padding: 1px 10px; color: rgb(255, 255, 255); background: rgb(238, 238, 238) none repeat scroll 0% 0%; max-width: 100px; width: 80%;">
			<span id="show_keyword_in_mob" style="font-size: 14px;font-weight: bold;"><?php echo $keywordselectedval;?></span>
			</div>
		</div>
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