<?php




if ( ! defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG' ) ) {
	require_once(WP_CONTENT_DIR . '/plugins/wp-optimize-by-xtraffic/inc/wp-optimize-by-xtraffic-init-constant.php');
}


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');






if ( !class_exists('WPOptimizeByxTraffic_AdvancedCache') ) :  



class WPOptimizeByxTraffic_AdvancedCache
{
	
	public $cacheObj;
	
	private $baseCacheData;
	
	public $urlFullRequest;
	
	public $optimize_cache_cachePageObject = false;
	
	
	
	function __construct() 
	{
		
		$this->cacheObj = new PepVN_Cache();
		$this->cacheObj->cache_time = 86400;
		
		$this->urlFullRequest = PepVN_Data::$defaultParams['urlFullRequest'];
		
	}
	
	
	private function get_options($input_parameters = false)
	{
		return $this->cacheObj->get_cache(WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY);
	}
	
	
	
	
	
	public function optimize_cache_get_userhash_current_request()
	{
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
		
		return $userhash;
		
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
		
		return 'cp-'.md5($currentRequestId).'-pc';
	}
	
	
	
	public function optimize_cache_get_current_request_etag($filenamecache = 0,$rsGetFilemtime = 0)
	{
		if(!$filenamecache) {
			$filenamecache = $this->optimize_cache_get_filenamecache_current_request();
		}
		
		if(!$rsGetFilemtime) {
			$rsGetFilemtime = PepVN_Data::$cacheSitePageObject->get_filemtime($filenamecache);
		}
		
		if(0 == $rsGetFilemtime) {
			$rsGetFilemtime = time();
		}
		
		
		$etag = $filenamecache.'?etag='.$rsGetFilemtime;
		$etag = md5($etag);
		
		return $etag;
		
	}
	
