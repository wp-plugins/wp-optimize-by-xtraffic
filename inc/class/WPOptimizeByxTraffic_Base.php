<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Images.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_CSSmin.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_JSMin.php'); 


if ( !class_exists('WPOptimizeByxTraffic_Base') ) :


class WPOptimizeByxTraffic_Base {
	
	// Name for our options in the DB
	protected $wpOptimizeByxTraffic_DB_option = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	protected $wpOptimizeByxTraffic_options; 
	
	protected $cacheObj;
	
	protected $baseCacheData = array();
	
	protected $adminNoticesData = array();
	
	function __construct() 
	{
		
		$cachePathTemp = WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH;
		if($cachePathTemp && file_exists($cachePathTemp)) {
			
		} else {
			PepVN_Data::createFolder($cachePathTemp, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($cachePathTemp,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD); 
		}
		
		$cachePathTemp = PEPVN_CACHE_DATA_DIR;
		if($cachePathTemp && file_exists($cachePathTemp)) {
			
		} else {
			PepVN_Data::createFolder($cachePathTemp, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($cachePathTemp,WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD); 
		}
		
		
		
		$priorityFirst = 0 + (mt_rand() / 1000000000);
		$priorityFirst = (float)$priorityFirst;
		
		$priorityLast = 999999999.999999999 + (mt_rand() / 1000000000);
		$priorityLast = (float)$priorityLast;
		
	
	
		$doActions = array();
		
		$this->cacheObj = new PepVN_Cache();
		$this->cacheObj->cache_time = 86400;
		
		$options = $this->get_options();
		if ($options) {
			
			if ($options['optimize_links_process_in_post'] || $options['optimize_links_process_in_page']) {	
				add_filter('the_content',  array(&$this, 'optimize_links_the_content_filter'), $priorityLast);	
				
			}
			
			add_filter('the_content',  array(&$this, 'optimize_images_the_content_filter'), $priorityLast);
			
			
			
			if ($options['optimize_links_process_in_comment']) {
				add_filter('comment_text',  array(&$this, 'optimize_links_comment_text_filter'), $priorityLast);	
			}
			
			
			
			
			add_filter('wp_head',  array(&$this, 'header_footer_the_head_filter'), $priorityLast);
			add_filter('wp_footer',  array(&$this, 'header_footer_the_footer_filter'), $priorityLast);
			add_filter('the_content',  array(&$this, 'header_footer_the_content_filter'), $priorityLast);
			
			
			
			
			
			//clear cache
			if(!isset($options['last_time_clear_cache'])) {
				$options['last_time_clear_cache'] = 0;
			}
			$options['last_time_clear_cache'] = (int)$options['last_time_clear_cache'];
			if((time() - $options['last_time_clear_cache']) > (86400 * 7)) {
				
				
				$options['last_time_clear_cache'] = time();
				
				$doActions['updateOptions'] = 1;
				
				$doActions['base_clear_data'] = 1;
				
			}
			
			
			
			
			
		}
		
		// Add Options Page
		add_action('admin_menu',  array(&$this, 'wpoptimizebyxtraffic_admin_menu'));

		if (isset($options['notice']) && $options['notice']) {
			$adminNoticesData[] = $options['notice'];
		}
		
		if(isset($doActions['updateOptions']) && $doActions['updateOptions']) {
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		}
		
		if(isset($doActions['base_clear_data']) && $doActions['base_clear_data']) {
			$this->base_clear_data(',all,');
		}
		
		
		$this->fullDomainName = '';
		$parseUrl = parse_url(get_bloginfo('wpurl'));
		if(isset($parseUrl['host']) && $parseUrl['host']) {
			$this->fullDomainName = $parseUrl['host'];
			
		}
		
		$this->urlProtocol = 'http:';
		if(PepVN_Data::is_ssl()) {
			$this->urlProtocol = 'https:';
		}
		
		
		add_action('admin_notices', array(&$this,'admin_notice'));
		
		//add_action('save_post', array(&$this,'base_clear_data'));
		
	}
	
	
	
	
	public function base_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		/*
		*	error_no : 2x - Images ; 3x : Speed
		*/
		$resultData['notice']['error_no'] = array();
		
		
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
				$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp1.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			}
			
			
			
			$cachePathTemp1 = PEPVN_CACHE_DATA_DIR;
			if(
				$cachePathTemp1
				&& file_exists($cachePathTemp1)
				&& PepVN_Data::isAllowReadAndWrite($cachePathTemp1)
			) {
				
			} else {
				$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp1.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
			}
			
			
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server should set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$cachePathTemp.'</b>" '.__('to achieve maximum performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</div>';
		}
		
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	public function base_get_sponsorsblock($input_type='vertical_01')
	{
		$resultData = '';
		
		if('statistics' === $input_type) {
			
		} else if('vertical_01' === $input_type) {
			$resultData .= '<div id="sideblock" style="float:right;width:290px;margin-left:10px;"><iframe width="290" height="1000" frameborder="0" src="//static.pep.vn/library/pepvn/wp-optimize-by-xtraffic/client/vertical_01.html?utm_source='.rawurlencode($this->fullDomainName).'&utm_medium=plugin-wp-optimize-by-xtraffic&utm_campaign=WP+Optimize+By+xTraffic"></iframe></div>';
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
	
	
	
	
	// Handle our options
	public function get_options($input_parameters = false) 
	{
		
		if(!$input_parameters) {
			$input_parameters = array();
		}
		
		$keyCache1 = md5('base_get_options');
		
		if(isset($input_parameters['cache_status']) && $input_parameters['cache_status']) {
			
			if(isset($this->baseCacheData[$keyCache1]) && $this->baseCacheData[$keyCache1]) {
				return $this->baseCacheData[$keyCache1];
			}
			
		}
		
		
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
			
			'optimize_images_alttext' => '%img_name %title',
			'optimize_images_titletext' => '',
			
			'optimize_images_override_alt' => 'on',//on
			'optimize_images_override_title' => '',//on
			
			//watermark image
			'optimize_images_watermarks_enable' => '',//on
			'optimize_images_watermarks_watermark_position' => 'bottom_right',
			
			'optimize_images_watermarks_watermark_opacity_value' => 100,
			'optimize_images_watermarks_watermark_type' => 'text',
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
			'optimize_images_maximum_files_handled_each_request' => 2,
			'optimize_images_handle_again_files_different_configuration_enable' => '',//on
			'optimize_images_remove_files_available_different_configuration_enable' => 'on',//on
			
			
			
			
			
			/*
			* Optimize Speed Setting
			*/
			
			
			//optimize_javascript
			'optimize_speed_optimize_javascript_enable' => '',//on
			'optimize_speed_optimize_javascript_combine_javascript_enable' => 'on',//on
			'optimize_speed_optimize_javascript_minify_javascript_enable' => 'on',//on
			'optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable' => 'on',//on
			'optimize_speed_optimize_javascript_exclude_external_javascript_enable' => '',//on
			'optimize_speed_optimize_javascript_exclude_inline_javascript_enable' => 'on',//on
			'optimize_speed_optimize_javascript_exclude_url' => 'alexa.com,',//text
			
			
			//optimize_css
			'optimize_speed_optimize_css_enable' => '',//on
			'optimize_speed_optimize_css_combine_css_enable' => 'on',//on
			'optimize_speed_optimize_css_minify_css_enable' => 'on',//on
			'optimize_speed_optimize_css_asynchronous_css_loading_enable' => 'on',//on
			'optimize_speed_optimize_css_exclude_external_css_enable' => '',//on
			'optimize_speed_optimize_css_exclude_inline_css_enable' => '',//on
			'optimize_speed_optimize_css_exclude_url' => '',//text
			
			
			//optimize_html
			'optimize_speed_optimize_html_enable' => '',//on
			'optimize_speed_optimize_html_minify_html_enable' => 'on',//on
			
			
			
			
			
			
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

		$saved = get_option($this->wpOptimizeByxTraffic_DB_option); 


		if (!empty($saved)) {
			foreach ($saved as $key => $option) {
				$options[$key] = $option;
			}
		}

		if ($saved != $options)	{
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		}
		
		$this->baseCacheData[$keyCache1] = $options;

		return $options;

	}

	
	public function base_clear_data($input_action='')
	{
		
		$input_action = (array)$input_action;
		$input_action = ','.implode(',',$input_action).',';
		
		$timestampNow = time();
		$timestampNow = (int)$timestampNow;
		
		
		
		$cachePath = PEPVN_CACHE_DATA_DIR.'s'.DIRECTORY_SEPARATOR;
		
		$arrayPaths = array();
		$arrayPaths[] = $cachePath;
		if(isset($this->pepvn_UploadsPreviewImgFolderPath) && $this->pepvn_UploadsPreviewImgFolderPath) {
			$arrayPaths[] = $this->pepvn_UploadsPreviewImgFolderPath;
		}
		
		foreach($arrayPaths as $path1) {
			if($path1) {
				$pathTemp1 = $path1;
				
				if($pathTemp1 && file_exists($pathTemp1)) {
					PepVN_Data::rrmdir($pathTemp1);
					
					$pathTemp1 = $path1;
					
					if($pathTemp1 && file_exists($pathTemp1)) {
					} else {
						PepVN_Data::createFolder($path1, WPOPTIMIZEBYXTRAFFIC_CHMOD);
						PepVN_Data::chmod($path1,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
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
				,WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN.'static-files'.DIRECTORY_SEPARATOR
			);
			
			foreach($arrayPathFilesNeedCheck as $key1 => $value1) {
				if($value1) {
					if(file_exists($value1)) {
						if(PepVN_Data::is_writable($value1)) {
							
							
							$globPaths = glob($value1."*.*");
							
							
							if($globPaths && (count($globPaths)>0)) {
								$timeout1 = 86400 * 3; 
								foreach ($globPaths as $filename) {
									
									if($filename && file_exists($filename)) {
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
		
		
		
	}
	
	public function handle_options()
	{
		
		
		$options = $this->get_options();
		
		if (isset($_GET['notice'])) {
			if ($_GET['notice']==1) {
				$options['notice']=0;
				update_option($this->wpOptimizeByxTraffic_DB_option, $options);
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
					'optimize_links_process_in_post'
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
					
					
					//watermark image
					,'optimize_images_watermarks_enable'
					,'optimize_images_watermarks_watermark_position'
					,'optimize_images_watermarks_watermark_opacity_value'
					,'optimize_images_watermarks_watermark_type'
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
				
			}
			
			
			
			//optimize_speed
			if ( isset($_POST['optimize_speed_submitted']) ) {
				
				$arrayFields1 = array(
					
					//optimize_javascript
					'optimize_speed_optimize_javascript_enable'
					,'optimize_speed_optimize_javascript_combine_javascript_enable'
					,'optimize_speed_optimize_javascript_minify_javascript_enable'
					,'optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable'
					,'optimize_speed_optimize_javascript_exclude_external_javascript_enable'
					,'optimize_speed_optimize_javascript_exclude_inline_javascript_enable'
					,'optimize_speed_optimize_javascript_exclude_url'
					
					//optimize_css
					,'optimize_speed_optimize_css_enable'
					,'optimize_speed_optimize_css_combine_css_enable'
					,'optimize_speed_optimize_css_minify_css_enable'
					,'optimize_speed_optimize_css_asynchronous_css_loading_enable'
					,'optimize_speed_optimize_css_exclude_external_css_enable'
					,'optimize_speed_optimize_css_exclude_inline_css_enable'
					,'optimize_speed_optimize_css_exclude_url'
					
					
					//optimize_html
					,'optimize_speed_optimize_html_enable'
					,'optimize_speed_optimize_html_minify_html_enable'
					
				);
				
				foreach($arrayFields1 as $key1 => $value1) {
					if(isset($_POST[$value1])) {
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
				
				$options['optimize_speed_optimize_javascript_exclude_url'] = preg_replace('#[\'\"]+#','',$options['optimize_speed_optimize_javascript_exclude_url']);
				$options['optimize_speed_optimize_css_exclude_url'] = preg_replace('#[\'\"]+#','',$options['optimize_speed_optimize_css_exclude_url']);
				
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
						$options[$value1] = $_POST[$value1];
					} else {
						$options[$value1] = '';
					}
				}
			}
		
			
			
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
			
			
			echo '<div class="updated fade"><p><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Options saved',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</p></div>';
			
			$this->base_clear_data(',all,');
			
		}
		
		$resultData = array(
			'options' => $options
		);
		
		return $resultData; 
		
	}
	

	public function admin_notice() 
	{
		
		$this->adminNoticesData = array_unique($this->adminNoticesData);
		
		if(!PepVN_Data::isEmptyArray($this->adminNoticesData)) {
			foreach($this->adminNoticesData as $keyOne => $valueOne) {
				echo $valueOne;
				unset($this->adminNoticesData[$keyOne]);
			}
		}
		
	}
	
	
	
	

	public function base_dashboard_handle_options()
	{
		
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
		
		
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2>WP Optimize By xTraffic (General Settings)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="base_dashboard_submitted" value="1" /> 
						
						<h2>',__('Settings',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h2>
						
						<br />
												
						<h5>',__('Custom Post Types',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>	
						
						<p>',__('You may wish to add the "Custom Post Types" so plugin can process them. Separate them by comma. (ignored this if you do not know).',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <a href="http://codex.wordpress.org/Post_Types#Custom_Post_Types" target="_blank">Learn more about "Custom Post Types" here</a></p>
						<input type="text" name="base_custom_post_types" size="255" value="',$base_custom_post_types,'" style="max-width:660px;" /> 
						<br /><br /><hr /><br />
						
						
						
						<h5>',__('Custom Taxonomies',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>	
						
						<p>',__('You may wish to add the "Custom Taxonomies" so plugin can process them. Separate them by comma. (ignored this if you do not know).',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <a href="http://codex.wordpress.org/Taxonomies#Custom_Taxonomies" target="_blank">Learn more about "Custom Taxonomies" here</a></p>
						<input type="text" name="base_custom_taxonomies" size="255" value="',$base_custom_taxonomies,'" style="max-width:660px;" /> 
						<br /><br /><hr /><br />
						
						
						<div class="submit"><input type="submit" name="Submit" value="',__('Update Options',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" class="button-primary" /></div>
						
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

'; 
		
		
	}
	
	
	
	public function wpoptimizebyxtraffic_admin_menu()
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
				, 'Optimize Speed'	//page_title
				, 'Optimize Speed'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_speed'	//menu_slug
				, array( $this, 'optimize_speed_handle_options' )	//function
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
		
		
		
	}
	
	
	function quickGetUrlContent($input_url, $input_args = false) 
	{
		if(!$input_args) {
			$input_args = array();
		}
		
		
		
		
		if(preg_match('#^//.+#i',$input_url,$matched1)) {
			$input_url = 'http:'.$input_url;
		}
		
		$cacheTimeout = 0;
		
		if(isset($input_args['cache_timeout'])) {
			$cacheTimeout = (int)$input_args['cache_timeout'];
			unset($input_args['cache_timeout']);
		}
		
		$cacheTimeout = (int)$cacheTimeout;
		
		if($cacheTimeout > 0) {
			$keyCache1 = PepVN_Data::createKey(array(
				__METHOD__
				,$input_url
			));
			
			$resultData = $this->cacheObj->get_cache($keyCache1);
			if($resultData) {
				return $resultData;
			}
		}
		
		$args1 = array(
			'timeout'     => 6,
			'redirection' => 9,
			//'httpversion' => '1.0',
			'user-agent'  => 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36',//'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			'blocking'    => true,
			'headers'     => array(),
			'cookies'     => array(),
			//'body'        => null,
			'compress'    => true,
			'decompress'  => true,
			'sslverify'   => false,
			//'stream'      => false,
			//'filename'    => null
		);
		
		foreach($input_args as $key1 => $value1) {
			$args1[$key1] = $value1;
		}
		
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
						$this->cacheObj->set_cache($keyCache1, $resultData['body']);
					}
					return $resultData['body'];  
				}
				
			}
		}
		
		
		
		$resultData = '';
		return $resultData;
		
		
	}


}



endif;



?>