<?php

/*
Plugin Name: WP Optimize By xTraffic
Version: 1.0
Plugin URI: http://blog-xtraffic.pep.vn/wordpress-optimize-by-xtraffic/
Author: xTraffic
Author URI: http://blog-xtraffic.pep.vn/
Description: WP Optimize By xTraffic provides automatically optimize links (internal & external links), optimize images.
*/


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION', '1.0' );
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
	}
	
endif;

wp_register_style( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/css/styles.css' ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, 'all');
wp_enqueue_style( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );

wp_register_script( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/js/scripts.js' ), array(), WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, true);
wp_enqueue_script( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );



?>