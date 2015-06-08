<?php




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {
	include(dirname(__FILE__) . '/init-constant.php');
}

if ( !class_exists('WPOptimizeByxTraffic_AdvancedCache') ) :  




class WPOptimizeByxTraffic_AdvancedCache
{
	private $baseCacheData;
	
	public $urlFullRequest;
	
	public $mobileDetect = false;
	
	private $initWpdbCacheStatus = false;
    
    public $patternCookiesNotCache = '';
    
    public $patternRequestUriNotCache = '';
    
	
	function __construct() 
	{
		$this->urlFullRequest = PepVN_Data::$defaultParams['urlFullRequest'];
        
        $this->patternCookiesNotCache = str_replace(';','|',PepVN_Data::preg_quote(preg_replace('#[\;\,]+#is',';',implode(';',PepVN_Data::$defaultParams['wp_cookies_not_cache']))));
        $this->patternRequestUriNotCache = str_replace(';','|',PepVN_Data::preg_quote(preg_replace('#[\;\,]+#is',';',implode(';',PepVN_Data::$defaultParams['wp_request_uri_not_cache']))));
	}
	
	private function _init_mobile_detect()
	{
		if(false === $this->mobileDetect) {
			$this->mobileDetect = new Mobile_Detect;
		}
	}
	
