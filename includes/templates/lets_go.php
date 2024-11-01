<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

	if(!isset($_SESSION["SESLETSGO"])){	
		echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings&tab=verify_info')."';</script>";	
		exit;
	}
				
	if(isset($_POST['btnInvitePeople'])){
		
		include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
		$fetch_settings = new WP_WETEXT_Settings();
		$fetch_rec_val  = $fetch_settings->wetext_get_settings();	
		$fetch_rec 		= unserialize($fetch_rec_val->wetext_info);
		
		$chk_optval = $fetch_settings->wetext_fetch_data( "option_id,option_value", "options", "option_name='wetext_api_details'" );
		
		$mailchimpcounter=0;
		$constantcontactcounter=0;
		$localusercounter=0;
		
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
			$fetch_settings->wetext_update_table( "options",$update_data,"option_id",$chk_optval->option_id );			
		}else{			
			$insert_data  = array( 'option_name' => 'wetext_api_details', 'option_value' => $integration_serialize, 'autoload' => 'no' );	
			$fetch_settings->wetext_insert_data( "options", $insert_data );
		}		
		
		
		## _____________________________ Start to invite users _____________________________ ##
		
		ignore_user_abort(true);
		set_time_limit(0);
		
		$email_headers = array('Content-Type: text/html; charset=UTF-8');
				
		$subject = $_POST['txtinvsub']!='' ? sanitize_text_field(stripslashes_deep($_POST['txtinvsub'])) : 'Signup for Texting!';
		
		$message = $_POST['txtwetextmsg']!='' ? nl2br(sanitize_textarea_field(stripslashes_deep($_POST['txtwetextmsg']))) : 'Hello [WeText_Recipient],<br /><br />We are excited to announce we can now send you information and updates via Text.<br />Please click the button below to signup for our text service:<br /><br />[WeText_Invitation_Button]<br /><br />Thanks,<br />'.$_SERVER['SERVER_NAME'];
										
		$buttonlink = '<a href="'.esc_url_raw(WETEXTDOMAIN.'/api/signups.php?id='.$fetch_rec['wetext_signup_api_id']).'" target="_blank" style="padding:4px; background: #0085ba;border-color: #0073aa #006799 #006799;box-shadow: 0 1px 0 #006799;color: #fff;text-decoration: none;">Signup Now</a>';
				
		$message = str_replace('[WeText_Invitation_Button]',$buttonlink,$message);	

				
		
		/* ## ---- Mail-Chimp [Start] ---- ## */
		
		if((isset($_POST['txtmailchimpapikey']) && $_POST['txtmailchimpapikey']!='') && (isset($_POST['txtmailchimplistid']) && $_POST['txtmailchimplistid']!='')){			
			
			$api_key = sanitize_text_field(stripslashes_deep($_POST['txtmailchimpapikey']));
			$list_id = sanitize_text_field(stripslashes_deep($_POST['txtmailchimplistid']));
			$dc      = substr($api_key,strpos($api_key,'-')+1); // us5, us8 etc
			$data    = array();
			$url     = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list_id;
			$url    .= '?' . http_build_query($data);
						
			// connect and get results			
			$post_mailchimp_info = wp_remote_post( esc_url_raw($url), array(
							'method'      => 'GET',														
							'headers'     => array('Content-Type' => 'application/json', 'Authorization' => 'Basic '.base64_encode( 'user:'. $api_key ))							
						));
						
			$mailchimp_body = wp_remote_retrieve_body( $post_mailchimp_info );	
			$body = json_decode($mailchimp_body,true);	
															
			$member_count = $body['stats']['member_count'];
							
			//$emails = array();
			for( $offset = 0; $offset < $member_count; $offset += 50 ) :
				$data = array(
					'offset' => $offset,
					'count'  => 50
				);
			 
				// URL to connect
				$url  = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list_id.'/members';
				$url .= '?' . http_build_query($data);
				
				// connect and get results										
				$post_mailchimp_info = wp_remote_post( esc_url_raw($url), array(
							'method'      => 'GET',														
							'headers'     => array('Content-Type' => 'application/json', 'Authorization' => 'Basic '.base64_encode( 'user:'. $api_key ))							
						));
						
				$body_mailchimp_member_info = wp_remote_retrieve_body( $post_mailchimp_info );
				
				$body = json_decode($body_mailchimp_member_info);
									 
				foreach ( $body->members as $member ) {
					
					$mcsubscriberName = $member->merge_fields->FNAME.' '.$member->merge_fields->LNAME;
					
					// email users 
					if(!empty($member->email_address)){
						//$fetch_settings->wetext_insert_data( "wetext_subscribers", $subscribers_mail_chimp_data );
						$message_body_mailchimp = str_replace('[WeText_Recipient]',$mcsubscriberName,$message);							
						wp_mail( $member->email_address, $subject, $message_body_mailchimp, $email_headers );
						$mailchimpcounter = ($mailchimpcounter+1);
					}								
				}
			 
			endfor;					
		}
		
		/* ## ----- Mail-Chimp [EOF] ----- ## */
		
		
		/* ## ---- Constant Contact [Start] ---- ## */
		
		if((isset($_POST['txtccapikey']) && $_POST['txtccapikey']!='') && (isset($_POST['txtccaccesstoken']) && $_POST['txtccaccesstoken']!='')){	
			
			$apiKey = sanitize_text_field(stripslashes_deep($_POST['txtccapikey']));
			$accessToken = sanitize_text_field(stripslashes_deep($_POST['txtccaccesstoken']));			 
			$url = "https://api.constantcontact.com/v2/contacts?status=ALL&api_key=".$apiKey;
			
			$post_constant_contact_info = wp_remote_post( esc_url_raw($url), array(
							'method'      => 'GET',														
							'headers'     => array('Authorization' => 'Bearer '.$accessToken)							
						));
						
			$constant_contact_response = wp_remote_retrieve_body( $post_constant_contact_info );
						
			$constant_contact_val = json_decode($constant_contact_response);
			
			foreach ($constant_contact_val->results as $key){
				$subscriberName = $key->first_name.' '.$key->last_name;				
				if($key->cell_phone!=''){
					$subscriberPhone = $key->cell_phone;
				}else if($key->cell_phone=='' && $key->home_phone=='' && $key->work_phone!=''){
					$subscriberPhone = $key->work_phone;
				}else if($key->cell_phone=='' && $key->home_phone!='' && $key->work_phone==''){
					$subscriberPhone = $key->home_phone;
				}
							
				// send email to users				
				
				if(!empty($key->email_addresses[0]->email_address)){
					//$fetch_settings->wetext_insert_data( "wetext_subscribers", $subscribers_constant_contact_data );					
					$message_body_cc = str_replace('[WeText_Recipient]',$subscriberName,$message);							
					wp_mail( $key->email_addresses[0]->email_address, $subject, $message_body_cc, $email_headers );	
					$constantcontactcounter = ($constantcontactcounter+1);
				}								
			}			
		}
		
		/* ## ---- Constant Contact [Eof] ---- ## */
		
		
		/* ## ---- invite people from my website's address book [Start] ---- ## */
			
		if(isset($_POST['chklocadbook'])){
							
			$args = array( 'meta_key' => 'wp_capabilities',  'meta_value' => 'Administrator', 'meta_compare' => 'NOT LIKE', 'order' => 'ASC','orderby' => 'display_name' );
							
			$wp_user_query = new WP_User_Query($args);
			$authors = $wp_user_query->get_results();
			if (!empty($authors)) {
				
				foreach ($authors as $author) {
					$author_info = get_userdata($author->ID);									
										
					/* // == Mking local user name // */
					
					if(!empty($author_info->display_name)){
						$message_body_localuser = str_replace('[WeText_Recipient]',$author_info->display_name,$message);
					}else{
						$message_body_localuser = $message;
					}
					
					wp_mail( $author_info->user_email, $subject, $message_body_localuser, $email_headers );
					$localusercounter = ($localusercounter+1);
					
				}					
			} 
		}
		
		/* ## ---- invite people from my website's address book [Eof] ---- ## */
		
		$totaluserImport = base64_encode('&'.$mailchimpcounter.'&'.$constantcontactcounter.'&'.$localusercounter);

		## _____________________________ End to invite users _____________________________ ##		
		
		echo "<script>window.location = '".esc_url_raw($base_path.'&mode=settings&tab=lets_go&message=success&rec='.$totaluserImport)."';</script>";		
	}
		
