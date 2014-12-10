<?php

/*
 * PepVN_Data v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
 
 
 

if ( !class_exists('PepVN_Data') ) :


class PepVN_Data 
{

	public static $defaultParams = false;
	public static $cacheData = array();
	
	public static $cacheObject = false;
	public static $cacheSitePageObject = false;
	
	
	public static $staticVarDataFileObject = false;
	
	function __construct()
	{
		self::setDefaultParams(); 
	}
	
	
	public static function setDefaultParams()
	{
		if(!self::$defaultParams) {
			
			
			self::$defaultParams['status'] = 1;
			
			unset($arrayVietnameseChar);
			$arrayVietnameseChar = array(
				'à' => 'a',
				'á' => 'a',
				'ạ' => 'a',
				'ả' => 'a',
				'ã' => 'a',
				'â' => 'a',
				'ầ' => 'a',
				'ấ' => 'a',
				'ậ' => 'a',
				'ẩ' => 'a',
				'ẫ' => 'a',
				'ă' => 'a',
				'ằ' => 'a',
				'ắ' => 'a',
				'ặ' => 'a',
				'ẳ' => 'a',
				'ẵ' => 'a',
				'è' => 'e',
				'é' => 'e',
				'ẹ' => 'e',
				'ẻ' => 'e',
				'ẽ' => 'e',
				'ê' => 'e',
				'ề' => 'e',
				'ế' => 'e',
				'ệ' => 'e',
				'ể' => 'e',
				'ễ' => 'e',
				'ì' => 'i',
				'í' => 'i',
				'ị' => 'i',
				'ỉ' => 'i',
				'ĩ' => 'i',
				'ò' => 'o',
				'ó' => 'o',
				'ọ' => 'o',
				'ỏ' => 'o',
				'õ' => 'o',
				'ô' => 'o',
				'ồ' => 'o',
				'ố' => 'o',
				'ộ' => 'o',
				'ổ' => 'o',
				'ỗ' => 'o',
				'ơ' => 'o',
				'ờ' => 'o',
				'ớ' => 'o',
				'ợ' => 'o',
				'ở' => 'o',
				'ỡ' => 'o',
				'ù' => 'u',
				'ú' => 'u',
				'ụ' => 'u',
				'ủ' => 'u',
				'ũ' => 'u',
				'ư' => 'u',
				'ừ' => 'u',
				'ứ' => 'u',
				'ự' => 'u',
				'ử' => 'u',
				'ữ' => 'u',
				'ỳ' => 'y',
				'ý' => 'y',
				'ỵ' => 'y',
				'ỷ' => 'y',
				'ỹ' => 'y',
				'đ' => 'd',
				'đ' => 'd',
				'Ð' => 'D'
			);
			
			$arrayVietnameseHasSign = explode(',',(trim(self::strtoupper(trim(implode(',',(array_keys($arrayVietnameseChar))))))));
			$arrayVietnameseNoSign = explode(',',(trim(strtoupper(trim(implode(',',(array_values($arrayVietnameseChar))))))));
			
			self::$defaultParams['char']['vietnamese'] = array_merge($arrayVietnameseChar, array_combine($arrayVietnameseHasSign, $arrayVietnameseNoSign));
			unset($arrayVietnameseHasSign, $arrayVietnameseNoSign, $arrayVietnameseChar);
			
			
			self::$defaultParams['http']['user-agent'] = 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
			$valueTemp1['user-agent'] = 'User-Agent: '.self::$defaultParams['http']['user-agent'];
			$valueTemp1['accept'] = 'Accept: */*;';
			//$valueTemp1['accept-language'] = 'Accept-Language: en-us,en;q=0.9,de;q=0.8,ja;q=0.8,zh;q=0.7,zh-cn;q=0.6,nl;q=0.5,fr;q=0.5,it;q=0.4,ko;q=0.3,es;q=0.2,ru;q=0.2,pt;q=0.1';
			$valueTemp1['accept-encoding'] = 'Accept-Encoding: gzip,deflate';
			$valueTemp1['accept-charset'] = 'Accept-Charset: UTF-8,*;';
			$valueTemp1['keep-alive'] = 'Keep-Alive: 300';
			$valueTemp1['connection'] = 'Connection: keep-alive';
			//$opts_Headers = array();
			
			self::$defaultParams['http']['headers'] = $valueTemp1;
			
			
			
			
			self::createFolder(WPOPTIMIZEBYXTRAFFIC_CACHE_PATH, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			self::chmod(WPOPTIMIZEBYXTRAFFIC_CACHE_PATH,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
			
			
			
			self::createFolder(WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			self::chmod(WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN,WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP,WPOPTIMIZEBYXTRAFFIC_CHMOD);
			
			self::$defaultParams['optimize_images']['number_images_processed_request'] = 0;
			
			self::$defaultParams['urlProtocol'] = 'http:';
			if(self::is_ssl()) {
				self::$defaultParams['urlProtocol'] = 'https:';
			}
			
			self::$defaultParams['urlFullRequest'] = self::$defaultParams['urlProtocol'].'//'.$_SERVER['HTTP_HOST'];
			if(isset($_SERVER['REQUEST_URI'])) {
				self::$defaultParams['urlFullRequest'] .= $_SERVER['REQUEST_URI'];
			}
			
			self::$defaultParams['parseedUrlFullRequest'] = self::parseUrl(self::$defaultParams['urlFullRequest']);
			
			self::$defaultParams['fullDomainName'] = '';
			$parseUrl = parse_url(self::$defaultParams['urlFullRequest']);
			if(isset($parseUrl['host']) && $parseUrl['host']) {
				self::$defaultParams['fullDomainName'] = $parseUrl['host'];
			}
			
			self::$defaultParams['serverSoftware'] = '';
			if(isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE']) {
				$valueTemp = $_SERVER['SERVER_SOFTWARE'];
				$valueTemp = trim($valueTemp);
				if(preg_match('#nginx#i',$valueTemp)) {
					self::$defaultParams['serverSoftware'] = 'nginx';
				} else if(preg_match('#apache#i',$valueTemp)) {
					self::$defaultParams['serverSoftware'] = 'apache';
				}
				
			}
			
		}
	}
	
	
	
	public static function strtolower($input_text,$input_encoding = 'UTF-8') 
	{
		return mb_convert_case($input_text, MB_CASE_LOWER, $input_encoding);
		
	}
	
	
	public static function strtoupper($input_text,$input_encoding = 'UTF-8') 
	{
		return mb_convert_case($input_text, MB_CASE_UPPER, $input_encoding);
		
	}
	
	
	
	public static function gmdate_gmt($input_timestamp)
	{
		$input_timestamp = (int)$input_timestamp;
		$formatStringGMDate = 'D, d M Y H:i:s';
		$resultData = gmdate($formatStringGMDate, $input_timestamp).' GMT';
		return $resultData;
	}
	
	
	
	public static function randomHash() 
	{

		$rsData = mt_rand().'_'.time().'_'.mt_rand(); 

		$rsData = trim($rsData);

		$rsData = strtolower(md5($rsData));

		return $rsData;

	}
	
	public static function rgb2hex($rgb) 
	{
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex; // returns the hex value including the number sign (#)
		
	}

	public static function hex2rgb($hex) 
	{
		$hex = str_replace("#", "", $hex); 

		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		
		$rgb = array($r, $g, $b);
		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}

	
	public static function mb_substr($input_str, $input_start, $input_length = 0, $input_encoding = 'UTF-8')
	{
		return mb_substr($input_str, $input_start, $input_length, $input_encoding);
	}
	
	public static function mb_strlen($input_str, $input_encoding = 'UTF-8')
	{
		return mb_strlen($input_str, $input_encoding);
	}
	
	public static function countWords($input_str) 
	{
		$input_str = trim($input_str);
		$input_str = explode(' ',$input_str);
		return count($input_str);
	}
	
	
	public static function minifyHtml($input_data)
	{
		$input_data = (string)$input_data;
		$input_data = trim($input_data);
		
		/*
		$patterns = array(
			1 => "/(\<\!\-\-)(.*?)(\-\-\>)/s", // Remove all comments
			2 => '/([\t])+/',
			3 => '/( ){2,}/',
			4 => '/[\r\n\t ]+(<)/s', //before
			5 => '/(>)([\r\n\t ])+/s', //after
			6 => '/[\r\n\t ]+(\<\/)/s', //before
			7 => '/(\>)[\r\n\t ]+(\<)/s',
		);
		
		$replacements = array(
			1 => ' ',
			2 => ' ',
			3 => ' ',
			4 => ' $1',
			5 => '$1 ',
			6 => ' $1',
			7 => '$1 $2', 
		);
		
		$input_data = preg_replace($patterns, $replacements, $input_data);
		//*/
		
		
		
		
		
		//*
		
		$patternsEscaped1 = array();
		
		
		$rsOne = '';
		
		$rsOne = self::escapeHtmlTagsAndContents($input_data,'pre;code');
		$input_data = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped1 = array_merge($patternsEscaped1, $rsOne['patterns']);
		}
		
		$rsOne = '';
		
		$input_data = PepVN_Minify_HTML::minify($input_data, array(
			'jsCleanComments' => true
			,'xhtml' => true
		));
		
		if(!self::isEmptyArray($patternsEscaped1)) {
			$input_data = str_replace(array_values($patternsEscaped1),array_keys($patternsEscaped1),$input_data); 
		}
		
		//*/
		
		$input_data = trim($input_data); 
		
		return $input_data;
	}
	
	
	
	public static function minifyJavascript($input_data)
	{
		
		$input_data = (array)$input_data;
		$input_data = implode(PHP_EOL,$input_data);
		$input_data = (string)$input_data;
		$input_data = trim($input_data);
		
		
		/*
		$rsOne = self::escapeByPattern($input_data,array(
			'pattern' => '#(\'|\").*?\1#is'
			,'target_patterns' => array(
				0
			)
			,'wrap_target_patterns' => '______'
		));
		$input_data = $rsOne['content']; $rsOne['content'] = '';
		
		
		
		$patternsFindAndReplace = array(
			'#\/\*(.*?)\*\/#is' => ' ' // Remove all comments  
			,'#[\t ]+#i' => ' '
			,'#([\r\n])+#i' => PHP_EOL
		);
		
		$input_data = preg_replace(array_keys($patternsFindAndReplace), array_values($patternsFindAndReplace), $input_data);
		
		
		
		if(!self::isEmptyArray($rsOne['patterns'])) {
			$input_data = str_replace(array_values($rsOne['patterns']),array_keys($rsOne['patterns']),$input_data);
		}
		*/
		
		$rsOne = self::escapeByPattern($input_data,array(
			'pattern' => '#[\+\-]+[ \t\s]+[\+\-]+#is'
			,'target_patterns' => array(
				0
			)
			,'wrap_target_patterns' => '+'
		));
		
		
		$pepVN_JavaScriptPacker = false;$pepVN_JavaScriptPacker = new PepVN_JavaScriptPacker($rsOne['content'], 'Normal', true, false);
		$rsOne['content'] = $pepVN_JavaScriptPacker->pack();unset($pepVN_JavaScriptPacker);$pepVN_JavaScriptPacker=false;
		
		
		if(!self::isEmptyArray($rsOne['patterns'])) {
			$rsOne['content'] = str_replace(array_values($rsOne['patterns']),array_keys($rsOne['patterns']),$rsOne['content']);
		}
		
		$input_data = $rsOne['content']; $rsOne = false;
		
		
		$input_data = trim($input_data);
		
		return $input_data;
	}
	
	
	
	public static function minifyCss($input_data)
	{
		$input_data = (string)$input_data;
		$input_data = trim($input_data);
		
		/*
		$input_data = self::removeCommentInCss($input_data);
		
		$patterns = array(
			'#\s+#is' => ' ' // Compress all spaces into single space
			//,'#/\*[^*]*\*+([^/][^*]*\*+)* /#' => ''
			//,'#(\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>)#s' => ' '// Remove all comments
			,'#(\/\*)(.*?)(\*\/)#is' => ' '// Remove all comments
			//,'#(\s+)?([,{};:>\+]+)(\s+)?#s' => '$2' // Remove un-needed spaces around special characters
			//,'#url\([\'\"](.*?)[\'\"]\)#s' => 'url($1)'// Remove quotes from urls
			//,'#;{2,}#' => ';' // Remove unecessary semi-colons
			
			,'#\s+([\,\{\}\;\:]+)#is' => ' $1' // Remove un-needed spaces around special characters
			,'#([\,\{\}\;\:]+)\s+#is' => '$1 ' // Remove un-needed spaces around special characters
			
			//,'#^\s+#m' => ' '
			,'#url\((\s+)([^\)]+)(\s+)\)#' => 'url($2)'
		);
		
		$input_data = preg_replace(array_keys($patterns), array_values($patterns), $input_data);
		*/
		
		
		$pepVN_CSSmin = new PepVN_CSSmin();
		$input_data = $pepVN_CSSmin->run($input_data,FALSE);
		
		
		return $input_data;
		
	}
	
	
	
	public static function removeCommentInCss($input_data)
	{
		$patterns = array(
			'#(\/\*)(.*?)(\*\/)#is' => ' '// Remove all comments
		);
		
		$input_data = preg_replace(array_keys($patterns), array_values($patterns), $input_data);
		
		return $input_data;
		
	}
	
	
	
	public static function hashMd5($input_data)
	{
		$input_data = serialize($input_data);
		$input_data = (string)$input_data;
		$input_data = preg_replace('#\s+#is','',$input_data);
		$input_data = md5($input_data);
		return $input_data;
	}
	
	
	
	
	public static function mcrc32($str)
	{
        $str = dechex(crc32($str));

        $str = preg_replace('/[^a-z0-9]+/i','r',$str); 

        $str = (string)$str;
        $str = trim($str);
        $str = 'm'.$str;

		return $str;
	}
	
	
	public static function mhash($str,$length = 8)
	{
		
		
		$length1 = $length * 2;
		
		$resultData = md5($str);
		
		while(strlen($resultData) < $length1) {
			
			$resultData .= md5($resultData);
			
		}
		
		$resultData .= md5($resultData);
		$resultData .= md5($resultData);
		
		
		$totalChars = strlen($resultData);
		$totalChars = (int)$totalChars;
		$stepNum = ceil($totalChars / $length);
		
		
		$valueTemp = str_split($resultData,1);
		$valueTemp_Count = count($valueTemp);
		$valueTemp_Count = (int)$valueTemp_Count;
		
		$resultData = '';
		
		for($i=0;$i<$valueTemp_Count;$i++) {
			if(0 === ($i % $stepNum)) {
				
				$resultData .= $valueTemp[$i];
				if(strlen($resultData) >= $length) {
					break;
				}
			}
		}
		
		
		return $resultData; 
		
	}
	

	
	public static function createKey($input_data)
	{
		//don't change here, it make id for data
		$input_data = serialize($input_data); 
		$input_data = (string)$input_data;
		$input_data = preg_replace('#\s+#is','',$input_data); 
		$input_data = md5($input_data);
		return $input_data;
	}
	
	
	public static function toTitleUrl($input_string)
	{
		$input_string = self::removeVietnameseSign($input_string);
		$input_string = self::strtolower($input_string);
		$input_string = preg_replace('#[^a-z0-9]+#is',' ',$input_string);
		$input_string = self::reduceSpace($input_string);
		$input_string = preg_replace('#\s#is','-',$input_string);
		return $input_string;
	}
	
		
	/**
	 * Determine if SSL is used.
	 *
	 * @return bool True if SSL, false if not used.
	 */
	public static function is_ssl() 
	{
		if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) )
				return true;
			if ( '1' == $_SERVER['HTTPS'] )
				return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}
	
	
	
	
	public static function removeVietnameseSign($input_text)
	{
		
		$self_Char_Vietnamese = self::$defaultParams['char']['vietnamese'];
		return str_replace(array_keys($self_Char_Vietnamese), array_values($self_Char_Vietnamese), $input_text);
		
	}//End Function

	
	public static function explode($input_delimiter, $input_data) 
	{
		$input_delimiter = (string)$input_delimiter;
		
		if(preg_match('#[\,\;]+#is',$input_delimiter)) {
			
			$input_delimiter = ';';
			
			$input_data = (array)$input_data;
			$input_data = implode(';',$input_data);
			
			$input_data = preg_replace('#[\;\,]+#is',';',$input_data);
		
		}
		
		$input_data = (string)$input_data;
		
		$input_data = explode($input_delimiter,$input_data);
		
		return $input_data;
		
	}
	
	
	public static function splitAndCleanKeywords($input_data) 
	{
		$resultData = array();
		$input_data = (array)$input_data;
		$input_data = implode(';',$input_data);
		$input_data = preg_replace('#[;,]+#i',';',$input_data);
		$input_data = preg_replace('#\s+#i',' ',$input_data);
		$input_data = explode(';',$input_data);
		foreach($input_data as $valueOne) {
			$valueOne = trim($valueOne);
			if($valueOne) {
				$resultData[] = $valueOne;
			}
		}
		
		return $resultData;
		
	}
	
	
	
	public static function appendTextToTagHeadOfHtml($input_text,$input_html) 
	{
		return preg_replace('#(\s*?</head>\s*?<body[^>]*?>)#is', $input_text.' \1',$input_html); 
	}
	
	public static function appendTextToTagBodyOfHtml($input_text,$input_html) 
	{
		return preg_replace('#(\s*?</body>\s*?</html>\s*?)$#is', $input_text.' \1',$input_html);
	}
	
	
	public static function reduceSpace($input_data) 
	{
		$input_data = preg_replace('#[ \t]+#i',' ',$input_data);
		$input_data = trim($input_data);
		return $input_data;
	}
	
	public static function cleanKeyword($input_data) 
	{
		$input_data = preg_replace('#[\'\"]+#i',' ',$input_data);
		$input_data = self::reduceSpace($input_data);
		return $input_data;
	}
	
	public static function cleanPregPatternsArray($input_data) 
	{
		$input_data = (array)$input_data;
		$input_data = implode(';',$input_data);
		$input_data = preg_replace('#[\,\;]+#',';',$input_data);
		$input_data = explode(';',$input_data);
		$input_data = self::cleanArray($input_data);
		foreach($input_data as $keyOne => $valueOne) {
			$input_data[$keyOne] = preg_quote($valueOne, '#');
		}
		
		return $input_data;
		
	}
	
	
	
	public static function removeProtocolUrl($input_url) 
	{
		$input_url = trim($input_url);
		
		$input_url = preg_replace('#^https?://#i','',$input_url);
		$input_url = preg_replace('#^:?//#i','',$input_url);
		
		return $input_url;
		
	}
	
	
	
	public static function is_writable($input_path) 
	{
		$resultData = false;
		
		if($input_path) {
			if(file_exists($input_path)) {
				if(is_writable($input_path)) {
					$resultData = true;
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	public static function is_readable($input_path) 
	{
		$resultData = false;
		
		if($input_path) {
			if(file_exists($input_path)) {
				if(is_readable($input_path)) {
					$resultData = true;
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	//base on http://detectmobilebrowsers.com
	public static function isMobileDevice($useragent='') 
	{
		$resultData = false;
		
		if(!$useragent) {
			if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
			}
		}
		
		$useragent = trim($useragent);
		
		if($useragent) {
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
				$resultData = true;
			}
		}
		
		return $resultData;
	}
	
	
	
	
	static function parseUrl($url)
	{
				
		/*** 
			get the url parts 
			Exam: http://username:password@hostname/path?arg=value#anchor
			
			[scheme] => http
			[host] => hostname
			[user] => username
			[pass] => password
			[path] => /path
			[query] => arg=value
			[fragment] => anchor
			'domain'
			'root'
			'url_no_parameters'
			'parameters'
		***/
		
		$url = trim($url);
		if(!$url) {
			return false;
		}
		
		$parts = parse_url($url);
		
		$domain = (isset($parts['host']) ? $parts['host'] : '');
		if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			$parts['domain'] = $regs['domain'];
		}
		
		if(isset($parts['scheme']) && isset($parts['host'])) {
			$parts['root'] = $parts['scheme'].'://'.$parts['host'];
		}
		
		if(isset($parts['root']) && !isset($parts['path'])) {
			$valueTemp = $url;
			$valueTemp = explode('?', $valueTemp, 2);
			$parts['path'] = str_replace($parts['root'], '', trim($valueTemp[0]));
		}
		
		if(isset($parts['root']) && isset($parts['path'])) {
			$parts['url_no_parameters'] = $parts['root'].$parts['path']; 
		}
		
		if(isset($parts['query'])) {
			parse_str($parts['query'], $parseStr);
			$parts['parameters'] = $parseStr;
		}
				
		/*** return the host domain ***/
		//return $parts['scheme'].'://'.$parts['host'];
		return $parts;
		
	}
	
	
	public static function addParamStringToUrl($input_url, $input_param_string)
    {
		$resultData = $input_url;
		
		if($input_url && $input_param_string) {
			
			$rsParseUrl = self::parseUrl($input_url);
			
			if(is_array($rsParseUrl)) {
				
				if(isset($rsParseUrl['parameters']) && is_array($rsParseUrl['parameters'])) {
					$input_url .= '&';
				} else {
					if(false === stripos($input_url,'?')) {
						$input_url .= '?';
					}
				}
				
				$input_url .= $input_param_string;
				
				$resultData = $input_url;
			}
		}
		
		return $resultData;
	}
	
	
	
	public static function addParamsToUrl($input_url, $input_params)
    {
		$resultData = $input_url;
		
		if($input_url && $input_params) {
			
			$rsParseUrl = self::parseUrl($input_url);
			
			if(is_array($rsParseUrl)) {
				$params = array();
				
				if(isset($rsParseUrl['parameters']) && is_array($rsParseUrl['parameters'])) {
					$params = array_merge($params, $rsParseUrl['parameters']);
				}
				
				if(is_array($input_params)) {
					$params = array_merge($params, $input_params);
				}
				
				$resultData = $rsParseUrl['url_no_parameters'].'?'.http_build_query($params);
				
			}
		}
		
		return $resultData;
	}
	
	
	
	public static function isEmptyArray($input_data) 
	{
		$resultData = true;
		
		if($input_data) {
			if(is_array($input_data)) {
				if(count($input_data)>0) {
					$resultData = false;
				}
			}
		}
		
		return $resultData;
	}
	
	
	public static function cleanArray($input_data) 
	{
		$resultData = array();
		
		$input_data = (array)$input_data;
		foreach($input_data as $value1) {
			$value1 = trim($value1);
			if(strlen($value1)>0) {
				$resultData[] = $value1;
			}
		}
		
		$resultData = array_unique($resultData);
		
		return $resultData;
		
	}
	
	
	
	public static function escapeByPattern($input_content, $input_options) 
	{

		$input_content = (string)$input_content;
		
		
		if(!isset($input_options['pattern'])) {
			$input_options['pattern'] = '';
		}
		
		if(!isset($input_options['target_patterns'])) {
			$input_options['target_patterns'] = array();
		}
		$input_options['target_patterns'] = (array)$input_options['target_patterns'];
		
		if(!isset($input_options['wrap_target_patterns'])) {
			$input_options['wrap_target_patterns'] = '';
		}

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);
		
		
		$checkStatus1 = false;
		if($input_options['pattern']) {
			if(!self::isEmptyArray($input_options['target_patterns'])) { 
				$checkStatus1 = true;
			}
			
		}
		
		if(!$checkStatus1) {
			return $resultData;
		}
		

		$patternsEscape1 = array();
		
		$matched1 = false;
		preg_match_all($input_options['pattern'],$input_content,$matched1);
		foreach($input_options['target_patterns'] as $keyOne => $valueOne) {
			if(isset($matched1[$valueOne]) && $matched1[$valueOne]) {
				if(count($matched1[$valueOne])>0) {
					foreach($matched1[$valueOne] as $keyTwo => $valueTwo) {
						$search1 = $valueTwo;
						$replace1 = md5($valueTwo);
						$replace1 = str_split($replace1);
						$replace1 = implode('_',$replace1);

						$patternsEscape1[$search1] = $input_options['wrap_target_patterns'].'______'.$replace1.'______'.$input_options['wrap_target_patterns'];
						
					}
				}
			}
		}
		
		
		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}
	
	
	
	
	public static function escapeHtmlTags($input_content) 
	{

		$input_content = (string)$input_content;

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);

		$patternsEscape1 = array();

		$matched1 = false;
		preg_match_all('/<a(\s+[^><]*?)?>.*?<\/a>/is',$input_content,$matched1);
		if(isset($matched1[0]) && $matched1[0]) {
			if(count($matched1[0])>0) {
				foreach($matched1[0] as $key1 => $value1) {
					$search1 = $value1;
					$replace1 = md5($value1);
					$replace1 = str_split($replace1);
					$replace1 = implode('_',$replace1);

					$patternsEscape1[$search1] = '______'.$replace1.'______';
				}
			}
		}
		

		$matched1 = false;
		preg_match_all('#<[^><]+>#i',$input_content,$matched1);
		if(isset($matched1[0]) && $matched1[0]) {
			if(count($matched1[0])>0) {
				foreach($matched1[0] as $key1 => $value1) {
					$search1 = $value1;
					$replace1 = md5($value1);
					$replace1 = str_split($replace1);
					$replace1 = implode('_',$replace1);

					$patternsEscape1[$search1] = '______'.$replace1.'______';
				}
			}
		}

		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}


	
	public static function escapeHtmlTagsAndContents($input_content, $input_tags) 
	{

		$input_content = (string)$input_content;
		
		$input_tags = (array)$input_tags;
		$input_tags = implode(';',$input_tags);
		$input_tags = preg_replace('#[\,\;]+#',';',$input_tags);
		$input_tags = explode(';',$input_tags);
		$input_tags = self::cleanArray($input_tags);
		

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);

		$patternsEscape1 = array();
		
		if(count($input_tags)>0) {
			foreach($input_tags as $tagName) {
				$tagName = preg_replace('#[^a-z0-9]+#','',$tagName);
				$tagName = trim($tagName);
				if($tagName) {
					$matched1 = false;
					preg_match_all('#<('.self::preg_quote($tagName).')(\s+[^><]*?)?>(.*?</\1>)?#is',$input_content,$matched1);
					if(isset($matched1[0]) && $matched1[0]) {
						if(count($matched1[0])>0) {
							foreach($matched1[0] as $key1 => $value1) {
								$search1 = $value1;
								$replace1 = md5($value1);
								$replace1 = str_split($replace1);
								$replace1 = implode('_',$replace1);

								$patternsEscape1[$search1] = '______'.$replace1.'______';
							}
						}
					}
				}
			}
		}
		
		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}
	
	
	
	
	public static function escapeSpecialElementsInHtmlPage($input_content) 
	{

		$input_content = (string)$input_content;

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);

		$patternsEscape1 = array();

		$matched1 = false;
		
		preg_match_all('/<\!--\s*\[\s*if[^>]+>(.*?)<\!\s*\[\s*endif\s*\]\s*-->/si', $input_content, $matched1);
		if(isset($matched1[0]) && $matched1[0]) {
			if(count($matched1[0])>0) {
				foreach($matched1[0] as $key1 => $value1) {
					$search1 = $value1;
					$replace1 = md5($value1);
					$replace1 = str_split($replace1);
					$replace1 = implode('_',$replace1);

					$patternsEscape1[$search1] = '______'.$replace1.'______';
				}
			}
		}
		
		
		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}


	
	
	
	public static function rrmdir($dir) 
	{
		if($dir) {
			if(file_exists($dir)) {
				if (is_dir($dir)) {
					$objects = scandir($dir);
					foreach ($objects as $object) {
						if ($object != "." && $object != "..") {
							if (filetype($dir."/".$object) == "dir") {
								self::rrmdir($dir."/".$object); 
							} else {
								unlink   ($dir."/".$object); 
							}
						}
					}
					reset($objects);
					rmdir($dir);
				}
			}
		}
		
	}

	
	
	
	public static function mergeArrays($input_parameters)
	{
		$merged = false;
		
		if(is_array($input_parameters)) {
		
			$merged = array_shift($input_parameters); // using 1st array as base
			
			foreach($input_parameters as $array) {
				foreach ($array as $key => $value) {
					
					if(isset($merged[$key]) && is_array($value) && is_array($merged[$key])) {
						
						$merged[$key] = self::mergeArrays(array($merged[$key], $value));
						
					} else {
						if(preg_match('/^[0-9]+$/i', $key)) {
							$merged[] = $value;
						} else {
							$merged[$key] = $value;
						}
					}
				}
			}
		}
		
		return $merged;
	}//End Function
	
	
	
	
	public static function base64Encode($input_data)
	{
				
		$input_data = base64_encode($input_data);
		$input_data = str_replace(array('+','/','='), array('-','_','.'), $input_data);
		
        return $input_data;
	}
	
	public static function base64Decode($input_data)
	{
	
		$input_data = str_replace(array('-','_','.'), array('+','/','='), $input_data);
		$input_data = base64_decode($input_data);
        return $input_data;
	}
	
	
	public static function isUrl($input_data)
	{
		$resultData = false;
		
		if(preg_match('#^https?://.+$#i',$input_data,$matched1)) {
			$resultData = true;
		}
		
		return $resultData; 
	}
	
	
	
	public static function isSameHost($input_link1,$input_link2)
	{
		$input_link1 = 'http://'.self::removeProtocolUrl($input_link1);
		$input_link2 = 'http://'.self::removeProtocolUrl($input_link2);
		$parseUrl1 = parse_url($input_link1);
		if(isset($parseUrl1['host']) && $parseUrl1['host']) {
			$parseUrl2 = parse_url($input_link2);
			if(isset($parseUrl2['host']) && $parseUrl2['host']) {
				$parseUrl1['host'] = self::strtolower(trim($parseUrl1['host']));
				$parseUrl2['host'] = self::strtolower(trim($parseUrl2['host']));
				if($parseUrl2['host'] === $parseUrl1['host']) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	
	public static function isImg($input_data)
	{
		$resultData = false;
		
		if(preg_match('#^.+\.(gif|jpeg|jpg|png)$#i',$input_data,$matchedTemp)) {
			$resultData = true;
		}
		
		return $resultData;
	}
	
	
	
	public static function encodeVar($input_data)
	{
		$input_data = json_encode($input_data);
		
		$input_data = utf8_encode($input_data);
		
		$input_data = self::base64Encode($input_data);
		
		return $input_data;
		
	}//End Function
	
	public static function decodeVar($input_data)
	{
		
		$input_data = self::base64Decode($input_data);
		
		$input_data = utf8_decode($input_data);
		
		$input_data = json_decode($input_data, true, 99999);
		
		return $input_data;
		
	}//End Function
	
	
	
	
	public static function getDataSent()
	{
		$resultData = false;
		
		$keyDataRequest = WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST;
		
		if(isset($_GET[$keyDataRequest]) && $_GET[$keyDataRequest]) {
			$rsOne = self::decodeVar($_GET[$keyDataRequest]);
			if($rsOne && isset($rsOne['localTimeSent']) && $rsOne['localTimeSent']) {
				if(!$resultData) {
					$resultData = array();
				}
				$resultData = self::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
			$rsOne = false;
		}
		
		if(isset($_POST[$keyDataRequest]) && $_POST[$keyDataRequest]) {
			$rsOne = self::decodeVar($_POST[$keyDataRequest]);
			
			if($rsOne && isset($rsOne['localTimeSent']) && $rsOne['localTimeSent']) {
				if(!$resultData) {
					$resultData = array();
				}
				$resultData = self::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
			$rsOne = false;
		}
		
		return $resultData;
		
	}
	
	
	
	public static function encodeResponseData($input_data)
	{
		
		$keyDataRequest = WPOPTIMIZEBYXTRAFFIC_KEY_DATA_REQUEST;
		
		$callback = false;

		$resultData = array();
		
		$resultData[$keyDataRequest] = self::encodeVar($input_data);

		if(isset($_GET['jsoncallback'])) {
			$callback = trim($_GET['jsoncallback']);
		} elseif(isset($_GET['callback'])) {
			$callback = trim($_GET['callback']);
		}
		
		if($callback) {
			$resultData = $callback.'('.json_encode($resultData).')';
		} else {
			$resultData = json_encode($resultData);
		}

		return $resultData;
	}//End Function
	
	
	public static function replaceSpecialChar($input_text, $input_replace_char = ' ')
	{
		return preg_replace('#['.preg_quote('`~!@#$%^&*()-_=+{}[]\\\|;:\'",.<>/?','#').']+#i',$input_replace_char,$input_text);
	}
	
	public static function fixPath($input_path)
	{
		$input_path = preg_replace('/(\\\|\/)+/i',DIRECTORY_SEPARATOR,$input_path);
		$input_path = preg_replace('#/+$#i','',$input_path);
		$input_path = preg_replace('#^/+#i',DIRECTORY_SEPARATOR,$input_path);
		return $input_path;
	}
	
	
	public static function createFolder($input_path, $input_chmod = '')
	{
		$resultData = '';
		
		
		$chmod = '';
		if($input_chmod) {
			$chmod = $input_chmod;
		}
		
		if(!$chmod) {
			$chmod = WPOPTIMIZEBYXTRAFFIC_CHMOD;
		}
		
		$pathRoot = '';
		if(defined('ABSPATH')) {
			$pathRoot = ABSPATH;
		}
		if($pathRoot) {
			$pathRoot = preg_replace('#/+$#i','',$pathRoot);
			$pathRoot .= DIRECTORY_SEPARATOR;
		}
		
		if($pathRoot) {
			$input_path = preg_replace('#^'.self::preg_quote($pathRoot).'#','',$input_path,1);
		}
		
		$input_path = self::fixPath($input_path);
		
		$pathTemp1 = $pathRoot.$input_path;
		
		if($pathTemp1 && file_exists($pathTemp1)) {
			$resultData = $pathTemp1;
			return $resultData;
		}
		
		
		$pathInfo = pathinfo($input_path);
		
		
		$arrayPath = explode(DIRECTORY_SEPARATOR, $input_path);
		if(isset($pathInfo['extension'])) {
			array_pop($arrayPath);
		}
		
		$folderPath = $pathRoot;
		$folderPath = preg_replace('#/+$#i','',$folderPath);
		
		foreach($arrayPath as $path1) {
			$folderPath .= DIRECTORY_SEPARATOR . $path1;
			$pathTemp1 = $folderPath;
			
			if($pathTemp1 && file_exists($pathTemp1)) {
			} else {
				
				@mkdir($folderPath);
				$pathTemp1 = $folderPath;
				
				if($pathTemp1 && file_exists($pathTemp1)) {
				} else {
					return $resultData;
				}
			}
		}
		
		if($folderPath) {
			
			if($folderPath && file_exists($folderPath)) {
				$resultData = $folderPath . DIRECTORY_SEPARATOR; 
				if(isset($pathInfo['extension'])) {
					if(isset($pathInfo['basename'])) {
						$resultData .= $pathInfo['basename'];
					}
					
				}
			}
		}
		
		return $resultData;
		
	}//End Function
	
	
	
	public static function checkChmod($input_path, $input_chmod)
	{
		$resultData = false;
		
		if($input_path && file_exists($input_path)) {
			$pathPerms = substr(sprintf('%o', fileperms($input_path)), -4);
			
			$pathPerms = (int)$pathPerms;
			
			$input_chmod = (int)$input_chmod;
			
			if($pathPerms == $input_chmod) {
				$resultData = true;
			}
		}
		
		return $resultData;
	}
	
	public static function isAllAllowReadAndWriteIfExists($input_path,$input_from_path='')
	{
		$resultData = true;
		
		$input_path = self::fixPath($input_path);
		$input_from_path = self::fixPath($input_from_path);
		
		$pathNeedChmod = $input_from_path;
		
		if($input_from_path) {
			$input_path = preg_replace('#^'.preg_quote($input_from_path,'#').'#','',$input_path);
			$input_path = self::fixPath($input_path);
		}
		
		$arrayPath = explode(DIRECTORY_SEPARATOR, $input_path);
		
		foreach($arrayPath as $path1) {
			$pathNeedChmod .= DIRECTORY_SEPARATOR . $path1;
			$pathTemp1 = $pathNeedChmod;
			
			if($pathTemp1 && file_exists($pathTemp1)) {
				if(is_readable($input_path) && is_writable($input_path)) {
					
				} else {
					$resultData = false;
				}
			}
			
			if(!$resultData) {
				break;
			}
		}
		
		
		return $resultData;
	}
	
	public static function getFolderPath($input_path)
	{
		$resultData = '';
		
		$input_path = self::fixPath($input_path);
		
		$pathInfo = pathinfo($input_path);
		
		
		$arrayPath = explode(DIRECTORY_SEPARATOR, $input_path);
		if(isset($pathInfo['extension'])) {
			array_pop($arrayPath);
		}
		
		$resultData = implode(DIRECTORY_SEPARATOR,$arrayPath);
		
		return $resultData;
		
	}
	
	public static function isAllowReadAndWrite($input_path)
	{
		$resultData = false;
		
		if($input_path) {
			if(file_exists($input_path)) {
				if(is_readable($input_path)) {
					if(is_writable($input_path)) {
						$resultData = true;
					}
				}
			}
		}
		return $resultData;
	}
	
	public static function chmod($input_path, $input_from_path, $input_chmod = '')
	{
		return true;
		
		$resultData = false;
		
		$chmod = '';
		if($input_chmod) {
			$chmod = $input_chmod;
		}
		
		if(!$chmod) {
			$chmod = WPOPTIMIZEBYXTRAFFIC_CHMOD;
		}
		
		$input_path = self::fixPath($input_path);
		$input_from_path = self::fixPath($input_from_path);
		
		$pathInfo = pathinfo($input_path);
		
		$pathNeedChmod = '';
		
		if($input_from_path) {
			$input_path = preg_replace('#^'.preg_quote($input_from_path,'#').'#','',$input_path);
			$input_path = self::fixPath($input_path);
		}
		
		$arrayPath = explode(DIRECTORY_SEPARATOR, $input_path);
		
		foreach($arrayPath as $path1) {
			$pathNeedChmod .= DIRECTORY_SEPARATOR . $path1;
			$pathTemp1 = $pathNeedChmod;
			
			if($pathTemp1 && file_exists($pathTemp1)) {
				if(!self::checkChmod($pathTemp1,$chmod)) {
					@chmod($pathTemp1, $chmod);
					
				}
			}
		}
		
		
		
		if($pathNeedChmod && file_exists($pathNeedChmod)) {
			if(self::checkChmod($pathNeedChmod,$chmod)) {
				
				$resultData = true;
			}
		}
		
		
		return $resultData;
		
	}
	
	
	
	public static function getRequestMethod()
	{
		$resultData = 'get';
		
		if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']) {
			
			$resultData = $_SERVER['REQUEST_METHOD'];
			$resultData = trim($resultData);
			$resultData = strtolower($resultData);
			
		}
		
		return $resultData;
	}
	
	
	public static function preg_quote($input_text)
	{
		return preg_quote($input_text,'#');
	}
	
	public static function decodeText($input_text)
	{
		$input_text = rawurldecode($input_text);
		$input_text = html_entity_decode($input_text, ENT_QUOTES, 'UTF-8');
		return $input_text;
	}
	
	
	
	static function analysisKeyword_RemovePunctuations($input_text, $input_excepts = false)
	{
	
		$punctuations = array(
			',', ')', '(',"'", '"',
			'<', '>', '!', '?',
			'[', ']', '+', '=', '#', '$', ';'
			,':','-','.','–','-'
			,'$', '&quot;', '&copy;', '&gt;', '&lt;', 
			'&nbsp;', '&trade;', '&reg;', ';', 
			chr(10), chr(13), chr(9)
		);
		
		if($input_excepts) {
			$input_excepts = (array)$input_excepts;
			$punctuations1 = array();
			foreach($punctuations as $key1 => $value1) {
				if(!in_array($value1,$input_excepts)) {
					$punctuations1[] = $value1;
				}
			}
			$punctuations = $punctuations1;$punctuations1 = false;
		}
		
		
		$punctuations = array_unique($punctuations);
		
		$input_text = str_replace($punctuations, ' ', $input_text);
		
		return $input_text;
		
	}
	
	
	static function analysisKeyword_PrepareContents($input_parameters)
	{
		
		if(isset($input_parameters['contents'])) {
			$input_parameters['contents'] = self::decodeText($input_parameters['contents']);
			$input_parameters['contents'] = strip_tags($input_parameters['contents']);
			
			$input_parameters['contents'] = self::strtolower($input_parameters['contents']);
			
			$input_parameters['contents'] = self::analysisKeyword_RemovePunctuations($input_parameters['contents']);
			
			$input_parameters['contents'] = self::reduceSpace($input_parameters['contents']);
			
			return $input_parameters;
		}
		
		return false;
	}
	
	
	
	public static function analysisKeyword_OccureFilter($array_count_values, $min_occur)
	{
		$min_occur_sub = $min_occur - 1;
		
		$occur_filtered = false;
		
		foreach($array_count_values as $word => $occured) {
			if($occured > $min_occur_sub) {
				$occur_filtered[$word] = $occured;
			}
		}

		return $occur_filtered;
	}
	
	
	
	public static function analysisKeyword_GetKeywordsFromText($input_parameters)
	{
		//ini_set('max_execution_time', 10);
		$resultData = array();
		$resultData['data'] = false;
		
		$inputExplodedContents = false;
		$resultAnalysis = false;
		$common = array();
		
		$checkStatusOne = false;  
		
		$keyCache1 = self::createKey(array(
			__METHOD__
			,$input_parameters
		));
		
		$resultData = self::$cacheObject->get_cache($keyCache1);
		
		if(!$resultData) {
			$resultData = array();
			$resultData['data'] = false;
		
			if(isset($input_parameters['contents'])) {
				$input_parameters['contents'] = (array)$input_parameters['contents'];
				$input_parameters['contents'] = implode(' ',$input_parameters['contents']);
				$input_parameters['contents'] = trim($input_parameters['contents']);
				if($input_parameters['contents']) {
					$inputExplodedContents = self::analysisKeyword_PrepareContents($input_parameters);
					if(isset($inputExplodedContents['contents']) && $inputExplodedContents['contents']) {
						$inputExplodedContents = explode(' ', $inputExplodedContents['contents']);
						unset($input_parameters['contents']);  
						$checkStatusOne = true;
					}
				}
			}
			
			if($checkStatusOne) {
				$inputMinWord = 0;
				$inputMaxWord = 0;
				$inputMinOccur = 0;
				$inputMinCharEachWord = 0;
				
				if(isset($input_parameters['min_word']) && $input_parameters['min_word']) {
					$inputMinWord =  $input_parameters['min_word'];
				}
				
				if(isset($input_parameters['max_word']) && $input_parameters['max_word']) {
					$inputMaxWord =  $input_parameters['max_word'];
				}
				
				if(isset($input_parameters['min_occur']) && $input_parameters['min_occur']) {
					$inputMinOccur =  $input_parameters['min_occur'];
				}
				
				if(isset($input_parameters['min_char_each_word']) && $input_parameters['min_char_each_word']) {
					$inputMinCharEachWord =  $input_parameters['min_char_each_word'];
				}
				
				$inputMinWord = (int)$inputMinWord;
				$inputMaxWord = (int)$inputMaxWord;
				$inputMinOccur = (int)$inputMinOccur;
				$inputMinCharEachWord = (int)$inputMinCharEachWord;
				
				if(!$inputMinWord) {
					$inputMinWord = 1;//min word each keyword
				}
				if(!$inputMaxWord) {
					$inputMaxWord = 3;//max word each keyword
				}
				if(!$inputMinOccur) {
					$inputMinOccur = 2;//number appear
				}
				if(!$inputMinCharEachWord) {
					$inputMinCharEachWord = 3;// min char each word
				}
				
				if($inputMinWord>$inputMaxWord) {
					$inputMinWord = $inputMaxWord;
				}
			}
			
			if($checkStatusOne) { 
				$countExplodedContents = count($inputExplodedContents);
				for($iOne = 0; $iOne < $countExplodedContents; ++$iOne) {
					
					for($iTwo = $inputMinWord; $iTwo <= $inputMaxWord; ++$iTwo) {
						$minCharOfPhrase = ($inputMinCharEachWord * $iTwo) + ($iTwo - 1);
						
						$phraseNeedAnalysis = '';
						$maxIThree = $iOne + $iTwo;
						for($iThree = $iOne; $iThree < $maxIThree; ++$iThree) {
							if(isset($inputExplodedContents[$iThree])) {
								$wordTemp = trim($inputExplodedContents[$iThree]);
								if(isset($wordTemp[0])) {
									$phraseNeedAnalysis .= ' '.$wordTemp;
								}
							} else {
								break 2;
							}
						}
						
						$phraseNeedAnalysis = trim($phraseNeedAnalysis);
						
						if((mb_strlen($phraseNeedAnalysis, 'UTF-8') >= $minCharOfPhrase)  && (!isset($common[$phraseNeedAnalysis]))  && (!is_numeric($phraseNeedAnalysis))) {
							$resultAnalysis[$iTwo][] = $phraseNeedAnalysis;
							
						}
					}
					
				}
			}
			
			if(is_array($resultAnalysis)) {
				reset($resultAnalysis);
				foreach($resultAnalysis as $keyOne => $valueOne) {
					$valueOne = array_count_values($valueOne);
					
					if($inputMinOccur>1) {
						$valueOne = self::analysisKeyword_OccureFilter($valueOne, $inputMinOccur);
					}
					
					if(is_array($valueOne)) {
						arsort($valueOne);
						$resultAnalysis[$keyOne] = $valueOne;
					} else {
						unset($resultAnalysis[$keyOne]);
					}
				}
				krsort($resultAnalysis);
			}
			
			$resultData['data'] = $resultAnalysis; 
			
			
			self::$cacheObject->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
		
	}
	
	
	
	
	
	
	public static function staticVar_InitDataFileObject()
	{
		if(!self::$staticVarDataFileObject) {
			self::$staticVarDataFileObject = new PepVN_Cache();
			self::$staticVarDataFileObject->cache_time = 86400 * 15;
			self::$staticVarDataFileObject->cache_path = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'data' . DIRECTORY_SEPARATOR . 'lg' . DIRECTORY_SEPARATOR;
		}
	}
	
	
	public static function staticVar_GetKeyDataFileObject()
	{
		$keyCacheStaticVarData = __FILE__;
		
		$keyCacheStaticVarData = (string)$keyCacheStaticVarData;
		$keyCacheStaticVarData .= '_pepvn_staticvar_keycachestaticvardata_ilateddebidemasani';
		$keyCacheStaticVarData = md5($keyCacheStaticVarData);
		
		return $keyCacheStaticVarData;
	}
	
	
	public static function staticVar_InitData()
	{
		/*
		* all keys should not use number
		*/
		
		$resultData = array();
		
		$resultData['time_init'] = time();
		
		$resultData['total_number_requests'] = 0;
		
		$resultData['statistics']['group_urls'] = array(
			//'url' => number_access
		);
		
		$resultData['group_urls_prebuild_cache'] = array(
			//'url' => time_build_cache
		);
		
		return $resultData;
	}
	
	public static function staticVar_GetData($input_data = false)
	{
		self::staticVar_InitDataFileObject();
		$keyCacheStaticVarData = self::staticVar_GetKeyDataFileObject();
		
		$resultData = self::$staticVarDataFileObject->get_cache($keyCacheStaticVarData);
		if(!$resultData) {
			$resultData = self::staticVar_InitData();
		}
		
		return $resultData;
		
	}
	
	/*
	*	$input_data (array)
	*	$input_method (string) : [ m : merge ; r : replace ]
	*/
	public static function staticVar_SetData($input_data, $input_method = 'm')
	{
		if($input_data && is_array($input_data)) {
		
			self::staticVar_InitDataFileObject();
			
			$keyCacheStaticVarData = self::staticVar_GetKeyDataFileObject();
			
			if('m' === $input_method) {
				$input_data = self::mergeArrays(array(
					self::staticVar_GetData()
					, $input_data
				));
			}
			
			self::$staticVarDataFileObject->set_cache($keyCacheStaticVarData,$input_data);
		}
		
	}
	
	
	

}//class PepVN_Data


PepVN_Data::setDefaultParams();

endif; //if ( !class_exists('PepVN_Data') )



