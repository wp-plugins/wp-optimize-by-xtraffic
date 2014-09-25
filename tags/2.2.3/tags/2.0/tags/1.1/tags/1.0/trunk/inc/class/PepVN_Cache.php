<?php

/*
 * PepVN_Cache v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */

 
 
if(!defined('PEPVN_CACHE_DIR')){
	define('PEPVN_CACHE_DIR', realpath(dirname(__FILE__).'/../').'/cache/temp/');
}
 

if ( !class_exists('PepVN_Cache') ) :


class PepVN_Cache 
{

	//Path to cache folder (with trailing /)
	public $cache_path = PEPVN_CACHE_DIR;
	
	//Length of time to cache a file in seconds
	public $cache_time = 3600;
	
	function __construct() {
		$this->cache_path = PEPVN_CACHE_DIR;
		$this->cache_time = 3600;
	}

	
	function set_cache($label, $data)
	{
		if(!file_exists($this->cache_path)) {
			//mkdir($this->cache_path);
			mkdir($this->cache_path, WPOPTIMIZEBYXTRAFFIC_CHMOD, true);
		}
		
		if(file_exists($this->cache_path)) {
			file_put_contents($this->cache_path . $this->safe_filename($label) .'.cache', gzcompress(serialize($data),2));
			
		}
		
	}

	function get_cache($label)
	{
		if($this->is_cached($label)){
            $filename = $this->cache_path . $this->safe_filename($label) .'.cache';
			return unserialize(gzuncompress(file_get_contents($filename)));
		}

		return false;
	}

	function is_cached($label)
	{
		$filename = $this->cache_path . $this->safe_filename($label) .'.cache';

		if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;

		return false;
	}

	//Helper function to validate filenames
	function safe_filename($filename)
	{
		return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
	}




}//class PepVN_Cache

endif; //if ( !class_exists('PepVN_Cache') )


?>
