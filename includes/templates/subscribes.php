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
	
	// ##### Sync User When Load The Page [Start] ##### //	
	
	$url     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_subscribers.php?token='.$fetch_rec['wetext_api_key']);		
	$subscribers_info = wp_remote_post( $url, array('method' => 'GET' ));						
	$get_subscribers_info = wp_remote_retrieve_body( $subscribers_info );	
	$subscribers_info = json_decode($get_subscribers_info,true);
	
	// ##### Sync User When Load The Page [End] ##### //	
			
?>

<table class="form-table">
	<tr>
		<td width="50%">
			<table>
				<tr>			
					<td><a href="<?php echo esc_url_raw($base_path.'&wtab=invite_people'); ?>" class="button button-primary">Invite Users</a></td>			
					<td><a href="<?php echo esc_url_raw(WETEXTDOMAIN.'/api/signups.php?id='.$fetch_rec['wetext_signup_api_id']); ?>" target="_blank" class="button button-primary">Add New User</a></td>			
				</tr>
			</table>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>

<!-- --------------------------------------------------------------------- -->
<?php
	if(!empty($subscribers_info)){			
?>
<table id="subscribers" class="display responsive nowrap" style="width:100%">
	<thead>
		<tr>
			<th style="text-align:left;">Name</th>			
			<th style="text-align:left;">Email</th>  
			<th style="text-align:left;">Phone Number</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($subscribers_info as $key_subscribers_info){				
		?>
		<tr>
			<td><?php echo esc_html($key_subscribers_info['FirstName'].' '.$key_subscribers_info['LastName']); ?></td>			
			<td><?php echo esc_html($key_subscribers_info['Email']); ?></td>   
			<td><?php echo esc_html($key_subscribers_info['Cell']); ?></td>			
		</tr>
		<?php				
			}
		?>
	</tbody>
	<tfoot>
		<tr>
			<th style="text-align:left;">Name</th>
			<th style="text-align:left;">Email</th> 
			<th style="text-align:left;">Phone Number</th>			
		</tr>
	</tfoot>
</table>
	
<script>
	jQuery(document).ready(function() {
		jQuery('#subscribers').DataTable( {
			"pageLength": 50,
			"bLengthChange": false,
			responsive: true,
			order: [],
			columnDefs: [ { orderable: false, targets: [0,1,2] } ],
			columns: [ { width: '40%' }, { width: '40%' }, { width: '20%' } ]
		} );
	} );
</script>
<?php		
	}else{
?>

<table class="wp-list-table widefat fixed striped users">
	<thead>
		<tr>
			<th style="text-align:left;">Name</th>			
			<th style="text-align:left;">Email</th>  
			<th style="text-align:left;">Phone Number</th>
		</tr>
	</thead>
	<tr>
		<td colspan="3"><div class="error_text">No records found. Please add or invite some users</div></td>
	</tr>
	<tr>
		<th style="text-align:left;">Name</th>			
		<th style="text-align:left;">Email</th>  
		<th style="text-align:left;">Phone Number</th>
	</tr>
</table>

<?php
	}
?>