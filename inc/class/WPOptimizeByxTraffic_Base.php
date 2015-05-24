<?php




if ( !class_exists('WPOptimizeByxTraffic_Base') ) :

class WPOptimizeByxTraffic_Base 
{
	
	// Name for our options in the DB
	protected $wpOptimizeByxTraffic_DB_option = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	protected $wpOptimizeByxTraffic_options; 
	
	protected $PLUGIN_NS = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	
	protected $PLUGIN_NAME = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME;
	
	protected $PLUGIN_SLUG = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG;
	
	public $baseObjects; //array(id => object)
	
	protected $mobileDetectObject = false;
	
	protected $currentUserId = false;
	
	protected $currentUserInfo = false;
	
	protected $wpOptions_GmtOffset = 0;
	protected $wpOptions_GmtOffsetString = '';
	public $wpCurrentUserTimestamp = 0;
	public $wpCurrentUserDatetime = '';
	
	
	protected $baseCacheData = array();
	
	protected $adminNoticesData = array();
	
	protected $urlFullRequest = '';
	
	
	protected $bufferSizeBytesForEncryptData = 1024;
	
	protected $bufferSizeBytesForReadAndWriteData = 1024;
	
	
	protected $logFolderPath = '';
	
	
	protected $homeUrl = '';
	protected $currentSiteUrl = '';
	protected $backstageSecureUrl = '';
	protected $currentAdminUrl = '';
	protected $currentAdminAjaxUrl = '';
	
	private $passwordForEncryptDataBackstageSecure = '';
	
	protected $http_UserAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';
	
	
	protected $systemSafeMaxExecutionTimeSeconds = 300;
	
	protected $enumNoticeTypes = false;
	
	
	protected $cacheObject_GetUri = false;
	
	public $admin_menu_page;
	
	public $requestTime = 0;
	
	public $mobileDetect = false;
	
	
	public $advancedCacheObject = false;
	
