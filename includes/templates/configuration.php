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
	
	if(isset($_POST['btnconfigurationsubmit'])){		
		
		$table_name   = 'wetext_settings';	
		$publish_post_status = isset($_POST['chkpnpactive'])&&$_POST['chkpnpactive']=='y'  ? 'y' : 'n';
		$publish_post_message = sanitize_textarea_field(stripslashes_deep($_POST['txtnewpostmessage']));
		$woocomerce_new_order_status = isset($_POST['chkwcactive']) ? 'y' : 'n';
		$woocomerce_new_order_message_body = sanitize_textarea_field(stripslashes_deep($_POST['txtwoocomercemessage']));
		
		$update_data  = array( 'publish_new_posts_status' => $publish_post_status, 'publish_new_posts_message_body' => $publish_post_message, 'woocommerce_new_order_status' => $woocomerce_new_order_status, 'woocommerce_new_order_message_body' => $woocomerce_new_order_message_body );
				
		$configuration_data = serialize($update_data);
		
		if($fetch_settings->wetext_update_configuration($configuration_data)){
			echo "<script>window.location = '".esc_url_raw($base_path.'&wtab=configuration&message=success')."';</script>";			
		}
	}
	
	/* // ---------- Fetch configuration ----------- // */
	$get_configuration = $fetch_settings->wetext_get_configuration();
	$configuration	   = unserialize($get_configuration->wetext_configuration);	
		
?>
<div class="stapsTabs">
	<ol class="cd-multi-tabs text-bottom">
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=admin_profile');?>">Admin Profile</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=message_usage');?>">Message Usage</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=manage_api');?>">Manage API</a></li>
		<li class="active_tab"><a href="<?php echo esc_url_raw($base_path.'&wtab=configuration');?>">Configuration</a></li>
		<li class=""><a href="<?php echo esc_url_raw($base_path.'&wtab=invite_people');?>">Invite People</a></li>
	</ol>
</div>
<form name="configurationfrm" action="" method="post">
<table class="form-table">
<tbody>
<tr>
	<td colspan="2"><?php if(isset($_GET['message']) && $_GET['message']=='success'){ echo '<span class="message_text">Record updated successfully.</span>'; } ?></td>
</tr>

<tr>
<td colspan="2"><h2><span>Publish new posts</span></h2></td>
</tr>
<tr>
	<td width="15%" valign="top">
		<strong>Status:</strong>
	</td>
	<td valign="middle">
		<div style="margin-bottom:8px;"><input type="checkbox" id="pnpactive" name="chkpnpactive" <?php if($configuration['publish_new_posts_status']=='y'){ echo 'checked="checked"';} ?> value="y" > <label for="pnpactive">Active</label></div>
		<div><em class="emText">Set the status active if you want to send SMS to the subscribers when you publish a new post.</em></div>
	</td>
</tr>
<tr>
	<td width="10%" valign="top">
		<strong>Message Body:</strong>
	</td>
	<td>
		<div style="margin-bottom:8px;"><textarea name="txtnewpostmessage" id="txtnewpostmessage" rows="9" style="width:90%;"><?php echo $configuration['publish_new_posts_message_body']; ?></textarea></div>
		<div><em class="emText">Enter the contents of the sms message.</em>
		<p><em class="emText">Post title: <span>%post_title%</span>, Post url: <span>%post_url%</span>, Post date: <span>%post_date%</span></em></p></div>
	</td>
</tr>
<tr>
<td colspan="2"><h2><span>Woocommerce</span></h2></td>
</tr>
<tr>
	<td width="15%" valign="top">
		<strong>New Order:</strong>
	</td>
	<td valign="middle">
		<div style="margin-bottom:8px;"><input type="checkbox" id="wcactive" name="chkwcactive" <?php if($configuration['woocommerce_new_order_status']=='y'){ echo 'checked="checked"';} ?> value="y"> <label for="wcactive">Active</label></div>
		<div><em class="emText">Set the status active, so when a new order is placed by your customer. Your customer will receive the order details via text.</em></div>
	</td>
</tr>
<tr>
	<td width="10%" valign="top">
		<strong>Message Body:</strong>
	</td>
	<td>
		<div style="margin-bottom:8px;"><textarea name="txtwoocomercemessage" id="txtwoocomercemessage" rows="9" style="width:90%;"><?php echo $configuration['woocommerce_new_order_message_body']; ?></textarea></div>
		<div><em class="emText">Enter the contents of the sms message.</em>
		<p><em class="emText">Order date: <span>%order_date%</span>, Product SKU: <span>%product_sku%</span>, Product name: <span>%product_name%</span>, Order ID: <span>%order_id%</span>, Order Status:<span> %order_status%</span></em></p></div>
	</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" value="Save Changes" class="button button-primary" id="btnconfigurationsubmit" name="btnconfigurationsubmit">		
	</td>
</tr>
</tbody>
</table>
</form>