<?php

if ( !class_exists('PepVN_Cache') ) : 

class PepVN_Cache 
{
	
	const CLEANING_MODE_ALL = 1;				//CLEAN ALL CACHE
	
	const CLEANING_MODE_EXPIRED = 2;				//CLEAN CACHE IS EXPIRED TIME
	
	const CLEANING_MODE_MATCHING_ALL_TAG = 3;	//CLEAN CACHE MATCH TAG 1 AND TAG 2 ...
	
	const CLEANING_MODE_MATCHING_ANY_TAG = 4;	//CLEAN CACHE MATCH TAG 1 OR TAG 2 ...
	
	const CLEANING_MODE_NOT_MATCHING_TAG = 5;	//CLEAN CACHE NOT HAS TAG 1 OR TAG 2...
	
	const CLEANING_MODE_CONTAIN_IN_TAG = 6;		//CLEAN CACHE CONTAIN CHARS IN TAG
	
	private $_options = false;
	
	private $_requestTime = 0;
	
	private $_memcache = false;
	
	private $_metadatasArrayCache = array();
	
	private $_shortKeysMethodsCache = array(
		'apc' => 'ac'
		,'memcache' => 'mc'
		,'file' => 'fi'
	);
	
	function __construct($options = array()) 
	{
		$options = array_merge(
			array(
				'cache_timeout' => 3600	//int : seconds
				,'hash_key_method' => 'crc32b'
				,'hash_key_salt' => ''
				,'gzcompress_level' => 2
				,'key_prefix' => ''
				/*
				,'cache_methods' => array(
					
					'apc' => array(
						'cache_timeout' => 3600
					),
					'memcache' => array(
						'cache_timeout' => 3600
						,'object' => false
						,'servers' => array(
							array(
								'host' => '127.0.0.1'
								,'port' => '11211'
								,'persistent' => true
								,'weight' => 1
							)
						)
						
					),
					
					'file' => array(
						'cache_timeout' => 3600
						, 'cache_dir' => ''
					),
					
				)
				*/
			)
			, (array)$options
		);
		
		
		$checkStatus1 = false;
		
		if(
			isset($options['cache_methods'])
			&& ($options['cache_methods'])
			&& !empty($options['cache_methods'])
		) {
			
			$shortKeysMethodsCache = $this->_shortKeysMethodsCache;
			
			foreach($options['cache_methods'] as $key1 => $val1) {
				if(!isset($shortKeysMethodsCache[$key1])) {
					unset($options['cache_methods'][$key1]);
				}
			}
			
			if(
				($options['cache_methods'])
				&& !empty($options['cache_methods'])
			) {
				$checkStatus1 = true;
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
			
			if(isset($_SERVER['REQUEST_TIME']) && $_SERVER['REQUEST_TIME']) {
				$this->_requestTime = $_SERVER['REQUEST_TIME'];
			} else {
				$this->_requestTime = time();
			}
			$this->_requestTime = (int)$this->_requestTime;
			
			if(isset($options['cache_methods']['memcache'])) {
				if(isset($options['cache_methods']['memcache']['object']) && ($options['cache_methods']['memcache']['object'])) {
					$this->_memcache = $options['cache_methods']['memcache']['object'];
					unset($options['cache_methods']['memcache']['object']);
				} else {
					if(
						isset($options['cache_methods']['memcache']['servers'])
						&& $options['cache_methods']['memcache']['servers']
						&& !empty($options['cache_methods']['memcache']['servers'])
					) {
						
						$this->_memcache = new Memcache;
						
						if($this->_memcache) {
							foreach($options['cache_methods']['memcache']['servers'] as $val1) {
								if($val1) {
									$opt1 = array_merge(
										array(
											'host' => '127.0.0.1'
											,'port' => '11211'
											,'persistent' => true
											,'weight' => 1
										)
										, $val1
									);
									
									$this->_memcache->addServer($opt1['host'], $opt1['port'], $opt1['persistent'], $opt1['weight']);
								}
							}
						}
						
						$memcacheVersion = false;
						
						if($this->_memcache) {
							$memcacheVersion = $this->_memcache->getVersion();
						}
						
						if(false === $memcacheVersion) {
							$this->_memcache = false;
						}
						
						unset($options['cache_methods']['memcache']['servers']);
					}
					
				}
				
			}
			
			
			if(
				isset($options['cache_methods']['file'])
			) {
				
				$isCacheMethodFileValid = false;
				
				if(
					isset($options['cache_methods']['file']['cache_dir'])
				) {
					if($options['cache_methods']['file']['cache_dir']) {
						if(
							!file_exists($options['cache_methods']['file']['cache_dir']) 
							|| !is_dir($options['cache_methods']['file']['cache_dir'])
						) {
							@mkdir($options['cache_methods']['file']['cache_dir'], null, true);
						}
						
						if(
							file_exists($options['cache_methods']['file']['cache_dir']) 
							&& is_dir($options['cache_methods']['file']['cache_dir'])
						) {
							if(
								is_readable($options['cache_methods']['file']['cache_dir'])
								&& is_writable($options['cache_methods']['file']['cache_dir'])
							) {
								$isCacheMethodFileValid = true;
							}
						}
					}
				}
				
				if(!$isCacheMethodFileValid) {
					unset($options['cache_methods']['file']);
				}
			}
			
			if(
				($options['cache_methods'])
				&& !empty($options['cache_methods'])
			) {
				$this->_options = $options;
			}
		}
		
		$options = null;unset($options);
	}
	
	private function _get_key($keyData,$type)
	{
		if('cache' === $type) {
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
		} else {
			$keyData = hash('crc32b', $this->_options['hash_key_salt'].$keyData);
		}
		
		$keyData = $this->_options['key_prefix'].$keyData;
		
		$keyData = $this->_safe_keydata($keyData);
		
		if('cache' === $type) {
			$keyData .= '.cache';
		} else if('tags' === $type) {
			$keyData .= '.tags';
		} else if('gmeta' === $type) {	//Only 1 file store all keyCache
			$keyData .= '.gmeta';
		}
		
		return $keyData;
	}
	
	private function _get_filepath($keyData)
	{
		$filepath = false;
		
		if(isset($this->_options['cache_methods']['file']['cache_dir'])) {
			$filepath = $this->_options['cache_methods']['file']['cache_dir'] . $keyData;
		}
		
		return $filepath;
	}
	
	private function _get_template_data()
	{
		return array(
			'd' => ''			//data
			,'t' => array()		//tags
			,'e' => 0			//expire time, data will expire at this timestamp
		);
	}
	
	public function set_cache($keyCache, $data, $tags = array(), $timeout = false)
	{
		if(false !== $this->_options) {
			
			$keyCache = $this->_get_key($keyCache,'cache');
			
			$data = array_merge(
				$this->_get_template_data()
				, array(
					'd' => (($this->_options['gzcompress_level']>0) ? gzcompress(serialize($data), $this->_options['gzcompress_level']) : $data)	//data
					,'t' => $tags	//tags
					,'e' => 0		//expire time
				)
			);
			
			foreach($this->_options['cache_methods'] as $method => $val1) {
				if(isset($data['e'])) {
					unset($data['e']);
				}
				$this->_set_data($keyCache,$data,$method,$timeout);
			}
			
			$this->_process_metatags($keyCache, $tags, 'add');
			
			$this->_process_gmeta(array(
				'gmeta_type' => 'caches'
				,'key_cache' => $keyCache
				,'cache_timeout' => $timeout
				,'action_type' => 'add'
			));
		}
	}
	
	public function get_cache($keyCache)
	{
		if(false !== $this->_options) {
			$keyCache = $this->_get_key($keyCache,'cache');
			$data = $this->_get_data($keyCache);
			if($data && isset($data['d'])) {
				return $data['d'];
			}
		}
		
		return null;
	}
	
	public function get_filemtime_filecache($keyCache)
	{
		$resultData = 0;
		
		if(false !== $this->_options) {
			$keyCache = $this->_get_key($keyCache,'cache');
			$filepath = $this->_get_filepath($keyCache);
			if($filepath && is_file($filepath) && is_readable($filepath)) {
				$resultData = filemtime($filepath);
			}
		}
		
		$resultData = (int)$resultData;
		
		return $resultData;
	}
	
	private function _get_data($keyData)
	{
		$cacheMethodsMiss = array();
		
		$data = null;
		
		foreach($this->_options['cache_methods'] as $method => $val1) {
			$data = $this->_get_data_by_method($keyData,$method);
			if($data && isset($data['d'])) {
				break;
			} else {
				$cacheMethodsMiss[$method] = $method;
			}
		}
		
		if($data && isset($data['d'])) {
			if(!empty($cacheMethodsMiss)) {
				
				$data_d = $data['d'];
				
				if($this->_options['gzcompress_level']>0) {
					$data['d'] = gzcompress(serialize($data['d']), $this->_options['gzcompress_level']);
				}
				
				foreach($cacheMethodsMiss as $method => $val1) {
					if(isset($data['e'])) {
						unset($data['e']);
					}
					$this->_set_data($keyData,$data,$method);
				}
				
				$data['d'] = $data_d;
				$data_d = 0;
				
			}
			
			return $data;
		}
	
		return null;
		
	}
	
	private function _is_key_cache($keyData)
	{
		if(1 === preg_match('#\.cache$#',$keyData)) {
			return true;
		}
		
		return false;
	}
	
	private function _get_data_by_method($keyData,$method)
	{
		$deleteCacheStatus = false;
		$data = false;
		
		if('file' === $method) {
			$filepath = $this->_get_filepath($keyData);
			if($filepath && is_file($filepath) && is_readable($filepath)) {
				$data = file_get_contents($filepath);
			}
		} else {
			
			if('apc' === $method) {
				if(apc_exists($keyData)) {
					$data = apc_fetch($keyData, $apcFetchStatus);
					if(!$data || !$apcFetchStatus) {
						$data = false;
						$deleteCacheStatus = true;
					}
				}
			} else if('memcache' === $method) {
				if($this->_memcache) {
					$data = $this->_memcache->get($keyData);
				}
			}
		}
		
		if($data) {
			
			$data = unserialize($data);
			
			if($data) {
				
				if(
					isset($data['d'])
					&& isset($data['t'])
					&& isset($data['e'])
				) {
					
					if($this->_options['gzcompress_level']>0) {
						if($this->_is_key_cache($keyData)) {
							if($data['d']) {
								$data['d'] = unserialize(gzuncompress($data['d']));
							}
						}
					}
					
					if($data['e']>0) {
						if($this->_requestTime < $data['e']) {
							return $data;
						} else {
							$deleteCacheStatus = true;
						}
					} else {
						return $data;
					}
				}
			}
		}
		
		if($deleteCacheStatus) {
			$this->_delete_data_by_method($keyData,$method);
		}
		
		return null;
	}
	
	
	private function _set_data($keyData,&$data,$method,$timeout = false)
	{
		if(false === $timeout) {
			$timeout = $this->_get_cache_timeout_by_method($method);
		}
		
		if(!isset($data['e'])) {
			$data['e'] = $this->_get_cache_expire_by_method($method,$timeout);
		}
		
		$data['e'] = (int)$data['e'];
		
		if('apc' === $method) {
			apc_store($keyData,serialize($data),$timeout);
		} else if('memcache' === $method) {
			if($this->_memcache) {
				$this->_memcache->set($keyData, serialize($data), MEMCACHE_COMPRESSED, $timeout);
			}
		} else if('file' === $method) {
			$filepath = $this->_get_filepath($keyData);
			if($filepath) {
				@file_put_contents($filepath, serialize($data));
			}
		}
	}
	
	public function delete_cache($keyCache)
	{
		if(false !== $this->_options) {
			
			$keyCache = $this->_get_key($keyCache,'cache');
			
			$this->_delete_cache($keyCache);
			
		}
	}
	
	private function _delete_cache($keyCache)
	{
		$data = $this->_get_data($keyCache);
		
		//Remove This KeyCache From All Tags
		if($data && isset($data['t']) && !empty($data['t'])) {
			$this->_process_metatags($keyCache, $data['t'], 'delete');
		}
		
		$data = null;
		
		$this->_process_gmeta(array(
			'gmeta_type' => 'caches'
			,'key_cache' => $keyCache
			,'action_type' => 'delete'
		));
		
		foreach($this->_options['cache_methods'] as $method => $val1) {
			$this->_delete_data_by_method($keyCache,$method);
		}
	}
	
	private function _delete_data_by_method($keyData,$method)
	{
		if('file' === $method) {
			$filepath = $this->_get_filepath($keyData);
			if($filepath && is_file($filepath) && is_writable($filepath)) {
				unlink($filepath);
			}
		} else if('apc' === $method) {
			if(apc_exists($keyData)) {
				apc_delete($keyData);
			}
		} else if('memcache' === $method) {
			if($this->_memcache) {
				$this->_memcache->delete($keyData);
			}
		}
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
				
				$keyCachesNeedDeleteAll = array();
				
				if(
					(self::CLEANING_MODE_EXPIRED === $input_parameters['clean_mode'])
					|| (self::CLEANING_MODE_CONTAIN_IN_TAG === $input_parameters['clean_mode'])
				) {
					
					if(
						(self::CLEANING_MODE_EXPIRED === $input_parameters['clean_mode'])
					) {
						$rsGmeta = $this->_get_gmeta('caches');
					} else if(self::CLEANING_MODE_CONTAIN_IN_TAG === $input_parameters['clean_mode']) {
						$rsGmeta = $this->_get_gmeta('tags');
					}
				}
				
				if(self::CLEANING_MODE_ALL === $input_parameters['clean_mode']) {
					foreach($this->_options['cache_methods'] as $method => $val1) {
						$this->_clean_all_by_method($method);
					}
				} else if(self::CLEANING_MODE_EXPIRED === $input_parameters['clean_mode']) {
					
					$keyCachesMethodsIsExpired = array();
					
					$shortKeysMethodsCache = $this->_shortKeysMethodsCache;
					$shortKeysMethodsCache = array_flip($shortKeysMethodsCache);
					
					if($rsGmeta && isset($rsGmeta['d']['cache_keys']) && !empty($rsGmeta['d']['cache_keys'])) {
						foreach($rsGmeta['d']['cache_keys'] as $key1 => $val1) {
							if($val1 && isset($val1['e']) && !empty($val1['e'])) {
								foreach($val1['e'] as $key2 => $val2) {
									if($this->_is_expired($val2)) {
										if(isset($shortKeysMethodsCache[$key2])) {
											$keyCachesMethodsIsExpired[$key1][$shortKeysMethodsCache[$key2]] = 1;
										}
										unset($val1['e'][$key2]);
									}
								}
								
								if(empty($val1['e'])) {
									$val1 = null;
								}
							}
							
							if(null === $val1) {
								unset($rsGmeta['d']['cache_keys'][$key1]);
								$keyCachesNeedDeleteAll[$key1] = 1;
								if(isset($keyCachesMethodsIsExpired[$key1])) {
									unset($keyCachesMethodsIsExpired[$key1]);
								}
								
							} else {
								$rsGmeta['d']['cache_keys'][$key1] = $val1;
							}
							
						}
					}
					
					$this->_set_gmeta('cache', $rsGmeta);
					
					$rsGmeta = null;
					
					if(!empty($keyCachesMethodsIsExpired)) {
						foreach($keyCachesMethodsIsExpired as $key1 => $val1) {
							if($val1 && !empty($val1)) {
								foreach($val1 as $key2 => $val2) {
									$this->_delete_data_by_method($key1,$key2);
								}
							}
						}
					}
					
				} else if(
					(self::CLEANING_MODE_MATCHING_ALL_TAG === $input_parameters['clean_mode'])
					|| (self::CLEANING_MODE_MATCHING_ANY_TAG === $input_parameters['clean_mode'])
					|| (self::CLEANING_MODE_NOT_MATCHING_TAG === $input_parameters['clean_mode'])
					|| (self::CLEANING_MODE_CONTAIN_IN_TAG === $input_parameters['clean_mode'])
				) {
					
					if(
						isset($input_parameters['tags'])
						&& ($input_parameters['tags'])
						&& !empty($input_parameters['tags'])
					) {
					
						$input_parameters['tags'] = (array)$input_parameters['tags'];
						$input_parameters['tags'] = array_unique($input_parameters['tags']);
						
						if(
							(self::CLEANING_MODE_MATCHING_ALL_TAG === $input_parameters['clean_mode'])
							|| (self::CLEANING_MODE_MATCHING_ANY_TAG === $input_parameters['clean_mode'])
							|| (self::CLEANING_MODE_NOT_MATCHING_TAG === $input_parameters['clean_mode'])
						) {
							
							$rsOne = $this->_get_cache_keys_matching_any_tags($input_parameters['tags']);
							
							if(null !== $rsOne) {
								if(!empty($rsOne)) {
									foreach($rsOne as $key1 => $val1) {
										
										if($val1) {
										
											if(
												(self::CLEANING_MODE_MATCHING_ALL_TAG === $input_parameters['clean_mode'])
												|| (self::CLEANING_MODE_NOT_MATCHING_TAG === $input_parameters['clean_mode'])
											) {
												$isDeleteThisCacheStatus = false;
												
												$rsTwo = $this->_get_data($val1);	//cache data
												
												if($rsTwo && isset($rsTwo['t']) && $rsTwo['t'] && !empty($rsTwo['t'])) {
													if(self::CLEANING_MODE_MATCHING_ALL_TAG === $input_parameters['clean_mode']) {
														$rsThree = array_diff($input_parameters['tags'], $rsTwo['t']);
														if($rsThree && !empty($rsThree)) {
															
														} else {
															$isDeleteThisCacheStatus = true;
														}
													} else if(self::CLEANING_MODE_NOT_MATCHING_TAG === $input_parameters['clean_mode']) {
														foreach($input_parameters['tags'] as $key2 => $val2) {
															if($val2) {
																if(!in_array($val2,$rsTwo['t'])) {
																	$isDeleteThisCacheStatus = true;
																	break 1;
																}
															}
															
														}
													}
													
												}
												
												if(true === $isDeleteThisCacheStatus) {
													$keyCachesNeedDeleteAll[$val1] = 1;
												}
											} else if(self::CLEANING_MODE_MATCHING_ANY_TAG === $input_parameters['clean_mode']) {
												$keyCachesNeedDeleteAll[$val1] = 1;
											}
											
										}//if($val1) {
									}
								}
							}
						} else if(
							(self::CLEANING_MODE_CONTAIN_IN_TAG === $input_parameters['clean_mode'])
						) {
							
							$tagsNeedGet = array();
							
							if($rsGmeta && isset($rsGmeta['d']['tags']) && !empty($rsGmeta['d']['tags'])) {
								foreach($rsGmeta['d']['tags'] as $tag => $val1) {
									if($tag) {
										foreach($input_parameters['tags'] as $inputTag) {
											
											if(false !== strpos($tag, $inputTag)) {
												$tagsNeedGet[$tag] = 1;
												break 1;
											}
										}
									}
								}
							}
							
							$rsGmeta = null;
							
							if(!empty($tagsNeedGet)) {
								
								$rsOne = $this->_get_cache_keys_matching_any_tags(array_keys($tagsNeedGet));
								
								if(null !== $rsOne) {
									if(!empty($rsOne)) {
										foreach($rsOne as $key1 => $val1) {
											if($val1) {
												$keyCachesNeedDeleteAll[$val1] = 1;
											}
										}
									}
								}
								
							}
						}
					}
					
				}
				
				if(!empty($keyCachesNeedDeleteAll)) {
					foreach($keyCachesNeedDeleteAll as $key1 => $val1) {
						$this->_delete_cache($key1);
					}
				}
				
			}
		}
		
	}
	
	
	private function _clean_all_by_method($method)
	{
		if('apc' === $method) {
			apc_clear_cache('user');
		} else if('memcache' === $method) {
			if($this->_memcache) {
				$this->_memcache->flush();
			}
		} else if('file' === $method) {
			if(isset($this->_options['cache_methods']['file']['cache_dir'])) {
				$cacheDir = $this->_options['cache_methods']['file']['cache_dir'];
				$cacheDir = preg_replace('#[\/\\\]+$#is','',$cacheDir).'/';
				if(file_exists($cacheDir) && is_dir($cacheDir) && is_readable($cacheDir) && is_writable($cacheDir)) {
					$files = glob($cacheDir.'*.*');
					if($files && is_array($files) && !empty($files)) {
						foreach($files as $file) {
							if($file && is_file($file) && is_writable($file)) {
								unlink($file);
							}
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
	
	private function _get_cache_timeout_by_method($method)
	{
		
		$cacheTimeout = 0;
		
		if(isset($this->_options['cache_methods'][$method]['cache_timeout'])) {
			$cacheTimeout = $this->_options['cache_methods'][$method]['cache_timeout'];
		} else {
			$cacheTimeout = $this->_options['cache_timeout'];
		}
		
		$cacheTimeout = (int)$cacheTimeout;
		
		return $cacheTimeout;
	}
	
	
	private function _get_cache_expire_by_method($method,$timeout = false)
	{
		$expire = 0;
		
		if(false === $timeout) {
			$timeout = $this->_get_cache_timeout_by_method($method);
		}
		
		$timeout = (int)$timeout;
		if($timeout > 0) {
			$expire = $this->_requestTime + $timeout;
		}
		
		$expire = (int)$expire;
		
		return $expire;
	}
	
	private function _is_expired($expire)
	{
		$expire = (int)$expire;
		if($expire > 0) {
			if($this->_requestTime >= $expire) {
				return true;
			}
		}
		
		return false;
	}
	
	public function _get_cache_keys_matching_any_tags($tags)
	{
		if(!empty($tags)) {
			$data = $this->_process_metatags(false,$tags,'get_cache_keys_matching_any_tags');
			
			if(
				isset($data['d']['cache_keys'])
				&& !empty($data['d']['cache_keys'])
			) {
				return array_keys($data['d']['cache_keys']);
			}
		}
		
		return null;
	}
	
	/*
	* Store Cache's Tags. For best performance, shouldn't gzcompress data
	*/
	private function _process_metatags($keyCache, $tags, $actionType)
	{
		$resultData = array();
		
		if(!empty($tags)) {
			
			$tags = (array)$tags;
			$tags = array_unique($tags);
			
			foreach($tags as $tag) {
				
				$keyTag = $this->_get_key($tag,'tags');
				
				$data = $this->_get_data($keyTag);
				
				if(null === $data) {
					if('add' === $actionType) {
						$data = $this->_get_template_data();
					}
				}
				
				if('add' === $actionType) {
					if($keyCache) {
						$data['d']['cache_keys'][$keyCache] = 1;
					}
				} else if('delete' === $actionType) {
					if(isset($data['d']['cache_keys'][$keyCache])) {
						unset($data['d']['cache_keys'][$keyCache]);
					}
				}
				
				if(in_array($actionType,array(
					'add'
					,'delete'
				))) {
					if(
						isset($data['d']['cache_keys'])
						&& !empty($data['d']['cache_keys'])
					) {
						foreach($this->_options['cache_methods'] as $method => $val1) {
							if(isset($data['e'])) {
								unset($data['e']);
							}
							$this->_set_data($keyTag,$data,$method,0);
						}
						
						$this->_process_gmeta(array(
							'gmeta_type' => 'tags'
							,'tags' => array($tag)
							,'action_type' => 'add'
						));
					} else {
						
						foreach($this->_options['cache_methods'] as $method => $val1) {
							$this->_delete_data_by_method($keyTag,$method);
						}
						
						$this->_process_gmeta(array(
							'gmeta_type' => 'tags'
							,'tags' => array($tag)
							,'action_type' => 'delete'
						));
					}
				} else if(in_array($actionType,array(
					'get_cache_keys_matching_any_tags'
				))) {
					if(
						isset($data['d']['cache_keys'])
						&& !empty($data['d']['cache_keys'])
					) {
						$resultData = array_merge($resultData, $data);
					}
				}
				
				$data = null;
				
			}
			
		}
		
		return $resultData;
	}
	
	/*
	* Store Cache's Information : expire time. For best performance, shouldn't gzcompress data
	*/
	private function _process_gmeta($input_parameters)
	{
		$data = null;
		
		if(
			isset($input_parameters['action_type'])
			&& ($input_parameters['action_type'])
		) {
			
			$actionType = $input_parameters['action_type'];
			
			$keyCache = false;
			
			$tags = false;
			
			if(
				isset($input_parameters['key_cache'])
				&& ($input_parameters['key_cache'])
			) {
				$keyCache = $input_parameters['key_cache'];
			}
			
			if(
				isset($input_parameters['tags'])
				&& ($input_parameters['tags'])
				&& !empty($input_parameters['tags'])
			) {
				
				$tags = (array)$input_parameters['tags'];
				$tags = array_unique($tags);
			}
			
			$keyGmeta = $this->_get_key_gmeta($input_parameters['gmeta_type']);
			
			$data = $this->_get_gmeta($input_parameters['gmeta_type']);
			
			if('add' === $actionType) {
				if($keyCache) {
					
					$cacheGmetaData = array();
					
					if(
						!isset($input_parameters['cache_timeout'])
					) {
						$input_parameters['cache_timeout'] = false;
					}
					
					$shortKeysMethodsCache = $this->_shortKeysMethodsCache;
					foreach($this->_options['cache_methods'] as $method => $val1) {
						$cacheGmetaData['e'][$shortKeysMethodsCache[$method]] = $this->_get_cache_expire_by_method($method,$input_parameters['cache_timeout']);
					}
					
					$data['d']['cache_keys'][$keyCache] = $cacheGmetaData;
				} else if($tags) {
					foreach($tags as $tag) {
						if($tag) {
							$data['d']['tags'][$tag] = 1;
						}
					}
				}
			} else if('delete' === $actionType) {
				if($keyCache) {
					if(isset($data['d']['cache_keys'][$keyCache])) {
						unset($data['d']['cache_keys'][$keyCache]);
					}
				} else if($tags) {
					foreach($tags as $tag) {
						if($tag) {
							if(isset($data['d']['tags'][$tag])) {
								unset($data['d']['tags'][$tag]);
							}
						}
					}
				}
			}
			
			if(in_array($actionType,array(
				'add'
				,'delete'
			))) {
				if(
					(
						isset($data['d']['cache_keys'])
						&& !empty($data['d']['cache_keys'])
					)
					
					||
					
					(
						isset($data['d']['tags'])
						&& !empty($data['d']['tags'])
					)
					
				) {
					$this->_set_gmeta($input_parameters['gmeta_type'], $data);
				} else {
					foreach($this->_options['cache_methods'] as $method => $val1) {
						$this->_delete_data_by_method($keyGmeta,$method);
					}
				}
			}
			
		}
		
		return $data;
	}
	
	private function _get_key_gmeta($gmetaType)
	{
		return $this->_get_key(hash('crc32b', md5($gmetaType . __FILE__ . __METHOD__)),'gmeta');
	}
	
	private function _get_gmeta($gmetaType)
	{
		$keyGmeta = $this->_get_key_gmeta($gmetaType);
		
		$data = $this->_get_data($keyGmeta);
		
		if(null === $data) {
			$data = $this->_get_template_data();
		}
		
		return $data;
	}
	
	private function _set_gmeta($gmetaType, &$data)
	{
		$keyGmeta = $this->_get_key_gmeta($gmetaType);
		
		foreach($this->_options['cache_methods'] as $method => $val1) {
			if(isset($data['e'])) {
				unset($data['e']);
			}
			$this->_set_data($keyGmeta,$data,$method,0);
		}
	}
	
}//class PepVN_Cache

endif; //if ( !class_exists('PepVN_Cache') ) 

