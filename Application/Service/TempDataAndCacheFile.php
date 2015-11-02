<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WpPepVN\Utils
;

class TempDataAndCacheFile
{
	
	private static $_tempData = array();
	
	private static $_key_salt = 0;
	
	private static $_group = 'default';
	
	public static function init()
	{
		self::$_key_salt = hash('crc32b', 'WPOptimizeByxTraffic/Application/Service/TempDataAndCacheFile/init' . WP_PEPVN_SITE_SALT, false );
	}
	
	public static function get_cache($keyCache, $useCachePermanentStatus = false, $useWpCacheStatus = false) 
	{
		$keyCache = Utils::hashKey(array(
			self::$_key_salt
			, $keyCache
		));
		
		if(isset(self::$_tempData[$keyCache])) {
			return self::$_tempData[$keyCache];
		} else {
			
			$found = false;
			
			if($useWpCacheStatus) {
				$tmp = wp_cache_get($keyCache, self::$_group, false, $found );
			}
			
			if(!$found) {
				
				$tmp = null;
				
				if(false === $useCachePermanentStatus) {
					$tmp = PepVN_Data::$cacheObject->get_cache($keyCache);
				} else {
					$tmp = PepVN_Data::$cachePermanentObject->get_cache($keyCache);
				}
				if($tmp !== null) {
					wp_cache_set( $keyCache, $tmp, self::$_group, WP_PEPVN_CACHE_TIMEOUT_NORMAL );
				}
			}
			
			if($tmp !== null) {
				self::$_tempData[$keyCache] = $tmp;
				return self::$_tempData[$keyCache];
			}
		}
		
		return null;
	}
	
	
	public static function set_cache($keyCache, $data, $useCachePermanentStatus = false, $useWpCacheStatus = false)
	{
		$keyCache = Utils::hashKey(array(
			self::$_key_salt
			, $keyCache
		));
		
		if(is_object($data)) {
			$data = clone $data;
		}
		
		self::$_tempData[$keyCache] = $data;
		
		if(false === $useCachePermanentStatus) {
			PepVN_Data::$cacheObject->set_cache($keyCache,$data);
		} else {
			PepVN_Data::$cachePermanentObject->set_cache($keyCache,$data);
		}
		
		if($useWpCacheStatus) {
			wp_cache_set( $keyCache, $data, self::$_group, WP_PEPVN_CACHE_TIMEOUT_NORMAL );
		}
		
		return true;
	}
	
}

TempDataAndCacheFile::init();