<?php
function wetext_chkAuth(){
	include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
	$fetch_settings = new WP_WETEXT_Settings();
	$get_subscribe = $fetch_settings->wetext_get_settings();	
	
	if( empty($get_subscribe->wetext_info) ){
		echo "<script>window.location = '".esc_url_raw('?page=wetext&mode=settings')."';</script>";
		exit;
	}
}
	
$base_path = '?page=wetext';

if(isset($_GET['mode']) && $_GET['mode']=='settings'){
	if(isset($_GET['tab']) && $_GET['tab']=='verify_info'){
		$template_name = 'templates/verify_info.php';
	}else if(isset($_GET['tab']) && $_GET['tab']=='pick_keyword'){
		$template_name = 'templates/pick_keyword.php';
	}else if(isset($_GET['tab']) && $_GET['tab']=='cell_verification'){
		$template_name = 'templates/cell_verification.php';
	}else if(isset($_GET['tab']) && $_GET['tab']=='referral_code'){
		$template_name = 'templates/referral_code.php';
	}else if(isset($_GET['tab']) && $_GET['tab']=='lets_go'){
		$template_name = 'templates/lets_go.php';
	}else{
		$template_name = 'templates/settings.php';
	}
	
	$settings_active_class='active';			
	
}else if(isset($_GET['mode']) && $_GET['mode']=='subscribers'){	

	$subscribes_active_class='active';	
	
	if((isset($_GET['mtype']) && $_GET['mtype']=='add_new')){		
		$template_name = 'templates/add_user.php';
	}else if(isset($_GET['mtype']) && $_GET['mtype']=='import_subscribers'){	
		$template_name = 'templates/import_users.php';
	}else if(isset($_GET['mtype']) && $_GET['mtype']=='edit_subscriber'){	
		$template_name = 'templates/edit_subscriber.php';
	}else if(isset($_GET['mtype']) && $_GET['mtype']=='trash'){	
		$template_name = 'templates/subscribes_trash_list.php';
	}else{	
		$template_name = 'templates/subscribes.php';
	}
	
}else if(isset($_GET['mode']) && $_GET['mode']=='messages'){
	
	$messages_active_class='active';
	$template_name = 'templates/messages.php';
	
}else if(isset($_GET['mode']) && $_GET['mode']=='upgrade_plan'){
	
	$template_name = 'templates/upgrade_plan.php';
	
}else if(isset($_GET['mode']) && $_GET['mode']=='additional_opt_in_methods'){
	
	$template_name = 'templates/additional_opt_in_methods.php';
	
}else{
	$settings_active_class='active';
	$template_name = 'templates/settings.php';
}	
	
?>

<div>&nbsp;</div>
<div class="plugin_nw">
	<div class="left_panel">
		<?php include_once('left_panel.php'); ?>
	</div>
	<div class="right_panel">
		<?php include_once($template_name); ?>		
	</div>	
</div>
