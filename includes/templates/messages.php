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
	
	/* // Fetch opt-in users [start] // */	
		
		$url     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_subscribers.php?token='.$fetch_rec['wetext_api_key']);				
		$subscribers_info = wp_remote_post( $url, array('method' => 'GET') );						
		$get_subscribers_info = wp_remote_retrieve_body( $subscribers_info );			
		$fetch_users = json_decode($get_subscribers_info,true);	
				
	/* // Fetch Opt-in users [end] // */
		
	if(isset($_POST['btnSendMsg'])){
		
		if(!empty($_POST['secrecipients'])){
			$recipients_email = '';
			$recipients_phone = '';
			foreach($_POST['secrecipients'] as $key_recipients){				
				$recipients_data   = explode('&',$key_recipients);					
				$recipients_phone .= $recipients_data[0].',';
				$recipients_email .= $recipients_data[1].',';				
			}
		}
		
		$recipients_email = trim($recipients_email,',');		
		$recipients_phone = trim($recipients_phone,',');
		
		$medialink = !empty($_POST['txtmedialink']) ? "&mediaUrl=".sanitize_text_field(stripslashes_deep($_POST['txtmedialink'])) : '';
		
		if(isset($_POST['chkrecemail']) && $_POST['chkrecemail']=='1'){
			$emailcopy = '&emails='.$recipients_email;
		}else{
			$emailcopy = '';
		}
		
		$message = urlencode(sanitize_textarea_field(stripslashes_deep($_POST['txtwetextmsg'])));
			
		$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/send.php?recipients=".$recipients_phone."&message=".$message."&token=".$fetch_rec['wetext_api_key'].$emailcopy.$medialink);	
		$send_sms_info = wp_remote_post( $url, array('method' => 'GET') );						
		$send_sms = wp_remote_retrieve_body( $send_sms_info );	
		$send_sms_response = json_decode($send_sms,true);
						
		if($send_sms_response[0]['responseType']=='Success'){
			echo "<script>window.location = '".esc_url_raw($base_path.'&mode=messages&response=success')."';</script>";					
		}else{
			$responseMessage = $send_sms_response[0]['responseMessage']!='' ? esc_html($send_sms_response[0]['responseMessage']) : 'Error!! Try again';				
			echo "<script>window.location = '".esc_url_raw($base_path.'&mode=messages&response=error&msg='.base64_encode($responseMessage))."';</script>";					
		}				
	}
	
?>

<!-- ----------------------------------------------------------- -->
<style>
ul{margin:0;padding:0;}
#slider7{height:auto;overflow:hidden;}
#slider7 .viewport{width:249px; height:454px; float:left; overflow:hidden; position:relative;}
#slider7 .disable{visibility:hidden;}
#slider7 .overview{list-style:none;position:absolute;width:240px;left:0 top:0;}
#slider7 .overview li{float:left;padding:1px;height:454px;width:249px;}
</style>
<!-- ----------------------------------------------------------- -->