	public function optimize_cache_get_info_current_request($input_mode = 1)
	{
		$input_mode = (int)$input_mode;
		
		$keyCache1 = 'optimize_speed_optimize_cache_get_info_current_request_'.$input_mode;
		if(!isset($this->baseCacheData[$keyCache1])) {
			$options = $this->get_options(array(
				'cache_status' => 1
			));
			if(!$options) {
				return false;
			}
			
			
			$this->baseCacheData[$keyCache1] = array();
			
			
			$filenamecache = $this->optimize_cache_get_filenamecache_current_request();
			$rsGetFilemtime = PepVN_Data::$cacheSitePageObject->get_filemtime($filenamecache);
			
			if(0 == $rsGetFilemtime) {
				$rsGetFilemtime = time();
			}
			
			$etag = $this->optimize_cache_get_current_request_etag($filenamecache, $rsGetFilemtime);
			
			
			$contentType = '';
			if($input_mode) {
				$contentType = 'text/html; charset=UTF-8';
				if(is_feed()) {
					$contentType = 'application/xml; charset=UTF-8';
				}
			}
			
			
			
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
		header('Server: '.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'/'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION,true); 
		
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
			
			$lastModifiedTime = time();
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
		
		$isCacheStatus = false;
		
		if(isset($options['optimize_speed_optimize_cache_enable']) && $options['optimize_speed_optimize_cache_enable']) {
			$isCacheStatus = true;
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
		
		
		if($isCacheStatus) {
			if('get' !== $requestMethod) {
				$isCacheStatus = false;
			} else {
				if(isset($_POST) && ($_POST)) {
					if(is_array($_POST) && (count($_POST)>0)) {
						$isCacheStatus = false;
					}
				}
			}
		}
		
		
		if($isCacheStatus) {
			if(isset($options['optimize_speed_optimize_cache_url_get_query_cache_enable']) && $options['optimize_speed_optimize_cache_url_get_query_cache_enable']) {
			
			} else {
				if ( preg_match('#.+\?+.*?#i', $urlFullRequest) ) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		
		if($isCacheStatus) {
			if(isset($options['optimize_speed_optimize_cache_ssl_request_cache_enable']) && $options['optimize_speed_optimize_cache_ssl_request_cache_enable']) {
			
			} else {
				if(PepVN_Data::is_ssl()) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		
		if($isCacheStatus) {
			if(isset($options['optimize_speed_optimize_cache_mobile_device_cache_enable']) && $options['optimize_speed_optimize_cache_mobile_device_cache_enable']) {
			
			} else {
				if ( PepVN_Data::isMobileDevice() ) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		if($isCacheStatus) {
			
			if(isset($options['optimize_speed_optimize_cache_logged_users_cache_enable']) && $options['optimize_speed_optimize_cache_logged_users_cache_enable']) {
			
			} else {
				$userhash = $this->optimize_cache_get_userhash_current_request();
				if($userhash) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		
		
		return $isCacheStatus;
		
		
	}
	
	
	
	
	
	public function optimize_cache_check_and_create_static_page_cache_for_server_software($input_parameters)
	{
		
		$checkStatus1 = false;
		
		if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) {
			$checkStatus1 = true;
		}
		
		
		if($checkStatus1) {	//no cache with GET query
			if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['parameters']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['parameters']) {
				$checkStatus1 = false;
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
			
			if(PepVN_Data::is_ssl()) {
				$folderPathPlus .= 'https/';
			} else {
				$folderPathPlus .= 'http/';
			}
			
			if ( PepVN_Data::isMobileDevice() ) {
				$folderPathPlus .= 'mobile/';
			} else {
				$folderPathPlus .= 'pc/';
			}
			
			
			if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'])) {
				$folderPathPlus .= PepVN_Data::$defaultParams['parseedUrlFullRequest']['path'] . '/';
			}
			
			$folderPathPlus = PepVN_Data::fixPath($folderPathPlus) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
			
			$folderPath2 = $folderPath1.$folderPathPlus;
			
			if(!file_exists($folderPath2)) {
				PepVN_Data::createFolder($folderPath2, WPOPTIMIZEBYXTRAFFIC_CHMOD); 
			}
			
			
			
			
			if(PepVN_Data::is_writable($folderPath2)) {
				
				$filePath1 = $folderPath2.'index.html';
				
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
					$filePath1 = $folderPath2.'index.html.gz';
						
					$isWritableFileStatus2 = false;
					
					if(!file_exists($filePath1)) {
						$isWritableFileStatus2 = true;
					} else {
						if(PepVN_Data::is_writable($filePath1)) {
							$isWritableFileStatus2 = true;
						}
					}
					
					if($isWritableFileStatus2) {
						$input_parameters['content'] = @gzencode( $input_parameters['content'], 2, FORCE_GZIP );
						if($input_parameters['content']) {
							@file_put_contents($filePath1,$input_parameters['content']);
						}
					}
					//*/
				}
				
				
				
				
				
			}
			
		}
	}
	
	
	
	public function optimize_cache_check_and_get_page_cache()
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(!$options) {
			return false;
		}
		
		$isCacheableStatus = $this->optimize_cache_isCacheable($options);
		if(!$isCacheableStatus) {
			return false;
		}
		
		
		$isBrowserCacheStatus = false;
		if(isset($options['optimize_speed_optimize_cache_browser_cache_enable']) && $options['optimize_speed_optimize_cache_browser_cache_enable']) {
			$isBrowserCacheStatus = true;
		}
		
		if(!isset($options['optimize_speed_optimize_cache_cachetimeout'])) {
			$options['optimize_speed_optimize_cache_cachetimeout'] = 0;
		}
		
		$options['optimize_speed_optimize_cache_cachetimeout'] = (int)$options['optimize_speed_optimize_cache_cachetimeout'];
		if($options['optimize_speed_optimize_cache_cachetimeout'] < 300) {
			$options['optimize_speed_optimize_cache_cachetimeout'] = 300; 
		}
		
		PepVN_Data::$cacheSitePageObject->cache_time = $options['optimize_speed_optimize_cache_cachetimeout'];
		
		
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
					$parametersTemp['cache_timeout'] = $options['optimize_speed_optimize_cache_cachetimeout'];
				}
				
				$this->optimize_cache_flush_http_headers($parametersTemp);
				$parametersTemp = 0;
			}
			
		}
		
		$pageCacheContent = PepVN_Data::$cacheSitePageObject->get_cache($filenamecache);
		
		if($pageCacheContent) {
		
			$this->optimize_cache_check_and_create_static_page_cache_for_server_software(array(
				'content' => $pageCacheContent
			));
			
			$parametersTemp = array();
			$parametersTemp['content_type'] = $contentType;
			$parametersTemp['cache_timeout'] = 0;
			if($isBrowserCacheStatus) {
				$parametersTemp['etag'] = $etag;
				$parametersTemp['last_modified_time'] = $rsGetFilemtime;
				$parametersTemp['cache_timeout'] = $options['optimize_speed_optimize_cache_cachetimeout'];
			}
			
			$this->optimize_cache_flush_http_headers($parametersTemp); 
			$parametersTemp = 0;
			
			echo $pageCacheContent;
			
			exit();
		}
		
	}
	
	
	
	
	
	
	
	public function statistic_access_urls_sites()
	{
		
		$urlFullRequest = PepVN_Data::$defaultParams['urlFullRequest'];
		if(!preg_match('#(\#|\?|wp\-(admin|content|includes)|\.php)+#i',$urlFullRequest)) {
			
			$staticVarData = PepVN_Data::staticVar_GetData();
			
			if(!isset($staticVarData['statistics']['group_urls'][$urlFullRequest])) {
				$staticVarData['statistics']['group_urls'][$urlFullRequest] = 0;
			}
			$staticVarData['statistics']['group_urls'][$urlFullRequest]++;
			
			$staticVarData['total_number_requests']++;
			
			PepVN_Data::staticVar_SetData($staticVarData); 
			
			
		}
		
		
	}
	




	
	


}//class WPOptimizeByxTraffic_AdvancedCache

$wpOptimizeByxTraffic_AdvancedCache = new WPOptimizeByxTraffic_AdvancedCache();
$wpOptimizeByxTraffic_AdvancedCache->statistic_access_urls_sites();
$wpOptimizeByxTraffic_AdvancedCache->optimize_cache_check_and_get_page_cache();



endif; //if ( !class_exists('WPOptimizeByxTraffic_AdvancedCache') )

