<?php
/*
 * Plugin Name: WeText
 * Plugin URI: http://wetext.co/
 * Description: Keep your customer updated via Text (SMS), Picture, Audio & Video (MMS) messages! Thanks to WordPress, you now have access to the enormous power of Text messaging to grow your business right from your website with this plugin. Solve your sales, marketing, communication and e-commerce problems by embedding videos, audios, pictures, app-like plugins and credit card processing directly in the body of a Text message. 
 * Version: 1.0
 * Author: WeText
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

register_activation_hook   (__FILE__, array("wpwetext_class", "wetext_install"));
register_deactivation_hook (__FILE__, array("wpwetext_class", "wetext_unstall"));

define('WETEXT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WETEXT_PLUGIN_INCLUDE_DIR_PATH', dirname(__FILE__).'/includes/');
define('WETEXTAPIKEY', '612e648bf9594adb50844cad6895f2cf');
define('WETEXTDOMAIN', 'https://www.wetext.co'); 

class wpwetext_class
{
	static function wetext_install (){		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );					
	}	
	
	static function wetext_unstall (){				
		$unlinkPath = WETEXT_PLUGIN_URL.'/wetext';
		unlink($unlinkPath);	
	}	
}

function wetext_admin() {
	include('includes/wetext.php');
}

function wetext_adminmenu_actions() {	
	add_menu_page(__('Wetext','wetext'), __('WeText','wetext'), 'manage_options', 'wetext', 'wetext_admin',WETEXT_PLUGIN_URL . 'images/wetextplugin.png');	
}
add_action('admin_menu', 'wetext_adminmenu_actions');

add_action( 'admin_enqueue_scripts', 'load_wetext_admin_styles' );
function load_wetext_admin_styles() {
	if(isset($_GET["page"]) && ($_GET["page"] == "wetext")){
		wp_enqueue_style( 'wetext', plugins_url('css/style.css',__FILE__ ), false );		
		if(isset($_GET["wtab"]) && ($_GET["wtab"] == "message_usage")){			
			wp_enqueue_script('wetext_amcharts_js', plugins_url('js/amcharts.js',__FILE__ ), false);
			wp_enqueue_script('wetext_serial_js', plugins_url('js/serial.js',__FILE__ ), false);
		}		
		if(isset($_GET["mode"]) && ($_GET["mode"] == "subscribers")){	
			wp_enqueue_style( 'wetext_dataTables', plugins_url('css/jquery.dataTables.min.css',__FILE__ ), false );
			wp_enqueue_style( 'wetext_responsive_dataTables', plugins_url('css/responsive.dataTables.min.css',__FILE__ ), false );			
			wp_enqueue_script('wetext_dataTables_js', plugins_url('js/jquery.dataTables.min.js',__FILE__ ), false);
			wp_enqueue_script('wetext_dataTables_responsive_js', plugins_url('js/dataTables.responsive.min.js',__FILE__ ), false);
		}
		if(isset($_GET["mode"]) && ($_GET["mode"] == "messages")){				
			wp_enqueue_script('wetext_tinycarousel_js', plugins_url('js/jquery.tinycarousel.js',__FILE__ ), false);
		}		
	}
} 

add_action('wp_logout', 'wetext_EndSession');
add_action('wp_login', 'wetext_EndSession');
function wetext_EndSession() {
	session_start();
    unset($_SESSION["SESSIGNUP"]);
	unset($_SESSION["SESLETSGO"]);
}

/* // --- Send notification [Post/Page] (start) --- // */
add_action( 'transition_post_status', 'wetext_send_page_post_notification', 10, 3 );
function wetext_send_page_post_notification( $new_status, $old_status, $post ){
  $post_ID=$post->ID;  
  $postType=get_post_type($post_ID);  
  if($postType=="post" || $postType=="page"){  	  
	if ( 'publish' !== $new_status )
	  return;
	if('publish' != $old_status){		
		global $wpdb, $table_prefix;
		
		$get_user_info = $wpdb->get_row( "SELECT option_value as wetext_info FROM `".$table_prefix."options` WHERE option_name='wetext_user_info'" );
		
		$get_configuration_info = $wpdb->get_row( "SELECT option_value as wetext_configuration FROM `".$table_prefix."options` WHERE option_name='wetext_configuration_info'" );
		
		$get_post_status_data	= unserialize($get_user_info->wetext_info);	
		$get_configuration 		= unserialize($get_configuration_info->wetext_configuration);		
		$get_post_status_result = array_merge($get_post_status_data,$get_configuration);
				
		if(!empty($get_post_status_result) && $get_post_status_result['publish_new_posts_status']=='y'){
		   
			$meta_values = get_post_meta($post_ID, 'wetext_is_notified', true);

			if($meta_values!='yes'){
				$pub_post = get_post($post_ID);
				$author_id=$pub_post->post_author;
				$post_title=$pub_post->post_title;
				$postperma=get_permalink( $post_ID );
				$post_date = $pub_post->post_date;				
				$date_stamp = strtotime($post_date);
				$postdate = date("m-d-Y H:i A", $date_stamp);
				
				$user_info = get_userdata($author_id);
				$usernameauth=$user_info->user_login;
				$user_nicename=$user_info->user_nicename;
				$user_email=$user_info->user_email;
				$first_name=$user_info->user_firstname;
				$last_name=$user_info->user_lastname;

				$blog_title = get_bloginfo('name');			
				$siteurl=get_bloginfo('wpurl');  
				$siteurlhtml="<a href='".esc_url_raw($siteurl)."' target='_blank' >".esc_url_raw($siteurl)."</a>";
								
				$msgBody = stripslashes(nl2br($get_post_status_result['publish_new_posts_message_body'])); 				
				$msgBody = str_replace('%post_title%',$post_title,$msgBody); 
				$msgBody = str_replace('%post_url%',$postperma,$msgBody); 
				$msgBody = str_replace('%post_date%',$postdate,$msgBody); 
				
				$msgBody = stripslashes(htmlspecialchars_decode($msgBody));
				
				/* ---- Fetch Opted-in subscribers & send notification (start) ---- */
				
				$urldata     = esc_url_raw(WETEXTDOMAIN.'/api/wordpress/get_subscribers.php?token='.$get_post_status_result['wetext_api_key']);			
				$subscriber_info = wp_remote_post( $urldata, array(	'method' => 'GET') );						
				$get_subscriber_data = wp_remote_retrieve_body( $subscriber_info );						
				$fetch_users = json_decode($get_subscriber_data,true);	
								
				if(!empty($fetch_users)){					
					
					$recipients_phone = '';			
					
					foreach($fetch_users as $key_subscriber){												
						$recipients_phone .= $key_subscriber['Cell'].',';						
					}
					
					$recipients_phone = trim($recipients_phone,',');
					$sms_message      = urlencode($msgBody);
					
					$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/send.php?recipients=".$recipients_phone."&message=".$sms_message."&token=".$get_post_status_result['wetext_api_key']);														
					
					$send_notification_msg = wp_remote_post( $url, array('method' => 'GET') );						
					wp_remote_retrieve_body( $send_notification_msg );			
				}
				
				/* ----- Fetch Opted-in subscribers & send notification (end) ----- */
				
				  add_post_meta($post_ID, 'wetext_is_notified', 'yes');
				 
			}
		}				
	} 
  }
}
/* // --- Send notification [Post/Page] (end) --- // */