<script>
jQuery( document ).ready(function() {
	var setmaxLength = 1600;
	jQuery('#txtwetextmsg').keyup(function() {
	  var length = jQuery(this).val().length;
	  var length = setmaxLength-length;	
	  jQuery('#chars_remaining').text(length);	  
	  if(length==0){
		  jQuery("#txtwetextmsg").maxlength();	  
	  }	  
	});

	jQuery("#btnSendMsg").click(function() {
		
		jQuery("#txtwetextmsg, #secrecipients").removeClass("invalid");
		jQuery("#wetextmsgdiv, #wetextrecipientsdiv").html("");	
		
		if (!jQuery("#secrecipients option:selected").length) {	
			jQuery("#secrecipients").addClass("invalid");
			jQuery("#wetextrecipientsdiv").html('Please select subscriber.');	
			return false;
		}else if(jQuery.trim(jQuery("#txtwetextmsg").val())==''){
			jQuery("#txtwetextmsg").addClass("invalid");
			jQuery("#wetextmsgdiv").html('This field is required.');	
			return false;
		}
	});
});
</script>
<table class="form-table">
<tr>
<td colspan="2"><h2><span>Send Messages</span></h2></td>
</tr>
<tr>
	<td width="70%">
		<form name="frmsendmsg" action="" method="post">
		<table width="100%">
			<tr>
				<td width="20%">&nbsp;</td>
				<td width="80%">
				<?php 
					if(isset($_GET['response']) && $_GET['response']=='success'){ 
						echo '<span class="message_text">Message send successfully</span>';	
					}else if(isset($_GET['response']) && $_GET['response']=='error'){ 
						echo '<span class="error_msg_box">'.base64_decode($_GET['msg']).'</span>';	
					}				
				?>				
				</td>
			</tr>
			<tr>
				<td width="20%">
					<strong>To :</strong>
				</td>				
				<td width="80%">
				<div style="margin-bottom:8px;"><em class="emText">Only the <span>Opted-in</span> subscribers are shown in the address book.</em></div>
				<div style="margin-bottom:8px;">
				<?php
					if(!empty($fetch_users)){
				?>
					<select multiple="multiple" id="secrecipients" name="secrecipients[]">
						<?php
							foreach($fetch_users as $users){								
						?>
						<option value="<?php echo $users['Cell'].'&'.$users['Email']; ?>"><?php echo $users['FirstName'].' '.$users['LastName']; ?></option>
						<?php								
							}
						?>
					</select>	
				<?php
					}else{
						echo '<span class="error_text">No subscribers are found.<br>Please go to Subscribers tab and add/invite users.</span>';
					}
				?>
				</div>
				<div id="wetextrecipientsdiv" class="error_text"></div>
				<div><em class="emText">Ctrl + Click to select multiple recipents.</em></div>
				</td>
			</tr>			
			<tr>
				<td width="20%">
					<strong>Message :</strong>
				</td>				
				<td width="80%">				
				<div style="margin-bottom:8px;">
					<textarea style="width:100%" rows="5" id="txtwetextmsg" name="txtwetextmsg" maxlength="1600"></textarea>
				</div>
				<div id="wetextmsgdiv" class="error_text"></div>				
				<div><em class="emText">Characters: <span id="chars_remaining">1600</span>&nbsp;(remaining)<br>Links are allowed in the message.</em></div>
				</td>
			</tr>			
			<tr>
				<td width="20%" valign="top">
					<strong>Media URL :</strong>
				</td>				
				<td width="80%">				
				<div style="margin-bottom:8px;">
					<input type="text" placeholder="Example: http://wetext.co/wetext.jpg" style="width:100%" name="txtmedialink" />
				</div>
				<div>
					<ul>
						<li><em class="emText">Attach a media file (video, audio or picture only) within your text message.</em></li>
						<li><em class="emText">Picture (.jpg), Audio (.amr) Video (.3gp) only.</em></li>
						<li><em class="emText">File size must be 300kb or less.</em></li>
						<li><em class="emText">URL must start with http:// and file must be publicly accessible.</em></li>
					</ul>
				</div>
				</td>
			</tr>			
			<tr>
				<td width="20%" valign="top">&nbsp;</td>				
				<td width="80%">				
				<div style="margin-bottom:8px;">
					<input type="checkbox" id="chkrecemail" name="chkrecemail" value="1" /> <em><strong><label for="chkrecemail">Send a copy to the recipient's email</label></strong></em>
				</div>				
				</td>
			</tr>			
			<tr>
				<td width="20%" valign="top">&nbsp;</td>
				<td width="20%" valign="top">
					<?php
					if(!empty($fetch_users)){
					?>
					<input type="submit" class="button button-primary" value="Send" name="btnSendMsg" id="btnSendMsg">
					<?php }else{  ?>
					<input type="button" class="button" value="Send" name="btnSendMsg" id="btndisable">
					<?php } ?>
				</td>
				<td width="20%" valign="top">&nbsp;</td>			
			</tr>			
			<tr>
				<td colspan="3">&nbsp;</td>						
			</tr>			
		</table>
		</form>
	</td>	
	<td width="30%">
		<?php
			$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/carousel_images.php");			
			$carousel_info = wp_remote_post( $url, array(
							'method'      => 'GET',													
							'headers'     => array('api_key' => WETEXTAPIKEY)							
						));						
			$getcarousel = wp_remote_retrieve_body( $carousel_info );			
			$carousel_imgs = json_decode($getcarousel,true);
			
			/* ////////////////////////////// C R A S O U L ~ S T A R T ////////////////////////////// */			
						
		?>
		<p style="font-weight:800;margin-bottom:5px;">Example of WeText in Action</p>
		<div id="slideshow" style="border:1px solid #cec5c5; padding:2px; text-align:center;">			 
			<div id="slider7">                    
				<div class="viewport">
					<ul class="overview">
						<?php
							foreach($carousel_imgs as $ci=>$vl){								
								$imageurl = esc_url_raw("https://www.wetext.co/api/wordpress/scroll/".$vl);								
								echo '<li><img src="'.$imageurl.'" alt="" width="256" height="459"></li>';					
							}
						?>										
					</ul>
				</div>                    
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#slider7").tinycarousel({ interval: true,intervalTime : 8000 });                            
					});
				</script>
			</div>  
		   <!-- ////////////////////////////// C R A S O U L ~ E N D ////////////////////////////// -->  
		</div>
	</td>	
