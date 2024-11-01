<?php
session_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if(isset($_SESSION["SESLETSGO"])){
	unset($_SESSION["SESLETSGO"]);
}
include_once WETEXT_PLUGIN_INCLUDE_DIR_PATH. 'class_wetext_settings.php';
$fetch_settings = new WP_WETEXT_Settings();
$get_subscribe = $fetch_settings->wetext_get_settings();
if( empty($get_subscribe->wetext_info) ){	
?>

<p>Let's activate your Free WeText Service. The free service gives you some <span>Free Text, Video, Audio and Picture messages</span> every month. You can add more messages from the plugin anytime.</p>

<a class="activate_btn" href="<?php echo esc_url_raw($base_path.'&mode=settings&tab=verify_info'); ?>">Activate Free Service</a>

<?php
}else{
	
	if(isset($_GET['wtab']) && $_GET['wtab']=='admin_profile'){
		include_once( 'admin_profile.php' );
	}else if(isset($_GET['wtab']) && $_GET['wtab']=='message_usage'){
		include_once( 'message_usage.php' );
	}else if(isset($_GET['wtab']) && $_GET['wtab']=='manage_api'){
		include_once( 'manage_api.php' );
	}else if(isset($_GET['wtab']) && $_GET['wtab']=='configuration'){
		include_once( 'configuration.php' );
	}else if(isset($_GET['wtab']) && $_GET['wtab']=='invite_people'){
		include_once( 'invite_people.php' );
	}else if(isset($_GET['wtab']) && $_GET['wtab']=='additional_opt_in_methods'){
		include_once( 'additional_opt_in_methods.php' );
	}else{		
		include_once( 'admin_profile.php' );
	}
	
 } ?>