/* // --- Send notification [WooCommerce] (start) --- // */

add_action( 'woocommerce_thankyou', 'wetext_wooCommerce_tracking' );
function wetext_wooCommerce_tracking( $order_id ) {

	// Lets grab the order
	$order = wc_get_order( $order_id );	
	$order_data = $order->get_data();
	$order_status = $order->get_status();
	$order_date = $order_data["date_created"]->date('m-d-Y H:i A');
    $order_id = $order_data["id"];		
	$billing_phone_no = $order_data["billing"]["phone"]!='' ? $order_data["billing"]["phone"] : '';
	$billing_email    = $order_data["billing"]["email"];
	$billing_country  = $order_data["billing"]["country"];
		
	/**
	 * Put your tracking code here
	 * You can get the order total etc e.g. $order->get_total();
	 */
	 
	// This is the order total
	$order->get_total();
 
	// This is how to grab line items from the order 
	$line_items = $order->get_items();
	
	$sku  = array();
	$name = array();

	// This loops over line items
	foreach ( $line_items as $item ) {
  		// This will be a product
  		$product = $order->get_product_from_item( $item );  		
		array_push($sku,$product->get_sku());		
		// This is the qty purchased
		$qty = $item['qty'];		
		// This is the name of the product purchased
		array_push($name,$item['name']);		
		// Line item total cost including taxes and rounded
		$total = $order->get_line_total( $item, true, true );		
		// Line item subtotal (before discounts)
		$subtotal = $order->get_line_subtotal( $item, true, true );		
	}
	
	$product_name = implode(",",$name);
	$product_sku  = implode(",",$sku);
		
	global $wpdb, $table_prefix;		
		
		$get_user_info = $wpdb->get_row( "SELECT option_value as wetext_info FROM `".$table_prefix."options` WHERE option_name='wetext_user_info'" );
		$get_configuration_info = $wpdb->get_row( "SELECT option_value as wetext_configuration FROM `".$table_prefix."options` WHERE option_name='wetext_configuration_info'" );
		
		$get_post_status_data	= unserialize($get_user_info->wetext_info);	
		$get_configuration 		= unserialize($get_configuration_info->wetext_configuration);		
		$get_woocommerce_status_result = array_merge($get_post_status_data,$get_configuration);
				
		if(!empty($get_woocommerce_status_result) && $get_woocommerce_status_result['woocommerce_new_order_status']=='y'){
			
			// Countrycode 
			
			$country_iso_code_array = array('93'=>'AF','355'=>'AL','213'=>'DZ',''=>'AS','33'=>'AD',''=>'AI',''=>'AG','54'=>'AR','374'=>'AM','297'=>'AW','61'=>'AU','43'=>'AT','994'=>'AZ',''=>'BS','973'=>'BH','880'=>'BD','1246'=>'BB','375'=>'BY','32'=>'BE','501'=>'BZ','229'=>'BJ',''=>'BM','975'=>'BT','591'=>'BO','599'=>'BES','387'=>'BA','267'=>'BW','55'=>'BR','673'=>'BRN','359'=>'BF','226'=>'BF','95'=>'MMR','257'=>'BI','855'=>'KH','237'=>'CM',''=>'CA',''=>'KY','235'=>'TD','56'=>'CL','86'=>'CN','57'=>'CO','269'=>'KM','682'=>'CK','506'=>'CR','385'=>'HR','53'=>'CU','599'=>'CUW','357'=>'CY','420'=>'CZ','45'=>'DK','253'=>'DJ',''=>'DM',''=>'DO','593'=>'EC','20'=>'EG','503'=>'SV','240'=>'GQ','291'=>'ER','372'=>'EE','251'=>'ETH','500'=>'FLK','298'=>'FO','679'=>'FJ','358'=>'FI','33'=>'FR','594'=>'GF','689'=>'PF','241'=>'GA','220'=>'GM','995'=>'GE','49'=>'DE','233'=>'GH','350'=>'GI','44'=>'GB','30'=>'GR','299'=>'GL','599'=>'GD','590'=>'GP',''=>'GU','502'=>'GT','224'=>'GIN','245'=>'GW','592'=>'GY','509'=>'HT','504'=>'HN','852'=>'HK','36'=>'HU','354'=>'IS','91'=>'IN','62'=>'ID','98'=>'IRN','964'=>'IQ','353'=>'IE','972'=>'IL','39'=>'IT','225'=>'CI',''=>'JM','81'=>'JP','962'=>'JO','855'=>'KHM','7'=>'KZ','254'=>'KE','855'=>'KHM','686'=>'KI','82'=>'KOR','965'=>'KW','996'=>'KG','856'=>'LA','371'=>'LV','961'=>'LB','266'=>'LS','231'=>'LR','41'=>'LI','370'=>'LT','352'=>'LU','853'=>'MO','389'=>'MKD','261'=>'MG','265'=>'MW','60'=>'MY','960'=>'MV','223'=>'ML','356'=>'MT','692'=>'MH','596'=>'MQ','222'=>'MR','230'=>'MU','52'=>'MX','691'=>'FM','33'=>'MC','976'=>'MN','382'=>'MNE',''=>'MS','212'=>'MA','258'=>'MZ','95'=>'MM','264'=>'NA','674'=>'NRU','977'=>'NP','31'=>'NL','599'=>'AN','869'=>'KN','687'=>'NC','64'=>'NZ','505'=>'NI','227'=>'NE','234'=>'NG','683'=>'NU','850'=>'PRK','47'=>'NO','968'=>'OM','92'=>'PK','680'=>'PW','507'=>'PA','675'=>'PG','595'=>'PY','51'=>'PE','63'=>'PH','48'=>'PL','351'=>'PT',''=>'PR','974'=>'QA','262'=>'REU','40'=>'RO','7'=>'RUS','250'=>'RW','239'=>'ST','39'=>'SM','966'=>'SA','221'=>'SN','381'=>'SRB','248'=>'SC','232'=>'SL','65'=>'SG','421'=>'SVK','386'=>'SI','677'=>'SB','252'=>'SO','27'=>'ZA','82'=>'KOR','34'=>'ES','94'=>'LK','249'=>'SD','597'=>'SR','268'=>'SZ','46'=>'SE','41'=>'CH','963'=>'SYR','886'=>'TW','7'=>'TJ','255'=>'TZA','66'=>'TH','228'=>'TG','676'=>'TO',''=>'TT','216'=>'TN','90'=>'TR','993'=>'TM',''=>'TC','688'=>'TV','256'=>'UG','380'=>'UA','971'=>'AE','44'=>'GB','598'=>'UY','7'=>'UZ','678'=>'VU','58'=>'VE','84'=>'VNM',''=>'VG',''=>'VIR','685'=>'WSM','967'=>'YE','381'=>'YU','243'=>'ZR','260'=>'ZM','263'=>'ZW');
			
			$countrycode = array_search(strtoupper($billing_country),$country_iso_code_array);
			
			if(!empty($billing_phone_no)){
						
				$woo_msgBody = stripslashes(nl2br($get_woocommerce_status_result['woocommerce_new_order_message_body']));			
				$woo_msgBody = str_replace('%order_id%',$order_id,$woo_msgBody); 
				$woo_msgBody = str_replace('%order_status%',$order_status,$woo_msgBody); 
				$woo_msgBody = str_replace('%order_date%',$order_date,$woo_msgBody); 
				$woo_msgBody = str_replace('%product_name%',$product_name,$woo_msgBody); 
				$woo_msgBody = str_replace('%product_sku%',$product_sku,$woo_msgBody); 
				$woo_msgBody = stripslashes($woo_msgBody);			
				$woo_msgBody = stripslashes(htmlspecialchars_decode($woo_msgBody));
				$woo_message = urlencode($woo_msgBody);
								
				$recipients_no = $countrycode.$billing_phone_no;
				
				$url = esc_url_raw(WETEXTDOMAIN."/api/wordpress/send.php?recipients=".$recipients_no."&message=".$woo_message."&token=".$get_woocommerce_status_result['wetext_api_key']);
								
				$send_notification_msg = wp_remote_post( $url, array('method' => 'GET') );						
				wp_remote_retrieve_body( $send_notification_msg );					
			
			}	
		}
}

/* // --- Send notification [WooCommerce] (end) --- // */	
?>