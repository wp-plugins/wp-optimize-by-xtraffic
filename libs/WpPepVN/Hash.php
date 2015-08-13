<?php
namespace WpPepVN;

use WpPepVN\System
	,WpPepVN\Exception
;

class Hash 
{
	protected static $_tempData = array();
	
	public static function has_algos($algorithm_name)
	{
		if(!isset(self::$_tempData['hash_algos'])) {
			self::$_tempData['hash_algos'] = array();
			if(System::function_exists('hash_algos')) {
				self::$_tempData['hash_algos'] = hash_algos();
				self::$_tempData['hash_algos'] = array_flip(self::$_tempData['hash_algos']);
			}
		}
		
		return isset(self::$_tempData['hash_algos'][$algorithm_name]);
		
	}
	
	public static function sha256($data, $raw_output = false)
	{
		$algo = 'sha256';
		
		if(!self::has_algos($algo)) {
			throw new Exception(sprintf('ERROR : Hashing algorithms "%s" not exist on your system. Please see details here "%s".', $algo , 'http://php.net/manual/en/function.hash.php'));
			return false;
		}
		
		return hash($algo, (string)$data, (bool)$raw_output);
	}
	
	
	public static function crc32b($data, $raw_output = false)
	{
		$algo = 'crc32b';
		
		return hash($algo, (string)$data, (bool)$raw_output);
	}
}
