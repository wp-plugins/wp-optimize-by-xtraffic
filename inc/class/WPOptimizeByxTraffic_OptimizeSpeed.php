<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeLinks.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeSpeed') ) :


class WPOptimizeByxTraffic_OptimizeSpeed extends WPOptimizeByxTraffic_OptimizeLinks 
{
	
	private $optimize_speed_getUrlContentCacheTimeout = 86400;
	
	private $optimize_speed_loadJsTimeDelay = 1000;//miliseconds
	private $optimize_speed_loadCssTimeDelay = 10;//miliseconds
	
	private $optimize_speed_numberLoadCssAsync = 0;
	
	public function __construct() 
	{
	
		parent::__construct();
		
		
		$plusPathAndUrl1 = 'static-files/';
		$this->optimize_speed_UploadsStaticFilesFolderPath = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN . $plusPathAndUrl1;
		$this->optimize_speed_UploadsStaticFilesFolderUrl = WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_URL_CACHE_PEPVN . $plusPathAndUrl1;
		if(!file_exists($this->optimize_speed_UploadsStaticFilesFolderPath)) {
			
			PepVN_Data::createFolder($this->optimize_speed_UploadsStaticFilesFolderPath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($this->optimize_speed_UploadsStaticFilesFolderPath,WPOPTIMIZEBYXTRAFFIC_CONTENT_FOLDER_PATH_CACHE_PEPVN,WPOPTIMIZEBYXTRAFFIC_CHMOD);
		}
		
		
		
	}
	
	
	public function optimize_speed_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		
		
		if(function_exists('ob_start')) {
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : Your server must support "<a href="http://php.net/manual/en/function.ob-start.php" target="_blank"><b>ob_start</b></a>" to use "<b>Optimize Speed</b>" feature</div>';
		}
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		
		
		return $resultData;
		
	}
	
	
	
	public function optimize_speed_fix_javascript_code($input_data) 
	{
		
		$patterns = array(
			'#'.PepVN_Data::preg_quote('document.open();document.write("<img id=\"wpstats\" src=\""+u+"\" alt=\"\" />");document.close();').'#is' => 'wpOptimizeByxtraffic_appendHtml(document.getElementsByTagName("body")[0],"<img id=\"wpstats\" src=\""+u+"\" alt=\"\" />");'
		);
		
		$input_data = preg_replace(array_keys($patterns), array_values($patterns), $input_data);
		
		return $input_data;
	}
	
	public function optimize_speed_get_all_javascripts($text) 
	{
		$resultData = array();
		
		preg_match_all('/<script[^><]*>.*?<\/script>/is',$text,$matched1);
		
		if(isset($matched1[0]) && !PepVN_Data::isEmptyArray($matched1[0])) {
			$resultData = $matched1[0];
		}
		

		$resultData = (array)$resultData;
		
		return $resultData;
		
	}
	
	
	
	public function optimize_speed_get_all_css($text) 
	{
		$resultData = array();
		
		preg_match_all('/<link[^><]*\/?>.*?(<\/\1>)?/is',$text,$matched1);
		
		if(isset($matched1[0]) && !PepVN_Data::isEmptyArray($matched1[0])) {
			$matched1 = $matched1[0];
			foreach($matched1 as $key1 => $value1) {
				if($value1) {
					if(preg_match('#type=(\'|")text/css\1#i',$value1,$matched2)) {
						
						$positions1 = strpos($text,$value1);
						if(false !== $positions1) {
							$resultData[$value1] = (int)$positions1;
						}
						
					}
				}
			}
		}
		
		
		
		
		preg_match_all('/<style[^><]*>.*?<\/style>/is',$text,$matched1);
		
		if(isset($matched1[0]) && !PepVN_Data::isEmptyArray($matched1[0])) {
			$matched1 = $matched1[0];
			foreach($matched1 as $key1 => $value1) {
				if($value1) {
				
					$positions1 = strpos($text,$value1);
					if(false !== $positions1) {
						$resultData[$value1] = (int)$positions1;
					}
					
				}
			}
		}
		
		asort($resultData);
		$resultData = array_keys($resultData);
		$resultData = (array)$resultData;
		
		return $resultData;
	}
	
	
	
