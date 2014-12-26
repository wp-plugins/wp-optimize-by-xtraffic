<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_HeaderFooter.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeTraffic') ) :


class WPOptimizeByxTraffic_OptimizeTraffic extends WPOptimizeByxTraffic_HeaderFooter 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
		
		
	}
	
	
	
	public function optimize_traffic_check_system_ready() 
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
	
	
	
	
	
	
	public function optimize_traffic_remove_escaped_string($input_text)
	{
		$input_text = preg_replace('#______[a-z0-9\_]+______#',' ',$input_text);
		
		return $input_text;
	}
	
	public function optimize_traffic_the_content_filter($input_text)
	{
		if(is_single() || is_page() || is_singular()) {
		} else {
			return $input_text;
		}
		
		$keyCacheProcessText = array(
			__METHOD__
			,$input_text
			,'process_text'
		);
		
		$keyCacheProcessText = PepVN_Data::createKey($keyCacheProcessText);
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessText); 
		
		if($valueTemp) {
			return $valueTemp; 
		}
		
		
		
		
		global $wpdb, $post;
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(isset($options['optimize_traffic_modules']) && !PepVN_Data::isEmptyArray($options['optimize_traffic_modules'])) {
		} else {
			return $input_text;
		}
		
		
		
		
		$patternsEscaped1 = array();
		
		$rsOne = PepVN_Data::escapeByPattern($input_text,array(
			'pattern' => '#<([a-z]+)[^><]+class=(\'|\")[^><\'\"]*?wp\-caption[^><\'\"]*?\2[^><]*?>.*?</\1>#is'
			,'target_patterns' => array(
				0
			)
			,'wrap_target_patterns' => ''
		));
		
		$input_text = $rsOne['content'];
		
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped1 = array_merge($patternsEscaped1,$rsOne['patterns']);
		}
		$rsOne = 0;
		
		
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($input_text,'a;table;pre;ol;ul;blockquote');
		$input_text = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped1 = array_merge($patternsEscaped1, $rsOne['patterns']);
		}
		$rsOne = 0;
		
		
		
		$original_InputText1 = $input_text;
		
		$post->ID = (int)$post->ID;
		
		$rsGetTerms = $this->base_get_terms_by_post_id((int)$post->ID);
		
		$rsGetTerms2 = $rsGetTerms;
		$rsGetTerms2 = array_keys($rsGetTerms2);
		$rsGetTerms2 = $this->optimize_traffic_clean_terms($rsGetTerms2);
		
		$postExcerpt1 = $post->post_content;
		$postExcerpt1 = PepVN_Data::mb_substr($postExcerpt1, 0 , 360);
		
		
		$allPostTextCombined = $post->post_title.' '.PHP_EOL.' '.$post->post_excerpt.' '.PHP_EOL.implode(' ',$rsGetTerms2).' '.PHP_EOL.' '.$post->post_content;
		$allPostTextCombined = $this->optimize_traffic_remove_escaped_string($allPostTextCombined);
		
		
		$patternsModulesReplaceText = array();
		
		$groupModules_ByModuleType = array();
		$groupModules_PositionsAddedToQueue = array();
		$groupModules_ByFixedTypeBeginOrEnd = array();
		
		foreach($options['optimize_traffic_modules'] as $keyOne => $valueOne) {
			if(isset($valueOne['module_type']) && $valueOne['module_type']) {
				if(isset($valueOne['module_position'])) {
					
					if(!in_array($valueOne['module_position'], $groupModules_PositionsAddedToQueue)) {
						
						$valueOne['module_mumber_of_items'] = abs((int)$valueOne['module_mumber_of_items']);
						if($valueOne['module_mumber_of_items']<1) {
							$valueOne['module_mumber_of_items'] = 1;
						} else if($valueOne['module_mumber_of_items']>10) {
							$valueOne['module_mumber_of_items'] = 10;
						}
						
						
						$groupModules_PositionsAddedToQueue[] = $valueOne['module_position'];
						$groupModules_ByModuleType[$valueOne['module_type']][$valueOne['module_position']] = $valueOne;
						
					}
				}
			}
		}
		
		
		$numberElementContentInText = 0;
		
		preg_match_all('/<(p|h1|h2|h3|h4|h5|h6)(\s+[^><]*?)?>.*?<\/\1>/is',$original_InputText1,$matchedElementContentInText);
		
		if(isset($matchedElementContentInText[0]) && $matchedElementContentInText[0]) {
			if(count($matchedElementContentInText[0])>0) {
				foreach($matchedElementContentInText[0] as $key1 => $value1) {
					$valueTemp1 = $value1;
					$valueTemp1 = $this->base_get_clean_raw_text_for_process_search($valueTemp1);
					
					$checkStatus2 = false;
					if($valueTemp1) {
						$valueTemp2 = explode(' ',$valueTemp1);
						if(count($valueTemp2)>5) {
							$checkStatus2 = true;
						} else {
							if(preg_match('#<(h1|h2|h3|h4|h5|h6)(\s+[^><]*?)?>.*?</\1>#is',$value1,$matched1)) {
								$checkStatus2 = true;
							}
						}
					}
					
					if(!$checkStatus2) {
						unset($matchedElementContentInText[0][$key1]);
					}
				}
			}
		
			$numberElementContentInText = count($matchedElementContentInText[0]);
			
		}
		
		
		
		
		
		
		
		
		
		
		
		if(
			isset($groupModules_ByModuleType['flyout']) 
			&& (count($groupModules_ByModuleType['flyout'])>0)
		) {
			foreach($groupModules_ByModuleType['flyout'] as $keyOne => $valueOne) {
				
				if(isset($valueOne['module_type'])) {
					
					$postsIdsFound1 = array();
					
					$rsSearchPost1 = $this->optimize_traffic_search_post_by_text($allPostTextCombined, array(
						'group_text_weight' => array(
							array(
								'text' => $post->post_title
								,'weight' => 4
							)
							,array(
								'text' => $post->post_excerpt
								,'weight' => 3
							)
							,array(
								'text' => implode(' ',$rsGetTerms2)
								,'weight' => 3
							)
							,array(
								'text' => $postExcerpt1
								,'weight' => 2
							)
						)
						,'exclude_posts_ids' => array($post->ID)
						,'limit' => $valueOne['module_mumber_of_items']
						,'key_cache' => $valueOne['module_type'].'_'.$valueOne['module_position']
					));
					
					if($rsSearchPost1) {
						
						foreach($rsSearchPost1 as $keyTwo => $valueTwo) {
							$postsIdsFound1[] = $valueTwo['post_id'];
						}
					}
					
					
					if(count($postsIdsFound1)>0) {
						$rsCreateTrafficModule1 = $this->optimize_traffic_create_traffic_module(array(
							'option' => $valueOne
							,'data' => array(
								'posts_ids' => $postsIdsFound1
							)
						));
						
						if($rsCreateTrafficModule1['module']) {
							$input_text .= ' '.$rsCreateTrafficModule1['module'];
						}
						
					}
					
					
					
				}
			}
		}
		
		
		if(isset($groupModules_ByModuleType['fixed']) && !PepVN_Data::isEmptyArray($groupModules_ByModuleType['fixed'])) {
			
			foreach($groupModules_ByModuleType['fixed'] as $keyOne => $valueOne) {
				if(isset($valueOne['module_position'])) {
					$valueOne['module_position'] = (int)$valueOne['module_position'];
					if(
						(0 == $valueOne['module_position'])
						|| (100 == $valueOne['module_position'])
					) {
						$groupModules_ByFixedTypeBeginOrEnd[$valueOne['module_position']] = $valueOne;
						unset($groupModules_ByModuleType['fixed'][$keyOne]);
					}
				}
			}
			
			
			if(count($groupModules_ByFixedTypeBeginOrEnd)>0) {
				ksort($groupModules_ByFixedTypeBeginOrEnd);
				foreach($groupModules_ByFixedTypeBeginOrEnd as $keyOne => $valueOne) {
					
					if(isset($valueOne['module_position'])) {
						$valueOne['module_position'] = (int)$valueOne['module_position'];
						
						$postsIdsFound1 = array();
					
						$rsSearchPost1 = $this->optimize_traffic_search_post_by_text($allPostTextCombined, array(
							'group_text_weight' => array(
								array(
									'text' => $post->post_title
									,'weight' => 4
								)
								,array(
									'text' => $post->post_excerpt
									,'weight' => 3
								)
								,array(
									'text' => implode(' ',$rsGetTerms2)
									,'weight' => 3
								)
								,array(
									'text' => $postExcerpt1
									,'weight' => 2
								)
							)
							,'exclude_posts_ids' => array($post->ID)
							,'limit' => $valueOne['module_mumber_of_items']
							,'key_cache' => $valueOne['module_type'].'_'.$valueOne['module_position']
						));
						
						if($rsSearchPost1) {
							
							foreach($rsSearchPost1 as $keyTwo => $valueTwo) {
								$postsIdsFound1[] = $valueTwo['post_id'];
							}
						}
						
						if(count($postsIdsFound1)>0) {
							$rsCreateTrafficModule1 = $this->optimize_traffic_create_traffic_module(array(
								'option' => $valueOne
								,'data' => array(
									'posts_ids' => $postsIdsFound1
								)
							));
							
							if($rsCreateTrafficModule1['module']) {
								
								if(0 == $valueOne['module_position']) {
									$input_text = $rsCreateTrafficModule1['module'].' '.$input_text;
								} else if(100 == $valueOne['module_position']) {
									$input_text .= ' '.$rsCreateTrafficModule1['module'];
								}
							}
							
						}
						
						
						
					}
				}
			}
			
			
			
			
			
			
			
			
			if($numberElementContentInText>0) {
				
				ksort($groupModules_ByModuleType['fixed']);
				
				$arrayMatchedElementContentInTextIsProcessed = array();
			
				foreach($groupModules_ByModuleType['fixed'] as $keyOne => $valueOne) {
					if(isset($valueOne['module_position'])) {
						$valueOne['module_position'] = (int)$valueOne['module_position'];
						
						$originalTextNeedProcess1 = '';
						$rawTextNeedProcess1 = '';
						$iNumber1 = 0;
						foreach($matchedElementContentInText[0] as $keyTwo => $valueTwo) {
							
							if(!in_array($valueTwo,$arrayMatchedElementContentInTextIsProcessed)) {
								//$arrayMatchedElementContentInTextIsProcessed[] = $valueTwo;
								
								$originalTextNeedProcess1 .= ' '.$valueTwo;
								
								
							}
							
							$iNumber1++;
							$currentPercentPos = ($iNumber1 / $numberElementContentInText) * 100;
							$currentPercentPos = (int)$currentPercentPos;
							
							if(($currentPercentPos >= $valueOne['module_position']) && $originalTextNeedProcess1) {
								if(preg_match('#<(p)(\s+[^><]*?)?>.*?</\1>#is',$valueTwo,$matched2)) {
									
									$originalTextNeedProcess1 = $this->optimize_traffic_remove_escaped_string($originalTextNeedProcess1);
									
									$postsIdsFound1 = array();
									
									$rsSearchPost1 = $this->optimize_traffic_search_post_by_text($originalTextNeedProcess1, array(
										'group_text_weight' => array(
											array(
												'text' => $post->post_title
												,'weight' => 4
											)
											,array(
												'text' => $post->post_excerpt
												,'weight' => 3
											)
											,array(
												'text' => implode(' ',$rsGetTerms2)
												,'weight' => 3
											)
											,array(
												'text' => $originalTextNeedProcess1
												,'weight' => 8
											)
										)
										,'exclude_posts_ids' => array($post->ID)
										,'limit' => $valueOne['module_mumber_of_items']
										,'key_cache' => $valueOne['module_type'].'_'.$valueOne['module_position']
									));
									
									if($rsSearchPost1) {
										
										foreach($rsSearchPost1 as $keyThree => $valueThree) {
											$postsIdsFound1[] = $valueThree['post_id'];
										}
									}
									
									if(count($postsIdsFound1)>0) {
										$rsCreateTrafficModule1 = $this->optimize_traffic_create_traffic_module(array(
											'option' => $valueOne
											,'data' => array(
												'posts_ids' => $postsIdsFound1
											)
										));
										
										if($rsCreateTrafficModule1['module']) {
											$originalTextNeedProcess1 = '';
											$patternsModulesReplaceText_K = $valueTwo;
											$patternsModulesReplaceText_V = $valueTwo.' '.$rsCreateTrafficModule1['module'];
											$patternsModulesReplaceText[$patternsModulesReplaceText_K] = $patternsModulesReplaceText_V;
											break;
										}
										
									}
									
								}
							
							}
							
						}
						
					}
				}
				
				$arrayMatchedElementContentInTextIsProcessed = 0;
				
			}
			
		}
		
		
		
		if(count($patternsModulesReplaceText)>0) {
			foreach($patternsModulesReplaceText as $key1 => $value1) {
				$input_text = preg_replace('#'.PepVN_Data::preg_quote($key1).'#', $value1, $input_text, 1);
			}
			
		}
		
		
		if(count($patternsEscaped1)>0) {
			$input_text = str_replace(array_values($patternsEscaped1),array_keys($patternsEscaped1),$input_text);
		}
		
		return $input_text;
	}
	
	
	
	public function optimize_traffic_clean_terms($input_terms)
	{
		$input_terms = (array)$input_terms;
		
		$input_terms = implode(';',$input_terms);
		$input_terms = PepVN_Data::strtolower($input_terms);
		$input_terms = PepVN_Data::analysisKeyword_RemovePunctuations($input_terms, ';');
		$input_terms = PepVN_Data::reduceSpace($input_terms);
		
		$input_terms = explode(';',$input_terms);
		$input_terms = PepVN_Data::cleanArray($input_terms);
		
		
		return $input_terms;
		
	}
	
	
	public function optimize_traffic_search_post_by_text($input_text, $input_options = false)
	{
		
		
		$keyCacheMethod = array(
			__METHOD__
		);
		$keyCacheMethod = PepVN_Data::createKey($keyCacheMethod);
		
		
		
		
		if(!$input_options) {
			$input_options = array();
		}
		
		$input_options['limit'] = (int)$input_options['limit'];
		
		
		$input_options['exclude_posts_ids'] = (array)$input_options['exclude_posts_ids'];
		if(
			isset(PepVN_Data::$cacheData[$keyCacheMethod]['posts_ids_added']) 
			&& PepVN_Data::$cacheData[$keyCacheMethod]['posts_ids_added']
			&& !PepVN_Data::isEmptyArray(PepVN_Data::$cacheData[$keyCacheMethod]['posts_ids_added'])
		) {
			$valueTemp = array_keys(PepVN_Data::$cacheData[$keyCacheMethod]['posts_ids_added']);
			$input_options['exclude_posts_ids'] = array_merge($input_options['exclude_posts_ids'], $valueTemp);
		}
		
		$input_options['exclude_posts_ids'] = array_unique($input_options['exclude_posts_ids']);
		arsort($input_options['exclude_posts_ids']);
		$input_options['exclude_posts_ids'] = array_values($input_options['exclude_posts_ids']);
		
		if(!isset($input_options['key_cache'])) {
			$input_options['key_cache'] = array();
		}
		$input_options['key_cache'] = (array)$input_options['key_cache'];
		
		
		
		$keyCacheProcessText = array(
			$keyCacheMethod
			,$input_text
			,$input_options
			,'process_text'
		);
		
		$keyCacheProcessText = PepVN_Data::createKey($keyCacheProcessText);
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessText); 
		
		if($valueTemp) {
			return $valueTemp; 
		}
		
		
		$keyCacheGroupNameOfTagsAndCategories = array(
			__METHOD__
			,'groupNameOfTagsAndCategories'
		);
		
		$keyCacheGroupNameOfTagsAndCategories = PepVN_Data::createKey($keyCacheGroupNameOfTagsAndCategories);
		
		$groupNameOfTagsAndCategories = $this->cacheObj->get_cache($keyCacheGroupNameOfTagsAndCategories); 
		
		if(!$groupNameOfTagsAndCategories) {
			
			$groupNameOfTagsAndCategories = array();
			
			$valueTemp = $this->base_get_categories();
			$valueTemp = array_keys($valueTemp);
			$groupNameOfTagsAndCategories = array_merge($groupNameOfTagsAndCategories, $valueTemp);
			
			$valueTemp = $this->base_get_tags();
			$valueTemp = array_keys($valueTemp);
			$groupNameOfTagsAndCategories = array_merge($groupNameOfTagsAndCategories, $valueTemp);
			
			$groupNameOfTagsAndCategories = array_values($groupNameOfTagsAndCategories);
			
			$groupNameOfTagsAndCategories = $this->optimize_traffic_clean_terms($groupNameOfTagsAndCategories);
			
			$this->cacheObj->set_cache($keyCacheGroupNameOfTagsAndCategories, $groupNameOfTagsAndCategories);
			
		}
		
		
		
		
		$rsGetKeywordsFromText = PepVN_Data::analysisKeyword_GetKeywordsFromText(array(
			'contents' => $input_text
			,'min_word' => 1
			,'max_word' => 6
			,'min_occur' => 2
			,'min_char_each_word' => 3
		));
		
		
		$groupKeywordsFromText = array();
		if(isset($rsGetKeywordsFromText['data']) && is_array($rsGetKeywordsFromText['data'])) {
			foreach($rsGetKeywordsFromText['data'] as $keyOne => $valueOne) {
				if($valueOne && is_array($valueOne)) {
					foreach($valueOne as $keyTwo => $valueTwo) {
						if($keyTwo) {
							$valueTwo = (int)$valueTwo;
							$groupKeywordsFromText[$keyTwo] = ceil($valueTwo * (PepVN_Data::countWords($keyTwo) * 1));
						}
					}
				}
			}
		}
		
		arsort($groupKeywordsFromText);
		
		
		$groupKeywordsFromText2 = $groupKeywordsFromText;
		$groupKeywordsFromText2 = array_slice($groupKeywordsFromText2,0,10);
		
		$groupKeywordsFromText3 = array();
		foreach($groupKeywordsFromText as $keyOne => $valueOne) {
			if(in_array($keyOne, $groupNameOfTagsAndCategories)) {
				$groupKeywordsFromText3[$keyOne] = $valueOne;
			}
		}
		arsort($groupKeywordsFromText3);
		$groupKeywordsFromText3 = array_slice($groupKeywordsFromText3,0,10);
		
		
		
		$groupKeywordsFromText4 = array();
		
		foreach($groupKeywordsFromText2 as $keyOne => $valueOne) {
			if(!isset($groupKeywordsFromText4[$keyOne])) {
				$groupKeywordsFromText4[$keyOne] = 0;
			}
			$groupKeywordsFromText4[$keyOne] += (int)$valueOne;
		}
		
		
		foreach($groupKeywordsFromText3 as $keyOne => $valueOne) {
			if(!isset($groupKeywordsFromText4[$keyOne])) {
				$groupKeywordsFromText4[$keyOne] = 0;
			}
			$groupKeywordsFromText4[$keyOne] += (int)$valueOne * 2;
		}
		
		
		
		if(isset($input_options['group_text_weight']) && !PepVN_Data::isEmptyArray($input_options['group_text_weight'])) {
			foreach($input_options['group_text_weight'] as $keyOne => $valueOne) {
				if(isset($valueOne['text']) && $valueOne['text']) {
					$valueOne['text'] = $this->base_get_clean_raw_text_for_process_search($valueOne['text']);
					if($valueOne['text']) {
						foreach($groupKeywordsFromText4 as $keyTwo => $valueTwo) {
							$valueTemp2 = substr_count($valueOne['text'], $keyTwo);
							if($valueTemp2) {
								$groupKeywordsFromText4[$keyTwo] += (int)$valueTemp2 * $valueOne['weight'];
								
							}
							
						}
					
					}
				}
			}
		}
		
		
		$rsSearchPosts = $this->base_search_posts_by_fulltext(array(
			'keywords' => $groupKeywordsFromText4
			,'limit' => $input_options['limit']
			,'exclude_posts_ids' => $input_options['exclude_posts_ids']
		)); 
		
		if($rsSearchPosts) {
			foreach($rsSearchPosts as $key1 => $value1) {
				if(isset($value1['post_id']) && $value1['post_id']) {
					PepVN_Data::$cacheData[$keyCacheMethod]['posts_ids_added'][$value1['post_id']] = $value1;
				}
			}
		}
		
		
		
		$this->cacheObj->set_cache($keyCacheProcessText, $rsSearchPosts);
		
		
		return $rsSearchPosts;
	}
	
	
	
	
	
	public function optimize_traffic_preview_optimize_traffic_modules($input_parameters)
	{
		$resultData = array();
		
		if(isset($input_parameters['preview_optimize_traffic_modules']) && $input_parameters['preview_optimize_traffic_modules']) {
			$input_parameters['preview_optimize_traffic_modules'] = (array)$input_parameters['preview_optimize_traffic_modules'];
			
			$modulesIds = array();
			
			foreach($input_parameters['preview_optimize_traffic_modules'] as $key1 => $value1) {
				if(false !== strpos($key1,'[module_id]')) {
					$modulesIds[] = $value1;
				}
			}
			
			$modulesData = array();
			
			$modulesIds = PepVN_Data::cleanArray($modulesIds);
			if(count($modulesIds)>0) {
				foreach($modulesIds as $key1 => $value1) {
					foreach($input_parameters['preview_optimize_traffic_modules'] as $key2 => $value2) {
						if($key2) {
							$key2 = preg_replace('#optimize_traffic_modules\[[^\]]+\]\[([^\]]+)\]#','\1',$key2);
							$key2 = trim($key2);
							if($key2) {
								$modulesData[$value1][$key2] = $value2;
							}
						}
					}
				}
			}
			
			if(count($modulesData)>0) {
				foreach($modulesData as $key1 => $value1) {
					
					$resultData = $this->optimize_traffic_create_traffic_module(array(
						'option' => $value1
					));
					
				}
			}
			
		}
		
		
		
		return $resultData;
	}
	
	
	
	
	
	
	
	
	public function optimize_traffic_create_traffic_module($input_parameters)
	{
		global $wpdb;
		
		
		$resultData = array(
			'module' => ''
			,'module_id' => ''
		);
		
		if(isset($input_parameters['option']['module_id']) && $input_parameters['option']['module_id']) {
			
			
			$nsModule = 'wpoptimizebyxtraffic_module_traffic';
			
			$resultData['module_id'] = $input_parameters['option']['module_id'];
			
			
			
			
			$isModuleNoText = true;
			if(isset($input_parameters['option']['enable_items_title']) && $input_parameters['option']['enable_items_title']) {
				$isModuleNoText = false;
			}
			
			if(isset($input_parameters['option']['enable_items_excerpt']) && $input_parameters['option']['enable_items_excerpt']) {
				$isModuleNoText = false;
			}
			
			if($isModuleNoText) {
				$input_parameters['option']['module_style'] = 'style_2';
			}
			
			if(!isset($input_parameters['option']['title_of_module'])) {
				$input_parameters['option']['title_of_module'] = '';//Related article
			}
			
			if(!isset($input_parameters['option']['custom_class_css_of_module'])) {
				$input_parameters['option']['custom_class_css_of_module'] = '';
			}
			
			if(!isset($input_parameters['option']['custom_id_css_of_module'])) {
				$input_parameters['option']['custom_id_css_of_module'] = '';
			}
			
			if(!isset($input_parameters['option']['module_type'])) {
				$input_parameters['option']['module_type'] = 'fixed';
			}
			
			
			if(!isset($input_parameters['option']['module_mumber_of_items'])) {
				$input_parameters['option']['module_mumber_of_items'] = 1;
			}
			$input_parameters['option']['module_mumber_of_items'] = abs((int)$input_parameters['option']['module_mumber_of_items']);
			if($input_parameters['option']['module_mumber_of_items']>10) {
				$input_parameters['option']['module_mumber_of_items'] = 10;
			} else if($input_parameters['option']['module_mumber_of_items']<1) {
				$input_parameters['option']['module_mumber_of_items'] = 1;
			}
			
			
			if(!isset($input_parameters['option']['thumbnail_width'])) {
				$input_parameters['option']['thumbnail_width'] = 0;
			}
			$input_parameters['option']['thumbnail_width'] = abs((int)$input_parameters['option']['thumbnail_width']);
			
			if(!isset($input_parameters['option']['thumbnail_height'])) {
				$input_parameters['option']['thumbnail_height'] = 0;
			}
			$input_parameters['option']['thumbnail_height'] = abs((int)$input_parameters['option']['thumbnail_height']);
			
			
			if(!isset($input_parameters['option']['maximum_width_each_item'])) {
				$input_parameters['option']['maximum_width_each_item'] = 0;
			}
			$input_parameters['option']['maximum_width_each_item'] = abs((int)$input_parameters['option']['maximum_width_each_item']);
			
			
			
			if(isset($input_parameters['option']['maximum_number_characters_items_title']) && $input_parameters['option']['maximum_number_characters_items_title']) {
			} else {
				$input_parameters['option']['maximum_number_characters_items_title'] = 60;
			}
			$input_parameters['option']['maximum_number_characters_items_title'] = abs((int)$input_parameters['option']['maximum_number_characters_items_title']);
			
			
			
			if(isset($input_parameters['option']['maximum_number_characters_items_excerpt']) && $input_parameters['option']['maximum_number_characters_items_excerpt']) {
			} else {
				$input_parameters['option']['maximum_number_characters_items_excerpt'] = 120;
			}
			$input_parameters['option']['maximum_number_characters_items_excerpt'] = abs((int)$input_parameters['option']['maximum_number_characters_items_excerpt']);
			
			
			if(isset($input_parameters['option']['module_appear_when_user_scroll_length']) && $input_parameters['option']['module_appear_when_user_scroll_length']) {
			} else {
				$input_parameters['option']['module_appear_when_user_scroll_length'] = '80%';
			}
			
			
			if(isset($input_parameters['option']['module_appear_when_user_read_for_seconds']) && $input_parameters['option']['module_appear_when_user_read_for_seconds']) {
			} else {
				$input_parameters['option']['module_appear_when_user_read_for_seconds'] = 0;
			}
			$input_parameters['option']['module_appear_when_user_read_for_seconds'] = abs((int)$input_parameters['option']['module_appear_when_user_read_for_seconds']);
			
			
			
			if(isset($input_parameters['option']['module_margin_bottom']) && $input_parameters['option']['module_margin_bottom']) {
			} else {
				$input_parameters['option']['module_margin_bottom'] = 0;
			}
			$input_parameters['option']['module_margin_bottom'] = (int)$input_parameters['option']['module_margin_bottom'];
			
			
			if(isset($input_parameters['option']['module_margin_left']) && $input_parameters['option']['module_margin_left']) {
			} else {
				$input_parameters['option']['module_margin_left'] = 0;
			}
			$input_parameters['option']['module_margin_left'] = (int)$input_parameters['option']['module_margin_left'];
			
			
			
			
			
			if(isset($input_parameters['data']['posts_ids']) && (!PepVN_Data::isEmptyArray($input_parameters['data']['posts_ids']))) {
			} else {
				
				$input_parameters['data']['posts_ids'] = array();
			
				$queryString1 = '
	SELECT ID
	FROM '.$wpdb->posts.'
	WHERE ( ( post_status = \'publish\') AND ( post_type = \'post\' ) )
	ORDER BY RAND()
	LIMIT 0,'.$input_parameters['option']['module_mumber_of_items'];
				$rsOne = $wpdb->get_results($queryString1);
			
			
				if($rsOne) {
					foreach($rsOne as $keyOne => $valueOne) {
						if($valueOne) {
							if(isset($valueOne->ID) && $valueOne->ID) {
								$input_parameters['data']['posts_ids'][] = $valueOne->ID;
							}
						}
					}
				}
			
			}
			
			
			
			$input_parameters['data']['posts_ids'] = (array)$input_parameters['data']['posts_ids'];
			$input_parameters['data']['posts_ids'] = array_unique($input_parameters['data']['posts_ids']);
			
			
			$moduleDataPlus = array();
			
			$moduleClassPlus = array();
			$moduleClassPlus[] = $nsModule.'_'.$input_parameters['option']['module_type'];
			$moduleClassPlus[] = $nsModule.'_'.$input_parameters['option']['module_style'];
			$moduleClassPlus[] = $input_parameters['option']['custom_class_css_of_module'];
			
			if(isset($input_parameters['option']['enable_thumbnails']) && $input_parameters['option']['enable_thumbnails']) {
				$moduleClassPlus[] = $nsModule.'_enable_thumbnails';
			}
			
			if('flyout' === $input_parameters['option']['module_type']) {
				if(isset($input_parameters['option']['module_position']) && $input_parameters['option']['module_position']) {
					$moduleClassPlus[] = $nsModule.'_side_'.$input_parameters['option']['module_position'];
				}
				
				$moduleClassPlus[] = 'wpoptxtr_shawy';
			}
			
			
			
			
			
			$moduleStylePlus = array();
			if('flyout' === $input_parameters['option']['module_type']) {
				if('style_1' === $input_parameters['option']['module_style']) {
					if($input_parameters['option']['maximum_width_each_item']>0) {
						$valueTemp = $input_parameters['option']['maximum_width_each_item'];
						$valueTemp = $valueTemp * 1.1;
						$valueTemp = (int)$valueTemp;
						$moduleStylePlus[] = 'width:'.$valueTemp.'px;max-width:'.$valueTemp.'px;';
					}
					
					
				}
			}
			
			if(0 != $input_parameters['option']['module_margin_bottom']) {
				$valueTemp = (int)$input_parameters['option']['module_margin_bottom'];
				$moduleStylePlus[] = 'margin-bottom:'.$valueTemp.'px;';
			}
			
			
			if(0 != $input_parameters['option']['module_margin_left']) {
				$valueTemp = (int)$input_parameters['option']['module_margin_left'];
				$moduleStylePlus[] = 'margin-left:'.$valueTemp.'px;';
			}
			
			
			
			$moduleDataPlus[] = 'pepvn_data_module_appear_when_user_read_for_seconds="'.$input_parameters['option']['module_appear_when_user_read_for_seconds'].'"';
			$moduleDataPlus[] = 'pepvn_data_module_appear_when_user_scroll_length="'.$input_parameters['option']['module_appear_when_user_scroll_length'].'"';
			$moduleDataPlus[] = 'pepvn_data_module_position="'.$input_parameters['option']['module_position'].'"';
			$moduleDataPlus[] = 'pepvn_data_module_id="'.$input_parameters['option']['module_id'].'"';
			
			
			$resultData['module'] .= '
	
			<div class="wpoptimizebyxtraffic_module_traffic '.implode(' ',$moduleClassPlus).'" id="'.$input_parameters['option']['module_id'].'" pepvn_data_options="'.PepVN_Data::encodeVar($input_parameters['option']).'" style="'.implode(';',$moduleStylePlus).'" '.implode(' ',$moduleDataPlus).' >';
			
			if('flyout' === $input_parameters['option']['module_type']) {
				$resultData['module'] .= '
				<span class="wpoptimizebyxtraffic_module_traffic_button_show_wrapper"></span>
				
				<span class="wpoptimizebyxtraffic_module_traffic_button_close"></span>
				';
			}
			
			$resultData['module'] .= '
				
				<span class="wpoptimizebyxtraffic_module_traffic_title"><strong>'.$input_parameters['option']['title_of_module'].'</strong></span>
				
				<ul>';
				
			foreach($input_parameters['data']['posts_ids'] as $keyOne => $valueOne) {
				
				if($valueOne) {
					$valueOne = (int)$valueOne;
					if($valueOne>0) {
						
						$rsGetPost1 = $this->base_get_post_by_id($valueOne);
						
						if($rsGetPost1) {
						
							if(isset($rsGetPost1->pepvn_PostPermalink) && $rsGetPost1->pepvn_PostPermalink) {
								
								
								$thumbnailUrl1 = '';
								
								if(isset($input_parameters['option']['enable_thumbnails']) && $input_parameters['option']['enable_thumbnails']) {
									
									if(isset($input_parameters['option']['default_thumbnail_url']) && $input_parameters['option']['default_thumbnail_url']) {
										$thumbnailUrl1 = $input_parameters['option']['default_thumbnail_url'];
									}
									
									if(isset($rsGetPost1->pepvn_PostThumbnailUrl) && $rsGetPost1->pepvn_PostThumbnailUrl) {
										$thumbnailUrl1 = $rsGetPost1->pepvn_PostThumbnailUrl;
									} else {
										if(isset($rsGetPost1->pepvn_PostImages) && $rsGetPost1->pepvn_PostImages && (!PepVN_Data::isEmptyArray($rsGetPost1->pepvn_PostImages))) {
											$postImages1 = $rsGetPost1->pepvn_PostImages;
											shuffle($postImages1);
											$thumbnailUrl1 = $postImages1[0];
										}
									}
									
									
								}
								
								
								$thumbnailUrl1 = trim($thumbnailUrl1);
								
								
								
								$itemClassPlus = array();
								if($thumbnailUrl1) {
									$itemClassPlus[] = $nsModule.'_item_has_thumbnail';
								}
								
								$resultData['module'] .= '
					<li class="wpoptimizebyxtraffic_module_traffic_item '.implode(' ',$itemClassPlus).'" style="';
								if($input_parameters['option']['maximum_width_each_item']>0) {
									$resultData['module'] .= 'width:'.$input_parameters['option']['maximum_width_each_item'].'px;max-width:'.$input_parameters['option']['maximum_width_each_item'].'px;';
								}
								
								$resultData['module'] .= '" >
					
						<a href="'.$rsGetPost1->pepvn_PostPermalink.'" class="wpoptimizebyxtraffic_module_traffic_item_anchor" '.(
							isset($input_parameters['option']['enable_open_links_in_new_windows']) ? ' target="_blank" ' : ''
						).' title="'.$rsGetPost1->post_title.'" >';
								
								if($thumbnailUrl1) {
									
									$styleImg1 = '';
									
									if($input_parameters['option']['thumbnail_width']>0) {
										$styleImg1 .= 'width:'.$input_parameters['option']['thumbnail_width'].'px;max-width:'.$input_parameters['option']['thumbnail_width'].'px;';
									}
									if($input_parameters['option']['thumbnail_height']>0) {
										$styleImg1 .= 'height:'.$input_parameters['option']['thumbnail_height'].'px;max-height:'.$input_parameters['option']['thumbnail_height'].'px;';
									}
									
									
								
									if('style_1' === $input_parameters['option']['module_style']) {
										
										$resultData['module'] .= '
							<img class="wpoptimizebyxtraffic_module_traffic_item_img" src="'.$thumbnailUrl1.'" style="'.$styleImg1.'" /> ';
											
									} else {
										
										$resultData['module'] .= '
						<span class="wpoptimizebyxtraffic_module_traffic_item_img" style="'.$styleImg1.'" >
							<img src="'.$thumbnailUrl1.'" style="'.$styleImg1.'" />
						</span>';
									}
										
										
									
								}
								
								$postTitle1 = $rsGetPost1->post_title;
								$postTitle1 = $this->base_remove_shortcodes($postTitle1);
								if($input_parameters['option']['maximum_number_characters_items_title']>0) {
									if(PepVN_Data::mb_strlen($postTitle1) > $input_parameters['option']['maximum_number_characters_items_title']) {
										$postTitle1 = PepVN_Data::mb_substr($postTitle1, 0, $input_parameters['option']['maximum_number_characters_items_title']).'...';
									}
								}
								
								
								$postExcerpt1 = $rsGetPost1->post_excerpt;
								if(!$postExcerpt1) {
									$postExcerpt1 = $rsGetPost1->pepvn_PostContentRawText;
									$postExcerpt1 = PepVN_Data::mb_substr($postExcerpt1, 0, 350).'...';
								}
								
								$postExcerpt1 = $this->base_remove_shortcodes($postExcerpt1);
								
								if($input_parameters['option']['maximum_number_characters_items_excerpt']>0) {
									if(PepVN_Data::mb_strlen($postExcerpt1) > $input_parameters['option']['maximum_number_characters_items_excerpt']) {
										$postExcerpt1 = PepVN_Data::mb_substr($postExcerpt1, 0, $input_parameters['option']['maximum_number_characters_items_excerpt']).'...';
									}
								}
								
								
								
								if('style_1' === $input_parameters['option']['module_style']) {
								
									if(isset($input_parameters['option']['enable_items_title']) && $input_parameters['option']['enable_items_title']) {
										$resultData['module'] .= '<strong class="wpoptimizebyxtraffic_module_traffic_item_title">'.$postTitle1.'</strong>';
									}
								
									if(isset($input_parameters['option']['enable_items_excerpt']) && $input_parameters['option']['enable_items_excerpt']) {
										if($postExcerpt1) {
											$resultData['module'] .= '<br /><span class="wpoptimizebyxtraffic_module_traffic_item_excerpt">'.$postExcerpt1.'</span>';
										}
									}
									
								} else {
									$resultData['module'] .= '
						<span class="wpoptimizebyxtraffic_module_traffic_item_text">';
									
									if(isset($input_parameters['option']['enable_items_title']) && $input_parameters['option']['enable_items_title']) {
										$resultData['module'] .= '
							<span class="wpoptimizebyxtraffic_module_traffic_item_title"><strong>'.$postTitle1.'</strong></span>';
									}
									
									
								
									if(isset($input_parameters['option']['enable_items_excerpt']) && $input_parameters['option']['enable_items_excerpt']) {
										if($postExcerpt1) {
											$resultData['module'] .= '
							<span class="wpoptimizebyxtraffic_module_traffic_item_excerpt">'.$postExcerpt1.'</span>';
									}
									
								}
								
									$resultData['module'] .= '
						</span>';
								}
						
						
						
						
						
								$resultData['module'] .= '
						</a>
					</li>';
								
							
							}
						}
						
					}
				}
			}
			
			$resultData['module'] .= '
				</ul>
			</div>';
			
			
			
			
		}
		
		
		
		
		return $resultData;
	}
	
	
	public function optimize_traffic_create_traffic_module_options($input_parameters = false)
	{
		$resultData = array(
			'module' => ''
			,'module_id' => ''
		);
		
		if(!$input_parameters) {
			$input_parameters = array();
		}
		
		$moduleId = '';
		if(isset($input_parameters['module_id']) && $input_parameters['module_id']) {
			$moduleId = $input_parameters['module_id'];
		}
		
		if(!$moduleId) {
			$moduleId = PepVN_Data::mcrc32(PepVN_Data::randomHash());
		}
		
		$resultData['module_id'] = $moduleId;
		
		if('traffic_module_sample_id' === $moduleId) {
			$input_parameters['moduleOptionsData']['enable_thumbnails'] = 'on';
			$input_parameters['moduleOptionsData']['enable_items_title'] = 'on';
			$input_parameters['moduleOptionsData']['enable_items_excerpt'] = 'on';
			$input_parameters['moduleOptionsData']['title_of_module'] = 'Related articles :';
			
			$input_parameters['moduleOptionsData']['enable_open_links_in_new_windows'] = 'on';
			
		}
		
		if(!isset($input_parameters['moduleOptionsData']['maximum_number_characters_items_title'])) {
			$input_parameters['moduleOptionsData']['maximum_number_characters_items_title'] = '60';
		}
		
		if(!isset($input_parameters['moduleOptionsData']['maximum_number_characters_items_excerpt'])) {
			$input_parameters['moduleOptionsData']['maximum_number_characters_items_excerpt'] = '120';
		}
		
		
		
		
		$resultData['module'] .= '

		<div id="'.$moduleId.'" class="wpoptimizebyxtraffic_green_block optimize_traffic_module_container">
			
			<h5 class="optimize_traffic_module_container_head">Traffic Module - ID : <span>'.$moduleId.'</span> - <a href="#" class="optimize_traffic_module_button_remove">Remove Module</a> - <a href="#" style="font-size: 80%;" class="optimize_traffic_module_button_minimize_maximize">Minimize/Maximize</a></h5>
			
			<input type="hidden" name="optimize_traffic_modules['.$moduleId.'][module_id]" value="'.$moduleId.'" /> 
			
			<div class="optimize_traffic_module_container_body">
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					<h6>
						<span class="optimize_traffic_module_options_tilte">Module Type</span> : 
						<select name="optimize_traffic_modules['.$moduleId.'][module_type]" style="width: 200px;margin-left: 2%;">
							<option value="fixed" '.(
								(isset($input_parameters['moduleOptionsData']['module_type']) && ('fixed' === $input_parameters['moduleOptionsData']['module_type'])) ? ' selected="selected" ' : ''
							).' >Fixed</option>
							<option value="flyout" '.(
								(isset($input_parameters['moduleOptionsData']['module_type']) && ('flyout' === $input_parameters['moduleOptionsData']['module_type'])) ? ' selected="selected" ' : ''
							).' >Flyout</option>
						</select>
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>Fixed : '.__('Module will appear at fixed location as your choice in post\'s content',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
									<li>Flyout : '.__('Module will appear on the right or left of user\'s screen',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
					</h6>
					
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">Module Style</span> : 
						<select name="optimize_traffic_modules['.$moduleId.'][module_style]" style="width: 200px;margin-left: 2%;">
							<option value="style_1"  '.(
								(isset($input_parameters['moduleOptionsData']['module_style']) && ('style_1' === $input_parameters['moduleOptionsData']['module_style'])) ? ' selected="selected" ' : ''
							).' >Style 1</option>
							<option value="style_2" '.(
								(isset($input_parameters['moduleOptionsData']['module_style']) && ('style_2' === $input_parameters['moduleOptionsData']['module_style'])) ? ' selected="selected" ' : ''
							).' >Style 2</option>
						</select>
					</h6>
					
				</div>
				
				
				<div class="optimize_traffic_module_options postbox wpoptimizebyxtraffic_hide" style="padding-top: 12px; padding-bottom: 12px;display:none;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">Display Animation Type</span> : 
						<select name="optimize_traffic_modules['.$moduleId.'][animation_type]" style="width: 200px;margin-left: 2%;">
							<option value="slideout">Slideout</option>
							<option value="fade">Fade</option>
						</select>
					</h6>
					
				</div>
				
				
				
				
				<div class="optimize_traffic_module_options postbox wpoptimizebyxtraffic_hide" style="padding-top: 12px; padding-bottom: 12px;">
					<h6>
						'.__('When should the Module appear?',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'
					</h6>
					
					<br />
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('When user scroll length of site\'s height',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'(px or %)</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][module_appear_when_user_scroll_length]" value="'.(
							isset($input_parameters['moduleOptionsData']['module_appear_when_user_scroll_length']) ? $input_parameters['moduleOptionsData']['module_appear_when_user_scroll_length'] : ''
						).'" type="text" style="width:300px;margin-left: 2%;" />
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>'.__('When the user scrolls to the location you set, the module will appear.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
									<li>'.__('You can set up the "80%" or "80" (px). All values are based on the height of the site',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
					</h6>
					
					<br />
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('When user view for seconds',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][module_appear_when_user_read_for_seconds]" value="'.(
							isset($input_parameters['moduleOptionsData']['module_appear_when_user_read_for_seconds']) ? $input_parameters['moduleOptionsData']['module_appear_when_user_read_for_seconds'] : ''
						).'" type="text" style="width:300px;margin-left: 2%;" />
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>'.__('When the user access and view your website in number of seconds, the module will appear.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
						
					</h6>
					
					
				</div>
				
				
				<div class="optimize_traffic_module_options postbox wpoptimizebyxtraffic_hide" style="padding-top: 12px; padding-bottom: 12px;">
													
					<h6>
						<span class="optimize_traffic_module_options_tilte">Margin bottom (px)</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][module_margin_bottom]" value="'.(
							isset($input_parameters['moduleOptionsData']['module_margin_bottom']) ? $input_parameters['moduleOptionsData']['module_margin_bottom'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
					</h6>
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">Margin left (px)</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][module_margin_left]" value="'.(
							isset($input_parameters['moduleOptionsData']['module_margin_left']) ? $input_parameters['moduleOptionsData']['module_margin_left'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
					</h6>
					
					
				</div>
				
				';
				
				$resultData['module'] .= '
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Position of Module',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<select name="optimize_traffic_modules['.$moduleId.'][module_position]" style="width: 200px;margin-left: 2%;" pepvn_data_val="'.(
							isset($input_parameters['moduleOptionsData']['module_position']) ? $input_parameters['moduleOptionsData']['module_position'] : ''
						).'" >';
				
				$resultData['module'] .= '
						</select> 
						
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>'.__('When the position is %, module will appear in post\'s content corresponding to the value you set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
									<li>'.__('When the position is Left/Right, module will appear on Left/Right side of user\'s screen',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
						
					</h6>
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Title of Module',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][title_of_module]" value="'.(
							isset($input_parameters['moduleOptionsData']['title_of_module']) ? $input_parameters['moduleOptionsData']['title_of_module'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
					</h6>
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">Custom class (CSS) of Module</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][custom_class_css_of_module]"  value="'.(
							isset($input_parameters['moduleOptionsData']['custom_class_css_of_module']) ? $input_parameters['moduleOptionsData']['custom_class_css_of_module'] : ''
						).'" type="text" style="width:300px;margin-left: 2%;" placeholder="Ex : your_custom_class_1 your_custom_class_2" />
						
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>'.__('This option will help you design module according to your wishes through CSS',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
					</h6>
					
					<h6 style="display:none;">
						<span class="optimize_traffic_module_options_tilte">Custom ID (CSS) of Module</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][custom_id_css_of_module]" type="text" style="width:300px;margin-left: 2%;" placeholder="Ex : your_custom_id" />
					</h6>
					
				</div>
				
				
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Maximum Number of Items',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<select name="optimize_traffic_modules['.$moduleId.'][module_mumber_of_items]" style="width: 200px;margin-left: 2%;">';
				for($iOne = 1; $iOne<11; $iOne++) {
					$resultData['module'] .= '
							<option value="'.$iOne.'" '.(
						(
							isset($input_parameters['moduleOptionsData']['module_mumber_of_items']) 
							&& ($iOne == $input_parameters['moduleOptionsData']['module_mumber_of_items'])
						) ? ' selected="selected" ' : ''
					).' >'.$iOne.'</option>';
				}
							
				$resultData['module'] .= '
						</select>
						
						<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
							base64_encode(
								'<ul>
									<li>'.__('Maximum number of items (posts) in this module',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
								</ul>'
							)
						).'"></span>
					</h6>
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">Thumbnails</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][enable_thumbnails]" type="checkbox" style="margin-left: 2%;"  '.(
							isset($input_parameters['moduleOptionsData']['enable_thumbnails']) ? ' checked="checked" ' : ''
						).'  /> '.__('Enable Thumbnails',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'
					</h6>
					
					<div class="wpoptimizebyxtraffic_hide  wpoptimizebyxtraffic_enabled_thumbnails">
						
						<h6>
							<span class="optimize_traffic_module_options_tilte">'.__('Default Thumbnail Url (include http:// or https://)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
							<input name="optimize_traffic_modules['.$moduleId.'][default_thumbnail_url]" value="'.(
							isset($input_parameters['moduleOptionsData']['default_thumbnail_url']) ? $input_parameters['moduleOptionsData']['default_thumbnail_url'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;"  />
						
							<span class="wpoptimizebyxtraffic_help_icon wpoptimizebyxtraffic_tooltip" title="" data_content="'.(
								base64_encode(
									'<ul>
										<li>'.__('When the item does not have thumbnail image, plugin will get this image to make an thumbnail',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</li>
									</ul>'
								)
							).'"></span>
							
						</h6>
						
						<h6>
							<span class="optimize_traffic_module_options_tilte">'.__('Thumbnail Width',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' (px)</span> : 
							<input name="optimize_traffic_modules['.$moduleId.'][thumbnail_width]" value="'.(
							isset($input_parameters['moduleOptionsData']['thumbnail_width']) ? (int)$input_parameters['moduleOptionsData']['thumbnail_width'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
						</h6>
						
						<h6>
							<span class="optimize_traffic_module_options_tilte">'.__('Thumbnail Height',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' (px)</span> : 
							<input name="optimize_traffic_modules['.$moduleId.'][thumbnail_height]" value="'.(
							isset($input_parameters['moduleOptionsData']['thumbnail_height']) ? (int)$input_parameters['moduleOptionsData']['thumbnail_height'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
						</h6>
						
					</div>
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;"> 
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Maximum width of each item?',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' (px)</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][maximum_width_each_item]" value="'.(
							isset($input_parameters['moduleOptionsData']['maximum_width_each_item']) ? (int)$input_parameters['moduleOptionsData']['maximum_width_each_item'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
					</h6>
					
				</div>
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Items\'s Title',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][enable_items_title]" type="checkbox" style="margin-left: 2%;" '.(
							isset($input_parameters['moduleOptionsData']['enable_items_title']) ? ' checked="checked" ' : ''
						).'  /> Enable Items\'s Title
					</h6>
					
					<div class="wpoptimizebyxtraffic_hide wpoptimizebyxtraffic_enabled_items_title">
						<h6>
							<span class="optimize_traffic_module_options_tilte">'.__('Maximum number of characters for items\'s title?',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
							<input name="optimize_traffic_modules['.$moduleId.'][maximum_number_characters_items_title]"  value="'.(
							isset($input_parameters['moduleOptionsData']['maximum_number_characters_items_title']) ? (int)$input_parameters['moduleOptionsData']['maximum_number_characters_items_title'] : ''
						).'" type="text" style="width:300px;margin-left: 2%;" />
						</h6>
					</div>
					
				</div>
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Items\'s Excerpt',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][enable_items_excerpt]" type="checkbox" style="margin-left: 2%;" '.(
							isset($input_parameters['moduleOptionsData']['enable_items_excerpt']) ? ' checked="checked" ' : ''
						).'  /> '.__('Enable Items\'s Excerpt',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'
					</h6>
					
					<div class="wpoptimizebyxtraffic_hide wpoptimizebyxtraffic_enabled_items_excerpt">
						<h6>
							<span class="optimize_traffic_module_options_tilte">'.__('Maximum number of characters for items\'s excerpt?',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
							<input name="optimize_traffic_modules['.$moduleId.'][maximum_number_characters_items_excerpt]"  value="'.(
							isset($input_parameters['moduleOptionsData']['maximum_number_characters_items_excerpt']) ? (int)$input_parameters['moduleOptionsData']['maximum_number_characters_items_excerpt'] : ''
						).'"  type="text" style="width:300px;margin-left: 2%;" />
						</h6>
					</div>
					
				</div>
				
				
				
				<div class="optimize_traffic_module_options postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Open Links In New Window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</span> : 
						<input name="optimize_traffic_modules['.$moduleId.'][enable_open_links_in_new_windows]" type="checkbox" style="margin-left: 2%;" '.(
							isset($input_parameters['moduleOptionsData']['enable_open_links_in_new_windows']) ? ' checked="checked" ' : ''
						).'  /> '.__('Enable Open Links In New Window',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'
					</h6>
					
					
				</div> 
				
				
				
				<div class="optimize_traffic_module_options optimize_traffic_module_preview_container postbox" style="padding-top: 12px; padding-bottom: 12px;">
					
					<h6>
						<span class="optimize_traffic_module_options_tilte">'.__('Preview Module',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' ( <a href="#" class="optimize_traffic_module_preview_button_show_me"><b>'.__('Show me',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b></a> )</span> : 
					</h6>
					
					<div class="optimize_traffic_module_preview postbox" style="padding: 12px;margin-top: 26px;">
						
					</div>
					
					
					
				</div>
			</div>
			
		</div>
		
		';
		
		
		return $resultData;
		
		
	}
	
	
	public function optimize_traffic_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];	
		
		

		$header_footer_code_add_head_home = $options['header_footer_code_add_head_home'];
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
		
		
		$rsTemp = $this->optimize_traffic_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		
		
		$rsTrafficModuleSample = $this->optimize_traffic_create_traffic_module_options(array(
			'module_id' => 'traffic_module_sample_id'
		));
		
		
		$modulesOptionsText = '';
		
		if(isset($options['optimize_traffic_modules']) && !PepVN_Data::isEmptyArray($options['optimize_traffic_modules'])) {
			foreach($options['optimize_traffic_modules'] as $keyOne => $valueOne) {
				$rsOne = $this->optimize_traffic_create_traffic_module_options(array(
					'module_id' => $valueOne['module_id']
					,'moduleOptionsData' => $valueOne
				));
				if(isset($rsOne['module']) && $rsOne['module']) {
					$modulesOptionsText .= ' '.$rsOne['module'];
				}
			}
			
		}
		
		
		echo '
<script language="javascript" type="text/javascript">
	var wpOptimizeByxTraffic_Request_Nonce = "',$nonce,'";
</script>
		';
		
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2>WP Optimize By xTraffic (Optimize Traffic)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="optimize_traffic_submitted" value="1" /> 
						
						<h3 class="optimize_traffic_button_add_traffic_module_container">
							<a href="#" class="button optimize_traffic_button_add_traffic_module"><b>Add Traffic Module</b></a>
						</h3> 
						
						',$modulesOptionsText,'
						
						<div style="display:none;" class="wpoptimizebyxtraffic_hide optimize_traffic_traffic_module_sample" data_module_sample="',base64_encode($rsTrafficModuleSample['module']),'" ></div>
						
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





