<?php
/*
Plugin Name: WP Optimize By xTraffic
Version: 4.1.11
Plugin URI: http://blog-xtraffic.pep.vn/wordpress-optimize-by-xtraffic/
Author: xTraffic
Author URI: http://blog-xtraffic.pep.vn/
Description: WP Optimize By xTraffic provides automatically optimize your WordPress site
*/

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_INIT' ) ) : 

define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_INIT', 1 );

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION', '4.1.11' );
}


define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_TIMESTART', microtime(true));


global $wpOptimizeByxTraffic;

if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
	
} else {
	$wpOptimizeByxTraffic = false;
}




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_FILE' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_FILE', __FILE__ );
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PATH' ) ) { 
	define( 'WPOPTIMIZEBYXTRAFFIC_PATH', plugin_dir_path( WPOPTIMIZEBYXTRAFFIC_FILE ) );
}




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {
	include_once(WPOPTIMIZEBYXTRAFFIC_PATH . 'inc/wp-optimize-by-xtraffic-init-constant.php');
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL', plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG ).'/' ); 
}



if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL', admin_url('admin-ajax.php')); 
}



$wpoptimizebyxtraffic_upload_dir = wp_upload_dir();

$wpoptimizebyxtraffic_content_url = content_url() . '/';
	
$wpoptimizebyxtraffic_content_dir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR;


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_WP' ) ) {
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_WP', $wpoptimizebyxtraffic_upload_dir['baseurl'] . '/');
	
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_PEPVN', $wpoptimizebyxtraffic_upload_dir['baseurl'] . '/pep-vn/');
}




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH' ) ) {
	
	
	
	
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH', $wpoptimizebyxtraffic_content_dir);
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_URL', $wpoptimizebyxtraffic_content_url);
	
	
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN', $wpoptimizebyxtraffic_content_dir. 'cache' . DIRECTORY_SEPARATOR . 'pep-vn' . DIRECTORY_SEPARATOR);
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_URL_CACHE_PEPVN', $wpoptimizebyxtraffic_content_url . 'cache/pep-vn/');
	
}





if ( !class_exists('WPOptimizeByxTraffic_AdvancedCache') ) {
	require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/wp-optimize-by-xtraffic-advanced-cache.php');
}


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeTraffic.php');



if ( !class_exists('WPOptimizeByxTraffic') ) :


class WPOptimizeByxTraffic extends WPOptimizeByxTraffic_OptimizeTraffic 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
	}
	
	
	// Set up everything when plugin active
	public function activation()
	{
		$this->wpOptimizeByxTraffic_options = $this->get_options();	
		
		$this->base_activate();
		
	}
	
	
	
	//when plugin deactivate
	public function deactivate() 
	{
		$this->base_deactivate();
		
	}


}//class WPOptimizeByxTraffic



endif; //if ( !class_exists('WPOptimizeByxTraffic') )