	public function optimize_speed_parse_load_html_scripts_by_tag($input_parameters) 
	{
		$resultData = '';
		if(isset($input_parameters['url'])) {
			
			$input_parameters['url'] = PepVN_Data::removeProtocolUrl($input_parameters['url']);
			
			if(!isset($input_parameters['id'])) {
				$input_parameters['id'] = md5($input_parameters['url']);
			}
			
			$jsLoaderId = md5($input_parameters['id'].'_js_loader');
			
			if(!isset($input_parameters['media'])) {
				$input_parameters['media'] = 'all';
			}
			
			if(!isset($input_parameters['append_to'])) {
				$input_parameters['append_to'] = '';
			}
			
			$loadTimeDelay = $this->optimize_speed_loadCssTimeDelay;
			if('js' === $input_parameters['file_type']) {
				$loadTimeDelay = $this->optimize_speed_loadJsTimeDelay;
			}
			
			$loadTimeDelay = (int)$loadTimeDelay;
			if($loadTimeDelay<10) {
				$loadTimeDelay = 10;
			}
			
			if('js' === $input_parameters['load_by']) {
				
				if('js' === $input_parameters['file_type']) {
					$resultData = ' <script data-cfasync="false" language="javascript" type="text/javascript" id="'.$jsLoaderId.'">
/*<![CDATA[*/
setTimeout(function() {
(function(e) { var t, n, r, s, i = "'.$input_parameters['id'].'"; if(e.getElementById(i)) { return 0; } t = document.location.protocol; if(-1 !== t.indexOf("https")) { n = "https:"; } else { n = "http:"; } r = e.createElement("script"); r.setAttribute("data-cfasync","false"); r.id = i; r.setAttribute("language","javascript"); r.setAttribute("type","text/javascript"); r.async = true; r.src = n + "//'.$input_parameters['url'].'"; s = e.getElementById("'.$jsLoaderId.'"); s.parentNode.insertBefore(r, s); s.parentNode.removeChild(s); })(document);
}, '.$loadTimeDelay.');
/*]]>*/
</script> ';
				} else if('css' === $input_parameters['file_type']) { 
					
					if('head' === $input_parameters['append_to']) {
					
						$resultData = ' <script language="javascript" type="text/javascript" id="'.$jsLoaderId.'">
/*<![CDATA[*/
setTimeout(function() {
(function(e) { var t, n, r, s, hd = document.getElementsByTagName("head")[0], i = "'.$input_parameters['id'].'"; if(e.getElementById(i)) { return 0; } t = document.location.protocol; if(-1 !== t.indexOf("https")) { n = "https:"; } else { n = "http:"; } r = e.createElement("link"); r.id = i; r.setAttribute("rel","stylesheet"); r.setAttribute("type","text/css"); r.setAttribute("media","'.$input_parameters['media'].'"); r.async = true; r.href = n + "//'.$input_parameters['url'].'"; hd.appendChild(r); s = e.getElementById("'.$jsLoaderId.'"); s.parentNode.removeChild(s); })(document);
}, '.(($this->optimize_speed_numberLoadCssAsync * $loadTimeDelay) + 2).');
/*]]>*/
</script> ';
						$this->optimize_speed_numberLoadCssAsync++;

					} else {
						$resultData = ' <script language="javascript" type="text/javascript" id="'.$jsLoaderId.'">
/*<![CDATA[*/
setTimeout(function() {
(function(e) { var t, n, r, s, i = "'.$input_parameters['id'].'"; if(e.getElementById(i)) { return 0; } t = document.location.protocol; if(-1 !== t.indexOf("https")) { n = "https:"; } else { n = "http:"; } r = e.createElement("link"); r.id = i; r.setAttribute("rel","stylesheet"); r.setAttribute("type","text/css"); r.setAttribute("media","'.$input_parameters['media'].'"); r.async = true; r.href = n + "//'.$input_parameters['url'].'"; s = e.getElementById("'.$jsLoaderId.'"); s.parentNode.insertBefore(r, s); s.parentNode.removeChild(s); })(document);
}, '.$loadTimeDelay.');
/*]]>*/
</script> ';
					}
					
				}
				
				
			} else if('div_tag' === $input_parameters['load_by']) {
				
				
				$resultData = ' <div class="wp-optimize-by-xtraffic-js-loader-data" pepvn_data_append_to="'.$input_parameters['append_to'].'" pepvn_data_src="'.($input_parameters['url']).'" pepvn_data_id="'.$input_parameters['id'].'" pepvn_data_file_type="'.$input_parameters['file_type'].'" pepvn_data_media="'.$input_parameters['media'].'" pepvn_data_time_delay="'.$loadTimeDelay.'" style="display:none;" ></div> ';  
				
			}
		}
		
		return $resultData;
	}
	
	
	
	public function optimize_speed_process_html_pages($text) 
	{
		
		if ( is_feed() ) {
			return $text;
		}
		
		$options = $this->get_options();
		
		
		//Check can process
		
		$processJavascriptStatus = false;
		$processCssStatus = false;
		$processHtmlStatus = false;
		
		if('on' == $options['optimize_speed_optimize_javascript_enable']) {
			if('on' == $options['optimize_speed_optimize_javascript_combine_javascript_enable']) {
				$processJavascriptStatus = true;
			}
		}
		
		
		if('on' == $options['optimize_speed_optimize_css_enable']) {
			if(
				('on' == $options['optimize_speed_optimize_css_combine_css_enable'])
				|| ('on' == $options['optimize_speed_optimize_css_minify_css_enable'])
				|| ('on' == $options['optimize_speed_optimize_css_asynchronous_css_loading_enable'])
			) {
				$processCssStatus = true;
			}
		}
		
		
		
	
		if('on' == $options['optimize_speed_optimize_html_enable']) {
			if('on' == $options['optimize_speed_optimize_html_minify_html_enable']) {
				$processHtmlStatus = true;
			}
		}
	
		
		
		if(
			(!$processJavascriptStatus)
			&& (!$processCssStatus)
			&& (!$processHtmlStatus)
		) {
			return $text;
		}
		
		
		
		$keyCacheProcessMain = array(
			'keyCache1' => array(
				__METHOD__
				,$text
				,'process_main'
			)
			,'options' => array()
		);
		
		foreach($options as $keyOne => $valueOne) {
			if(0 === strpos($keyOne,'optimize_speed_')) {
				$keyCacheProcessMain['options'][$keyOne] = $valueOne;
			}
		}
		
		$keyCacheProcessMain = PepVN_Data::createKey($keyCacheProcessMain);
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessMain); 
		
		if($valueTemp) {
			return $valueTemp;
		}
		
		
		$patternsEscaped = array();
		
		$rsOne = PepVN_Data::escapeSpecialElementsInHtmlPage($text);
		$text = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		$rsOne = false;
		
		
		$textAppendToBody = '';
		
		$textAppendToHead = '';
		
		
		$fullDomainName = $this->fullDomainName;
		$fullDomainNamePregQuote = PepVN_Data::preg_quote($fullDomainName);
		
		
		
