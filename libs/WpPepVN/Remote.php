<?php 
namespace WpPepVN;

use WpPepVN\DependencyInjection
	,WpPepVN\Utils
	,WpPepVN\Remote\Curl
;

/**
 * WpPepVN\Remote
 *
 * Allows to validate data using custom or built-in validators
 */
class Remote
{
	
	private $_cacheFile = false;
	
	private $_http_UserAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36';
	
	private $_http_Headers = array();
	
	private $di = false;
	
	private $_tempData = array();
	
	private $_curl = false;
	
	public function __construct(DependencyInjection $di) 
	{
		
		$this->di = $di;
		
		$tmp = array();
		$tmp['user-agent'] = 'User-Agent: '.$this->_http_UserAgent;
		$tmp['accept'] = 'Accept: */*;';
		$tmp['accept-encoding'] = 'Accept-Encoding: gzip,deflate';
		$tmp['accept-charset'] = 'Accept-Charset: UTF-8,*;';
		$tmp['keep-alive'] = 'Keep-Alive: 300';
		$tmp['connection'] = 'Connection: keep-alive';
		
		$this->_http_Headers = $tmp;
		
		if(function_exists('curl_init')) {
			$this->_curl = new Curl();
			$this->_curl->setUserAgent($this->_http_UserAgent);
			$this->_curl->setHeaders($this->_http_Headers);
		}
	}
	
	public function setCacheFileObject($cacheFile) 
	{
		$this->_cacheFile = $cacheFile;
	}
	
	
	private function _is_has_curl() 
	{
		$k = '_is_has_curl';
		
		if(!isset($this->_tempData[$k])) {
			if(function_exists('curl_init')) {
				$this->_tempData[$k] = true;
			} else {
				$this->_tempData[$k] = false;
			}
		}
		
		return $this->_tempData[$k];
	}
	
	
	public function get($input_url, $input_args = false) 
	{
		if(!$this->_cacheFile) {
			$this->_cacheFile = $this->di->getShared('cache');
		}
		
		if(!$input_args) {
			$input_args = array();
		}
		
		if(preg_match('#^//.+#i',$input_url,$matched1)) {
			$input_url = 'http:'.$input_url;
		}
		
		if(!isset($input_args['request_timeout'])) {
			$input_args['request_timeout'] = 6;
		}
		
		$input_args['request_timeout'] = (int)$input_args['request_timeout'];
		if($input_args['request_timeout'] < 1) {
			$input_args['request_timeout'] = 1;
		}
		
		$return_detail_status = false;
		
		if(isset($input_args['return_detail_status'])) {
			$return_detail_status = (bool)$input_args['return_detail_status'];
			unset($input_args['return_detail_status']);
		}
		
		$cache_timeout = 0;
		if(isset($input_args['cache_timeout'])) {
			$cache_timeout = (int)$input_args['cache_timeout'];
			unset($input_args['cache_timeout']);
		}
		$cache_timeout = (int)$cache_timeout;
		
		$redirection = 9;
		if(isset($input_args['redirection'])) {
			$redirection = (int)$input_args['redirection'];
			unset($input_args['redirection']);
		}
		$redirection = (int)$redirection;
		
		if($cache_timeout > 0) {
			if($this->_cacheFile) {
				
				$keyCache1 = Utils::hashKey(array(
					'WpPepVN_Remote_get'
					,$input_url
					,$input_args
				));
				
				$resultData = $this->_cacheFile->get($keyCache1);
				
				if(null !== $resultData) {
					return $resultData;
				}
			}
		}
		
		$input_args = array_merge(array(
			'method' => 'GET',
			'timeout'     => 6,
			'redirection' => $redirection,
			//'httpversion' => '1.0',
			'user-agent'  => $this->_http_UserAgent,
			'blocking'    => true,
			'headers'     => array(),//$this->_http_Headers,
			'cookies'     => array(),
			//'body'        => null,
			'compress'    => true,
			'decompress'  => true,
			'sslverify'   => false,
			//'stream'      => false,
			'filename'    => null
		), $input_args);
		
		//$objWPHttp = new \WP_Http_Streams();
		$objWPHttp = new \WP_Http();
		
		$resultData = $objWPHttp->request($input_url, $input_args);
		
		if(!$return_detail_status) {
			
			$isOkStatus = false;
			
			if ( !is_wp_error( $resultData ) ) {
				if ( is_array( $resultData ) ) {
					if(isset($resultData['body'])) {
						
						$isOkStatus = true;
						
						if(isset($resultData['response']['code']) && $resultData['response']['code']) {
							$resultData['response']['code'] = (int)$resultData['response']['code'];
							if(200 !== $resultData['response']['code']) {
								$isOkStatus = false;
							}
						}
					}
				}
			}
			
			if($isOkStatus) {
				$resultData = $resultData['body'];
			} else {
				$resultData = false;
				if($this->_curl) {
					$resultData = $this->_curl->get($input_url, $input_args);
					if(!$resultData) {
						$resultData = false;
					}
				}
			}
		}
		
		if($cache_timeout > 0) {
			if($this->_cacheFile) {
				$this->_cacheFile->save($keyCache1, $resultData);
			}
		}
		
		return $resultData;
		
		
	}
	
	
	public function request($input_url, $input_args = false) 
	{
		return $this->get($input_url, $input_args);
	}
	
}