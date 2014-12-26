<?php




require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Cache.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Data.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Images.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_CSSmin.php');
require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_JSMin.php'); 

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Minify_HTML.php');

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/PepVN_Mobile_Detect.php');


if ( !class_exists('WPOptimizeByxTraffic_Base') ) :


class WPOptimizeByxTraffic_Base 
{
	
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
	
	
	
	public function base_getABSPATH()
	{
		$path = ABSPATH;
		$siteUrl = site_url();
		$homeUrl = home_url();
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

	
	
	public function base_clear_config_content($input_data)
	{
		$input_data = preg_replace('/[\s \t]+(\#\#\# BEGIN WPOPTIMIZEBYXTRAFFIC \#\#\#)/s', PHP_EOL . ' $1' ,$input_data);
		$input_data = preg_replace('/(\#\#\# END WPOPTIMIZEBYXTRAFFIC \#\#\#)[\s \t]+/s', '$1 ' . PHP_EOL ,$input_data);
		$input_data = preg_replace('/([\s \t]*?)\#\#\# BEGIN WPOPTIMIZEBYXTRAFFIC \#\#\#.+\#\#\# END WPOPTIMIZEBYXTRAFFIC \#\#\#([\s \t]*?)/s', PHP_EOL ,$input_data);
		
		return $input_data;
	}
	
	
	//do when plugin active once
	public function base_activate()
	{
		
		$pathRootWP = ABSPATH;
		
		if($this->optimize_speed_is_subdirectory_install()){
			$pathRootWP = $this->base_getABSPATH();
		}
		
		
		$pluginNameVersion = WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'/'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_VERSION;
		
		if('apache' === PepVN_Data::$defaultParams['serverSoftware']) {
			
			$pathFileHtaccess = $pathRootWP.'.htaccess';
			
			$htaccessContent = false;
			
			if(file_exists($pathFileHtaccess) && is_file($pathFileHtaccess) && is_writable($pathFileHtaccess)){
				$htaccessContent = @file_get_contents($pathFileHtaccess);
			} else if(PepVN_Data::is_writable($pathRootWP)) {
				@file_put_contents($pathFileHtaccess,'');
				if(PepVN_Data::is_writable($pathFileHtaccess)) {
					$htaccessContent = @file_get_contents($pathFileHtaccess);
				}
			}
			
			
			if(false !== $htaccessContent) {
			
				$htaccessContent = $this->base_clear_config_content($htaccessContent);
				
				$htaccessContent = trim($htaccessContent); 
				
				
				$myHtaccessConfig = 
'

### BEGIN WPOPTIMIZEBYXTRAFFIC ###

<IfModule pagespeed_module>
ModPagespeed on
</IfModule>

<ifModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/html text/plain text/xml application/xml application/xhtml+xml text/css text/javascript application/javascript application/x-javascript
</ifModule>


<ifModule mod_expires.c>
ExpiresActive On
ExpiresByType text/cache-manifest "access plus 0 seconds"

# Data
ExpiresByType text/xml "access plus 0 seconds"
ExpiresByType application/xml "access plus 0 seconds"
ExpiresByType application/json "access plus 0 seconds"

# Feed
ExpiresByType application/rss+xml "access plus 3600 seconds"
ExpiresByType application/atom+xml "access plus 3600 seconds"

# Favicon
ExpiresByType image/x-icon "access plus 15552000 seconds"

# Media: images, video, audio
ExpiresByType image/gif "access plus 15552000 seconds"
ExpiresByType image/png "access plus 15552000 seconds"
ExpiresByType image/jpeg "access plus 15552000 seconds"
ExpiresByType image/jpg "access plus 15552000 seconds"
ExpiresByType video/ogg "access plus 15552000 seconds"
ExpiresByType audio/ogg "access plus 15552000 seconds"
ExpiresByType video/mp4 "access plus 15552000 seconds"
ExpiresByType video/webm "access plus 15552000 seconds"

# HTC files  (css3pie)
ExpiresByType text/x-component "access plus 15552000 seconds"

# Webfonts
ExpiresByType application/x-font-ttf "access plus 15552000 seconds"
ExpiresByType font/opentype "access plus 15552000 seconds"
ExpiresByType font/woff2 "access plus 15552000 seconds"
ExpiresByType application/x-font-woff "access plus 15552000 seconds"
ExpiresByType image/svg+xml "access plus 15552000 seconds"
ExpiresByType application/vnd.ms-fontobject "access plus 15552000 seconds"

# CSS and JavaScript
ExpiresByType text/css "access plus 15552000 seconds"
ExpiresByType application/javascript "access plus 15552000 seconds"
ExpiresByType text/javascript "access plus 15552000 seconds"
ExpiresByType application/javascript "access plus 15552000 seconds"
ExpiresByType application/x-javascript "access plus 15552000 seconds"

# Others files
ExpiresByType application/x-shockwave-flash "access plus 15552000 seconds"
ExpiresByType application/octet-stream "access plus 15552000 seconds"
</ifModule>


<ifModule mod_headers.c>
	<filesMatch "\.(ico|jpe?g|png|gif|swf)$">
		Header set Cache-Control "public, max-age=15552000"
		Header set Pragma "public"
	</filesMatch>
	<filesMatch "\.(css)$">
		Header set Cache-Control "public, max-age=15552000"
		Header set Pragma "public"
	</filesMatch>
	<filesMatch "\.(js)$">
		Header set Cache-Control "public, max-age=15552000"
		Header set Pragma "public"
	</filesMatch>
	
	Header set X-Powered-By "'.$pluginNameVersion.'"
	Header set Server "'.$pluginNameVersion.'"
</ifModule>


<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
# If you serve pages from behind a proxy you may want to change \'RewriteCond %{HTTPS} on\' to something more sensible
AddDefaultCharset UTF-8

RewriteCond %{REQUEST_URI} !^.*[^/]$
RewriteCond %{REQUEST_URI} !^.*//.*$
RewriteCond %{REQUEST_URI} !^.*(wp-includes|wp-content|wp-admin|\.php).*$
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{QUERY_STRING} !.*=.*
RewriteCond %{HTTP:Cookie} !^.*(comment_author_|wordpress_logged_in|wp-postpass_).*$
RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP_USER_AGENT} !^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).* [NC]
RewriteCond %{HTTP_user_agent} !^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).* [NC]
RewriteCond %{HTTP_USER_AGENT} !(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge\ |maemo|midp|mmp|mobile.+firefox|netfront|opera\ m(ob|in)i|palm(\ os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows\ ce|xda|xiino [NC,OR]
RewriteCond %{HTTP_USER_AGENT} !^(1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a\ wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r\ |s\ )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1\ u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp(\ i|ip)|hs\-c|ht(c(\-|\ |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac(\ |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt(\ |\/)|klon|kpt\ |kwc\-|kyo(c|k)|le(no|xi)|lg(\ g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-|\ |o|v)|zz)|mt(50|p1|v\ )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v\ )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-|\ )|webc|whit|wi(g\ |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-) [NC]
RewriteCond %{HTTPS} on
RewriteCond %{DOCUMENT_ROOT}/wp-content/cache/'.WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG.'/data/%{SERVER_NAME}/https/pc/$1/data/index.html -f
RewriteRule ^(.*) "/wp-content/cache/'.WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG.'/data/%{SERVER_NAME}/https/pc/$1/data/index.html" [L]

RewriteCond %{REQUEST_URI} !^.*[^/]$
RewriteCond %{REQUEST_URI} !^.*//.*$
RewriteCond %{REQUEST_URI} !^.*(wp-includes|wp-content|wp-admin|\.php).*$
RewriteCond %{REQUEST_METHOD} !POST
RewriteCond %{QUERY_STRING} !.*=.*
RewriteCond %{HTTP:Cookie} !^.*(comment_author_|wordpress_logged_in|wp-postpass_).*$
RewriteCond %{HTTP:X-Wap-Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP:Profile} !^[a-z0-9\"]+ [NC]
RewriteCond %{HTTP_USER_AGENT} !^.*(2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800).* [NC]
RewriteCond %{HTTP_user_agent} !^(w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-).* [NC]
RewriteCond %{HTTP_USER_AGENT} !(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge\ |maemo|midp|mmp|mobile.+firefox|netfront|opera\ m(ob|in)i|palm(\ os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows\ ce|xda|xiino [NC,OR]
RewriteCond %{HTTP_USER_AGENT} !^(1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a\ wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r\ |s\ )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1\ u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp(\ i|ip)|hs\-c|ht(c(\-|\ |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac(\ |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt(\ |\/)|klon|kpt\ |kwc\-|kyo(c|k)|le(no|xi)|lg(\ g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-|\ |o|v)|zz)|mt(50|p1|v\ )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v\ )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-|\ )|webc|whit|wi(g\ |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-) [NC]
RewriteCond %{HTTPS} !on
RewriteCond %{DOCUMENT_ROOT}/wp-content/cache/'.WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG.'/data/%{SERVER_NAME}/http/pc/$1/data/index.html -f
RewriteRule ^(.*) "/wp-content/cache/'.WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG.'/data/%{SERVER_NAME}/http/pc/$1/data/index.html" [L]
</IfModule>

### END WPOPTIMIZEBYXTRAFFIC ###

';

				$myHtaccessConfig = preg_replace('#\#[^\r\n]+#is','',$myHtaccessConfig);
				$myHtaccessConfig = preg_replace('#[\r\n]{2,}#is',PHP_EOL . PHP_EOL,$myHtaccessConfig);
				
				$myHtaccessConfig = trim($myHtaccessConfig);
				
				$myHtaccessConfig = PHP_EOL . '### BEGIN WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL . $myHtaccessConfig . PHP_EOL . '### END WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL;
				
				$htaccessContent = $myHtaccessConfig . $htaccessContent;
				
				@file_put_contents($pathFileHtaccess,$htaccessContent);
			}
			
			
			$folderPath = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH;
						
			$pathFileHtaccess = $folderPath.'.htaccess';
			
			$htaccessContent = false;
			
			if(file_exists($pathFileHtaccess) && is_file($pathFileHtaccess) && is_writable($pathFileHtaccess)){
				$htaccessContent = @file_get_contents($pathFileHtaccess);
			} else if(PepVN_Data::is_writable($folderPath)) {
				@file_put_contents($pathFileHtaccess,'');
				if(PepVN_Data::is_writable($pathFileHtaccess)) {
					$htaccessContent = @file_get_contents($pathFileHtaccess);
				}
			}
			
			
			if(false !== $htaccessContent) {
			
				$htaccessContent = $this->base_clear_config_content($htaccessContent);
				
				$htaccessContent = trim($htaccessContent);
				
				$myHtaccessConfig = 
			
'

### BEGIN WPOPTIMIZEBYXTRAFFIC ###

<IfModule mod_mime.c>
  <FilesMatch "\.html\.gz$">
    ForceType text/html
    FileETag None
  </FilesMatch>
  AddEncoding gzip .gz
  AddType text/html .gz
</IfModule>
<IfModule mod_deflate.c>
  SetEnvIfNoCase Request_URI \.gz$ no-gzip
</IfModule>
<IfModule mod_headers.c>
  Header set Vary "Accept-Encoding, Cookie"
  Header set Cache-Control \'max-age=15, must-revalidate\'
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/html A3
</IfModule>

### END WPOPTIMIZEBYXTRAFFIC ###

';
				
				$myHtaccessConfig = preg_replace('#\#[^\r\n]+#is','',$myHtaccessConfig);
				$myHtaccessConfig = preg_replace('#[\r\n]+#is',PHP_EOL,$myHtaccessConfig);
				
				$myHtaccessConfig = trim($myHtaccessConfig);
				
				$myHtaccessConfig = PHP_EOL . '### BEGIN WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL . $myHtaccessConfig . PHP_EOL . '### END WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL;
				
				$htaccessContent = $myHtaccessConfig . $htaccessContent;
				
				@file_put_contents($pathFileHtaccess,$htaccessContent);

			}
			
		} else if('nginx' === PepVN_Data::$defaultParams['serverSoftware']) {
			
			$pathFileConf = $pathRootWP.'xtraffic-nginx.conf';
			
			$configContent = false;
			
			if(file_exists($pathFileConf) && is_file($pathFileConf) && is_writable($pathFileConf)){
				$configContent = @file_get_contents($pathFileConf);
			} else if(PepVN_Data::is_writable($pathRootWP)) {
				@file_put_contents($pathFileConf,'');
				if(PepVN_Data::is_writable($pathFileConf)) {
					$configContent = @file_get_contents($pathFileConf);
				}
			}
			
			
			if(false !== $configContent) {
			
				$configContent = $this->base_clear_config_content($configContent);
				
				$configContent = trim($configContent); 
				
				
				$myConfigContent = 
'
# Deny access to any files with a .php extension in the uploads directory
# Works in sub-directory installs and also in multisite network
# Keep logging the requests to parse later (or to pass to firewall utilities such as fail2ban)
location ~* /(?:uploads|files)/.*\.php$ {
	deny all;
}

# Deny all attempts to access hidden files such as .htaccess, .htpasswd, .DS_Store (Mac).
# Keep logging the requests to parse later (or to pass to firewall utilities such as fail2ban)
location ~ /\. {
	deny all;
}

location ~ /('.WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG.'/inc)/ {
   deny all;
   return 403;
}

keepalive_timeout 60s;
server_tokens off;

gzip on;
gzip_comp_level 2;
gzip_min_length 1100;
gzip_buffers 4 8k;
gzip_types text/css text/x-component application/json application/x-javascript application/javascript text/javascript text/x-js text/richtext image/svg+xml text/plain text/xsd text/xsl application/xml application/xml+rss text/xml image/x-icon;
gzip_vary on;
gzip_proxied any;
gzip_disable "MSIE [1-6]\.";

add_header X-Powered-By "'.$pluginNameVersion.'";
add_header Server "'.$pluginNameVersion.'";
add_header Connection "keep-alive";

location ~ \.(css|htc|less|js|js2|js3|js4)$ {
    expires 15552000s;
    add_header Pragma "public";
    add_header Cache-Control "max-age=15552000, public";
	access_log off; log_not_found off;
}

location ~ \.(asf|asx|wax|wmv|wmx|avi|bmp|class|divx|doc|docx|eot|exe|gif|gz|gzip|ico|jpg|jpeg|jpe|json|mdb|mid|midi|mov|qt|mp3|m4a|mp4|m4v|mpeg|mpg|mpe|mpp|otf|odb|odc|odf|odg|odp|ods|odt|ogg|pdf|png|pot|pps|ppt|pptx|ra|ram|svg|svgz|swf|tar|tif|tiff|ttf|ttc|wav|wma|wri|woff|woff2|xla|xls|xlsx|xlt|xlw|zip)$ {
    expires 15552000s;
    add_header Pragma "public";
    add_header Cache-Control "max-age=15552000, public";
	access_log off; log_not_found off;
}


location ~ \.(rtf|rtx|svg|svgz|txt|xsd|xsl|xml)$ {
    expires 300s;
    add_header Pragma "public";
    add_header Cache-Control "max-age=300, public";
}


#Warning : html is error, must disable this
#location ~ \.(html|htm)$ {
#    expires 15s;
#    add_header Pragma "public";
#    add_header Cache-Control "max-age=15, public";
#}




# '.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.' rules.

set $cache_uri $request_uri;

# POST requests and urls with a query string should always go to PHP

if ($request_method = POST) {
	set $cache_uri \'null cache\';
}

if ($request_method = PUT) {
	set $cache_uri \'null cache\';
}

if ($request_method = UPDATE) {
	set $cache_uri \'null cache\';
}


if ($request_method = DELETE) {
	set $cache_uri \'null cache\';
}

if ($query_string != "") {
	set $cache_uri \'null cache\'; 
}   

# Don\'t cache uris containing the following segments
if ($request_uri ~* "(/wp-admin/|/wp-content/|/wp-includes/|/xmlrpc.php|/wp-(app|cron|login|register|mail).php|wp-.*.php|/feed/|index.php|wp-comments-popup.php|wp-links-opml.php|wp-locations.php|sitemap(_index)?.xml|[a-z0-9_-]+-sitemap([0-9]+)?.xml)") {
	set $cache_uri \'null cache\';
}   

# Don\'t use the cache for logged in users or recent commenters

if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_logged_in") {
	set $cache_uri \'null cache\';
}

# START MOBILE
# Mobile browsers section to server them non-cached version. COMMENTED by default as most modern wordpress themes including twenty-eleven are responsive. Uncomment config lines in this section if you want to use a plugin like WP-Touch

if ($http_x_wap_profile) {
	set $cache_uri \'null cache\';
}

if ($http_profile) {
	set $cache_uri \'null cache\';
}

if ($http_user_agent ~* (2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800)) {
	set $cache_uri \'null cache\';
}

if ($http_user_agent ~* (w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-)) {
	set $cache_uri \'null cache\';
}


if ($http_user_agent ~* ((android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge\ |maemo|midp|mmp|mobile.+firefox|netfront|opera\ m(ob|in)i|palm(\ os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows\ ce|xda|xiino)) {
	set $cache_uri \'null cache\';
}

if ($http_user_agent ~* (1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a\ wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r\ |s\ )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1\ u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp(\ i|ip)|hs\-c|ht(c(\-|\ |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac(\ |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt(\ |\/)|klon|kpt\ |kwc\-|kyo(c|k)|le(no|xi)|lg(\ g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-|\ |o|v)|zz)|mt(50|p1|v\ )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v\ )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-|\ )|webc|whit|wi(g\ |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-)) {
	set $cache_uri \'null cache\';
}

#END MOBILE



location / {
	root '.$pathRootWP.';
	index index.php index.html index.htm default.html default.htm;
	try_files /wp-content/cache/'.WPOPTIMIZEBYXTRAFFIC_OPTIMIZE_CACHE_SLUG.'/data/$http_host/$scheme/pc/$cache_uri/data/index.html $uri $uri/ /index.php?$args;
}


';
				
				$myConfigContent = preg_replace('#\#[^\r\n]+#is','',$myConfigContent);
				$myConfigContent = preg_replace('#([\;\{\}]+)\s+#is','$1 ',$myConfigContent);
				$myConfigContent = preg_replace('#\s+([\;\{\}]+)#is',' $1',$myConfigContent);
				
				
				$myConfigContent = trim($myConfigContent);
				
				
				$myConfigContent = PHP_EOL . '### BEGIN WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL . $myConfigContent . PHP_EOL . '### END WPOPTIMIZEBYXTRAFFIC ###' . PHP_EOL;
				
				$configContent = $myConfigContent . $configContent;
				
				@file_put_contents($pathFileConf, $configContent);
			}
		}
		
		
	
		
		$pathFile1 = $pathRootWP.'wp-settings.php';
		
		$fileContent1 = false;
		
		if(file_exists($pathFile1) && is_file($pathFile1) && is_writable($pathFile1)){
			$fileContent1 = @file_get_contents($pathFile1);
			if($fileContent1) {
				
				$fileContent1 = $this->base_clear_config_content($fileContent1);
				
				
				$patterns = array();
				
				$pathFile2 = $pathRootWP.'wp-content/plugins/wp-optimize-by-xtraffic/inc/wp-optimize-by-xtraffic-advanced-cache.php';
				$replace1 = 
'wp_initial_constants();

### BEGIN WPOPTIMIZEBYXTRAFFIC ###

if(file_exists(\''.$pathFile2.'\')) {
	@include_once(\''.$pathFile2.'\');
}

### END WPOPTIMIZEBYXTRAFFIC ###

';
				$patterns['wp_initial_constants();'] = $replace1; 
				
				$fileContent1 = str_replace(array_keys($patterns),array_values($patterns),$fileContent1);
				
				@file_put_contents($pathFile1,$fileContent1);
				
			}
			
		}
		
		
		$this->enable_db_fulltext(array(
			'force_check_fulltext_status' => 1
		));
		
		
		$this->base_clear_data(',all,');
	}
	
	
	
	
	//do when plugin deactivate once
	public function base_deactivate()
	{
		$pathRootWP = ABSPATH;
		
		if($this->optimize_speed_is_subdirectory_install()){
			$pathRootWP = $this->base_getABSPATH();
		}
		
		$arrayFilesNeedCleanConfigs = array();
		
		$arrayFilesNeedCleanConfigs[] = $pathRootWP.'wp-settings.php';
		
		if('apache' === PepVN_Data::$defaultParams['serverSoftware']) {
			
			$arrayFilesNeedCleanConfigs[] = $pathRootWP.'.htaccess';
			$arrayFilesNeedCleanConfigs[] = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH.'.htaccess';
			$arrayFilesNeedCleanConfigs[] = $pathRootWP.'xtraffic-nginx.conf';
		}
		
		$arrayFilesNeedCleanConfigs = array_unique($arrayFilesNeedCleanConfigs);
		
		foreach($arrayFilesNeedCleanConfigs as $keyOne => $valueOne) {
			
			if($valueOne) {
			
				if(file_exists($valueOne) && is_file($valueOne) && is_writable($valueOne)){
					$fileContent = @file_get_contents($valueOne);
					if($fileContent) {
						
						$fileContent = $this->base_clear_config_content($fileContent);
						
						@file_put_contents($valueOne,$fileContent);
						
					}
					
					
				}
				
			}
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
	
	
	
	public function base_add_plugin_info_html($text)
	{
		
		$textAppendToEndBodyTagHtml = PHP_EOL . '<!-- '
		. PHP_EOL . '+ This website has been optimized by plugin "WP Optimize By xTraffic".' 
		. PHP_EOL . '+ Served from : '.$this->fullDomainName.' @ ' . date('Y-m-d H:i:s') . ' by "WP Optimize By xTraffic".'
		//. PHP_EOL . '+ Page Caching using disk.'
		//. PHP_EOL . '+ Processing time before use cache : '.number_format(microtime(true) - WPOPTIMIZEBYXTRAFFIC_PLUGIN_TIMESTART, 10, '.', '').' seconds.'
		. PHP_EOL . '+ Learn more here : http://wordpress.org/plugins/wp-optimize-by-xtraffic/ '
		. PHP_EOL . ' -->';
		
		$text = PepVN_Data::appendTextToTagBodyOfHtml($textAppendToEndBodyTagHtml,$text); 
		
		$text = trim($text);
		
		return $text;
		
	}
	
	
	public function base_gmdate_gmt($input_timestamp)
	{
		return PepVN_Data::gmdate_gmt($input_timestamp);
		
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
		
		//set options
		$keyCache1 = WPOPTIMIZEBYXTRAFFIC_PLUGIN_OPTIONS_CACHE_KEY;
		if(!PepVN_Data::$cacheObject->get_cache($keyCache1)) {
			PepVN_Data::$cacheObject->set_cache($keyCache1, $this->get_options(array(
				'cache_status' => 1
			)));
		}
		
		
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
			
			'optimize_links_enable' => 'on', //on
			
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
			* Optimize Speed Setting
			*/
			
			//optimize_cache
			'optimize_speed_optimize_cache_enable' => '',//on
			
			'optimize_speed_optimize_cache_browser_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_front_page_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_feed_page_cache_enable' => 'on',//on
			'optimize_speed_optimize_cache_ssl_request_cache_enable' => '',//on
			'optimize_speed_optimize_cache_mobile_device_cache_enable' => '',//on
			'optimize_speed_optimize_cache_url_get_query_cache_enable' => '',//on
			'optimize_speed_optimize_cache_logged_users_cache_enable' => '',//on
			'optimize_speed_optimize_cache_database_cache_enable' => '',//on
			'optimize_speed_optimize_cache_prebuild_cache_enable' => '',//on
			'optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process' => 1,//int 
			'optimize_speed_optimize_cache_cachetimeout' => 86400,//int 
			'optimize_speed_optimize_cache_exclude_url' => '/cart/,/checkout/',//text
			
			
			
			//optimize_javascript
			'optimize_speed_optimize_javascript_enable' => '',//on
			'optimize_speed_optimize_javascript_combine_javascript_enable' => '',//on
			'optimize_speed_optimize_javascript_minify_javascript_enable' => 'on',//on
			'optimize_speed_optimize_javascript_asynchronous_javascript_loading_enable' => '',//on
			'optimize_speed_optimize_javascript_exclude_external_javascript_enable' => 'on',//on
			'optimize_speed_optimize_javascript_exclude_inline_javascript_enable' => 'on',//on 
			'optimize_speed_optimize_javascript_exclude_url' => 'alexa.com,',//text
			
			
			//optimize_css
			'optimize_speed_optimize_css_enable' => '',//on
			'optimize_speed_optimize_css_combine_css_enable' => '',//on
			'optimize_speed_optimize_css_minify_css_enable' => 'on',//on
			'optimize_speed_optimize_css_asynchronous_css_loading_enable' => '',//on
			'optimize_speed_optimize_css_exclude_external_css_enable' => 'on',//on
			'optimize_speed_optimize_css_exclude_inline_css_enable' => 'on',//on 
			'optimize_speed_optimize_css_exclude_url' => '',//text
			
			
			//optimize_html
			'optimize_speed_optimize_html_enable' => '',//on
			'optimize_speed_optimize_html_minify_html_enable' => 'on',//on
			
			
			
			//cdn
			'optimize_speed_cdn_enable' => '',//on
			'optimize_speed_cdn_domain' => '',//string
			'optimize_speed_cdn_exclude_url' => 'captcha,/wp-admin/,.php,',//string 
			
			
			
			
			
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

	
	
	public function base_get_woocommerce_urls()
	{
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,'base_get_woocommerce_urls'
		));
		
		$valueTemp = $this->cacheObj->get_cache($keyCache1);
		
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
		
		$this->cacheObj->set_cache($keyCache1,$resultData);
		
		return $resultData;
		
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
		
		if(isset(PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) && PepVN_Data::$defaultParams['parseedUrlFullRequest']['host']) {
			$arrayPaths[] = WPOPTIMIZEBYXTRAFFIC_WPCONTENT_OPTIMIZE_CACHE_PATH.'data/'.PepVN_Data::$defaultParams['parseedUrlFullRequest']['host'] . '/';
		}
		
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
						//PepVN_Data::chmod($path1,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
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
		
		
		
		
		$staticVarData = PepVN_Data::staticVar_GetData();
		$staticVarData['group_urls_prebuild_cache'] = array();
		PepVN_Data::staticVar_SetData($staticVarData,'r');
		
		
		
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
					
					
					//cdn
					,'optimize_speed_cdn_enable'
					,'optimize_speed_cdn_domain'
					,'optimize_speed_cdn_exclude_url'
					
					
					
					
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
					,'optimize_speed_optimize_cache_prebuild_cache_enable'
					,'optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'
					,'optimize_speed_optimize_cache_cachetimeout'
					,'optimize_speed_optimize_cache_exclude_url'
					
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
				
				
				$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] = abs((int)$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process']);
				if($options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] < 1) {
					$options['optimize_speed_optimize_cache_prebuild_cache_number_pages_each_process'] = 1;
				}
				
				
				$keyField1 = 'optimize_speed_cdn_domain';
				$options[$keyField1] = trim($options[$keyField1]);
				if($options[$keyField1]) {
					$valueField1 = $options[$keyField1];
					$valueField1 = 'http://'.PepVN_Data::removeProtocolUrl($valueField1);
					$valueField1 = PepVN_Data::parseUrl($valueField1); 
					if($valueField1) {
						if(isset($valueField1['host']) && $valueField1['host']) {
							$valueField1['host'] = trim($valueField1['host']);
							if($valueField1['host']) {
								$options[$keyField1] = strtolower($valueField1['host']);
							}
						}
					}
				}
				
				$keyField1 = 'optimize_speed_cdn_exclude_url';
				$options[$keyField1] = preg_replace('#[\'\"]+#','',$options[$keyField1]);
				$options[$keyField1] = trim($options[$keyField1]);
				
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
				
				if($this->base_is_current_user_logged_in_can('activate_plugins')) {
					$rsOne = $this->optimize_traffic_preview_optimize_traffic_modules($dataSent);
					
					$resultData = PepVN_Data::mergeArrays(array(
						$resultData
						,$rsOne
					));
				}
			}
			
			
			if(isset($dataSent['cronjobs']['status']) && $dataSent['cronjobs']['status']) {
				$rsOne = $this->base_cronjobs();
				
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
				
			}
		}
		
		
		
		echo PepVN_Data::encodeResponseData($resultData);
		
	
	
	
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
	
	public function base_cronjobs() 
	{
		sleep( 2 );
		
		$resultData = array();
		$resultData['cronjobs_status'] = 0;
		
		$staticVarData = PepVN_Data::staticVar_GetData();
		
		$doCronjobsStatus = true;
		
		if(isset($staticVarData['is_processing_base_cronjobs_status']) && $staticVarData['is_processing_base_cronjobs_status']) {
			
			$doCronjobsStatus = false;
			
			if(isset($staticVarData['time_last_process_base_cronjobs']) && $staticVarData['time_last_process_base_cronjobs']) {
				if(($staticVarData['time_last_process_base_cronjobs'] + 3600) < time()) {	//is timeout
					$doCronjobsStatus = true;
				}
			}
			
		} else {
			if(isset($staticVarData['time_last_process_base_cronjobs']) && $staticVarData['time_last_process_base_cronjobs']) {
				$doCronjobsStatus = false;
				
				if(($staticVarData['time_last_process_base_cronjobs'] + 15) < time()) {	//is timeout 
					$doCronjobsStatus = true;
				}
			}
		}
		
		
		
		if($doCronjobsStatus) {
			
			$staticVarData['time_last_process_base_cronjobs'] = time();
			$staticVarData['is_processing_base_cronjobs_status'] = 1;
			
			PepVN_Data::staticVar_SetData($staticVarData);
			
			
			
			
			
			
			
			
			
			
			$resultData['cronjobs_status'] = 1;
			
			$this->optimize_speed_optimize_cache_prebuild_urls_cache();
			sleep( 1 );
			
			
			
			
			
			
			
			
			
			$staticVarData['time_last_process_base_cronjobs'] = time();
			$staticVarData['is_processing_base_cronjobs_status'] = 0;
			
			PepVN_Data::staticVar_SetData($staticVarData);
		}
		
		return $resultData;
		
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
			
			$args1 = array(
				'timeout'     => 6,
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

