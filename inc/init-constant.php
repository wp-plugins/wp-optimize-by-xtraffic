<?php 




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION', '5.0.0' ); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {	//basic 
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG', 'wp-optimize-by-xtraffic' );
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PRIORITY_BASE_FIRST' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PRIORITY_BASE_FIRST', 1.1);
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PRIORITY_BASE_LAST' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PRIORITY_BASE_LAST', 99999999);
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY', hash('crc32b',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG . '-options-cache-key')) ;
}

if ( ! defined( 'WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY' ) ) {
	define( 'WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY', hash('crc32b',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG . '-wp-optimize-speed-options-cache-key')) ;
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PATH' ) ) { 
	define( 'WPOPTIMIZEBYXTRAFFIC_PATH', WP_CONTENT_DIR . '/plugins/'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/' );
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME', 'WP Optimize By xTraffic' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS', 'WPOptimizeByxTraffic' );
}


if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT', 'wotxtf' );
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID' ) ) {
	if(isset($_SERVER['SERVER_NAME'])) {
		define( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID', md5( WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT . $_SERVER['SERVER_NAME']) ); 
	} else if(isset($_SERVER['HTTP_HOST'])) {
		define( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID', md5(WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT . $_SERVER['HTTP_HOST']) ); 
	}
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT' ) ) { 
	
	if(isset($_SESSION['wpoptxtr']['site_constant_salt']) && $_SESSION['wpoptxtr']['site_constant_salt']) {
		$wpoptimizebyxtraffic_valueTemp1 = $_SESSION['wpoptxtr']['site_constant_salt'];
		$wpoptimizebyxtraffic_valueTemp1 = (string)$wpoptimizebyxtraffic_valueTemp1;
		define( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT', $wpoptimizebyxtraffic_valueTemp1 );  
	} else {
		
		$wpoptimizebyxtraffic_valueTemp1 = '';
		
		$wpoptimizebyxtraffic_valueTemp1 .= WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG;
		$wpoptimizebyxtraffic_valueTemp1 .= __FILE__;
		
		if ( defined( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID;
		}
		
		if ( defined( 'AUTH_KEY' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= AUTH_KEY;
		}
		if ( defined( 'SECURE_AUTH_KEY' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= SECURE_AUTH_KEY;
		}
		if ( defined( 'LOGGED_IN_KEY' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= LOGGED_IN_KEY;
		}
		if ( defined( 'NONCE_KEY' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= NONCE_KEY;
		}
		if ( defined( 'AUTH_SALT' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= AUTH_SALT;
		}
		if ( defined( 'SECURE_AUTH_SALT' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= SECURE_AUTH_SALT;
		}
		if ( defined( 'LOGGED_IN_SALT' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= LOGGED_IN_SALT;
		}
		if ( defined( 'NONCE_SALT' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= NONCE_SALT;
		}
		if ( defined( 'DB_NAME' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= DB_NAME;
		}
		if ( defined( 'DB_USER' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= DB_USER;
		}
		if ( defined( 'DB_HOST' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= DB_HOST;
		}
		
		if ( defined( 'ABSPATH' ) ) {
			$wpoptimizebyxtraffic_valueTemp1 .= ABSPATH;
		}
		
		$wpoptimizebyxtraffic_valueTemp1 = md5($wpoptimizebyxtraffic_valueTemp1);
		$_SESSION['wpoptxtr']['site_constant_salt'] = $wpoptimizebyxtraffic_valueTemp1;
		
		define( 'WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT', $wpoptimizebyxtraffic_valueTemp1 ); 
	}	
	
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CHMOD' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CHMOD', 0755 ); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CACHE_PATH' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CACHE_PATH', WPOPTIMIZEBYXTRAFFIC_PATH.'inc/cache/' ); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_LOG_FOLDER_PATH' ) ) { 
	define( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_LOG_FOLDER_PATH', WPOPTIMIZEBYXTRAFFIC_PATH.'inc/log/' ); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH', WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'files/'); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST', 'pepvndtecv'); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP', WP_CONTENT_DIR . '/uploads/');
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN', WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP . 'pep-vn' . DIRECTORY_SEPARATOR); 
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG' ) ) {
	
	define( 'WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG', WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG . '-optimize-cache');
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS', hash('crc32b',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG . '-key-static-var-base-cronjobs'));
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH' ) ) {
	define( 'WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH', WP_CONTENT_DIR . '/cache/' . WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG . '/');
}

if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN' ) ) {
	
	define( 'WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'pep-vn' . DIRECTORY_SEPARATOR);
	
}

function wpOptimizeByxTraffic_autoloader($class) 
{
	if ( !class_exists($class) ) {
		$filePath = WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/'.$class.'.php';
		if(file_exists($filePath) && is_file($filePath)) {
			include_once($filePath);
		}
	}
}
spl_autoload_register('wpOptimizeByxTraffic_autoloader');

PepVN_Data::session_start(); 
