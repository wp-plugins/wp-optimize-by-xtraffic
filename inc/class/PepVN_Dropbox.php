<?php

/*
* PepVN_Dropbox v1.0
*
* By PEP.VN
* http://pep.vn/
*
* Free to use and abuse under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
*/




if(!function_exists('Dropbox_autoload')) {
	require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/libs/dropbox-php/src/Dropbox/autoload.php');
	//https://github.com/Dropbox-PHP/dropbox-php
	//http://evertpot.com/dropbox-client-library-for-php/
	
}


if ( !class_exists('PepVN_Dropbox') ) :

class PepVN_Dropbox
{
	
	private $dbxAppConfig = false;
	private $ns = '';
	private $appInfo = '';
	private $dropboxObj = '';
	private $oauthObj = '';
	private $keySessionData = '';
	private $accountInfo = false;
	
	
	function __construct($input_parameters)
	{
		
		$this->dbxAppConfig = array();
		$this->dbxAppConfig['app_key'] = $input_parameters['app_key'];
		$this->dbxAppConfig['app_secret'] = $input_parameters['app_secret'];
		$this->dbxAppConfig['access_type'] = $input_parameters['access_type'];	//"FullDropbox" or "AppFolder"
		
		$this->dbxAppConfig['redirect_url'] = $input_parameters['redirect_url'];
		$this->ns = $input_parameters['namespace'];
		
		$this->keySessionData = PepVN_Data::createKey(array(
			$this->ns
			,$input_parameters
		));
		
		$keySessionData = $this->keySessionData;
		
		if(class_exists('OAuth')) {
			$this->oauthObj = new Dropbox_OAuth_PHP($this->dbxAppConfig['app_key'], $this->dbxAppConfig['app_secret']);
		} else if(class_exists('HTTP_OAuth')) {
			$this->oauthObj = new Dropbox_OAuth_PEAR($this->dbxAppConfig['app_key'], $this->dbxAppConfig['app_secret']);
		} else {
			$this->oauthObj = false;
		}
		
		if($this->oauthObj) {
			if($input_parameters['original_access_token']) {
				
				if(
					isset($input_parameters['original_access_token']['token']) 
					&& ($input_parameters['original_access_token']['token'])
					
					&& isset($input_parameters['original_access_token']['token_secret']) 
					&& ($input_parameters['original_access_token']['token_secret'])
				) {
					
					if(
						isset($_SESSION[$keySessionData]['getAccessToken']['token']) 
						&& ($_SESSION[$keySessionData]['getAccessToken']['token'])
						
						&& isset($_SESSION[$keySessionData]['getAccessToken']['token_secret']) 
						&& ($_SESSION[$keySessionData]['getAccessToken']['token_secret'])
					) {
					} else {
						$_SESSION[$keySessionData]['getAccessToken'] = $input_parameters['original_access_token'];
					}
				}
			}
		}
		
		
	}
	
	
	public function getAccountInfo() 
	{
		$resultData = array();
		
		$resultData['accountInfo'] = false;
		
		if($this->oauthObj && $this->dropboxObj) {
			if($this->accountInfo) {
				$resultData['accountInfo'] = $this->accountInfo;
			} else {
				try {
					$resultData['accountInfo'] = $this->dropboxObj->getAccountInfo();
					if($resultData['accountInfo']) {
						$this->accountInfo = $resultData['accountInfo'];
					}
				} catch( Exception $e ) {
					$this->accountInfo = false;
					$resultData['accountInfo'] = false;
					$resultData['notice']['error'][] = $e->getMessage();
				}
			}
		}
		
		return $resultData;
		
	}
	
	
	
	
	private function dropbox_Init($input_parameters)
	{
		$resultData = array();
		$resultData['authorized_status'] = true;
		if($this->oauthObj) {
			if(!$this->accountInfo) {
				$resultData['authorized_status'] = false;
				
				$rsOne = $this->authorizeAccount(array());
				$resultData = PepVN_Data::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
		}
		return $resultData;
	}
	
	
	
	
	public function getAuthData()
	{
		$keySessionData = $this->keySessionData;
		
		if(
			isset($_SESSION[$keySessionData]['getAccessToken']['token']) 
			&& ($_SESSION[$keySessionData]['getAccessToken']['token'])
			
			&& isset($_SESSION[$keySessionData]['getAccessToken']['token_secret']) 
			&& ($_SESSION[$keySessionData]['getAccessToken']['token_secret'])
		) {
			return $_SESSION[$keySessionData];
		}
		
		return false;
	}
	
	public function getKeySessionData()
	{
		return $this->keySessionData;
	}
	
	public function clearAuthData()
	{
		$keySessionData = $this->keySessionData;
		$_SESSION[$keySessionData] = array();
		
	}
	
	
	public function authorizeAccount($input_parameters)
	{
		
		$resultData = array();
		
		$keySessionData = $this->keySessionData;
		
		$resultData['authorized_status'] = false;
		
		if($this->accountInfo) {
			$resultData['authorized_status'] = true;
		}
		
		if(!$resultData['authorized_status']) {
			
			if(
				isset($_SESSION[$keySessionData]['getAccessToken']['token']) 
				&& ($_SESSION[$keySessionData]['getAccessToken']['token'])
				
				&& isset($_SESSION[$keySessionData]['getAccessToken']['token_secret']) 
				&& ($_SESSION[$keySessionData]['getAccessToken']['token_secret'])
			) {
				if($this->oauthObj) {
					$this->dropboxObj = 0;
					
					$this->oauthObj->setToken($_SESSION[$keySessionData]['getAccessToken']);
					
					$this->dropboxObj = new Dropbox_API($this->oauthObj);
					$this->dropboxObj->chunkSize = 1 * 1024 * 1024;	//x MB
					
					
					$rsOne = $this->getAccountInfo();
					$resultData = PepVN_Data::mergeArrays(array(
						$resultData
						,$rsOne
					));
					
					if($this->accountInfo) {
						$resultData['authorized_status'] = true;
					} else {
						$_SESSION[$keySessionData]['getRequestToken'] = false;
						$_SESSION[$keySessionData]['getAuthorizeUrl'] = false;
						$_SESSION[$keySessionData]['getAccessToken'] = false;
						$this->clearAuthData(); 
					}
					
				}
			}
		}
		
		if(!$resultData['authorized_status']) {
			
			if($this->oauthObj) {
			
				
				if(
					isset($_SESSION[$keySessionData]['getRequestToken']['token']) 
					&& ($_SESSION[$keySessionData]['getRequestToken']['token'])
					
					&& isset($_SESSION[$keySessionData]['getRequestToken']['token_secret']) 
					&& ($_SESSION[$keySessionData]['getRequestToken']['token_secret'])
				) {
				
				
				} else {
					try {
						$_SESSION[$keySessionData]['getRequestToken'] = $this->oauthObj->getRequestToken();
					} catch ( Exception $e ) {
						$_SESSION[$keySessionData]['getRequestToken'] = false;
						$resultData['notice']['error'][] = $e->getMessage();
					}
					
					if(
						isset($_SESSION[$keySessionData]['getRequestToken']['token']) 
						&& ($_SESSION[$keySessionData]['getRequestToken']['token'])
						
						&& isset($_SESSION[$keySessionData]['getRequestToken']['token_secret']) 
						&& ($_SESSION[$keySessionData]['getRequestToken']['token_secret'])
					) {
						try {
							$_SESSION[$keySessionData]['getAuthorizeUrl'] = $this->oauthObj->getAuthorizeUrl();
							if($_SESSION[$keySessionData]['getAuthorizeUrl']) {
								$_SESSION[$keySessionData]['getAuthorizeUrl'] .= '&oauth_token_secret='.$_SESSION[$keySessionData]['getRequestToken']['token_secret'];
							}
							if($this->dbxAppConfig['redirect_url']) {
								$_SESSION[$keySessionData]['getAuthorizeUrl'] .= '&oauth_callback='.rawurlencode($this->dbxAppConfig['redirect_url']);
							}
						} catch ( Exception $e ) {
							$_SESSION[$keySessionData]['getAuthorizeUrl'] = false;
							$resultData['notice']['error'][] = $e->getMessage();
						}
						
						
						
					}
					
					if(
						isset($_SESSION[$keySessionData]['getRequestToken']['token']) 
						&& ($_SESSION[$keySessionData]['getRequestToken']['token'])
						
						&& isset($_SESSION[$keySessionData]['getRequestToken']['token_secret']) 
						&& ($_SESSION[$keySessionData]['getRequestToken']['token_secret'])
						
						&& ($_SESSION[$keySessionData]['getAuthorizeUrl'])
					) {
					
					} else {
						$_SESSION[$keySessionData]['getRequestToken'] = false;
						$_SESSION[$keySessionData]['getAuthorizeUrl'] = false;
					}
				}
				
				
				
				if(
					isset($_SESSION[$keySessionData]['getRequestToken']['token']) 
					&& ($_SESSION[$keySessionData]['getRequestToken']['token'])
					
					&& isset($_SESSION[$keySessionData]['getRequestToken']['token_secret']) 
					&& ($_SESSION[$keySessionData]['getRequestToken']['token_secret'])
					
					&& ($_SESSION[$keySessionData]['getAuthorizeUrl'])
				) {
				
					try {
						$this->oauthObj->setToken($_SESSION[$keySessionData]['getRequestToken']);
						
						$_SESSION[$keySessionData]['getAccessToken'] = $this->oauthObj->getAccessToken();
						
						
					} catch ( Exception $e ) {
						$_SESSION[$keySessionData]['getAccessToken'] = false;
						$resultData['notice']['error'][] = $e->getMessage();
					}
					
				}
				
			}
			
		}
		
		
		if(isset($_SESSION[$keySessionData]['getAuthorizeUrl']) && $_SESSION[$keySessionData]['getAuthorizeUrl']) {
			$resultData['authorize_url'] = $_SESSION[$keySessionData]['getAuthorizeUrl'];
		}
		
		//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($_SESSION[$keySessionData],$resultData),'</pre>';
		
		return $resultData;

	}
	
	public function getAuthUrl()
	{
	
		$resultData = array();
		$resultData['authorize_url'] = false;
		
		$keySessionData = $this->keySessionData;
		
		$rsOne = $this->dropbox_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		if(isset($_SESSION[$keySessionData]['getAuthorizeUrl']) && ($_SESSION[$keySessionData]['getAuthorizeUrl'])) {
			$resultData['authorize_url'] = $_SESSION[$keySessionData]['getAuthorizeUrl'];
			if($this->dbxAppConfig['redirect_url']) {
				$resultData['authorize_url'] .= '&oauth_callback='.rawurlencode($this->dbxAppConfig['redirect_url']);
			}
		}
		
		return $resultData;
		
	}
	
	
	public function createFolder($input_folder_path, $input_options = false)
	{
		$resultData = array();
		
		$resultData['status'] = false;
		
		$rsAuthorizeAccount = $this->authorizeAccount(array());
		
		if($rsAuthorizeAccount['authorized_status']) {
			
			$rsCreateFolder = false;
			
			try {
				$rsCreateFolder = $this->dropboxObj->getMetaData($input_folder_path);
			} catch ( Exception $e ) {
				$rsCreateFolder = false;
				$resultData['notice']['error'][] = $e->getMessage();
			}
			
			if(
				$rsCreateFolder
				
				&& isset($rsCreateFolder['path'])
				&& ($rsCreateFolder['path'])
				
				&& isset($rsCreateFolder['is_dir'])
				&& ($rsCreateFolder['is_dir'])
				
			) {
				
			} else {
				$rsCreateFolder = $this->dropboxObj->createFolder($input_folder_path);
			}
			
			
			if(
				$rsCreateFolder
				
				&& isset($rsCreateFolder['path'])
				&& ($rsCreateFolder['path'])
				
				&& isset($rsCreateFolder['is_dir'])
				&& ($rsCreateFolder['is_dir'])
				
			) {
				$resultData['status'] = true;
			}
			
			//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($rsGetMetaData_Folder1,$rsCreateFolder),'</pre>';exit();
		}
		
		
		return $resultData;
		
	}
	
	
	
	
	public function getFile($input_source_file_path, $input_destination_file_path = false, $input_options = false)
	{
		if($input_source_file_path) {
			$rsAuthorizeAccount = $this->authorizeAccount(array());
		
			if($rsAuthorizeAccount['authorized_status']) {
		
				$rsOne = '';
				
				try {
					$rsOne = $this->dropboxObj->getFile($input_source_file_path);
				} catch ( Exception $e ) {
					$rsOne = '';
					$resultData['notice']['error'][] = $e->getMessage();
				}
				
				if($rsOne) {
					
					if($input_destination_file_path) {
						$input_destination_file_path = PepVN_Data::createFolder($input_destination_file_path);
						if($input_destination_file_path) {
							@file_put_contents($input_destination_file_path, $rsOne);
							if(file_exists($input_destination_file_path)) {
								$resultData['status'] = true;
								$resultData['data'] = $input_destination_file_path;
							}
						}
					} else {
						$resultData['status'] = true;
						$resultData['data'] = $rsOne;
					}
				}
				
				$rsOne = '';
				
			
			}
		}
		
		return $resultData;
	}
	
	public function putFile($input_source_file_path, $input_destination_file_path, $input_options = false)
	{
		$resultData = array();
		
		$resultData['status'] = false;
		
		$rsAuthorizeAccount = $this->authorizeAccount(array());
		
		if($rsAuthorizeAccount['authorized_status']) {
			
			$rsOne = false;
			
			try {
				$rsOne = $this->dropboxObj->putFile($input_destination_file_path, $input_source_file_path);
			} catch ( Exception $e ) {
				$rsOne = false;
				$resultData['notice']['error'][] = $e->getMessage();
			}
			
			if($rsOne) {
				$resultData['status'] = true;
			}
			
		}
		
		
		return $resultData;
		
	}
	
	
	
	
	public function delete($input_path, $input_options = false)
	{
		$resultData = array();
		
		$resultData['status'] = false;
		
		$rsAuthorizeAccount = $this->authorizeAccount(array());
		
		if($rsAuthorizeAccount['authorized_status']) {
			
			$rsOne = false;
			
			try {
				$rsOne = $this->dropboxObj->delete($input_path);
			} catch ( Exception $e ) {
				$rsOne = false;
				$resultData['notice']['error'][] = $e->getMessage();
			}
			//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($rsOne,$resultData),'</pre>';exit();
			if($rsOne) {
				$resultData['status'] = true;
			}
			
		}
		
		
		return $resultData;
		
	}
	
	
}//class PepVN_Dropbox


endif; //if ( !class_exists('PepVN_Dropbox') )



