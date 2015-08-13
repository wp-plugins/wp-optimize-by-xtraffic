<?php
namespace WpPepVN;

use WpPepVN\Text
;

class Utils 
{
	public static $defaultParams = false;
	
	private static $_tempData = array();
	
    public function __construct() 
    {
        
    }
    
	public static function setDefaultParams()
	{
		if(false === self::$defaultParams) {
			self::$defaultParams['status'] = true;
			
			self::$defaultParams['_has_igbinary'] = false;
			if(function_exists('igbinary_serialize')) {
				self::$defaultParams['_has_igbinary'] = true;
			}
		}
	}
	
	public static function hasIgbinary()
	{
		return self::$defaultParams['_has_igbinary'];
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
                        if(is_numeric($key)) {
							$merged[] = $value;
						} else {
							$merged[$key] = $value;
						}
					}
				}
			}
		}
		
		return $merged;
	}
    
	public static function serialize($data) 
	{
		if(true === self::$defaultParams['_has_igbinary']) {
			return igbinary_serialize($data);
		} else {
			return serialize($data);
		}
	}
	
	public static function unserialize($data) 
	{
		if(true === self::$defaultParams['_has_igbinary']) {
			return igbinary_unserialize($data);
		} else {
			return unserialize($data);
		}
	}
	
	/*
	* This method create fastest * shortest key for short time. Don't use this method to create ID for store in database (use method makeID instead)
	*/
	public static function hashKey($input_data)
	{
		return hash('crc32b', md5(self::serialize($input_data)));
	}
	
	public static function randomHash()
	{
		return md5(mt_rand() . '_' . time() . '_' . mt_rand());
	}
	
	/*
	* IMPORTANT : This method create ID for store in database. Don't change anything in this method because it affects many serious issues
	*/
	public static function makeID($input_data, $strict_status = false)
	{
		$input_data = serialize($input_data);
		
		if(false === $strict_status) {
			$input_data = preg_replace('#[\s \t]+#is', '', $input_data);
		}
		
		return md5($input_data);
	}
	
	public static function base64_encode($input_data)
	{
		return str_replace(array('+','/','='), array('-','_','.'), base64_encode($input_data));
	}
	
	public static function base64_decode($input_data)
	{
		return base64_decode(str_replace(array('-','_','.'), array('+','/','='), $input_data));
	}
	
	public static function encodeVar($input_data)
	{
        return self::base64_encode(json_encode($input_data));
	}
	
	public static function decodeVar($input_data)
	{
		return json_decode(self::base64_decode($input_data), true);
	}
	
	public static function gzVar($input_data, $gzip_level = 2)	//gzip_level >= 0 && gzip_level <= 9
	{
		
		$input_data = array(
			'c' => false //compress status
			,'d' => self::serialize($input_data) 	//data
		);
		
		if($gzip_level > 0) {
			if(isset($input_data['d'][256])) {	// if length > 256 bytes then compress
				$input_data['c'] = true;
				$input_data['d'] = gzcompress($input_data['d'], $gzip_level);
			}
		}
		
		return $input_data;
	}
	
	public static function ungzVar($input_data)
	{
		if(true === $input_data['c']) {
			$input_data['d'] = gzuncompress($input_data['d']);
		}
		
		$input_data['d'] = self::unserialize($input_data['d']);
		
		return $input_data['d']; 
	}
	
	public static function preg_quote($input_text, $delimiter = '#')
	{
		return preg_quote($input_text, $delimiter);
	}
		
	/**
	 * Determine if SSL is used.
	 *
	 * @return bool True if SSL, false if not used.
	 */
	public static function is_ssl() 
	{
		if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) ) {
				return true;
			}
			if ( '1' == $_SERVER['HTTPS'] ) {
				return true;
			}
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		
		return false;
	}
	
	public static function parse_url($url)
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
	
	public static function fixPath($input_path)
	{
		$input_path = preg_replace('#[/\\\]+#i',DIRECTORY_SEPARATOR,$input_path);
		
		$input_path = trim($input_path, DIRECTORY_SEPARATOR);
		
		return $input_path;
	}
	
	public static function safeFileName($filename)
	{
		$pathInfo = pathinfo($filename);
		
		if(isset($pathInfo['extension']) && isset($pathInfo['filename'])) {
			$pathInfo['filename'] = preg_replace('#[\.]+#i',' ',$pathInfo['filename']);
			$pathInfo['extension'] = preg_replace('#[\.]+#i','.',$pathInfo['extension']);
			$pathInfo['filename'] = Text::toSlug($pathInfo['filename']);
			
			if(isset($pathInfo['dirname'])) {
				$filename = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.' . $pathInfo['extension'];
			} else {
				$filename = $pathInfo['filename'] . '.' . $pathInfo['extension'];
			}
		} else {
			$filename = Text::toSlug($filename);
		}
		
		return $filename;
	}
	
	public static function isUrl($input_data)
	{
		$resultData = false;
		
		if(preg_match('#^https?://.+$#i',$input_data)) {
			$resultData = true;
		}
		
		return $resultData; 
	}
	
	public static function removeScheme($input_url) 
	{
		$input_url = trim($input_url);
		
		$input_url = preg_replace('#^(https?:)?//#i','//',$input_url);
		
		return $input_url;
	}
	
	public static function getHeadersList($strict_status = false) 
	{
		
		$k = self::hashKey(array('getHeadersList', $strict_status));
		if(!isset(self::$_tempData[$k])) {
			self::$_tempData[$k] = array();
			
			$tmp = headers_list();
			if($tmp) {
				foreach($tmp as $key1 => $value1) {
					unset($tmp[$key1]);
					$value1 = explode(':',$value1,2);
					$value1[0] = trim($value1[0]);
					if(!$strict_status) {
						$value1[0] = strtolower($value1[0]);
					}
					if(isset($value1[1])) {
						
						$value1[1] = trim($value1[1]);
						
						if(!$strict_status) {
							$value1[1] = strtolower($value1[1]);
						}
						
						self::$_tempData[$k][$value1[0]] = $value1[1];
						
					} else {
						self::$_tempData[$k][$value1[0]] = '';
					}
				}
			}
		}
		
		
	}
	
	public static function getContentTypeHeadersList() 
	{
		$headersList = self::getHeadersList();
		if(isset($headersList['content-type'])) {
			return $headersList['content-type'];
		}
		
		return '';
	}
	
	public static function removeQuotes($s)
	{
		$s = (string)$s;
		return preg_replace('#[\'\"]+#is','',$s);
	}
	
	public static function isImageFilePath($filePath)
	{
		$resultData = false;
		
		if(preg_match('#^.+\.(gif|jpeg|jpg|png)$#i',$filePath)) {
			$resultData = true;
		}
		
		return $resultData;
	}
	
	public static function isImageUrl($url)
	{
		$resultData = false;
		
		if(preg_match('#^(https?:)?//.+\.(gif|jpeg|jpg|png)\??.*$#i',$url)) {
			$resultData = true;
		}
		
		return $resultData;
	}
	
	public static function isUrlSameHost($url, $host)
	{
		$resultData = false;
		
		if(preg_match('#^(https?:)?//'.preg_quote($host,'#').'/?#i',$url)) {
			$resultData = true;
		}
		
		return $resultData;
	}
	
	
	public static function isUrlSameDomain($url, $domain, $strict_status = true)
	{
		$resultData = false;
		
		if(true === $strict_status) {
			if(preg_match('#^(https?:)?//'.preg_quote($domain,'#').'/?#i',$url)) {
				$resultData = true;
			}
		} else {
			if(preg_match('#^(https?:)?//[^/\?]*'.preg_quote($domain,'#').'/?#i',$url)) {
				$resultData = true;
			}
		}
		
		return $resultData;
	}
}

Utils::setDefaultParams();
