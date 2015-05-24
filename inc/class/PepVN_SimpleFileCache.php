<?php

if ( !class_exists('PepVN_SimpleFileCache') ) : 

class PepVN_SimpleFileCache
{
	
	const CLEANING_MODE_ALL = 1;				//CLEAN ALL CACHE
	
	const CLEANING_MODE_EXPIRED = 2;				//CLEAN CACHE IS EXPIRED TIME
	
	private $_options = false;
	
	private $_requestTime = 0;
	
	function __construct($options = array()) 
	{
		$options = array_merge(
			array(
				'cache_timeout' => 3600	//int : seconds
				,'hash_key_method' => 'crc32b'
				,'hash_key_salt' => ''
				,'gzcompress_level' => 2
				,'key_prefix' => ''
				,'cache_dir' => ''
			)
			, (array)$options
		);
		
		$checkStatus1 = false;
		
		$options['cache_timeout'] = (int)$options['cache_timeout'];
		$options['cache_timeout'] = abs($options['cache_timeout']);
		
		if(
			isset($options['cache_dir'])
			&& $options['cache_dir']
			&& ($options['cache_timeout']>0)
		) {
			if(
				!file_exists($options['cache_dir']) 
				|| !is_dir($options['cache_dir'])
			) {
				@mkdir($options['cache_dir'], null, true);
			}
			
			if(
				file_exists($options['cache_dir']) 
				&& is_dir($options['cache_dir'])
			) {
				if(
					is_readable($options['cache_dir'])
					&& is_writable($options['cache_dir'])
				) {
					$checkStatus1 = true;
				}
			}
		}
				
		if(
			$checkStatus1
		) {
			
			$options['gzcompress_level'] = (int)$options['gzcompress_level'];
			
			if($options['gzcompress_level']>9) {
				$options['gzcompress_level'] = 9;
			} elseif($options['gzcompress_level'] < 0) {
				$options['gzcompress_level'] = 0;
			}
			
			if(!$options['hash_key_salt']) {
				$options['hash_key_salt'] = md5(__FILE__ . __METHOD__);
			}
			
			$options['hash_key_salt'] = hash('crc32b', md5($options['hash_key_salt']));
			
			if(isset($_SERVER['REQUEST_TIME']) && $_SERVER['REQUEST_TIME']) {
				$this->_requestTime = $_SERVER['REQUEST_TIME'];
			} else {
				$this->_requestTime = time();
			}
			$this->_requestTime = (int)$this->_requestTime;
			
			$this->_options = $options;
		}
		
		$options = null;unset($options);
	}
	
	public function set_cache($keyCache, $data)
	{
		if(false !== $this->_options) {
			
			$keyCache = $this->_get_key($keyCache);
			
			$data = array(
				'd' => ((0 !== $this->_options['gzcompress_level']) ? gzcompress(serialize($data), $this->_options['gzcompress_level']) : $data)	//data
				,'e' => 0		//expire time
			);
			
			$data['e'] = $this->_requestTime + $this->_options['cache_timeout'];
			
			$data['e'] = (int)$data['e'];
			
			$filepath = $this->_get_filepath($keyCache);
			
			if($filepath) {
				@file_put_contents($filepath, serialize($data));
				return true;
			}
		}
		
		return false;
	}
	
	
	public function get_cache($keyCache)
	{
		if(false !== $this->_options) {
			
			$keyCache = $this->_get_key($keyCache);
			
			$filepath = $this->_get_filepath($keyCache);
			
			$data = $this->_get_cache_by_filepath($filepath);
			
			if(isset($data['d'])) {
				return $data['d'];
			}
			
		}
		
		return null;
	}
	
	
	