if ( class_exists('WPOptimizeByxTraffic') ) :
	
	global $wpOptimizeByxTraffic;
	$wpOptimizeByxTraffic = new WPOptimizeByxTraffic();
	
	if (isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) : 
		
		$wpoptimizebyxtraffic_GETActionClearAllCacheKey = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin-bar-menu-clear-all-caches-status';
		
		register_activation_hook( __FILE__, array(&$wpOptimizeByxTraffic, 'activation') );
		register_deactivation_hook( __FILE__, array(&$wpOptimizeByxTraffic, 'deactivate') );
		
		
		
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
		
		
		
		
		
		//add_action( 'wp_ajax_wpoptimizebyxtraffic_base_process_ajax_action', 'wpoptimizebyxtraffic_base_process_ajax_action' ); 
		
		add_action( 'wp_ajax_wpoptimizebyxtraffic_preview_processed_image_action', 'wpoptimizebyxtraffic_preview_processed_image_action' );
		
		
		
		$wpOptimizeByxTraffic_wp_register_style_status = false;
		function wpOptimizeByxTraffic_wp_register_style() 
		{
			global $wpOptimizeByxTraffic_wp_register_style_status;
			if(!$wpOptimizeByxTraffic_wp_register_style_status) {
				$wpOptimizeByxTraffic_wp_register_style_status = true; 
				
				
				
				$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/pepvn_libs.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
				//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/pepvn_libs.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
				wp_register_style( $handleRegister, plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
				
				
					
				$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/admin_styles.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
				//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/admin_styles.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin_styles';
				wp_register_style( $handleRegister, plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
				
				
				
				$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/wp-optimize-by-xtraffic-fe.min.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
				//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/wp-optimize-by-xtraffic-fe.css?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&__t='.time().mt_rand();//test
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
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
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
				wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
				
				
				$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/admin_scripts.min.js?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
				//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/admin_scripts.js?__ts='.time().mt_rand();//test
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin_scripts';
				wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
				
				
				$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/wp-optimize-by-xtraffic-fe.min.js?v='.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
				//$urlFileTemp = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/wp-optimize-by-xtraffic-fe.js?__ts='.time().mt_rand();//test
				$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
				wp_register_script($handleRegister , plugins_url( $urlFileTemp ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true); 
				
			}
			
			
		}






		function wpOptimizeByxTraffic_load_custom_wp_admin_styles() 
		{
			wpOptimizeByxTraffic_wp_register_style();
			
			wp_enqueue_style( 'wp-pointer' );
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
			wp_enqueue_style( $handleRegister );
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin_styles';
			wp_enqueue_style( $handleRegister );
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
			wp_enqueue_style( $handleRegister );
			
		}



		function wpOptimizeByxTraffic_load_custom_wp_admin_scripts() 
		{
			wpOptimizeByxTraffic_wp_register_script();
			
			
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-pointer' );
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
			wp_enqueue_script( $handleRegister ); 
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
			wp_enqueue_script( $handleRegister ); 
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin_scripts';
			wp_enqueue_script( $handleRegister ); 
			
			
			
			
			
		}



		function wpOptimizeByxTraffic_load_custom_wp_styles() 
		{
			wpOptimizeByxTraffic_wp_register_style();
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
			wp_enqueue_style( $handleRegister ); 
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
			wp_enqueue_style( $handleRegister ); 
			
		}

		function wpOptimizeByxTraffic_load_custom_wp_scripts() 
		{
			wpOptimizeByxTraffic_wp_register_script();
			
			
			wp_enqueue_script( 'jquery' );
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-pepvn_libs';
			wp_enqueue_script( $handleRegister ); 
			
			$handleRegister = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-wp-optimize-by-xtraffic-fe';
			wp_enqueue_script( $handleRegister ); 
			
		}

		add_action( 'wp_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_styles' );
		add_action( 'wp_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_scripts' );

		add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_styles' );
		add_action( 'admin_enqueue_scripts', 'wpOptimizeByxTraffic_load_custom_wp_admin_scripts' ); 





		// Minify HTML codes when page is output.
		function wpOptimizeByxTraffic_start_load_html_pages() 
		{
			/** 
			* use wpOptimizeByxTraffic_start_load_html_pages($html) function to minify html codes.
			*/
			
			if(function_exists('ob_start')) {
				ob_start('wpOptimizeByxTraffic_process_html_pages'); 
			}
			
		}
		
		

		function wpOptimizeByxTraffic_process_html_pages($buffer)
		{
			/** 
			* some minify codes here ...
			*/
			
			global $wpOptimizeByxTraffic;
			
			$buffer = $wpOptimizeByxTraffic->optimize_speed_process_html_pages($buffer);
			
			
			
			$options = $wpOptimizeByxTraffic->get_options(array(
				'cache_status' => 1
			));
			
			
			if($options['optimize_images_images_lazy_load_enable']) { 
				if($options['optimize_images_images_lazy_load_frontpage_enable']) {
					$buffer = $wpOptimizeByxTraffic->optimize_images_process_allimagestags_lazyload($buffer,1); 
				}
			}
			
			
			$buffer = $wpOptimizeByxTraffic->optimize_speed_cdn_process_text($buffer,'html');
			
			$buffer = $wpOptimizeByxTraffic->base_add_plugin_info_html($buffer);
			
			$wpOptimizeByxTraffic->optimize_speed_optimize_cache_check_and_create_page_cache(array(
				'content' => $buffer
			));
			
			$wpOptimizeByxTraffic->optimize_speed_optimize_cache_check_and_flush_http_browser_cache();
			
			return $buffer;
			
		}
		
		function wpOptimizeByxTraffic_clear_cache()
		{
			global $wpOptimizeByxTraffic;
			
			$wpOptimizeByxTraffic->base_clear_data('');
			
		}

		
		function wpOptimizeByxTraffic_admin_bar_menu()
		{
			global $wpOptimizeByxTraffic;
			if($wpOptimizeByxTraffic->base_is_admin() && $wpOptimizeByxTraffic->base_is_current_user_logged_in_can('activate_plugins')) {
				global $wp_admin_bar;
				
				$parentAdminBarIdClass = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-admin-bar-menu';
				
				$wp_admin_bar->add_menu( array(
					'id' => $parentAdminBarIdClass,
					'title' => '<span class="ab-icon"><img src="'.plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/images/icon.png').'" /></span><span class="ab-label">WP Optimize</span>',
					'href' => FALSE,
					'meta' => array(
						'title' => 'WP Optimize',
						'class' => $parentAdminBarIdClass
					)
				) );
				
				
				$menuKey = $parentAdminBarIdClass.'-clear-all-caches'; 
				$wp_admin_bar->add_menu( array(
					'id' => $menuKey,
					'parent' => $parentAdminBarIdClass,
					'title' => __('Clear All Caches',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),
					'href' => $wpOptimizeByxTraffic->base_add_parameters_to_url('',array(
						$menuKey.'-status' => time()
					))
				) );
				
			}
		}




		
		
		
		function wpOptimizeByxTraffic_post_thumbnail_custom( $html, $post_id, $post_thumbnail_id, $size, $attr ) 
		{
			global $wpOptimizeByxTraffic;
			
			if(!$html) {
				$html = '';
			}
			$html = (string)$html;
			
			if(!$post_thumbnail_id) {
				$post_thumbnail_id = 0;
			}
			$post_thumbnail_id = (int)$post_thumbnail_id;
			
			
			if($post_thumbnail_id>0) {
			} else {
				if($post_id) {
					$post_id = (int)$post_id;
					if($post_id>0) {
					
						$post_thumbnail_id = get_post_thumbnail_id($post_id);
						$post_thumbnail_id = (int)$post_thumbnail_id;
					}
				}
			}
			
			if($post_thumbnail_id>0) {
				
				
				$attachment_metadata = wp_get_attachment_metadata($post_thumbnail_id);
				
				$image_src = wp_get_attachment_image_src($post_thumbnail_id, $size);
				
				$imgName = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG;
				
				$imgInfo = pathinfo($image_src[0]);
				if(isset($imgInfo['filename'])) {
					$imgName = $imgInfo['filename'];
				}
				
				
				$rsProcessImage1 = $wpOptimizeByxTraffic->optimize_images_process_image(array(
					'optimized_image_file_name' => $imgName
					,'original_image_src' => $image_src[0]
					,'resize_max_width' => $image_src[1]
					,'resize_max_height' => $image_src[2]
					,'action' => 'do_process_image'
				));
				
				
				if($rsProcessImage1['image_optimized_file_url']) {
					$image_src[0] = $rsProcessImage1['image_optimized_file_url'];
					
				}
				
				
				
				if(strlen($html)>0) {
					$html = preg_replace('#\s*?src=(\'|\")[^\'\"]*?\1#is',' src=$1'.$image_src[0].'$1',$html);
				} else {
					
					$imgClass = 'wp-post-image';
					if(!is_array($size)) {
						$imgClass .= ' attachment-'.(string)$size;
					}
					
					$html = ' <img width="'.$image_src[1].'" height="'.$image_src[2].'" src="'.$image_src[0].'" class="'.$imgClass.'" alt="'.$attachment_metadata['image_meta']['caption'].' - '.$attachment_metadata['image_meta']['title'].'"> ';
					
				}
				
			}
			
			return $html;
		}

		
		
		
		
		
		
		
		//Action when wordpress init : 1
		function wpOptimizeByxTraffic_init_first()
		{
			
			global $wpOptimizeByxTraffic; 
			
			global $wpoptimizebyxtraffic_GETActionClearAllCacheKey;
			
			if(isset($_GET[$wpoptimizebyxtraffic_GETActionClearAllCacheKey]) && ($_GET[$wpoptimizebyxtraffic_GETActionClearAllCacheKey])) {
				if($wpOptimizeByxTraffic->base_is_current_user_logged_in_can('activate_plugins')) {
					wpOptimizeByxTraffic_clear_cache();
				}
			}
			
			
			// Localization
			load_plugin_textdomain( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
			
			add_action('save_post', 'wpOptimizeByxTraffic_clear_cache', 999999999.0000000001);
			
			add_action('admin_bar_menu', 'wpOptimizeByxTraffic_admin_bar_menu', 999999999);
			
			add_filter( 'post_thumbnail_html', 'wpOptimizeByxTraffic_post_thumbnail_custom', 999999999.0000000001, 5 );
			
			
			
		}


		//Action when wordpress init
		function wpOptimizeByxTraffic_init_last()
		{
			
			
		}
		
		
		
		
		
		function wpOptimizeByxTraffic_wp_loaded_first() 
		{
			if(isset($_GET['action']) && $_GET['action']) {
				if('wpoptimizebyxtraffic_base_process_ajax_action' === $_GET['action']) {
					global $wpOptimizeByxTraffic;
					if($wpOptimizeByxTraffic->base_is_admin()) {
						wpoptimizebyxtraffic_base_process_ajax_action();
					}
					
					
				}
				
			}
			
			
			
			
		}


		function wpOptimizeByxTraffic_wp_loaded_last() 
		{
			
		}



		function wpOptimizeByxTraffic_wp_init_first() 
		{
			global $wpOptimizeByxTraffic;
			
			
			
		}


		
		function wpOptimizeByxTraffic_wp_init_last() 
		{
			global $wpOptimizeByxTraffic;
			
			if ( $wpOptimizeByxTraffic->base_is_admin() ) {
			
			} else {
				wpOptimizeByxTraffic_start_load_html_pages();
			}
			
		}

		


		
		function wpOptimizeByxTraffic_wp_shutdown_first() 
		{
			
			
		}
		
		function wpOptimizeByxTraffic_wp_shutdown_last() 
		{
			global $wpOptimizeByxTraffic;
			
			$wpOptimizeByxTraffic->base_do_before_wp_shutdown();
			
		}
		
		
		
		
		

		/*
		* @init 1
		* Runs after WordPress has finished loading but before any headers are sent. Useful for intercepting $_GET or $_POST triggers.
		*/
		add_action('init', 'wpOptimizeByxTraffic_init_first', 0);

		add_action('init', 'wpOptimizeByxTraffic_init_last', 999999999.0000000001);
		
		
		/*
		* @wp_loaded 2
		* After WordPress is fully loaded.
		*/
		add_action('wp_loaded','wpOptimizeByxTraffic_wp_loaded_first', 0.0000000001);
		
		add_action('wp_loaded','wpOptimizeByxTraffic_wp_loaded_last', 999999999.0000000001);
		
		
		
		/*
		* @wp 3
		* This action hook runs immediately after the global WP class object is set up. The $wp object is passed to the hooked function as a reference (no return is necessary).
		* This hook is one effective place to perform any high-level filtering or validation, following queries, but before WordPress does any routing, processing, or handling.
		*/
		add_action( 'wp', 'wpOptimizeByxTraffic_wp_init_first', 0.0000000001 );
		
		add_action( 'wp', 'wpOptimizeByxTraffic_wp_init_last', 999999999.0000000001 );
		
		
		/*
		* @shutdown 4
		* 
		*/
		add_action( 'shutdown', 'wpOptimizeByxTraffic_wp_shutdown_first', 0.0000000001 );
		
		add_action( 'shutdown', 'wpOptimizeByxTraffic_wp_shutdown_last', 999999999.0000000001 );
		
		
		
		
	endif;
	
endif;






endif;