</tr>
</table>
<div style="border-top:1px solid #cec5c5;"></div>
<table class="form-table">
<tr>
<td>
	<h2><span>Message History (Last 3 Months)</span></h2>	
</td>
</tr>
</table>

<table class="wp-list-table widefat fixed striped users">
	<thead>
	<tr>		
		<th scope="col" id="title" class="manage-column column-title column-primary">Message</th>
		<th scope="col" id="tofrom" class="manage-column column-role">To/From</th>
		<th scope="col" id="direction" class="manage-column column-author">Direction</th>
		<th scope="col" id="type" class="manage-column column-comments">Type</th>
		<th scope="col" id="date" class="manage-column column-date" style="width:11%;">Date</th>		
		<th scope="col" id="result" class="manage-column column-author" style="width:11%;">Result</th>
	</tr>
	</thead>

	<tbody id="the-list" data-wp-lists="list:message">
	
	<?php
		$date_on_prevoius_month_making = strtotime("-3 month");			
		$start_date = date('Y-m-d%20H:i:s',$date_on_prevoius_month_making);
		$end_date   = date('Y-m-d%20H:i:s');	
		$url = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_messages.php?token='.$fetch_rec['wetext_api_key'].'&start='.$start_date.'&end='.$end_date);	
		$message_data_info = wp_remote_post( $url, array('method' => 'GET') );						
		$fetch_message_data = wp_remote_retrieve_body( $message_data_info );		
		$message_data = json_decode($fetch_message_data,true);
						
		if(!empty($message_data)){
			if($message_data[0]['responseType']!='Error'){			
			
				foreach($message_data as $message_history){					
												
					$message_date = strtotime($message_history['Date']);
					$message_subject = $message_history['Subject']!='' ? $message_history['Subject'] : '{null}';
					$message_attachment = $message_history['Attachment']!='' ? $message_history['Attachment'] : '{null}';
				
	?>
		
	<tr id="message-1">		
		<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
		<?php 
			if($message_subject!='{null}'){
				echo 'Subject: '.esc_html($message_subject).'<br>';
			}	
			
			echo $message_history['Message']; 
						
			if($message_attachment!='{null}'){
				echo '<br>Attachment: <a href="'.esc_url_raw($message_attachment).'" target="_blank">'.esc_url_raw($message_attachment).'</a>';
			}
		?>
		<br>	
		<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
		
		<td class="tofrom column-role" data-colname="To/From"><?php echo esc_html($message_history['To/From']); ?></td>
		<td class="direction column-author" data-colname="Direction"><?php echo esc_html($message_history['Direction']); ?></td>
		<td class="type column-comments" data-colname="Type"><?php echo esc_html($message_history['Type']); ?></td>
		<td class="date column-date" data-colname="Date" style="width:11%;"><?php echo date('m/d/Y',$message_date).'<br>'.date('H:i A',$message_date); ?></td>		
		<td class="result column-author" data-colname="Result" style="width:11%;">
		<?php
			if(strstr($message_history['Result'],'@')){								
				echo str_replace(": Successful"," -> Successful<br>",$message_history['Result']);
			}else{
				$resultval = explode(':', $message_history['Result']);
				if(!empty($resultval)){
					foreach($resultval as $keyresult){
						if(!empty($keyresult)){
							
							echo esc_html($keyresult).'<br>';
						}
					}
				}
			}
		?>
		</td>
	</tr>
	
	<?php
				}
			}else{
				echo '<tr><td colspan="6"><div class="error_text">'.esc_html($message_data[0]['responseMessage']).'</div></td></tr>';
			}
		}else{
	?>
	<tr>
		<td colspan="6"><div class="error_text">No records found.</div></td>
	</tr>
	<?php 
		}
	?>
		
	</tbody>
	<tfoot>
	<tr>
		
		<th scope="col" class="manage-column column-title column-primary"> Message</th>
		<th scope="col" class="manage-column column-role">To/From</th>
		<th scope="col" class="manage-column column-author">Direction</th>
		<th scope="col" class="manage-column column-comments">Type</th>
		<th scope="col" class="manage-column column-date" style="width:11%;">Date</th>	
		<th scope="col" class="manage-column column-author" style="width:11%;">Result</th>	
		
	</tr>
	</tfoot>

</table>