	function __construct() 
	{
		PepVN_Data::session_start();
		
		global $wpOptimizeByxTraffic_AdvancedCache;
		$this->advancedCacheObject = $wpOptimizeByxTraffic_AdvancedCache;
		
		$cachePathTemp = WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH;
		if($cachePathTemp && file_exists($cachePathTemp)) {
			
		} else {
			PepVN_Data::createFolder($cachePathTemp, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			//PepVN_Data::chmod($cachePathTemp,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD); 
		}
		
		$cachePathTemp = PEPVN_CACHE_DATA_DIR;
		if($cachePathTemp && file_exists($cachePathTemp)) {
			
		} else {
			PepVN_Data::createFolder($cachePathTemp, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			//PepVN_Data::chmod($cachePathTemp,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);   
		}
		
		
		$cachePathTemp = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH;
		if($cachePathTemp && file_exists($cachePathTemp)) {
			
		} else {
			PepVN_Data::createFolder($cachePathTemp, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			//PepVN_Data::chmod($cachePathTemp,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD); 
		}
		
		$valueTemp = WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT;
		$valueTemp = (string)$valueTemp;
		$valueTemp = sha1(md5(sha1($valueTemp)));
		
		$this->passwordForEncryptDataBackstageSecure = $valueTemp;
		
		$this->enumNoticeTypes = array(
			'success'
			,'warning'
			,'error'
		);
		
		
		$this->logFolderPath = WPOPTIMIZEBYXTRAFFIC_PATH.'inc/log/';
		
		
		$this->enumIndexFileName = array(
			'index.html'
			,'index.htm'
			,'index.shtml'
			,'default.htm'
			,'index.php'
		);
		
		$this->enumSpecialFileName = $this->enumIndexFileName;
		$this->enumSpecialFileName[] = '.htaccess';
		
		$serverInfo = $this->base_get_server_info(array('cache_status' => true));
		
		if(
			($serverInfo['server_max_execution_time_seconds'] > 0)
		) {
			$this->systemSafeMaxExecutionTimeSeconds = $serverInfo['server_max_execution_time_seconds'] - 5;
			$this->systemSafeMaxExecutionTimeSeconds = (int)$this->systemSafeMaxExecutionTimeSeconds;
			if($this->systemSafeMaxExecutionTimeSeconds > 15) {
				$this->systemSafeMaxExecutionTimeSeconds = 15;
			} else if($this->systemSafeMaxExecutionTimeSeconds < 1) {
				$this->systemSafeMaxExecutionTimeSeconds = 1;
			}
		}
		
		$this->bufferSizeBytesForEncryptData = 128 * 1024; //x KB - in bytes
		$this->bufferSizeBytesForEncryptData = (int)$this->bufferSizeBytesForEncryptData;
		
		$this->bufferSizeBytesForSplitFile = 128 * 1024; //x KB # in bytes 
		$this->bufferSizeBytesForSplitFile = (int)$this->bufferSizeBytesForSplitFile;
		
		$this->bufferSizeBytesForReadAndWriteData = 1 * 1024 * 1024; //x MB - in bytes
		$this->bufferSizeBytesForReadAndWriteData = (int)$this->bufferSizeBytesForReadAndWriteData;
		
		
		$this->wpOptions_GmtOffset = get_option('gmt_offset');
		$this->wpOptions_GmtOffset = (int)$this->wpOptions_GmtOffset;
		
		$this->wpOptions_GmtOffsetString = 'GMT';
		if($this->wpOptions_GmtOffset < 0) {
			$this->wpOptions_GmtOffsetString .= ' -';
		} else {
			$this->wpOptions_GmtOffsetString .= ' +';
		}
		$this->wpOptions_GmtOffsetString .= str_pad($this->wpOptions_GmtOffset, 2, "0", STR_PAD_LEFT).':00';
		
		$this->wpCurrentUserDatetime = current_time('mysql');
		$this->wpCurrentUserDatetime = (string)$this->wpCurrentUserDatetime;
		
		
		$this->wpCurrentUserTimestamp = current_time('timestamp', $this->wpOptions_GmtOffset);
		$this->wpCurrentUserTimestamp = (float)$this->wpCurrentUserTimestamp;
		
		
		$this->homeUrl = home_url( '/' );
		$this->currentSiteUrl = get_site_url();
		
		$this->currentAdminUrl = admin_url();//get_admin_url();
		
		$this->currentAdminAjaxUrl = admin_url('admin-ajax.php');
		
		$this->backstageSecureUrl = WPOPTIMIZEBYXTRAFFIC_ADMIN_AJAX_URL.'?action=xtr_base_backstage_secure_action&__ts='.PepVN_Data::$defaultParams['requestTime']; 
		
		
		
		$priorityFirst = 0 + (mt_rand() / 1000000000);
		$priorityFirst = (float)$priorityFirst;
		
		$priorityLast = 99999999.999999999 + (mt_rand() / 1000000000);
		$priorityLast = (float)$priorityLast;
		
		$priorityLast2 = $priorityLast + 9;
		$priorityLast3 = $priorityLast2 + 9;
	
	
		$doActions = array();
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$this->_init_cache_object();
		
		if ($options) {
			
			if(!is_admin()) {
				
				if ($options['optimize_links_process_in_post'] || $options['optimize_links_process_in_page']) {	
					add_filter('the_content',  array(&$this, 'optimize_links_the_content_filter'), $priorityLast);	
					
					if ( 
						class_exists( 'bbPress' ) 
					) {
						add_filter('bbp_get_topic_content',  array(&$this, 'optimize_links_the_content_filter'), $priorityLast);	
						add_filter('bbp_get_reply_content',  array(&$this, 'optimize_links_the_content_filter'), $priorityLast);
						
					}
				}
				
					
				
				add_filter('the_content',  array(&$this, 'optimize_images_the_content_filter'), $priorityLast);
				
				if ( 
					class_exists( 'bbPress' ) 
				) {
					add_filter('bbp_get_topic_content',  array(&$this, 'optimize_images_the_content_filter'), $priorityLast);
					add_filter('bbp_get_reply_content',  array(&$this, 'optimize_images_the_content_filter'), $priorityLast);
					
				}
				
				
				if ($options['optimize_links_process_in_comment']) {
					add_filter('comment_text',  array(&$this, 'optimize_links_comment_text_filter'), $priorityLast);	
				}
				
				
				add_filter('the_content',  array(&$this, 'optimize_traffic_the_content_filter'), $priorityLast2);
				
				
				//!base_is_admin
			} else {
					
				add_filter('admin_head',  array(&$this, 'header_footer_the_admin_head_filter'), $priorityLast);
				
			}
			
			add_filter('wp_head',  array(&$this, 'header_footer_the_head_filter'), $priorityLast);
			add_filter('wp_footer',  array(&$this, 'header_footer_the_footer_filter'), $priorityLast);
			add_filter('the_content',  array(&$this, 'header_footer_the_content_filter'), $priorityLast);
			
			add_action('login_head', array(&$this, 'header_footer_the_head_filter'), $priorityLast);
		}
		
		

		if (isset($options['notice']) && $options['notice']) {
			$adminNoticesData[] = $options['notice'];
		}
		
		if(isset($doActions['updateOptions']) && $doActions['updateOptions']) {
			$this->update_options($this->wpOptimizeByxTraffic_DB_option, $options);
		}
		
		if(isset($doActions['base_clear_data']) && $doActions['base_clear_data']) {
			$this->base_clear_data(',all,');
		}
		
		
		$this->fullDomainName = '';
		if(PepVN_Data::$defaultParams['fullDomainName']) {
			$this->fullDomainName = PepVN_Data::$defaultParams['fullDomainName'];
		} else {
			$parseUrl = parse_url(get_bloginfo('wpurl'));
			if(isset($parseUrl['host']) && $parseUrl['host']) {
				$this->fullDomainName = $parseUrl['host'];
				PepVN_Data::$defaultParams['fullDomainName'] = $this->fullDomainName;
				
			}
		}
		
		$this->urlProtocol = PepVN_Data::$defaultParams['urlProtocol'];
		
		$this->urlFullRequest = PepVN_Data::$defaultParams['urlFullRequest']; 
		$this->urlRequestNoParameters = PepVN_Data::$defaultParams['urlFullRequest'];
		if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['url_no_parameters']) && (PepVN_Data::$defaultParams['parseedUrlFullRequest']['url_no_parameters'])) {
			$this->urlRequestNoParameters = PepVN_Data::$defaultParams['parseedUrlFullRequest']['url_no_parameters']; 
		}
		
		if(is_admin()) {
			$this->is_admin_init();
		}
		
		
		//$this->base_get_server_info();
		//$this->base_test_on_request();
	}
	
	private function is_admin_init() 
	{
		
		// Add Options Page
		add_action('admin_menu',  array(&$this, 'admin_menu'));
		
		add_action('admin_notices', array(&$this,'admin_notice'));
		
	}
	
	
	
	private function _init_cache_object() 
	{
		if(false === $this->cacheObject_GetUri) {
			
			$dirPathTemp = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'files'.DIRECTORY_SEPARATOR;
			$cacheTimeoutTemp = 86400 * 15;
			
			if(!is_dir($dirPathTemp)) {
				PepVN_Data::createFolder($dirPathTemp);
			}

			if(is_dir($dirPathTemp) && is_readable($dirPathTemp) && is_writable($dirPathTemp)) {
				$hashKeySaltTemp = __FILE__ . __METHOD__;
				if(defined('WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT')) {
					$hashKeySaltTemp .= '_'.WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_SALT;
				}
				$this->cacheObject_GetUri = new PepVN_SimpleFileCache(array(
					'cache_timeout' => $cacheTimeoutTemp				//seconds
					,'hash_key_method' => 'crc32b'		//best is crc32b
					,'hash_key_salt' => md5($hashKeySaltTemp)
					,'gzcompress_level' => 2
					,'key_prefix' => 'dt_'
					,'cache_dir' => $dirPathTemp
				));
			} else {
				$this->cacheObject_GetUri = new PepVN_SimpleFileCache(array()); 
			}

		}
		
	}
	
	
	/*
	* This method for test only
	*/
	public function base_test_on_request() 
	{
		return false;
		
		if(isset($_REQUEST['test_status'])) {
		
			$args = array(
			  'public'   => true
			); 
			
			$output = 'names'; // or objects
			$operator = 'and'; // 'and' or 'or'
			$taxonomies = get_taxonomies( $args, $output, $operator ); 
			
			$the_term = get_the_terms(299,'product_cat');
			
			$rsGetAllAvailableTaxonomies =  $this->base_get_all_available_taxonomies();
			
		}
	
	}
	
	public function base_parse_display_notices($input_notices) 
	{
		$resultData = $this->base_parse_notices_for_display($input_notices);
		$input_notices = 0;
		
		if($resultData && !empty($resultData)) {
			echo implode(' ',$resultData);
			return true;
		}
		
		return false;
	}
	
	
	
	public function base_parse_notices_for_display($input_notices) 
	{
		$resultData = array();
		
		$input_notices = (array)$input_notices;
		
		if(isset($input_notices['success'])) {
			if($input_notices['success']) {
				$input_notices['success'] = (array)$input_notices['success'];
				$input_notices['success'] = array_unique($input_notices['success']);
				foreach($input_notices['success'] as $valueOne) {
					$resultData[] = '<div style="display:block;"><div class="updated fade wpoptxtr_success" style="padding: 1% 2%;background-color:none;"><b>'.WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_NAME.'</b> : '.$valueOne.'</div></div>';
				}
			}
			unset($input_notices['success']);
		}
		
		if(isset($input_notices['error'])) {
			if($input_notices['error']) {
				$input_notices['error'] = (array)$input_notices['error'];
				$input_notices['error'] = array_unique($input_notices['error']);
				foreach($input_notices['error'] as $valueOne) {
					$resultData[] = '<div style="display:block;"><div class="update-nag fade wpoptxtr_error" style="padding: 1% 2%;background-color:none;"><b>'.WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_NAME.'</b> : '.$valueOne.'</div></div>';
				}
			}
			unset($input_notices['error']);
		}
		
		
		if(isset($input_notices['warning'])) {
			if($input_notices['warning']) {
				$input_notices['warning'] = (array)$input_notices['warning'];
				$input_notices['warning'] = array_unique($input_notices['warning']);
				foreach($input_notices['warning'] as $valueOne) {
					$resultData[] = '<div style="display:block;"><div class="update-nag fade" style="padding: 1% 2%;background-color:none;"><b>'.WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_NAME.'</b> : '.$valueOne.'</div></div>';
				}
			}
			unset($input_notices['warning']);
		}
		
		$resultData = array_unique($resultData);
		
		return $resultData;
	}
	
	
	public function base_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		/*
		*	error_no : 2x - Images ; 3x : Speed
		*/
		$resultData['notice']['error_no'] = array();
		
		
		
		$arrayFilesFoldersDoNotSetTheCorrectPermissions = array();
		
		
		$cachePathTemp = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH;
		if(
			$cachePathTemp
			&& file_exists($cachePathTemp)
			&& PepVN_Data::isAllowReadAndWrite($cachePathTemp)
		) {
			
			$cachePathTemp1 = WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH;
			if(
				$cachePathTemp1
				&& file_exists($cachePathTemp1)
				&& PepVN_Data::isAllowReadAndWrite($cachePathTemp1)
			) {
				
			} else {
				$arrayFilesFoldersDoNotSetTheCorrectPermissions[$cachePathTemp1] = array(
					'permissions' => array('readable','writable')
				);
				//$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp1.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			}
			
			
			
			$cachePathTemp1 = PEPVN_CACHE_DATA_DIR;
			if(
				$cachePathTemp1
				&& file_exists($cachePathTemp1)
				&& PepVN_Data::isAllowReadAndWrite($cachePathTemp1)
			) {
				
			} else {
				$arrayFilesFoldersDoNotSetTheCorrectPermissions[$cachePathTemp1] = array(
					'permissions' => array('readable','writable')
				);
				//$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp1.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			}
			
			
		} else {
			$arrayFilesFoldersDoNotSetTheCorrectPermissions[$cachePathTemp] = array(
				'permissions' => array('readable','writable')
			);
			//$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
		}
		
		
		
		$folderPathTemp = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN;
		if(
			$folderPathTemp
			&& file_exists($folderPathTemp)
			&& PepVN_Data::isAllowReadAndWrite($folderPathTemp)
		) {
			
		} else {
			$folderPathTemp = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH;
			if(
				$folderPathTemp
				&& file_exists($folderPathTemp)
				&& PepVN_Data::isAllowReadAndWrite($folderPathTemp)
			) {
				$folderPathTemp1 = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN;
				if(
					$folderPathTemp1
					&& file_exists($folderPathTemp1)
					&& PepVN_Data::isAllowReadAndWrite($folderPathTemp1)
				) {
				} else {
					PepVN_Data::createFolder($folderPathTemp1, WPOPTIMIZEBYXTRAFFIC_CHMOD);
				}
				
				if(
					$folderPathTemp1
					&& file_exists($folderPathTemp1)
					&& PepVN_Data::isAllowReadAndWrite($folderPathTemp1)
				) {
					
				} else {
					$arrayFilesFoldersDoNotSetTheCorrectPermissions[$folderPathTemp1] = array(
						'permissions' => array('readable','writable')
					);
				}
				
				
			} else {
				$arrayFilesFoldersDoNotSetTheCorrectPermissions[$folderPathTemp] = array(
					'permissions' => array('readable','writable')
				);
				//$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$folderPathTemp.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			}
		}
		
		
		if(!function_exists('mb_strlen')) {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server need support "Multibyte String" to use this plugin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' - <a href="http://php.net/manual/en/mbstring.installation.php" target="_blank"><b>'.__('Read more here',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b></a></div>';
		}
		
		if(
			!function_exists('gzcompress') 
			|| !function_exists('gzuncompress') 
		) {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server need support "Zlib Compression" to use this plugin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' - <a href="http://php.net/manual/en/book.zlib.php" target="_blank"><b>'.__('Read more here',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b></a></div>';
		}
		
		foreach($arrayFilesFoldersDoNotSetTheCorrectPermissions as $keyOne => $valueOne) {
			$errorTemp = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' ';
			
			$permissionsTemp = array();
			if(in_array('readable', $valueOne['permissions'])) {
				$permissionsTemp[] = '<u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u>';
			}
			if(in_array('writable', $valueOne['permissions'])) {
				$permissionsTemp[] = '<u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u>';
			}
			$errorTemp .= implode(' & ',$permissionsTemp) . ' ' .__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$keyOne.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			$resultData['notice']['error'][] = $errorTemp;
		}
		
		
		if($this->logFolderPath) {
			if(!file_exists($this->logFolderPath)) {
				PepVN_Data::createFolder($this->logFolderPath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			}
		}
		
		
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	
	public function get_device_screen_width()
	{
		return $this->advancedCacheObject->get_device_screen_width();
	}
	
	
	public function base_get_sponsorsblock($input_type='vertical_01')
	{
		$resultData = '';
		
		if('statistics' === $input_type) {
			
		} else if('vertical_01' === $input_type) {
			$resultData .= '<div id="sideblock" style=""><iframe width="auto" height="1000" frameborder="0" src="//static.pep.vn/library/pepvn/wp-optimize-by-xtraffic/client/vertical_01.html?utm_source='.rawurlencode($this->fullDomainName).'&utm_medium=plugin-wp-optimize-by-xtraffic-v-'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&utm_campaign=WP+Optimize+By+xTraffic"></iframe></div>';
		}
		
		return $resultData;
	}
	
	
	
	public function base_getABSPATH()
	{
		$path = ABSPATH;
		$siteUrl = $this->site_url();
		$homeUrl = $this->home_url();
		$diff = str_replace($homeUrl, "", $siteUrl);
		$diff = trim($diff,"/");

		$pos = strrpos($path, $diff);

		if($pos !== false){
			$path = substr_replace($path, "", $pos, strlen($diff));
			$path = trim($path,"/");
			$path = "/".$path."/";
		}
		
		return $path;
	}
	
	
	public function base_get_available_server_memory()
	{
		$availableServerMemoryBytes = 0;
		
		$memoryUsageBytes = @memory_get_usage(true);
		if($memoryUsageBytes) {
			$memoryUsageBytes = (int)$memoryUsageBytes;
			if($memoryUsageBytes > 0) {
				$serverInfo = $this->base_get_server_info(array('cache_status' => true));
				if($serverInfo['server_memory_limit_kb'] > 0) {
					$serverMemoryLimitBytes = $serverInfo['server_memory_limit_kb'] * 1024; $serverInfo = 0;
					$serverMemoryLimitBytes = (int)$serverMemoryLimitBytes;
					if($serverMemoryLimitBytes > 0) {
						$valueTemp = $serverMemoryLimitBytes - $memoryUsageBytes;
						$valueTemp = (int)$valueTemp;
						if($valueTemp > 0) {
							$availableServerMemoryBytes = $valueTemp;
						} else {
							$availableServerMemoryBytes = 1;
						}
					}
				}
			}
		}
		
		return $availableServerMemoryBytes;
	}
	
	
	
	public function base_get_time_spent()
	{
		$microtimeNow = microtime(true);
		$microtimeNow = (float)$microtimeNow;
		
		$microtimePluginStart = WPOPTIMIZEBYXTRAFFIC_PLUGIN_TIMESTART;
		$microtimePluginStart = (float)$microtimePluginStart;
		
		$secondsRan = abs($microtimeNow - $microtimePluginStart);
		$secondsRan = (float)$secondsRan;
		
		return $secondsRan;
	}
	
	public function base_get_available_server_time_for_run()
	{
		$availableServerTimeForRunInSeconds = 0;
		
		$server_max_execution_time_seconds = 0;
		
		$serverInfo = $this->base_get_server_info(array('cache_status' => true));
		if($serverInfo['server_max_execution_time_seconds'] > 0) {
			$serverInfo['server_max_execution_time_seconds'] = (float)$serverInfo['server_max_execution_time_seconds'];
			$server_max_execution_time_seconds = $serverInfo['server_max_execution_time_seconds'];
		}
		$serverInfo = 0;
		
		if($server_max_execution_time_seconds > 0) {
		
			$secondsRan = $this->base_get_time_spent();
			
			$availableServerTimeForRunInSeconds = $server_max_execution_time_seconds - $secondsRan;
			
			if($availableServerTimeForRunInSeconds < 1) {
				$availableServerTimeForRunInSeconds = 1;
			}
			
		}
		
		return $availableServerTimeForRunInSeconds;
	}
	
	
	
	public function base_get_safe_buffer_size_bytes_for_read_write_data() 
	{
		$resultData = array();
		
		$bufferSizeBytes = $this->bufferSizeBytesForReadAndWriteData;
		
		$availableServerMemoryBytes1 = $this->base_get_available_server_memory();
		if($availableServerMemoryBytes1 > 0) {
			$availableServerMemoryBytes1 = ceil($availableServerMemoryBytes1 * 0.5);
			if($availableServerMemoryBytes1 < $bufferSizeBytes) {
				$bufferSizeBytes = $availableServerMemoryBytes1;
			}
		}
		
		$bufferSizeBytes = (int)$bufferSizeBytes;
		
		return $bufferSizeBytes;
	}
	
	
	
	public function base_is_system_safe_to_continue_run() 
	{
		$resultData = true;
		
		
		if($resultData) {
			$timePluginStart = WPOPTIMIZEBYXTRAFFIC_PLUGIN_TIMESTART;
			$periodTimeRun = abs(microtime(true) - $timePluginStart);
			if($periodTimeRun > $this->systemSafeMaxExecutionTimeSeconds) {
				$resultData = false;
			}
			
		}
		
		
		/*
		$availableServerTimeForRunInSeconds = $this->base_get_available_server_time_for_run();
		if($availableServerTimeForRunInSeconds > 0) {
			$resultData = false;
			if($availableServerTimeForRunInSeconds > 5) {
				$resultData = true;
			}
		}
		*/
		
		if($resultData) {
			$availableServerMemoryBytes = $this->base_get_available_server_memory();
			if($availableServerMemoryBytes > 0) {
				$resultData = false;
				$bufferSizeBytes = $this->base_get_safe_buffer_size_bytes_for_read_write_data();
				if($availableServerMemoryBytes > ($bufferSizeBytes * 1.2)) {
					$resultData = true;
				}
			}
		}
		
		
		return $resultData;
	}
	

	
	public function base_get_server_info($input_parameters = false)
	{
		$resultData = false;
		
		$cacheStatus = false;
		
		if(isset($input_parameters['cache_status']) && ($input_parameters['cache_status'])) {
			$cacheStatus = true;
		}
		
		if($cacheStatus) {
			$keyCache1 = PepVN_Data::fKey(array(
				__METHOD__
			));
			
			$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		}
		
		if(!$resultData) {
			$resultData = array();
			
			$resultData['config_options'] = ini_get_all();
			
			$resultData['server_memory_limit_kb'] = 0;
			$resultData['server_max_execution_time_seconds'] = 0;
			
			if(
				isset($resultData['config_options']['memory_limit']['local_value'])
				&& ($resultData['config_options']['memory_limit']['local_value'])
			) {
				$resultData['server_memory_limit_kb'] = PepVN_Data::toNumber($resultData['config_options']['memory_limit']['local_value']);
				if($resultData['server_memory_limit_kb']>0) {
					if(false !== stripos($resultData['config_options']['memory_limit']['local_value'],'G')) {
						$resultData['server_memory_limit_kb'] = $resultData['server_memory_limit_kb'] * 1024 * 1024;
					} else if(false !== stripos($resultData['config_options']['memory_limit']['local_value'],'M')) {
						$resultData['server_memory_limit_kb'] = $resultData['server_memory_limit_kb'] * 1024;
					} else if(false !== stripos($resultData['config_options']['memory_limit']['local_value'],'K')) {
						$resultData['server_memory_limit_kb'] = $resultData['server_memory_limit_kb'] * 1;
					} else {
						$resultData['server_memory_limit_kb'] = $resultData['server_memory_limit_kb'] / 1024;
					}
				}
			}
			
			if(
				isset($resultData['config_options']['max_execution_time']['local_value'])
				&& ($resultData['config_options']['max_execution_time']['local_value'])
			) {
				$resultData['server_max_execution_time_seconds'] = PepVN_Data::toNumber($resultData['config_options']['max_execution_time']['local_value']);
			}
			
			$resultData['server_memory_limit_kb'] = (float)$resultData['server_memory_limit_kb'];
			$resultData['server_max_execution_time_seconds'] = (int)$resultData['server_max_execution_time_seconds'];
			
			if($cacheStatus) {
				PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			}
		}
		
		
		return $resultData;
	}
	
	
	public function base_clear_config_content($input_data)
	{
		$input_data = preg_replace('/[\s \t]+(\#\#\# BEGIN WPOPTIMIZEBYXTRAFFIC \#\#\#)/s', PHP_EOL . ' $1' ,$input_data);
		$input_data = preg_replace('/(\#\#\# END WPOPTIMIZEBYXTRAFFIC \#\#\#)[\s \t]+/s', '$1 ' . PHP_EOL ,$input_data);
		$input_data = preg_replace('/([\s \t]*?)\#\#\# BEGIN WPOPTIMIZEBYXTRAFFIC \#\#\#.+\#\#\# END WPOPTIMIZEBYXTRAFFIC \#\#\#([\s \t]*?)/s', PHP_EOL ,$input_data);
		
		return $input_data;
	}
	
	public function site_url()
	{
		$keyCache1 = 'base_site_url';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->baseCacheData[$keyCache1] = site_url();
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function home_url()
	{
		$keyCache1 = 'base_home_url';
		if(!isset($this->baseCacheData[$keyCache1])) {
			$this->baseCacheData[$keyCache1] = home_url();
		}
		return $this->baseCacheData[$keyCache1];
	}
	
	public function is_subdirectory_install()
	{
		$keyCache1 = 'base_is_subdirectory_install';
		if(!isset($this->baseCacheData[$keyCache1])) {
			if(strlen($this->site_url()) > strlen($this->home_url())){
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	
	
	public function base_get_folder_plus_path_for_cache()
	{
		$resultData = '';
		
		if(isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT']) {
			$pathRootWP = ABSPATH;
			$documentRoot = $_SERVER['DOCUMENT_ROOT'];
			$resultData = preg_replace('#^'.PepVN_Data::preg_quote($documentRoot).'#','',$pathRootWP,1);
			$resultData = preg_replace('#^/+#','',$resultData);
			$resultData = preg_replace('#/+$#','',$resultData);
		}
		
		
		return $resultData;
	}
	
	
	public function base_clear_files_config_content()
	{
		$rsOne = $this->base_get_all_files_config_content_available();
		foreach($rsOne as $key1 => $value1) {
			if($value1) {
				$fileConfigContent = @file_get_contents($value1);
				$fileConfigContent = $this->base_clear_config_content($fileConfigContent);
				@file_put_contents($value1,$fileConfigContent);
			}
		}
	}
	
	public function base_get_all_files_config_content()
	{
		$resultData = array();
		
		$pathRootWP = ABSPATH;
		
		if($this->is_subdirectory_install()){
			$pathRootWP = $this->base_getABSPATH();
		}
		
		$pathFileConfig = $pathRootWP.'.htaccess';
		$resultData[$pathFileConfig] = $pathFileConfig;
		
		$pathFileConfig = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH.'.htaccess';
		$resultData[$pathFileConfig] = $pathFileConfig;
		
		$pathFileConfig = $pathRootWP.'wp-settings.php';
		$resultData[$pathFileConfig] = $pathFileConfig;
		
		$pathFileConfig = $pathRootWP.'xtraffic-nginx.conf';
		$resultData[$pathFileConfig] = $pathFileConfig;
		
		return $resultData;
	}
	
	public function base_get_all_files_config_content_available()
	{
		$resultData = $this->base_get_all_files_config_content();
		foreach($resultData as $keyOne => $valueOne) {
			if($valueOne && file_exists($valueOne) && is_file($valueOne) && is_writable($valueOne)) {
			} else {
				unset($resultData[$keyOne]);
			}
		}
		
		return $resultData;
		
	}
	
	
	//do when plugin active once
	public function base_activate()
	{
		
		global $wpOptimizeSpeedByxTraffic;
		
		$this->base_clear_files_config_content();
		
		if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
			$wpOptimizeSpeedByxTraffic->base_activate();
		}
		
		$options = $this->get_options(array(
			'cache_status' => 0
		));
		
		$options['base_activate_timestamp'] = PepVN_Data::$defaultParams['requestTime'];
		
		$this->update_options($this->wpOptimizeByxTraffic_DB_option, $options);
		
		$this->enable_db_fulltext(array(
			'force_check_fulltext_status' => 1
		));
		
		$this->base_clear_data(',all,');
	}
	
	
	
	
	//do when plugin deactivate once
	public function base_deactivate()
	{
		global $wpOptimizeSpeedByxTraffic;
		
		$this->base_clear_files_config_content();
		
		if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
			$wpOptimizeSpeedByxTraffic->base_deactivate();
		}
		
		$this->base_clear_data(',all,');
		
	}
	
	public function base_get_current_user_id() 
	{
		if(false === $this->currentUserId) {
			$this->currentUserId = 0;
			
			$valueTemp = get_current_user_id();
			if($valueTemp) {
				$valueTemp = (int)$valueTemp;
				if($valueTemp > 0) {
					$this->currentUserId = $valueTemp;
				}
			}
			
			$this->currentUserId = (int)$this->currentUserId;
		}
		
		return $this->currentUserId;
		
	}
	
	
	
	
	public function base_wp_get_current_user() 
	{
	
		if(!$this->currentUserInfo) {
			
			$this->currentUserInfo = wp_get_current_user();
			
			if($this->currentUserInfo && isset($this->currentUserInfo->ID)) {
				$this->currentUserInfo->ID = (int)$this->currentUserInfo->ID;
				
				return $this->currentUserInfo;
			}
		}
		
		return $this->currentUserInfo;
		
	}
	
	
	
	public function base_is_mobile() 
	{
		
		if(!isset($this->baseCacheData['rs_base_is_mobile'])) {
			if(!$this->mobileDetectObject) {
				$this->mobileDetectObject = new PepVN_Mobile_Detect();
			}
			
			if ( $this->mobileDetectObject->isMobile() ) {
				$this->baseCacheData['rs_base_is_mobile'] = true;
			} else {
				$this->baseCacheData['rs_base_is_mobile'] = false;
			}
			
		}
		
		return $this->baseCacheData['rs_base_is_mobile'];
		
		
	}
	
	
	
	public function base_is_tablet() 
	{
		
		if(!isset($this->baseCacheData['rs_base_is_tablet'])) {
			
			if(!$this->mobileDetectObject) {
				$this->mobileDetectObject = new PepVN_Mobile_Detect();
			}
			
			if ( $this->mobileDetectObject->isTablet() ) {
				$this->baseCacheData['rs_base_is_tablet'] = true;
			} else {
				$this->baseCacheData['rs_base_is_tablet'] = false;
			}
			
		}
		
		return $this->baseCacheData['rs_base_is_tablet'];
		
		
	}
	
	
	public function base_is_has_memcache() 
	{
		$keyCache1 = '_b_i_h_mc';
		if(!isset($this->baseCacheData[$keyCache1])) {
			if(
				defined('MEMCACHE_COMPRESSED')
				&& class_exists('Memcache')
			) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
			
		}
		
		return $this->baseCacheData[$keyCache1];
		
	}
	
	public function base_is_has_apc_cache() 
	{
		$keyCache1 = '_b_i_h_apc_c';
		if(!isset($this->baseCacheData[$keyCache1])) {
			if(
				function_exists('apc_exists')
				&& function_exists('apc_fetch')
				&& function_exists('apc_store')
				&& function_exists('apc_delete') 
			) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
		
	}
	
	public function base_is_admin() 
	{
		$keyCache1 = '_base_is_admin';
		
		if(!isset($this->baseCacheData[$keyCache1])) {
		
			if ( is_admin() ) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
			
		}
		
		return $this->baseCacheData[$keyCache1];
		
	}
	
	public function base_is_current_user_logged_in_can($input_capability) 
	{
		$keyCache1 = '_i_c_ur_lg_cn-'.$input_capability;
		
		if(!isset($this->baseCacheData[$keyCache1])) {
			
			$this->baseCacheData[$keyCache1] = false;
			
			if ( is_user_logged_in() ) {
				if ( current_user_can($input_capability) ) {
					$this->baseCacheData[$keyCache1] = true;
				}
			}
			
		}
		
		return $this->baseCacheData[$keyCache1]; 
		
	}
	
	
	
	public function base_add_parameters_to_url($url = '',$params) 
	{
		$url = trim($url);
		if(!$url) {
			$url = $this->urlFullRequest;
		}
		
		
		return PepVN_Data::addParamsToUrl($url, $params);
		
		
	}
	
	public function base_get_plugin_info()
	{
		$resultData = array();
		
		$resultData['url'] = array();
		$resultData['url']['wp_plugin'] = 'http://wordpress.org/plugins/wp-optimize-by-xtraffic/';
		
		$resultData['intro'] = array();
		$resultData['intro'][0] = 'This website has been optimized by plugin "'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'".';
		$resultData['intro'][1] = 'Learn more here : '.$resultData['url']['wp_plugin'].' ';
		
		
		$resultData['html']['intro'][0] = 'This website has been optimized by plugin "<u><em><strong><a href="'.$resultData['url']['wp_plugin'].'" target="_blank">'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</a></strong></em></u>".';
		$resultData['html']['intro'][1] = 'Learn more here : <u><em><strong><a href="'.$resultData['url']['wp_plugin'].'" target="_blank">'.$resultData['url']['wp_plugin'].'</a></strong></em></u>';
		
		return $resultData;
	}
	
	public function base_add_plugin_info_html($text)
	{
		$rsGetPluginInfo = $this->base_get_plugin_info();
		
		$textAppendToEndBodyTagHtml = PHP_EOL . '<!-- '
		. PHP_EOL . '+ ' . $rsGetPluginInfo['intro'][0]
		. PHP_EOL . '+ Served from : '.$this->fullDomainName.' @ ' . date('Y-m-d H:i:s') . ' by "'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'".'
		//. PHP_EOL . '+ Page Caching using disk.'
		//. PHP_EOL . '+ Processing time before use cache : '.number_format(microtime(true) - WPOPTIMIZEBYXTRAFFIC_PLUGIN_TIMESTART, 10, '.', '').' seconds.'
		. PHP_EOL . '+ ' . $rsGetPluginInfo['intro'][1]
		. PHP_EOL . ' -->';
		
		$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToEndBodyTagHtml,$text); 
		
		$text = trim($text);
		
		return $text;
		
	}
	
	
	
	public function base_get_html_template($input_parameters) 
	{
		$resultData = '';
		
		if(isset($input_parameters['template_name']) && $input_parameters['template_name']) {
			$templatePath = WPOPTIMIZEBYXTRAFFIC_PATH.'inc/template/'.$input_parameters['template_name'];
			if(file_exists($templatePath)) {
				$resultData = @file_get_contents($templatePath);
			}
		}
		
		return $resultData;
	}
	
	public function base_parse_html_template($input_parameters) 
	{
		if(isset($input_parameters['patterns']) && $input_parameters['patterns']) {
			if(isset($input_parameters['content']) && $input_parameters['content']) {
				$input_parameters['content'] = (string)$input_parameters['content'];
				$input_parameters['content'] = trim($input_parameters['content']);
				$input_parameters['patterns'] = (array)$input_parameters['patterns'];
				$input_parameters['content'] = str_replace(array_keys($input_parameters['patterns']), array_values($input_parameters['patterns']), $input_parameters['content']);
				$input_parameters['content'] = preg_replace('/#[a-z0-9\-\_]+#/i','',$input_parameters['content']);
				$input_parameters['content'] = pepvn_MinifyHtml($input_parameters['content']);
				return $input_parameters['content'];
			}
		}
		
		return '';
	}
	
	public function base_get_parse_index_html_template($input_patterns) 
	{
		$resultData = $this->base_get_html_template(array('template_name' => 'index.html'));
		$resultData = $this->base_parse_html_template(array(
			'content' => $resultData
			,'patterns' => $input_patterns
		));
		
		return $resultData;
	}
	
	
	
	
	public function base_get_parse_html_template($input_parameters) 
	{
		$resultData = '';
		
		if(
			isset($input_parameters['template_name'])
			&& ($input_parameters['template_name'])
			
			&& isset($input_parameters['patterns'])
			&& ($input_parameters['patterns'])
		) {
			$resultData = $this->base_get_html_template(array('template_name' => $input_parameters['template_name']));
			$resultData = $this->base_parse_html_template(array(
				'content' => $resultData
				,'patterns' => $input_parameters['patterns']
			));
		}
		
		return $resultData;
	}
	
	
	
	public function base_put_file_index($input_parameters) 
	{
		if(
			isset($input_parameters['path']) 
			&& ($input_parameters['path'])
		) {
			$inputFolderPath = PepVN_Data::getFolderPath($input_parameters['path']);
			if($inputFolderPath) {
				if(PepVN_Data::isAllowReadAndWrite($inputFolderPath)) {
					$rsGetPluginInfo = $this->base_get_plugin_info();
					
					$htmlPatterns = array();
					$htmlPatterns['#xtr_head_title#'] = $rsGetPluginInfo['intro'][0];
					$htmlPatterns['#xtr_head_description#'] = $rsGetPluginInfo['intro'][0].$rsGetPluginInfo['intro'][1];
					$htmlPatterns['#xtr_body#'] = '<h1>'.$rsGetPluginInfo['intro'][0].'</h1><p>'.$rsGetPluginInfo['html']['intro'][1].'</p>';
					
					if(
						isset($input_parameters['redirect']) 
						&& ($input_parameters['redirect'])
					) {
						if('home' === $input_parameters['redirect']) {
							
							$htmlPatterns['#xtr_body_javascript#'] = 'setTimeout(function() {window.location = "'.$this->currentSiteUrl.'";}, 6000);';
							$htmlPatterns['#xtr_head_plus#'] = '<meta http-equiv="refresh" content="6; url='.$this->currentSiteUrl.'" />';
						}
					}
					
					$htmlContent = $this->base_get_parse_index_html_template($htmlPatterns);
					
					$arrayFilesName = $this->enumIndexFileName;
					
					foreach($arrayFilesName as $valueOne) {
						@file_put_contents($inputFolderPath.'/'.$valueOne,$htmlContent);
					}
					
				}
			}
			
		}
	}
	
	
	
	public function base_protect_folder($input_parameters)
	{
		if(
			isset($input_parameters['path']) 
			&& ($input_parameters['path'])
		) {
			$input_parameters['path'] = (array)$input_parameters['path'];
			$input_parameters['path'] = PepVN_Data::cleanArray($input_parameters['path']);
			foreach($input_parameters['path'] as $valueOne) {
				$valueOne = trim($valueOne);
				if($valueOne) {
					$folderPath1 = PepVN_Data::getFolderPath($valueOne);
					if($folderPath1) {
						if(PepVN_Data::isAllowReadAndWrite($folderPath1)) {
							$this->base_put_file_index(array(
								'path' => $valueOne
								,'redirect' => 'home'
							));
							$valueTemp1 = $folderPath1.'/'.'.htaccess';
							if(!file_exists($valueTemp1)) {
								@file_put_contents($valueTemp1,'deny from all');
							}
						}
					}
				}
			}
		}
	}
	
	public function base_gmdate_gmt($input_timestamp)
	{
		return PepVN_Data::gmdate_gmt($input_timestamp);
		
	}
	
	public function base_get_list_folders_files_local_host($input_path, $input_options = false) 
	{
		$resultData = false;
		
		if(!$input_options) {
			$input_options = array();
		}
		
		if(!isset($input_options['cache_status'])) {
			$input_options['cache_status'] = false;
		}
		
		if($input_options['cache_status']) {
			$keyCache1 = PepVN_Data::fKey(array(
				__METHOD__
				, $input_path
				, $input_options
			));
			
			$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
			
		}
		
		if(!$resultData) {
			$resultData = array();
			
			$stepId = PepVN_Data::mcrc32(PepVN_Data::randomHash());
			
			
			$this->baseObjects[$stepId] = array();
			
			if(isset($input_options['exclude_pattern']) && $input_options['exclude_pattern']) {
				$this->baseObjects[$stepId]['options']['exclude_pattern'] = $input_options['exclude_pattern'];
			}
			
			$this->baseObjects[$stepId]['get_list_folders_files'] = array();
			
			$this->baseObjects[$stepId]['get_list_folders_files']['list'] = array();
			
			$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['number'] = 0;
			$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['size'] = 0;
			
			$this->baseObjects[$stepId]['get_list_folders_files']['sum']['dir']['number'] = 0;
			
			$this->_get_list_folders_files($input_path, array(
				'step_id' => $stepId
			));
			
			$resultData = $this->baseObjects[$stepId]['get_list_folders_files'];
			
			$this->baseObjects[$stepId] = 0;
			
			if($input_options['cache_status']) {
				PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			}
		}
		
		return $resultData;
	}
	
	
	private function _get_list_folders_files($input_path, $input_options) 
	{
		
		if($input_path) {
			
			$stepId = $input_options['step_id'];
			
			$checkStatus1 = true;
			
			if(
				isset($this->baseObjects[$stepId]['options']['exclude_pattern']) 
				&& $this->baseObjects[$stepId]['options']['exclude_pattern']
				&& preg_match($this->baseObjects[$stepId]['options']['exclude_pattern'],$input_path)
			) {
				$checkStatus1 = false;
			}
			
			if($checkStatus1) {
			
				if(file_exists($input_path)) {
					
					$filetype_input_path = filetype($input_path); 
					
					if ($filetype_input_path === 'dir') {
						
						$path1 = $input_path . DIRECTORY_SEPARATOR;
						$this->baseObjects[$stepId]['get_list_folders_files']['list'][$path1] = array(
							't' => 'd'
							,'s' => 0
						);
						$this->baseObjects[$stepId]['get_list_folders_files']['sum']['dir']['number']++;
						
						
						$objects = scandir($input_path);
						foreach ($objects as $object) {
							if (($object !== '.') && ($object !== '..')) {
								$object_path = $input_path. DIRECTORY_SEPARATOR .$object;
								
								$checkStatus2 = true;
			
								if(
									isset($this->baseObjects[$stepId]['options']['exclude_pattern']) 
									&& $this->baseObjects[$stepId]['options']['exclude_pattern']
									&& preg_match($this->baseObjects[$stepId]['options']['exclude_pattern'],$object_path)
								) {
									$checkStatus2 = false;
								}
								
								if($checkStatus2) {
								
									$filetype_object = filetype($object_path);
									if ($filetype_object === 'dir') {
										$this->_get_list_folders_files($object_path, $input_options);
									} elseif ($filetype_object === 'file') {
										$fileSize1 = filesize($object_path);
										$fileSize1 = (int)$fileSize1;
										
										$filemtime1 = filemtime($object_path);
										$filemtime1 = (int)$filemtime1;
						
										$this->baseObjects[$stepId]['get_list_folders_files']['list'][$object_path] = array(
											't' => 'f'
											,'s' => $fileSize1
											,'mt' => $filemtime1
										);
										
										$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['number']++;
										$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['size'] += $fileSize1;
									}
									
								}
							}
						}
						
					} else if ($filetype_input_path === 'file') {
						
						$fileSize1 = filesize($input_path);
						$fileSize1 = (int)$fileSize1;
						
						$filemtime1 = filemtime($input_path);
						$filemtime1 = (int)$filemtime1;
						
						$this->baseObjects[$stepId]['get_list_folders_files']['list'][$input_path] = array(
							't' => 'f'
							,'s' => $fileSize1
							,'mt' => $filemtime1
						);
						
						$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['number']++;
						$this->baseObjects[$stepId]['get_list_folders_files']['sum']['file']['size'] += $fileSize1;
						
						
					}
				}
			}
		}
		
	}
	
	public function base_get_categories($input_term_id = 0)
	{
	
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,$input_term_id
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		if(!$resultData) {
			$resultData = array();
			
			$categories = get_categories();
			if ($categories) {
				foreach($categories as $category) {
					if($category) {
						if(isset($category->term_id) && $category->term_id) {
							$category->term_id = (int)$category->term_id;
							$resultData[$category->name] = array(
								'name' => $category->name
								,'term_id' => $category->term_id
								,'link' => get_category_link($category->term_id)
							);
							
						}
					}
				}
			}
			
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	
	
	
	public function base_get_tags($input_term_id = 0)
	{
	
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,$input_term_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		if(!$resultData) {
			$resultData = array();
			
			$tags = get_tags();
			if ($tags) {
				foreach($tags as $tag) {
					if($tag) {
						if(isset($tag->term_id) && $tag->term_id) {
							$tag->term_id = (int)$tag->term_id;
							$resultData[$tag->name] = array(
								'name' => $tag->name
								,'term_id' => $tag->term_id
								,'link' => get_tag_link($tag->term_id)
							);
							
						}
					}
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	
	
	
	
	
	
	
	
	public function base_get_all_available_taxonomies()
	{
		$args = array(
		  'public'   => true
		);
		
		$output = 'objects'; // or objects
		$operator = 'and'; // 'and' or 'or'
		
		return $this->base_get_taxonomies( $args, $output, $operator ); 
		
	}
	
	public function base_get_taxonomies($args, $output, $operator)
	{
		
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,$args
			,$output
			,$operator
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		if(!$resultData) {
			$resultData = array();
			
			if(function_exists('get_taxonomies')) {
				$resultData = get_taxonomies( $args, $output, $operator );
				if(count($resultData) > 0) {
				} else {
					$resultData = array();
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
		}
		
		return $resultData;
	}
	
	public function base_get_terms_by_post_id($input_post_id)
	{
		$input_post_id = (int)$input_post_id;
		
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,$input_post_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		if(!$resultData) {
			$resultData = array();
			
			$groupsTerms = array();
			
			if($input_post_id > 0) {
				
				$groupsTerms['tags'] = get_the_tags((int)$input_post_id);
				$groupsTerms['category'] = get_the_category((int)$input_post_id);
				foreach($groupsTerms as $keyOne => $valueOne) {
					if ($valueOne) {
						foreach($valueOne as $valueTwo) {
							if($valueTwo) {
								if ($valueTwo && (!is_wp_error($valueTwo))) {
								
									if(isset($valueTwo->term_id) && $valueTwo->term_id) {
										$valueTwo->term_id = (int)$valueTwo->term_id;
										$linkTerm = '';
										if('tags' === $keyOne) {
											$linkTerm = get_tag_link($valueTwo->term_id);
										} else if('category' === $keyOne) {
											$linkTerm = get_category_link($valueTwo->term_id);
										}
										
										
										$rsTermData = array(
											'name' => $valueTwo->name
											,'term_id' => $valueTwo->term_id
											,'link' => $linkTerm
											,'slug' => ''
											,'xtr_term_type' => $keyOne
										);
										
										
										
										if(isset($valueTwo->slug)) {
											$rsTermData['slug'] = $valueTwo->slug;
										}
										
										$resultData[] = $rsTermData;
										
										
									}
									
								}
							}
						}
					}
				}
				
				$groupsTerms = false;
				
				$rsGetAllAvailableTaxonomies =  $this->base_get_all_available_taxonomies();
				
				$arrayTaxonomiesNameExclude = array(
					'category'
					,'post_tag'
				);
				
				foreach($rsGetAllAvailableTaxonomies as $valueOne) {
					if($valueOne) {
						if(isset($valueOne->name) && $valueOne->name) {
							if(!in_array($valueOne->name, $arrayTaxonomiesNameExclude)) {
								$rsGetTheTerms = get_the_terms($input_post_id,$valueOne->name);
								if($rsGetTheTerms) {
									if(is_array($rsGetTheTerms) && (count($rsGetTheTerms) > 0)) {
										foreach($rsGetTheTerms as $valueTwo) {
											if($valueTwo) {
												if(isset($valueTwo->name) && $valueTwo->name) {
													
													$rsTermData = array(
														'name' => $valueTwo->name
														,'term_id' => $valueTwo->term_id
														,'link' => ''
														,'slug' => ''
														,'xtr_term_type' => $valueOne->name
													);
													
													if(isset($valueTwo->slug)) {
														$rsTermData['slug'] = $valueTwo->slug;
													}
													
													$resultData[] = $rsTermData;
												}
											}
										}
									}
								}
							}
							
						}
					}
				}
				
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
		}
		
		return $resultData;
	}
	
	public function explode_trim($separator, $text)
	{
		$arr = explode($separator, $text);
		
		$ret = array();
		foreach($arr as $e) {
		  $ret[] = trim($e);        
		}
		return $ret;
	}
	
	public function base_set_cache_all_options()
	{
		//set options
		PepVN_Data::$cacheObject->set_cache(WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY, $this->get_options(array(
			'cache_status' => 1
		)));
		
		global $wpOptimizeSpeedByxTraffic;
		
		if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
			$wpOptimizeSpeedByxTraffic->set_cache_options();
		}
		
	}
	
	public function base_do_before_wp_shutdown()
	{
		
		$this->advancedCacheObject->statistic_access_urls_sites(array(
			'calculate_time_php_process_status' => true
		));
		
		
		$this->base_set_cache_all_options();
		
	}
	
	
	
	public function base_get_clean_raw_text_for_process_search($input_text)
	{
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,$input_text
		));
		
		$resultData = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);
		if(null !== $resultData) {
			return $resultData;
		}
		
		$input_text = (array)$input_text;
		$input_text = implode(' ',$input_text);
		$input_text = PepVN_Data::decodeText($input_text);
		$input_text = strip_tags($input_text);
		$input_text = PepVN_Data::strtolower($input_text);
		$input_text = PepVN_Data::analysisKeyword_RemovePunctuations($input_text);
		$input_text = PepVN_Data::replaceSpecialChar($input_text);
		$input_text = PepVN_Data::reduceSpace($input_text);
		
		PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $resultData);
		
		return $input_text;
	}
	
	
	
	public function base_get_results_by_query($input_query, $input_options = false) 
	{
		global $wpdb;
		
		$resultData = false;
		
		if(!$input_options) {
			$input_options = array();
		}
		$input_options = (array)$input_options;
		
		$keyCache1 = false;
		
		if(isset($input_options['cache_status']) && $input_options['cache_status']) {
			$keyCache1 = PepVN_Data::fKey(array(
				__METHOD__
				,$input_query
			));
			
			$resultData = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);
			
			if($resultData) {
				return $resultData;
			}
		}
		
		$resultData = $wpdb->get_results($input_query);
		if($resultData) {
			if($keyCache1) {
				PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $resultData);
			}
		}
		
		return $resultData;
	}
	
	
	public function base_search_posts_by_fulltext($input_parameters) 
	{
		global $wpdb;
		
		$resultData = array();
			
		if(isset($input_parameters['post_types']) && $input_parameters['post_types']) {
			
		} else {
			$input_parameters['post_types'] = array(
				'post'
				,'page'
			);
		}
		
		
		$input_parameters['post_types'] = PepVN_Data::cleanArray($input_parameters['post_types']);
		
		if($input_parameters['post_types'] && (!empty($input_parameters['post_types']))) {
		
		} else {
			return $resultData;
		}
		
		/*
			input_parameters['keywords'] = array(
				keyword a => (float)w
			);
		*/
		
		$keywords = array();
		
		$valueTemp = (array)$input_parameters['keywords'];
		
		foreach($valueTemp as $key1 => $value1) {
			$key1 = preg_replace('#[\'\"\\\]+#i',' ',$key1);
			$key1 = preg_replace('#[\s]+#is',' ',$key1);
			$key1 = $this->base_get_clean_raw_text_for_process_search($key1);
			$key1 = trim($key1);
			if($key1) {
				$keywords[$key1] = (float)$value1;
			}
		}
		
		if($keywords && !empty($keywords)) {
			
		} else {
			return $resultData;
		}
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$keywords = array_slice($keywords,0,30);
		$input_parameters['keywords'] = $keywords;
		
		if(!isset($input_parameters['limit'])) {
			$input_parameters['limit'] = 10; 
		}
		$input_parameters['limit'] = (int)$input_parameters['limit'];
		
		if(!isset($input_parameters['exclude_posts_ids'])) {
			$input_parameters['exclude_posts_ids'] = array();
		}
		$input_parameters['exclude_posts_ids'] = (array)$input_parameters['exclude_posts_ids'];
		foreach($input_parameters['exclude_posts_ids'] as $key1 => $value1) {
			$input_parameters['exclude_posts_ids'][$key1] = (int)$value1;
		}
		$input_parameters['exclude_posts_ids'] = array_unique($input_parameters['exclude_posts_ids']);
		arsort($input_parameters['exclude_posts_ids']);
		$input_parameters['exclude_posts_ids'] = array_values($input_parameters['exclude_posts_ids']);
		
		$keyCache1 = array(
			__METHOD__
			,$input_parameters
			,$wpdb->posts
		);
		
		if(isset($options['db_has_fulltext_status']) && ($options['db_has_fulltext_status'])) {
			$keyCache1[] = 'db_has_fulltext_status';
		}
		
		$keyCache1 = PepVN_Data::fKey($keyCache1);
		
		$resultData = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$combinedKeywords = array(
				'kw' => ''
				,'w' => 0
			);
			foreach($input_parameters['keywords'] as $key1 => $value1) {
				$combinedKeywords['kw'] .= ' '.$key1;
				$combinedKeywords['w'] += $value1;
			}
			
			$combinedKeywords['kw'] = trim($combinedKeywords['kw']);
			if($combinedKeywords['kw']) {
				$combinedKeywords['w'] = $combinedKeywords['w'] / count($input_parameters['keywords']);
				$input_parameters['keywords'][$combinedKeywords['kw']] = $combinedKeywords['w'];
			}
			
			$queryString_Where_PostType = array();
			
			foreach($input_parameters['post_types'] as $keyOne => $valueOne) {
				if($valueOne) {
					$valueOne = trim($valueOne);
					if($valueOne) {
						$queryString_Where_PostType[] = ' ( post_type = \''.$valueOne.'\' ) ';
					}
				}
				
			}
			
			$queryString_Where_PostType = implode(' OR ',$queryString_Where_PostType);
			$queryString_Where_PostType = trim($queryString_Where_PostType);
			
			$totalWeight = 1;
			
			$queryString_MatchAgainstKeywords = array();
			$queryString_LikeKeywords = array();
			
			$queryString1 = 
'
SELECT ID , post_title , post_content 
'; 
	
			if(isset($options['db_has_fulltext_status']) && ($options['db_has_fulltext_status'])) {
				
				foreach($input_parameters['keywords'] as $key1 => $value1) {
					
					$queryString_MatchAgainstKeywords[] = '
						(
							(
								( ( MATCH(post_title) AGAINST(\''.$key1.'\' IN NATURAL LANGUAGE MODE) ) * 3 )
								+ ( ( MATCH(post_excerpt) AGAINST(\''.$key1.'\' IN NATURAL LANGUAGE MODE) ) * 2 )
								+ ( ( MATCH(post_content) AGAINST(\''.$key1.'\' IN NATURAL LANGUAGE MODE) ) * 10 )
								+ ( ( MATCH(post_name) AGAINST(\''.$key1.'\' IN NATURAL LANGUAGE MODE) ) * 2 )
							) * '.(float)$value1.'
						)
					';
					
					
					$totalWeight += $value1;
				}
				
				$totalWeight = (float)$totalWeight;
			
				$queryString1 .= ', (
	(
		'.implode(' + ',$queryString_MatchAgainstKeywords).'
	)
) AS wpxtraffic_score ';

			} else {
				
				foreach($input_parameters['keywords'] as $key1 => $value1) {
					
					$queryString_LikeKeywords[] = 
'
	(
		post_title LIKE \'%'.$key1.'%\'
	)
';
					
				}
				
				$queryString1 .= ', ID AS wpxtraffic_score ';
				
			}
			
			
			$queryString1 .= 
'
FROM '.$wpdb->posts.' 
WHERE ( ( post_status = \'publish\') AND ( post_password = \'\') 
'; 
		
			if(count($queryString_LikeKeywords)>0) {
				$queryString1 .= ' AND ( '.implode(' OR ',$queryString_LikeKeywords).' ) ';
				
			}
			
			if($queryString_Where_PostType) {
				$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) '; 
			}
			
			if(count($input_parameters['exclude_posts_ids'])>0) {
				$queryString1 .= ' AND ( '.$wpdb->posts.'.ID NOT IN ('.implode(',',$input_parameters['exclude_posts_ids']).') ) '; 
			}
			
			$queryString1 .= ' ) ';
			
			$queryString1 .= ' 
ORDER BY wpxtraffic_score DESC 
LIMIT 0,'.($input_parameters['limit']).' 
';
			
			$rsOne = $this->base_get_results_by_query($queryString1, array(
				'cache_status' => 1
			));
			
			
			if($rsOne) {
				foreach($rsOne as $keyOne => $valueOne) {
					if($valueOne) {
						if(isset($valueOne->wpxtraffic_score)) {
							$valueOne->wpxtraffic_score = (float)$valueOne->wpxtraffic_score / $totalWeight;
							if($valueOne->wpxtraffic_score >= 1) {
								$postId = (int)$valueOne->ID;
								
								if($postId) {
									
									$foundKeywordStatus = false;
									
									
									$valueTemp1 = $valueOne->post_title;
									$valueTemp1 = $this->base_get_clean_raw_text_for_process_search($valueTemp1);
									foreach($input_parameters['keywords'] as $key1 => $value1) {
										if(false !== stripos($valueTemp1, $key1)) {
											$foundKeywordStatus = true;
											break;
										}
									}
									
									if(!$foundKeywordStatus) {
										$valueTemp1 = $valueOne->post_content;
										$valueTemp1 = PepVN_Data::mb_substr($valueTemp1, 0 ,500);
										$valueTemp1 = $this->base_get_clean_raw_text_for_process_search($valueTemp1);
										foreach($input_parameters['keywords'] as $key1 => $value1) {
											if(false !== stripos($valueTemp1, $key1)) {
												$foundKeywordStatus = true;
												break;
											}
										}
									}
									
									
									if($foundKeywordStatus) {
									
										$postLink = get_permalink( $postId, false );
										if($postLink) {
											
											$postLink = trailingslashit($postLink);
											
											$resultData[$postId] = array(
												'post_id' => $postId,
												'post_title' => $valueOne->post_title,
												'post_link' => $postLink,
												'wpxtraffic_score' => $valueOne->wpxtraffic_score
												
											);
										}
										
									}
								}
								
								
							}
							
						}
					}
				}
			}
			
			PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $resultData);
		}
		
		return $resultData;
		
	}
	
	
	// Handle our options
	public function get_options($input_parameters = false) 
	{
		
		if(!$input_parameters) {
			$input_parameters = array();
		}
		
		if(!isset($input_parameters['create_default_options_status'])) {
			$input_parameters['create_default_options_status'] = true;
		}
		
		if(!isset($input_parameters['save_options_when_different_status'])) {
			$input_parameters['save_options_when_different_status'] = true;
		}
		
		if(!isset($input_parameters['options_id'])) {
			$input_parameters['options_id'] = $this->wpOptimizeByxTraffic_DB_option;
		}
		
		$input_parameters['options_id'] = (string)$input_parameters['options_id'];
		
		
		if(!isset($input_parameters['cache_status'])) {
			$input_parameters['cache_status'] = false;
		}
		
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			, $input_parameters
		));
		
		if(isset($input_parameters['cache_status']) && $input_parameters['cache_status']) {
			
			if(isset($this->baseCacheData[$keyCache1]) && $this->baseCacheData[$keyCache1]) {
				return $this->baseCacheData[$keyCache1];
			}
			
		}
		
		$options = array();
		
		if($input_parameters['create_default_options_status']) {
		
			$rs_parse_url = parse_url(get_bloginfo('wpurl'));
			
			
			$options = array(
				
				
				
				/*
				* Dashboard General Settings
				*/
				
				'base_custom_post_types' => '',
				'base_custom_taxonomies' => '',
				
				
				
				
				
				
				
				/*
				* Optimize Links Setting
				*/
				
				'optimize_links_enable' => '', //on
				
				'optimize_links_process_in_post' => 'on',
				'optimize_links_allow_link_to_postself' => '',
				'optimize_links_process_in_page' => 'on',
				'optimize_links_allow_link_to_pageself' => '',
				'optimize_links_process_in_comment' => '',
				'optimize_links_excludeheading' => '',//on
				'optimize_links_link_to_posts' => 'on',//on
				'optimize_links_link_to_pages' => 'on',//on
				'optimize_links_link_to_cats' => 'on', 
				'optimize_links_link_to_tags' => '', 
				//'optimize_links_ignore' => 'about,', 
				'optimize_links_ignorepost' => 'contact', 
				'optimize_links_maxlinks' => 3,
				'optimize_links_maxsingle' => 1,
				'optimize_links_minusage' => 0,
				'optimize_links_customkey' => '',
				'optimize_links_customkey_preventduplicatelink' => FALSE,
				'optimize_links_customkey_url' => '',
				'optimize_links_customkey_url_value' => '',
				'optimize_links_customkey_url_datetime' => '',
				'optimize_links_nofoln' =>'',
				'optimize_links_nofolo' =>'',
				//'optimize_links_blankn' =>'',
				'optimize_links_blanko' =>'',
				'optimize_links_onlysingle' => 'on',
				'optimize_links_open_autolink_new_window' => 'on',
				'optimize_links_casesens' =>'',
				'optimize_links_process_in_feed' => '',
				'optimize_links_maxsingleurl' => '1',
				
				'optimize_links_use_cats_as_keywords' => 'on',
				'optimize_links_use_tags_as_keywords' => 'on',
				'optimize_links_nofollow_urls' => '',
				'optimize_links_nofolo_blanko_exclude_urls' => '',
				
				'optimize_links_notice'=>'1',
				
				
				
				
				/*
				* Optimize Images Setting
				*/
				
				'optimize_images_alttext' => '',//%img_name %title
				'optimize_images_titletext' => '',
				
				'optimize_images_override_alt' => '',//on
				'optimize_images_override_title' => '',//on
				
				
				'optimize_images_optimize_image_file_enable' => '',//on
				
				'optimize_images_auto_resize_images_enable' => '',//on
				
				
				//image lazy load
				'optimize_images_images_lazy_load_enable' => '',//on
				'optimize_images_images_lazy_load_frontpage_enable' => 'on',//on 
				
				
				//watermark image
				'optimize_images_watermarks_enable' => '',//on
				'optimize_images_watermarks_watermark_position' => 'bottom_right',
				
				'optimize_images_watermarks_watermark_opacity_value' => 100,
				'optimize_images_watermarks_watermark_type' => 'text',
				'optimize_images_file_minimum_width_height' => 150,	//x pixel
				'optimize_images_file_maximum_width_height' => 0,		//x pixel
				'optimize_images_watermarks_watermark_text_value' => $rs_parse_url['host'],
				
				'optimize_images_watermarks_watermark_text_font_name' => 'arial',
				'optimize_images_watermarks_watermark_text_size' => '20%',
				'optimize_images_watermarks_watermark_text_color' => 'ffffff',
				'optimize_images_watermarks_watermark_text_margin_x' => 10,
				'optimize_images_watermarks_watermark_text_margin_y' => 10,
				'optimize_images_watermarks_watermark_text_opacity_value' => 100,
				'optimize_images_watermarks_watermark_text_background_enable' => 'on',
				'optimize_images_watermarks_watermark_text_background_color' => '222222',
				'optimize_images_watermarks_watermark_text_background_opacity_value' => 100,
				
				'optimize_images_watermarks_watermark_text_outline_enable' => '',
				'optimize_images_watermarks_watermark_text_outline_color' => 'ffffff',
				'optimize_images_watermarks_watermark_text_outline_width' => 1,
				
				'optimize_images_watermarks_watermark_image_url' => '',
				'optimize_images_watermarks_watermark_image_width' => '',
				'optimize_images_watermarks_watermark_image_margin_x' => 10,
				'optimize_images_watermarks_watermark_image_margin_y' => 10,
				
				
				'optimize_images_image_quality_value' => 100,
				'optimize_images_rename_img_filename_value' => '',
				'optimize_images_maximum_files_handled_each_request' => 1,
				'optimize_images_handle_again_files_different_configuration_enable' => '',//on
				'optimize_images_remove_files_available_different_configuration_enable' => 'on',//on
				
				
				
				/*
				* Optimize Databases Setting
				*/
				'optimize_databases_keep_last_data_enable' => 'on',//on
				'optimize_databases_keep_last_data_days' => 15,//interger
				'optimize_databases_actions_enable' => '',//array
				'optimize_databases_scheduled_actions_enable' => '',//on
				'optimize_databases_scheduled_actions_days' => 7,//interger
				
				
				
				/*
				* Optimize Security Setting
				*/
				
				//optimize_captcha
				'optimize_security_captcha_enable_for' => '',//on
				'optimize_security_captcha_disable_for' => '',//on
				'optimize_security_captcha_disable_for_users_roles' => '',//on
				'optimize_security_captcha_charset' => 'ABCEFGHKLMNPRTWY',//string
				'optimize_security_captcha_charlength' => 4,//interger
				'optimize_security_captcha_image_width' => 175,//interger - pixel
				'optimize_security_captcha_image_height' => 50,//interger - pixel
				
				'optimize_security_captcha_title_form' => __('Prove you\'re not a robot',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),//string
				'optimize_security_captcha_required_symbol' => '(*)',//string
				'optimize_security_captcha_title_input_form' => __('Type the captcha image above',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),//string
				'optimize_security_captcha_title_input_placeholder_form' => __('Enter captcha',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),//string
				'optimize_security_captcha_title_reload_image_captcha_form' => __('Click Here To Reload Captcha Image',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),//string
				'optimize_security_captcha_notice_when_user_not_enter_captcha' => PepVN_Captcha::$defaultParams['captcha']['notice']['error']['not_entered_captcha'],//string
				'optimize_security_captcha_notice_when_user_enter_wrong_captcha' => PepVN_Captcha::$defaultParams['captcha']['notice']['error']['wrong_captcha'],//string
				'optimize_security_captcha_notice_unknown_error' => PepVN_Captcha::$defaultParams['captcha']['notice']['error']['unknown'],//string
				
				
				
				/*
				* Optimize Backup Setting
				*/
				'optimize_backup_backup_enable_for' => array(),
				
				'optimize_backup_backup_enable_database_tables' => array(),
				
				'optimize_backup_backup_enable_folders_files_root_path' => $this->base_getABSPATH(),
				'optimize_backup_backup_enable_folders_files_include' => $this->base_getABSPATH(),
				'optimize_backup_backup_enable_folders_files_exclude' => '',
				
				'optimize_backup_backup_file_name' => 'backup_%Y-%m-%d_%H-%i-%s',
				
				'optimize_backup_enable_split_backup_file' => '',//on
				'optimize_backup_split_backup_file_size' => 20,//in MB
				
				'optimize_backup_enable_encrypt_backup_file' => '',//on
				'optimize_backup_encrypt_backup_file_data_password' => '',//string
				'optimize_backup_encrypt_backup_file_data_repassword' => '',//string
				
				'optimize_backup_backup_enable_storages' => array(),
				
				'optimize_backup_backup_enable_storages_to_folder_path' => WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'backup'.DIRECTORY_SEPARATOR,
				
				'optimize_backup_backup_enable_storages_to_email_address' => get_option('admin_email'),
				
				'optimize_backup_ftp_setup_data_server_address' => '',	//string
				'optimize_backup_ftp_setup_data_server_port' => '21',	//string
				'optimize_backup_ftp_setup_data_username' => '',	//string
				'optimize_backup_ftp_setup_data_password' => '',	//string
				'optimize_backup_ftp_setup_data_folder_store' => '',	//string
				'optimize_backup_ftp_setup_data_enable_secure' => '',//on
				'optimize_backup_ftp_setup_data_enable_passive_mode' => '',//on
				
				'optimize_backup_dropbox_setup_data_app_key' => '',	//string
				'optimize_backup_dropbox_setup_data_app_secret' => '',	//string
				
				
				'optimize_backup_googledrive_setup_data_client_id' => '',	//string
				'optimize_backup_googledrive_setup_data_client_secret' => '',	//string
				
				'optimize_backup_enable_backup_scheduled' => '',//on
				'optimize_backup_backup_scheduled_days' => 7,//interger - days
				
				'optimize_backup_maximum_number_backup_versions_retained' => 0,//interger - days
				
				'optimize_backup_enable_run_backup_scheduled_specified_time_each_day' => '',//on
				'optimize_backup_run_backup_scheduled_specified_time_each_day_from_to' => '1-3',//string 0-23
				
				
				
				
				
				
				/*
				* Optimize Email Setting
				*/
				
				'optimize_email_enable_advanced_email' => '',	//on
				
				'optimize_email_advanced_email_from_email' => '',	//string
				'optimize_email_advanced_email_from_name' => '',	//string
				
				'optimize_email_advanced_email_method' => '',	//string
				
				'optimize_email_advanced_email_smtp_server' => '',	//string or ipaddress
				'optimize_email_advanced_email_smtp_port' => '',	//interger
				'optimize_email_advanced_email_smtp_secure_type' => '',	//string
				'optimize_email_advanced_email_smtp_username' => '',	//string
				'optimize_email_advanced_email_smtp_password' => '',	//string
				
				
				
				
				
				
				
				
				
				
				
				
				/*
				* Header & Footer Setting
				*/
				
				'header_footer_code_add_head_home' => '',
				'header_footer_code_add_footer_home' => '',
				
				'header_footer_code_add_head_all' => '',
				'header_footer_code_add_footer_all' => '',
				
				'header_footer_code_add_before_articles_all' => '',
				'header_footer_code_add_after_articles_all' => '',
				
				
				
				
				
				
				/*
				* System General Setting
				*/
				
				//System General Setting
				'last_time_clear_cache' => 0
			);
		}
		
		

		$saved = get_option($input_parameters['options_id']);
		
		if (!empty($saved)) {
			$saved = $this->base_fix_options($saved);
			
			foreach ($saved as $key => $option) {
				$options[$key] = $option;
			}
		}
		
		$options = $this->base_fix_options($options);
		
		if($input_parameters['save_options_when_different_status']) {
			if ($saved != $options)	{
				$this->update_options($input_parameters['options_id'], $options);
			}
		}
		
		
		$this->baseCacheData[$keyCache1] = $options;

		return $options;

	}
	
	
	public function handle_options()
	{
		$options = $this->get_options();
		
		if (isset($_GET['notice'])) {
			if ($_GET['notice']==1) {
				$options['notice']=0;
				$this->update_options($this->wpOptimizeByxTraffic_DB_option, $options);
			}
		}
		
		if ( isset($_POST['submitted']) ) {
			
			check_admin_referer(WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
			
			
			
			if ( isset($_POST['base_dashboard_submitted']) ) {
				
				$arrayFields1 = array(
					'base_custom_post_types'
					,'base_custom_taxonomies'
				);
				
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = ''; 
					}
				}
				
				$options['base_custom_post_types'] = trim(preg_replace('#[\'\"]+#is','',$options['base_custom_post_types']));
				$options['base_custom_taxonomies'] = trim(preg_replace('#[\'\"]+#is','',$options['base_custom_taxonomies']));
				
			}
			
			
			if ( isset($_POST['optimize_links_submitted']) ) {
				
				$arrayFields1 = array(
					'optimize_links_enable'
					,'optimize_links_process_in_post'
					,'optimize_links_allow_link_to_postself'
					,'optimize_links_process_in_page'
					,'optimize_links_allow_link_to_pageself'
					,'optimize_links_process_in_comment'
					,'optimize_links_excludeheading'
					,'optimize_links_link_to_posts'
					,'optimize_links_link_to_pages'
					,'optimize_links_link_to_cats'
					,'optimize_links_link_to_tags'
					,'base_custom_post_types'
					,'base_custom_taxonomies'
					//,'optimize_links_ignore'
					,'optimize_links_ignorepost'
					,'optimize_links_maxlinks'
					,'optimize_links_maxsingle'
					,'optimize_links_maxsingleurl'
					,'optimize_links_minusage'
					,'optimize_links_customkey'
					,'optimize_links_customkey_url'
					,'optimize_links_customkey_preventduplicatelink'
					,'optimize_links_nofolo'
					,'optimize_links_blanko'
					,'optimize_links_onlysingle'
					,'optimize_links_casesens'
					,'optimize_links_process_in_feed'
					,'optimize_links_use_cats_as_keywords'
					,'optimize_links_use_tags_as_keywords'
					,'optimize_links_nofollow_urls'
					,'optimize_links_nofolo_blanko_exclude_urls'
					
					,'optimize_links_open_autolink_new_window'
					
				);
				
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				
				$options['optimize_links_maxlinks']=(int) $options['optimize_links_maxlinks'];	
				$options['optimize_links_maxsingle']=(int) $options['optimize_links_maxsingle'];					
				$options['optimize_links_maxsingleurl']=(int) $options['optimize_links_maxsingleurl'];
				$options['optimize_links_minusage']=(int) $options['optimize_links_minusage'];
				
				
			}
			
			if ( isset($_POST['optimize_images_submitted']) ) {
				
				$arrayFields1 = array(
					'optimize_images_alttext'
					,'optimize_images_titletext'
					,'optimize_images_override_alt'
					,'optimize_images_override_title'
					
					
					,'optimize_images_optimize_image_file_enable'
					
					,'optimize_images_auto_resize_images_enable'
					
					//lazy load
					,'optimize_images_images_lazy_load_enable'
					,'optimize_images_images_lazy_load_frontpage_enable'
					
					//watermark image
					
					,'optimize_images_watermarks_enable'
					,'optimize_images_watermarks_watermark_position'
					,'optimize_images_watermarks_watermark_opacity_value'
					,'optimize_images_watermarks_watermark_type'
					,'optimize_images_file_minimum_width_height'
					,'optimize_images_file_maximum_width_height'
					,'optimize_images_watermarks_watermark_text_value'
					,'optimize_images_watermarks_watermark_text_font_name'
					,'optimize_images_watermarks_watermark_text_size'
					,'optimize_images_watermarks_watermark_text_color'
					,'optimize_images_watermarks_watermark_text_margin_x'
					,'optimize_images_watermarks_watermark_text_margin_y'
					,'optimize_images_watermarks_watermark_text_opacity_value'
					,'optimize_images_watermarks_watermark_text_background_enable'
					,'optimize_images_watermarks_watermark_text_background_color'
					,'optimize_images_watermarks_watermark_text_background_opacity_value'
					,'optimize_images_watermarks_watermark_text_outline_enable'
					,'optimize_images_watermarks_watermark_text_outline_color'
					,'optimize_images_watermarks_watermark_text_outline_width'
					
					,'optimize_images_watermarks_watermark_image_url'
					,'optimize_images_watermarks_watermark_image_width'
					,'optimize_images_watermarks_watermark_image_margin_x'
					,'optimize_images_watermarks_watermark_image_margin_y'
					
					,'optimize_images_image_quality_value'
					,'optimize_images_rename_img_filename_value'
					,'optimize_images_maximum_files_handled_each_request'
					,'optimize_images_handle_again_files_different_configuration_enable'
					,'optimize_images_remove_files_available_different_configuration_enable'
					
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				$keyField1 = 'optimize_images_file_minimum_width_height';
				$options[$keyField1] = (int)$options[$keyField1];
				
				$keyField1 = 'optimize_images_file_maximum_width_height';
				$options[$keyField1] = (int)$options[$keyField1];
				
			}
			
			
			
			
			//optimize_traffic
			if ( isset($_POST['optimize_traffic_submitted']) ) {
				
				$arrayFields1 = array(
					'optimize_traffic_modules'
				);
				
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = (array)$_POST[$value1];
					} else {
						$options[$value1] = array();
					}
				}
				
				
				$arrayModuleTypePosAdded = array();
				foreach($options['optimize_traffic_modules'] as $key1 => $value1) {
					if(isset($value1['module_type']) && $value1['module_type']) {
						$keyTemp = $value1['module_type'].'_'.$value1['module_position'];
						if(!in_array($keyTemp,$arrayModuleTypePosAdded)) {
							$arrayModuleTypePosAdded[] = $keyTemp;
						} else {
							unset($options['optimize_traffic_modules'][$key1]);
						}
					}
				}
				
			}
			
			
			
			//optimize_databases
			if ( isset($_POST['optimize_databases_submitted']) ) {
				
				$arrayFields1 = array(
					
					//optimize_javascript
					'optimize_databases_actions_enable'
					,'optimize_databases_keep_last_data_enable'
					,'optimize_databases_keep_last_data_days'
					,'optimize_databases_scheduled_actions_enable'
					,'optimize_databases_scheduled_actions_days'
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				$options['optimize_databases_actions_enable'] = (array)$options['optimize_databases_actions_enable'];
				
				$options['optimize_databases_keep_last_data_days'] = abs((int)$options['optimize_databases_keep_last_data_days']);
				$options['optimize_databases_scheduled_actions_days'] = abs((int)$options['optimize_databases_scheduled_actions_days']);
				
			}
			
			
			
			//optimize_security
			if ( isset($_POST['optimize_security_submitted']) ) {
				
				$arrayFields1 = array(
					
					//optimize_captcha
					'optimize_security_captcha_enable_for'
					,'optimize_security_captcha_disable_for'
					,'optimize_security_captcha_disable_for_users_roles'
					,'optimize_security_captcha_charset'
					,'optimize_security_captcha_charlength'
					,'optimize_security_captcha_image_width'
					,'optimize_security_captcha_image_height'
					,'optimize_security_captcha_title_form'
					,'optimize_security_captcha_required_symbol'
					,'optimize_security_captcha_title_input_form'
					,'optimize_security_captcha_title_input_placeholder_form'
					,'optimize_security_captcha_title_reload_image_captcha_form'
					,'optimize_security_captcha_notice_when_user_not_enter_captcha'
					,'optimize_security_captcha_notice_when_user_enter_wrong_captcha'
					,'optimize_security_captcha_notice_unknown_error'
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				$arrayFields1 = array(
					
					//optimize_javascript
					'optimize_security_captcha_enable_for'
					,'optimize_security_captcha_disable_for_users_roles'
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					$options[$value1] = (array)$options[$value1];
				}
				
				$arrayFields1 = array(
					
					'optimize_security_captcha_charset'					
					,'optimize_security_captcha_title_form'
					,'optimize_security_captcha_required_symbol'
					,'optimize_security_captcha_title_input_form'
					,'optimize_security_captcha_title_input_placeholder_form'
					,'optimize_security_captcha_title_reload_image_captcha_form'
					,'optimize_security_captcha_notice_when_user_not_enter_captcha'
					,'optimize_security_captcha_notice_when_user_enter_wrong_captcha'
					,'optimize_security_captcha_notice_unknown_error'
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					
					$options[$value1] = (string)$options[$value1];
					//$options[$value1] = htmlentities($options[$value1], ENT_QUOTES, "UTF-8");
					$options[$value1] = stripslashes($options[$value1]);
				}
				
				
				$options['optimize_security_captcha_charlength'] = abs((int)$options['optimize_security_captcha_charlength']);
				if($options['optimize_security_captcha_charlength']<1) {
					$options['optimize_security_captcha_charlength'] = 1;
				}
				$options['optimize_security_captcha_image_width'] = abs((int)$options['optimize_security_captcha_image_width']);
				$options['optimize_security_captcha_image_height'] = abs((int)$options['optimize_security_captcha_image_height']);
				
				$options['optimize_security_captcha_title_input_placeholder_form'] = trim(PepVN_Data::removeQuotes($options['optimize_security_captcha_title_input_placeholder_form']));
			}
			
			
			
			
			//optimize_backup
			if ( isset($_POST['optimize_backup_submitted']) ) {
				
				$arrayFields1 = array(
					
					'optimize_backup_backup_enable_for'
					
					,'optimize_backup_backup_enable_database_tables'
					
					,'optimize_backup_backup_enable_folders_files_root_path'
					,'optimize_backup_backup_enable_folders_files_include'
					,'optimize_backup_backup_enable_folders_files_exclude'
					
					,'optimize_backup_backup_file_name'
					
					,'optimize_backup_enable_split_backup_file'
					,'optimize_backup_split_backup_file_size'
					
					,'optimize_backup_enable_encrypt_backup_file'
					,'optimize_backup_encrypt_backup_file_data_password'
					,'optimize_backup_encrypt_backup_file_data_repassword'
					
					,'optimize_backup_backup_enable_storages'
					
					,'optimize_backup_backup_enable_storages_to_folder_path'
					
					,'optimize_backup_backup_enable_storages_to_email_address'
					
					,'optimize_backup_ftp_setup_data_server_address'
					,'optimize_backup_ftp_setup_data_server_port'
					,'optimize_backup_ftp_setup_data_username'
					,'optimize_backup_ftp_setup_data_password'
					,'optimize_backup_ftp_setup_data_folder_store'
					,'optimize_backup_ftp_setup_data_enable_secure'
					,'optimize_backup_ftp_setup_data_enable_passive_mode'
						
					,'optimize_backup_dropbox_setup_data_app_key'
					,'optimize_backup_dropbox_setup_data_app_secret'
					
					,'optimize_backup_googledrive_setup_data_client_id'
					,'optimize_backup_googledrive_setup_data_client_secret'
					
					,'optimize_backup_enable_backup_scheduled'
					,'optimize_backup_backup_scheduled_days'
					
					,'optimize_backup_maximum_number_backup_versions_retained'
					
					,'optimize_backup_enable_run_backup_scheduled_specified_time_each_day'
					,'optimize_backup_run_backup_scheduled_specified_time_each_day_from_to'
					
				);
				
				
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				
				//to array
				$arrayFields1 = array(
					'optimize_backup_backup_enable_for'
					,'optimize_backup_backup_enable_database_tables'
					,'optimize_backup_backup_enable_storages'
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					$options[$value1] = (array)$options[$value1];
				}
				
				
				//to string
				$arrayFields1 = array(
					'optimize_backup_backup_enable_folders_files_root_path'					
					,'optimize_backup_backup_enable_folders_files_include'
					,'optimize_backup_backup_enable_folders_files_exclude'
					,'optimize_backup_backup_file_name'
					,'optimize_backup_encrypt_backup_file_data_password'
					,'optimize_backup_encrypt_backup_file_data_repassword'
					,'optimize_backup_backup_enable_storages_to_folder_path'
					,'optimize_backup_backup_enable_storages_to_email_address'
					
					,'optimize_backup_ftp_setup_data_server_address'
					,'optimize_backup_ftp_setup_data_server_port'
					,'optimize_backup_ftp_setup_data_username'
					,'optimize_backup_ftp_setup_data_password'
					,'optimize_backup_ftp_setup_data_folder_store'
					
					,'optimize_backup_dropbox_setup_data_app_key'
					,'optimize_backup_dropbox_setup_data_app_secret'
					
					,'optimize_backup_googledrive_setup_data_client_id'
					,'optimize_backup_googledrive_setup_data_client_secret'
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					$options[$value1] = (string)$options[$value1];
				}
				
				
				$options['optimize_backup_split_backup_file_size'] = abs((int)$options['optimize_backup_split_backup_file_size']); 
				
				$options['optimize_backup_backup_scheduled_days'] = abs((int)$options['optimize_backup_backup_scheduled_days']);
				
				$options['optimize_backup_maximum_number_backup_versions_retained'] = abs((int)$options['optimize_backup_maximum_number_backup_versions_retained']);
				
				
				$options['optimize_backup_backup_enable_folders_files_root_path'] = $this->base_getABSPATH();
				$options['optimize_backup_backup_enable_folders_files_include'] = $this->base_getABSPATH();
				
				$options['optimize_backup_backup_enable_folders_files_exclude'] = explode(PHP_EOL , $options['optimize_backup_backup_enable_folders_files_exclude']);
				$options['optimize_backup_backup_enable_folders_files_exclude'] = PepVN_Data::cleanArray($options['optimize_backup_backup_enable_folders_files_exclude']);
				$options['optimize_backup_backup_enable_folders_files_exclude'] = implode(PHP_EOL , $options['optimize_backup_backup_enable_folders_files_exclude']);
				$options['optimize_backup_backup_enable_folders_files_exclude'] = trim($options['optimize_backup_backup_enable_folders_files_exclude']);
				
				
				//compare password
				$savePasswordStatus1 = false;
				
				if($options['optimize_backup_encrypt_backup_file_data_password'] && $options['optimize_backup_encrypt_backup_file_data_repassword']) {
					if($options['optimize_backup_encrypt_backup_file_data_password'] === $options['optimize_backup_encrypt_backup_file_data_repassword']) {
						$savePasswordStatus1 = true;
					}
				}
				
				if(!$savePasswordStatus1) {
					unset($options['optimize_backup_encrypt_backup_file_data_password']);
					unset($options['optimize_backup_encrypt_backup_file_data_repassword']);
				}
				
			}
			
			
			//optimize_email 
			if ( isset($_POST['optimize_email_submitted']) ) {
				
				$arrayFields1 = array(
					
					'optimize_email_enable_advanced_email'
				
					,'optimize_email_advanced_email_from_email'
					,'optimize_email_advanced_email_from_name'
					
					,'optimize_email_advanced_email_method'
					
					,'optimize_email_advanced_email_smtp_server'
					,'optimize_email_advanced_email_smtp_port'
					,'optimize_email_advanced_email_smtp_secure_type'
					,'optimize_email_advanced_email_smtp_username'
					,'optimize_email_advanced_email_smtp_password'
					
				);
				
				
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				//to string
				$arrayFields1 = array(
					'optimize_email_enable_advanced_email' => ''	//on
				
					,'optimize_email_advanced_email_from_email' => ''	//string
					,'optimize_email_advanced_email_from_name' => ''	//string
					
					,'optimize_email_advanced_email_method' => ''	//string
					
					,'optimize_email_advanced_email_smtp_server' => ''	//string or ipaddress
					,'optimize_email_advanced_email_smtp_secure_type' => ''	//string
					,'optimize_email_advanced_email_smtp_username' => ''	//string
					,'optimize_email_advanced_email_smtp_password' => ''	//string
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					$options[$value1] = (string)$options[$value1];
				}
				
				
				$options['optimize_email_advanced_email_smtp_port'] = abs((int)$options['optimize_email_advanced_email_smtp_port']); 
				
			}
			
			
			//header_footer
			if ( isset($_POST['header_footer_submitted']) ) {
				
				$arrayFields1 = array(
					
					'header_footer_code_add_head_home'
					,'header_footer_code_add_footer_home'
					,'header_footer_code_add_head_all'
					,'header_footer_code_add_footer_all'
					,'header_footer_code_add_before_articles_all'
					,'header_footer_code_add_after_articles_all'
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $this->header_footer_encode_option($_POST[$value1]);
					} else {
						$options[$value1] = ''; 
					}
				}
				
				
			}
			
			$this->update_options($this->wpOptimizeByxTraffic_DB_option, $options);
			
			echo '<div class="updated fade"><p><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Options saved',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</p></div>';
			
			$this->base_clear_data(',all,'); 
			
		}
		
		$resultData = array(
			'options' => $options
		);
		
		return $resultData; 
		
	}
	
	
	
	public function base_fix_options($options)
	{
		foreach($options as $key1 => $value1) {
			if(false !== stripos($key1,'[]')) {
				unset($options[$key1]);
				$key1 = preg_replace('#\[\]#is','',$key1);
				$options[$key1] = $value1;
			}
		}
		
		
		
		$arrayFields1 = array(
			'optimize_images_watermarks_watermark_position'
			,'optimize_images_watermarks_watermark_type'
			
			,'optimize_databases_actions_enable'
			
			,'optimize_security_captcha_enable_for'
			,'optimize_security_captcha_disable_for_users_roles'
			
			,'optimize_backup_backup_enable_for'
			,'optimize_backup_backup_enable_database_tables'
			,'optimize_backup_backup_enable_storages'
		);
		
		foreach($arrayFields1 as $key1 => $value1) {
			if(isset($options[$value1]) && $options[$value1]) {
				if(is_array($options[$value1])) {
					$options[$value1] = array_unique($options[$value1]);
				}
			}
		}
		
		
		return $options;
		
	}
	
	
	public function update_options($options_id, $options, $input_configs = false)
	{
		if(!$input_configs) {
			$input_configs = array();
		}
		
		$base_is_set_server_config_for_optimize_speed = false;
		
		$options = $this->base_fix_options($options);
		
		if(isset($input_configs['merge_status']) && $input_configs['merge_status']) {
			
			$optionsSaved = get_option($options_id);
			
			if (!empty($optionsSaved)) {
			
				$optionsSaved = $this->base_fix_options($optionsSaved);
				
				foreach ($options as $key1 => $value1) {
					if($this->base_is_set_server_config_for_optimize_speed($key1)) {
						if(isset($optionsSaved[$key1])) {
							if($optionsSaved[$key1] != $value1) {
								$base_is_set_server_config_for_optimize_speed = true;
							}
						}
					}
					
					$optionsSaved[$key1] = $value1;
				}
				
				$options = $optionsSaved;
				$optionsSaved = 0;
				
			}
			
		}
		
		$resultData = update_option($options_id, $options);
		
		if($base_is_set_server_config_for_optimize_speed) {
			global $wpOptimizeSpeedByxTraffic;
			if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
				$wpOptimizeSpeedByxTraffic->base_activate_optimize_speed();
			}
		}
		
		return $resultData;
	}
	
	
	public function base_is_set_server_config_for_optimize_speed($option_key)
	{
		return in_array($option_key, array(
			'optimize_cache_mobile_device_cache_enable'
			,'optimize_cache_url_get_query_cache_enable'
			,'optimize_images_auto_resize_images_enable'
		));
	}
	
	
	
	public function base_get_jobs_multi($input_jobs_id)
	{
		$keyStaticVar = PepVN_Data::fKey(array(
			$this->wpOptimizeByxTraffic_DB_option . '_jobs_multi_data'
			,$input_jobs_id
		));
		
		$staticVarData = PepVN_Data::staticVar_GetData($keyStaticVar, false); 
		
		return $staticVarData;
	}
	
	
	public function base_update_jobs_multi($input_jobs_id, $input_jobs_data)
	{
		
		if($input_jobs_id) {
		
			$keyStaticVar = PepVN_Data::fKey(array(
				$this->wpOptimizeByxTraffic_DB_option . '_jobs_multi_data'
				,$input_jobs_id
			));
			
			
			
			if(null === $input_jobs_data) {
				PepVN_Data::staticVar_RemoveData($keyStaticVar);
			} else if(is_array($input_jobs_data)) {
				PepVN_Data::staticVar_SetData($keyStaticVar, $input_jobs_data, 'r'); 
			}
			
		}
	}
	
	
	
	
	
	public function base_is_has_contact_form()
	{
		$resultData = array();
		$resultData['status'] = 0;
		$resultData['contact_plugins_name'] = array();
		
		if(!$resultData['status']) {
			if(defined('WPCF7_PLUGIN_NAME')) {
				$resultData['status'] = 1;
				$resultData['contact_plugins_name'][] = 'contact-form-7';
			}
		}
		
		
		
		return $resultData;
		
	}
	
	
	public function base_get_woocommerce_urls()
	{
		
		$keyCache1 = PepVN_Data::fKey(array(
			__METHOD__
			,'base_get_woocommerce_urls'
		));
		
		$valueTemp = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if($valueTemp) {
			return $valueTemp;
		}
		
		$resultData = array();
		$resultData['urls'] = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
		
			if(function_exists('woocommerce_get_page_id')) {
				global $woocommerce;
				if(isset($woocommerce) && $woocommerce) {
					if(isset($woocommerce->cart) && $woocommerce->cart) {
						if(
							method_exists($woocommerce->cart,'get_cart_url')
							&& method_exists($woocommerce->cart,'get_checkout_url')
						) {
							
							
							$resultData['urls'] = array();
							
							$resultData['urls']['cart_url'] = $woocommerce->cart->get_cart_url();
							$resultData['urls']['checkout_url'] = $woocommerce->cart->get_checkout_url();
							
							$pageId1 = woocommerce_get_page_id( 'shop' );
							if($pageId1) {
								$pageId1 = (int)$pageId1;
								if($pageId1>0) {
									$resultData['urls']['shop_page_url'] = get_permalink( $pageId1 );
								}
							}
							
							
							$pageId1 = get_option( 'woocommerce_myaccount_page_id' );
							if($pageId1) {
								$pageId1 = (int)$pageId1;
								if($pageId1>0) {
									$resultData['urls']['myaccount_page_url'] = get_permalink( $pageId1 );
									$resultData['urls']['logout_url'] = wp_logout_url( $resultData['urls']['myaccount_page_url'] );
								}
							}
							
							$pageId1 = woocommerce_get_page_id( 'pay' );
							if($pageId1) {
								$pageId1 = (int)$pageId1;
								if($pageId1>0) {
									$resultData['urls']['payment_page_url'] = get_permalink( $pageId1 ); 
								}
							}
							
							
							
							
						}
					}
					
					
				}
				
			}
			
		}
		
		if($resultData['urls']) {
			if($this->fullDomainName) {
				foreach($resultData['urls'] as $key1 => $value1) {
					if($value1) {
						$value1 = PepVN_Data::removeProtocolUrl($value1);
						$value1 = preg_replace('#^'.PepVN_Data::preg_quote($this->fullDomainName).'#is','',$value1,1);
						$value1 = trim($value1);
						if(strlen($value1)>0) {
							$value1 = explode('?',$value1,2);
							$value1[0] = trim($value1[0]);
							if(strlen($value1[0])>0) {
								$resultData['urls_paths'][$key1] = $value1[0];
							}
						}
						
						
					}
				}
			}
		}
		
		PepVN_Data::$cacheObject->set_cache($keyCache1,$resultData);
		
		return $resultData;
		
	}
	
	
	
	public function base_gzip_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['gzip_file_path'] = '';
		$resultData['source_file_position'] = 0;
		
		$checkStatus1 = false;
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			if(PepVN_Data::isAllowReadAndWrite($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(!isset($input_parameters['source_file_position'])) {
						$input_parameters['source_file_position'] = 0;
					}
					
					$input_parameters['source_file_position'] = abs((int)$input_parameters['source_file_position']);
					
					$checkStatus1 = true;
				}
			}
		}
		
		if($checkStatus1) {
			
			$resultData['source_file_position'] += $input_parameters['source_file_position'];
			
			$sourceFileHandle = fopen($input_parameters['source_file_path'],'r');
			
			if($sourceFileHandle) {
				
				if($input_parameters['source_file_position'] > 0) {
					fseek($sourceFileHandle, $input_parameters['source_file_position'], SEEK_SET);
				}
			
				$gzipFilePath = $input_parameters['source_file_path'].'.gz';
				
				$gzipFileHandle = gzopen($gzipFilePath, 'ab9');
				
				if($gzipFileHandle) {
					
					if($this->base_is_system_safe_to_continue_run()) {
					
						$bufferSizeBytes = $this->base_get_safe_buffer_size_bytes_for_read_write_data();
						
						while(!feof($sourceFileHandle)) {
						
							$gzwriteStatus = gzwrite($gzipFileHandle, fread($sourceFileHandle, $bufferSizeBytes));
							
							if($gzwriteStatus) {
								$resultData['source_file_position'] += $bufferSizeBytes;
							}
							
							if(!$this->base_is_system_safe_to_continue_run()) {
								$resultData['break_for_system_safe_status'] = true;
								break;
							}
							
						}
						
						if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
						} else {
							$resultData['gzip_file_path'] = $gzipFilePath;
						}
					}
					
					gzclose($gzipFileHandle);
					$gzipFileHandle = 0;
				}
				
				fclose($sourceFileHandle);
				$sourceFileHandle = 0;
				
			}
		}
		
		return $resultData;
	}
	
	
	
	public function base_ungzip_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['ungzip_file_path'] = '';
		$resultData['source_file_position'] = 0;
		
		$checkStatus1 = false;
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			if(PepVN_Data::isAllowReadAndWrite($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(!isset($input_parameters['source_file_position'])) {
						$input_parameters['source_file_position'] = 0;
					}
					
					$input_parameters['source_file_position'] = abs((int)$input_parameters['source_file_position']);
					
					$checkStatus1 = true;
				}
			}
		}
		
		if($checkStatus1) {
			
			$resultData['source_file_position'] += $input_parameters['source_file_position'];
			
			$sourceFileHandle = gzopen($input_parameters['source_file_path'],'r');
			
			if($sourceFileHandle) {
				
				if($input_parameters['source_file_position'] > 0) {
					fseek($sourceFileHandle, $input_parameters['source_file_position'], SEEK_SET);
				}
			
				$ungzipFilePath = $input_parameters['source_file_path'];
				$ungzipFilePath = preg_replace('#(\.gz)+$#','',$ungzipFilePath);
				
				if($input_parameters['source_file_position'] > 0) {
					$ungzipFileHandle = fopen($ungzipFilePath, 'a');
				} else {
					$ungzipFileHandle = fopen($ungzipFilePath, 'w');
				}
				
				if($ungzipFileHandle) {
					
					if($this->base_is_system_safe_to_continue_run()) {
					
						$bufferSizeBytes = $this->base_get_safe_buffer_size_bytes_for_read_write_data();
						
						while(!feof($sourceFileHandle)) {
						
							$ungzwriteStatus = fwrite($ungzipFileHandle, gzread($sourceFileHandle, $bufferSizeBytes));
							
							if($ungzwriteStatus) {
								$resultData['source_file_position'] += $bufferSizeBytes;
							}
							
							if(!$this->base_is_system_safe_to_continue_run()) {
								$resultData['break_for_system_safe_status'] = true;
								break;
							}
							
						}
						
						if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
						} else {
							$resultData['ungzip_file_path'] = $ungzipFilePath;
						}
					}
					
					fclose($ungzipFileHandle);
					$ungzipFileHandle = 0;
				}
				
				gzclose($sourceFileHandle);
				$sourceFileHandle = 0;
				
			}
		}
		
		return $resultData;
	}
	
	
	
	
	
	
	public function base_is_file_encrypt($input_file_path) 
	{
		if(preg_match('#\.encrypted$#', $input_file_path)) {
			return true;
		}
		
		return false;
	}
	
	
	
	public function base_encrypt_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['encrypt_file_path'] = '';
		$resultData['source_file_position'] = 0;
		
		$checkStatus1 = false;
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			if(PepVN_Data::isAllowReadAndWrite($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(
						isset($input_parameters['password'])
						&& ($input_parameters['password'])
					) {
						
						if(!isset($input_parameters['source_file_position'])) {
							$input_parameters['source_file_position'] = 0;
						}
						
						$input_parameters['source_file_position'] = abs((int)$input_parameters['source_file_position']);
						
						$checkStatus1 = true;
						
					}
				}
			}
		}
		
		if($checkStatus1) {
			
			$resultData['source_file_position'] += $input_parameters['source_file_position'];
			
			$sourceFileHandle = fopen($input_parameters['source_file_path'],'r');
			
			if($input_parameters['source_file_position'] > 0) {
				fseek($sourceFileHandle, $input_parameters['source_file_position'], SEEK_SET);
			}
			
			if($sourceFileHandle) {
				
				$encryptFilePath = $input_parameters['source_file_path'].'.encrypted';
				
				$encryptFileHandle = fopen($encryptFilePath, 'a+');
				
				if($encryptFileHandle) {
					
					if($this->base_is_system_safe_to_continue_run()) {
					
						$bufferSizeBytes = $this->bufferSizeBytesForEncryptData;
						
						$iNumber = 0;
						
						while(!feof($sourceFileHandle)) {
							
							$iNumber++;
							
							$fwriteStatus = fwrite(
								$encryptFileHandle
								, PepVN_Data::encryptData_Rijndael256((string)fread($sourceFileHandle, $bufferSizeBytes), $input_parameters['password']) . PHP_EOL
							);
							
							if($fwriteStatus) {
								$resultData['source_file_position'] += $bufferSizeBytes;
							}
							
							if(0 === ($iNumber % 1000)) {
								if(!$this->base_is_system_safe_to_continue_run()) {
									$resultData['break_for_system_safe_status'] = true;
									break;
								}
							}
							
						}
						
						if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
						} else {
							$resultData['encrypt_file_path'] = $encryptFilePath;
						}
					}
					
					fclose($encryptFileHandle);
					$encryptFileHandle = 0;
				}
				
				fclose($sourceFileHandle);
				$sourceFileHandle = 0;
				
			}
		}
		
		return $resultData;
		
	}
	
	
	
	public function base_decrypt_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['decrypt_file_path'] = ''; 
		$resultData['source_file_position'] = 0;
		
		$decryptFilePath = '';
		
		$checkStatus1 = false;
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			if(PepVN_Data::isAllowReadAndWrite($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(
						isset($input_parameters['password'])
						&& ($input_parameters['password'])
					) {
						
						if(!isset($input_parameters['source_file_position'])) {
							$input_parameters['source_file_position'] = 0;
						}
						
						$input_parameters['source_file_position'] = abs((int)$input_parameters['source_file_position']);
						
						$checkStatus1 = true;
						
						
					}
				}
			}
		}
		
		if($checkStatus1) {
			
			$sourceFileHandle = fopen($input_parameters['source_file_path'],'r');
			
			if($sourceFileHandle) {
					
				if($input_parameters['source_file_position'] > 0) {
					fseek($sourceFileHandle, $input_parameters['source_file_position'], SEEK_SET); 
					$resultData['source_file_position'] += $input_parameters['source_file_position'];
				}
				
				$decryptFilePath = preg_replace('#\.encrypted$#','',$input_parameters['source_file_path']);
				$decryptFilePath = trim($decryptFilePath);
				
				$decryptFileHandle = fopen($decryptFilePath, 'a');
				
				if($decryptFileHandle) {
					
					if($this->base_is_system_safe_to_continue_run()) {
					
						$iNumber = 0;
						
						while(!feof($sourceFileHandle)) {
							
							$iNumber++;
							
							$sourceFile_LineContent = fgets($sourceFileHandle);
							$sourceFile_LineContentLength = strlen($sourceFile_LineContent);
							
							if($sourceFile_LineContentLength > 0) {
								
								$fwriteStatus = fwrite(
									$decryptFileHandle
									, PepVN_Data::decryptData_Rijndael256($sourceFile_LineContent, $input_parameters['password'])
								);
								
								if($fwriteStatus) {
									$resultData['source_file_position'] += $sourceFile_LineContentLength;
								}
								
							}
							
							if(0 === ($iNumber % 1000)) {
								if(!$this->base_is_system_safe_to_continue_run()) {
									$resultData['break_for_system_safe_status'] = true;
									break;
								}
							}
							
						}
						
						if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
						} else {
							$resultData['decrypt_file_path'] = $decryptFilePath;
						}
					}
					
					fclose($decryptFileHandle);
					$decryptFileHandle = 0;
				}
				
				fclose($sourceFileHandle);
				$sourceFileHandle = 0;
				
			}
		}
		
		return $resultData;
		
	}
	
	
	
	
	public function base_get_order_number_of_split_file($input_file_path) 
	{
		$resultData = '';
		preg_match('#\.split\.([0-9]{6})$#', $input_file_path,$matched);
		if(isset($matched[1])) {
			$resultData = $matched[1];
		}
		
		return $resultData;
	}
	
	public function base_is_file_split($input_file_path) 
	{
		if(preg_match('#\.split\.([0-9]{6})$#', $input_file_path)) {
			return true;
		}
		
		return false;
	}
	
	public function base_split_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['split_file_path'] = array();
		$resultData['source_file_position'] = 0;
		
		$checkStatus1 = false;
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			
			$resultData['split_file_path'] = array($input_parameters['source_file_path']);
			
			if(PepVN_Data::isAllowReadAndWrite($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(
						isset($input_parameters['chunk_size_bytes'])
						&& ($input_parameters['chunk_size_bytes'])
					) {
						$input_parameters['chunk_size_bytes'] = (int)$input_parameters['chunk_size_bytes'];
						if($input_parameters['chunk_size_bytes'] > 0) {
							
							$sourceFileSizeBytes = filesize($input_parameters['source_file_path']);
							
							if($sourceFileSizeBytes) {
								$sourceFileSizeBytes = (int)$sourceFileSizeBytes;
								if($sourceFileSizeBytes > 0) {
									
									$totalChunkFiles = ceil($sourceFileSizeBytes / $input_parameters['chunk_size_bytes']);
									
									if($totalChunkFiles > 1) {	//when sourceFileSizeBytes > $input_parameters['chunk_size_bytes']
										if(!isset($input_parameters['source_file_position'])) {
											$input_parameters['source_file_position'] = 0;
										}
										
										$input_parameters['source_file_position'] = abs((int)$input_parameters['source_file_position']);
										
										$iStartNumberFile = 1;
										
										if($input_parameters['source_file_position'] > 0) {
											$iStartNumberFile = ceil(($input_parameters['source_file_position'] + 1) / $input_parameters['chunk_size_bytes']);
										}
										
										$iStartNumberFile = (int)$iStartNumberFile;
										
										if($iStartNumberFile > 0) {
											
											$checkStatus1 = true;
										}
										
									}
								}
							}
						}
						
					}
				}
			}
		}
		
		if($checkStatus1) {
			
			$resultData['source_file_position'] += $input_parameters['source_file_position'];
			
			$sourceFileHandle = fopen($input_parameters['source_file_path'],'r');
			
			if($sourceFileHandle) {
				
				$bufferSizeBytes = $this->bufferSizeBytesForSplitFile;
				
				$iNumberFile = $iStartNumberFile;
				
				for($iNumberFile = $iStartNumberFile;$iNumberFile <= $totalChunkFiles; ++$iNumberFile) {
					
					$thisChunkFileSize = 0;
					
					$chunkFilePath = $input_parameters['source_file_path'].'.split'.'.'.str_pad($iNumberFile, 6, '0', STR_PAD_LEFT);
					
					$isHasThisChunkFileStatus1 = false;
					if(file_exists($chunkFilePath)) {
						$thisChunkFileSize = filesize($chunkFilePath);
						$thisChunkFileSize = (int)$thisChunkFileSize;
						if($thisChunkFileSize >= $input_parameters['chunk_size_bytes']) {
							$isHasThisChunkFileStatus1 = true;
						}
						
					}
					
					if(!$isHasThisChunkFileStatus1) {
						
						
						$chunkFileHandle = fopen($chunkFilePath, 'a');
						
						if($chunkFileHandle) {
							
							$sourceFilePosForThisProcess  = (($iNumberFile - 1) * $input_parameters['chunk_size_bytes']);
							
							if($thisChunkFileSize > 0) {
								$sourceFilePosForThisProcess  += ($thisChunkFileSize - 1);
							}
							
							$sourceFilePosForThisProcess = (int)$sourceFilePosForThisProcess;
							
							fseek($sourceFileHandle, $sourceFilePosForThisProcess, SEEK_SET);
							
							
							if(!$this->base_is_system_safe_to_continue_run()) {
								$resultData['break_for_system_safe_status'] = true;
								break 1;
							} else {
								
								while(!feof($sourceFileHandle)) {
									
									$fwriteStatus = fwrite($chunkFileHandle, fread($sourceFileHandle, $bufferSizeBytes), $bufferSizeBytes);
									
									if($fwriteStatus) {
										$thisChunkFileSize += $bufferSizeBytes;
									}
									
									/*
									if(!$this->base_is_system_safe_to_continue_run()) {
										$resultData['break_for_system_safe_status'] = true;
										break 2;
									}
									*/
									
									if($thisChunkFileSize >= $input_parameters['chunk_size_bytes']) {
										break 1;
									}
									
								}
								
							}
							
							fclose($chunkFileHandle);
							$chunkFileHandle = 0;
						}
					}
				}
				
				
				if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
					
				} else {
					$resultData['split_file_path'] = glob($input_parameters['source_file_path'].'.split.*');
					$resultData['split_file_path'] = (array)$resultData['split_file_path'];
					$resultData['split_file_path'] = PepVN_Data::cleanArray($resultData['split_file_path']);
				}
				
				
				fclose($sourceFileHandle);
				$sourceFileHandle = 0;
				
			}
		}
		
		return $resultData;
		
	}
	
	
	
	
	public function base_join_file($input_parameters)
	{
		$resultData = array();
		
		$resultData['join_file_path'] = '';
		$resultData['source_file_position'] = 0;
		$resultData['split_file_joined'] = array();
		
		$checkStatus1 = false;
		
		$arrayChunkFilesNeedJoin = array();
		
		$joinFilePath = '';
		
		if(
			isset($input_parameters['source_file_path'])
			&& ($input_parameters['source_file_path'])
		) {
			if(PepVN_Data::isAllowRead($input_parameters['source_file_path'])) {
				if(PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['source_file_path']))) {
					if(preg_match('#\.split\.[0-9]{6}$#',$input_parameters['source_file_path'])) {
						$joinFilePath = preg_replace('#\.split\.[0-9]{6}$#','',$input_parameters['source_file_path']);
						$joinFilePath = trim($joinFilePath);
						if($joinFilePath) {
							$globData1 = glob($joinFilePath.'.split.*');
							if($globData1) {
								if(is_array($globData1) && (count($globData1)>0)) {
									foreach($globData1 as $valueOne) {
										if($valueOne) {
											if(PepVN_Data::isAllowRead($valueOne)) {
												$iChunk = $this->base_get_order_number_of_split_file($valueOne);
												$iChunk = (int)$iChunk;
												if($iChunk) {
													$arrayChunkFilesNeedJoin[$valueOne] = $iChunk;
												}
											}
										}
									}
								}
							}
							
							$globData1 = 0;
							
						}
						
					}
					
				}
			}
		}
		