?>

<script>
jQuery( document ).ready(function() {
	jQuery('#btnInvitePeople').click(function() {
		
		jQuery(".error_text").remove();
		jQuery('input').removeClass("invalid");
		
		var isChecked = jQuery("#chklocadbook").is(":checked");
		
        if ((!isChecked) && (jQuery.trim(jQuery("#txtmailchimpapikey").val())=='') && (jQuery.trim(jQuery("#txtmailchimplistid").val())=='') && (jQuery.trim(jQuery("#txtccapikey").val())=='') && (jQuery.trim(jQuery("#txtccaccesstoken").val())=='') && (jQuery.trim(jQuery("#txtsfusername").val())=='') && (jQuery.trim(jQuery("#txtsfpass").val())=='') && (jQuery.trim(jQuery("#txtsfsectiken").val())=='')) {
			
			alert("To invite people, please put Mail-Chimp/Constant Contact/Sales Force required information or check the Also invite people from my website's address book checkbox.");
			return false;
		
		}else if((jQuery.trim(jQuery("#txtmailchimpapikey").val())!='') && (jQuery.trim(jQuery("#txtmailchimplistid").val())=='')){
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

<div class="stapsSection">
<ol class="cd-multi-steps text-bottom">
<li class="completed">Verify your info</li>
<li class="completed">Pick your Keyword</li>
<li class="completed">Cell number verification</li>
<li class="completed">Referral Code</li>                           
<li class="completed">Let's Go</li>
</ol>
</div>
<h2><span>Let's Go</span></h2>
<table class="form-table form-table1">
<tbody>
<tr>
	<td>
		<?php 			
			if(isset($_GET['message']) && $_GET['message']=='success'){ 
				$getinvcounterdata = base64_decode($_GET['rec']);
				$invUser = explode('&',$getinvcounterdata);				
				$mc = '<li>Mail-Chimp invitations = '.$invUser[1].'</li>';
				$cc = '<li>Constant Contact invitations = '.$invUser[2].'</li>';
				$lu = '<li>Website\'s Address Book = '.$invUser[3].'</li>';	
				$invrec = '<ul>'.$mc.$cc.$lu.'</ul>';				
				$totalinvitation = ($invUser[1]+$invUser[2]+$invUser[3]);				
				echo '<div class="message_text">Total '.$totalinvitation.' Invitations sent.'.$invrec.'</div>';
			}
		?>
	</td>
</tr>

<tr>
	<td>	
		<div style="font-size:18px;padding: 0 0 30px 0; text-align:center;"><strong>Your Free WeText Services is now active!</strong></div>
		
		<div style="text-align:left;"><p style="font-weight:normal;">Now let's invite some people to opt-in so you can start texting them. Opt-in is required by law in USA and many other countries.</p>		
		<p style="font-weight:normal;"><br>If you use Mail-Chimp, Constant Contact or Sales Force, we can now send an invite to your email list. Please enter login data below. If you do not use any of these services, scroll down to "Additional Opt-in Methods".</p></div>		
		
			
	</td>
</tr>
</tbody>
</table>
<form name="frminvite" action="" method="post">
<table class="form-table form-table1">
<tbody>
<tr>
	<td width="50%">
		<table class="form-table form-table1">
			<tbody>
				<tr>
					<td><strong>Mail-Chimp</strong></td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Mail-Chimp API Key" id="txtmailchimpapikey" name="txtmailchimpapikey" value="">
					</td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Mail-Chimp List ID" id="txtmailchimplistid" name="txtmailchimplistid" value="">
					</td>	
				</tr>
				<tr>
					<td><strong>Constant Contact</strong></td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Constant Contact API Key" id="txtccapikey" name="txtccapikey" value="">
					</td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Constant Contact Access Token" id="txtccaccesstoken" name="txtccaccesstoken" value="">
					</td>	
				</tr>
				<tr>
					<td><strong>Sales Force</strong>&nbsp;(<span><em>Coming Soon</em></span>)</td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Sales Force Username" id="txtsfusername" name="txtsfusername" disabled="disabled" style="background:#d2d0d04f;">
					</td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Sales Force Password" id="txtsfpass" name="txtsfpass" disabled="disabled" style="background:#d2d0d04f;">
					</td>	
				</tr>
				<tr>
					<td>						
						<input type="text" class="regular-text" placeholder="Sales Force Security Token" id="txtsfsectiken" name="txtsfsectiken" disabled="disabled" style="background:#d2d0d04f;">
					</td>	
				</tr>
			</tbody>
		</table>
	</td>	
	<td width="50%">
		<table class="form-table form-table1">
			<tbody>
				<tr>
					<td>
						<strong>Email Preview</strong>
					</td>	
				</tr>
				<tr>
					<td>
						Invitation email subject:
						<input type="text" class="regular-text" placeholder="Invitation email subject" id="txtinvsub" name="txtinvsub" value="Signup for Texting!">
					</td>	
				</tr>
				<tr>
					<td>
						Invitation email content:
						<textarea style="width:100%" rows="13" id="txtwetextmsg" name="txtwetextmsg">Hello [WeText_Recipient],
						
We are excited to announce we can now send you information and updates via Text. Please click the button below to signup for our text service:

[WeText_Invitation_Button]

Thanks,
<?php echo $_SERVER['SERVER_NAME']; ?></textarea>
<br><em class="emText">Recipient Name: <span>[WeText_Recipient]</span>, Invitation Button With Link: <span>[WeText_Invitation_Button]</span></em>

					</td>	
				</tr>
			</tbody>
		</table>
	</td>	
</tr>

<tr>
<td colspan="2">
	<input type="checkbox" id="chklocadbook" name="chklocadbook" value="1" /> <span><strong><label for="chklocadbook">Also invite people from my website's address book (<?php echo isset($_SERVER['HTTPS']) ? 'https://' : 'http://';?><?php echo $_SERVER['SERVER_NAME']; ?>)</label></strong></span>
</td>
</tr>
<tr>
<td colspan="2">
	<div style="text-align:center;">			
		<input type="submit" class="button button-primary bt_margin" value="Invite People Now" name="btnInvitePeople" id="btnInvitePeople">	
	</div>	
</td>
</tr>
</tbody>
</table>
</form>		
<div class="clear"></div>

<?php

		include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
		$fetch_settings = new WP_WETEXT_Settings();
		$fetch_rec_val  = $fetch_settings->wetext_get_settings();	
		$fetch_rec 		= unserialize($fetch_rec_val->wetext_info);	
		
?>


<div style="height:1px; border-bottom:1px solid #bfbfbf;"></div>
<h2><span>Additional Opt-in Methods:</span></h2>
<table class="form-table form-table1">
<tbody>
<tr>
	<td>		
		<div style="text-align:left;">This plugin gives you two other ways to opt-in people.</div>
			<ol>
				<li>You can always add a user manually by clicking on the Add New User button below which is also found on the Subscribers tab. Feel free to add yourself now for testing.<span style="color: #f00"> Please disable any pop-up
blockers before clicking on the button.</span>
				<p><a href="<?php echo esc_url_raw(WETEXTDOMAIN.'/api/signups.php?id='.$fetch_rec['wetext_signup_api_id']); ?>" target="_blank" class="button button-primary">Add New User</a></p>
				</li>
				<li>You can invite people to opt-in by sharing this link via web, email, social media or other means: &lt;<a href="<?php echo esc_url_raw(WETEXTDOMAIN.'/api/signups.php?id='.$fetch_rec['wetext_signup_api_id']); ?>" target="_blank"><?php echo esc_url_raw(WETEXTDOMAIN.'/api/signups.php?id='.$fetch_rec['wetext_signup_api_id']); ?></a>&gt; Please save this link for your record. It can also be found on the Invite People tab.</li>
			</ol>
		
		<div style="text-align:left;">			
			If you have added at least one user, you are now ready to send your first text message. Please click on the <a href="<?php echo esc_url_raw($base_path.'&mode=messages'); ?>">Messages</a> tab to get started!
		</div>		
	</td>
</tr>
</table>
<div class="clear"></div>

<div class="footer_outer">
	<div class="footer_box">
		<div class="lft"><img src="<?php echo WETEXT_PLUGIN_URL; ?>images/logo_big.png" width="107" alt="wetext logo" style="height:auto;" /></div>
		<div class="rgt"><strong>Opting Out: </strong>Reply "stop" via Text and wait for the confirmation reply. Carrier message and data rates may apply. By clicking "Submit" you agree to the <a href="https://wetext.co/wp-content/uploads/2016/11/Privi-Terms-and-Conditions.pdf" target="_blank">Terms & Conditions.</a></div>
	</div>
</div>