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
<p>Let's activate your Free We Text Service. The free service gives you some <span>Free Text, Video, Audio and Picture messages</span> every month. You can add more messages from the plugin anytime.</p>
<a class="activate_btn" href="<?php echo esc_url_raw($base_path.'&mode=settings&tab=verify_info'); ?>">Activate Free Service</a>
<?php
}
?>