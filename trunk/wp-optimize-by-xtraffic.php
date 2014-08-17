<?php

/*
Plugin Name: WP Optimize By xTraffic
Version: 1.1
Plugin URI: http://blog-xtraffic.pep.vn/wordpress-optimize-by-xtraffic/
Author: xTraffic
Author URI: http://blog-xtraffic.pep.vn/
Description: WP Optimize By xTraffic provides automatically optimize your wordpress site
*/


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION', '1.1' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG', 'wp-optimize-by-xtraffic' );
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
	define( 'WPOPTIMIZEBYXTRAFFIC_CACHE_PATH', realpath(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/cache/').'/' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL', plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG ).'/' ); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL', admin_url('admin-ajax.php')); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST', 'wpxtrdtecv'); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN' ) ) {
	
	$wpoptimizebyxtraffic_upload_dir = wp_upload_dir();
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP', $wpoptimizebyxtraffic_upload_dir['basedir'] . DIRECTORY_SEPARATOR);
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN', WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP . 'pep-vn' . DIRECTORY_SEPARATOR); 
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_PEPVN', $wpoptimizebyxtraffic_upload_dir['baseurl'] . '/pep-vn/');
}


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeLinks.php');



if ( !class_exists('WPOptimizeByxTraffic') ) :


class WPOptimizeByxTraffic extends WPOptimizeByxTraffic_OptimizeLinks 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
	}
	
	
	// Set up everything
	function activation() {
		$this->wpOptimizeByxTraffic_options = $this->get_options();		
		
		
	}
	
	
	


}//class WPOptimizeByxTraffic

endif; //if ( !class_exists('WPOptimizeByxTraffic') )



if ( class_exists('WPOptimizeByxTraffic') ) :
	global $wpOptimizeByxTraffic;
	$wpOptimizeByxTraffic = new WPOptimizeByxTraffic();
	if (isset($wpOptimizeByxTraffic)) {
		register_activation_hook( __FILE__, array(&$wpOptimizeByxTraffic, 'activation') );
		
		add_action( 'wp_ajax_wpoptimizebyxtraffic_preview_processed_image_action', 'wpoptimizebyxtraffic_preview_processed_image_action' );
		
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
		
	}
	
endif;



function wpOptimizeByxTraffic_load_custom_wp_admin_styles() {        
	wp_register_style( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/admin_styles.min.css' ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.mt_rand(), 'all');
	wp_enqueue_style( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
}



function wpOptimizeByxTraffic_load_custom_wp_admin_scripts() {
	wp_register_script( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/admin_scripts.min.js' ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.mt_rand(), true);
	wp_enqueue_script( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
}

add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_styles' );
add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_scripts' ); 



?>