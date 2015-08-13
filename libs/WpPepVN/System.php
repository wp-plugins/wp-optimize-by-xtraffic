<?php 
namespace WpPepVN;

use WpPepVN\Utils
	,WpPepVN\Hash
	,WpPepVN\Exception
	
;

class System
{
	protected static $_tempData = array();
	
	public function __construct() 
    {
		
	}
	
	public static function extension_loaded($name)
	{
		$k = crc32('extension_loaded' . $name);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		} else {
			self::$_tempData[$k] = extension_loaded($name);
			return self::$_tempData[$k];
		}
	}
	
	public static function function_exists($name)
	{
		$k = crc32('function_exists' . $name);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		} else {
			self::$_tempData[$k] = function_exists($name);
			return self::$_tempData[$k];
		}
	}
	
	public static function class_exists($name)
	{
		$k = crc32('class_exists' . $name);
		
		if(isset(self::$_tempData[$k])) {
			return self::$_tempData[$k];
		} else {
			self::$_tempData[$k] = class_exists($name);
			return self::$_tempData[$k];
		}
	}
	
	public static function file_exists($path)
	{
		return file_exists($filename);
	}
	
	public static function getWebServerSoftwareName()
	{
		$k = 'gtwbsvswnm';
		
		if(!isset(self::$_tempData[$k])) {
			
			self::$_tempData[$k] = '';
			
			if(isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE']) {
				$tmp = $_SERVER['SERVER_SOFTWARE'];
				if(preg_match('#nginx#i',$tmp)) {
					self::$_tempData[$k] = 'nginx';
				} else if(preg_match('#apache#i',$tmp)) {
					self::$_tempData[$k] = 'apache';
				}
			}
		}
		
		return self::$_tempData[$k];
	}
	
	/*
	* 	#is_writable
		(PHP 4, PHP 5, PHP 7)
		is_writable — Tells whether the filename is writable
		Returns TRUE if the filename exists and is writable. The filename argument may be a directory name allowing you to check if a directory is writable.
		Keep in mind that PHP may be accessing the file as the user id that the web server runs as (often 'nobody'). 
		Safe mode limitations are not taken into account.
	*/
	public static function is_writable($filename)
	{
		return is_writable($filename);
	}
	
	public static function countElementsInDir($dir, $clearstatcacheStatus = true) 
	{
		$objects = false;
		
		if(is_dir($dir)) {
			if(is_readable($dir)) {
				
				if($clearstatcacheStatus) {
					clearstatcache(true, $dir);
				}
				
				$objects = scandir($dir);
				
				if(is_array($objects)) {
					$objects = array_diff($objects, array('.','..')); 
					$objects = count($objects);
				}
			}
		}
		
		return $objects;
	}
	
	public static function unlink($filename) 
	{
		$status = false;
		
		if(is_file($filename)) {
			if(is_writable($filename)) {
				$status = unlink($filename);
				clearstatcache(true, $filename);
			}
		}
		
		return $status;
	}
	
	public static function rmdir($dirname) 
	{
		$status = false;
		
		$numCountElementsInDir = self::countElementsInDir($dirname, true);
		if(false !== $numCountElementsInDir) {
			if($numCountElementsInDir < 1) {
				if(is_writable($dirname)) {
					$status = rmdir($dirname);
					clearstatcache(true, $dirname);
				}
			}
		}
		
		return $status;
	}
	
	public static function scandir($dir, $clearstatcacheStatus = true) 
	{
		
		if($clearstatcacheStatus) {
			clearstatcache();
		}
		
		$objects = array();
		
		if($dir) {
			if (is_dir($dir)) {
				if(is_readable($dir)) {
					$objects = scandir($dir);
				}
			}
		}
		
		if(!empty($objects)) {
			$objects = array_diff($objects, array('.','..')); 
			if(!empty($objects)) {
				foreach ($objects as $key => $value) {
					$objects[$key] = $dir . DIRECTORY_SEPARATOR . $value;
				}
			}
		}
		
		return $objects;
	}
	
	/* 
		#rmdirR()
		Remove dir recursive. This method remove dir and all files & subfolders in dir
	
	*/
	public static function rmdirR($dir, $clearstatcacheStatus = true) 
	{
		$resultData = array(
			'files' => 0
			,'dirs' => 0
			,'total' => 0
			,'error' => 0
		);
		
		if($clearstatcacheStatus) {
			clearstatcache();
		}
		
		if($dir) {
			if(is_writable($dir)) {
				if (is_dir($dir)) {
					
					$objects = false;
					
					if(is_readable($dir)) {
						$objects = scandir($dir);
					}
					
					if(is_array($objects)) {
						$objects = array_diff($objects, array('.','..')); 
						if(!empty($objects)) {
							foreach ($objects as $obj) {
								$objPath = $dir . DIRECTORY_SEPARATOR . $obj;
								if (is_dir($objPath)) {
									$rsOne = self::rmdirR($objPath, false);
									$resultData['files'] += $rsOne['files'];
									$resultData['dirs'] += $rsOne['dirs'];
									$resultData['total'] += $rsOne['total'];
									$resultData['error'] += $rsOne['error'];
								} else {
									$status = self::unlink($objPath);
									if($status) {
										$resultData['files']++;
									} else {
										$resultData['error']++;
									}
								}
							}
						}
							
						$status = self::rmdir($dir);
						if($status) {
							$resultData['dirs']++;
						} else {
							$resultData['error']++;
						}
					} else {
						$resultData['error']++;
					}
					
				} else {
					$status = self::unlink($dir);
					if($status) {
						$resultData['files']++;
					} else {
						$resultData['error']++;
					}
				}
			} else {
				$resultData['error']++;
			}
		} else {
			$resultData['error']++;
		}
		
		return $resultData;
	}
    
	public static function mkdir($dir) 
	{
		if(!is_dir($dir)) {
			return mkdir($dir,WP_PEPVN_CHMOD,true);
		}
		
		return true;
	}
	
	public static function hasAPC()
	{
		$k = 'hasAPC';
		
		if(!isset(self::$_tempData[$k])) {
			self::$_tempData[$k] = self::function_exists('apc_exists');
		}
		
		return self::$_tempData[$k];
	}
	
	// http://php.net/manual/en/class.memcached.php
	public static function hasMemcached()
	{
		$k = 'hasMemcached';
		
		if(!isset(self::$_tempData[$k])) {
			self::$_tempData[$k] = self::class_exists('Memcached');
		}
		
		return self::$_tempData[$k];
	}
	
	// http://php.net/manual/en/book.memcache.php
	public static function hasMemcache()
	{
		$k = 'hasMemcache';
		
		if(!isset(self::$_tempData[$k])) {
			self::$_tempData[$k] = self::class_exists('Memcache');
		}
		
		return self::$_tempData[$k];
	}
	
	public static function cleanServerConfigs($config_key,$text)
	{
		if($config_key) {
			$text = preg_replace('/\s*###### BEGIN '.preg_quote($config_key,'/').' ######.+###### END '.$config_key.' ######\s*/s',' ',$text);
		}
		
		return $text;
	}
	
	public static function setServerConfigs($input_parameters)
	{
		if(!isset($input_parameters['ROOT_PATH']) || !$input_parameters['ROOT_PATH']) {
			throw new Exception('"ROOT_PATH" is required');
		} else {
			$input_parameters['ROOT_PATH'] = rtrim($input_parameters['ROOT_PATH'],'/');
			$input_parameters['ROOT_PATH'] = rtrim($input_parameters['ROOT_PATH'],DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		}
		
		if(!isset($input_parameters['CONFIG_KEY']) || !$input_parameters['CONFIG_KEY']) {
			throw new Exception('"CONFIG_KEY" is required');
		} else {
			$input_parameters['CONFIG_KEY'] = strtoupper($input_parameters['CONFIG_KEY']);
		}
		
		$status = false;
		
		$webServerSoftwareName = self::getWebServerSoftwareName();
		
		if('apache' === $webServerSoftwareName) {
			
			if(!isset($input_parameters['htaccess']) || !$input_parameters['htaccess']) {
				throw new Exception('"htaccess" is required for Apache Web Server');
			}
			
			$pathFileHtaccess = $input_parameters['ROOT_PATH'].'.htaccess';
			
			$htaccessContent = false;
			
			if(is_file($pathFileHtaccess) && is_readable($pathFileHtaccess) && is_writable($pathFileHtaccess)) {
				$htaccessContent = file_get_contents($pathFileHtaccess);
				
			} else if(is_writable($input_parameters['ROOT_PATH'])) {
				file_put_contents($pathFileHtaccess,'');
				if(is_file($pathFileHtaccess) && is_readable($pathFileHtaccess) && is_writable($pathFileHtaccess)) {
					$htaccessContent = file_get_contents($pathFileHtaccess);
				}
			}
			
			if(false !== $htaccessContent) {
				$htaccessContent = self::cleanServerConfigs($input_parameters['CONFIG_KEY'],$htaccessContent);
				
				$htaccessContent = trim($htaccessContent);
				
				$input_parameters['htaccess'] = preg_replace('/###### BEGIN '.preg_quote($input_parameters['CONFIG_KEY'],'/').' ######/',' ',$input_parameters['htaccess']);
				$input_parameters['htaccess'] = preg_replace('/###### END '.preg_quote($input_parameters['CONFIG_KEY'],'/').' ######/',' ',$input_parameters['htaccess']);
				
				$input_parameters['htaccess'] = preg_replace('#\#[^\r\n]+#is','',$input_parameters['htaccess']);
				$input_parameters['htaccess'] = preg_replace('#[\r\n]{2,}#is',PHP_EOL . PHP_EOL,$input_parameters['htaccess']);
				
				$input_parameters['htaccess'] = trim($input_parameters['htaccess']);
				
				$input_parameters['htaccess'] = PHP_EOL . ' ###### BEGIN ' . $input_parameters['CONFIG_KEY'] . ' ###### ' . PHP_EOL . $input_parameters['htaccess'] . PHP_EOL . ' ###### END ' . $input_parameters['CONFIG_KEY'] . ' ###### ' . PHP_EOL;
				
				if(isset($input_parameters['append_status']) && ($input_parameters['append_status'])) {
					$status = file_put_contents($pathFileHtaccess, $htaccessContent . $input_parameters['htaccess']);
				} else {
					$status = file_put_contents($pathFileHtaccess,$input_parameters['htaccess'] . $htaccessContent);
				}
				
			}
			
		} else if('nginx' === $webServerSoftwareName) {
			
			if(!isset($input_parameters['nginx']) || !$input_parameters['nginx']) {
				throw new Exception('"nginx" is required for Nginx Web Server');
			}
			
			$pathFileConfig = $input_parameters['ROOT_PATH'].'xtraffic-nginx.conf';
			
			$configContent = false;
			
			if(is_file($pathFileConfig) && is_readable($pathFileConfig) && is_writable($pathFileConfig)) {
				$configContent = file_get_contents($pathFileConfig);
				
			} else if(is_writable($input_parameters['ROOT_PATH'])) {
				file_put_contents($pathFileConfig,'');
				if(is_file($pathFileConfig) && is_readable($pathFileConfig) && is_writable($pathFileConfig)) {
					$configContent = file_get_contents($pathFileConfig);
				}
			}
			
			if(false !== $configContent) {
				$configContent = self::cleanServerConfigs($input_parameters['CONFIG_KEY'],$configContent);
				
				$configContent = trim($configContent);
				
				$input_parameters['nginx'] = preg_replace('/###### BEGIN '.$input_parameters['CONFIG_KEY'].' ######/',' ',$input_parameters['nginx']);
				$input_parameters['nginx'] = preg_replace('/###### END '.$input_parameters['CONFIG_KEY'].' ######/',' ',$input_parameters['nginx']);
				
				$input_parameters['nginx'] = preg_replace('#\#[^\r\n]+#is','',$input_parameters['nginx']);
				$input_parameters['nginx'] = preg_replace('#([\;\{\}]+)\s+#is','$1 ',$input_parameters['nginx']);
				$input_parameters['nginx'] = preg_replace('#\s+([\;\{\}]+)#is',' $1',$input_parameters['nginx']);
				
				$input_parameters['nginx'] = trim($input_parameters['nginx']);
				
				$input_parameters['nginx'] = PHP_EOL . ' ###### BEGIN ' . $input_parameters['CONFIG_KEY'] . ' ###### ' . PHP_EOL . $input_parameters['nginx'] . PHP_EOL . ' ###### END ' . $input_parameters['CONFIG_KEY'] . ' ###### ' . PHP_EOL;
				
				if(isset($input_parameters['append_status']) && ($input_parameters['append_status'])) {
					$status = file_put_contents($pathFileConfig, $configContent . $input_parameters['nginx']);
				} else {
					$status = file_put_contents($pathFileConfig,$input_parameters['nginx'] . $configContent);
				}
				
			}
		}
		
		return $status;
	}
	
	
}