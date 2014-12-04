<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeLinks.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeSpeed') ) :


class WPOptimizeByxTraffic_OptimizeSpeed extends WPOptimizeByxTraffic_OptimizeLinks 
{
	
	private $optimize_speed_getUrlContentCacheTimeout = 86400; 
	
	private $optimize_speed_loadJsTimeDelay = 1000;//miliseconds
	private $optimize_speed_loadCssTimeDelay = 10;//miliseconds
	
	private $optimize_speed_numberLoadCssAsync = 0; 
	
	
	public $optimize_speed_cdn_patternFilesTypeAllow = '';
		
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
		
		
		
		
		$this->optimize_speed_cdn_patternFilesTypeAllow = array(
			//img
			'jpg'
			,'jpeg'
			,'gif'
			,'png'
			,'ico'
			,'svg'
			
			//js & css
			,'css'
			,'js'
			
			//font
			,'ttf'
			,'woff2'
			
			//audio
			,'wav'
			,'ogg'
			,'mp3'
			,'wma'
			,'mid'
			,'midi'
			,'rm'
			,'ram'
			,'aac'
			,'mp4'
						
			
			//video
			,'mpg'
			,'mpeg'
			,'avi'
			,'wmv'
			,'mov'
			,'rm'
			,'ram'
			,'ogg'
			,'webm'
			,'mp4'
			
			//flash
			,'swf'
			,'flv'
			
			//document
			,'pdf'
			
		);
		