	public function is_mobile()
	{
		$keyCache1 = 'is_mobile';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			
			if ( $this->mobileDetect->isMobile() ) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function is_tablet()
	{
		$keyCache1 = 'is_tablet';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			
			if ( $this->mobileDetect->isTablet() ) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function is_ios()
	{
		$keyCache1 = 'is_ios';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			
			if ( $this->mobileDetect->isiOS() ) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function is_androidos()
	{
		$keyCache1 = 'is_androidos';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			
			if ( $this->mobileDetect->isAndroidOS() ) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	
	public function mobile_detect_version($component)
	{
		$keyCache1 = hash('crc32b', 'mobile_detect_version_'.$component);
		
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			$this->baseCacheData[$keyCache1] = $this->mobileDetect->version($component);
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function mobile_detect_is($component)
	{
		$keyCache1 = hash('crc32b', 'mobile_detect_is_'.$component);
		
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->_init_mobile_detect();
			if($this->mobileDetect->is($component)) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	
	public function get_device_screen_width()
	{
		$keyCache1 = '_gt_dv_sc_wd';
		if(!isset($this->baseCacheData[$keyCache1])) {
			
			$this->baseCacheData[$keyCache1] = 0;	//pixel
			
			$cookieKey = 'xtrdvscwd';
			$screenWidthCookie = false;
			if(isset($_COOKIE[$cookieKey]) && $_COOKIE[$cookieKey]) {
				$screenWidthCookie = $_COOKIE[$cookieKey];
				$screenWidthCookie = (int)$screenWidthCookie;
			}
			
			if(
				$screenWidthCookie
				&& ($screenWidthCookie>0)
			) {
				$this->baseCacheData[$keyCache1] = $screenWidthCookie;
			} else {
				
				$this->_init_mobile_detect();
				
				$httpUserAgent = '';
				
				if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
					$httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
				}
				
				$deviceVersion = false;
				
				$isGooglePageSpeedStatus = false;
				if(false !== stripos($httpUserAgent, 'Google Page Speed')) {
					$isGooglePageSpeedStatus = true;
				}
				
				if ( $this->is_mobile() ) { //mobile or tablet
					if($this->is_tablet()) {
						
						$this->baseCacheData[$keyCache1] = 960;
						
						if($this->mobile_detect_is('Kindle')) {	//Kindle
							$this->baseCacheData[$keyCache1] = 1024;
						} else if($this->mobile_detect_is('iPad')) {	//iPad
							$this->baseCacheData[$keyCache1] = 1024;
							
							$deviceVersion = $this->mobile_detect_version('iPad');
							
							if($deviceVersion) {
								if(0 === stripos($deviceVersion,'4_')) {	//iPad 1/2/mini
									$this->baseCacheData[$keyCache1] = 2048;
								}
							}
						} else if(false !== stripos($httpUserAgent,'Nexus 10')) { //Nexus 10
							$this->baseCacheData[$keyCache1] = 2560;
						} else if(false !== stripos($httpUserAgent,'Nexus 7')) { //Nexus 7
							$this->baseCacheData[$keyCache1] = 1280;
						}
						
					} else {	//is mobile phone
						
						$this->baseCacheData[$keyCache1] = 320;
						
						if($isGooglePageSpeedStatus) {
							$this->baseCacheData[$keyCache1] = 320;
						} else if($this->mobile_detect_is('iPhone')) {
							
							$this->baseCacheData[$keyCache1] = 320;
							
							$deviceVersion = $this->mobile_detect_version('iPhone');
							if($deviceVersion) {
								if(0 === stripos($deviceVersion,'8_')) { //iPhone 6
									$this->baseCacheData[$keyCache1] = 750;
								} else if(0 === stripos($deviceVersion,'4_')) { //iPhone 4
									$this->baseCacheData[$keyCache1] = 640;
								}
							}
						} else if(false !== stripos($httpUserAgent,'BB10; Touch')) { //BlackBerry Z10
							$this->baseCacheData[$keyCache1] = 768; 
						} else if(false !== stripos($httpUserAgent,'Nexus 4')) { //Nexus 4
							$this->baseCacheData[$keyCache1] = 768;
						} else if(false !== stripos($httpUserAgent,'Nexus 5')) { //Nexus 5
							$this->baseCacheData[$keyCache1] = 1080;
						} else if(false !== stripos($httpUserAgent,'Nexus S')) { //Nexus S
							$this->baseCacheData[$keyCache1] = 480;
						} else if(false !== stripos($httpUserAgent,'Nokia')) { //Nokia
							$this->baseCacheData[$keyCache1] = 360;
							if(false !== stripos($httpUserAgent,'Lumia')) { //Nokia Lumia
								$this->baseCacheData[$keyCache1] = 480;
							}
						}
					}
					
				} else {	//desktop
					if($isGooglePageSpeedStatus) {
						$this->baseCacheData[$keyCache1] = 1024;
					}
				}
				
			}
			
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	
	public function optimize_images_check_get_screen_width()
	{
		$keyCache1 = '_op_im_c_g_sc_wd';
		
		if(!isset($this->baseCacheData[$keyCache1])) {
			
			$this->baseCacheData[$keyCache1] = 0;	//pixel
			
			$options = $this->get_options(array());
			
			if(
				isset($options['optimize_images_auto_resize_images_enable'])
				&& $options['optimize_images_auto_resize_images_enable']
			) {
				$this->baseCacheData[$keyCache1] = $this->get_device_screen_width();
				$this->baseCacheData[$keyCache1] = (int)$this->baseCacheData[$keyCache1];
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function checkAndInitWpdbCache()
	{
		
		$checkStatus = false;
		
		if(false === $this->initWpdbCacheStatus) {
			if(!is_admin()) {
				$options = $this->get_options(array());
				if(null !== $options) {
					if(isset($options['optimize_cache_enable']) && ($options['optimize_cache_enable'])) {
						if(isset($options['optimize_cache_database_cache_enable']) && ($options['optimize_cache_database_cache_enable'])) {
							$checkStatus = true;
						}
					}
				}
			}
		}
		
		
		if(true === $checkStatus) {
			global $wpdb;
			if(isset($wpdb) && $wpdb) {
				if(isset($wpdb->pepvn_wpdb_status) && $wpdb->pepvn_wpdb_status) {
					$checkStatus = false;
				}
			} else {
				$checkStatus = false;
			}
			
		}
		
		if(true === $checkStatus) {
			
			$pepvnDirCachePathTemp = PEPVN_CACHE_DATA_DIR.'db'.DIRECTORY_SEPARATOR;
			
			if(!is_dir($pepvnDirCachePathTemp)) {
				PepVN_Data::createFolder($pepvnDirCachePathTemp);
			}
			
			if(is_dir($pepvnDirCachePathTemp) && is_readable($pepvnDirCachePathTemp) && is_writable($pepvnDirCachePathTemp)) {
				
				$pepvnCacheHashKeySaltTemp = __METHOD__ . __FILE__;
				
				if(defined('WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT')) {
					$pepvnCacheHashKeySaltTemp .= '_'.WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT;
				}
				
				$pepvnCacheHashKeySaltTemp = md5($pepvnCacheHashKeySaltTemp);
				
				$optimize_cache_cachetimeout = 0;
				if(isset($options['optimize_cache_cachetimeout']) && $options['optimize_cache_cachetimeout']) {
					$options['optimize_cache_cachetimeout'] = (int)$options['optimize_cache_cachetimeout'];
				}
				if($options['optimize_cache_cachetimeout']>0) {
					$optimize_cache_cachetimeout = $options['optimize_cache_cachetimeout'];
				}
				
				$cacheMethods = array();
				
				if(
					isset($options['optimize_cache_database_cache_methods']['apc'])
					&& ($options['optimize_cache_database_cache_methods']['apc'])
					&& ('apc' === $options['optimize_cache_database_cache_methods']['apc'])
				) {
					if(
						function_exists('apc_exists') 
					) {
						$cacheTimeoutTemp = ceil($optimize_cache_cachetimeout / 3);
						if(
							($cacheTimeoutTemp > 0)
							&& ($cacheTimeoutTemp < 30)
						) {
							$cacheTimeoutTemp = 30;
						}
						$cacheTimeoutTemp = (int)$cacheTimeoutTemp;
						
						$cacheMethods['apc'] = array(
							'cache_timeout' => $cacheTimeoutTemp
						);
					}
				}
				
				if(
					isset($options['optimize_cache_database_cache_methods']['memcache'])
					&& ($options['optimize_cache_database_cache_methods']['memcache'])
					&& ('memcache' === $options['optimize_cache_database_cache_methods']['memcache'])
				) {
					if(
						isset($options['memcache_servers'])
						&& ($options['memcache_servers'])
					) {
						$options['memcache_servers'] = PepVN_Data::cleanArray($options['memcache_servers']);
						if(!empty($options['memcache_servers'])) {
							if(class_exists('Memcache')) {
								$memcacheServers = array();
								
								foreach($options['memcache_servers'] as $server) {
									if($server) {
										$server = explode(':',$server,2);
										$serverTemp = array(
											'host' => $server[0]
										);
										if(isset($server[1])) {
											$serverTemp['port'] = $server[1];
										}
										$memcacheServers[] = $serverTemp;
									}
								}
								
								if(!empty($memcacheServers)) {
									$cacheTimeoutTemp = ceil($optimize_cache_cachetimeout / 2);
									if(
										($cacheTimeoutTemp > 0)
										&& ($cacheTimeoutTemp < 30)
									) {
										$cacheTimeoutTemp = 30;
									}
									$cacheTimeoutTemp = (int)$cacheTimeoutTemp;
									
									$cacheMethods['memcache'] = array(
										'cache_timeout' => $cacheTimeoutTemp
										,'object' => false
										,'servers' => $memcacheServers
									);
								}
							}
						}
					}
					
				}
				
				$cacheMethods['file'] = array(
					'cache_timeout' => $optimize_cache_cachetimeout
					, 'cache_dir' => $pepvnDirCachePathTemp 
				);
				
				PepVN_Data::$cacheDbObject = new PepVN_Cache(array(
					'cache_timeout' => $optimize_cache_cachetimeout		//seconds
					,'hash_key_method' => 'crc32b'		//best is crc32b
					,'hash_key_salt' => md5($pepvnCacheHashKeySaltTemp)
					,'gzcompress_level' => 0
					,'key_prefix' => 'db_'
					,'cache_methods' => $cacheMethods
				));
				
				$wpdb = new pepvn_wpdb_wrapper($wpdb);
				$cacheMethods = 0;
				
				$this->initWpdbCacheStatus = true;
			}
		}
		
	}
	
	public function get_options($input_parameters = false)
	{
		$options = array();
		
		if(defined('WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY')) {
			$valueTemp = PepVN_Data::$cacheObject->get_cache(WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY);
			if(null !== $valueTemp) {
				$options = array_merge($options,(array)$valueTemp);
			}
			$valueTemp = 0;
		}
		
		if(defined('WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY')) {
			$valueTemp = PepVN_Data::$cacheObject->get_cache(WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY);
			if(null !== $valueTemp) {
				$options = array_merge($options,(array)$valueTemp);
			}
			$valueTemp = 0;
		}
		
		if(!empty($options)) {
			return $options;
		}
		
		return null;
	}
	
	public function optimize_cache_get_userhash_current_request()
	{
        $keyCache1 = 'g_ushs_cr_rq';
		if(!isset($this->baseCacheData[$keyCache1])) {
            $userhash = '';
            
            if(isset($_COOKIE) && $_COOKIE) {
                if(is_array($_COOKIE)) {
                    foreach($_COOKIE as $keyOne => $valueOne) {
                        $keyOne = (string)$keyOne;
                        $valueOne = (string)$valueOne;
                        
                        $keyOne = trim($keyOne);
                        $valueOne = trim($valueOne);
                        
                        if($keyOne && $valueOne) {
                            
                            if(preg_match('#^wordpress_logged_in_.+$#i',$keyOne)) {
                                $userhash = $valueOne;
                                $userhash = md5($userhash);
                                break;
                            }
                        }
                    }
                }
            }
            
            $this->baseCacheData[$keyCache1] = $userhash;
        }
        
		return $this->baseCacheData[$keyCache1];
		
	}
	
	
	public function optimize_cache_get_filenamecache_current_request()
	{
		
		$currentRequestId = '';
		
		if ( PepVN_Data::isMobileDevice() ) {
			$currentRequestId .= '-device-mobile';
		} else {
			$currentRequestId .= '-device-computer';
		}
		
		
		$currentRequestId .= '-user-'.md5($this->optimize_cache_get_userhash_current_request());
		
		$currentRequestId .= '-url-'.$this->urlFullRequest;
		
		$device_screen_width = $this->optimize_images_check_get_screen_width();
		
		$currentRequestId .= '-scrwd-'.$device_screen_width;
		
		return 'cp-'.md5($currentRequestId).'-pc';
	}
	
	
	
	public function optimize_cache_get_current_request_etag($filenamecache = 0,$rsGetFilemtime = 0)
	{
		if(!$filenamecache) {
			$filenamecache = $this->optimize_cache_get_filenamecache_current_request();
		}
		
		if(!$rsGetFilemtime) {
			$rsGetFilemtime = PepVN_Data::$cacheObject->get_filemtime_filecache($filenamecache);
		}
		
		if(0 == $rsGetFilemtime) {
			$rsGetFilemtime = PepVN_Data::$defaultParams['requestTime'];
		}
		
		
		$etag = $filenamecache.'?etag='.$rsGetFilemtime;
		$etag = md5($etag);
		
		return $etag;
		
	}
	
	public function optimize_cache_get_info_current_request($input_mode = 1)
	{
		$input_mode = (int)$input_mode;
		
		$keyCache1 = 'optimize_cache_get_info_current_request_'.$input_mode;
		if(!isset($this->baseCacheData[$keyCache1])) {
			$options = $this->get_options(array(
				'cache_status' => 1
			));
			
			if(null === $options) {
				return false;
			}
			
			$this->baseCacheData[$keyCache1] = array();
			
			$filenamecache = $this->optimize_cache_get_filenamecache_current_request();
			$rsGetFilemtime = PepVN_Data::$cacheObject->get_filemtime_filecache($filenamecache);
			
			if(0 === $rsGetFilemtime) {
				$rsGetFilemtime = PepVN_Data::$defaultParams['requestTime'];
			}
			
			$etag = $this->optimize_cache_get_current_request_etag($filenamecache, $rsGetFilemtime);
			
			$contentType = '';
			if($input_mode) {
				$contentType = 'text/html; charset=UTF-8';
				if(is_feed()) {
					$contentType = 'application/xml; charset=UTF-8';
				}
				
				if(false !== stripos($contentType,'text/html')) {
					global $wpOptimizeByxTraffic;
					if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
						
						$wpOptimizeByxTraffic_options = $wpOptimizeByxTraffic->get_options(array(
							'cache_status' => 1
						));
						
						if(isset($wpOptimizeByxTraffic_options['optimize_images_auto_resize_images_enable']) && $wpOptimizeByxTraffic_options['optimize_images_auto_resize_images_enable']) {
							$valueTemp = 'auto_resize_images_';
							$valueTemp .= $wpOptimizeByxTraffic->get_device_screen_width();
							$filenamecache .= $valueTemp;
							$etag .= $valueTemp;
						}
					}
				}
			}
			
			$etag = hash('crc32b',$etag);
			$filenamecache = hash('crc32b',$filenamecache);
			
			$this->baseCacheData[$keyCache1]['filenamecache'] = $filenamecache;
			$this->baseCacheData[$keyCache1]['filemtime'] = $rsGetFilemtime;
			$this->baseCacheData[$keyCache1]['etag'] = $etag;
			$this->baseCacheData[$keyCache1]['content_type'] = $contentType;
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	
	public function optimize_cache_flush_http_headers($input_parameters)
	{
		if (headers_sent()) {
			return false;
		}
		
		header('X-Powered-By: '.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'/'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION,true);
		//header('Server: '.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'/'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION,true); 
		
		if(isset($input_parameters['content_type']) && $input_parameters['content_type']) {
			header('Content-Type: '.$input_parameters['content_type'],true);
		}
		
		if(isset($input_parameters['cache_timeout']) && $input_parameters['cache_timeout']) {
			header('Cache-Control: public, max-age='.$input_parameters['cache_timeout'],true);
			header('Pragma: public',true);
		}
		
		if(isset($input_parameters['cache_timeout']) && $input_parameters['cache_timeout']) {
			
			if(isset($input_parameters['etag'])) {
				header('Etag: '.$input_parameters['etag'],true);
			}
			
			$lastModifiedTime = PepVN_Data::$defaultParams['requestTime'];
			if(isset($input_parameters['last_modified_time']) && $input_parameters['last_modified_time']) {
				if($input_parameters['last_modified_time'] > 0) {
					$lastModifiedTime = $input_parameters['last_modified_time'];
				}
			}
			$lastModifiedTime = (int)$lastModifiedTime;
			
			header('Expires: '.PepVN_Data::gmdate_gmt($lastModifiedTime + $input_parameters['cache_timeout']),true);
			
			header('Last-Modified: '.PepVN_Data::gmdate_gmt($lastModifiedTime),true);
			
		}
		
		if(isset($input_parameters['isNotModifiedStatus'])) {
			header('HTTP/1.1 304 Not Modified',true,304);
			exit();
		}
		
	}
	
	public function optimize_cache_isCacheable($options)
	{
		$urlFullRequest = PepVN_Data::$defaultParams['urlFullRequest'];
		$requestMethod = PepVN_Data::getRequestMethod();
		$fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		
		$keyCache = PepVN_Data::fKey(array(
			__METHOD__
			, $urlFullRequest
			, $requestMethod
			, $fullDomainName
			, $options
		)); 
		
		if(isset($this->baseCacheData[$keyCache])) {
			return $this->baseCacheData[$keyCache];
		}
		
		$isCacheStatus = false;
		
		if(isset($options['optimize_cache_enable']) && $options['optimize_cache_enable']) {
			$isCacheStatus = true;
		}
        
        
        if($isCacheStatus) {
			if(preg_match('#('.$this->patternRequestUriNotCache.')#i', $urlFullRequest)) {
				$isCacheStatus = false;
			}
		}
        
		if($isCacheStatus) {//no cache special path
			if(preg_match('#['.preg_quote('`~!@#$%^*()|;\'",<>+“”‘’','#').']+#i',PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'])) {
				$isCacheStatus = false;
			}
		}
		
        /*
		if($isCacheStatus) {
			if ( preg_match('#'.PepVN_Data::preg_quote($fullDomainName).'/.+(\.php)#i', $urlFullRequest) ) {
				$isCacheStatus = false;
			}
		}
		
		if($isCacheStatus) {
			if($fullDomainName) {
				if ( preg_match('#'.PepVN_Data::preg_quote($fullDomainName).'/(wp-admin|wp-content|wp-includes)/#i', $urlFullRequest) ) {
					$isCacheStatus = false;
				}
			}
		}
		
		if($isCacheStatus) {
			if ( preg_match('#\.php#i', $urlFullRequest) ) {
				$isCacheStatus = false;
			}
		}
        */
		
		if($isCacheStatus) {
			if('get' !== $requestMethod) {
				$isCacheStatus = false;
			}
		}
		
		if($isCacheStatus) {
			if(true === PepVN_Data::isAjaxRequest()) {
				$isCacheStatus = false;
			}
		}
		
		if($isCacheStatus) {
			if(true !== PepVN_Data::isHttpResponseCode(200)) {
				$isCacheStatus = false;
			}
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_cache_url_get_query_cache_enable']) && $options['optimize_cache_url_get_query_cache_enable']) {
			
			} else {
				if ( preg_match('#.+\?+.*?#i', $urlFullRequest) ) {
					$isCacheStatus = false; 
				}
			}
		}
		
		if($isCacheStatus) {
			if(is_admin()) {
				$isCacheStatus = false; 
			}
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_cache_feed_page_cache_enable']) && $options['optimize_cache_feed_page_cache_enable']) {
			
			} else {
				if(is_feed()) {
					$isCacheStatus = false; 
				}
			}
		}
		
		if($isCacheStatus) {
			if(
				!is_single()
				&& !is_page()
				&& !is_singular()
				&& !is_feed()
			) {
				if(isset($options['optimize_cache_front_page_cache_enable']) && $options['optimize_cache_front_page_cache_enable']) {
				
				} else {
					$isCacheStatus = false;
				}
			}
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_cache_ssl_request_cache_enable']) && $options['optimize_cache_ssl_request_cache_enable']) {
			
			} else {
				if(PepVN_Data::is_ssl()) {
					$isCacheStatus = false; 
				}
			}
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_cache_mobile_device_cache_enable']) && $options['optimize_cache_mobile_device_cache_enable']) {
			
			} else {
				if ( PepVN_Data::isMobileDevice() ) {
					$isCacheStatus = false; 
				}
			}
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_cache_logged_users_cache_enable']) && $options['optimize_cache_logged_users_cache_enable']) {
			
			} else {
				if(PepVN_Data::wp_get_current_user_id() > 0) {
					$isCacheStatus = false; 
				}
			}
		}
		
		if($isCacheStatus) {
            if(isset($options['optimize_cache_exclude_url']) && $options['optimize_cache_exclude_url']) {
                $options['optimize_cache_exclude_url'] = trim($options['optimize_cache_exclude_url']);
                $valueTemp = $options['optimize_cache_exclude_url'];
                if($valueTemp) {
                    $valueTemp = PepVN_Data::cleanPregPatternsArray($valueTemp);
                    if($valueTemp && !empty($valueTemp)) {
                        if(preg_match('#('.implode('|',$valueTemp).')#i',$urlFullRequest)) {
                            $isCacheStatus = false;
                        }
                    }
                }
            }
		}
        
        
        $cookiesImploded = false;
        
        if($isCacheStatus) {
            if(isset($_COOKIE) && $_COOKIE && !empty($_COOKIE)) {
                $cookiesImploded = implode(';',$_COOKIE);
                $cookiesImploded = trim($cookiesImploded);
                if($cookiesImploded) {
                    if(preg_match('#('.$this->patternCookiesNotCache.')#', $cookiesImploded)) {
                        $isCacheStatus = false;
                    }
                }
            }
        }
		
        /*
		if($isCacheStatus) {
			
			if(isset($options['optimize_cache_exclude_cookie']) && $options['optimize_cache_exclude_cookie']) {
				$options['optimize_cache_exclude_cookie'] = trim($options['optimize_cache_exclude_cookie']);
				if($options['optimize_cache_exclude_cookie']) {
					$options['optimize_cache_exclude_cookie'] = explode(',',$options['optimize_cache_exclude_cookie']);
					$options['optimize_cache_exclude_cookie'] = PepVN_Data::cleanArray($options['optimize_cache_exclude_cookie']);
					if(count($options['optimize_cache_exclude_cookie']) > 0) {
						
						foreach($options['optimize_cache_exclude_cookie'] as $valueOne) {
							
							$cookieKey = false;
							$cookieVal = false;
							
							if(false !== strpos($valueOne,'=')) {
								$valueOne = explode('=',$valueOne, 2);
								if(isset($valueOne[0]) && isset($valueOne[1])) {
									$cookieKey = $valueOne[0];
									$cookieVal = $valueOne[1];
								}
								
							} else {
								$cookieKey = $valueOne;
							}
							
							if(false !== $cookieKey) {
								if(isset($_COOKIE[$cookieKey])) {
									if(false !== $cookieVal) {
										if($_COOKIE[$cookieKey] == $cookieVal) {
											$isCacheStatus = false; 
										}
									} else {
										$isCacheStatus = false;
									}
								}
								
							}
							
							if(!$isCacheStatus) {
								break;
							}
							
						}
					}
				}
			}
		}
        */
        
        if($isCacheStatus) {
            if($cookiesImploded) {
                if(isset($options['optimize_cache_exclude_cookie']) && $options['optimize_cache_exclude_cookie']) {
                    $options['optimize_cache_exclude_cookie'] = trim($options['optimize_cache_exclude_cookie']);
                    $valueTemp = $options['optimize_cache_exclude_cookie'];
                    if($valueTemp) {
                        $valueTemp = PepVN_Data::cleanPregPatternsArray($valueTemp);
                        if($valueTemp && !empty($valueTemp)) {
                            if(preg_match('#('.implode('|',$valueTemp).')#i',$cookiesImploded)) {
                                $isCacheStatus = false;
                            }
                        }
                    }
                }
            }
		}
		
		$this->baseCacheData[$keyCache] = $isCacheStatus;
		
		return $this->baseCacheData[$keyCache];
	}
	
	public function optimize_cache_check_and_create_static_page_cache_for_server_software($input_parameters)
	{
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		
		$checkStatus1 = false;
		
		if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) {
			$checkStatus1 = true;
		}
		
		if($checkStatus1) {//no cache special path
			if(preg_match('#['.preg_quote('`~!@#$%^*()|;\'",<>+“”‘’','#').']+#i',PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'])) {
				$checkStatus1 = false;
			}
		}
		
		
		if($checkStatus1) {	//no cache with GET query
			if(isset($options['optimize_cache_url_get_query_cache_enable']) && $options['optimize_cache_url_get_query_cache_enable']) {
				
			} else {
				if(
					isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['parameters']) 
					&& PepVN_Data::$defaultParams['parseedUrlFullRequest']['parameters']
					&& !empty(PepVN_Data::$defaultParams['parseedUrlFullRequest']['parameters'])
				) {
					$checkStatus1 = false;
				}
			}
		}
		
		if($checkStatus1) {
			$userhash = $this->optimize_cache_get_userhash_current_request();
			if($userhash) {	//no cache with user logged in
				$checkStatus1 = false;
			}
		}
		
		if($checkStatus1) {
			if ( PepVN_Data::isMobileDevice() ) {	//no cache with mobile
				$checkStatus1 = false;
			}
		}
		
		if($checkStatus1) {
			$checkStatus1 = false;
			if(isset(PepVN_Data::$defaultParams['serverSoftware']) && PepVN_Data::$defaultParams['serverSoftware']) {
				if(
					('apache' === PepVN_Data::$defaultParams['serverSoftware'])
					|| ('nginx' === PepVN_Data::$defaultParams['serverSoftware'])
				) {
					$checkStatus1 = true;
				}
			}
		}
		
		
		if($checkStatus1) {
			$checkStatus1 = false;
			if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['scheme']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['scheme']) {
				if(
					('http' === PepVN_Data::$defaultParams['parseedUrlFullRequest']['scheme'])
					|| ('https' === PepVN_Data::$defaultParams['parseedUrlFullRequest']['scheme'])
				) {
					$checkStatus1 = true;
				}
			}
		}
		
		
		if($checkStatus1) {
			
			$folderPath1 = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH.'data/';
			
			if(!file_exists($folderPath1)) {
				PepVN_Data::createFolder($folderPath1, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			}
			
			$folderPathPlus = '';
			$folderPathPlus .= PepVN_Data::$defaultParams['parseedUrlFullRequest']['host'] . '/';
			
			$folderPathTemp = $folderPath1.$folderPathPlus;
			if(!file_exists($folderPathTemp)) {
				@mkdir($folderPathTemp);
			}
			
			
			$httpsPathPlus = '';
			if(PepVN_Data::is_ssl()) {
				$httpsPathPlus = '-https';
			}
			
			if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'])) {
				$folderPathPlus .= PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'] . '/';
			}
			
			$folderPathPlus = PepVN_Data::fixPath($folderPathPlus) . DIRECTORY_SEPARATOR;
			
			$folderPath2 = $folderPath1.$folderPathPlus;
			
			if(file_exists($folderPath2) && is_dir($folderPath2)) {
			} else {
				PepVN_Data::createFolder($folderPath2);
			}
			
			if(file_exists($folderPath2) && is_dir($folderPath2)) {
			} else {
				@mkdir($folderPath2,WPOPTIMIZEBYXTRAFFIC_CHMOD,true);
			}
			
			if(PepVN_Data::is_writable($folderPath2)) {
				
				if(isset($input_parameters['file_extension']) && $input_parameters['file_extension']) {
					$fileExtension = $input_parameters['file_extension'];
				} else {
					$fileExtension = 'html';
					
					if(function_exists('is_feed')) {
						if(is_feed()) {
							$fileExtension = 'xml';
						}
					}
				}
				
				$filePath1 = $folderPath2.'index'.$httpsPathPlus;
				if('html' === $fileExtension) {
					$filePath1 .= '-sw_';
					global $wpOptimizeByxTraffic;
					if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
						
						$wpOptimizeByxTraffic_options = $wpOptimizeByxTraffic->get_options(array(
							'cache_status' => 1
						));
						
						if(isset($wpOptimizeByxTraffic_options['optimize_images_auto_resize_images_enable']) && $wpOptimizeByxTraffic_options['optimize_images_auto_resize_images_enable']) {
							$screenWidth = $wpOptimizeByxTraffic->get_device_screen_width();
							if($screenWidth && ($screenWidth>0)) {
								$filePath1 .= $screenWidth;
							}
						}
					}
				}
				
				$filePath1 .= '.'.$fileExtension;
				
				$isWritableFileStatus1 = false;
				
				if(!file_exists($filePath1)) {
					$isWritableFileStatus1 = true;
				} else {
					if(PepVN_Data::is_writable($filePath1)) {
						if(isset($input_parameters['force_write_status']) && $input_parameters['force_write_status']) {
							$isWritableFileStatus1 = true;
						}
						
					}
				}
				
				if($isWritableFileStatus1) {
					
					@file_put_contents($filePath1, $input_parameters['content']);
					
					/*
					if(
						('apache' === PepVN_Data::$defaultParams['serverSoftware'])
					) {
						
						$filePath1 .= '.gz';
							
						$isWritableFileStatus2 = false;
						
						if(!file_exists($filePath1)) {
							$isWritableFileStatus2 = true;
						} else {
							if(PepVN_Data::is_writable($filePath1)) {
								$isWritableFileStatus2 = true;
							}
						}
						
						if($isWritableFileStatus2) {
							$input_parameters['content'] = @gzencode( $input_parameters['content'], 9, FORCE_GZIP );
							if($input_parameters['content']) {
								@file_put_contents($filePath1,$input_parameters['content']);
							}
						}
					}
					*/
				}
				
			}
			
		}
	}
	
	public function optimize_cache_check_and_get_page_cache()
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(null === $options) {
			return false;
		}
		
		$isCacheableStatus = $this->optimize_cache_isCacheable($options);
		
		if(!$isCacheableStatus) {
			return false;
		}
        
		$isBrowserCacheStatus = false;
		if(isset($options['optimize_cache_browser_cache_enable']) && $options['optimize_cache_browser_cache_enable']) {
			$isBrowserCacheStatus = true;
		}
		
		if(!isset($options['optimize_cache_cachetimeout'])) {
			$options['optimize_cache_cachetimeout'] = 0;
		}
		
		$options['optimize_cache_cachetimeout'] = (int)$options['optimize_cache_cachetimeout'];
		if($options['optimize_cache_cachetimeout'] < 300) {
			$options['optimize_cache_cachetimeout'] = 300; 
		}
		
		$rsOne = $this->optimize_cache_get_info_current_request(0);
		
		$filenamecache = $rsOne['filenamecache'];
		$rsGetFilemtime = $rsOne['filemtime'];
		$etag = $rsOne['etag'];
		$contentType = $rsOne['content_type'];
		
		$isNotModifiedStatus = false;
		
		if($isBrowserCacheStatus) {
			
			if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH']) {
				if(trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
					$isNotModifiedStatus = true;
				}
			}
			
			if(!$isNotModifiedStatus) {
			
				if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
					if($rsGetFilemtime > 0) {
						if(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $rsGetFilemtime) {
							$isNotModifiedStatus = true;
						}
					}
					
				}
				
			}
			
			if($isNotModifiedStatus) {
				$parametersTemp = array();
				$parametersTemp['isNotModifiedStatus'] = true;
				$parametersTemp['content_type'] = $contentType; 
				$parametersTemp['etag'] = $etag;
				$parametersTemp['last_modified_time'] = $rsGetFilemtime;
				$parametersTemp['cache_timeout'] = 0;
				if($isBrowserCacheStatus) {
					$parametersTemp['cache_timeout'] = ceil($options['optimize_cache_cachetimeout'] / 3);
				}
				
				$this->optimize_cache_flush_http_headers($parametersTemp);
				$parametersTemp = 0;
			}
			
		}
		
		$pageCacheContent = PepVN_Data::$cacheObject->get_cache($filenamecache);
		
		if(null !== $pageCacheContent) {
			
			$parametersTemp = array();
			$parametersTemp['content_type'] = $contentType;
			$parametersTemp['cache_timeout'] = 0;
			if($isBrowserCacheStatus) {
				$parametersTemp['etag'] = $etag;
				$parametersTemp['last_modified_time'] = $rsGetFilemtime;
				$parametersTemp['cache_timeout'] = ceil($options['optimize_cache_cachetimeout'] / 3);
			}
			
			$this->optimize_cache_flush_http_headers($parametersTemp); 
			$parametersTemp = 0;
			
			echo $pageCacheContent; $pageCacheContent = null;
			
			exit();
		}
		
	}
	
	
	public function statistic_access_urls_sites($input_options)
	{
		$urlsNeedStatistics = array();
		
		if(isset(PepVN_Data::$defaultParams['urlFullRequest']) && PepVN_Data::$defaultParams['urlFullRequest']) {
			$urlsNeedStatistics[] = PepVN_Data::$defaultParams['urlFullRequest'];
		}
		
		
		$dataSent = PepVN_Data::getDataSent();
		if($dataSent && isset($dataSent['localTimeSent']) && $dataSent['localTimeSent']) {
			if(isset($dataSent['window_location_href']) && $dataSent['window_location_href']) {
				$urlsNeedStatistics[] = $dataSent['window_location_href'];
			}
		}
		$dataSent = 0;
		
		$urlsNeedStatistics = array_unique($urlsNeedStatistics);
		if(!empty($urlsNeedStatistics)) {
			foreach($urlsNeedStatistics as $key1 => $val1) {
				$checkStatus1 = false;
				if(preg_match('#^https?://.+#is',$val1)) {
					if(!preg_match('#(\#|\?|wp\-(admin|content|includes)|\.php)+#i',$val1)) {
						$checkStatus1 = true;
					}
				}
				
				if(!$checkStatus1) {
					unset($urlsNeedStatistics[$key1]);
				}
			}
		}
		
		$staticVarData = PepVN_Data::staticVar_GetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, false); 
		
		if(!empty($urlsNeedStatistics)) {
			foreach($urlsNeedStatistics as $key1 => $val1) {
				
				if(!isset($staticVarData['statistics']['group_urls'][$val1])) {
					$staticVarData['statistics']['group_urls'][$val1] = array(
						'v' => 0	//total number views (request)
						,'t' => 0	//total time process (float)
						,'ht' => 0	//total hits with time process (request)
						,'r' => 0	//rank - float (higher is more important)
					);
				}
				
				if(isset($input_options['calculate_time_php_process_status'])) {
					$timeProcess = abs(microtime(true) - PepVN_Data::$defaultParams['microtime_start']);
					$timeProcess = (float)$timeProcess;
					
					$staticVarData['statistics']['group_urls'][$val1]['t'] += $timeProcess;
					$staticVarData['total_microtime_process'] += $timeProcess;
					
					$staticVarData['total_hits_microtime_process']++;
					$staticVarData['statistics']['group_urls'][$val1]['ht']++;
				} else {
					$staticVarData['statistics']['group_urls'][$val1]['v']++;
					$staticVarData['total_number_requests']++;
				}
				
				//calculate rank
				
				$staticVarData['statistics']['group_urls'][$val1]['r'] = $staticVarData['statistics']['group_urls'][$val1]['v'];
				
				$averageTimeProcessEachAllViews = 0;
				
				if($staticVarData['total_hits_microtime_process'] > 0) {
					$averageTimeProcessEachAllViews = $staticVarData['total_microtime_process'] / $staticVarData['total_hits_microtime_process'];
				}
				
				$averageTimeProcessEachAllViews = (float)$averageTimeProcessEachAllViews;
				
				if($averageTimeProcessEachAllViews > 0) {
					if($staticVarData['statistics']['group_urls'][$val1]['ht'] > 0) {
						
						$averageTimeProcessEachThisViews = $staticVarData['statistics']['group_urls'][$val1]['t'] / $staticVarData['statistics']['group_urls'][$val1]['ht'];
						$averageTimeProcessEachThisViews = (float)$averageTimeProcessEachThisViews;
						
						$staticVarData['statistics']['group_urls'][$val1]['r'] += ($staticVarData['statistics']['group_urls'][$val1]['ht'] * ($averageTimeProcessEachThisViews / $averageTimeProcessEachAllViews));
						
					}
					
				}
				
				$staticVarData['statistics']['group_urls'][$val1]['r'] = (float)$staticVarData['statistics']['group_urls'][$val1]['r'];
			}
			
			if(isset($input_options['calculate_time_php_process_status'])) {
				
				global $wpOptimizeByxTraffic;
				if(isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic) {
					$screenWidth = $wpOptimizeByxTraffic->get_device_screen_width();
					$screenWidth = (int)$screenWidth;
					if($screenWidth && ($screenWidth>0)) {
						$keyScreenWidth = 'w'.$screenWidth;
						if(!isset($staticVarData['statistics']['group_screen_width'][$keyScreenWidth])) {
							$staticVarData['statistics']['group_screen_width'][$keyScreenWidth] = 0;
						}
						$staticVarData['statistics']['group_screen_width'][$keyScreenWidth]++;
					}
					
				}
				
				
			}
		}
		
		if(
			(0 === $staticVarData['last_time_clean_data'])
			|| ($staticVarData['last_time_clean_data'] <= ( PepVN_Data::$defaultParams['requestTime'] - (2 * 3600)))	//is timeout
		) {
			
			PepVN_Data::ref_sort_array_by_key($staticVarData['statistics']['group_urls'], 'r', 'desc');
			
			$iNumber = 0;
			foreach($staticVarData['statistics']['group_urls'] as $key1 => $val1) {
				++$iNumber;
				if($iNumber >= 2000) {
					unset($staticVarData['statistics']['group_urls'][$key1]);
				}
			}
			
			$staticVarData['last_time_clean_data'] = PepVN_Data::$defaultParams['requestTime'];
			PepVN_Data::staticVar_SetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, $staticVarData, 'r');
		} else {
			PepVN_Data::staticVar_SetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, $staticVarData, 'm'); 
		}
		
		$staticVarData = 0;
	}
	
}//class WPOptimizeByxTraffic_AdvancedCache