		if(
			isset($input_parameters['split_file_joined'])
			&& ($input_parameters['split_file_joined'])
		) {
			$input_parameters['split_file_joined'] = (array)$input_parameters['split_file_joined'];
			$resultData['split_file_joined'] = array_merge($resultData['split_file_joined'], $input_parameters['split_file_joined']);
		}
		
		
		if(count($arrayChunkFilesNeedJoin)>0) {
			
			$resultData['split_file_joined'] = PepVN_Data::cleanArray($resultData['split_file_joined']);
			
			if(!isset($input_parameters['source_file_position'])) {
				$input_parameters['source_file_position'] = 0;
			}
			$input_parameters['source_file_position'] = (int)$input_parameters['source_file_position'];
			
			$bufferSizeBytes = $this->bufferSizeBytesForSplitFile;
			
			asort($arrayChunkFilesNeedJoin);
			
			$joinFileHandle = fopen($joinFilePath, 'a+');
		
			if($joinFileHandle) {
				
				if($input_parameters['source_file_position'] > 0) {
					fseek($joinFileHandle, $input_parameters['source_file_position'], SEEK_SET);
					$resultData['source_file_position'] += $input_parameters['source_file_position'];
				}
				
				if(!$this->base_is_system_safe_to_continue_run()) {
					$resultData['break_for_system_safe_status'] = true;
				} else {
					
					$iNumber = 0;
					
					foreach($arrayChunkFilesNeedJoin as $keyOne => $valueOne) {
						if(
							$keyOne
							&& (!in_array($keyOne,$resultData['split_file_joined']))
						) {
						
							$chunkFileHandle = fopen($keyOne, 'r');
							if($chunkFileHandle) {
								
								while(!feof($chunkFileHandle)) {
									$iNumber++;
									
									$fwriteStatus = fwrite($joinFileHandle, fread($chunkFileHandle, $bufferSizeBytes), $bufferSizeBytes);
									
									if($fwriteStatus) {
										$resultData['source_file_position'] += $bufferSizeBytes;
										$resultData['split_file_joined'][] = $keyOne;
									}
									if(0 === ($iNumber % 1000)) {
										if(!$this->base_is_system_safe_to_continue_run()) {
											$resultData['break_for_system_safe_status'] = true;
											break 2;
										}
									}
									
								}
								
								fclose($chunkFileHandle);
								$chunkFileHandle = 0;
								
								
							}
							
							if(!$this->base_is_system_safe_to_continue_run()) {
								$resultData['break_for_system_safe_status'] = true;
								break 1;
							}
			
						}
					}
					
				}
				
				fclose($joinFileHandle);
				$joinFileHandle = 0;
				
				
				if(isset($resultData['break_for_system_safe_status']) && $resultData['break_for_system_safe_status']) {
				} else {
					$resultData['join_file_path'] = $joinFilePath;
				}
				
			}
			
		}
		
