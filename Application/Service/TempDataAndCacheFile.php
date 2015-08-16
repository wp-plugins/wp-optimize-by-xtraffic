<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WpPepVN\Utils
;

class TempDataAndCacheFile
{
	
	private static $_tempData = array();
	
	private static $_has_igbinary_status = false;
	
	private static $_key_salt = 0;
	
	public static function init()
	{
		self::$_key_salt = hash('crc32b', 'WPOptimizeByxTraffic/Application/Service/TempDataAndCacheFile/init', false );
	}
	
	public static function get_cache($keyCache, $useCachePermanentStatus = false) 
	{
		$keyCache = Utils::hashKey(array(
			self::$_key_salt
			, $keyCache
		));
		
		if(isset(self::$_tempData[$keyCache])) {
			return self::$_tempData[$keyCache];
		} else {
			
			$tmp = null;
			
			if(false === $useCachePermanentStatus) {
				$tmp = PepVN_Data::$cacheObject->get_cache($keyCache);
			} else {
				$tmp = self::$_tempData[$keyCache] = PepVN_Data::$cachePermanentObject->get_cache($keyCache);
			}
			
			if($tmp !== null) {
				//return $tmp;
				self::$_tempData[$keyCache] = $tmp;
				return self::$_tempData[$keyCache];
			}
		}
		
		return null;
	}
	
	
	public static function set_cache($keyCache, $data, $useCachePermanentStatus = false)
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
		
		return true;
	}
	
}

TempDataAndCacheFile::init();