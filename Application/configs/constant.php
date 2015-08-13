<?php 
//$_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : NULL;
//$_SERVER['HTTP_HOST'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL;


defined('WP_PEPVN_CHMOD') || define('WP_PEPVN_CHMOD', 0755);

if ( ! defined( 'WP_PEPVN_SITE_ID' ) ) {
	if(isset($_SERVER['SERVER_NAME'])) {
		define( 'WP_PEPVN_SITE_DOMAIN',$_SERVER['SERVER_NAME']);
		define( 'WP_PEPVN_SITE_ID', md5(WP_PEPVN_NS_SHORT . $_SERVER['SERVER_NAME']) ); 
	} else if(isset($_SERVER['HTTP_HOST'])) {
		define( 'WP_PEPVN_SITE_DOMAIN',$_SERVER['HTTP_HOST']);
		define( 'WP_PEPVN_SITE_ID', md5(WP_PEPVN_NS_SHORT . $_SERVER['HTTP_HOST']) ); 
	}
}

/*
* SALT use for encode data but not sustainably, so don't use for make ID in database (use WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_SITE_CONSTANT_ID instead)
*/

if ( ! defined( 'WP_PEPVN_SITE_SALT' ) ) { 
	
    $tmp = array();
    
    $tmp[] = WP_PEPVN_NS_SHORT;
    $tmp[] = defined('WP_PEPVN_SITE_ID') ? WP_PEPVN_SITE_ID : 0;
    $tmp[] = defined('AUTH_KEY') ? AUTH_KEY : 0;
    $tmp[] = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 0;
    $tmp[] = defined('LOGGED_IN_KEY') ? LOGGED_IN_KEY : 0;
    $tmp[] = defined('NONCE_KEY') ? NONCE_KEY : 0;
    $tmp[] = defined('AUTH_SALT') ? AUTH_SALT : 0;
    $tmp[] = defined('SECURE_AUTH_SALT') ? SECURE_AUTH_SALT : 0;
    $tmp[] = defined('LOGGED_IN_SALT') ? LOGGED_IN_SALT : 0;
    $tmp[] = defined('NONCE_SALT') ? NONCE_SALT : 0;
    $tmp[] = defined('DB_NAME') ? DB_NAME : 0;
    $tmp[] = defined('DB_USER') ? DB_USER : 0;
    $tmp[] = defined('DB_HOST') ? DB_HOST : 0;
    $tmp[] = defined('ABSPATH') ? ABSPATH : 0;
    
    $tmp = md5(implode('',$tmp));
    
    define( 'WP_PEPVN_SITE_SALT', $tmp); unset($tmp);
}

defined('WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_DIR') || define('WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_DIR', WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_APPLICATION_DIR . 'includes' . DIRECTORY_SEPARATOR . 'storages' . DIRECTORY_SEPARATOR);
defined('WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_CACHE_DIR') || define('WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_CACHE_DIR', WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_DIR . 'cache' . DIRECTORY_SEPARATOR );

//@WP_UPLOADS_PEPVN_DIR : Store images processed by this plugin
$tmp = wp_upload_dir();
defined('WP_UPLOADS_PEPVN_DIR') || define('WP_UPLOADS_PEPVN_DIR', $tmp['basedir'] . DIRECTORY_SEPARATOR . 'pep-vn' . DIRECTORY_SEPARATOR);
defined('WP_UPLOADS_PEPVN_URL') || define('WP_UPLOADS_PEPVN_URL', $tmp['baseurl'] . '/pep-vn/');

//@WP_UPLOADS_PEPVN_DIR : Store cache request uri, static files.
defined('WP_CONTENT_PEPVN_DIR') || define('WP_CONTENT_PEPVN_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'pep-vn' . DIRECTORY_SEPARATOR);
defined('WP_CONTENT_PEPVN_URL') || define('WP_CONTENT_PEPVN_URL', WP_CONTENT_URL . '/pep-vn/');

defined('WP_PEPVN_CACHE_TIMEOUT_NORMAL') || define('WP_PEPVN_CACHE_TIMEOUT_NORMAL', 86400);
defined('WP_PEPVN_CACHE_PREFIX') || define('WP_PEPVN_CACHE_PREFIX', md5(WP_PEPVN_SITE_SALT));
defined('WP_PEPVN_CACHE_TRIGGER_CLEAR_KEY') || define('WP_PEPVN_CACHE_TRIGGER_CLEAR_KEY', md5(WP_PEPVN_SITE_SALT . 'CACHE_TRIGGER_CLEAR'));
defined('WP_PEPVN_KEY_DATA_REQUEST') || define('WP_PEPVN_KEY_DATA_REQUEST', 'wppepvndtecv');