class pepvn_wpdb_wrapper
{
	public $pepvn_wpdb_status = true;
	
	private $pepvn_wpDbObj = false;
	
	private $pepvn_BaseCacheData = array();
	
	public function __construct($wpdbObj) 
	{
		$this->pepvn_wpDbObj = $wpdbObj;
	}
	
	
	private function _pepvn_is_admin()
	{
		$keyCache = '_pepvn_is_admin';
		if(!isset($this->pepvn_BaseCacheData[$keyCache])) {
			if(is_admin()) {
				$this->pepvn_BaseCacheData[$keyCache] = true;
			} else {
				$this->pepvn_BaseCacheData[$keyCache] = false;
			}
		}
		
		return $this->pepvn_BaseCacheData[$keyCache];
	}
	
	
	private function _pepvn_is_cachable($method,$args,$key_check)
	{
		$key_check = hash('crc32b',$key_check);
		
		if(!isset($this->pepvn_BaseCacheData[$key_check])) {
			
			$isCachableStatus = true;
			
			if(is_admin()) {
				$isCachableStatus = false;
			} else {
			
				$methodsNotCache = array(
					'insert'
					,'replace'
					,'update'
					,'delete'
					,'get_results'
					,'query'
					,'get_row'
					,'get_col'
					,'get_var'
					
					
					//,'prepare'
					//,'get_row'
					//,'get_col'
					//,'get_var'
				);
				
				if(preg_match('#^('.implode('|',$methodsNotCache).').*?#s',$method)) {
					$isCachableStatus = false;
				} else if(!preg_match('#^get_.+#s',$method)) {
					$isCachableStatus = false;
					
					$methodsNotCache = array(
						'bail'
						,'check_connection'
						,'db_connect'
						,'flush'
						,'get_caller'
						,'hide_errors'
						,'init_charset'
						,'print_error'
						,'replace'
						,'select'
						,'set_blog_id'
						,'set_charset'
						,'set_prefix'
						,'set_sql_mode'
						,'show_errors'
						,'suppress_errors'
						,'timer_start'
						,'timer_stop'
					);
					
					if(!preg_match('#^('.implode('|',$methodsNotCache).')#',$method)) {
						if(isset($args[0]) && $args[0]) {
							$args[0] = (string)$args[0];
							if(preg_match('#^([\s \t\(])*?SELECT[\s \t]+#is',$args[0])) {
								if(!preg_match('#(FOUND_ROWS)#is',$args[0])) {
									$isCachableStatus = true; 
								}
							}
						}
					}
					
				} else {
					if(isset($args[0]) && $args[0]) {
						$args[0] = (string)$args[0];
						if(preg_match('#.*?(FOUND_ROWS).*?#is',$args[0])) {
							$isCachableStatus = false;
						}
					}
				}
			}
			
			if($isCachableStatus) {
				
				if(!isset($this->pepvn_BaseCacheData['_is_ajax'])) {
					if ( defined('DOING_AJAX') && DOING_AJAX ) {
						$this->pepvn_BaseCacheData['_is_ajax'] = true;
					} else {
						$this->pepvn_BaseCacheData['_is_ajax'] = false;
					}
				}
				
				if($this->pepvn_BaseCacheData['_is_ajax']) {
					$isCachableStatus = false;
				}
			}
			
			$this->pepvn_BaseCacheData[$key_check] = $isCachableStatus;
		}
		
		return $this->pepvn_BaseCacheData[$key_check];
	}
	
