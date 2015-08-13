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
		if(function_exists('igbinary_serialize')) {
			self::$_has_igbinary_status = true;
		}
		
		self::$_key_salt = crc32( __FILE__ );
	}
	
	private static function _serialize($data) 
	{
		if(true === self::$_has_igbinary_status) {
			return igbinary_serialize($data);
		} else {
			return serialize($data);
		}
	}
	
	private static function _unserialize($data) 
	{
		if(true === self::$_has_igbinary_status) {
			return igbinary_unserialize($data);
		} else {
			return unserialize($data);
		}
	}
	
	private static function _hashKey($input_data)
	{
		return hash('crc32b', md5(self::_serialize($input_data)));
	}
	
	public static function get_cache($keyCache, $useCachePermanentStatus = false) 
	{
		$keyCache = self::_hashKey(array(
			self::$_key_salt
			, $keyCache
		));
		
		if(isset(self::$_tempData[$keyCache])) {
			return Utils::ungzVar(self::$_tempData[$keyCache]);
		} else {
			if(false === $useCachePermanentStatus) {
				$tmp = PepVN_Data::$cacheObject->get_cache($keyCache);
			} else {
				$tmp = PepVN_Data::$cachePermanentObject->get_cache($keyCache);
			}
			if(null !== $tmp) {
				self::$_tempData[$keyCache] = Utils::gzVar($tmp);
				return $tmp;
			}
		}
		
		return null;
		
	}
	
	
	public static function set_cache($keyCache, $data, $useCachePermanentStatus = false)
	{
		$keyCache = self::_hashKey(array(
			self::$_key_salt
			, $keyCache
		));
		
		self::$_tempData[$keyCache] = Utils::gzVar($data);
		
		if(false === $useCachePermanentStatus) {
			PepVN_Data::$cacheObject->set_cache($keyCache,$data);
		} else {
			PepVN_Data::$cachePermanentObject->set_cache($keyCache,$data);
		}
		
		unset($data);
		
		return true;
	}
	
}

TempDataAndCacheFile::init();