	public function get_filemtime_filecache($keyCache)
	{
		$resultData = 0;
		
		if(false !== $this->_options) {
			$keyCache = $this->_get_key($keyCache);
			$filepath = $this->_get_filepath($keyCache);
			if($filepath && is_file($filepath) && is_readable($filepath)) {
				$resultData = filemtime($filepath);
			}
		}
		
		$resultData = (int)$resultData;
		
		return $resultData;
	}
	
	
	public function delete_cache($keyCache)
	{
		if(false !== $this->_options) {
			
			$keyCache = $this->_get_key($keyCache);
			
			$filepath = $this->_get_filepath($keyCache);
			
			if($filepath && is_file($filepath) && is_writable($filepath)) {
				@unlink($filepath);
				return true;
			}
			
		}
		
		return false;
	}
	
	
	/*
	*	Remove Cache
	*/
	public function clean($input_parameters)
	{
		if(false !== $this->_options) {
			
			if(
				isset($input_parameters['clean_mode'])
				&& ($input_parameters['clean_mode'])
			) {
				
				$cacheDir = $this->_options['cache_dir'];
				
				$cacheDir = preg_replace('#[\/\\\]+$#is','',$cacheDir).'/';
				
				if(self::CLEANING_MODE_ALL === $input_parameters['clean_mode']) {
					
					if(file_exists($cacheDir) && is_dir($cacheDir) && is_readable($cacheDir) && is_writable($cacheDir)) {
						$files = glob($cacheDir.'*.*');
						if($files && is_array($files) && !empty($files)) {
							foreach($files as $key => $file) {
								unset($files[$key]);
								if($file && is_file($file) && is_writable($file)) {
									@unlink($file);
								}
							}
						}
					}
					
				} else if(self::CLEANING_MODE_EXPIRED === $input_parameters['clean_mode']) {
					if(file_exists($cacheDir) && is_dir($cacheDir) && is_readable($cacheDir) && is_writable($cacheDir)) {
						$files = glob($cacheDir.'*.*');
						if($files && is_array($files) && !empty($files)) {
							foreach($files as $key => $file) {
								unset($files[$key]);
								if($file && is_file($file) && is_writable($file)) {
									$isDeleteStatus1 = false;
									
									$data1 = $this->_get_cache_by_filepath($file);
									
									if(!isset($data1['d'])) {
										if($file && is_file($file) && is_writable($file)) {
											@unlink($file);
										}
									}
									
									$data1 = 0;
									
								}
							}
							
							$files = 0;
						}
					}
				}
				
			}
		}
		
	}
	
	//Helper function to validate key cache
	private function _safe_keydata($keyData)
	{
		$keyData = trim(preg_replace('#[^0-9a-z\_\-]+#is',' ', strtolower($keyData)));
		
		return preg_replace('#[\s \t]+#is','-',$keyData);
	}
	
	private function _get_cache_by_filepath($filepath)
	{
	
		$data = null;
		
		$cacheStatus = array();
		
		if($filepath && is_file($filepath)) {
			$data = file_get_contents($filepath);
		}
		
		if($data) {
			$data = unserialize($data);
			if($data) {
				if(
					isset($data['d'])
					&& isset($data['e'])
				) {
					if($this->_requestTime < $data['e']) { //is cache valid
						
						if(0 !== $this->_options['gzcompress_level']) {
							$data['d'] = unserialize(gzuncompress($data['d']));
						}
						
						$cacheStatus['valid'] = 1;
						
					} else {
						$cacheStatus['delete'] = 1;
					}
				}
			}
		}
		
		if(isset($cacheStatus['valid'])) {
			if(isset($data['d'])) {
				return $data;
			}
		} elseif(isset($cacheStatus['delete'])) {
			if($filepath && is_file($filepath)) {
				@unlink($filepath);
			}
		}
		
		return null;
	}
	
	
	private function _get_key($keyData)
	{
		if(false !== $this->_options['hash_key_method']) {
			if('crc32b' === $this->_options['hash_key_method']) {
				$keyData = hash('crc32b', $this->_options['hash_key_salt'].$keyData);
			} else if('crc32' === $this->_options['hash_key_method']) {
				$keyData = crc32($this->_options['hash_key_salt'].$keyData);
			} else if('md5' === $this->_options['hash_key_method']) {
				$keyData = md5($this->_options['hash_key_salt'].$keyData);
			} else {
				$keyData = hash('crc32b', $this->_options['hash_key_salt'].$keyData);
			}
		}
		
		return $this->_options['key_prefix'].$this->_safe_keydata($keyData).'.cache';
	}
	
	private function _get_filepath($keyData)
	{
		return $this->_options['cache_dir'] . $keyData;
	}
	
	
}//class PepVN_SimpleFileCache

endif; //if ( !class_exists('PepVN_SimpleFileCache') ) 