	public function __isset( $name ) {
		return isset( $this->pepvn_wpDbObj->$name );
	}
	
	public function __set( $name, $value ) 
	{
		$this->pepvn_wpDbObj->$name = $value;
	}
	
	public function __unset( $name ) 
	{
		unset( $this->pepvn_wpDbObj->$name );
	}
	
	public function __get($varname)
    {
		return $this->pepvn_wpDbObj->$varname;
    }
	
	public static function __callStatic($method, $args)
    {
		return $this->_pepvn_process_call_wpdb_method($method,$args); 
    }
	
	public function __call($method,$args)
    {
		return $this->_pepvn_process_call_wpdb_method($method,$args);
    }
	
	
	private function _pepvn_is_process_call_wpdb_method($method) 
	{
		$key_check = hash('crc32b', '_pepvn_is_process_call_wpdb_method'.$method);
		
		if(!isset($this->pepvn_BaseCacheData[$key_check])) {
			
			$this->pepvn_BaseCacheData[$key_check] = false;
			
			if(
				!method_exists($this,$method)
				&& method_exists($this->pepvn_wpDbObj,$method)
			) {
				$this->pepvn_BaseCacheData[$key_check] = true;
			}
		}
		
		return $this->pepvn_BaseCacheData[$key_check];
	}
	
