<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Images.php');


if ( !class_exists('WPOptimizeByxTraffic_Base') ) :


class WPOptimizeByxTraffic_Base {
	
	// Name for our options in the DB
	protected $wpOptimizeByxTraffic_DB_option = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	protected $wpOptimizeByxTraffic_options; 
	
	protected $cacheObj;
	
	function __construct() 
	{
	
		$doActions = array();
		
		$this->cacheObj = new PepVN_Cache();
		$this->cacheObj->cache_time = 86400;
		
		$options = $this->get_options();
		if ($options) {
			
			if ($options['optimize_links_process_in_post'] || $options['optimize_links_process_in_page']) {	
				add_filter('the_content',  array(&$this, 'optimize_links_the_content_filter'), 10);	
				
			}
			
			add_filter('the_content',  array(&$this, 'optimize_images_the_content_filter'), 11);
			
			
			
			if ($options['optimize_links_process_in_comment']) {
				add_filter('comment_text',  array(&$this, 'optimize_links_comment_text_filter'), 10);	
			}
			
			
			
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
			add_action('admin_notices', array(&$this,'admin_notice'));
		}
		
		if(isset($doActions['updateOptions']) && $doActions['updateOptions']) {
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		}
		
		if(isset($doActions['base_clear_data']) && $doActions['base_clear_data']) {
			$this->base_clear_data();
		}
		
		
		$this->fullDomainName = '';
		$parseUrl = parse_url(get_bloginfo('wpurl'));
		if(isset($parseUrl['host']) && $parseUrl['host']) {
			$this->fullDomainName = $parseUrl['host'];
			
		}
		
	}
	
		
	function explode_trim($separator, $text)
	{
		$arr = explode($separator, $text);
		
		$ret = array();
		foreach($arr as $e)
		{        
		  $ret[] = trim($e);        
		}
		return $ret;
	}
	
	
	
	
	// Handle our options
	function get_options() 
	{

		$rs_parse_url = parse_url(get_bloginfo('wpurl'));
		
		
		$options = array(
			
			//optimize_links Setting
			
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
			'optimize_links_ignore' => 'about,', 
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
			
			//optimize_images Setting
			
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
			'optimize_images_maximum_files_handled_each_request' => 0,
			'optimize_images_handle_again_files_different_configuration_enable' => '',//on
			'optimize_images_remove_files_available_different_configuration_enable' => 'on',//on
			
			
			
			//General Setting
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
		

		return $options;

	}

	
	function base_clear_data()
	{
		
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
				$pathTemp1 = realpath($path1);
				if($pathTemp1 && file_exists($pathTemp1)) {
					PepVN_Data::rrmdir($pathTemp1);
					
					$pathTemp1 = $path1;
					$pathTemp1 = realpath($pathTemp1);
					if($pathTemp1 && file_exists($pathTemp1)) {
					} else {
						PepVN_Data::createFolder($path1, WPOPTIMIZEBYXTRAFFIC_CHMOD);
						PepVN_Data::chmod($path1,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
					}
				}
				
			}
		}
		
		
		$globPaths = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'files'.DIRECTORY_SEPARATOR;
		$globPaths = glob($globPaths."*.*");
		
		
		if($globPaths && (count($globPaths)>0)) {
			$timeout1 = 86400 * 3;
			foreach ($globPaths as $filename) {
				$filename = realpath($filename);
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
	
	function handle_options()
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
					,'optimize_links_ignore'
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
		
			
			
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
			
			
			echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
			
			$this->base_clear_data();
			
		}
		
		$resultData = array(
			'options' => $options
		);
		
		return $resultData;
		
	}
	

	function admin_notice() 
	{
		
		
		
	}
	
	
	function wpoptimizebyxtraffic_admin_menu()
	{
		
		$admin_page = add_menu_page( 
			'WP Optimize By xTraffic'	//page_title
			,'WP Optimize'	//menu_title
			, 'manage_options'	//capability
			, 'wpoptimizebyxtraffic_optimize_links'	//menu_slug
			, array( $this, 'optimize_links_handle_options' )	//function
			, plugins_url( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/images/icon.png')	//icon_url
			, '100.236899629' //position

		);
		
		
		
		// Sub menu pages
		$submenu_pages = array(
			
			array( 
				
				'wpoptimizebyxtraffic_optimize_links'
				, 'Optimize Links'	//page_title
				, 'Optimize Links'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_links'	//menu_slug
				, array( $this, 'optimize_links_handle_options' )	//function
				
			),
			
			array( 
				'wpoptimizebyxtraffic_optimize_links' //parent_slug
				, 'Optimize Images'	//page_title
				, 'Optimize Images'	//menu_title
				, 'manage_options'	//capability
				, 'wpoptimizebyxtraffic_optimize_images'	//menu_slug
				, array( $this, 'optimize_images_handle_options' )	//function
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
		
		$objWPHttp = new WP_Http();
		$resultData = $objWPHttp->get($input_url, $args1);
		
		if($resultData && is_array($resultData)) {
			if(isset($resultData['body']) && $resultData['body']) {
				return $resultData['body']; 
			}
		}
		
		
		
		$resultData = '';
		return $resultData;
		
		
	}


}



endif;



?>