		$resultData['split_file_joined'] = PepVN_Data::cleanArray($resultData['split_file_joined']);
		
		
		return $resultData;
	}
	
	
	public function base_clear_data($input_action='')
	{
		$arrayFoldersNeedCreate = array();
		
		$input_action = (array)$input_action;
		$input_action = ','.implode(',',$input_action).',';
		
		$timestampNow = PepVN_Data::$defaultParams['requestTime'];
		$timestampNow = (int)$timestampNow;
		
		//get_queried_object()
		
		if(PepVN_Data::$cacheDbObject) {
			PepVN_Data::$cacheDbObject->clean(array(
				'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
			));
		}
		
		PepVN_Data::$cacheObject->clean(array(
			'clean_mode' => PepVN_SimpleFileCache::CLEANING_MODE_ALL
		));
		
		if(
			(false !== stripos($input_action,',cache_permanent,'))
		) {
			PepVN_Data::$cachePermanentObject->clean(array(
				'clean_mode' => PepVN_SimpleFileCache::CLEANING_MODE_ALL
			));
		}
		
		$cachePath = PEPVN_CACHE_DATA_DIR.'s'.DIRECTORY_SEPARATOR;
		
		$arrayPaths = array();
		$arrayPaths[] = $cachePath;
		
		if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) {
			$arrayPaths[] = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH.'data/'.PepVN_Data::$defaultParams['parseedUrlFullRequest']['host'] . '/';
		}
		
		if(isset($this->pepvn_UploadsPreviewImgFolderPath) && $this->pepvn_UploadsPreviewImgFolderPath) {
			$arrayPaths[] = $this->pepvn_UploadsPreviewImgFolderPath;
		}
		
		$arrayFoldersNeedCreate = array_merge($arrayFoldersNeedCreate, $arrayPaths);
		
		foreach($arrayPaths as $path1) {
			if($path1) {
				$pathTemp1 = $path1;
				
				if(
					$pathTemp1 
					&& PepVN_Data::isAllowReadAndWrite($pathTemp1)
				) {
					PepVN_Data::rrmdir($pathTemp1);
					
					$pathTemp1 = $path1;
					
					if($pathTemp1 && file_exists($pathTemp1)) {
					} else {
						PepVN_Data::createFolder($path1);
					}
				}
				
			}
		}
		
		if(
			(false !== stripos($input_action,',all,'))
			|| (false !== stripos($input_action,',cache_files,'))
		) {
			
			$arrayPathFilesNeedCheck = array(
				WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'files'.DIRECTORY_SEPARATOR
			);
			
			$arrayFoldersNeedCreate = array_merge($arrayFoldersNeedCreate, $arrayPathFilesNeedCheck);
			
			foreach($arrayPathFilesNeedCheck as $key1 => $value1) {
				if($value1) {
					if(file_exists($value1)) {
						if(PepVN_Data::is_writable($value1)) {
							
							$globPaths = glob($value1."*.*");
							
							if($globPaths && (count($globPaths)>0)) {
								$timeout1 = 86400 * 15; 
								foreach ($globPaths as $filename) {
									
									if($filename && file_exists($filename) && is_file($filename)) {
										$deleteStatus1 = true;
										$filemtimeTemp1 = filemtime($filename);
										if($filemtimeTemp1) {
											$filemtimeTemp1 = (int)$filemtimeTemp1;
											if(($timestampNow - $filemtimeTemp1) <= $timeout1) {
												$deleteStatus1 = false;
											}
										}
										if($deleteStatus1) {
											@unlink($filename);
											
										}
									}
									
								}
							}
							
						}
					}
				}
			}
			
			
		}
		
		
		
		if(
			(false !== stripos($input_action,',all,'))
		) {
			$arrayPaths = array();
			
			$keyTemp = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH;
			$arrayPaths[$keyTemp] = 30;	//days
			
			$keyTemp = PEPVN_CACHE_DATA_DIR.'lg'.DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 7;	//days
			
			$keyTemp = PEPVN_CACHE_DATA_DIR.'pm'.DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 7;	//days
			
			$keyTemp = PEPVN_CACHE_DATA_DIR.'db'.DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 7;	//days
			
			$keyTemp = PEPVN_CACHE_DATA_DIR.'cbtg'.DIRECTORY_SEPARATOR;	//cache by tags
			$arrayPaths[$keyTemp] = 8;	//days
			
			$keyTemp = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN.'static-vars'.DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 365;	//days
			
			$keyTemp = PEPVN_CACHE_DATA_DIR.'st'.DIRECTORY_SEPARATOR;	//static vars
			$arrayPaths[$keyTemp] = 365;	//days
			
			$keyTemp = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN.'static-files'.DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 30;	//days
			
			$arrayFoldersNeedCreate = array_merge($arrayFoldersNeedCreate, array_keys($arrayPaths));
			
			$enumSpecialFileName = $this->enumSpecialFileName;
			
			$optionsForGetListFoldersFiles = array();
			
			$options = $this->get_options(array(
				'cache_status' => 1
			));
			
			if ($options) {
				if(
					isset($options['optimize_backup_backup_enable_storages_to_folder_path'])
					&& ($options['optimize_backup_backup_enable_storages_to_folder_path'])
				) {
					$valueTemp = $options['optimize_backup_backup_enable_storages_to_folder_path'];
					$valueTemp = trim($valueTemp);
					if($valueTemp) {
						$valueTemp = PepVN_Data::fixPath($valueTemp);
						$valueTemp = (array)$valueTemp;
						$valueTemp = PepVN_Data::cleanPregPatternsArray($valueTemp);
						$optionsForGetListFoldersFiles['exclude_pattern'] = '#('.implode('|',$valueTemp).')#is';
					}
				}
			}
			
			foreach($arrayPaths as $path1 => $timeoutDays) {
				$timeoutDays = (int)$timeoutDays;
				$timeoutSeconds = $timeoutDays * 86400;
				if($path1) {
					$path1 = PepVN_Data::fixPath($path1) . DIRECTORY_SEPARATOR;
					if(
						$path1 
						&& PepVN_Data::isAllowReadAndWrite($path1)
					) {
						$rsOne = $this->base_get_list_folders_files_local_host($path1, $optionsForGetListFoldersFiles);
						foreach($rsOne['list'] as $keyOne => $valueOne) {
							if($keyOne && $valueOne) {
								if(isset($valueOne['t']) && $valueOne['t']) {
									if('f' === $valueOne['t']) {
										if($valueOne['mt']>0) {
											if($valueOne['mt'] <= ( PepVN_Data::$defaultParams['requestTime'] - $timeoutDays)) {	//is timeout
												$filebasename1 = basename($keyOne);
												if($filebasename1) {
													$filebasename1 = trim($filebasename1);
													$filebasename1 = strtolower($filebasename1);
													if(!in_array($filebasename1, $enumSpecialFileName)) {
														if(PepVN_Data::isAllowReadAndWrite($keyOne)) {
															if(is_file($keyOne)) {
																@unlink($keyOne);
															}
														}
													}
												}
												
											}
										}
										
										
									} else if('d' === $valueOne['t']) {
										if(PepVN_Data::isAllowReadAndWrite($keyOne)) {
											$rsCountSubDirsAndFilesInsideDir = PepVN_Data::countSubDirsAndFilesInsideDir($keyOne);
											if(false !== $rsCountSubDirsAndFilesInsideDir) {
												if($rsCountSubDirsAndFilesInsideDir < 1) {
													@rmdir($keyOne);
												}
											}
										}
									}
								}
							}
						}
					}
				}
		
			}
		}
		
		$arrayFoldersNeedCreate = array_unique($arrayFoldersNeedCreate);
		foreach($arrayFoldersNeedCreate as $val1) {
			if($val1) {
				if(file_exists($val1) && is_dir($val1)) {
					
				} else {
					PepVN_Data::createFolder($val1);
				}
			}
		}
		
		global $wpOptimizeSpeedByxTraffic;

		if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
			PepVN_Data::$cacheObject->set_cache(WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY, $wpOptimizeSpeedByxTraffic->get_options(array(
				'cache_status' => 1
			)));
		}
		
		$this->base_protect_folder(array(
			'path' => array(
				WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'files'.DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'data'.DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 's' . DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 'st' . DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 'pm' . DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR
				,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 'cbtg' . DIRECTORY_SEPARATOR
			)
		));
		
		$staticVarData = PepVN_Data::staticVar_GetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS);
		$staticVarData['group_urls_prebuild_cache'] = array();
		PepVN_Data::staticVar_SetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, $staticVarData, 'r');
	}
	
	
	public function get_random_admin_notice() 
	{
		$resultData = array();
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		
		
		if(isset($options['base_activate_timestamp']) && $options['base_activate_timestamp']) {
			$options['base_activate_timestamp'] = (int)$options['base_activate_timestamp'];
			if($options['base_activate_timestamp'] > 0) {
				if($options['base_activate_timestamp'] <= ( PepVN_Data::$defaultParams['requestTime'] - (1))) {
					$noticeTemp = '<div style="display:block;"><div class="updated fade wpoptxtr_notice"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : 
						'.__('Sincerely thank you for your trust and use plugin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG). ' "'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'" ! '.'
						<br />' . __('If you liked this plugin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG). ', 
						<a href="https://bit.ly/wp-optimize-by-xtraffic-rating" target="_blank" ><b><u><i>' . __('you can support us by rating this plugin here',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG). '</i></u></b></a>. 
						' . __('We are grateful for your support :)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG). '
					</div></div>';
					$resultData[] = $noticeTemp;
				}
			}
		}
		
		$resultData = array_unique($resultData);
		if(isset($resultData[0])) {
			shuffle($resultData);
			$this->adminNoticesData[] = $resultData[0];
		}
		
		
		
		return $resultData;
	}
	
	
	public function admin_notice() 
	{
		if(0 === (mt_rand() % 6)) { 
			$this->get_random_admin_notice(); 
		}
		
		$this->adminNoticesData = array_unique($this->adminNoticesData);
		
		if(!PepVN_Data::isEmptyArray($this->adminNoticesData)) {
			
			foreach($this->adminNoticesData as $keyOne => $valueOne) {
				echo $valueOne;
				unset($this->adminNoticesData[$keyOne]);
			}
		}
		
	}
	
	
	public function add_admin_notice_session($notice_type,$notice_data) 
	{
		$_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices'][$notice_type][] = $notice_data;
		$_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices'][$notice_type] = array_unique($_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices'][$notice_type]);
	}
	
	
	
	public function display_admin_notices_session() 
	{
		if(is_admin()) {
			if(
				isset($_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices']) 
				&& $_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices']
				&& !empty($_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices'])
			) {
				$this->base_parse_display_notices($_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices']);
				unset($_SESSION[WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS_SHORT]['notices']);
			}
		}
		
	}
	
	
	
	public function run_activate_plugin( $plugin )
	{
		$current = get_option( 'active_plugins' );
		$plugin = plugin_basename( trim( $plugin ) );

		if ( !in_array( $plugin, $current ) ) {
			$plugin = trim( $plugin );
			$current[] = $plugin;
			sort( $current );
			do_action( 'activate_plugin', $plugin );
			update_option( 'active_plugins', $current );
			do_action( 'activate_' . $plugin );
			do_action( 'activated_plugin', $plugin );
		}

		return null;
	}

	
	public function run_deactivate_plugin( $plugin )
	{
		$current = get_option( 'active_plugins' );
		$plugin = plugin_basename( trim( $plugin ) );

		if ( in_array( $plugin, $current ) ) {
			$plugin = trim($plugin);
			
			foreach($current as $key1 => $value1) {
				if($plugin === $value1) {
					unset($current[$key1]);
				}
			}
			
			sort( $current );
			do_action( 'deactivate_plugin',  $plugin );
			update_option( 'active_plugins', $current );
			do_action( 'deactivate_' . $plugin );
			do_action( 'deactivated_plugin', $plugin );
			
		}
		
		return null;
	}

	
	
	public function install_plugin_install_status($input_args)
	{
		if(!function_exists('install_plugin_install_status')) {
			require_once (ABSPATH . 'wp-admin/includes/plugin-install.php');
        }
		if(!function_exists('get_plugins')) {
			require_once (ABSPATH . 'wp-admin/includes/plugin.php');
        }
		
		return install_plugin_install_status($this->get_plugin_info($input_args), false);
	}
	
	
	public function get_plugin_info($input_args)
	{
		if(!isset($input_args['fields'])) {
			$input_args['fields'] = array();
		}
		
		$keyCache = md5(serialize($input_args));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache);
		
		if(!$resultData) {
			
			if(!function_exists('plugins_api')) {
				require_once (ABSPATH . 'wp-admin/includes/plugin-install.php');
			}
			if(!function_exists('get_plugins')) {
				require_once (ABSPATH . 'wp-admin/includes/plugin.php');
			}
			
			$fields = array(
				'short_description' => true,
				'screenshots' => false,
				'changelog' => false,
				'installation' => false,
				'description' => false
			);
			
			$fields = array_merge($fields, (array)$input_args['fields']);
			
			$args = array(
				'slug' => $input_args['slug'],
				'fields' => $fields
			);
			
			$resultData = plugins_api('plugin_information', $args);
			
			PepVN_Data::$cacheObject->set_cache($keyCache, $resultData);
			
		}
		
        return $resultData;
		
    }
	
	public function check_plugin_status($plugin, $options = false)
	{
		$resultData = array();
		
		$resultData['file_path_key'] = $plugin.'/'.$plugin.'.php';
		$resultData['file_path'] = ABSPATH . 'wp-content/plugins/'.$resultData['file_path_key'];
		
		if('wp-optimize-by-xtraffic' === $plugin) {
			global $wpOptimizeByxTraffic;
			if(
				defined('WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION')
				&& defined('WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG')
				&& class_exists('WPOptimizeByxTraffic')
				&& isset($wpOptimizeByxTraffic) && $wpOptimizeByxTraffic
			) {
				
				if(isset($options['at_least_version'])) {
					if (version_compare(WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION, $options['at_least_version']) >= 0) {
						$resultData['success']['all_valid'] = true;
						$resultData['success']['activated'] = true;
						$resultData['success']['installed'] = true;
					} else {
						$resultData['errors']['at_least_version'] = $options['at_least_version'];
						$resultData['success']['activated'] = true;
						$resultData['success']['installed'] = true;
					}
				} else {
					$resultData['success']['all_valid'] = true;
					$resultData['success']['activated'] = true;
					$resultData['success']['installed'] = true;
				}
				
			} else {
				
				if(
					file_exists($resultData['file_path'])
					&& is_file($resultData['file_path'])
				) {
					$resultData['errors']['not_active'] = 'not_active';
					$resultData['success']['installed'] = true;
				} else {
					$resultData['errors']['not_installed'] = 'not_installed';
				}
			}
			
		} else if('wp-optimize-speed-by-xtraffic' === $plugin) {
			global $wpOptimizeSpeedByxTraffic;
			if(
				defined('WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_VERSION')
				&& isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic
			) {
				
				if(isset($options['at_least_version'])) {
					if (version_compare(WPOPTIMIZESPEEDBYXTRAFFIC_PLUGIN_VERSION, $options['at_least_version']) >= 0) {
						$resultData['success']['all_valid'] = true;
						$resultData['success']['activated'] = true;
						$resultData['success']['installed'] = true;
					} else {
						$resultData['errors']['at_least_version'] = $options['at_least_version'];
						$resultData['success']['activated'] = true;
						$resultData['success']['installed'] = true;
					}
				} else {
					$resultData['success']['all_valid'] = true;
					$resultData['success']['activated'] = true;
					$resultData['success']['installed'] = true;
				}
				
			} else {
				
				if(
					file_exists($resultData['file_path'])
					&& is_file($resultData['file_path'])
				) {
					$resultData['errors']['not_active'] = 'not_active';
					$resultData['success']['installed'] = true;
				} else {
					$resultData['errors']['not_installed'] = 'not_installed';
				}
			}
			
		}
		
		return $resultData;
	}
	
	public function base_dashboard_get_html_blocks_plugins()
	{
		
		$resultData = '';
	
		$arrayPlugins = array(
			'wp-optimize-speed-by-xtraffic' => array(
				'name' => 'WP Optimize Speed By xTraffic'
				, 'wp_plugin_slug' => 'wp-optimize-speed-by-xtraffic'
				, 'wp_plugin_url' => 'https://wordpress.org/plugins/wp-optimize-speed-by-xtraffic/'
				, 'thumb_url' => 'https://ps.w.org/wp-optimize-by-xtraffic/assets/icon.svg'
			)
		);
		
		foreach($arrayPlugins as $key1 => $val1) {
			$val1['file_path_key'] = $val1['wp_plugin_slug'].'/'.$val1['wp_plugin_slug'].'.php';
			$val1['file_path'] = ABSPATH . 'wp-content/plugins/'.$val1['file_path_key'];
			
			$val1['thumb_url'] = 'https://ps.w.org/'.$val1['wp_plugin_slug'].'/assets/icon.svg';
			
			$arrayPlugins[$key1] = $val1;
		}
		
		$iNumber = 0;
		
		foreach($arrayPlugins as $key1 => $val1) {

			$get_plugin_info1 = $this->get_plugin_info(array(
				'slug' => $val1['wp_plugin_slug']
				,'fields' => array(
					'short_description' => true
				)
			));
			
			$check_plugin_status = $this->check_plugin_status($val1['wp_plugin_slug']);
			
			$button['class'] = 'button-primary';
			$button['text'] = '';
			$button['title'] = '';
			$button['href'] = false;
			$button['html'] = '';
			
			$faCircle['class'] = 'fa fa-circle-o';
			$faCircle['title'] = '';
			$faCircle['style'] = 'color:#999;';
			
			if(isset($check_plugin_status['success']['all_valid']) && $check_plugin_status['success']['all_valid']) {
				//installed & actived
				$button['class'] = 'button';
				$button['text'] = 'Deactivate';
				$button['title'] = 'Click here to deactivate this plugin';
				
				
				$button['href'] = add_query_arg(array(
					'xtr-deactivate-plugin-key' => rawurlencode($val1['file_path_key'])
					,'xtr-deactivate-plugin-name' => rawurlencode($val1['name'])
					,'xtr-deactivate-plugin-via' => rawurlencode($this->PLUGIN_SLUG)
					,'page' => 'wpoptimizebyxtraffic_dashboard'
				), $this->currentAdminUrl.'admin.php');
				
				
				$faCircle['class'] = 'fa fa-circle';
				$faCircle['title'] = 'Actived';
				$faCircle['style'] = 'color:green;';
				
			} else if(isset($check_plugin_status['success']['installed']) && $check_plugin_status['success']['installed']) {
				//installed but not active
				
				$button['class'] = 'button-primary';
				$button['text'] = 'Activate';
				$button['title'] = 'Click here to activate this plugin';
				
				
				$button['href'] = add_query_arg(array(
					'xtr-active-plugin-key' => rawurlencode($val1['file_path_key'])
					,'xtr-active-plugin-name' => rawurlencode($val1['name'])
					,'xtr-active-plugin-via' => rawurlencode($this->PLUGIN_SLUG)
					,'page' => 'wpoptimizebyxtraffic_dashboard'
				), $this->currentAdminUrl.'admin.php');
				
			} else if(isset($check_plugin_status['errors']['not_installed']) && $check_plugin_status['errors']['not_installed']) {
				//not_installed
				
				$button['text'] = 'Install';
				$button['title'] = 'Click here to install this plugin';
				
				
				$install_plugin_install_status = $this->install_plugin_install_status(array(
					'slug' => $val1['wp_plugin_slug']
					,'fields' => array()
				));
				
				$button['href'] = $install_plugin_install_status['url'];
			}
			
			if($button['href']) {
				$button['html'] = '<a href="'.$button['href'].'" class="'.$button['class'].'" title="'.$button['title'].'">'.$button['text'].'</a>';
			} else {
				$button['html'] = '<input type="submit" id="" class="'.$button['class'].'" value="'.$button['text'].'" title="'.$button['title'].'" />';
			}
			
			$iNumberMod = $iNumber % 2;
			$resultData .= '
<div id="" class="widget dashboard-widget-xtraffic-plugin " style="margin-right: '.((0 === $iNumberMod) ? '2%;' : '0;').'" >
	<div class="widget-top" style="cursor: initial;">
		
		<div class="widget-title">
			<h4 style="padding-left: 6px;">
				<span class="'.$faCircle['class'].'" title="'.$faCircle['title'].'" style="'.$faCircle['style'].'" ></span>
				<span class="title" style="margin-left: 8px;">'.$val1['name'].' (<a href="'.$val1['wp_plugin_url'].'" target="_blank">Detail</a>)</span>
				<span class="version">'.$get_plugin_info1->version.'</span>
			</h4>
		</div>
	</div>
	
	<div class="module-inside" style="padding: 3%;">
		<div class="info">
			<img src="'.$val1['thumb_url'].'" alt="thumb" style="float: right;max-width: 100%;width: 80px;">
			<p>'.$get_plugin_info1->short_description.'...</p>
			<div class="actions">
				<form method="post" name="component-actions" action="">
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="d023118940" />					
					<div>
						'.$button['html'].'
					</div>
					<div class="clear"></div>
				</form>
			</div>
			
			<div class="clear"></div>
		</div>
	</div>		
</div>
	';
			
			$iNumber++;
		}
		
		return $resultData;
		
	}
	
	
	public function base_dashboard_handle_options()
	{
		
		$pluginPathActived = array();
		if(
			isset($_GET['xtr-active-plugin-key']) && $_GET['xtr-active-plugin-key']
			&& isset($_GET['xtr-active-plugin-name']) && $_GET['xtr-active-plugin-name']
			&& isset($_GET['xtr-active-plugin-via']) && $_GET['xtr-active-plugin-via']
		) {
			if($this->PLUGIN_SLUG === $_GET['xtr-active-plugin-via']) {
				$this->run_activate_plugin($_GET['xtr-active-plugin-key']);
				$this->add_admin_notice_session('success', 'Plugin "<u><b>'.$_GET['xtr-active-plugin-name'].'</b></u>" activated successfully!');
				$pluginPathActived[] = $_GET['xtr-active-plugin-key'];
			}
		}
		
		
		
		$pluginPathDeactived = array();
		if(
			isset($_GET['xtr-deactivate-plugin-key']) && $_GET['xtr-deactivate-plugin-key']
			&& isset($_GET['xtr-deactivate-plugin-name']) && $_GET['xtr-deactivate-plugin-name']
			&& isset($_GET['xtr-deactivate-plugin-via']) && $_GET['xtr-deactivate-plugin-via']
		) {
			if($this->PLUGIN_SLUG === $_GET['xtr-deactivate-plugin-via']) {
				$this->run_deactivate_plugin($_GET['xtr-deactivate-plugin-key']);
				$this->add_admin_notice_session('success', 'Plugin "<u><b>'.$_GET['xtr-deactivate-plugin-name'].'</b></u>" deactivated successfully!');
				$pluginPathDeactived[] = $_GET['xtr-deactivate-plugin-key'];
			}
		}
		
		if(!empty($pluginPathActived) || !empty($pluginPathDeactived)) {
			
			$urlRedirect = $this->currentAdminUrl.'admin.php?page=wpoptimizebyxtraffic_dashboard';
			
			if(!empty($pluginPathDeactived)) {
				$urlRedirect .= '&xtr_reload=1';
			}
			
			header('Location: '.$urlRedirect,true,302);
			echo '<script>window.location = "',$urlRedirect,'";</script>';exit();
			
		} else {
			if(isset($_GET['xtr_reload'])) {
				$urlRedirect = $this->currentAdminUrl.'admin.php?page=wpoptimizebyxtraffic_dashboard';
				
				header('Location: '.$urlRedirect,true,302);
				echo '<script>window.location = "',$urlRedirect,'";</script>';exit();
				
			}
		}
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
		$action_url = $_SERVER['REQUEST_URI'];	
	
		$base_custom_post_types=$options['base_custom_post_types'];
		$base_custom_taxonomies=$options['base_custom_taxonomies'];
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
		
		$rsTemp = $this->base_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		
		$this->display_admin_notices_session();
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2><b>WP Optimize By xTraffic</b></h2>
	
	<div class="wpoptimizebyxtraffic_green_block">
		<h3><b>',__('Thank you for your interest and use plugin!',$this->PLUGIN_SLUG),'</b></h3>
		<p>',__('This plugin will make your wordpress website more useful and powerful via its functions.',$this->PLUGIN_SLUG),'</p>
		<p>',__('More specifically, the plugin will help your website Google-friendly and increase point Google PageSpeed Insights.',$this->PLUGIN_SLUG),'</p>
	</div>
	
	<div id="poststuff" style="margin-top:10px;">
		
		<div id="mainblock" style="">
			
			<div id="module-list">
				<div class="clear"></div>
				',$this->base_dashboard_get_html_blocks_plugins(),'
			</div>
			
		</div>
		
		',$this->base_get_sponsorsblock('vertical_01'),'

	</div>
	
</div>

';	
	}
	
	public function base_remove_shortcodes($input_data)
	{
		$input_data = trim($input_data);
		if($input_data) {
			$rsOne = PepVN_Data::escapeByPattern($input_data, array(
				'pattern' => '#\[\[[^\[\]]+\]\]#is'
				,'target_patterns' => array(
					0
				)
				,'wrap_target_patterns' => ''
			)); 
			
			
			$rsOne['content'] = preg_replace('#\[/?[^\[\]]+\]#i','',$rsOne['content']);
			
			
			if(!PepVN_Data::isEmptyArray($rsOne['patterns'])) {
				$rsOne['content'] = str_replace(array_values($rsOne['patterns']),array_keys($rsOne['patterns']),$rsOne['content']);
			}
			
			$input_data = $rsOne['content']; $rsOne = false;
			$input_data = trim($input_data);
			
		}
		
		return $input_data;
	}
	
	
	
	
	public function admin_menu()
	{
		
		$admin_page = add_menu_page( 
			'WP Optimize By xTraffic'	//page_title
			,'WP Optimize'	//menu_title
			, 'manage_options'	//capability
			, 'wpoptimizebyxtraffic_dashboard'	//menu_slug
			, array( $this, 'base_dashboard_handle_options' )	//function
			, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/images/icon.png')	//icon_url
			, '100.236899629' //position

		);
		
		
		
		// Sub menu pages
		$submenu_pages = array(
			
			array( 
				
				'wpoptimizebyxtraffic_dashboard'
				, 'Dashboard'	//page_title
				, 'Dashboard'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_dashboard'	//menu_slug
				, array( $this, 'base_dashboard_handle_options' )	//function
				
			)
			
			,array( 
				
				'wpoptimizebyxtraffic_dashboard'
				, 'Optimize Links'	//page_title
				, 'Optimize Links'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_links'	//menu_slug
				, array( $this, 'optimize_links_handle_options' )	//function
				
			)
			
			
			, array( 
				'wpoptimizebyxtraffic_dashboard' //parent_slug
				, 'Optimize Images'	//page_title
				, 'Optimize Images'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_images'	//menu_slug
				, array( $this, 'optimize_images_handle_options' )	//function
				, null
			)
			
			, array( 
				'wpoptimizebyxtraffic_dashboard' //parent_slug
				, 'Optimize Traffic'	//page_title
				, 'Optimize Traffic'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_traffic'	//menu_slug
				, array( $this, 'optimize_traffic_handle_options' )	//function
				, null
			)
			
			
			, array( 
				'wpoptimizebyxtraffic_dashboard' //parent_slug
				, 'Header & Footer'	//page_title
				, 'Header & Footer'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_header_footer'	//menu_slug
				, array( $this, 'header_footer_handle_options' )	//function
				, null
			)
			
		);
		
		if ( count( $submenu_pages ) ) {
			foreach ( $submenu_pages as $submenu_page ) {
				// Add submenu page
				$admin_page = add_submenu_page( $submenu_page[0], $submenu_page[1], $submenu_page[2], $submenu_page[3], $submenu_page[4], $submenu_page[5] );
			}
		}
		
		$this->admin_menu_page = $admin_page;
		
	}
	
	
	public function base_get_post_by_id($post_id) 
	{
		$resultData = false;
		
		if($post_id) {
			$post_id = (int)$post_id;
			if($post_id>0) {
				
				$keyCache1 = PepVN_Data::fKey(array(
					__METHOD__
					,$post_id
				));

				$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
				
				if(null === $resultData) {
					
					$resultData = get_post($post_id);
					if($resultData) {
						if(isset($resultData->ID) && $resultData->ID) {
							if(isset($resultData->post_content) && $resultData->post_content) {
								
								$resultData->pepvn_PostImages = array();
								
								$resultData->pepvn_PostContentRawText = $resultData->post_content;
								$resultData->pepvn_PostContentRawText = strip_tags($resultData->pepvn_PostContentRawText);
								$resultData->pepvn_PostContentRawText = PepVN_Data::reduceSpace($resultData->pepvn_PostContentRawText);
								
								$resultData->pepvn_PostPermalink = '';
								$postLink1 = get_permalink( $resultData->ID, false );
								if($postLink1) {
									$resultData->pepvn_PostPermalink = $postLink1;
								}
								
								
								$resultData->pepvn_PostThumbnailId = 0;
								$resultData->pepvn_PostThumbnailUrl = '';
								
								$post_thumbnail_id1 = get_post_thumbnail_id($resultData->ID);
								if($post_thumbnail_id1) {
									$resultData->pepvn_PostThumbnailId = $post_thumbnail_id1;
									$post_thumbnail_url1 = wp_get_attachment_url($post_thumbnail_id1);
									if($post_thumbnail_url1) {
										$resultData->pepvn_PostThumbnailUrl = $post_thumbnail_url1;
										$resultData->pepvn_PostImages[] = $post_thumbnail_url1;
									}
								}
								$resultData->pepvn_PostThumbnailId = (int)$resultData->pepvn_PostThumbnailId;
								$resultData->pepvn_PostThumbnailUrl = trim($resultData->pepvn_PostThumbnailUrl);
								
								
								
								if(isset($resultData->post_excerpt) && $resultData->post_excerpt) {
								} else {
									$resultData->post_excerpt = '';
								}
								
								$resultData->post_excerpt = strip_tags($resultData->post_excerpt);
								$resultData->post_excerpt = PepVN_Data::reduceSpace($resultData->post_excerpt);
								
								$valueTemp = $resultData->post_content;
								if(preg_match_all('#<img[^>]+\\\?>#i', $valueTemp, $matched1)) {
					
									if(isset($matched1[0]) && is_array($matched1[0]) && (count($matched1[0])>0)) {
										
										foreach($matched1[0] as $keyOne => $valueOne) {
											if(preg_match('#src=(\'|")(https?://[^"\']+)\1#i',$valueOne,$matched2)) {
												if(isset($matched2[2]) && $matched2[2]) {
													$resultData->pepvn_PostImages[] = trim($matched2[2]);
												}
												
											}
										}
									}
									
								}
								
								$resultData->pepvn_PostImages = array_unique($resultData->pepvn_PostImages);
								
							}
							
							PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
						}
					}
					
				}
			}
		}
		
		
		return $resultData;
	}
	
	
	public function base_encode_var_for_xtraffic_html5_data_string($input_data) 
	{
		
		if(!$input_data) {
			$input_data = array();
		}
		
		if(!is_array($input_data)) {
			$input_data = (array)$input_data;
		}
		
		$input_data = ' data-wpoptxtr="'.base64_encode(json_encode($input_data)).'" ';
		
		$input_data = (string)$input_data;
		$input_data = trim($input_data);
		
		return $input_data;
	}
	
	
	
	
	public function base_get_ajax_url($input_data_send) 
	{
		$input_data_send = (array)$input_data_send;
		$input_data_send['localTimeSent'] = PepVN_Data::$defaultParams['requestTime'];
		
		$keyDataRequest = WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST;
		return $this->currentAdminAjaxUrl.'?action=wpoptimizebyxtraffic_base_process_ajax_action&'.$keyDataRequest.'='.PepVN_Data::encodeVar($input_data_send).'&__rqid='.PepVN_Data::randomHash();
	}
	
	public function base_process_ajax() 
	{
	
		
		$resultData = array(
			'status' => 1
		);
		
		$checkStatus1 = false;
		
		$dataSent = PepVN_Data::getDataSent();
		
		if($dataSent && isset($dataSent['localTimeSent']) && $dataSent['localTimeSent']) {
			
			if(isset($dataSent['preview_optimize_traffic_modules']) && $dataSent['preview_optimize_traffic_modules']) {
				
				if($this->base_is_current_user_logged_in_can('activate_plugins')) {
					$rsOne = $this->optimize_traffic_preview_optimize_traffic_modules($dataSent);
					
					$resultData = PepVN_Data::mergeArrays(array(
						$resultData
						,$rsOne
					));
				}
			}
			
			
			
			if(isset($dataSent['optimize_databases_actions_run_once']) && $dataSent['optimize_databases_actions_run_once']) {
				
				if($this->base_is_current_user_logged_in_can('activate_plugins')) {
				
					$rsOne = $this->optimize_databases_actions_run_once($dataSent);
					
					$resultData = PepVN_Data::mergeArrays(array(
						$resultData
						,$rsOne
					));
				}
			}
			
			
			if(isset($dataSent['optimize_email_send_test_email']) && $dataSent['optimize_email_send_test_email']) {
				
				if($this->base_is_current_user_logged_in_can('activate_plugins')) {
				
					$rsOne = $this->optimize_email_do_send_test_email($dataSent);
					
					$resultData = PepVN_Data::mergeArrays(array(
						$resultData
						,$rsOne
					));
				}
			}
			
			
			if(isset($dataSent['optimize_backup']) && $dataSent['optimize_backup']) {
				
				$rsOne = $this->optimize_backup_process_ajax($dataSent);
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
				
			}
			
			
			if(isset($dataSent['cronjobs']['status']) && $dataSent['cronjobs']['status']) {
				$rsOne = $this->base_cronjobs();
				
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
				
			}
		}
		
		if(
			isset($resultData['notice']['success'])
			&& ($resultData['notice']['success'])
			&& is_array($resultData['notice']['success'])
		) {
			$resultData['notice']['success'] = array_unique($resultData['notice']['success']);
		}
		
		if(
			isset($resultData['notice']['error'])
			&& ($resultData['notice']['error'])
			&& is_array($resultData['notice']['error'])
		) {
			$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		}
		
		
		echo PepVN_Data::encodeResponseData($resultData);
	}
	
	
	
	
	
	public function base_process_backstage_secure() 
	{
	
		
		$resultData = array(
			'status' => 1
		);
		
		$checkStatus1 = false;
		
		$dataSent = $this->base_get_data_request_secure();
		
		if($dataSent && isset($dataSent['xtr_timestamp_request']) && $dataSent['xtr_timestamp_request']) {
			
			if(isset($dataSent['optimize_backup']) && $dataSent['optimize_backup']) {
				$rsOne = $this->optimize_backup_backstage_process($dataSent);
				
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
			
		}
		
		if(
			isset($resultData['notice']['success'])
			&& ($resultData['notice']['success'])
			&& is_array($resultData['notice']['success'])
		) {
			$resultData['notice']['success'] = array_unique($resultData['notice']['success']);
		}
		
		if(
			isset($resultData['notice']['error'])
			&& ($resultData['notice']['error'])
			&& is_array($resultData['notice']['error'])
		) {
			$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		}
		
		
		$resultData = PepVN_Data::encodeVarForBackstageSecurePHP(array('xtr_data_encrypt' => $resultData));
		$resultData = PepVN_Data::encryptData_Rijndael256($resultData, $this->passwordForEncryptDataBackstageSecure);
		if(!$resultData) {
			$resultData = ''; 
		}
		
		$resultData = (string)$resultData;
		
		
		echo $resultData; exit(); die();
	}
	
	public function base_StaticVar_SafeVarForCronjobs($staticVarData) 
	{
		
		$staticVarData = (array)$staticVarData; 
		
		$fieldsNeedUnset = array(
			'is_processing_base_cronjobs_status'
			,'time_last_process_base_cronjobs'
		);
		
		foreach($fieldsNeedUnset as $key1 => $value1) {
			if($value1) {
				if(isset($staticVarData[$value1])) {
					unset($staticVarData[$value1]);
				}
			}
		}
		
		return $staticVarData;
	}
	
	
	protected function base_get_data_request_secure($input_parameters = false)
	{
		
		$dataSent = false;
		
		if(isset($_POST['xtr_data_encrypt']) && $_POST['xtr_data_encrypt']) {
			$dataSent = $_POST['xtr_data_encrypt'];
			$_POST['xtr_data_encrypt'] = 1;
		} else if(isset($_GET['xtr_data_encrypt']) && $_GET['xtr_data_encrypt']) {
			$dataSent = $_GET['xtr_data_encrypt'];
			$_GET['xtr_data_encrypt'] = 1;
		}
		
		if($dataSent) {
			
			$dataSent = PepVN_Data::decryptData_Rijndael256($dataSent, $this->passwordForEncryptDataBackstageSecure);
			if($dataSent) {
				$dataSent = PepVN_Data::decodeVarForBackstageSecurePHP($dataSent);
				
				if($dataSent) {
					if(
						isset($dataSent['xtr_timestamp_request'])
						&& ($dataSent['xtr_timestamp_request'])
						
						&& isset($dataSent['xtr_timeout_request'])
					) {
						
						$dataSent['xtr_timestamp_request'] = (float)$dataSent['xtr_timestamp_request'];
						$dataSent['xtr_timeout_request'] = (float)$dataSent['xtr_timeout_request'];
						
						if($dataSent['xtr_timestamp_request'] > 0) {
							$checkStatus1 = true;
							
							if($dataSent['xtr_timeout_request'] > 0) {
								if($dataSent['xtr_timestamp_request'] <= ( $this->wpCurrentUserTimestamp - $dataSent['xtr_timeout_request'])) {
									$checkStatus1 = false;
								}
							}
							
							if($checkStatus1) {
								return $dataSent;
							}
							
						}
						
					}
				}
			}
		}
		
		return false;
	}
	
	
	protected function base_request_secure($input_parameters)
	{
		$resultData = array();
		
		$resultData['response_data'] = '';
		
		if(
			isset($input_parameters['url'])
			&& ($input_parameters['url'])
		) {
			if(!isset($input_parameters['request_options'])) {
				$input_parameters['request_options'] = array();
			}
			
			$input_parameters['request_options'] = (array)$input_parameters['request_options'];
			
			
			if(!isset($input_parameters['data_send'])) {
				$input_parameters['data_send'] = array();
			}
			
			$input_parameters['data_send'] = (array)$input_parameters['data_send'];
			$input_parameters['data_send']['xtr_timestamp_request'] = $this->wpCurrentUserTimestamp; 
			$input_parameters['data_send']['xtr_timeout_request'] = 6 * 3600;
			
			$input_parameters['data_send'] = PepVN_Data::encodeVarForBackstageSecurePHP($input_parameters['data_send']);
			
			$input_parameters['data_send'] = PepVN_Data::encryptData_Rijndael256($input_parameters['data_send'], $this->passwordForEncryptDataBackstageSecure);
			if($input_parameters['data_send']) {
				
				$input_parameters['request_options']['body'] = array(
					'xtr_data_encrypt' => $input_parameters['data_send']
				);
				$input_parameters['data_send'] = 0;
				
				if(isset($input_parameters['request_options']['timeout']) && $input_parameters['request_options']['timeout']) {
				} else {
					$input_parameters['request_options']['timeout'] = 6;
				}
				$input_parameters['request_options']['timeout'] = (int)$input_parameters['request_options']['timeout'];
				
				$input_parameters['request_options']['redirection'] = 9;
				$input_parameters['request_options']['method'] = 'POST';
				
				$resultData['response_data'] = $this->base_get_valid_body_from_request_url_response( wp_remote_post( $input_parameters['url'], $input_parameters['request_options'] ));  
				
				$input_parameters = 0;
			}
			
		}
		
		return $resultData;
	}
	
	
	public function base_get_valid_body_from_request_url_response($response) 
	{
		$resultData = '';
		
		if($response && (!is_wp_error($response))) { 
			
			if(isset($response['body']) && $response['body']) {
				$isOkStatus = true;
				if(isset($response['response']['code']) && $response['response']['code']) {
					$response['response']['code'] = (int)$response['response']['code'];
					if(200 !== $response['response']['code']) {
						$isOkStatus = false;
					}
				}
				
				if($isOkStatus) {
					$resultData = $response['body'];  
				}
				
			}
			
		}
		
		$resultData = (string)$resultData;
		
		return $resultData;
		
	}
	
	
	public function base_cronjobs() 
	{
	
		$resultData = array();
		$resultData['cronjobs_status'] = 0;
		
		$staticVarData = PepVN_Data::staticVar_GetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, false);
		
		$doCronjobsStatus = true;
		
		
		if($doCronjobsStatus) {
			if(isset($staticVarData['time_last_process_base_cronjobs']) && $staticVarData['time_last_process_base_cronjobs']) {
				$doCronjobsStatus = false;
				if(($staticVarData['time_last_process_base_cronjobs'] + (5 * 60)) < PepVN_Data::$defaultParams['requestTime']) {	//is timeout 
					$doCronjobsStatus = true;
				}
			}
		}
		
		
		if($doCronjobsStatus) {
			if(isset($staticVarData['is_processing_base_cronjobs_status']) && $staticVarData['is_processing_base_cronjobs_status']) {
				
				$doCronjobsStatus = false;
				
				if(isset($staticVarData['time_last_process_base_cronjobs']) && $staticVarData['time_last_process_base_cronjobs']) {
					if(($staticVarData['time_last_process_base_cronjobs'] + (3 * 3600)) < PepVN_Data::$defaultParams['requestTime']) {	//is timeout
						$doCronjobsStatus = true;
					}
				}
				
			}
		}
		
		if($doCronjobsStatus) {
			
			$staticVarData['time_last_process_base_cronjobs'] = PepVN_Data::$defaultParams['requestTime'];
			$staticVarData['is_processing_base_cronjobs_status'] = 1;
			
			PepVN_Data::staticVar_SetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, $staticVarData, 'm');
			
			$resultData['cronjobs_status'] = 1;
			
			
			/*
			* Begin process cronjobs actions
			*/
			
			
			//$this->optimize_databases_do_scheduled_actions(); 
			sleep( 1 );
			
			
			//prebuild_cache
			global $wpOptimizeSpeedByxTraffic;
			if(isset($wpOptimizeSpeedByxTraffic) && $wpOptimizeSpeedByxTraffic) {
				$rsOne = $wpOptimizeSpeedByxTraffic->optimize_cache_prebuild_urls_cache();
				$resultData = array_merge($resultData, $rsOne);
				$rsOne = 0;
				sleep(1);
			}
			
			//$this->optimize_backup_do_scheduled_actions(); 
			sleep( 1 ); 
			
			
			
			
			/*
			* End process cronjobs actions
			*/
			
			
			$staticVarData['time_last_process_base_cronjobs'] = PepVN_Data::$defaultParams['requestTime'];
			$staticVarData['is_processing_base_cronjobs_status'] = 0;
			
			PepVN_Data::staticVar_SetData(WPOPTIMIZEBYXTRAFFIC_KEY_STATIC_VAR_BASE_CRONJOBS, $staticVarData, 'm');
			
		}
		
		return $resultData;
		
	}
	
	
	public function base_is_has_curl() 
	{
		$keyCache1 = 'base_is_has_curl';
		
		if(!isset($this->baseCacheData[$keyCache1])) {
			if(function_exists('curl_init')) {
				$this->baseCacheData[$keyCache1] = true;
			} else {
				$this->baseCacheData[$keyCache1] = false;
			}
		}
		
		return $this->baseCacheData[$keyCache1];
	}
	
	public function quickGetUrlContent_ViaCurl($input_url, $input_args = false) 
	{
		$resultData = '';
		
		if($this->base_is_has_curl()) {
			$connect_timeout = $input_args['request_timeout'];
			
			$opts_Headers = array();
			
			$opts_Headers['user-agent'] = 'User-Agent: '.$this->http_UserAgent;
			$opts_Headers['accept'] = 'Accept: */*;';
			$opts_Headers['accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
			$opts_Headers['accept-encoding'] = 'Accept-Encoding: gzip,deflate';
			$opts_Headers['accept-charset'] = 'Accept-Charset: UTF-8,*;';
			$opts_Headers['keep-alive'] = 'Keep-Alive: 60';
			$opts_Headers['connection'] = 'Connection: keep-alive';
			
			$opts_Headers = array();
			$opts_Headers[] = 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
			$opts_Headers[] = 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36';
			
			if(isset($input_args['referer_url'])) {
				$opts_referer_url = $input_args['referer_url'];
			} else {
				$opts_referer_url = $input_url;
				$rs_parseUrl = PepVN_Data::parseUrl($opts_referer_url);
				if(isset($rs_parseUrl['root']) && ($rs_parseUrl['root'])) {
					$opts_referer_url = trim($rs_parseUrl['root']);
				}
			}
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $input_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLINFO_HEADER_OUT, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->http_UserAgent);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($opts_Headers));
			curl_setopt($ch, CURLOPT_TIMEOUT, $connect_timeout);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 6); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 3600);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			
			if(isset($input_args['cookies']) && ($input_args['cookies'])) {
				$cookiesTemp = array();
				foreach($input_args['cookies'] as $key1 => $val1) {
					$cookiesTemp[] = $key1.'='.$val1;
				}
				if(!empty($cookiesTemp)) {
					curl_setopt($ch, CURLOPT_COOKIE, implode(';',$cookiesTemp));
				}
				$cookiesTemp = 0;
			}
			
			
			if($opts_referer_url) {
				curl_setopt($ch, CURLOPT_REFERER, $opts_referer_url);
			}
			
			$resultData = curl_exec($ch);
			
			if(curl_errno($ch) || !$resultData) {
				$resultData = '';
				$resultData = array($input_url, $input_args);
			}
			curl_close($ch);
			$ch = 0; unset($ch);
			
		}
		
		return $resultData;
		
		
	}
	
	public function quickGetUrlContent($input_url, $input_args = false) 
	{
		if(!$input_args) {
			$input_args = array();
		}
		
		if(preg_match('#^//.+#i',$input_url,$matched1)) {
			$input_url = 'http:'.$input_url;
		}
		
		$request_timeout = 6;
		if(isset($input_args['request_timeout']) && $input_args['request_timeout']) {
			$request_timeout = $input_args['request_timeout'];
		}
		$request_timeout = (int)$request_timeout;
		if($request_timeout < 1) {
			$request_timeout = 1;
		}
		$input_args['request_timeout'] = $request_timeout;
		
		$cacheTimeout = 0;
		
		if(isset($input_args['cache_timeout'])) {
			$cacheTimeout = (int)$input_args['cache_timeout'];
			unset($input_args['cache_timeout']);
		}
		
		$cacheTimeout = (int)$cacheTimeout;
		
		if($cacheTimeout > 0) {
			$keyCache1 = PepVN_Data::fKey(array(
				__METHOD__
				,$input_url
			));
			
			$resultData = $this->cacheObject_GetUri->get_cache($keyCache1);
			if(null !== $resultData) {
				return $resultData;
			}
		}
		
		
		$get_via_method = false;
		
		if(isset($input_args['get_via_method'])) {
			if('curl' === $input_args['get_via_method']) {
				$get_via_method = $input_args['get_via_method'];
			}
			unset($input_args['get_via_method']);
		}
		
		$isHasCurlStatus = false;
		
		if($this->base_is_has_curl()) {
			$isHasCurlStatus = true; 
		}
		
		if(
			$isHasCurlStatus
			&& ('curl' === $get_via_method)
		) {
			
			$resultData = $this->quickGetUrlContent_ViaCurl($input_url, $input_args);
			if($resultData) {
				if($cacheTimeout > 0) {
					$this->cacheObject_GetUri->set_cache($keyCache1, $resultData);
				}
			}
			
			return $resultData;
			
		} else {
			
			$args1 = array(
				'timeout'     => $input_args['request_timeout'],
				'redirection' => 9,
				//'httpversion' => '1.0',
				'user-agent'  => $this->http_UserAgent,
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
				//'body'        => null,
				'compress'    => true,
				'decompress'  => true,
				'sslverify'   => false,
				//'stream'      => false,
				'filename'    => null
			);
			
			foreach($input_args as $key1 => $value1) {
				$args1[$key1] = $value1;
			}
			$input_args = 0;
			
			$objWPHttp = new WP_Http_Streams();
			$resultData = $objWPHttp->request($input_url, $args1);
			
			if($resultData && is_array($resultData)) {
				if(isset($resultData['body']) && $resultData['body']) {
					$isOkStatus = true;
					if(isset($resultData['response']['code']) && $resultData['response']['code']) {
						$resultData['response']['code'] = (int)$resultData['response']['code'];
						if(200 !== $resultData['response']['code']) {
							$isOkStatus = false;
						}
					}
					
					if($isOkStatus) {
						if($cacheTimeout > 0) {
							$this->cacheObject_GetUri->set_cache($keyCache1, $resultData['body']);
						}
						return $resultData['body'];  
					}
					
				}
			}
			
			if($this->base_is_has_curl()) {
				$resultData = $this->quickGetUrlContent_ViaCurl($input_url, $input_args);
				if($resultData) {
					if($cacheTimeout > 0) {
						$this->cacheObject_GetUri->set_cache($keyCache1, $resultData);
					}
					return $resultData;
				}
			}
		}
		
		
		$resultData = '';
		return $resultData; 
		
		
	}


}



endif;

