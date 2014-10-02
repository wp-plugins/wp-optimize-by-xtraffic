<?php
/*
Plugin Name: WP Optimize By xTraffic
Version: 4.0.0
Plugin URI: http://blog-xtraffic.pep.vn/wordpress-optimize-by-xtraffic/
Author: xTraffic
Author URI: http://blog-xtraffic.pep.vn/
Description: WP Optimize By xTraffic provides automatically optimize your wordpress site
*/


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION', '4.0.0' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG', 'wp-optimize-by-xtraffic' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME', 'WP Optimize By xTraffic' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS', 'WPOptimizeByxTraffic' );
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_FILE' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_FILE', __FILE__ );
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CHMOD' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CHMOD', 0777 ); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PATH' ) ) { 
	define( 'WPOPTIMIZEBYXTRAFFIC_PATH', plugin_dir_path( WPOPTIMIZEBYXTRAFFIC_FILE ) );
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CACHE_PATH' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CACHE_PATH', WPOPTIMIZEBYXTRAFFIC_PATH.'inc/cache/' ); 
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH', WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'files/');
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL', plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG ).'/' ); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL', admin_url('admin-ajax.php')); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST', 'pepvndtecv'); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN' ) ) {
	
	$wpoptimizebyxtraffic_upload_dir = wp_upload_dir();
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP', $wpoptimizebyxtraffic_upload_dir['basedir'] . DIRECTORY_SEPARATOR);
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_WP', $wpoptimizebyxtraffic_upload_dir['baseurl'] . '/');
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN', WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP . 'pep-vn' . DIRECTORY_SEPARATOR); 
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_PEPVN', $wpoptimizebyxtraffic_upload_dir['baseurl'] . '/pep-vn/');
}




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH' ) ) {
	
	$wpoptimizebyxtraffic_content_url = content_url() . '/';
	
	$wpoptimizebyxtraffic_content_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR;
	
	
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH', $wpoptimizebyxtraffic_content_dir);
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_URL', $wpoptimizebyxtraffic_content_url);
	
	
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN', $wpoptimizebyxtraffic_content_dir. 'cache' . DIRECTORY_SEPARATOR . 'pep-vn' . DIRECTORY_SEPARATOR);
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_URL_CACHE_PEPVN', $wpoptimizebyxtraffic_content_url . 'cache/pep-vn/');
	
}




require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeTraffic.php');



if ( !class_exists('WPOptimizeByxTraffic') ) :


class WPOptimizeByxTraffic extends WPOptimizeByxTraffic_OptimizeTraffic 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
	}
	
	
	// Set up everything
	function activation() 
	{
		$this->wpOptimizeByxTraffic_options = $this->get_options();		
		
		
	}
	
	
	


}//class WPOptimizeByxTraffic

endif; //if ( !class_exists('WPOptimizeByxTraffic') )



if ( class_exists('WPOptimizeByxTraffic') ) :
	global $wpOptimizeByxTraffic;
	$wpOptimizeByxTraffic = new WPOptimizeByxTraffic();
	if (isset($wpOptimizeByxTraffic)) {
		register_activation_hook( __FILE__, array(&$wpOptimizeByxTraffic, 'activation') );
		
		
		
		function wpoptimizebyxtraffic_preview_processed_image_action() 
		{
			
			if ( !wp_verify_nonce( $_REQUEST['nonce'], WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG)) {
				echo 'error';exit();
			}
			
			global $wpdb; // this is how you get access to the database
			global $wpOptimizeByxTraffic;
			
			$wpOptimizeByxTraffic->optimize_images_preview_processed_image();
			
			exit(); die(); // this is required to return a proper result
			
		}
		
		
		
		
		
		
		
		function wpoptimizebyxtraffic_base_process_ajax_action() 
		{
			/*
			if ( !wp_verify_nonce( $_REQUEST['nonce'], WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG)) {
				//echo 'error';exit();
			}
			*/
			
			
			
			global $wpdb; // this is how you get access to the database
			global $wpOptimizeByxTraffic;
			
			$wpOptimizeByxTraffic->base_process_ajax();
			
			exit(); die(); // this is required to return a proper result
			
		}
		
		add_action( 'wp_ajax_wpoptimizebyxtraffic_base_process_ajax_action', 'wpoptimizebyxtraffic_base_process_ajax_action' ); 
		
		add_action( 'wp_ajax_wpoptimizebyxtraffic_preview_processed_image_action', 'wpoptimizebyxtraffic_preview_processed_image_action' );
		
		
	}
	
