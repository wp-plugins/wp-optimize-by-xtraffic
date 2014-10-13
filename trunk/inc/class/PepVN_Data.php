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
	
	function __construct()
	{
		self::setDefaultParams(); 
	}
	
	
	public static function setDefaultParams()
	{
		if(!self::$defaultParams) {
			
			self::$defaultParams['status'] = 1;
			
				
			self::$cacheObject = new PepVN_Cache();
			self::$cacheObject->cache_time = 86400;
			
			
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
		
		return $input_data;
	}
	
	
	
	public static function minifyCss($input_data)
	{
		$input_data = self::removeCommentInCss($input_data);
		
		$patterns = array(
			'#/\*[^*]*\*+([^/][^*]*\*+)*/#' => ''
			,'#\s+#s' => ' ' // Compress all spaces into single space
			
			//,'#(\/\*|\<\!\-\-)(.*?)(\*\/|\-\-\>)#s' => ' '// Remove all comments
			,'#(\/\*)(.*?)(\*\/)#is' => ' '// Remove all comments
			,'#(\s+)?([,{};:>\+])(\s+)?#s' => '$2' // Remove un-needed spaces around special characters
			//,'#url\([\'\"](.*?)[\'\"]\)#s' => 'url($1)'// Remove quotes from urls
			,'#;{2,}#' => ';' // Remove unecessary semi-colons
			
			
			,'#^\s+#m' => ' '
			,'#url\((\s+)([^\)]+)(\s+)\)#' => 'url($2)'
		);
		
		$input_data = preg_replace(array_keys($patterns), array_values($patterns), $input_data);
		
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
		
		$methodHash = 'sha1';
		
		if($length<=64) {
			$methodHash = 'sha256';
		} else if($length<=96) {
			$methodHash = 'sha384';
		} else {
			$methodHash = 'sha512';
		}
		
		$resultData = sha1($str);
		
		while(strlen($resultData) < $length) {
			
			$resultData .= hash($methodHash,$resultData,false);
			
		}
		
		$resultData .= hash($methodHash,$resultData,false);
		$resultData .= hash($methodHash,$resultData,false);
		
		while(strlen($resultData) > $length) {
			$valueTemp = str_split($resultData,1);
			$valueTemp_Count = count($valueTemp);
			
			$resultData = '';
			
			for($i=0;$i<$valueTemp_Count;$i++) {
				if(0 === ($i % 2)) {
					$resultData .= $valueTemp[$i];
				}
			}
			
			
		}
		
		
		return $resultData; 
		
	}
	

	
	public static function createKey($input_data)
	{
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
		$input_delimiter = $input_delimiter;
		if(preg_match('#[,;]+#i',$input_delimiter)) {
			$input_delimiter = preg_replace('#[;,]+#i',';',$input_delimiter);
			
			$input_data = (array)$input_data;
			$input_data = implode(';',$input_data);
		
		}
		
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
		preg_match_all('/<a[^><]+>.*?<\/a>/i',$input_content,$matched1);
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
					preg_match_all('/<'.$tagName.'[^><]+>.*?<\/'.$tagName.'>/i',$input_content,$matched1);
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
		
		preg_match_all("/<\!--\s*\[\s*if[^>]+>(.*?)<\!\s*\[\s*endif\s*\]\s*-->/si", $input_content, $matched1);
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
		
		$input_path = self::fixPath($input_path);
		
		$pathTemp1 = $input_path;
		
		if($pathTemp1 && file_exists($pathTemp1)) {
			$resultData = $pathTemp1;
			return $resultData;
		}
		
		
		$pathInfo = pathinfo($input_path);
		
		
		$arrayPath = explode(DIRECTORY_SEPARATOR, $input_path);
		if(isset($pathInfo['extension'])) {
			array_pop($arrayPath);
		}
		
		
		$pathTemp1 = implode(DIRECTORY_SEPARATOR,$arrayPath);
		@mkdir($pathTemp1, $chmod, true);
		if($pathTemp1 && file_exists($pathTemp1)) {
			return $input_path;
		}
		
		
		
		$folderPath = '';
		foreach($arrayPath as $path1) {
			$folderPath .= DIRECTORY_SEPARATOR . $path1;
			$pathTemp1 = $folderPath;
			
			if($pathTemp1 && file_exists($pathTemp1)) {
			} else {
				@mkdir($folderPath, $chmod, true);
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
		
		if($input_path && file_exists($input_path)) {
			if(is_readable($input_path)) {
				if(is_writable($input_path)) {
					$resultData = true;
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
	
	
	
	
	
	
	
	
	
	

}//class PepVN_Data


PepVN_Data::setDefaultParams(); 

endif; //if ( !class_exists('PepVN_Data') )


?>
