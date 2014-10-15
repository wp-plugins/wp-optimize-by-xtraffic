<?php

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Images.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_CSSmin.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_JSMin.php'); 

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Mobile_Detect.php');


if ( !class_exists('WPOptimizeByxTraffic_Base') ) :


class WPOptimizeByxTraffic_Base {
	
	// Name for our options in the DB
	protected $wpOptimizeByxTraffic_DB_option = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NS;
	protected $wpOptimizeByxTraffic_options; 
	
	public $cacheObj;
	
	protected $mobileDetectObject = false;
	
	protected $currentUserId = false;
	
	protected $baseCacheData = array();
	
	protected $adminNoticesData = array();
	
	protected $urlFullRequest = '';
	
	protected $http_UserAgent = 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
	
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
		
		$priorityLast = 99999999.999999999 + (mt_rand() / 1000000000);
		$priorityLast = (float)$priorityLast;
		
		$priorityLast2 = $priorityLast + 9;
		$priorityLast3 = $priorityLast2 + 9;
	
	
		$doActions = array();
		
		$this->cacheObj = new PepVN_Cache();
		$this->cacheObj->cache_time = 86400;
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if ($options) {
			
			if ($options['optimize_links_process_in_post'] || $options['optimize_links_process_in_page']) {	
				add_filter('the_content',  array(&$this, 'optimize_links_the_content_filter'), $priorityLast);	
				
			}
			
			add_filter('the_content',  array(&$this, 'optimize_images_the_content_filter'), $priorityLast);
			
			
			
			if ($options['optimize_links_process_in_comment']) {
				add_filter('comment_text',  array(&$this, 'optimize_links_comment_text_filter'), $priorityLast);	
			}
			
			
			add_filter('the_content',  array(&$this, 'optimize_traffic_the_content_filter'), $priorityLast2);
			
			
			
			add_filter('wp_head',  array(&$this, 'header_footer_the_head_filter'), $priorityLast);
			add_filter('wp_footer',  array(&$this, 'header_footer_the_footer_filter'), $priorityLast);
			add_filter('the_content',  array(&$this, 'header_footer_the_content_filter'), $priorityLast);
			
			
			add_filter('admin_head',  array(&$this, 'header_footer_the_admin_head_filter'), $priorityLast);
			
			
			
			
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
		
		$this->urlFullRequest = $this->urlProtocol.'//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		
		
		add_action('admin_notices', array(&$this,'admin_notice'));
		
		
		
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
		
		
		if(!function_exists('mb_strlen')) {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server need support "Multibyte String" to use this plugin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' - <a href="http://php.net/manual/en/mbstring.installation.php" target="_blank"><b>'.__('Read more here',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b></a></div>';
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
			$resultData .= '<div id="sideblock" style="float:right;width:290px;margin-left:10px;"><iframe width="290" height="1000" frameborder="0" src="//static.pep.vn/library/pepvn/wp-optimize-by-xtraffic/client/vertical_01.html?utm_source='.rawurlencode($this->fullDomainName).'&utm_medium=plugin-wp-optimize-by-xtraffic-v-'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION.'&utm_campaign=WP+Optimize+By+xTraffic"></iframe></div>';
		}
		
		return $resultData;
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
	
	
	
	public function base_is_admin() 
	{
		$keyCache1 = 'rs_base_is_admin';
		
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
		$keyCache1 = 'rs_base_is_current_user_can-'.$input_capability;
		
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
	
	
	
	public function base_gmdate_gmt($input_timestamp)
	{
		$input_timestamp = (int)$input_timestamp;
		$formatStringGMDate = 'D, d M Y H:i:s';
		$resultData = gmdate($formatStringGMDate, $input_timestamp).' GMT';
		return $resultData;
	}
	
	
	
	
	public function base_get_categories($input_term_id = 0)
	{
	
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$input_term_id
		));
		
		$resultData = $this->cacheObj->get_cache($keyCache1);
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
			
			
			$this->cacheObj->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	
	
	
	public function base_get_tags($input_term_id = 0)
	{
	
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$input_term_id 
		));
		
		$resultData = $this->cacheObj->get_cache($keyCache1);
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
			
			$this->cacheObj->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	
	
	public function base_get_terms_by_post_id($input_post_id)
	{
	
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$input_post_id 
		));
		