		if($processJavascriptStatus) {
		
			$patternJavascriptExcludeUrl = array(
				'wp\-admin'
			);
			//cleanPregPatternsArray
			if($options['optimize_speed_optimize_javascript_exclude_url']) {
				$valueTemp1 = $options['optimize_speed_optimize_javascript_exclude_url'];
				$valueTemp1 = PepVN_Data::cleanPregPatternsArray($valueTemp1);
				if(!PepVN_Data::isEmptyArray($valueTemp1)) {
					$patternJavascriptExcludeUrl = array_merge($patternJavascriptExcludeUrl, $valueTemp1);
				}
			}
			
			$patternJavascriptExcludeUrl = implode('|',$patternJavascriptExcludeUrl);
			$patternJavascriptExcludeUrl = trim($patternJavascriptExcludeUrl);
		
		
		
		
			
			$combineJavascriptsStatus = true;
			
			$rsGetAllJavascripts = $this->optimize_speed_get_all_javascripts($text);
			
			if(!PepVN_Data::isEmptyArray($rsGetAllJavascripts)) {
			
				$arrayDataTextNeedReplace = array();
				
				if(!$combineJavascriptsStatus) {
					
					$iNumberScript1 = 1;
					
					foreach($rsGetAllJavascripts as $key1 => $value1) {
						if($value1) {
							
							$jsLink1 = false;
							
							if(preg_match('#src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
								if(isset($matched2[2]) && $matched2[2]) {
									$matched2[2] = trim($matched2[2]);
									
									$jsLink1 = $matched2[2];
									
									
								}
							}
							
							if($jsLink1) {
							
								$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
									'url' => $jsLink1
									,'load_by' => 'div_tag'//js
									,'file_type' => 'js'
								));
								if($valueTemp) {
									$arrayDataTextNeedReplace[$value1] = $valueTemp;
								}
								
							}
							
						}
					}
					
					
					
				} else {//enable combine js
			
					$rsGetAllJavascripts1 = array();
					foreach($rsGetAllJavascripts as $key1 => $value1) {
					
						if($value1) {
							
							if(preg_match('#src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
							
								if(isset($matched2[2]) && $matched2[2]) {
									
									$matched2[2] = trim($matched2[2]);
									
									$isProcessStatus1 = true;
									
									if($patternJavascriptExcludeUrl) {
										if(preg_match('#('.$patternJavascriptExcludeUrl.')#i',$matched2[2],$matched3)) {
											$isProcessStatus1 = false;
										}
									}
									
									
									if('on' == $options['optimize_speed_optimize_javascript_exclude_external_javascript_enable']) {
										
										if(!preg_match('#^https?://'.$fullDomainNamePregQuote.'#i',$matched2[2],$matched3)) {
											
											$isProcessStatus1 = false;
										}
									}
									
									if($isProcessStatus1) {
										$rsGetAllJavascripts1[$key1] = $value1;
									}
								}
							} else if(preg_match('/<script[^><]*>(.*?)<\/script>/is',$value1,$matched2)) {
							
								if('on' === $options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable']) {
									
									if(isset($matched2[1]) && $matched2[1]) {
										$arrayDataTextNeedReplace[$value1] = ' <div class="wp-optimize-by-xtraffic-js-loader-inlinejs-data" pepvn_data_src="'.(base64_encode($matched2[1])).'"  style="display:none;" ></div> ';  
									}
								} else {
									$rsGetAllJavascripts1[$key1] = $value1;
								}
								
							}
						}
					}
					
					$rsGetAllJavascripts = $rsGetAllJavascripts1; $rsGetAllJavascripts1 = false;
					
					
					$keyCacheAllJavascripts = PepVN_Data::createKey($rsGetAllJavascripts);
					
					$combinedAllJavascriptsFilesPath = false;
					
					$combinedAllJavascriptsFilesPath1 = $this->optimize_speed_UploadsStaticFilesFolderPath . $keyCacheAllJavascripts.'.js';
					
					if(file_exists($combinedAllJavascriptsFilesPath1)) {
						if(filesize($combinedAllJavascriptsFilesPath1)) {
							$combinedAllJavascriptsFilesPath = $combinedAllJavascriptsFilesPath1;
						} else {
							$filemtimeTemp1 = filemtime($combinedAllJavascriptsFilesPath1);
							if($filemtimeTemp1) {
								$filemtimeTemp1 = (int)$filemtimeTemp1;
								if((time() - $filemtimeTemp1) <= (86400 * 1)) {
									$combinedAllJavascriptsFilesPath1 = false;
								} else {
									unlink($combinedAllJavascriptsFilesPath1);
								}
							}
						}
						
						
					}
					
					if($combinedAllJavascriptsFilesPath1 && !$combinedAllJavascriptsFilesPath) {
						if(PepVN_Data::is_writable($this->optimize_speed_UploadsStaticFilesFolderPath)) {
							
							file_put_contents($combinedAllJavascriptsFilesPath1,'');
							
							$combinedAllJavascriptsFilesContents = '';
							
							$breakProcessStatus1 = false;
							
							foreach($rsGetAllJavascripts as $key1 => $value1) {
								
								$jsContent1 = '';
								
								if($value1) {
									
									if(preg_match('#src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
									
										if(isset($matched2[2]) && $matched2[2]) {
											
											$matched2[2] = trim($matched2[2]);
										
											$protocol1 = 'http';
											
											if(false !== strpos($matched2[2],'https')) {
												$protocol1 .= 's';
											}
											
											$protocol1 .= ':';
											
											$matched2[2] = preg_replace('#^https?:#i','',$matched2[2]);
											
											$jsContent2 = $this->quickGetUrlContent($protocol1.$matched2[2], array(
												'cache_timeout' => $this->optimize_speed_getUrlContentCacheTimeout
											));
											
											if($jsContent2) {
												
												$jsContent1 = $jsContent2;
												
											}
											
											if(!$jsContent2) {
												$breakProcessStatus1 = true;
												break;
											}
											
										
										}
										
										
									} else if(preg_match('/<script[^><]*>(.*?)<\/script>/is',$value1,$matched2)) {
										
										if(isset($matched2[1]) && $matched2[1]) {
											$jsContent1 = $matched2[1];
										}
									}
									
								}
								
								
								if('' !== $jsContent1) {
									
									$arrayDataTextNeedReplace[$value1] = '';
									
									if('on' == $options['optimize_speed_optimize_javascript_minify_javascript_enable']) {
										
										
										$rsOne = PepVN_Data::escapeByPattern($jsContent1,array(
											'pattern' => '#[\+\-]+[ \t\s]+[\+\-]+#is'
											,'target_patterns' => array(
												0
											)
											,'wrap_target_patterns' => '+'
										));
																				
										$rsOne['content'] = PepVN_JSMin::minify($rsOne['content']);
										
										if(!PepVN_Data::isEmptyArray($rsOne['patterns'])) {
											$rsOne['content'] = str_replace(array_values($rsOne['patterns']),array_keys($rsOne['patterns']),$rsOne['content']);
										}
										
										$jsContent1 = $rsOne['content']; $rsOne = false;
										
										
									}
									
									$jsContent1 = $this->optimize_speed_fix_javascript_code($jsContent1);
									
									$jsContent1 = ' try { '.$jsContent1.' } catch(err) { } ';
									
									$combinedAllJavascriptsFilesContents .= PHP_EOL . ' ' . $jsContent1;
									
									
									
								}
								
								
								
								
								
							}
							
							
							if(!$breakProcessStatus1) {
								$combinedAllJavascriptsFilesContents = trim($combinedAllJavascriptsFilesContents);
								file_put_contents($combinedAllJavascriptsFilesPath1, $combinedAllJavascriptsFilesContents);
								$combinedAllJavascriptsFilesPath = $combinedAllJavascriptsFilesPath1;
							}
							
							
							
							
						}
					}
					
					
					if($combinedAllJavascriptsFilesPath) {
						
						foreach($rsGetAllJavascripts as $key1 => $value1) {
							$arrayDataTextNeedReplace[$value1] = '';
						}
						
						$combinedAllJavascriptsFilesUrl = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$combinedAllJavascriptsFilesPath);
						
						//$combinedAllJavascriptsFilesUrl = preg_replace('#^https?://#i','',$combinedAllJavascriptsFilesUrl);
						
						$combinedAllJavascriptsFilesUrl = PepVN_Data::removeProtocolUrl($combinedAllJavascriptsFilesUrl);
						
						$combinedAllJavascriptsFilesUrl = trim($combinedAllJavascriptsFilesUrl);
												
						if('on' == $options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable']) {
							
							$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
								'url' => $combinedAllJavascriptsFilesUrl
								,'load_by' => 'div_tag'
								,'file_type' => 'js'
							));
							if($valueTemp) {
								$textAppendToBody .= $valueTemp;
							}
							
						} else {
							$textAppendToBody .= ' <script language="javascript" type="text/javascript" src="//'.$combinedAllJavascriptsFilesUrl.'" ></script> ';
						}
						
					}
					
					
				}
				