endif;




$wpOptimizeByxTraffic_wp_register_style_status = false;
function wpOptimizeByxTraffic_wp_register_style() 
{
	global $wpOptimizeByxTraffic_wp_register_style_status;
	if(!$wpOptimizeByxTraffic_wp_register_style_status) {
		$wpOptimizeByxTraffic_wp_register_style_status = true;
		
		
		
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/pepvn_libs.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/pepvn_libs.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
		wp_register_style( $handleRegister, plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
		
		
			
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/admin_styles.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/admin_styles.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'admin_styles';
		wp_register_style( $handleRegister, plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
		
		
		
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/optimize_traffic.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/optimize_traffic.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
		wp_register_style( $handleRegister, plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
		
	}
}



$wpOptimizeByxTraffic_wp_register_script_status = false;
function wpOptimizeByxTraffic_wp_register_script() 
{
	global $wpOptimizeByxTraffic_wp_register_script_status;
	
	if(!$wpOptimizeByxTraffic_wp_register_script_status) {
		$wpOptimizeByxTraffic_wp_register_script_status = true;
		
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/pepvn_libs.min.js?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/pepvn_libs.js?__ts='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
		wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
		
		
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/admin_scripts.min.js?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/admin_scripts.js?__ts='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'admin_scripts';
		wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
		
		
		$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/optimize_traffic.min.js?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/optimize_traffic.js?__ts='.time().mt_rand();//test
		$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
		wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
		
	}
	
	
}






function wpOptimizeByxTraffic_load_custom_wp_admin_styles() 
{
	wpOptimizeByxTraffic_wp_register_style();
	
	wp_enqueue_style( 'wp-pointer' );
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
	wp_enqueue_style( $handleRegister );
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'admin_styles';
	wp_enqueue_style( $handleRegister );
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
	wp_enqueue_style( $handleRegister );
	
}



function wpOptimizeByxTraffic_load_custom_wp_admin_scripts() 
{
	wpOptimizeByxTraffic_wp_register_script();
	
	
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'wp-pointer' );
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
	wp_enqueue_script( $handleRegister ); 
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
	wp_enqueue_script( $handleRegister ); 
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'admin_scripts';
	wp_enqueue_script( $handleRegister ); 
	
	
	
	
	
}



function wpOptimizeByxTraffic_load_custom_wp_styles() 
{
	wpOptimizeByxTraffic_wp_register_style();
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
	wp_enqueue_style( $handleRegister ); 
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
	wp_enqueue_style( $handleRegister ); 
	
	//wp_enqueue_style( 'core', 'style.css', false ); 
}

function wpOptimizeByxTraffic_load_custom_wp_scripts() 
{
	wpOptimizeByxTraffic_wp_register_script();
	
	
	wp_enqueue_script( 'jquery' );
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'pepvn_libs';
	wp_enqueue_script( $handleRegister ); 
	
	$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'optimize_traffic';
	wp_enqueue_script( $handleRegister ); 
	
	
	//wp_enqueue_script( 'my-js', 'filename.js', false );
}

add_action( 'wp_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_styles' );
add_action( 'wp_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_scripts' );

add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_styles' );
add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_scripts' ); 


// Minify HTML codes when page is output.
function wpOptimizeByxTraffic_start_load_html_pages() {
	/** 
	* use wpOptimizeByxTraffic_start_load_html_pages($html) function to minify html codes.
	*/
	if(function_exists('ob_start')) {
		ob_start('wpOptimizeByxTraffic_process_html_pages');
	}
}

function wpOptimizeByxTraffic_process_html_pages($input_html)
{
	/** 
	* some minify codes here ...
	*/

	global $wpOptimizeByxTraffic;
	
	return $wpOptimizeByxTraffic->optimize_speed_process_html_pages($input_html);
	
}

if ( is_admin() ) {
	
} else {
	add_action('wp_loaded','wpOptimizeByxTraffic_start_load_html_pages',0.0000000001); 
}







//Action when wordpress init
function wpOptimizeByxTraffic_init()
{
	// Localization
	load_plugin_textdomain( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

// Add actions
add_action('init', 'wpOptimizeByxTraffic_init');

?>