	private function _pepvn_process_call_wpdb_method($method,$args) 
	{
		
		$resultData = null;
		
		if(
			$this->_pepvn_is_process_call_wpdb_method($method)
		) {
			
			if(0 === strpos($method,'escape_by_ref')) {
				return $this->pepvn_wpDbObj->escape_by_ref($args[0]);
			}
			
			$keyCache = PepVN_Data::fKey(array($method,$args));
			
			$isCachableStatus = $this->_pepvn_is_cachable($method,$args,$keyCache);
			
			if($isCachableStatus) {
				$resultData = PepVN_Data::$cacheDbObject->get_cache($keyCache);
			}
			
			if(null === $resultData) {
				$resultData = call_user_func_array(array($this->pepvn_wpDbObj, $method), $args);
				
				if($isCachableStatus) {
					if(null !== $resultData) {
						PepVN_Data::$cacheDbObject->set_cache($keyCache,$resultData);
					}
					
				}
				
			}
			
		}
		
		return $resultData;
	}
	
	
	public function query($query) 
	{
		return $this->_pepvn_query( $query );
	}
	
	private function _pepvn_query( $query ) 
	{
		if ( ! $this->pepvn_wpDbObj->ready ) {
			return false;
		}
		
		/**
		 * Filter the database query.
		 *
		 * Some queries are made before the plugins have been loaded,
		 * and thus cannot be filtered with this method.
		 *
		 * @since 2.1.0
		 *
		 * @param string $query Database query.
		 */
		
		$this->pepvn_wpDbObj->flush();
		
		$this->pepvn_wpDbObj->last_query = $query;
		
		$keyCacheQuery = PepVN_Data::fKey(array(
			__METHOD__
			, $query
		));
		
		$rsDbCached = PepVN_Data::$cacheDbObject->get_cache($keyCacheQuery);
		
		if(null !== $rsDbCached) {
			
			$this->pepvn_wpDbObj->last_error = '';
			
			$this->pepvn_wpDbObj->last_query = $rsDbCached['last_query'];
			$this->pepvn_wpDbObj->last_result = $rsDbCached['last_result'];
			$this->pepvn_wpDbObj->col_info = $rsDbCached['col_info'];
			$this->pepvn_wpDbObj->num_rows = $rsDbCached['num_rows'];
			
			$return_val = $this->pepvn_wpDbObj->num_rows;
			
		} else {
			
			$return_val = $this->pepvn_wpDbObj->query( $query );
			
			if ( $return_val === false ) { // error executing sql query
				return false;
			} else {
				if(preg_match('#^([\s \t\(])*?SELECT[\s \t]+#is',$query)) {
					if(!preg_match('#(FOUND_ROWS)#is',$query)) {
						
						PepVN_Data::$cacheDbObject->set_cache($keyCacheQuery,array(
							'last_query' => $this->pepvn_wpDbObj->last_query
							,'last_result' => $this->pepvn_wpDbObj->last_result
							,'col_info' => $this->pepvn_wpDbObj->col_info
							,'num_rows' => $this->pepvn_wpDbObj->num_rows
						));
						
					}
				}
			}
			
			
		}
		
		
		return $return_val;
	}
	