				if(!PepVN_Data::isEmptyArray($arrayDataTextNeedReplace)) {
					$text = str_replace(array_keys($arrayDataTextNeedReplace),array_values($arrayDataTextNeedReplace),$text);
				}
				$arrayDataTextNeedReplace = array();
				
			}
			
			
		}
		
		if($processCssStatus) {
			
			$patternCssExcludeUrl = array(
				'wp\-admin'
			);
			//cleanPregPatternsArray
			if($options['optimize_speed_optimize_css_exclude_url']) {
				$valueTemp1 = $options['optimize_speed_optimize_css_exclude_url'];
				$valueTemp1 = PepVN_Data::cleanPregPatternsArray($valueTemp1);
				if(!PepVN_Data::isEmptyArray($valueTemp1)) {
					
					$patternCssExcludeUrl = array_merge($patternCssExcludeUrl, $valueTemp1);
				}
			}
			
			$patternCssExcludeUrl = implode('|',$patternCssExcludeUrl);
			$patternCssExcludeUrl = trim($patternCssExcludeUrl);
			
			
			$combineCssStatus = true;
			if(!$options['optimize_speed_optimize_css_combine_css_enable']) {
				$combineCssStatus = false;
			}
			
			$rsGetAllCss = $this->optimize_speed_get_all_css($text); 
			
			if(!PepVN_Data::isEmptyArray($rsGetAllCss)) {
				
				if(!$combineCssStatus) {
					
					$arrayDataTextNeedReplace = array();
					
					foreach($rsGetAllCss as $key1 => $value1) {
						if($value1) {
							
							$cssLink1 = false;
							
							if(preg_match('#href=(\'|")((https?:)?//[^"\']+)\1#is',$value1,$matched2)) {
								if(isset($matched2[2]) && $matched2[2]) {
									
									$matched2[2] = trim($matched2[2]);
									
									$isProcessStatus1 = true;
									
									if($patternCssExcludeUrl) {
										if(preg_match('#('.$patternCssExcludeUrl.')#i',$matched2[2],$matched3)) {
											$isProcessStatus1 = false;
										}
									}
									
									
									if('on' == $options['optimize_speed_optimize_css_exclude_external_css_enable']) {
										
										if(!preg_match('#^https?://'.$fullDomainNamePregQuote.'#i',$matched2[2],$matched3)) {
											$isProcessStatus1 = false;
										}
									}
									
									if($isProcessStatus1) {
										$cssLink1 = $matched2[2];
									}
									
									
								}
							}
							
							if($cssLink1) {
								
								$mediaType1 = 'all';
								if(preg_match('#media=(\'|")([^"\']+)\1#is',$value1,$matched2)) {
									if(isset($matched2[2]) && $matched2[2]) {
										$matched2[2] = trim($matched2[2]);
										if($matched2[2]) {
											$mediaType1 = $matched2[2]; 
										}
									}
								}
								
								
								
								if('on' == $options['optimize_speed_optimize_css_minify_css_enable']) {
									
									$keyCacheCssFile1 = PepVN_Data::createKey(array(
										__METHOD__
										,$cssLink1
									));
									
									$cssFilePath1 = false;
									
									$cssFilePath2 = $this->optimize_speed_UploadsStaticFilesFolderPath . $keyCacheCssFile1.'.css';
									
									if(file_exists($cssFilePath2)) {
										
										if(filesize($cssFilePath2)) {
											$cssFilePath1 = $cssFilePath2;
										} else {
											$filemtimeTemp1 = filemtime($cssFilePath2);
											if($filemtimeTemp1) {
												$filemtimeTemp1 = (int)$filemtimeTemp1;
												if((time() - $filemtimeTemp1) <= (60 * 15)) {
													$cssFilePath2 = false;
												} else {
													unlink($cssFilePath2);
												}
											}
										}
										
									}
									
									if($cssFilePath2 && !$cssFilePath1) {
										if(PepVN_Data::is_writable($this->optimize_speed_UploadsStaticFilesFolderPath)) {
											file_put_contents($cssFilePath2,'');
											
											
											$cssLinkTemp1 = $cssLink1;
											if(preg_match('#^//#i',$cssLinkTemp1,$matched3)) {
												$cssLinkTemp1 = 'http:'.$cssLinkTemp1;
											}
											
											if(PepVN_Data::isUrl($cssLinkTemp1)) {
												$cssContent1 = $this->quickGetUrlContent($cssLinkTemp1, array(
													'cache_timeout' => $this->optimize_speed_getUrlContentCacheTimeout
												));
												
												if($cssContent1) {
												
													$pepVN_CSSFixer = false;
													$pepVN_CSSFixer = new PepVN_CSSFixer();
													if('on' == $options['optimize_speed_optimize_css_minify_css_enable']) {
														$valueTemp = $pepVN_CSSFixer->fix(array(
															'css_content' => $cssContent1
															,'css_url' => $cssLinkTemp1
															,'minify_status' => true
														));
													} else {
														$valueTemp = $pepVN_CSSFixer->fix(array(
															'css_content' => $cssContent1
															,'css_url' => $cssLinkTemp1
															,'minify_status' => false
														));
													}
													if($valueTemp) {
														$cssContent1 = $valueTemp;
													}
													
													file_put_contents($cssFilePath2,$cssContent1);
													
													$cssFilePath1 = $cssFilePath2;
													
												}
											}
											
											
										}
									}
									
									
									if($cssFilePath1) {
										
										$cssLink1 = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$cssFilePath1);
										
									}
									
									
								}
								
								
								
								if('on' == $options['optimize_speed_optimize_css_asynchronous_css_loading_enable']) {
									$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
										'url' => $cssLink1
										,'load_by' => 'div_tag'
										,'file_type' => 'css'
										,'media' => $mediaType1
									));
									if($valueTemp) {
										$arrayDataTextNeedReplace[$value1] = $valueTemp;
									}
								} else {
									
									$arrayDataTextNeedReplace[$value1] = ' <link href="'.$cssLink1.'" media="'.$mediaType1.'" rel="stylesheet" type="text/css" /> ';
									
								}
								
							}
							
						}
					}
					
					if(!PepVN_Data::isEmptyArray($arrayDataTextNeedReplace)) {
						$text = str_replace(array_keys($arrayDataTextNeedReplace),array_values($arrayDataTextNeedReplace),$text);
					}
				
				
				} else {
					
					$breakProcessStatus1 = false;
					
					$rsGetAllCssGroup = array();
					
					$lastMediaType = false;
					$lastCssGroup = array(
						'media' => ''
						,'css' => array()
						,'original_full_css' => array()
					);
					
					foreach($rsGetAllCss as $key1 => $value1) {
						if($value1) {
							
							$cssContent1 = '';
					
					
							if(preg_match('#href=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
								
								if(isset($matched2[2]) && $matched2[2]) {
									$matched2[2] = trim($matched2[2]);
									
									$protocol1 = 'http';
									
									if(false !== strpos($matched2[2],'https')) {
										$protocol1 .= 's';
									}
									
									$protocol1 .= ':';
									
									$matched2[2] = preg_replace('#^https?:#i','',$matched2[2]);
									
									$cssContent1 = $protocol1.$matched2[2];
									
									
									$isProcessStatus1 = true;
									
									if($patternCssExcludeUrl) {
										if(preg_match('#('.$patternCssExcludeUrl.')#i',$cssContent1,$matched3)) {
											$isProcessStatus1 = false;
										}
									}
									
									
									if('on' == $options['optimize_speed_optimize_css_exclude_external_css_enable']) {
										
										if(!preg_match('#^https?://'.$fullDomainNamePregQuote.'#i',$cssContent1,$matched3)) {
											$isProcessStatus1 = false;
										}
									}
									
									if(!$isProcessStatus1) {
										$cssContent1 = '';
									}
									
								}
								
							} else if(preg_match('/<style[^><]*?>(.*?)<\/style>/is',$value1,$matched2)) {
								
								if(isset($matched2[1]) && $matched2[1]) {
									if(!$options['optimize_speed_optimize_css_exclude_inline_css_enable']) {
										
										$cssContent1 .= PHP_EOL . ' ' .$matched2[1];
									}
								}
								
								
							}
							
							if($cssContent1) {
								
								$mediaType1 = 'all';
								if(preg_match('#media=(\'|")([^"\']+)\1#is',$value1,$matched2)) {
									if(isset($matched2[2]) && $matched2[2]) {
										$matched2[2] = trim($matched2[2]);
										if($matched2[2]) {
											$mediaType1 = $matched2[2]; 
										}
									}
								}
								
								if(false === $lastMediaType) {
									$lastMediaType = $mediaType1;
								}
								
								if($lastMediaType !== $mediaType1) {
									$rsGetAllCssGroup[] = $lastCssGroup;
									$lastMediaType = $mediaType1;
									$lastCssGroup = array(
										'media' => ''
										,'css' => array()
										,'original_full_css' => array()
									);
								}
								
								$lastCssGroup['media'] = $lastMediaType;
								$lastCssGroup['css'][] = $cssContent1;
								$lastCssGroup['original_full_css'][] = $value1;
								
							}
							
						}
						
						
					}
					
					
					
					$cssLoadByJSString = '';
					
					
					$breakProcessStatus = false;
					
					$arrayDataTextNeedRemove = array();
					
					$rsGetAllCssGroup[] = $lastCssGroup;
					
					$iNumberCssFiles = 1;
					
					$appendCssToHead = '';
					
					foreach($rsGetAllCssGroup as $key1 => $value1) {
					
						if(isset($value1['css']) && !PepVN_Data::isEmptyArray($value1['css'])) {
							
							$keyCacheAllCss = PepVN_Data::createKey($value1);
							
							$combinedAllCssFilesPath = false;
							
							$combinedAllCssFilesPath1 = $this->optimize_speed_UploadsStaticFilesFolderPath . $keyCacheAllCss.'.css';
							
							if(file_exists($combinedAllCssFilesPath1)) {
								
								
								
								if(filesize($combinedAllCssFilesPath1)) {
									$combinedAllCssFilesPath = $combinedAllCssFilesPath1;
								} else {
									$filemtimeTemp1 = filemtime($combinedAllCssFilesPath1);
									if($filemtimeTemp1) {
										$filemtimeTemp1 = (int)$filemtimeTemp1;
										if((time() - $filemtimeTemp1) <= (86400 * 1)) {
											$combinedAllCssFilesPath1 = false;
										} else {
											@unlink($combinedAllCssFilesPath1);
										}
									}
								}
								
							}
							
							if($combinedAllCssFilesPath1 && !$combinedAllCssFilesPath) {
								if(PepVN_Data::is_writable($this->optimize_speed_UploadsStaticFilesFolderPath)) {
									file_put_contents($combinedAllCssFilesPath1,'');
									
											
									$combinedAllCssFilesContents = '';
									
									$breakProcessStatus1 = false;
									
									foreach($value1['css'] as $key2 => $value2) {
										$value2 = trim($value2);
										if($value2) {
											$cssContent2 = '';
											
											if(PepVN_Data::isUrl($value2)) {
												$cssContent3 = $this->quickGetUrlContent($value2, array(
													'cache_timeout' => $this->optimize_speed_getUrlContentCacheTimeout
												));
												
												if($cssContent3) {
													
													$pepVN_CSSFixer = false;
													$pepVN_CSSFixer = new PepVN_CSSFixer();
													$valueTemp = $pepVN_CSSFixer->fix(array(
														'css_content' => $cssContent3
														,'css_url' => $value2
														,'minify_status' => false
													));
													
													if($valueTemp) {
														$cssContent3 = $valueTemp;
													}
													
													
													$cssContent2 .= PHP_EOL . ' ' .$cssContent3;
													
													
												} else {
													
													$breakProcessStatus1 = true;
													break;
												}
											} else {
												$cssContent2 .= PHP_EOL . ' ' .$value2;
											}
											
											
											$cssContent2 = trim($cssContent2);
											if($cssContent2) {
												$combinedAllCssFilesContents .= $cssContent2;
											}
										}
									}
									
									if(!$breakProcessStatus1) {
										if('on' == $options['optimize_speed_optimize_css_minify_css_enable']) {
											$combinedAllCssFilesContents = PepVN_Data::minifyCss($combinedAllCssFilesContents);
										}
										$combinedAllCssFilesContents = trim($combinedAllCssFilesContents);
										file_put_contents($combinedAllCssFilesPath1, $combinedAllCssFilesContents);
										$combinedAllCssFilesPath = $combinedAllCssFilesPath1;
									} else {
										$breakProcessStatus = true;
										break;
									}
								}
							}
							
							
							if($combinedAllCssFilesPath) {
								
								if(isset($value1['original_full_css']) && !PepVN_Data::isEmptyArray($value1['original_full_css'])) {
									foreach($value1['original_full_css'] as $key2 => $value2) {
										$arrayDataTextNeedRemove[$value2] = '';
									}
								}
								
								$combinedAllCssFilesUrl = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$combinedAllCssFilesPath);
								
								$combinedAllCssFilesUrl = PepVN_Data::removeProtocolUrl($combinedAllCssFilesUrl);
								$combinedAllCssFilesUrl = trim($combinedAllCssFilesUrl);
								
								if('on' == $options['optimize_speed_optimize_css_asynchronous_css_loading_enable']) {
									$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
										'url' => $combinedAllCssFilesUrl
										,'load_by' => 'div_tag'
										,'append_to' => 'head'
										,'file_type' => 'css'
										,'media' => $value1['media']
									));
									if($valueTemp) {
										$textAppendToBody .= ' '.$valueTemp;
									}
								} else {
									$appendCssToHead .= ' <link href="//'.$combinedAllCssFilesUrl.'" media="'.$value1['media'].'" rel="stylesheet" type="text/css" /> ';
								}
							}
							
						}
						
					}
					
					
					if(!$breakProcessStatus) {
						
						$appendCssToHead = trim($appendCssToHead);
						if($appendCssToHead) {
							$textAppendToHead .= ' '.$appendCssToHead.' ';
						}
						
						if(!PepVN_Data::isEmptyArray($arrayDataTextNeedRemove)) {
							$text = str_replace(array_keys($arrayDataTextNeedRemove),array_values($arrayDataTextNeedRemove),$text);
						}
						$arrayDataTextNeedRemove = array();
						
						
					}
					
				}
			}
			
			
		}
		
		
		$jsUrl = WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL;
		$jsUrl = PepVN_Data::removeProtocolUrl($jsUrl);
		$jsUrl = $jsUrl . 'js/optimize_speed_by_xtraffic.min.js?v=' . WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		//$jsUrl = $jsUrl . 'js/optimize_speed_by_xtraffic.js?v=' . WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION . time();//test
		$jsId = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-optimize-speed';
		$jsLoaderId = $jsId.'-js-loader';
		
		$jsLoadString = '<script data-cfasync="false" language="javascript" type="text/javascript" id="'.$jsLoaderId.'">
