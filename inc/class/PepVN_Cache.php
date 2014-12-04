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

 
 
if(!defined('PEPVN_CACHE_DATA_DIR')){
	
	define('PEPVN_CACHE_DATA_DIR', WPOPTIMIZEBYXTRAFFIC_CACHE_PATH.'data'.DIRECTORY_SEPARATOR);
	
}
 

if ( !class_exists('PepVN_Cache') ) :


class PepVN_Cache 
{

	//Path to cache folder (with trailing /)
	public $cache_path = '';
	
	//Length of time to cache a file in seconds
	public $cache_time = 3600;
	
	
	public $serialize_status = true;
	
	public $gzcompress_status = true;
	
	
	
	function __construct() 
	{
		$this->cache_path = PEPVN_CACHE_DATA_DIR.'s'.DIRECTORY_SEPARATOR;
		$this->cache_time = 3600;
		
		
		PepVN_Data::createFolder($this->cache_path, WPOPTIMIZEBYXTRAFFIC_CHMOD);
		//PepVN_Data::chmod($this->cache_path,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
		
	}

	
	public function set_cache($label, $data)
	{
		if($this->cache_path && file_exists($this->cache_path)) {
		} else {
							
			PepVN_Data::createFolder($this->cache_path, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			//PepVN_Data::chmod($this->cache_path,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
			
		}
		
		
		if($this->cache_path && PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($this->cache_path))) {
			
			if($this->serialize_status) {
				$data = @serialize($data);
			}
			
			if($data) {
			
				if($this->gzcompress_status) {
					$data = @gzcompress($data,2);
				}
				
				
				if($data) {
					$filename = $this->get_filepath($label);
					@file_put_contents($filename, $data);
				}
			}
			
		}
		
	}

	public function get_cache($label)
	{
		if($this->is_cached($label)){
            
			$filename = $this->get_filepath($label);
			$data = @file_get_contents($filename);
			if($data) {
				
				if($this->gzcompress_status) {
					$data = @gzuncompress($data);
				}
				
				if($data) {
				
					if($this->serialize_status) {
						$data = @unserialize($data);
					}
					
					if($data) {
						return $data;
					}
				}
			}
			
		}

		return false;
	}

	public function is_cached($label)
	{
		$resultData = false;
		
		$filename = $this->get_filepath($label);
		
		if($filename) {
			if(PepVN_Data::is_readable($filename)) {
				$this->cache_time = abs((int)$this->cache_time);
				if(((filemtime($filename) + $this->cache_time) >= time())) {
					$resultData = true;
				}
			}
		}
		
		return $resultData;
	}

	//Helper function to validate filenames
	public function safe_filename($filename)
	{
		$filename = md5( WPOPTIMIZEBYXTRAFFIC_SITE_CONSTANT_ID . '_' . $filename ); 
		return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename)); 
	}
	
	public function get_filepath($label)
	{
		$filename = $this->cache_path . $this->safe_filename($label) .'.cache';
		
		return $filename;
	}

	public function get_filemtime($label)
	{
		$rsFilemtime = 0;
		
		$filename = $this->get_filepath($label);
		if($filename && PepVN_Data::is_readable($filename)) {
			$rsFilemtime = filemtime($filename);
		}
		
		$rsFilemtime = (int)$rsFilemtime;
		
		return $rsFilemtime;
	}


}//class PepVN_Cache


PepVN_Data::$cacheObject = new PepVN_Cache();
PepVN_Data::$cacheObject->cache_time = 86400;

PepVN_Data::$cacheSitePageObject = new PepVN_Cache();
PepVN_Data::$cacheSitePageObject->cache_time = 86400;
PepVN_Data::$cacheSitePageObject->serialize_status = false;
PepVN_Data::$cacheSitePageObject->gzcompress_status = true;



endif; //if ( !class_exists('PepVN_Cache') ) 