	public function get_var( $query = null, $x = 0, $y = 0 ) 
	{
		$this->pepvn_wpDbObj->func_call = "\$db->get_var(\"$query\", $x, $y)";
		
		if ( $query ) {
			$this->query( $query );
		}
		// Extract var out of cached results based x,y vals
		if ( !empty( $this->pepvn_wpDbObj->last_result[$y] ) ) {
			$values = array_values( get_object_vars( $this->pepvn_wpDbObj->last_result[$y] ) );
		}
		// If there is a value return it else return null
		return ( isset( $values[$x] ) && $values[$x] !== '' ) ? $values[$x] : null;
	}
	
	public function get_row( $query = null, $output = OBJECT, $y = 0 ) 
	{
		$this->pepvn_wpDbObj->func_call = "\$db->get_row(\"$query\",$output,$y)";
		
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}
		if ( !isset( $this->pepvn_wpDbObj->last_result[$y] ) )
			return null;
		if ( $output == OBJECT ) {
			return $this->pepvn_wpDbObj->last_result[$y] ? $this->pepvn_wpDbObj->last_result[$y] : null;
		} elseif ( $output == ARRAY_A ) {
			return $this->pepvn_wpDbObj->last_result[$y] ? get_object_vars( $this->pepvn_wpDbObj->last_result[$y] ) : null;
		} elseif ( $output == ARRAY_N ) {
			return $this->pepvn_wpDbObj->last_result[$y] ? array_values( get_object_vars( $this->pepvn_wpDbObj->last_result[$y] ) ) : null;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return $this->pepvn_wpDbObj->last_result[$y] ? $this->pepvn_wpDbObj->last_result[$y] : null;
		} else {
			$this->pepvn_wpDbObj->print_error( " \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N" );
		}
	}
	
	public function get_col( $query = null , $x = 0 ) 
	{
		
		if ( $query ) {
			$this->query( $query );
		}
		
		$new_array = array();
		// Extract the column values
		for ( $i = 0, $j = count( $this->pepvn_wpDbObj->last_result ); $i < $j; $i++ ) {
			$new_array[$i] = $this->get_var( null, $x, $i );
		}
		return $new_array;
	}
	
	
	public function get_results( $query = null, $output = OBJECT ) 
	{
		$this->func_call = "\$db->get_results(\"$query\", $output)";
				
		if ( $query ) {
			$this->query( $query );
		} else {
			return null;
		}
		$new_array = array();
		if ( $output == OBJECT ) {
			// Return an integer-keyed array of row objects
			return $this->pepvn_wpDbObj->last_result;
		} elseif ( $output == OBJECT_K ) {
			// Return an array of row objects with keys from column 1
			// (Duplicates are discarded)
			foreach ( $this->pepvn_wpDbObj->last_result as $row ) {
				$var_by_ref = get_object_vars( $row );
				$key = array_shift( $var_by_ref );
				if ( ! isset( $new_array[ $key ] ) )
					$new_array[ $key ] = $row;
			}
			return $new_array;
		} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
			// Return an integer-keyed array of...
			if ( $this->pepvn_wpDbObj->last_result ) {
				foreach( (array) $this->pepvn_wpDbObj->last_result as $row ) {
					if ( $output == ARRAY_N ) {
						// ...integer-keyed row arrays
						$new_array[] = array_values( get_object_vars( $row ) );
					} else {
						// ...column name-keyed row arrays
						$new_array[] = get_object_vars( $row );
					}
				}
			}
			return $new_array;
		} elseif ( strtoupper( $output ) === OBJECT ) {
			// Back compat for OBJECT being previously case insensitive.
			return $this->pepvn_wpDbObj->last_result;
		}
		return null;
	}
}

global $wpOptimizeByxTraffic_AdvancedCache;
$wpOptimizeByxTraffic_AdvancedCache = 0;
$wpOptimizeByxTraffic_AdvancedCache = new WPOptimizeByxTraffic_AdvancedCache();

$wpOptimizeByxTraffic_AdvancedCache->statistic_access_urls_sites(array());

//$wpOptimizeByxTraffic_AdvancedCache->checkAndInitWpdbCache();	//dont set here, it make site error

endif; //if ( !class_exists('WPOptimizeByxTraffic_AdvancedCache') )