/*<![CDATA[*/
setTimeout(function() {
(function(e) { var t, n, r, s, i = "'.$jsId.'"; if(e.getElementById(i)) { return 0; } t = document.location.protocol; if(-1 !== t.indexOf("https")) { n = "https:"; } else { n = "http:"; } r = e.createElement("script"); r.setAttribute("data-cfasync","false"); r.id = i; r.setAttribute("language","javascript"); r.setAttribute("type","text/javascript"); r.async = true; r.src = n + "//'.$jsUrl.'"; s = e.getElementById("'.$jsLoaderId.'"); s.parentNode.insertBefore(r, s); s.parentNode.removeChild(s); })(document);
}, 10);
/*]]>*/
</script>';
		$textAppendToBody .= ' '.$jsLoadString;
		
		$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToBody,$text);
		
		
		
		$textAppendToHead = trim($textAppendToHead);
		if($textAppendToHead) {
			
			$text = PepVN_Data::appendTextToTagHeadOfHtml($textAppendToHead,$text);
			
		}
		
		if($processHtmlStatus) {
			if('on' == $options['optimize_speed_optimize_html_minify_html_enable']) {
				$text = PepVN_Data::minifyHtml($text);
			}
			
		}
		
		
		if(count($patternsEscaped)>0) {
			$text = str_replace(array_values($patternsEscaped),array_keys($patternsEscaped),$text);
		}
		
		
		$valueTemp = PHP_EOL . '<!-- This website has been optimized by "WP Optimize By xTraffic". Learn more : http://wordpress.org/plugins/wp-optimize-by-xtraffic/ -->';
		$text = PepVN_Data::appendTextToTagBodyOfHtml($valueTemp,$text);
		
		$text = trim($text);
		
		$this->cacheObj->set_cache($keyCacheProcessMain,$text);
		
		return $text;
		
	}

	

	public function optimize_speed_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];
		
		
		
		
		//Optimize Javascript Options
		$optimize_speed_optimize_javascript_enable = $options['optimize_speed_optimize_javascript_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_javascript_combine_javascript_enable = $options['optimize_speed_optimize_javascript_combine_javascript_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_javascript_minify_javascript_enable = $options['optimize_speed_optimize_javascript_minify_javascript_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable = $options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_javascript_exclude_external_javascript_enable = $options['optimize_speed_optimize_javascript_exclude_external_javascript_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_javascript_exclude_inline_javascript_enable = $options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_javascript_exclude_url = $options['optimize_speed_optimize_javascript_exclude_url'];
		
		//Optimize CSS Options
		$optimize_speed_optimize_css_enable = $options['optimize_speed_optimize_css_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_css_combine_css_enable = $options['optimize_speed_optimize_css_combine_css_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_css_minify_css_enable = $options['optimize_speed_optimize_css_minify_css_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_css_asynchronous_css_loading_enable = $options['optimize_speed_optimize_css_asynchronous_css_loading_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_css_exclude_external_css_enable = $options['optimize_speed_optimize_css_exclude_external_css_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_css_exclude_inline_css_enable = $options['optimize_speed_optimize_css_exclude_inline_css_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_css_exclude_url = $options['optimize_speed_optimize_css_exclude_url'];
		
		
		//Optimize HTML Options
		$optimize_speed_optimize_html_enable = $options['optimize_speed_optimize_html_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_html_minify_html_enable = $options['optimize_speed_optimize_html_minify_html_enable'] == 'on' ? 'checked':'';
		
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
		
		$classSystemReady = '';
		$rsOne = $this->optimize_speed_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsOne['notice']['error'])) {
			echo implode(' ',$rsOne['notice']['error']);
			$classSystemReady = 'wpoptimizebyxtraffic_disabled';
		}
		
		
		echo <<<END

<div class="wrap wpoptimizebyxtraffic_admin $classSystemReady" style="">
	
	<h2>WP Optimize By xTraffic (Optimize Speed)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
			
				<form name="WPOptimizeByxTraffic" action="$action_url" method="post">
					
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					
					<input type="hidden" name="submitted" value="1" /> 
					<input type="hidden" name="optimize_speed_submitted" value="1" /> 
											
					<div class='xtraffic_tabs_nav'>
						<a href='#xtraffic_tabs_content1' class="active">Optimize Javascript</a> 
						<a href='#xtraffic_tabs_content2' class="">Optimize CSS</a>
						<a href='#xtraffic_tabs_content3' class="">Optimize HTML</a>
					</div>
					
					<div id='xtraffic_tabs_content1' class="xtraffic_tabs_contents">

						<h3>Optimize Javascript</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_javascript_container"  $optimize_speed_optimize_javascript_enable /> &nbsp; Enable Optimize Javascript</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_javascript_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_combine_javascript_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_javascript_container2"  $optimize_speed_optimize_javascript_combine_javascript_enable /> &nbsp; Enable Combine Javascript ( Recommended )</h5>
									<p class="description"></p>
									
								</li> 
								
								<div style="margin-top: 0;" id="optimize_speed_optimize_javascript_container2" class="wpoptimizebyxtraffic_show_hide_container">
									
									<ul>
									
										<li>
											
											<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_minify_javascript_enable" class="" $optimize_speed_optimize_javascript_minify_javascript_enable /> &nbsp; Enable Minify Javascript ( Recommended )</h6>
											<p class="description"></p>
											
										</li>
										
										<li>
										
											<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable" class="" $optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable /> &nbsp; Enable Asynchronous Javascript Loading ( Recommended )</h6>
											<p class="description"></p>
											
										</li>
										
										<li style="margin-bottom: 3%;">
											
											<h6 style="margin-bottom: 0;"><input type="checkbox" name="optimize_speed_optimize_javascript_exclude_external_javascript_enable" class="" $optimize_speed_optimize_javascript_exclude_external_javascript_enable /> &nbsp; Exclude external javascript ( Not Recommended )</h6>
											<p class="description">Plugin will ignore all external javascript files ( Which not scripts in your self-hosted ). <i>You should not enable this feature unless an error occurs</i></p>
											
										</li>
										
										<li style="margin-bottom: 3%;">
											
											<h6 style="margin-bottom: 0%;"><input type="checkbox" name="optimize_speed_optimize_javascript_exclude_inline_javascript_enable" class="" $optimize_speed_optimize_javascript_exclude_inline_javascript_enable /> &nbsp; Exclude inline javascript ( Not Recommended )</h6>
											<p class="description">Plugin will ignore all javascript code in your html. <i>You should not enable this feature unless an error occurs</i></p>
											
										</li>
										
										<li>
											<h6> Exclude (Contained in url, seperate them by comma)</h6> 
											<input type="text" name="optimize_speed_optimize_javascript_exclude_url" class="" value="$optimize_speed_optimize_javascript_exclude_url" style="width: 50%;" /> &nbsp;  
											<p class="description">Plugin will ignore these javascript files urls</p>
										</li>
										
									</ul>
									
								</div>
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->  
					
					
					
					
					<div id='xtraffic_tabs_content2' class="xtraffic_tabs_contents">

						<h3>Optimize CSS (Style)</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_css_container"  $optimize_speed_optimize_css_enable /> &nbsp; Enable Optimize CSS</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_css_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_combine_css_enable" class="" $optimize_speed_optimize_css_combine_css_enable /> &nbsp; Enable Combine CSS ( Recommended )</h5>
									<p class="description"></p>
									
								</li> 
								
						
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_minify_css_enable" class="" $optimize_speed_optimize_css_minify_css_enable /> &nbsp; Enable Minify CSS ( Recommended )</h5>
									<p class="description"></p>
									
								</li>
								
								
						
								<li>
								
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_asynchronous_css_loading_enable" class="" $optimize_speed_optimize_css_asynchronous_css_loading_enable /> &nbsp; Enable Asynchronous CSS Loading ( Recommended )</h5>
									<p class="description"></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h5 style="margin-bottom: 0;"><input type="checkbox" name="optimize_speed_optimize_css_exclude_external_css_enable" class="" $optimize_speed_optimize_css_exclude_external_css_enable /> &nbsp; Exclude external CSS ( Not Recommended )</h5>
									<p class="description">Plugin will ignore all external CSS files ( Which not CSS files in your self-hosted ). <i>You should not enable this feature unless an error occurs</i></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h5 style="margin-bottom: 0%;"><input type="checkbox" name="optimize_speed_optimize_css_exclude_inline_css_enable" class="" $optimize_speed_optimize_css_exclude_inline_css_enable /> &nbsp; Exclude inline CSS ( Not Recommended )</h5>
									<p class="description">Plugin will ignore all style (wrap by &#x3C;style&#x3E;&#x3C;/style&#x3E;) in your html. <i>You should not enable this feature unless an error occurs</i></p>
									
								</li> 
								
								<li>
									<h5> Exclude (Contained in url, seperate them by comma)</h5> 
									<input type="text" name="optimize_speed_optimize_css_exclude_url" class="" $optimize_speed_optimize_css_exclude_url style="width: 50%;" /> &nbsp;  
									<p class="description">Plugin will ignore these css files urls</p>
								</li>
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->
					
					
					
					
					<div id='xtraffic_tabs_content3' class="xtraffic_tabs_contents">

						<h3>Optimize HTML</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_html_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_html_container"  $optimize_speed_optimize_html_enable /> &nbsp; Enable Optimize HTML</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_html_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_html_minify_html_enable" class="" $optimize_speed_optimize_html_minify_html_enable /> &nbsp; Enable Minify HTML ( Recommended )</h5>
									<p class="description"></p>
									
								</li>
								
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->
					
						
					<div class="submit"><input type="submit" name="Submit" value="Update options" class="button-primary" /></div>
					
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

END;
		
		
	}
	
	

	
	


}//class WPOptimizeByxTraffic

endif; //if ( !class_exists('WPOptimizeByxTraffic') )



?>