		$this->optimize_speed_cdn_patternFilesTypeAllow = array_unique($this->optimize_speed_cdn_patternFilesTypeAllow);
		$this->optimize_speed_cdn_patternFilesTypeAllow = implode('|',$this->optimize_speed_cdn_patternFilesTypeAllow);
		
		
		
	}
	
	
	public function optimize_speed_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		$resultData['notice']['error_no'] = array();
		
		
		$rsTemp = $this->base_check_system_ready();
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsTemp
		));
		
		
		$folderPath = WP_CONTENT_DIR . '/cache/';
		if(!PepVN_Data::isAllowReadAndWrite($folderPath)) {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$folderPath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Speed</b>"</div>';
			$resultData['notice']['error_no'][] = 30;
		}
		
		
		if(function_exists('ob_start')) {
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must support',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<a href="http://php.net/manual/en/function.ob-start.php" target="_blank"><b>ob_start</b></a>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Speed</b>"</div>';
			$resultData['notice']['error_no'][] = 30;
		}
		
		
		if(
			isset($this->optimize_speed_UploadsStaticFilesFolderPath)
			&& $this->optimize_speed_UploadsStaticFilesFolderPath
			&& file_exists($this->optimize_speed_UploadsStaticFilesFolderPath)
			&& PepVN_Data::isAllowReadAndWrite($this->optimize_speed_UploadsStaticFilesFolderPath)
		) {
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$this->optimize_speed_UploadsStaticFilesFolderPath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Speed</b>"</div>';
			$resultData['notice']['error_no'][] = 30;
		}
		
		
		
		
		$path = ABSPATH;
		
		if($this->optimize_speed_is_subdirectory_install()){
			$path = $this->base_getABSPATH();
		}
		
		if('apache' === PepVN_Data::$defaultParams['serverSoftware']) {
			
			$pathFileHtaccess = $path.'.htaccess';
			
			$checkStatus1 = false;
			
			if(file_exists($pathFileHtaccess) && is_file($pathFileHtaccess) && is_writable($pathFileHtaccess)){
				$checkStatus1 = true;
			} else if(PepVN_Data::is_writable($path)) {
				$checkStatus1 = true;
			}
			
			if(!$checkStatus1) {
				$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server is using Apache. You should create file ',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"'.$pathFileHtaccess.'" and make it <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('to achieve the highest performance with ',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Speed</b>". '.__('After change, please deactivate & reactivate this plugin for the changes to be updated',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!</div>';
			}
			
			
			
		} else if('nginx' === PepVN_Data::$defaultParams['serverSoftware']) {
			$pathFileConfig = $path.'xtraffic-nginx.conf';
			
			$checkStatus1 = false;
			
			if(file_exists($pathFileConfig) && is_file($pathFileConfig) && is_writable($pathFileConfig)){
				$checkStatus1 = true;
			} else if(PepVN_Data::is_writable($path)) {
				$checkStatus1 = true; 
			}
			
			if(!$checkStatus1) {
				$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server is using Nginx. You should create file ',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"'.$pathFileConfig.'" and make it <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('to achieve the highest performance with ',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Speed</b>". '.__('After change, please deactivate & reactivate this plugin for the changes to be updated',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!</div>';
			} else {
				$resultData['notice']['error'][] = '
				<div class="update-nag fade">
					<p><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('To achieve the highest performance with the plugin on your Nginx server, you should follow the instructions below (if you have not already done) : ',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <input type="button" value="Show me" class="button-primary wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_nginx_config_server_guide_container" /></p>
					<div id="optimize_speed_optimize_nginx_config_server_guide_container" class="wpoptimizebyxtraffic_show_hide_container" style="display:none;">
						<ul>
						
							<li>
								<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>'.__('Step 1',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u></b> : '.__('Find and remove <i style="color: red;font-weight: 900;">red block below</i> (if exists) in your config "<i>server {...}</i>" block (at file .conf)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' :</h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;">server {
	listen   80; 
	## Your website name goes here.
	server_name '.$this->fullDomainName.';
	root '.$path.';
	index index.php;
	...
	<b style="color: red;font-weight: 900;"><i>location / {
		...
	}</i></b>
	...
}</pre>
							</li>
							
							<li>
								<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>'.__('Step 2',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u></b> : '.__('Add <i style="color: blue;font-weight: 900;">blue line below</i> into your config "<i>server {...}</i>" block (at file .conf)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' :</h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;">server {
	listen   80; 
	## Your website name goes here.
	server_name '.$this->fullDomainName.';
	root '.$path.';
	index index.php;
	...
	<b style="color: blue;font-weight: 900;"><i>include '.$pathFileConfig.';</i></b>
	...
}</pre>
							</li>
							
							<li>
								<h6 style="font-weight: 900;font-size: 100%;margin-bottom: 6px;"><b><u>'.__('Step 3',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u></b> : '.__('Restart your Nginx through SSH command',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' : </h6>
<pre style="background-color: #eee;padding: 20px 20px;margin-left: 2%;"># sudo service nginx restart</pre>
							</li>
						</ul>
					</div>
				</div>
				';
			}
			
		}
		
		
		
		
		
		
		
		
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	public function optimize_speed_fix_javascript_code($input_data) 
	{
		/*
		$patterns = array(
			'#'.PepVN_Data::preg_quote('document.open();document.write("<img id=\"wpstats\" src=\""+u+"\" alt=\"\" />");document.close();').'#is' => 'wpOptimizeByxtraffic_appendHtml(document.getElementsByTagName("body")[0],"<img id=\"wpstats\" src=\""+u+"\" alt=\"\" />");'
		);
		*/
		
		$patterns = array(
			'#document.write\((\'|\")(.+)\1\)#is' => 'wpOptimizeByxtraffic_appendHtml(document.getElementsByTagName("body")[0],$1$2$1)' 
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
				$input_parameters['id'] = PepVN_Data::mcrc32($input_parameters['url']);
			}
			
			$jsLoaderId = PepVN_Data::mcrc32($input_parameters['id'].'_js_loader');
			
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
				
				$resultData = ' <div class="wp-optimize-by-xtraffic-js-loader-data" id="'.$jsLoaderId.'" pepvn_data_loader_id="'.$jsLoaderId.'" pepvn_data_append_to="'.$input_parameters['append_to'].'" pepvn_data_src="'.($input_parameters['url']).'" pepvn_data_id="'.$input_parameters['id'].'" pepvn_data_file_type="'.$input_parameters['file_type'].'" pepvn_data_media="'.$input_parameters['media'].'" pepvn_data_time_delay="'.$loadTimeDelay.'" style="display:none;" ></div> ';  
				
			} else if('js_data' === $input_parameters['load_by']) {
				
				$resultData = ' <script language="javascript" type="text/javascript" id="'.$jsLoaderId.'" > (function(e) { if(typeof(e.wpOptimizeByxTraffic_JsLoaderData) === "undefined") { e.wpOptimizeByxTraffic_JsLoaderData = []; } e.wpOptimizeByxTraffic_JsLoaderData.push({ 
"pepvn_data_loader_id" : "'.$jsLoaderId.'"
,"pepvn_data_append_to" : "'.$input_parameters['append_to'].'"
,"pepvn_data_src" : "'.($input_parameters['url']).'"
,"pepvn_data_id" : "'.$input_parameters['id'].'"
,"pepvn_data_file_type" : "'.$input_parameters['file_type'].'"
,"pepvn_data_media" : "'.$input_parameters['media'].'"
,"pepvn_data_time_delay" : "'.$loadTimeDelay.'"
}); })(window); </script> ';
			}
			
		}
		
		return $resultData;
	}
	
	
	
	public function optimize_speed_process_html_pages($text) 
	{
		
		$checkStatus1 = true;
		
		if($checkStatus1) {
			if ( is_feed() ) {
				$checkStatus1 = false;
			}
		}
		
		if($checkStatus1) {
			if ( is_admin() ) {
				$checkStatus1 = false;
			}
		}
		
		if($checkStatus1) {
			$rsTemp = $this->optimize_speed_check_system_ready();
			if(in_array(30,$rsTemp['notice']['error_no'])) {
				$checkStatus1 = false; 
			}
		}
		
		if(!$checkStatus1) {
			return $text;
		}
		
		
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		
		//Check can process
		
		$processJavascriptStatus = false;
		$processCssStatus = false;
		$processHtmlStatus = false;
		
		if('on' == $options['optimize_speed_optimize_javascript_enable']) {
			
			if(
				('on' == $options['optimize_speed_optimize_javascript_combine_javascript_enable'])
				|| ('on' == $options['optimize_speed_optimize_javascript_minify_javascript_enable'])
				|| ('on' == $options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable'])
			) {
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
		
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'pre');
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
				,'stats.wp.com'
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
		
		
		
		
			
			$combineJavascriptsStatus = false;
			if(isset($options['optimize_speed_optimize_javascript_combine_javascript_enable']) && ($options['optimize_speed_optimize_javascript_combine_javascript_enable'])) {	
				$combineJavascriptsStatus = true;
			}
			
			
			$rsGetAllJavascripts = $this->optimize_speed_get_all_javascripts($text);
			
			if(!PepVN_Data::isEmptyArray($rsGetAllJavascripts)) {
			
				$arrayDataTextNeedReplace = array();
				
				$rsGetAllJavascripts1 = array();
				foreach($rsGetAllJavascripts as $key1 => $value1) {
					
					$checkStatus2 = false;
					if($value1) {
						$checkStatus2 = true;
						if(preg_match('#type=(\'|")([^"\']+)\1#i',$value1,$matched2)) {
							if(isset($matched2[2]) && $matched2[2]) {
								$matched2[2] = trim($matched2[2]);
								if($matched2[2]) {
									$checkStatus2 = false;
									if(false !== stripos($matched2[2],'javascript')) {
										$checkStatus2 = true;
									}
								}
							}
						}
					}
					
					
					if($checkStatus2) {
						
						if(preg_match('#<script[^><]*?src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
						
							if(isset($matched2[2]) && $matched2[2]) {
								
								$matched2[2] = trim($matched2[2]);
								
								$isProcessStatus1 = true;
								
								if($patternJavascriptExcludeUrl) {
									if(preg_match('#('.$patternJavascriptExcludeUrl.')#i',$matched2[2],$matched3)) {
										$isProcessStatus1 = false;
									}
								}
								
								
								
								if(isset($options['optimize_speed_optimize_javascript_exclude_external_javascript_enable']) && $options['optimize_speed_optimize_javascript_exclude_external_javascript_enable']) {
									
									
									if(!preg_match('#^(https?)?:?(//)?'.$fullDomainNamePregQuote.'#i',$matched2[2],$matched3)) {
										
										$isProcessStatus1 = false;
									}
								}
								
								if($isProcessStatus1) {
									$rsGetAllJavascripts1[$key1] = $value1;
								}
							}
						} else if(preg_match('/<script[^><]*>(.*?)<\/script>/is',$value1,$matched2)) {
						
							if(isset($matched2[1]) && $matched2[1]) {
								$matched2[1] = trim($matched2[1]);
								if($matched2[1]) {
									if(preg_match('#\s*?st_go\(\{.+#is',$matched2[1],$matched3)) { 
									
									} else {
										
										if(isset($options['optimize_speed_optimize_javascript_combine_javascript_enable']) && ($options['optimize_speed_optimize_javascript_combine_javascript_enable'])) {	
											if(isset($options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable']) && ($options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable'])) {
												$matched2[1] = PepVN_Data::minifyJavascript($this->optimize_speed_fix_javascript_code($matched2[1]));
												$arrayDataTextNeedReplace[$value1] = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
													'url' => '|__ecv__|'.PepVN_Data::encodeVar($matched2[1])//base64_encode($matched2[1])
													,'load_by' => 'js_data'//js_data,div_tag
													,'file_type' => 'js'
												));
											} else {
												$rsGetAllJavascripts1[$key1] = $value1;
											}
											
										} else {
											if(isset($options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable']) && ($options['optimize_speed_optimize_javascript_exclude_inline_javascript_enable'])) {
											} else {
												$rsGetAllJavascripts1[$key1] = $value1;
											}
										}
									}
								
								}
							}
							
							
						}
					}
				}
				
				$rsGetAllJavascripts = $rsGetAllJavascripts1; $rsGetAllJavascripts1 = 0;
				
				
				
				
				
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
							
								if(isset($options['optimize_speed_optimize_javascript_minify_javascript_enable']) && $options['optimize_speed_optimize_javascript_minify_javascript_enable']) {
									
									$keyCacheJsLink1 = PepVN_Data::createKey($jsLink1);
									
									$jsLink1FilesPath = false;
									
									$jsLink1FilesPath1 = $this->optimize_speed_UploadsStaticFilesFolderPath . $keyCacheJsLink1.'.js';
									
									if(file_exists($jsLink1FilesPath1)) {
										if(filesize($jsLink1FilesPath1)>0) {
											$jsLink1FilesPath = $jsLink1FilesPath1;
										} else {
											$filemtimeTemp1 = filemtime($jsLink1FilesPath1);
											if($filemtimeTemp1) {
												$filemtimeTemp1 = (int)$filemtimeTemp1;
												if((time() - $filemtimeTemp1) <= (86400 * 1)) {
													$jsLink1FilesPath1 = false;
												} else {
													unlink($jsLink1FilesPath1);
												}
											}
										}
										
										
									}
									
									
									
									if($jsLink1FilesPath1 && !$jsLink1FilesPath) {
										if(PepVN_Data::is_writable($this->optimize_speed_UploadsStaticFilesFolderPath)) {
											
											file_put_contents($jsLink1FilesPath1,'');
											
											$jsLink1Temp = $jsLink1;
											$protocol1 = 'http://';
											$jsLink1Temp = PepVN_Data::removeProtocolUrl($jsLink1Temp);
											
											if(preg_match('#^https\://#i', $jsLink1)) {
												$protocol1 = 'https://';
											}
											
											$jsContent1 = $this->quickGetUrlContent($protocol1.$jsLink1Temp, array(
												'cache_timeout' => $this->optimize_speed_getUrlContentCacheTimeout
											));
											
											
											if($jsContent1) {
												$jsContent1 = trim($jsContent1);
												if($jsContent1) {
													
													$jsContent1 = $this->optimize_speed_fix_javascript_code($jsContent1);
													
													$jsContent1 = PepVN_Data::minifyJavascript($jsContent1);
													
													$jsContent1 = ' try { '.$jsContent1.' } catch(err) { } ';
													
													@file_put_contents($jsLink1FilesPath1, $jsContent1);
													
													$jsLink1FilesPath = $jsLink1FilesPath1;
													
												}
											}
											
										}
									}
									
									if($jsLink1FilesPath) {
										$jsLink1 = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$jsLink1FilesPath);
										
									}
									
									
									
								}//optimize_speed_optimize_javascript_minify_javascript_enable
							}
							
							if($jsLink1) {
								$jsLink1 = $this->optimize_speed_cdn_get_cdn_link($jsLink1);
								$jsLink1 = PepVN_Data::removeProtocolUrl($jsLink1);
								
								$jsLink1 = trim($jsLink1);
								
								if($jsLink1) {
								
									if(isset($options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable']) && ($options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable'])) {
									
										$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
											'url' => $jsLink1
											,'load_by' => 'js_data'//js,js_data,div_tag
											,'file_type' => 'js'
										));
										if($valueTemp) {
											$arrayDataTextNeedReplace[$value1] = $valueTemp;
										}
										
									} else {
										$arrayDataTextNeedReplace[$value1] = ' <script language="javascript" type="text/javascript" src="//'.$jsLink1.'" ></script> ';
									}
								}
								
							}
							
						}
					}
					
					
				} else {//enable combine js
			
					
					$keyCacheAllJavascripts = PepVN_Data::createKey($rsGetAllJavascripts);
					
					$combinedAllJavascriptsFilesPath = false;
					
					$combinedAllJavascriptsFilesPath1 = $this->optimize_speed_UploadsStaticFilesFolderPath . $keyCacheAllJavascripts.'.js';
					
					if(file_exists($combinedAllJavascriptsFilesPath1)) {
						if(filesize($combinedAllJavascriptsFilesPath1)>0) {
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
							
							@file_put_contents($combinedAllJavascriptsFilesPath1,'');
							
							$combinedAllJavascriptsFilesContents = '';
							
							$breakProcessStatus1 = false;
							
							foreach($rsGetAllJavascripts as $key1 => $value1) {
								
								$jsContent1 = '';
								$value1 = trim($value1);
								if($value1) {
									
									
									if(preg_match('#<script[^><]*?src=(\'|")((https?:)?//[^"\']+)\1#i',$value1,$matched2)) {
									
									
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
											if(preg_match('#\s*?st_go\(\{.+#is',$matched2[1],$matched3)) {
											
											} else {
												$jsContent1 = $matched2[1];
											}
										}
									}
									
								}
								
								
								if('' !== $jsContent1) {
									
									$jsContent1 = $this->optimize_speed_fix_javascript_code($jsContent1);
									
									$arrayDataTextNeedReplace[$value1] = '';
									
									if(isset($options['optimize_speed_optimize_javascript_minify_javascript_enable']) && $options['optimize_speed_optimize_javascript_minify_javascript_enable']) {
										
										$jsContent1 = PepVN_Data::minifyJavascript($jsContent1);
										
									}
									
									$jsContent1 = ' try { '.$jsContent1.' } catch(err) { } ';
									
									$combinedAllJavascriptsFilesContents .= PHP_EOL . ' ' . $jsContent1;
									
									
									
								}
								
								
								
								
								
							}
							
							
							if(!$breakProcessStatus1) {
								$combinedAllJavascriptsFilesContents = trim($combinedAllJavascriptsFilesContents);
								@file_put_contents($combinedAllJavascriptsFilesPath1, $combinedAllJavascriptsFilesContents);
								$combinedAllJavascriptsFilesPath = $combinedAllJavascriptsFilesPath1;
							}
							
							
							
							
						}
					}
					
					
					if($combinedAllJavascriptsFilesPath) {
						
						foreach($rsGetAllJavascripts as $key1 => $value1) {
							$arrayDataTextNeedReplace[$value1] = '';
						}
						
						$combinedAllJavascriptsFilesUrl = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$combinedAllJavascriptsFilesPath);
						
						$combinedAllJavascriptsFilesUrl = $this->optimize_speed_cdn_get_cdn_link($combinedAllJavascriptsFilesUrl);
						
						$combinedAllJavascriptsFilesUrl = PepVN_Data::removeProtocolUrl($combinedAllJavascriptsFilesUrl);
						
						$combinedAllJavascriptsFilesUrl = trim($combinedAllJavascriptsFilesUrl);
												
						if(isset($options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable']) && ($options['optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable'])) {
							
							$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
								'url' => $combinedAllJavascriptsFilesUrl
								,'load_by' => 'js_data'//js_data,div_tag
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
				
				if(!$combineCssStatus) {	//combineCssStatus:false
					
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
									
									
									
									if(isset($options['optimize_speed_optimize_css_exclude_external_css_enable']) && ($options['optimize_speed_optimize_css_exclude_external_css_enable'])) {
										
										if(!preg_match('#^(https?)?:?(//)?'.$fullDomainNamePregQuote.'#i',$matched2[2],$matched3)) {
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
								
								
								
								if(isset($options['optimize_speed_optimize_css_minify_css_enable']) && ($options['optimize_speed_optimize_css_minify_css_enable'])) {
									
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
											@file_put_contents($cssFilePath2,'');
											
											
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
													
													if(isset($options['optimize_speed_optimize_css_minify_css_enable']) && ($options['optimize_speed_optimize_css_minify_css_enable'])) {
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
													
													$cssContent1 = $this->optimize_speed_cdn_process_text($cssContent1,'css');
													
													@file_put_contents($cssFilePath2,$cssContent1);
													
													$cssFilePath1 = $cssFilePath2;
													
												}
											}
											
											
										}
									}
									
									
									if($cssFilePath1) {
										
										$cssLink1 = str_replace($this->optimize_speed_UploadsStaticFilesFolderPath,$this->optimize_speed_UploadsStaticFilesFolderUrl,$cssFilePath1);
										
									}
									
									
								}
								
								if($cssLink1) {
									$cssLink1 = $this->optimize_speed_cdn_get_cdn_link($cssLink1);
									$cssLink1 = PepVN_Data::removeProtocolUrl($cssLink1);
								}
								
								if(isset($options['optimize_speed_optimize_css_asynchronous_css_loading_enable']) && ($options['optimize_speed_optimize_css_asynchronous_css_loading_enable'])) {
									$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
										'url' => $cssLink1
										,'load_by' => 'js_data'//js_data,div_tag
										,'file_type' => 'css'
										,'media' => $mediaType1
									));
									if($valueTemp) {
										$arrayDataTextNeedReplace[$value1] = $valueTemp;
									}
								} else {
									
									$arrayDataTextNeedReplace[$value1] = ' <link href="//'.$cssLink1.'" media="'.$mediaType1.'" rel="stylesheet" type="text/css" /> ';
									
								}
								
							}
							
						}
					}
					
					if(!PepVN_Data::isEmptyArray($arrayDataTextNeedReplace)) {
						$text = str_replace(array_keys($arrayDataTextNeedReplace),array_values($arrayDataTextNeedReplace),$text);
					}
				
				
				} else {//combineCssStatus:true
					
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
					
					
							if(preg_match('#href=(\'|")((https?)?:?//[^"\']+)\1#i',$value1,$matched2)) {
								
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
									
									
									
									if(isset($options['optimize_speed_optimize_css_exclude_external_css_enable']) && ($options['optimize_speed_optimize_css_exclude_external_css_enable'])) {
										
										if(!preg_match('#^(https?)?:?(//)?'.$fullDomainNamePregQuote.'#i',$cssContent1,$matched3)) {
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
									@file_put_contents($combinedAllCssFilesPath1,'');
									
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
													
													
													$cssContent2 .= PHP_EOL . ' ' .$cssContent3 . ' '.PHP_EOL;
													
													
												} else {
													
													$breakProcessStatus1 = true;
													break;
												}
											} else {
												$cssContent2 .= PHP_EOL . ' ' . $value2 . ' '.PHP_EOL;
											}
											
											
											$cssContent2 = trim($cssContent2);
											if($cssContent2) {
												$combinedAllCssFilesContents .= PHP_EOL . ' ' . $cssContent2 . ' '.PHP_EOL;
											}
										}
									}
									
									if(!$breakProcessStatus1) {
										
										$combinedAllCssFilesContents = $this->optimize_speed_cdn_process_text($combinedAllCssFilesContents,'css');
										
										if(isset($options['optimize_speed_optimize_css_minify_css_enable']) && ($options['optimize_speed_optimize_css_minify_css_enable'])) {
											$combinedAllCssFilesContents = PepVN_Data::minifyCss($combinedAllCssFilesContents);
										}
										$combinedAllCssFilesContents = trim($combinedAllCssFilesContents);
										@file_put_contents($combinedAllCssFilesPath1, $combinedAllCssFilesContents);
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
								
								
								$combinedAllCssFilesUrl = $this->optimize_speed_cdn_get_cdn_link($combinedAllCssFilesUrl);
								
								$combinedAllCssFilesUrl = PepVN_Data::removeProtocolUrl($combinedAllCssFilesUrl);
								$combinedAllCssFilesUrl = trim($combinedAllCssFilesUrl);
								
								if(isset($options['optimize_speed_optimize_css_asynchronous_css_loading_enable']) && ($options['optimize_speed_optimize_css_asynchronous_css_loading_enable'])) {
									$valueTemp = $this->optimize_speed_parse_load_html_scripts_by_tag(array(
										'url' => $combinedAllCssFilesUrl
										,'load_by' => 'js_data'//js_data,div_tag
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
		$jsUrl .= 'js/optimize_speed_by_xtraffic.min.js?v=' . WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION; 
		//$jsUrl .= 'js/optimize_speed_by_xtraffic.js?v=' . WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION . time();//test  
		$jsId = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'-optimize-speed';
		$jsLoaderId = $jsId.'-js-loader'; 
		
		$jsLoadString = '<script data-cfasync="false" language="javascript" type="text/javascript" id="'.$jsLoaderId.'">
/*<![CDATA[*/
setTimeout(function() {
(function(e) { var t, n, r, s, i = "'.$jsId.'"; if(e.getElementById(i)) { return 0; } t = document.location.protocol; if(-1 !== t.indexOf("https")) { n = "https:"; } else { n = "http:"; } r = e.createElement("script"); r.setAttribute("data-cfasync","false"); r.id = i; r.setAttribute("language","javascript"); r.setAttribute("type","text/javascript"); r.async = true; r.src = n + "//'.$jsUrl.'"; s = e.getElementById("'.$jsLoaderId.'"); s.parentNode.insertBefore(r, s); s.parentNode.removeChild(s); })(document);
}, 20);
/*]]>*/
</script>';
		$textAppendToBody .= ' '.$jsLoadString;
		
		$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToBody,$text);
		
		
		
		$textAppendToHead = trim($textAppendToHead);
		if($textAppendToHead) {
			
			$text = PepVN_Data::appendTextToTagHeadOfHtml($textAppendToHead,$text);
			
		}
		
		if($processHtmlStatus) {
			
			if(isset($options['optimize_speed_optimize_html_minify_html_enable']) && ($options['optimize_speed_optimize_html_minify_html_enable'])) {
				$text = PepVN_Data::minifyHtml($text);
			}
			
		}
		
		
		if(count($patternsEscaped)>0) {
			$text = str_replace(array_values($patternsEscaped),array_keys($patternsEscaped),$text);
		}
		
		$this->cacheObj->set_cache($keyCacheProcessMain,$text);
		
		return $text;
		
	}
	
	
	
	public function optimize_speed_cdn_get_cdn_link($input_link) 
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$checkStatus1 = false;
		
		if(isset($options['optimize_speed_cdn_enable']) && ($options['optimize_speed_cdn_enable'])) {
			if(isset($options['optimize_speed_cdn_domain']) && ($options['optimize_speed_cdn_domain'])) {
				$options['optimize_speed_cdn_domain'] = trim($options['optimize_speed_cdn_domain']);
				if($options['optimize_speed_cdn_domain']) {
					$checkStatus1 = true;
				}
			}
		}
		
		
		if($checkStatus1) {
			
			$keyCache1 = 'optimize_speed_cdn_get_cdn_link_optimize_speed_cdn_exclude_url_data';
		
			if(!isset($this->baseCacheData[$keyCache1])) {
					
				$optimize_speed_cdn_exclude_url = array();
				
				if(isset($options['optimize_speed_cdn_exclude_url']) && ($options['optimize_speed_cdn_exclude_url'])) {
					$valueTemp = trim($options['optimize_speed_cdn_exclude_url']);
					if($valueTemp) {
						$valueTemp = PepVN_Data::explode(',',$valueTemp);
						$valueTemp = PepVN_Data::cleanArray($valueTemp);
						if(!PepVN_Data::isEmptyArray($valueTemp)) {
							$optimize_speed_cdn_exclude_url = $valueTemp;
							
						}
					}
				}
				
				if(!PepVN_Data::isEmptyArray($optimize_speed_cdn_exclude_url)) {
					$optimize_speed_cdn_exclude_url = array_unique($optimize_speed_cdn_exclude_url);
					foreach($optimize_speed_cdn_exclude_url as $key1 => $value1) {
						$optimize_speed_cdn_exclude_url[$key1] = PepVN_Data::preg_quote($value1);
					}
				} else {
					$optimize_speed_cdn_exclude_url = false;
				}
				
				$this->baseCacheData[$keyCache1] = $optimize_speed_cdn_exclude_url;
			}
			
			$optimize_speed_cdn_exclude_url = $this->baseCacheData[$keyCache1];
			
			
			$currentProtocol = 'http://';
			if(PepVN_Data::is_ssl()) {
				$currentProtocol = 'https://';
			}
			
			
			
			$input_link1 = PepVN_Data::removeProtocolUrl($input_link);
			if(preg_match('#^'.PepVN_Data::preg_quote($this->fullDomainName).'.+$#i',$input_link1,$matched3)) {
				
				$checkStatus2 = true; 
	
				if($optimize_speed_cdn_exclude_url) {
					if(preg_match('#('.implode('|',$optimize_speed_cdn_exclude_url).')#i',$input_link1,$matched4)) {
						$checkStatus2 = false;
					}
				}
				
				if($checkStatus2) {
					return $currentProtocol.preg_replace('#^'.PepVN_Data::preg_quote($this->fullDomainName).'#i',$options['optimize_speed_cdn_domain'],$input_link1,1);
				}
				
			}
			
			
			
			
		}
		
		
		return $input_link;
	}
	
	
	
	/*
	*	input_type (string) : html | css | js
	*/
	public function optimize_speed_cdn_process_text($text, $input_type) 
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$checkStatus1 = true; 
				
		if($checkStatus1) {
			if ( is_feed() ) {
				$checkStatus1 = false;
			}
		}
		
		if($checkStatus1) {
			if ( is_admin() ) {
				$checkStatus1 = false;
			}
		}
		
		
		
		if($checkStatus1) {
			$checkStatus1 = false;
			
			if(isset($options['optimize_speed_cdn_enable']) && ($options['optimize_speed_cdn_enable'])) {
				if(isset($options['optimize_speed_cdn_domain']) && ($options['optimize_speed_cdn_domain'])) {
					$options['optimize_speed_cdn_domain'] = trim($options['optimize_speed_cdn_domain']);
					if($options['optimize_speed_cdn_domain']) {
						if($this->fullDomainName) {
							$checkStatus1 = true;
						}
					}
				}
			}
		}
		
		if(!$checkStatus1) {
			return $text;
		}
		
		
		
		$keyCacheProcessMain = array(
			__METHOD__
			,$text
			,'process_main'
		);
		
		$keyCacheProcessMain = PepVN_Data::createKey($keyCacheProcessMain);
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessMain); 
		
		if($valueTemp) {
			return $valueTemp;
		}
		
		
		$allTargetElements = array();
		$arrayDataTextNeedReplace = array();
		
		/*
		
		preg_match_all('#<script[^><]+src=[^><]+/?>.*?(</script>)?#is',$text,$matched1);
		if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
			$allTargetElements = array_merge($allTargetElements, $matched1[0]);
		}
		
		
		
		preg_match_all('#<img[^><]+src=[^><]+/?>#is',$text,$matched1);
		if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
			$allTargetElements = array_merge($allTargetElements, $matched1[0]);
		}
		
		
		
		preg_match_all('#<link[^><]+href=[^><]+/?>.*?(</link>)?#is',$text,$matched1);
		if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
			
			foreach($matched1[0] as $key1 => $value1) {
				if($value1) {
					if(preg_match('#(rel|type)=(\'|\")(stylesheet|text/css)#is',$value1,$matched2)) {
						$allTargetElements[] = $value1;
					}
				}
			}
		}
		
		preg_match_all('#<a[^><]+href=[^><]+>.*?</a>#is',$text,$matched1);
		if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
			$allTargetElements = array_merge($allTargetElements, $matched1[0]);
		}
		*/
		
		
		
		if('css' === $input_type) {
			preg_match_all('#(\'|\"|\(|\))(https?:)?//'.PepVN_Data::preg_quote($this->fullDomainName).'[^\'\"\(\)]+\.('.$this->optimize_speed_cdn_patternFilesTypeAllow.')\??[^\'\"\(\)]*?(\'|\"|\(|\))#is',$text,$matched1);
		} else {
			preg_match_all('#(\'|\")(https?:)?//'.PepVN_Data::preg_quote($this->fullDomainName).'[^\'\"]+\.('.$this->optimize_speed_cdn_patternFilesTypeAllow.')\??[^\'\"]*?\1#is',$text,$matched1);
		}
		
		if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
			$allTargetElements = array_merge($allTargetElements, $matched1[0]);
		}
		
		$allTargetElements = array_unique($allTargetElements);
		
		if(count($allTargetElements)>0) {
			
			foreach($allTargetElements as $key1 => $value1) {
				
				$checkStatus2 = false;
				
				if($value1) {
				
					//if(preg_match('#(href|src)?=?(\'|")((https?:)?//[^"\']+)\2#i',$value1,$matched2)) {
					
					$matched2 = false;
					
					if('css' === $input_type) {
						preg_match('#(\'|\"|\(|\))((https?:)?//[^\'|\"|\(|\)]+)(\'|\"|\(|\))#i',$value1,$matched2);
					} else {
						preg_match('#(\'|\")((https?:)?//[^\"\']+)\1#i',$value1,$matched2);
					}
					
					if(isset($matched2[2]) && $matched2[2]) {
						
						$matched2[2] = trim($matched2[2]);
						if($matched2[2]) {
							$valueTemp1 = $matched2[2];
							$valueTemp2 = $this->optimize_speed_cdn_get_cdn_link($valueTemp1);
							$valueTemp1 = PepVN_Data::removeProtocolUrl($valueTemp1);
							$valueTemp2 = PepVN_Data::removeProtocolUrl($valueTemp2);
							if($valueTemp1 !== $valueTemp2) {
								
								$valueTemp1 = '//'.$valueTemp1;
								$valueTemp2 = '//'.$valueTemp2;
								$arrayDataTextNeedReplace[$valueTemp1] = $valueTemp2;
								
							}
							
						}
					}
					
				}
				
			}
		}
		
		if(count($arrayDataTextNeedReplace)>0) {
			$text = str_replace(array_keys($arrayDataTextNeedReplace),array_values($arrayDataTextNeedReplace),$text);
		}
		
		$arrayDataTextNeedReplace = 0; 
		
		$text = trim($text); 
		
		$this->cacheObj->set_cache($keyCacheProcessMain,$text); 
		
		
		
		return $text;
		
		
	}
	
	
	
	
	public function optimize_speed_optimize_cache_get_filenamecache_current_request()
	{
		global $wpOptimizeByxTraffic_AdvancedCache;
		
		return $wpOptimizeByxTraffic_AdvancedCache->optimize_cache_get_filenamecache_current_request();
		
	}
	
	
	public function optimize_speed_optimize_cache_get_hash_current_request()
	{
		$resultData = array();
		
		$queried_object = get_queried_object();
		
		$resultData['fullDomainName'] = array(
			$this->fullDomainName => PepVN_Data::mcrc32($this->fullDomainName)
		);
		
		$resultData['urlFullRequest'] = array(
			$this->urlFullRequest => PepVN_Data::mcrc32($this->urlFullRequest)
		);
		
		
		
		$author = get_the_author();
		$authorObjId = 'author-'.$author;
		
		$resultData['authorObjId'] = array(
			$authorObjId => PepVN_Data::mcrc32($authorObjId)
		);
		
		
		
		$taxObjId = 'tax-';
		
		if($queried_object) {
			$term_id = $queried_object->term_id;
			if(is_category()) {
				$taxObjId = 'cat-'.get_cat_ID();
			} else if(is_tag()) {
				$taxObjId = 'tag-'.$term_id;
			} else if(is_tax()) {
				$taxObjId = 'tax-'.$term_id;
			}
		}
		
		$resultData['taxObjId'] = array(
			$taxObjId => PepVN_Data::mcrc32($taxObjId)
		);
		
	}
	
	
	
	
	
	
	public function optimize_speed_optimize_cache_flush_http_headers($input_parameters)
	{
		
		global $wpOptimizeByxTraffic_AdvancedCache;
		$wpOptimizeByxTraffic_AdvancedCache->optimize_cache_flush_http_headers($input_parameters);
		
	}
	
	
	
	
	
	
	
	public function optimize_speed_optimize_cache_isCacheable($options)
	{
		
		global $wpOptimizeByxTraffic_AdvancedCache;
		
		$isCacheStatus = false;
		
		if(isset($options['optimize_speed_optimize_cache_enable']) && $options['optimize_speed_optimize_cache_enable']) {
			$isCacheStatus = true;
		}
		
		if($isCacheStatus) {
			if(!$wpOptimizeByxTraffic_AdvancedCache->optimize_cache_isCacheable($options)) {
				$isCacheStatus = false;
			}
		}
		
		
		if($isCacheStatus) {
			if ( $this->base_is_admin() ) {
				$isCacheStatus = false;
			}
		}
		
		
		if($isCacheStatus) {
			if(is_single() || is_page() || is_singular() || is_feed()) {
			} else {
				
				if(isset($options['optimize_speed_optimize_cache_front_page_cache_enable']) && $options['optimize_speed_optimize_cache_front_page_cache_enable']) {
				
				} else {
					$isCacheStatus = false;
				}
				
			}
			
		}
		
		if($isCacheStatus) {
			if(isset($options['optimize_speed_optimize_cache_feed_page_cache_enable']) && $options['optimize_speed_optimize_cache_feed_page_cache_enable']) {
			
			} else {
				if(is_feed()) {
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
			if(isset($options['optimize_speed_optimize_cache_url_get_query_cache_enable']) && $options['optimize_speed_optimize_cache_url_get_query_cache_enable']) {
			
			} else {
				if ( preg_match('#.+\?+.*?#i', $this->urlFullRequest) ) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		if($isCacheStatus) {
			if(isset($options['optimize_speed_optimize_cache_logged_users_cache_enable']) && $options['optimize_speed_optimize_cache_logged_users_cache_enable']) {
			
			} else {
				if($this->base_get_current_user_id() > 0) {
					$isCacheStatus = false; 
				}
			}
		}
		
		
		
		
		if($isCacheStatus) {
			$options['optimize_speed_optimize_cache_exclude_url'] = trim($options['optimize_speed_optimize_cache_exclude_url']);
			$valueTemp = $options['optimize_speed_optimize_cache_exclude_url'];
			$valueTemp = PepVN_Data::cleanPregPatternsArray($valueTemp);
			if($valueTemp) {
				if(count($valueTemp)>0) {
					if(preg_match('#('.implode('|',$valueTemp).')#i',$this->urlFullRequest)) {
						$isCacheStatus = false;
					}
				}
			}
		}
		
		if($isCacheStatus) {
			$rs_get_woocommerce_urls = $this->base_get_woocommerce_urls();
			if(
				isset($rs_get_woocommerce_urls['urls_paths'])
				&& ($rs_get_woocommerce_urls['urls_paths'])
				&& is_array($rs_get_woocommerce_urls['urls_paths'])
			) {
			
				$valueTemp = $rs_get_woocommerce_urls['urls_paths'];
				$valueTemp = PepVN_Data::cleanPregPatternsArray($valueTemp);
				if($valueTemp) {
					if(count($valueTemp)>0) {
						if(preg_match('#('.implode('|',$valueTemp).')#i',$this->urlRequestNoParameters)) {
							$isCacheStatus = false; 
						}
					}
				}
				
			}
		}
		
		
		return $isCacheStatus; 
	}
	
	
	public function optimize_speed_optimize_cache_check_and_flush_http_browser_cache()
	{
		
		
		$options = $this->get_options(array( 
			'cache_status' => 1
		));
		
		$isCacheStatus = $this->optimize_speed_optimize_cache_isCacheable($options);
		
		if($isCacheStatus) {
			$isBrowserCacheStatus = false;
			if(isset($options['optimize_speed_optimize_cache_browser_cache_enable']) && $options['optimize_speed_optimize_cache_browser_cache_enable']) {
				$isBrowserCacheStatus = true;
			}
			
			
			
			if($isBrowserCacheStatus) {
					
				$rsOne = $this->optimize_speed_optimize_cache_get_info_current_request();
				
				$filenamecache = $rsOne['filenamecache'];
				$rsGetFilemtime = $rsOne['filemtime'];
				$etag = $rsOne['etag'];
				$contentType = $rsOne['content_type'];
				
				$parametersTemp = array();
				$parametersTemp['content_type'] = $contentType; 
				$parametersTemp['etag'] = $etag;
				$parametersTemp['last_modified_time'] = $rsGetFilemtime;
				$parametersTemp['cache_timeout'] = 0;
				if($isBrowserCacheStatus) {
					$parametersTemp['cache_timeout'] = $options['optimize_speed_optimize_cache_cachetimeout'];
				}
				
				$this->optimize_speed_optimize_cache_flush_http_headers($parametersTemp);
				$parametersTemp = 0;
				
			}
			
			
			
		}
	}
	
	public function optimize_speed_optimize_cache_get_info_current_request($input_mode = 1)
	{
		global $wpOptimizeByxTraffic_AdvancedCache;
		return $wpOptimizeByxTraffic_AdvancedCache->optimize_cache_get_info_current_request($input_mode);
	}
	
	
	public function optimize_speed_optimize_cache_check_and_get_page_cache()
	{
		global $wpOptimizeByxTraffic_AdvancedCache;
		$wpOptimizeByxTraffic_AdvancedCache->optimize_cache_check_and_get_page_cache();
	}
	
	
	
	
	public function optimize_speed_optimize_cache_check_and_create_page_cache($input_parameters = false)
	{
		
		$input_parameters['content'] = trim($input_parameters['content']);
		if($input_parameters['content']) {
			
			$options = $this->get_options(array(
				'cache_status' => 1
			));
			
			$isCacheStatus = $this->optimize_speed_optimize_cache_isCacheable($options);
			
			if($isCacheStatus) {
				
				$filenamecache = $this->optimize_speed_optimize_cache_get_filenamecache_current_request();
				
				PepVN_Data::$cacheSitePageObject->set_cache($filenamecache, $input_parameters['content']);
				
				$this->optimize_speed_optimize_cache_check_and_create_static_page_cache_for_server_software(array(
					'content' => $input_parameters['content']
					,'force_write_status' => 1
				));
				
			}
		}
		
		
	}
	
	
	public function optimize_speed_optimize_cache_check_and_create_static_page_cache_for_server_software($input_parameters)
	{
		global $wpOptimizeByxTraffic_AdvancedCache;
		$wpOptimizeByxTraffic_AdvancedCache->optimize_cache_check_and_create_static_page_cache_for_server_software($input_parameters);
		
	}
	
	
	
	
	public function optimize_speed_optimize_cache_prebuild_urls_cache()
	{
		sleep( 1 );
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$checkStatus1 = false;
		
		if(isset($options['optimize_speed_optimize_cache_enable']) && $options['optimize_speed_optimize_cache_enable']) {
			$checkStatus1 = true;
		}
		
		if($checkStatus1) {
			if(isset($options['optimize_speed_optimize_cache_prebuild_cache_enable']) && $options['optimize_speed_optimize_cache_prebuild_cache_enable']) {
			} else {
				$checkStatus1 = false;
			}
		}
		
		
		
		
		if(!$checkStatus1) {
			return false;
		}
		
		
		
		
		if(!isset($options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'])) {
			$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] = 1;
		}
		$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] = (int)$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'];
		if($options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] < 1) {
			$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] = 1;
		}
		
		
		
		
		if(!isset($options['optimize_speed_optimize_cache_cachetimeout'])) {
			$options['optimize_speed_optimize_cache_cachetimeout'] = 3600;
		}
		
		$timeoutRequest = 60;//seconds
		
		$maxTimePrebuild = 300;//seconds
		
		$staticVarData = PepVN_Data::staticVar_GetData();
		$staticVarData = $this->base_StaticVar_SafeVarForCronjobs($staticVarData);
		
		
		$groupUrlsStatistics = array();
		
		$groupUrlsNeedPrebuild = array();
		
		if(isset($staticVarData['statistics']['group_urls']) && is_array($staticVarData['statistics']['group_urls'])) {
			$groupUrlsStatistics = $staticVarData['statistics']['group_urls'];
		}
		
		$maxNumberUrlsPrebuild = 9999;
		
		if(isset($staticVarData['time_init']) && $staticVarData['time_init']) {
			
			$timePeriod = abs(time() - $staticVarData['time_init']);
			
			if($timePeriod < 1) {
				$timePeriod = 1;
			}
			
			$numberRequestsPerSecond = $staticVarData['total_number_requests'] / $timePeriod; 
			
			
			$maxNumberUrlsPrebuild1 = $maxTimePrebuild * $numberRequestsPerSecond;
			if($maxNumberUrlsPrebuild > $maxNumberUrlsPrebuild1) {
				$maxNumberUrlsPrebuild = $maxNumberUrlsPrebuild1;
			}
			
		}
		
	
		if($maxNumberUrlsPrebuild < 1) {
			$maxNumberUrlsPrebuild = 1;
		}
		
		if($maxNumberUrlsPrebuild > $options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process']) {
			$maxNumberUrlsPrebuild = $options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'];
		}
		
		$maxNumberUrlsPrebuild = (int)$maxNumberUrlsPrebuild;
		
		
		if(count($groupUrlsStatistics)>0) {
			arsort($groupUrlsStatistics);
			
			$iNumber1 = 0;
			
			foreach($groupUrlsStatistics as $key1 => $value1) {
				if($key1) {
					
					$checkStatus2 = true;
					
					if(isset($staticVarData['group_urls_prebuild_cache'][$key1]) && $staticVarData['group_urls_prebuild_cache'][$key1]) {
						$checkStatus2 = false;
						if(($staticVarData['group_urls_prebuild_cache'][$key1] + $options['optimize_speed_optimize_cache_cachetimeout']) < time()) {
							$checkStatus2 = true;
						}
					}
					
					if($checkStatus2) {
						$groupUrlsNeedPrebuild[] = $key1;
						$iNumber1++;
						if($iNumber1 > $maxNumberUrlsPrebuild) {
							break;
						}
					}
				}
				
			}
		}
		
		
		if(count($groupUrlsNeedPrebuild)>0) {
			$groupUrlsNeedPrebuild = array_unique($groupUrlsNeedPrebuild);
			
			foreach($groupUrlsNeedPrebuild as $key1 => $value1) {
				$this->quickGetUrlContent($value1, array(
					'timeout' => $timeoutRequest
					,'redirection' => 1
				));
				$staticVarData['group_urls_prebuild_cache'][$value1] = time();
				PepVN_Data::staticVar_SetData($staticVarData);
				sleep( 1 );
			}
		}
		
		
		PepVN_Data::staticVar_SetData($staticVarData); 
		
		sleep( 1 );
		
	}
	

	
	
	public function optimize_speed_is_subdirectory_install()
	{
		if(strlen(site_url()) > strlen(home_url())){
			return true;
		}
		return false;
	}
	
	
	
	public function optimize_speed_optimize_cache_check_and_create_database_cache()
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		$isCacheStatus = false;
		
		if(isset($options['optimize_speed_optimize_cache_enable']) && $options['optimize_speed_optimize_cache_enable']) {
			if(isset($options['optimize_speed_optimize_cache_database_cache_enable']) && $options['optimize_speed_optimize_cache_database_cache_enable']) {
				$isCacheStatus = true;
			}
		}
		
		
		if($isCacheStatus) {
			
		}
		
	}
	
	
	
	public function optimize_speed_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];
		
		
		
		//Optimize Cache Options
		$optimize_speed_optimize_cache_enable = $options['optimize_speed_optimize_cache_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_cache_browser_cache_enable = $options['optimize_speed_optimize_cache_browser_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_front_page_cache_enable = $options['optimize_speed_optimize_cache_front_page_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_database_cache_enable = $options['optimize_speed_optimize_cache_database_cache_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_cache_feed_page_cache_enable = $options['optimize_speed_optimize_cache_feed_page_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_ssl_request_cache_enable = $options['optimize_speed_optimize_cache_ssl_request_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_mobile_device_cache_enable = $options['optimize_speed_optimize_cache_mobile_device_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_url_get_query_cache_enable = $options['optimize_speed_optimize_cache_url_get_query_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_logged_users_cache_enable = $options['optimize_speed_optimize_cache_logged_users_cache_enable'] == 'on' ? 'checked':'';
		
		$optimize_speed_optimize_cache_prebuild_cache_enable = $options['optimize_speed_optimize_cache_prebuild_cache_enable'] == 'on' ? 'checked':'';
		$optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process = abs((int)$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process']);
		
		$optimize_speed_optimize_cache_cachetimeout = abs((int)$options['optimize_speed_optimize_cache_cachetimeout']);
		
		$optimize_speed_optimize_cache_exclude_url = trim($options['optimize_speed_optimize_cache_exclude_url']);
		
		
		
		
		
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
		
		
		
		
		
		//CDN Options
		$optimize_speed_cdn_enable = $options['optimize_speed_cdn_enable'] == 'on' ? 'checked':''; 
		$optimize_speed_cdn_domain = $options['optimize_speed_cdn_domain'];
		$optimize_speed_cdn_exclude_url = $options['optimize_speed_cdn_exclude_url'];
		
		
		
		
		
		
		
		
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
		
		$classSystemReady = '';
		$rsTemp = $this->optimize_speed_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		if(in_array(30,$rsTemp['notice']['error_no'])) {
			$classSystemReady = 'wpoptimizebyxtraffic_disabled';
		}
		
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin ',$classSystemReady,'" style="">
	
	<h2>WP Optimize By xTraffic (Optimize Speed)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
			
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
					
					<input type="hidden" name="submitted" value="1" /> 
					<input type="hidden" name="optimize_speed_submitted" value="1" /> 
					
					
					<h3>',__('Overview "Optimize Speed"',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
					<div class="wpoptimizebyxtraffic_green_block">
						<p>',__('Although we have tried and optimal features "Optimize Javascript" & "Optimize CSS" operate effectively on many websites, they make your website load faster and have higher scores on the measure tools.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
						<p>',__('But there are some exceptions make website\'s layout is broken or not running properly like before. If your website is in the unfortunate case, you just simply turn off only 2 features "Optimize Javascript" & "Optimize CSS" and experience other features, because they operate independently of each other.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
					</div>
					
					<div class="xtraffic_tabs_nav">
						<a href="#xtraffic_tabs_content1" class="active">Optimize Cache</a>
						<a href="#xtraffic_tabs_content2" class="">Optimize Javascript</a> 
						<a href="#xtraffic_tabs_content3" class="">Optimize CSS</a>
						<a href="#xtraffic_tabs_content4" class="">Optimize HTML</a>
						<a href="#xtraffic_tabs_content5" class="">CDN</a>
					</div>
					
					
					
					
					
					
					
					
					
					<div id="xtraffic_tabs_content1" class="xtraffic_tabs_contents">

						<h3>Optimize Cache</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_cache_container"  ',$optimize_speed_optimize_cache_enable,' /> &nbsp; ',__('Enable Optimize Cache',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_cache_container" class="wpoptimizebyxtraffic_show_hide_container">
							
							<ul>
								
								
								<li>
									
									<h6><input type="checkbox" name="optimize_speed_optimize_cache_front_page_cache_enable" class="" ',$optimize_speed_optimize_cache_front_page_cache_enable,' /> &nbsp; ',__('Enable Cache Front Page',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
									<p style="margin-bottom: 3%;" class="description">',__('Front Page include : Home page, category page, tag page, author page, date page, archives page,...',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
									
								</li> 
								
								<li>
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_feed_page_cache_enable" class="" ',$optimize_speed_optimize_cache_feed_page_cache_enable,' /> &nbsp; ',__('Enable Cache Feed (RSS/Atom) Page',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
									<p class="description"></p>
									
								</li> 
								
								<li>
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_browser_cache_enable" class="" ',$optimize_speed_optimize_cache_browser_cache_enable,' /> &nbsp; ',__('Enable Browser Cache',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
									<p class="description"></p>
									
								</li> 
								
								<li style="display:none;"> 
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_database_cache_enable" class="" ',$optimize_speed_optimize_cache_database_cache_enable,' /> &nbsp; ',__('Enable Database Cache',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
									<p class="description"></p>
									
								</li> 
								
								
								<li>
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_ssl_request_cache_enable" class="" ',$optimize_speed_optimize_cache_ssl_request_cache_enable,' /> &nbsp; ',__('Enable Cache SSL (https) Requests',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description"></p>
									
								</li> 
								
								<li style="margin-bottom: 3%;">
									
									<h6 style="margin-bottom: 0%;"><input type="checkbox" name="optimize_speed_optimize_cache_mobile_device_cache_enable" class="" ',$optimize_speed_optimize_cache_mobile_device_cache_enable,' /> &nbsp; ',__('Enable Cache For Mobile Device',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description" style="color:red;">',__('Warning',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': ',__('Don\'t turn on this option if you use one of these plugins: WP Touch, WP Mobile Detector, wiziApp, and WordPress Mobile Pack.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
									
								</li> 
								
								<li>
									
									<h6 style=""><input type="checkbox" name="optimize_speed_optimize_cache_url_get_query_cache_enable" class="" ',$optimize_speed_optimize_cache_url_get_query_cache_enable,' /> &nbsp; ',__('Enable Cache URIs with GET query string variables',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description" style="margin-bottom: 3%;">',__('Ex : "/?s=query..." at the end of a url',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
									
								</li> 
								
								
								
								<li>
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_cache_logged_users_cache_enable" class="" ',$optimize_speed_optimize_cache_logged_users_cache_enable,' /> &nbsp; ',__('Enable Cache For Logged Users',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description"></p>
									
								</li> 
								
								
								
								<li style="margin-bottom: 3%;">
									
									<h6 style="margin-bottom: 0;"><input type="checkbox" name="optimize_speed_optimize_cache_prebuild_cache_enable" data-target="#optimize_speed_optimize_cache_prebuild_cache_container"  class="wpoptimizebyxtraffic_show_hide_trigger" ',$optimize_speed_optimize_cache_prebuild_cache_enable,' /> &nbsp; ',__('Enable Prebuild Cache',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
									<p class="description">',__('Prebuild cache help your site load faster by creating cache of pages is the most visited.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
									<div style="margin-top: 0;" id="optimize_speed_optimize_cache_prebuild_cache_container" class="wpoptimizebyxtraffic_show_hide_container">
										<ul>	
											<li>
												<h6 style="margin-bottom: 0;">',__('Maximum number of pages is prebuilt each process',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'&nbsp;:&nbsp;<input type="text" name="optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process" class="" value="',$optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process,'" style="width: 100px;" />&nbsp;',__('pages',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6> 
												<p class="description">',__('This number depends on the performance of the server, if your server is fast then you should set this number higher and vice versa.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
											</li>
										</ul>
										
									</div>
								</li> 
								
								
								
						
								<li style="margin-bottom: 3%;">
									<h6> ',__('Cache Timeout',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'&nbsp;:&nbsp;<input type="text" name="optimize_speed_optimize_cache_cachetimeout" class="" value="',$optimize_speed_optimize_cache_cachetimeout,'" style="width: 100px;" />&nbsp;seconds </h6> 
									<p class="description">',__('How long should cached pages remain fresh? You should set this value from 21600 seconds (6 hours) to 86400 seconds (24 hours). Minimum value is 300 seconds (5 minutes).',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								</li>
								
								
								<li style="margin-bottom: 3%;">
									<h6> ',__('Exclude',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Contained in url, separate them by comma',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),')</h6> 
									<input type="text" name="optimize_speed_optimize_cache_exclude_url" class="" value="',$optimize_speed_optimize_cache_exclude_url,'" style="width: 50%;" /> &nbsp;  
									<p class="description">',__('Plugin will ignore these urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								</li>
								
								
							</ul>						
							<br /> 
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->  
					
					
					
					
					
					
					
					
					<div id="xtraffic_tabs_content2" class="xtraffic_tabs_contents">

						<h3>Optimize Javascript</h3>
						
						<ul>
							
							<li style="margin-bottom: 3%;">
								<h4 style="margin-bottom: 1%;"><input type="checkbox" name="optimize_speed_optimize_javascript_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_javascript_container"  ',$optimize_speed_optimize_javascript_enable,' /> &nbsp; ',__('Enable Optimize Javascript',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
								<p class="description" style="color:red;">',__('Warning',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': ',__('This option will help your site load faster. However, in some cases, web layout will be error. If an error occurs, you should disable this option.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_javascript_container" class="wpoptimizebyxtraffic_show_hide_container">
							
							<ul>
								<li>
									
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_combine_javascript_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_javascript_container2"  ',$optimize_speed_optimize_javascript_combine_javascript_enable,' /> &nbsp; ',__('Enable Combine Javascript',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description"></p>
									
								</li> 
								
								<!--
								<div style="margin-top: 0;" id="optimize_speed_optimize_javascript_container2" class="wpoptimizebyxtraffic_show_hide_container">
									
									<ul>
									
										<li>
											
											<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_minify_javascript_enable" class="" ',$optimize_speed_optimize_javascript_minify_javascript_enable,' /> &nbsp; ',__('Enable Minify Javascript',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h6>
											<p class="description"></p>
											
										</li>
									</ul>
									
								</div>
								-->
								
								<li>
											
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_minify_javascript_enable" class="" ',$optimize_speed_optimize_javascript_minify_javascript_enable,' /> &nbsp; ',__('Enable Minify Javascript',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description"></p>
									
								</li>
						
								<li>
								
									<h6 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable" class="" ',$optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable,' /> &nbsp; ',__('Enable Asynchronous Javascript Loading',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description"></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h6 style="margin-bottom: 0;"><input type="checkbox" name="optimize_speed_optimize_javascript_exclude_external_javascript_enable" class="" ',$optimize_speed_optimize_javascript_exclude_external_javascript_enable,' /> &nbsp; ',__('Exclude External Javascript File',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description">',__('Plugin will ignore all external javascript files',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Which not scripts in your self-hosted',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ). <i>',__('You should not enable this feature unless an error occurs',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</i></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h6 style="margin-bottom: 0%;"><input type="checkbox" name="optimize_speed_optimize_javascript_exclude_inline_javascript_enable" class="" ',$optimize_speed_optimize_javascript_exclude_inline_javascript_enable,' /> &nbsp; ',__('Exclude Inline Javascript Code',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
									<p class="description">',__('Plugin will ignore all javascript code in your html',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'. <i>',__('You should enable this feature unless an error occurs',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</i></p>
									
								</li>
								
								<li>
									<h6> ',__('Exclude',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Contained in url, separate them by comma',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),')</h6> 
									<input type="text" name="optimize_speed_optimize_javascript_exclude_url" class="" value="',$optimize_speed_optimize_javascript_exclude_url,'" style="width: 50%;" /> &nbsp;  
									<p class="description">',__('Plugin will ignore these javascript files urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								</li>
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->  
					
					
					
					
					<div id="xtraffic_tabs_content3" class="xtraffic_tabs_contents">

						<h3>',__('Optimize CSS (Style)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
						
						<ul>
							
							<li style="margin-bottom: 3%;">
								<h4 style="margin-bottom: 1%;"><input type="checkbox" name="optimize_speed_optimize_css_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_css_container"  ',$optimize_speed_optimize_css_enable,' /> &nbsp; ',__('Enable Optimize CSS',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
								<p class="description" style="color:red;">',__('Warning',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': ',__('This option will help your site load faster. However, in some cases, web layout will be error. If an error occurs, you should disable this option.',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_css_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_combine_css_enable" class="" ',$optimize_speed_optimize_css_combine_css_enable,' /> &nbsp; ',__('Enable Combine CSS',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
									<p class="description"></p>
									
								</li> 
								
						
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_minify_css_enable" class="" ',$optimize_speed_optimize_css_minify_css_enable,' /> &nbsp; ',__('Enable Minify CSS',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
									<p class="description"></p>
									
								</li>
								
								
						
								<li>
								
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_css_asynchronous_css_loading_enable" class="" ',$optimize_speed_optimize_css_asynchronous_css_loading_enable,' /> &nbsp; ',__('Enable Asynchronous CSS Loading',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
									<p class="description"></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h5 style="margin-bottom: 0;"><input type="checkbox" name="optimize_speed_optimize_css_exclude_external_css_enable" class="" ',$optimize_speed_optimize_css_exclude_external_css_enable,' /> &nbsp; ',__('Exclude External CSS Files',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
									<p class="description">',__('Plugin will ignore all external CSS files ( Which not CSS files in your self-hosted )',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'. <i>',__('You should not enable this feature unless an error occurs',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</i></p>
									
								</li>
								
								<li style="margin-bottom: 3%;">
									
									<h5 style="margin-bottom: 0%;"><input type="checkbox" name="optimize_speed_optimize_css_exclude_inline_css_enable" class="" ',$optimize_speed_optimize_css_exclude_inline_css_enable,' /> &nbsp; ',__('Exclude Inline CSS Code',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
									<p class="description">',__('Plugin will ignore all style (wrap by &#x3C;style&#x3E;&#x3C;/style&#x3E;) in your html',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'. <i>',__('You should not enable this feature unless an error occurs',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</i></p>
									
								</li> 
								
								<li>
									<h5> ',__('Exclude',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Contained in url, separate them by comma',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),')</h5> 
									<input type="text" name="optimize_speed_optimize_css_exclude_url" class="" ',$optimize_speed_optimize_css_exclude_url,' style="width: 50%;" /> &nbsp;  
									<p class="description">',__('Plugin will ignore these css files urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								</li>
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->
					
					
					
					
					<div id="xtraffic_tabs_content4" class="xtraffic_tabs_contents">

						<h3>',__('Optimize HTML',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_html_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_optimize_html_container"  ',$optimize_speed_optimize_html_enable,' /> &nbsp; ',__('Enable Optimize HTML',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),')</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_optimize_html_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								
								<li>
									
									<h5 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_optimize_html_minify_html_enable" class="" ',$optimize_speed_optimize_html_minify_html_enable,' /> &nbsp; ',__('Enable Minify HTML',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( ',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' )</h5>
									<p class="description"></p>
									
								</li>
								
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->
					
					
					
					
					<div id="xtraffic_tabs_content5" class="xtraffic_tabs_contents">

						<h3>CDN (Content Delivery Network)</h3>
						
						<ul>
							
							<li>
								<h4 style="margin-bottom: 3%;"><input type="checkbox" name="optimize_speed_cdn_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_speed_cdn_container"  ',$optimize_speed_cdn_enable,' /> &nbsp; ',__('Enable CDN',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h4>
							</li>
							
						</ul>
						
						<div style="margin-top: 0;" id="optimize_speed_cdn_container" class="wpoptimizebyxtraffic_show_hide_container">
							<ul>
								
								<li style="margin-bottom: 3%;">
									<h6> ',__('CNAME (CDN)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6> 
									<input type="text" name="optimize_speed_cdn_domain" class="" value="',$optimize_speed_cdn_domain,'" style="width: 50%;" /> &nbsp;  
									<p class="description"></p>
								</li>
								
								<li style="margin-bottom: 3%;">
									<h6> ',__('Exclude',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Contained in url, separate them by comma',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),')</h6> 
									<input type="text" name="optimize_speed_cdn_exclude_url" class="" value="',$optimize_speed_cdn_exclude_url,'" style="width: 50%;" /> &nbsp;  
									<p class="description">',__('Plugin will ignore these urls',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								</li>
								
								
								
							</ul>						
							<br />
							
						</div>
						
					</div><!-- //xtraffic_tabs_contents -->
					
					
					
					
					
					
					
					
					
					
						
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


