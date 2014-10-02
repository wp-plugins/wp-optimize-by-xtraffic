<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');


if ( !class_exists('WPOptimizeByxTraffic_Base') ) :


class WPOptimizeByxTraffic_Base {
	
	// Name for our options in the DB
	protected $wpOptimizeByxTraffic_DB_option = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	protected $wpOptimizeByxTraffic_options; 
	
	protected $cacheObj;
	
	function __construct() 
	{
	
		
		
		$this->cacheObj = new PepVN_Cache();
		$this->cacheObj->cache_time = 86400;
		
		$doUpdateOptionsStatus = false; 
		
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
				//$this->clear_cache();
				
				$options['last_time_clear_cache'] = time();
				
				
				$doUpdateOptionsStatus = true;
			}
		}
		
		// Add Options Page
		add_action('admin_menu',  array(&$this, 'wpoptimizebyxtraffic_admin_menu'));

		if ($options['notice']) {
			add_action('admin_notices', array(&$this,'admin_notice'));
		}
		
		if($doUpdateOptionsStatus) {
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
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

	
	function clear_cache()
	{
		$cachePath = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH;
		$cacheTempPath = $cachePath.'temp/';
		if($cacheTempPath && file_exists($cacheTempPath)) {
			PepVN_Data::rrmdir($cacheTempPath);
			
			$cacheTempPath1 = $cacheTempPath;
			$cacheTempPath1 = realpath($cacheTempPath1);
			if($cacheTempPath1 && file_exists($cacheTempPath1)) {
			} else {
				mkdir($cacheTempPath, WPOPTIMIZEBYXTRAFFIC_CHMOD, true);
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
				
				$options['optimize_links_process_in_post']=$_POST['optimize_links_process_in_post'];					
				$options['optimize_links_allow_link_to_postself']=$_POST['optimize_links_allow_link_to_postself'];					
				$options['optimize_links_process_in_page']=$_POST['optimize_links_process_in_page'];					
				$options['optimize_links_allow_link_to_pageself']=$_POST['optimize_links_allow_link_to_pageself'];					
				$options['optimize_links_process_in_comment']=$_POST['optimize_links_process_in_comment'];					
				$options['optimize_links_excludeheading']=$_POST['optimize_links_excludeheading'];									
				$options['optimize_links_link_to_posts']=$_POST['optimize_links_link_to_posts'];
				$options['optimize_links_link_to_pages']=$_POST['optimize_links_link_to_pages'];					
				$options['optimize_links_link_to_cats']=$_POST['optimize_links_link_to_cats'];					
				$options['optimize_links_link_to_tags']=$_POST['optimize_links_link_to_tags'];					
				$options['optimize_links_ignore']=$_POST['optimize_links_ignore'];	
				$options['optimize_links_ignorepost']=$_POST['optimize_links_ignorepost'];					
				$options['optimize_links_maxlinks']=(int) $_POST['optimize_links_maxlinks'];					
				$options['optimize_links_maxsingle']=(int) $_POST['optimize_links_maxsingle'];					
				$options['optimize_links_maxsingleurl']=(int) $_POST['optimize_links_maxsingleurl'];
				$options['optimize_links_minusage']=(int) $_POST['optimize_links_minusage'];			// credit to Dominik Deobald		
				$options['optimize_links_customkey']=$_POST['optimize_links_customkey'];	
				$options['optimize_links_customkey_url']=$_POST['optimize_links_customkey_url'];
				$options['optimize_links_customkey_preventduplicatelink']=$_POST['optimize_links_customkey_preventduplicatelink'];
				//$options['optimize_links_nofoln']=$_POST['optimize_links_nofoln'];		
				$options['optimize_links_nofolo']=$_POST['optimize_links_nofolo'];	
				//$options['optimize_links_blankn']=$_POST['optimize_links_blankn'];	
				$options['optimize_links_blanko']=$_POST['optimize_links_blanko'];	
				$options['optimize_links_onlysingle']=$_POST['optimize_links_onlysingle'];	
				$options['optimize_links_casesens']=$_POST['optimize_links_casesens'];	
				$options['optimize_links_process_in_feed']=$_POST['optimize_links_process_in_feed'];
				
				$options['optimize_links_use_cats_as_keywords']=$_POST['optimize_links_use_cats_as_keywords'];
				$options['optimize_links_use_tags_as_keywords']=$_POST['optimize_links_use_tags_as_keywords'];
				$options['optimize_links_nofollow_urls']=$_POST['optimize_links_nofollow_urls'];
				$options['optimize_links_nofolo_blanko_exclude_urls']=$_POST['optimize_links_nofolo_blanko_exclude_urls'];
				
			}
			
			if ( isset($_POST['optimize_images_submitted']) ) {
				$options['optimize_images_alttext'] = $_POST['optimize_images_alttext'];
				$options['optimize_images_titletext'] = $_POST['optimize_images_titletext'];
				
				$options['optimize_images_override_alt'] = $_POST['optimize_images_override_alt'];
				$options['optimize_images_override_title'] = $_POST['optimize_images_override_title'];
				
			}
		
			
			
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
			
			
			echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
			
			$this->clear_cache();
			
			
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


}

endif;



?>