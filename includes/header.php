<?php
		include_once dirname(__FILE__). '/class_wetext_settings.php';
		$fetch_settings = new WP_WETEXT_Settings();		
		$fetch_rec_val  = $fetch_settings->wetext_get_settings();
		$fetch_rec 		= unserialize($fetch_rec_val->wetext_info);		
				
		if( $fetch_rec['wetext_api_key']!='' ){
			
		$url     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_usage.php?token='.$fetch_rec['wetext_api_key']);			
		$get_usage = wp_remote_post( $url, array('method'	=> 'GET') );						
		$get_usage_info = wp_remote_retrieve_body( $get_usage );		
		$response_val = json_decode($get_usage_info,true);
						
		if($response_val[0]['responseType']=='Error'){
			echo '<div class="error_msg_box">'.esc_html($response_val[0]['responseMessage']).'</div><br>';
		}	
		
		if($_GET['mode']!='upgrade_plan'){
		
?>
		<div class="header_container">
			<a href="<?php echo esc_url_raw(WETEXTDOMAIN.'/api/wordpress/upgrade.php?token='.$fetch_rec['wetext_api_key']); ?>" target="_blank">UPGRADE PLAN - ADD MESSAGES - CHANGE CREDIT CARD</a>
		</div>

<?php } ?>		
		
		<table class="form-table">
			<tr>
			<td width="50%">
				<strong>PLAN :</strong> <?php echo ucwords(esc_html($response_val[0]['planName'])); ?> <br />
				<strong>PRICE :</strong> $<?php echo esc_html($response_val[0]['planPrice']); ?>
			</td>
			<td>
				<strong>PERIOD START DATE :</strong> <?php echo esc_html($response_val[0]['periodStartDate']); ?><br />
				<strong>PERIOD END DATE :</strong> <?php echo esc_html($response_val[0]['periodEndDate']); ?>
			</td>
			</tr>
			<tr>
				<td colspan="2">
				<div style="width:100%; margin:0 auto 6px auto; text-align:center; overflow:hidden;">
					<div class="planQuota active">PLAN QUOTA: <?php echo esc_html($response_val[0]['planQuota']); ?></div>
					<div class="planQuota">USED: <?php echo esc_html($response_val[0]['totalUsed']); ?></div>
					<div class="planQuota">AVAILABLE: <?php echo esc_html($response_val[0]['totalAvailable']); ?></div>
				</div>
				</td>
			</tr>
		</table>
		
<?php	} ?>