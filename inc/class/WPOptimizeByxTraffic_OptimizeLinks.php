<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeImages.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeLinks') ) :


class WPOptimizeByxTraffic_OptimizeLinks extends WPOptimizeByxTraffic_OptimizeImages 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
		
		
	}
	
	
	
	public function optimize_links_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		$resultData['notice']['error_no'] = array();
		
		
		$rsTemp = $this->base_check_system_ready();
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsTemp
		));
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	
	function optimize_links_explode_and_clean_data($input_data) 
	{
		$resultData = array();
		
		$input_data = explode(',',$input_data);
		foreach($input_data as $value1) {
			$value1 = trim($value1); 
			if(strlen($value1)>0) {
				$resultData[] = $value1;
			}
		}
		
		return $resultData;
		
	}
	
	
	function optimize_links_parse_keywords($text, $input_opts = false)
	{
				
		$resultData = array();
		
		if(!isset($input_opts['casesensitive_status'])) {
			$input_opts['casesensitive_status'] = 0;
		}
		
		$input_opts['casesensitive_status'] = (int)$input_opts['casesensitive_status'];
		
		
		$text = (array)$text;
		$text = implode(PHP_EOL,$text);
		$text = explode(PHP_EOL,$text);

		foreach($text as $key1 => $value1) {
			
			$temp1 = $this->optimize_links_explode_and_clean_data($value1);
			
			if(count($temp1)>0) {
				$keywords1 = array();
				$links1 = array();
				foreach($temp1 as $key2 => $value2) {
					
					$value2 = trim($value2);
					
					if(preg_match('#^https?://.+#i',$value2)) {
						
						$links1[] = $value2;
						
					} else {
					
						if(!$input_opts['casesensitive_status']) {
						
							$keywords1[] = mb_strtolower($value2, 'UTF-8');
							
						} else {
						
							$keywords1[] = $value2;
							
						}
						
					}
				}
				
				
				if(count($keywords1)>0) {
				
					foreach($keywords1 as $key2 => $value2) {
					
						if(!isset($resultData[$value2])) {
						
							$resultData[$value2] = array();
							
						}
						
						$resultData[$value2] = array_merge($resultData[$value2],$links1);
						
					}
				}
				
				
			}
		}

		return $resultData;
		
	}
	
	
	
	public function search_posts($input_parameters) 
	{
		global $wpdb;
		
		$resultData = array();
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
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
		
		
		$keyword = $input_parameters['keyword'];
		
		$keyword = preg_replace('#[\'\"\\\]+#i',' ',$keyword);
		$keyword = preg_replace('#[\s]+#is',' ',$keyword);
		
		$keyword = trim($keyword);
		
		$input_parameters['keyword'] = $keyword;
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$input_parameters
		));
		
		$resultData = $this->cacheObj->get_cache($keyCache1);
		
		if(!$resultData) {
			
			$resultData = array();
			
			
			$queryString_Where_PostType = array();
			
			foreach($input_parameters['post_types'] as $keyOne => $valueOne) {
				if($valueOne) {
					$valueOne = trim($valueOne);
					if($valueOne) {
						$queryString_Where_PostType[] = ' ( post_type = \''.$valueOne.'\' ) ';
					}
				}
				
			}
			
			/*
			if(in_array('post',$input_parameters['post_types'])) {
				$queryString_Where_PostType[] = ' ( post_type = \'post\' ) ';
			}
			
			if(in_array('page',$input_parameters['post_types'])) {
				$queryString_Where_PostType[] = ' ( post_type = \'page\' ) ';
			}
			*/
			
			$queryString_Where_PostType = implode(' OR ',$queryString_Where_PostType);
			$queryString_Where_PostType = trim($queryString_Where_PostType);
			
			
			if(isset($options['db_has_fulltext_status']) && ($options['db_has_fulltext_status'])) {
				
				$queryString1 = '
	SELECT ID , post_title , (
		(
			( ( MATCH(post_title) AGAINST(\''.$keyword.'\' IN NATURAL LANGUAGE MODE) ) * 3 )
			+ ( ( MATCH(post_excerpt) AGAINST(\''.$keyword.'\' IN NATURAL LANGUAGE MODE) ) * 2 )
			+ ( ( MATCH(post_content) AGAINST(\''.$keyword.'\' IN NATURAL LANGUAGE MODE) ) * 1 )
			+ ( ( MATCH(post_name) AGAINST(\''.$keyword.'\' IN NATURAL LANGUAGE MODE) ) * 2 )
		) / 8
	) AS wpxtraffic_score
	FROM '.$wpdb->posts.'
	WHERE ( ( post_status = \'publish\') AND ( post_password = \'\') 
	'; 
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) '; 
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
	ORDER BY wpxtraffic_score DESC 
	LIMIT 0,3 
	';
			} else {
			
				$queryString1 = '
	SELECT ID , post_title , (
		ID
	) AS wpxtraffic_score
	FROM '.$wpdb->posts.'
	WHERE ( ( post_status = \'publish\') AND ( post_password = \'\') AND ( post_title LIKE \'%'.$keyword.'%\' ) 
	';
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) ';
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
	ORDER BY wpxtraffic_score ASC 
	LIMIT 0,3 
	';
			}
		
			$rsOne = $wpdb->get_results($queryString1);
			
			
			if($rsOne) {
				foreach($rsOne as $keyOne => $valueOne) {
					if($valueOne) {
						if(isset($valueOne->wpxtraffic_score)) {
							$valueOne->wpxtraffic_score = (int)$valueOne->wpxtraffic_score;
							if($valueOne->wpxtraffic_score >= 1) {
								$postId = (int)$valueOne->ID;
								
								if($postId) {
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
			
		
		
			
			
			
			$this->cacheObj->set_cache($keyCache1, $resultData);
		}
		
		
		
		return $resultData;
		
	}
	
	
	
	
	/**
	 * 
	 * 
	 */
	public function enable_db_fulltext($input_parameters = false) 
	{
		global $wpdb;
		
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
		));
		
		if(isset($this->baseCacheData[$keyCache1]['got_options_status']) && $this->baseCacheData[$keyCache1]['got_options_status']) {
			$options = $this->get_options(array(
				'cache_status' => 1
			));
		} else {
			$options = $this->get_options();
			$this->baseCacheData[$keyCache1]['got_options_status'] = 1;
		}
		
		
		if(!$input_parameters) {
			$input_parameters = array();
		}
		
		
		$checkStatus1 = true;
		
		
		if(isset($input_parameters['force_check_fulltext_status']) && $input_parameters['force_check_fulltext_status']) {
			$options['db_has_fulltext_status'] = 0;
			$options['do_enable_db_fulltext_time'] = 0;
			
		}
		
		
		if(isset($options['db_has_fulltext_status']) && $options['db_has_fulltext_status']) {
			$checkStatus1 = false;
		}
		
		if($checkStatus1) {
			if(isset($options['do_enable_db_fulltext_time']) && $options['do_enable_db_fulltext_time']) {
				$options['do_enable_db_fulltext_time'] = (int)$options['do_enable_db_fulltext_time'];
				if((time() - $options['do_enable_db_fulltext_time']) <= 86400) {
					$checkStatus1 = false;
				}
				
			}
		}
		
		
		
		
		if(!$checkStatus1) {
			return false;
		}
		
		
		$options['do_enable_db_fulltext_time'] = time();
		
		update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		
		
		
		$dbFieldsHasIndexTypes = array();
		
		$dbFieldsHasIndexTypes['post_title'] = array();
		$dbFieldsHasIndexTypes['post_excerpt'] = array();
		$dbFieldsHasIndexTypes['post_content'] = array();
		$dbFieldsHasIndexTypes['post_name'] = array();
		
		
		$rsOne = $wpdb->get_results("SHOW INDEXES FROM {$wpdb->posts}");
		
		if($rsOne) {
		
			foreach($rsOne as $valueOne) {
				
				if($valueOne) {
				
					if(isset($valueOne->Column_name)) {
						
						$colName = $valueOne->Column_name;
						$colName = (string)$colName;
						$colName = trim($colName);
						
						if(isset($dbFieldsHasIndexTypes[$colName])) {
							
							if(isset($valueOne->Index_type) && $valueOne->Index_type) {
								
								$indexType = $valueOne->Index_type;
								$indexType = (string)$indexType;
								$indexType = trim($indexType);
								$indexType = strtolower($indexType);
								
								$dbFieldsHasIndexTypes[$colName][] = $indexType;
								
							}
							
							
						}
					}
				}
				
			}
			
			
		}
		
		
		$doAddFulltextIndexStatus = false;
		
		foreach($dbFieldsHasIndexTypes as $keyOne => $valueOne) {
			
			if($valueOne && in_array('fulltext',$valueOne)) {
			} else {
				$doAddFulltextIndexStatus = true;
			}
			
			
		}
		
		
		
		if($doAddFulltextIndexStatus) {
			
			$canAddFulltextIndexStatus = false;
			
			$rsOne = $wpdb->get_results("SHOW TABLE STATUS LIKE '{$wpdb->posts}'");
			
			if($rsOne) {
				
				foreach($rsOne as $valueOne) {
				
					if($valueOne) {
					
						if(isset($valueOne->Engine)) {
							
							$engine1 = $valueOne->Engine;
							$engine1 = (string)$engine1;
							$engine1 = trim($engine1);
							$engine1 = strtolower($engine1);
							
							if ($engine1 === 'myisam') {
								$canAddFulltextIndexStatus = true;
							} else if ($engine1 === 'innodb') {
								$rsTwo = $wpdb->get_results('SHOW VARIABLES LIKE "%version%"');
								
								if($rsTwo) {
									foreach($rsTwo as $valueTwo) {
				
										if($valueTwo) {
										
											if(isset($valueTwo->Variable_name) && $valueTwo->Variable_name && isset($valueTwo->Value) && $valueTwo->Value) {
												
												$variableName1 = $valueTwo->Variable_name;
												$value1 = $valueTwo->Value;
												
												$variableName1 = (string)$variableName1;
												$variableName1 = trim($variableName1);
												$variableName1 = strtolower($variableName1);
												
												$value1 = (string)$value1;
												$value1 = trim($value1);
												$value1 = strtolower($value1);
												
												if('version' === $variableName1) {
													$value1 = (float)$value1;
													if($value1 >= 5.6) {
														$canAddFulltextIndexStatus = true;
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
			
			
			
			if($canAddFulltextIndexStatus) { 
				
				foreach($dbFieldsHasIndexTypes as $keyOne => $valueOne) {
			
					if($valueOne && in_array('fulltext',$valueOne)) {
					} else {
						$wpdb->get_results(' ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT `wppepvn_'.$keyOne.'_ftind` (`'.$keyOne.'`) ');
					}
					
					$dbFieldsHasIndexTypes[$keyOne][] = 'fulltext';
				}
				
			} else {
				$keyTemp = 'post_title';
				if($dbFieldsHasIndexTypes[$keyTemp] && in_array('btree',$dbFieldsHasIndexTypes[$keyTemp])) {
				} else {
					$wpdb->get_results(' CREATE INDEX wppepvn_'.$keyTemp.'_btind ON '.$wpdb->posts.' ( '.$keyTemp.'(250) ) ');
				}
				
			}
			
		}
		
		$options['db_has_fulltext_status'] = 1;
		
		foreach($dbFieldsHasIndexTypes as $keyOne => $valueOne) {
			
			if($valueOne && in_array('fulltext',$valueOne)) {
			} else {
				$options['db_has_fulltext_status'] = 0;
			}
			
		}
		
		
		$options1 = $this->get_options();
		
		$options = PepVN_Data::mergeArrays(array(
			$options1
			,$options
		));
		
		$options['do_enable_db_fulltext_time'] = time();
		
		update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		
		return true;
		
	}
	

	

	function optimize_links_process_text($text, $mode)
	{
		
		global $wpdb, $post;
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		
		$isProcessTextStatus = true;
		
		if(isset($options['optimize_links_enable']) && $options['optimize_links_enable']) {
			
		} else {
			$isProcessTextStatus = false;
		}
		
		
		
		
		
		$links = 0;
		
		if($isProcessTextStatus) {
			
			if (is_feed() && !$options['optimize_links_process_in_feed']) {
				
				$isProcessTextStatus = false;
			} else if ($options['optimize_links_onlysingle']) {
				if(is_single() || is_page() || is_singular()) {
				} else {
					
					$isProcessTextStatus = false;
				}
				
			}
			
		}
		
		
		if($isProcessTextStatus) {
			$arrignorepost = PepVN_Data::explode(',',$options['optimize_links_ignorepost']);
			$arrignorepost = PepVN_Data::cleanArray($arrignorepost);
			
			if($arrignorepost && (count($arrignorepost)>0)) {
				if (is_page($arrignorepost) || is_single($arrignorepost)) {
					
					$isProcessTextStatus = false;
				}
			}
		}
		
		
		if($isProcessTextStatus) {
			if (!$mode) {
			
				if ($post->post_type=='post' && !$options['optimize_links_process_in_post']) {
					
					$isProcessTextStatus = false;
					
				} else if ($post->post_type=='page' && !$options['optimize_links_process_in_page']) {
					
					$isProcessTextStatus = false;
					
				}
				
				if (($post->post_type=='page' && !$options['optimize_links_allow_link_to_pageself']) || ($post->post_type=='post' && !$options['optimize_links_allow_link_to_postself'])) {
				
					$thistitle = $options['optimize_links_casesens'] ? $post->post_title : strtolower($post->post_title);
					$thisurl = trailingslashit(get_permalink($post->ID));
					
				} else {
					$thistitle='';
					$thisurl='';
				}
			
			}
		}
		
		if(!$isProcessTextStatus) {
			return $text;
		}
		
		
		
		
		$keyCacheMethod = array(
			__METHOD__
		);
		$keyCacheMethod = PepVN_Data::createKey($keyCacheMethod);
		
		
		$keyCacheProcessText = array(
			$keyCacheMethod
			,$text
			,$mode
			,'process_text'
		);
		
		$keyCacheProcessText = PepVN_Data::createKey($keyCacheProcessText);
		
		
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessText); 
		
		if($valueTemp) {
			return $valueTemp; 
		}
		
		
		
		$parametersPrimary = array();
		$parametersPrimary['group_keywords1'] = array();
		$parametersPrimary['group_keywords2'] = array();
		$parametersPrimary['group_keywords3'] = array();
		
		$patternsEscaped = array();
		
		
		//$options['do_enable_db_fulltext_time'] = 0;update_option($this->wpOptimizeByxTraffic_DB_option, $options);
		
		

		
		
		$optimize_links_maxlinks = ($options['optimize_links_maxlinks']>0) ? $options['optimize_links_maxlinks'] : 0;	
		$optimize_links_maxsingle = ($options['optimize_links_maxsingle']>0) ? $options['optimize_links_maxsingle'] : -1;
		$optimize_links_maxsingleurl = ($options['optimize_links_maxsingleurl']>0) ? $options['optimize_links_maxsingleurl'] : 0;
		$optimize_links_minusage = ($options['optimize_links_minusage']>0) ? $options['optimize_links_minusage'] : 1;
		
		$optimize_links_maxlinks = abs((int)$optimize_links_maxlinks);

		$urls = array();
		
		$array_base_custom_post_types = PepVN_Data::explode(',',$options['base_custom_post_types']);
		$array_base_custom_post_types = PepVN_Data::cleanArray($array_base_custom_post_types);
		
		$array_base_custom_taxonomies = PepVN_Data::explode(',',$options['base_custom_taxonomies']);
		$array_base_custom_taxonomies = PepVN_Data::cleanArray($array_base_custom_taxonomies);
		
		
		if ($options['optimize_links_link_to_posts']) {
			$array_base_custom_post_types[] = 'post';
		}
		
		if ($options['optimize_links_link_to_pages']) {
			$array_base_custom_post_types[] = 'page';
		}
		
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'a;script;style;link;meta;input;textarea;iframe;video;audio;object');
		$text = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		$rsOne = false;
		
		
		
		
		if ($options['optimize_links_excludeheading'] == 'on') {
			//escape a and h1 -> h6
			$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'a;h1;h2;h3;h4;h5;h6');
			$text = $rsOne['content'];
			if(count($rsOne['patterns'])>0) {
				$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
			}
			$rsOne = false;
			
		}
		
		$rsOne = PepVN_Data::escapeHtmlTags($text);
		$text = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		$rsOne = false;
		
		
		$text = ' '.$text.' ';
		
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__ 
			, 'group_keywords1' 
		));
		
		$parametersPrimary['group_keywords1'] = $this->cacheObj->get_cache($keyCache1);
		
		if(!$parametersPrimary['group_keywords1']) {
			
			$parametersPrimary['group_keywords1'] = array();
			
			if (!empty($options['optimize_links_customkey_url']))
			{
								
				$valueTemp = wp_remote_retrieve_body(wp_remote_get($options['optimize_links_customkey_url']));
				if($valueTemp) {
					$valueTemp = strip_tags($valueTemp);
					$valueTemp = trim($valueTemp);
					if($valueTemp) {
						$options['optimize_links_customkey'] = $options['optimize_links_customkey'] . PHP_EOL . $valueTemp; 
					}
				}
				
				
			}
			
			if ($options['optimize_links_use_tags_as_keywords']) {
				$posttags = get_tags();
				if ($posttags) {
					foreach($posttags as $tag) {
						if($tag) {
							if ($options['optimize_links_link_to_tags']) {
								$options['optimize_links_customkey'] .= PHP_EOL.''.$tag->name.','.get_tag_link((int)$tag->term_id);
							} else {
								$options['optimize_links_customkey'] .= PHP_EOL.''.$tag->name;
							}
						}
					}
				}
			}
			
			if ($options['optimize_links_use_cats_as_keywords']) {
				
				$categories = get_categories();
				if ($categories) {
					foreach($categories as $category) {
						if($category) {
							if ($options['optimize_links_link_to_cats']) {
								$options['optimize_links_customkey'] .= PHP_EOL.''.$category->name.','.get_category_link((int)$category->term_id);
							} else {
								$options['optimize_links_customkey'] .= PHP_EOL.''.$category->name;
							}
						}
					}
				}
				
			}
			
			
			
			// custom keywords
			if (!empty($options['optimize_links_customkey'])) {
				
				$optsTemp1 = array(
					'casesensitive_status' => 0
				);
				
				if($options['optimize_links_casesens']) {
					$optsTemp1['casesensitive_status'] = 1;
				}
				
				$parametersPrimary['group_keywords1'] = $this->optimize_links_parse_keywords($options['optimize_links_customkey'], $optsTemp1);
				
			}
			
			
			
			
			$this->cacheObj->set_cache($keyCache1,$parametersPrimary['group_keywords1']);
			
			
			
		}
		
		
		
		$numberTotalLinksAdded = 0;
		
		
		if($parametersPrimary['group_keywords1']) {
			if(count($parametersPrimary['group_keywords1'])>0) {
				
				$parametersPrimary['group_keywords2'] = array();
				
				foreach($parametersPrimary['group_keywords1'] as $key1 => $value1) {
					$targetKeywordClean = PepVN_Data::strtolower(PepVN_Data::cleanKeyword($key1));
					if(isset(PepVN_Data::$cacheData[$keyCacheMethod]['keywordsAdded'][$targetKeywordClean]) && PepVN_Data::$cacheData[$keyCacheMethod]['keywordsAdded'][$targetKeywordClean]) {
					} else {
					
						$patterns1 = '#([\s\,\;\.]+?)('.PepVN_Data::preg_quote($key1).')([\s\,\;\.]+?)#';
						if(!$options['optimize_links_casesens']) {
							$patterns1 .= 'i';
						}
						
						$numberMatched1 = preg_match_all( $patterns1,$text,$matched1);
						
						$numberMatched1 = (int)$numberMatched1;
						if($numberMatched1>0) {
							if(isset($parametersPrimary['group_keywords2'][$key1]) && $parametersPrimary['group_keywords2'][$key1]) {
							
							} else {
								$parametersPrimary['group_keywords2'][$key1] = 0;
							}
							
							$keywordLength1 = strlen($key1);
							$keywordLength1 = (int)$keywordLength1;
							$keywordLength1 = $keywordLength1 * 2.8;
							$keywordLength1 = (int)$keywordLength1;
							
							
							$parametersPrimary['group_keywords2'][$key1] += $numberMatched1 * $keywordLength1;
						}
						
					}
				}
				
				
			
				if(count($parametersPrimary['group_keywords2'])>0) {
					arsort($parametersPrimary['group_keywords2']);
					
					
					
					$numberTotalLinks = 0;
					
					foreach($parametersPrimary['group_keywords2'] as $key1 => $value1) {
						
						if($optimize_links_maxlinks > 0) {
							if($numberTotalLinksAdded >= $optimize_links_maxlinks) {
								break;
							}
						}
						
						$targetKeywordClean = PepVN_Data::strtolower(PepVN_Data::cleanKeyword($key1));
						
						$checkStatus1 = false;
						
						$targetLink1 = false;
						
						
						if(isset($parametersPrimary['group_keywords1'][$key1])) {
							
							$targetLink2 = false;
							$targetLinkTitle2 = false;
							
							if(($parametersPrimary['group_keywords1'][$key1]) && (count($parametersPrimary['group_keywords1'][$key1])>0)) {
								$targetLinks1 = $parametersPrimary['group_keywords1'][$key1];
								$targetLinks1 = PepVN_Data::cleanArray($targetLinks1);
								if(count($targetLinks1)>0) {
									shuffle($targetLinks1);
									foreach($targetLinks1 as $key2 => $value2) {
										$value2 = trim($value2);
										if($value2) {
											if(isset(PepVN_Data::$cacheData[$keyCacheMethod]['linksAdded'][$value2]) && PepVN_Data::$cacheData[$keyCacheMethod]['linksAdded'][$value2]) {
											} else {
												$targetLink2 = $value2;
												$targetLinkTitle2 = $key1;
												break;
											}
										}
									}
									
								}
							}
							
							if(!$targetLink2) {
								
								if ($array_base_custom_post_types && (count($array_base_custom_post_types)>0)) {
								
									$parametersTemp2 = array();
									$parametersTemp2['keyword'] = $key1;
									$parametersTemp2['post_types'] = $array_base_custom_post_types;
									
									$rsTwo = $this->search_posts($parametersTemp2);
									
									
									
									foreach($rsTwo as $keyTwo => $valueTwo) {
										
										$checkStatus2 = false;
										
										if($valueTwo['post_id'] != $post->ID) {
											$checkStatus2 = true;
										} else {
											if ($post->post_type == 'post') {
												if ($options['optimize_links_allow_link_to_postself']) {
													$checkStatus2 = true;
												}
											} else if ($post->post_type == 'page') {
												if ($options['optimize_links_allow_link_to_pageself']) {
													$checkStatus2 = true;
												}
											
											}
										}
										
										if($checkStatus2) {
											if(isset(PepVN_Data::$cacheData[$keyCacheMethod]['linksAdded'][$valueTwo['post_link']]) && PepVN_Data::$cacheData[$keyCacheMethod]['linksAdded'][$valueTwo['post_link']]) {
												$checkStatus2 = false;
											}
										}
										
										if($checkStatus2) {
											$targetLink2 = $valueTwo['post_link'];
											$targetLinkTitle2 = $valueTwo['post_title'];
											break;
										}
										
									}
									
									
								}
								
								
								
							}
							
							if($targetLink2) {
								
								
								$patterns2 = '#([\s\,\;\.]+?)('.PepVN_Data::preg_quote($key1).')([\s\,\;\.]+?)#';
								if(!$options['optimize_links_casesens']) {
									$patterns2 .= 'i';
								}
								
								$replace2 = '\1<a href="'.$targetLink2.'" '.($options['optimize_links_open_autolink_new_window'] ? ' target="_bank" ' : '').' title="';
								
								if($targetLinkTitle2) {
									$targetLinkTitle2 = PepVN_Data::cleanKeyword($targetLinkTitle2);
								}
								
								if($targetLinkTitle2) {
									$replace2 .= $targetLinkTitle2.'">';
								} else {
									$replace2 .= '\2">';
								}
								$replace2 .= '<strong>\2</strong></a>\3';
								
								
								$text = preg_replace ( $patterns2, $replace2,  $text, 1,$count2);
								$count2 = (int)$count2;

								if($count2>0) {
								
									
									PepVN_Data::$cacheData[$keyCacheMethod]['linksAdded'][$targetLink2] = 1;
									PepVN_Data::$cacheData[$keyCacheMethod]['keywordsAdded'][$targetKeywordClean] = 1; 

									$rsTwo = PepVN_Data::escapeHtmlTags($text);
									$text = $rsTwo['content'];
									if(count($rsTwo['patterns'])>0) {
										foreach($rsTwo['patterns'] as $key2 => $value2) {
											$patternsEscaped[$key2] = $value2;
										}
									}

									$numberTotalLinksAdded += $count2;
									if($optimize_links_maxlinks > 0) {
										if($numberTotalLinksAdded >= $optimize_links_maxlinks) {
											break;
										}
									}
									
								}
							}
							
						}
						
						

					}
					
					
				}
				
				
				
			}
		}
		
		

		
		if($patternsEscaped) {
			if(count($patternsEscaped)>0) {
				$text = str_replace(array_values($patternsEscaped), array_keys($patternsEscaped), $text);
			}
		}
		
		$text = trim($text);
		
		$this->cacheObj->set_cache($keyCacheProcessText,$text); 
		
		
		return $text;

	} 

	
	
	
	function optimize_links_attributes_links($text) 
	{
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$link = parse_url(get_bloginfo('wpurl'));
		$host = 'http://'.$link['host'];
		
		
		if(!isset($options['optimize_links_nofollow_urls'])) {
			$options['optimize_links_nofollow_urls'] = '';
		}
		$options['optimize_links_nofollow_urls'] = (string)$options['optimize_links_nofollow_urls'];
		$options['optimize_links_nofollow_urls'] = trim($options['optimize_links_nofollow_urls']);
		
		if($options['optimize_links_nofollow_urls'] || $options['optimize_links_blanko'] || $options['optimize_links_nofolo']) {
			
			if(!isset($options['optimize_links_nofolo_blanko_exclude_urls'])) {
				$options['optimize_links_nofolo_blanko_exclude_urls'] = '';
			}
			$options['optimize_links_nofolo_blanko_exclude_urls'] = (string)$options['optimize_links_nofolo_blanko_exclude_urls'];
			$options['optimize_links_nofolo_blanko_exclude_urls'] = trim($options['optimize_links_nofolo_blanko_exclude_urls']);
			
			
			$nofollow_urls = PepVN_Data::cleanPregPatternsArray($options['optimize_links_nofollow_urls']);
			$nofollow_urls = implode('|',$nofollow_urls);
			$nofollow_urls = trim($nofollow_urls);
			
			
			$nofolo_blanko_exclude_urls = PepVN_Data::cleanPregPatternsArray($options['optimize_links_nofolo_blanko_exclude_urls']);
			$nofolo_blanko_exclude_urls = implode('|',$nofolo_blanko_exclude_urls);
			$nofolo_blanko_exclude_urls = trim($nofolo_blanko_exclude_urls);
			
			$rsOne = PepVN_Data::escapeHtmlTags($text);
			
			if(count($rsOne['patterns'])>0) {
				
				$patterns1 = array();
				
				foreach($rsOne['patterns'] as $keyOne => $valueOne) {
					
					if(preg_match('#<a[^>]+>#i',$keyOne,$matched1)) {
						if(preg_match('#href=(\'|")(https?://[^"\']+)\1#i',$keyOne,$matched2)) { 
							
							if(isset($matched2[2]) && $matched2[2]) {
								
								$isNofollowStatus1 = false;
								
								$isExternalLinksStatus1 = false;
								if(false === stripos($matched2[2],$host)) {
									$isExternalLinksStatus1 = true;
								}
								
								$matched2[2] = trim($matched2[2]);
								
								if($nofollow_urls && (preg_match('#('.$nofollow_urls.')#i',$matched2[2],$matched3))) {
									$isNofollowStatus1 = true;
								} else {
									if($options['optimize_links_nofolo']) {
										if($isExternalLinksStatus1) {//is external links = true
											if($nofolo_blanko_exclude_urls && (preg_match('#('.$nofolo_blanko_exclude_urls.')#i',$matched2[2],$matched3))) {
											} else {
												$isNofollowStatus1 = true;
											}
										}
									}
								}
								
								
								if($options['optimize_links_blanko']) {
									if($isExternalLinksStatus1) {
										$keyOne = preg_replace('#target=(\'|")([^"\']+)\1#i','',$keyOne);
										
										$keyOne = preg_replace('#<a(.+)#is', '<a target="_blank" \\1', $keyOne);
										
									}
								}
								
								if($isNofollowStatus1) {
									if(preg_match('#rel=(\'|")([^"\']+)\1#i',$keyOne,$matched3)) {
										$keyOne = preg_replace('#(rel=)(\'|")([^"\']+)\2#i','\1\2\3 nofollow \2',$keyOne);
									} else {
										$keyOne = preg_replace('#<a(.+)#is', '<a rel="nofollow" \1', $keyOne);
									}
									
								}
								
							}
							
						}
					}
					
					$patterns1[$keyOne] = $valueOne;
				
				}
				
				
				$text = str_ireplace(array_values($patterns1),array_keys($patterns1),$rsOne['content']);
				
			}
			
			

		}
		
		return $text;
		
	}
	
	

		
	function optimize_links_the_content_filter($text) 
	{
		
		$text = $this->optimize_links_process_text($text, 0);
		
		$text = $this->optimize_links_attributes_links($text);
		
		return $text;
	}

		
	function optimize_links_comment_text_filter($text) 
	{
		$text = $this->optimize_links_process_text($text, 1);
		
		$text = $this->optimize_links_attributes_links($text);
		
		return $text;
	}
	
	
	

	function optimize_links_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];	
		
		
		
		$optimize_links_enable = $options['optimize_links_enable']=='on'?'checked':'';

		$optimize_links_process_in_post=$options['optimize_links_process_in_post']=='on'?'checked':'';
		$optimize_links_allow_link_to_postself=$options['optimize_links_allow_link_to_postself']=='on'?'checked':'';
		$optimize_links_process_in_page=$options['optimize_links_process_in_page']=='on'?'checked':'';
		$optimize_links_allow_link_to_pageself=$options['optimize_links_allow_link_to_pageself']=='on'?'checked':'';
		$optimize_links_process_in_comment=$options['optimize_links_process_in_comment']=='on'?'checked':'';
		$optimize_links_excludeheading=$options['optimize_links_excludeheading']=='on'?'checked':'';
		$optimize_links_link_to_posts=$options['optimize_links_link_to_posts']=='on'?'checked':'';
		$optimize_links_link_to_pages=$options['optimize_links_link_to_pages']=='on'?'checked':'';
		$optimize_links_link_to_cats=$options['optimize_links_link_to_cats']=='on'?'checked':'';
		$optimize_links_link_to_tags=$options['optimize_links_link_to_tags']=='on'?'checked':'';
		
		$base_custom_post_types=$options['base_custom_post_types'];
		$base_custom_taxonomies=$options['base_custom_taxonomies'];
		
		//$optimize_links_ignore=$options['optimize_links_ignore'];
		$optimize_links_ignorepost=$options['optimize_links_ignorepost'];
		$optimize_links_maxlinks=$options['optimize_links_maxlinks'];
		$optimize_links_maxsingle=$options['optimize_links_maxsingle'];
		$optimize_links_maxsingleurl=$options['optimize_links_maxsingleurl'];
		$optimize_links_minusage=$options['optimize_links_minusage'];
		$optimize_links_customkey=stripslashes($options['optimize_links_customkey']);
        $optimize_links_customkey_url=stripslashes($options['optimize_links_customkey_url']);
		$optimize_links_customkey_preventduplicatelink=$options['optimize_links_customkey_preventduplicatelink'] == TRUE ? 'checked' : '';
		//$optimize_links_nofoln=$options['optimize_links_nofoln']=='on'?'checked':'';
		$optimize_links_nofolo=$options['optimize_links_nofolo']=='on'?'checked':'';
		//$optimize_links_blankn=$options['optimize_links_blankn']=='on'?'checked':'';
		$optimize_links_blanko=$options['optimize_links_blanko']=='on'?'checked':'';
		$optimize_links_onlysingle=$options['optimize_links_onlysingle']=='on'?'checked':'';
		$optimize_links_casesens=$options['optimize_links_casesens']=='on'?'checked':'';
		$optimize_links_process_in_feed=$options['optimize_links_process_in_feed']=='on'?'checked':'';
		
		$optimize_links_use_cats_as_keywords = $options['optimize_links_use_cats_as_keywords']=='on'?'checked':'';
		$optimize_links_use_tags_as_keywords = $options['optimize_links_use_tags_as_keywords']=='on'?'checked':'';
		$optimize_links_nofollow_urls=$options['optimize_links_nofollow_urls'];
		$optimize_links_nofolo_blanko_exclude_urls=$options['optimize_links_nofolo_blanko_exclude_urls'];
		
		$optimize_links_open_autolink_new_window = $options['optimize_links_open_autolink_new_window']=='on'?'checked':'';
		
		

		if (!is_numeric($optimize_links_minusage)) {
			$optimize_links_minusage = 0;
		}
		$optimize_links_minusage = (int)$optimize_links_minusage;
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
		
		
		$rsTemp = $this->optimize_links_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2>WP Optimize By xTraffic (Optimize Links)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="optimize_links_submitted" value="1" /> 
						
						<h2>',__('Overview',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' "Optimize Links"</h2>
						
						<p>"Optimize Links" ',__('can automatically link keywords in your posts and comments with your focused links or best related posts',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
						<p>',__('This plugin allows you to set nofollow attribute and open links in a new window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
						
						
						<ul>
							
							<li>
								<h3 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_links_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_links_container"  ',$optimize_links_enable,' /> &nbsp; ',__('Enable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' Optimize Links</h3>
							</li>
							
						</ul>
						
						
						<div style="margin-top: 0;" id="optimize_links_container" class="wpoptimizebyxtraffic_show_hide_container">
							
							
							<h2>',__('Internal Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h2>
							
							<h4>',__('Process Internal Links In Posts/Pages',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
							
							<p>"Optimize Links" ',__('can automatically process your posts, pages, comments and feed\'s content with keywords and links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							
							<ul>
								<li>
									<input type="checkbox" name="optimize_links_process_in_post"  ',$optimize_links_process_in_post,' /><label for="optimize_links_process_in_post"> ',__('Process Posts\'s Content',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
							
								<ul>
									<li>
										&nbsp;<input type="checkbox" name="optimize_links_allow_link_to_postself" ',$optimize_links_allow_link_to_postself,' /><label for="optimize_links_allow_link_to_postself"> ',__('Allow links to self',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
									</li>
								</ul>
								<li>
									<input type="checkbox" name="optimize_links_process_in_page" ',$optimize_links_process_in_page,' /><label for="optimize_links_process_in_page"> ',__('Process Pages\'s Content',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								<ul>
									<li>&nbsp;<input type="checkbox" name="optimize_links_allow_link_to_pageself" ',$optimize_links_allow_link_to_pageself,' /><label for="optimize_links_allow_link_to_pageself"> ',__('Allow links to self',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label></li>
								</ul>
								
								<li class="">
									<input type="checkbox" name="optimize_links_process_in_comment" ',$optimize_links_process_in_comment,' /><label for="optimize_links_process_in_comment">',__('Process Comments\'s Content',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								
								<li class="">
									<input type="checkbox" name="optimize_links_process_in_feed" ',$optimize_links_process_in_feed,' /><label for="optimize_links_process_in_feed"> ',__('Process RSS feeds Content',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								
							</ul>
							
							<br />
							
							
							<h4>',__('Excluding',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
							<input type="checkbox" name="optimize_links_excludeheading"  ',$optimize_links_excludeheading,' /><label for="optimize_links_excludeheading">',__('Prevent linking in heading tags (h1,h2,h3,h4,h5,h6)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</label>
							
							<h4>',__('Target Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
							
							<p>',__('The targeted links should be considered. The match will be based on post/page title or category/tag name, case insensitive',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							<ul>
								<li>
									<input type="checkbox" name="optimize_links_link_to_cats" ',$optimize_links_link_to_cats,' /><label for="optimize_links_link_to_cats"> ',__('Categories',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								<li>
									<input type="checkbox" name="optimize_links_link_to_tags" ',$optimize_links_link_to_tags,' /><label for="optimize_links_link_to_tags"> ',__('Tags',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label><br>
								</li>
								
								<li>
									<input type="checkbox" name="optimize_links_link_to_posts" ',$optimize_links_link_to_posts,' /><label for="optimize_links_link_to_posts"> ',__('Posts',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								<li>
									<input type="checkbox" name="optimize_links_link_to_pages" ',$optimize_links_link_to_pages,' /><label for="optimize_links_link_to_pages"> ',__('Pages',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
							</ul>
							
							<h2>',__('Settings',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h2>
							
							<p>',__('To reduce database load you can choose "Optimize Links" process only on single posts and pages (for example not on main page or archives)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							<input type="checkbox" name="optimize_links_onlysingle" ',$optimize_links_onlysingle,' /><label for="optimize_links_onlysingle"> ',__('Process only single posts and pages',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>  <br>
									
							<p>',__('Set whether matching should be case sensitive',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							<input type="checkbox" name="optimize_links_casesens" ',$optimize_links_casesens,' /><label for="optimize_links_casesens"> ',__('Case sensitive matching',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>  <br>
							
									
							<p>',__('Set open autolinks in new window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							<input type="checkbox" name="optimize_links_open_autolink_new_window" ',$optimize_links_open_autolink_new_window,' /><label for="optimize_links_open_autolink_new_window"> ',__('Open autolinks in new window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>  <br>
							
							
							<h4>',__('Ignore Posts and Pages',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>	
							
							<p>',__('You may wish to forbid automatically linking on certain posts or pages. Separate them by comma. (id, slug or name)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							<input type="text" name="optimize_links_ignorepost" size="255" value="',$optimize_links_ignorepost,'" style="max-width:660px;" /> 
							<br>
											 
							<h4>',__('Custom Keywords/Targets Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
							
							<p>',__('Here you can enter manually the extra keywords you want to automatically link. Use comma (,) to separate keywords and target url. Use a new line for new set of urls and keywords. You can have these keywords link to any urls, not only your site',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'.</p>
							<p>',__('If you don\'t set any url with keywords, this plugin will automatically find posts/pages having the best related content and link to these keywords',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							<p>',__('You must use full link with http:// or https:// (example : http://wordpress.org/plugins/ or https://wordpress.org/plugins/)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							<p>
								<u>',__('Example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</u>:<br />
								seo, wordpress, plugin, http://wordpress.org/<br />
								ads, marketing<br />
								seo plugin, wordpress plugin,http://wordpress.org/,http://wordpress.org/plugins/<br />
							</p>
							
							<textarea name="optimize_links_customkey" id="optimize_links_customkey" rows="10" cols="90"  >',$optimize_links_customkey,'</textarea>
							<br><br>

							<p>',__('Load custom keywords & links from a URL. (Note: this appends to the list above.)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							<input type="text" name="optimize_links_customkey_url" size="90" value="',$optimize_links_customkey_url,'" />
							
							
							<ul>
								<li>
									<input type="checkbox" name="optimize_links_use_cats_as_keywords" ',$optimize_links_use_cats_as_keywords,' /><label for="optimize_links_use_cats_as_keywords"> ',__('Use categories\'s name as keywords',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
								
								<li>
									<input type="checkbox" name="optimize_links_use_tags_as_keywords" ',$optimize_links_use_tags_as_keywords,' /><label for="optimize_links_use_tags_as_keywords"> ',__('Use tags\'s name as keywords',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>
								</li>
							</ul>
							
							<br />
							
							<h4>',__('Limits',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>				
							
							<p>',__('You can limit the maximum number of different links "Optimize Links" which will generate per post. Set to 0 for no limit.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							',__('Max Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : <input type="text" name="optimize_links_maxlinks" size="3" value="',$optimize_links_maxlinks,'" />  (Recomend from 2 to 5 links)
							
							<br><br>
							 
							<h2>',__('External Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h2>			
							<p>',__('"Optimize Links" can open external links in new window and add nofollow attribute.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							
							<input type="checkbox" name="optimize_links_nofolo" ',$optimize_links_nofolo,' /><label for="optimize_links_nofolo"> ',__('Add nofollow attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>  <br>
							<input type="checkbox" name="optimize_links_blanko" ',$optimize_links_blanko,' /><label for="optimize_links_blanko"> ',__('Open in new window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label>  <br><br>
							<label for="optimize_links_nofolo_blanko_exclude_urls"> ',__('Exclude urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label><input type="text" name="optimize_links_nofolo_blanko_exclude_urls" size="90" value="',$optimize_links_nofolo_blanko_exclude_urls,'"/><br><br>
							
							<h2>',__('Nofollow Links',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h2>	
							
							<p>',__('You may wish to add nofollow links (include internal links & external links). Separate them by comma. (contained in url)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							
							<label for="optimize_links_nofollow_urls"> ',__('Add nofollow attribute to urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label><input type="text" name="optimize_links_nofollow_urls" size="90" value="',$optimize_links_nofollow_urls,'"/><br>
							<br>
						
						</div>
						
						<div class="submit"><input type="submit" name="Submit" value="',__('Update Options',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" class="button-primary" /></div>
						
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

'; 
		
		
	}
	
	


}//class WPOptimizeByxTraffic 

endif; //if ( !class_exists('WPOptimizeByxTraffic') )