		$resultData = $this->cacheObj->get_cache($keyCache1);
		if(!$resultData) {
			$resultData = array();
			
			$groupsTerms = array();
			
			$groupsTerms['tags'] = get_the_tags((int)$input_post_id);
			$groupsTerms['category'] = get_the_category((int)$input_post_id);
			foreach($groupsTerms as $keyOne => $valueOne) {
				if ($valueOne) {
					foreach($valueOne as $valueTwo) {
						if($valueTwo) {
							if(isset($valueTwo->term_id) && $valueTwo->term_id) {
								$valueTwo->term_id = (int)$valueTwo->term_id;
								$linkTerm = '';
								if('tags' === $keyOne) {
									$linkTerm = get_tag_link($valueTwo->term_id);
								} else if('category' === $keyOne) {
									$linkTerm = get_category_link($valueTwo->term_id);
								}
								
								$resultData[$valueTwo->name] = array(
									'name' => $valueTwo->name
									,'term_id' => $valueTwo->term_id
									,'link' => $linkTerm
								);
								
							}
						}
					}
				}
			}
			
			
			
			$this->cacheObj->set_cache($keyCache1, $resultData);
			
			
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
	
	
	
	
	
	public function base_do_before_wp_shutdown()
	{
		
		
	}
	
	
	
	public function base_get_clean_raw_text_for_process_search($input_text)
	{
		$input_text = (array)$input_text;
		$input_text = implode(' ',$input_text);
		$input_text = PepVN_Data::decodeText($input_text);
		$input_text = strip_tags($input_text);
		$input_text = PepVN_Data::strtolower($input_text);
		$input_text = PepVN_Data::analysisKeyword_RemovePunctuations($input_text);
		$input_text = PepVN_Data::reduceSpace($input_text);
		
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
			$keyCache1 = PepVN_Data::createKey(array(
				__METHOD__
				,$input_query
			));
			
			$resultData = $this->cacheObj->get_cache($keyCache1);
			
			if($resultData) {
				return $resultData;
			}
		}
		
		
		$resultData = $wpdb->get_results($input_query);
		if($resultData) {
			if($keyCache1) {
				$this->cacheObj->set_cache($keyCache1, $resultData);
			}
		}
		
		
		
		
		
		return $resultData;
	}
	
	
	public function base_search_posts_by_fulltext($input_parameters) 
	{
		global $wpdb;
		
		$resultData = array();
		
		$this->enable_db_fulltext();
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(isset($options['db_has_fulltext_status']) && ($options['db_has_fulltext_status'])) {
		} else {
			return false;
		}
		
		if(isset($input_parameters['post_types']) && $input_parameters['post_types']) {
			
		} else {
			$input_parameters['post_types'] = array(
				'post'
				,'page'
			);
		}
		
		
		$input_parameters['post_types'] = PepVN_Data::cleanArray($input_parameters['post_types']);
		if($input_parameters['post_types'] && (count($input_parameters['post_types'])>0)) {
		
		} else {
			return $resultData;
		}
		//$input_parameters['post_types'] = (array)$input_parameters['post_types'];
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
		
		if(count($keywords)>0) {
			
		} else {
			return $resultData;
		}
		
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
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$input_parameters
		));
		
		$resultData = $this->cacheObj->get_cache($keyCache1);
		
		if(!$resultData) {
			
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
			
			
			$queryString1 = '
	SELECT ID , post_title , post_content, (
		(
			'.implode(' + ',$queryString_MatchAgainstKeywords).'
		)
	) AS wpxtraffic_score
	FROM '.$wpdb->posts.'
	WHERE ( ( post_status = \'publish\') AND ( post_password = \'\') 
	'; 
				
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
									
									//*
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
									//*/
									
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
			
		
		
			
			
			
			$this->cacheObj->set_cache($keyCache1, $resultData);
		}
		
		
		
		return $resultData;
		
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
			
			'optimize_images_alttext' => '%img_name %title',
			'optimize_images_titletext' => '',
			
			'optimize_images_override_alt' => 'on',//on
			'optimize_images_override_title' => '',//on
			
			
			'optimize_images_optimize_image_file_enable' => 'on',//on
			
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
			
			//optimize_cache
			'optimize_speed_optimize_cache_enable' => '',//on
			
			'optimize_speed_optimize_cache_browser_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_front_page_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_feed_page_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_ssl_request_cache_enable' => '',//on
			'optimize_speed_optimize_cache_mobile_device_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_url_get_query_cache_enable' => '',//on
			'optimize_speed_optimize_cache_logged_users_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_database_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_cachetimeout' => 86400,//int 
			
			
			
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
			'optimize_speed_optimize_css_asynchronous_css_loading_enable' => '',//on
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
			$saved = $this->base_fix_options($saved);
			
			foreach ($saved as $key => $option) {
				$options[$key] = $option;
			}
		}
		
		$options = $this->base_fix_options($options);
		
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
		$arrayPaths[] = PEPVN_CACHE_DATA_DIR.'ocps'.DIRECTORY_SEPARATOR;
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
	
	
	
	
	
	public function base_fix_options($options)
	{
		
		$arrayFields1 = array(
			'optimize_images_watermarks_watermark_position'
			,'optimize_images_watermarks_watermark_type'
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
					
					
					
					//optimize_cache
					,'optimize_speed_optimize_cache_enable'
					,'optimize_speed_optimize_cache_browser_cache_enable'
					,'optimize_speed_optimize_cache_front_page_cache_enable'
					,'optimize_speed_optimize_cache_feed_page_cache_enable'
					,'optimize_speed_optimize_cache_ssl_request_cache_enable'
					,'optimize_speed_optimize_cache_mobile_device_cache_enable'
					,'optimize_speed_optimize_cache_url_get_query_cache_enable'
					,'optimize_speed_optimize_cache_logged_users_cache_enable'
					,'optimize_speed_optimize_cache_database_cache_enable'
					,'optimize_speed_optimize_cache_cachetimeout'
					
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
				
				$options['optimize_speed_optimize_cache_cachetimeout'] = abs((int)$options['optimize_speed_optimize_cache_cachetimeout']);
				if($options['optimize_speed_optimize_cache_cachetimeout'] < 300) {
					$options['optimize_speed_optimize_cache_cachetimeout'] = 300;
				}
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
		
			$options = $this->base_fix_options($options);
			
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
						
						<h3>',__('Overview "'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'"',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
						<div class="wpoptimizebyxtraffic_green_block">
							<h6>',__('Thank you for your interest and use plugin "'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'"',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :)</h6>
							<p>',__('We created this plugin with the highest goal is to make WordPress website more powerful and utilities through it\'s functions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							<p>',__('There is one thing you should know is that all the functionality of this plugin include : "Optimize Links", "Optimize Images", "Optimize Speed", "Optimize Traffic", "Header & Footer" operate independently. You can turn on/off one of these functions without affecting other functions.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
						</div>
						
						<h3>',__('Settings',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
						
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
		
		
		
	}
	
	
	public function base_get_post_by_id($post_id) 
	{
		$resultData = false;
		
		if($post_id) {
			$post_id = (int)$post_id;
			if($post_id>0) {
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
					}
				}
			}
		}
		
		
		return $resultData;
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
				
				
				$rsOne = $this->optimize_traffic_preview_optimize_traffic_modules($dataSent);
				
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
		}
		
		echo PepVN_Data::encodeResponseData($resultData);
		
	
	
	
	}
	
	
	
	
	public function quickGetUrlContent_ViaCurl($input_url, $input_args = false) 
	{
		$resultData = '';
		
		if(function_exists('curl_init')) {
			$connect_timeout = 6;
			
			$opts_Headers = array();
			$opts_Headers['user-agent'] = 'User-Agent: '.$this->http_UserAgent;
			$opts_Headers['accept'] = 'Accept: */*;';
			$opts_Headers['accept-encoding'] = 'Accept-Encoding: gzip,deflate';
			$opts_Headers['accept-charset'] = 'Accept-Charset: UTF-8,*;';
			$opts_Headers['keep-alive'] = 'Keep-Alive: 300';
			$opts_Headers['connection'] = 'Connection: keep-alive';
			
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $input_url);
			curl_setopt($ch, CURLOPT_HEADER, false);curl_setopt($ch, CURLINFO_HEADER_OUT, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, $this->http_UserAgent);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $opts_Headers);
			curl_setopt($ch, CURLOPT_ENCODING, 'utf-8');
			curl_setopt($ch, CURLOPT_TIMEOUT, $connect_timeout);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 6); 
			
			
			
			$resultData = curl_exec($ch);curl_close($ch);
			if(!$resultData) {
				$resultData = '';
			}
			
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
		
		
		$isHasCurlStatus = false;
		/*
		if(function_exists('curl_init')) {
			$isHasCurlStatus = true; 
		}
		//*/
		
		
		if($isHasCurlStatus) {
			$resultData = $this->quickGetUrlContent_ViaCurl($input_url, $input_args);
			if($resultData) {
				if($cacheTimeout > 0) {
					$this->cacheObj->set_cache($keyCache1, $resultData);
				}
			}
			
			return $resultData;
		} else {
			//$this->http_UserAgent,
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
			
		}
		
		
		$resultData = '';
		return $resultData;
		
		
	}


}



endif;

