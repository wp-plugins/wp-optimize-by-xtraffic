<?php
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\PepVN_Cache
	, WPOptimizeByxTraffic\Application\Service\PepVN_CacheSimpleFile
	, WpPepVN\DependencyInjectionInterface
	, WpPepVN\DependencyInjection
	, WpPepVN\System
;

class CacheManager 
{
	public $di = false;
	
	private $_cleanDataType = array();
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		//$wpExtend = $this->di->getShared('wpExtend');
		
	}
	
	public function clean_cache($data_type = ',common,')
	{
		
		$data_type = (array)$data_type;
		$data_type = implode(',',$data_type);
		$data_type = preg_replace('#[\,\;]+#is',';',$data_type);
		$data_type = explode(';',$data_type);
		foreach($data_type as $key1 => $value1) {
			$value1 = trim($value1);
			if(!$value1) {
				unset($data_type[$key1]);
			}
		}
		
		$data_type = array_unique($data_type);
		
		$data_type = array_values($data_type);
		
		$data_type = array_flip($data_type);
		
		$staticVarObject = $this->di->getShared('staticVar');
		
		$staticVarData = $staticVarObject->get();
		
		if(!isset($staticVarData['last_time_clean_cache_all'])) {
			$staticVarData['last_time_clean_cache_all'] = 0;
		}
		
		if($staticVarData['last_time_clean_cache_all'] <= ( PepVN_Data::$defaultParams['requestTime'] - 86400)) {	//is timeout
			$data_type['all'] = 1;
		}
		
		if(isset($data_type['all'])) {
			
			$staticVarData['last_time_clean_cache_all'] = PepVN_Data::$defaultParams['requestTime'];
			
			$staticVarObject->save($staticVarData);
		}
		
		unset($staticVarData,$staticVarObject);
		
		$timestampNow = PepVN_Data::$defaultParams['requestTime'];
		$timestampNow = (int)$timestampNow;
		
		//clean all cache data short time (<= 1 day)
		if(PepVN_Data::$cacheDbObject) {
			PepVN_Data::$cacheDbObject->clean(array(
				'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
			));
		}
		
		if(PepVN_Data::$cacheObject) {
			PepVN_Data::$cacheObject->clean(array(
				'clean_mode' => PepVN_CacheSimpleFile::CLEANING_MODE_ALL
			));
		}
		
		global $wp_object_cache;
		if(isset($wp_object_cache) && $wp_object_cache) {
			if(is_object($wp_object_cache)) {
				$wp_object_cache->flush();
			}
		}
		
		if(PepVN_Data::$cacheWpObject) {
			PepVN_Data::$cacheWpObject->clean(array(
				'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
			));
		}
		
		$cache = $this->di->getShared('cache');
		$cache->flush();
		
		/*
		* Clean all data in these folders
		*/
		$arrayPaths = array();
		
		$keyTemp = WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_CACHE_DIR . 'db' . DIRECTORY_SEPARATOR;
		$arrayPaths[$keyTemp] = 1;
		
		foreach($arrayPaths as $path1 => $value1) {
			unset($arrayPaths[$path1]);
			if($path1) {
				
				$objects = System::scandir($path1);
				
				foreach($objects as $objIndex => $objPath) {
					unset($objects[$objIndex]);
					if($objPath) {
						if(is_file($objPath)) {
							if(is_readable($objPath) && is_writable($objPath)) {
								unlink($objPath);
							}
						}
					}
				}
				
			}
		}
		
		if(
			isset($data_type['cache_permanent'])
			|| isset($data_type['all'])
		) {
			if(PepVN_Data::$cachePermanentObject) {
				PepVN_Data::$cachePermanentObject->clean(array(
					'clean_mode' => PepVN_CacheSimpleFile::CLEANING_MODE_ALL
				));
			}
			
			$cache = $this->di->getShared('cachePermanent');
			$cache->flush();
		}
		
		if(
			isset($data_type['cache_tag'])
			|| isset($data_type['all'])
		) {
			if(PepVN_Data::$cacheByTagObject) {
				PepVN_Data::$cacheByTagObject->clean(array(
					'clean_mode' => PepVN_Cache::CLEANING_MODE_ALL
				));
			}
		}
		
		if(
			isset($data_type['all'])
		) {
			
			$arrayPaths = array();
			
			$keyTemp = WP_CONTENT_PEPVN_DIR . 'cache' . DIRECTORY_SEPARATOR . 'static-files' . DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 24;	//hours
			
			$keyTemp = WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_STORAGES_CACHE_DIR . 'images' . DIRECTORY_SEPARATOR;
			$arrayPaths[$keyTemp] = 24;	//hours
			
			foreach($arrayPaths as $path1 => $timeout) {
				unset($arrayPaths[$path1]);
				
				$timeout = (int)$timeout;
				$timeoutSeconds = $timeout * 3600;
				
				if($path1) {
					
					$objects = System::scandir($path1);
					
					foreach($objects as $objIndex => $objPath) {
						unset($objects[$objIndex]);
						
						if($objPath) {
							if(is_file($objPath)) {
								if(is_readable($objPath) && is_writable($objPath)) {
									$fileatime = fileatime($objPath);
									if($fileatime && ($fileatime > 0)) {
										if(($fileatime + $timeoutSeconds) <= $timestampNow) {	//is timeout
											unlink($objPath);
										}
									} else {
										$filemtime = filemtime($objPath);
										if($filemtime && ($filemtime > 0)) {
											if(($filemtime + $timeoutSeconds) <= $timestampNow) {	//is timeout
												unlink($objPath);
											}
										}
									}
								}
							}
						}
					}
					
				}
			}
		}
		
		$wpExtend = $this->di->getShared('wpExtend');
		
		$remote = $this->di->getShared('remote');
		
		$arrayUrlNeedRequest = array(
			array(
				'url' => $wpExtend->get_home_url()
				,'config' => array(
					'method' => 'PURGE',
					'headers' => array( 
						'host' => PepVN_Data::$defaultParams['fullDomainName'], 
						'X-Purge-Method' => 'default'
					),
					'timeout'     => 1,
					'redirection'     => 1,
				)
			)
			
			, array(
				'url' => ($wpExtend->is_ssl() ? 'https://' : 'http://').'127.0.0.1/'
				,'config' => array(
					'method' => 'PURGE',
					'headers' => array( 
						'host' => PepVN_Data::$defaultParams['fullDomainName'], 
						'X-Purge-Method' => 'default'
					),
					'timeout'     => 1,
					'redirection'     => 1,
				)
			)
		);
		
		foreach($arrayUrlNeedRequest as $value1) {
			$remote->request($value1['url'], $value1['config']);
		}
		
		$hook = $this->di->getShared('hook');
		
		if($hook->has_action('clean_cache')) {
			$hook->do_action('clean_cache');
		}
		
		
		if($wpExtend->is_admin()) {
			
			$adminNotice = $this->di->getShared('adminNotice');
		
			$adminNotice->add_notice('<b>' . WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_NAME . '</b> : ' . 'All caches have been removed.', 'success');
		}
	}
	
}