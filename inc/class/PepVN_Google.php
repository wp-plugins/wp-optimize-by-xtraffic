<?php

/*
* PepVN_Google v1.0
*
* By PEP.VN
* http://pep.vn/
*
* Free to use and abuse under the MIT license.
* http://www.opensource.org/licenses/mit-license.php
*/




if(!function_exists('google_api_php_client_autoload')) {
	require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/libs/google-api-php-client-master/autoload.php');
}


if ( !class_exists('PepVN_Google') ) :

class PepVN_Google
{
	
	private $appConfig = false;
	private $ns = '';
	private $appInfo = '';
	private $appData = false;
	private $keySessionData = '';
	private $isAuthorizedAccount = false;
	
	private $googleClientObj = false;
	private $googleServiceDriveObj = false;
	
	private $cacheData = array();
	
	function __construct($input_parameters)
	{
	
		$this->appData = array();
		
		$this->appConfig = array();
		$this->appConfig['client_id'] = $input_parameters['client_id'];
		$this->appConfig['client_secret'] = $input_parameters['client_secret'];
		
		$this->appConfig['redirect_url'] = $input_parameters['redirect_url'];
		
		$this->appConfig['scopes'] = $input_parameters['scopes'];
		$this->appConfig['scopes'] = (array)$this->appConfig['scopes'];
		
		$this->appConfig['original_access_token'] = $input_parameters['original_access_token'];
		
		$this->ns = $input_parameters['namespace'];
		
		$this->keySessionData = PepVN_Data::createKey(array(
			$this->ns
			,$input_parameters
		));
		
		$this->googleClientObj = new Google_Client();
		// Get your credentials from the console
		$this->googleClientObj->setClientId($this->appConfig['client_id']);
		$this->googleClientObj->setClientSecret($this->appConfig['client_secret']);
		$this->googleClientObj->setRedirectUri($this->appConfig['redirect_url']);
		$this->googleClientObj->setState('token');
		$this->googleClientObj->setAccessType('offline');
		$this->googleClientObj->setApprovalPrompt('force');
		$this->googleClientObj->setScopes($this->appConfig['scopes']);
		
	}
	
	
	
	public function refreshToken($input_parameters)
	{
		$resultData = array();
		
		$keySessionData = $this->keySessionData;
		
		if(isset($this->appConfig['original_access_token']) && $this->appConfig['original_access_token']) {
			$original_access_token = $this->appConfig['original_access_token'];
			$original_access_token = json_decode($original_access_token, true, 512);
			if($original_access_token) {
				if(isset($original_access_token['refresh_token']) && $original_access_token['refresh_token']) {
					if($this->googleClientObj) {
						
						$keyStaticVar = PepVN_Data::createKey(array(
							$keySessionData
						));
						
						try {
							$this->googleClientObj->refreshToken($original_access_token['refresh_token']);
						} catch ( Exception $e ) {
							$resultData['notice']['error'][] = $e->getMessage();
						}
						
						
						$_SESSION[$keySessionData]['getAccessToken'] = $this->googleClientObj->getAccessToken();
						if($_SESSION[$keySessionData]['getAccessToken']) {
							$this->googleClientObj->setAccessToken($_SESSION[$keySessionData]['getAccessToken']);
							if($this->googleClientObj->isAccessTokenExpired()) {
								$_SESSION[$keySessionData] = array();
							}
						} else {
							$_SESSION[$keySessionData] = array();
						}
						
						PepVN_Data::staticVar_SetData($keyStaticVar, array(
							$keySessionData => array()
						), 'r');
						
						if($this->googleClientObj) {
							$this->googleServiceDriveObj = new Google_Service_Drive($this->googleClientObj);
						}
					}
				}
			}
		}
		
		return $resultData;
	}
	
	
	public function authorizeAccount($input_parameters)
	{
		$resultData = array();
		
		$resultData['authorized_status'] = false;
		
		$keySessionData = $this->keySessionData;
		
		$keyStaticVar = PepVN_Data::createKey(array(
			$keySessionData
		));
		
		
		if (isset($input_parameters['code']) && $input_parameters['code']) {
			if(!$this->isAuthorizedAccount) {
				try {
					$this->googleClientObj->authenticate($input_parameters['code']);
					$_SESSION[$keySessionData]['getAccessToken'] = $this->googleClientObj->getAccessToken();
					if($_SESSION[$keySessionData]['getAccessToken']) {
						$resultData['original_access_token'] = $_SESSION[$keySessionData]['getAccessToken'];
					}
				} catch ( Exception $e ) {
					$_SESSION[$keySessionData]['getAccessToken'] = false;
					$resultData['notice']['error'][] = $e->getMessage();
					//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($e->getMessage()),'</pre>';
				}
			}
			
		}
		
		if($this->isAuthorizedAccount) {
			$resultData['authorized_status'] = true;
		}
		
		if(!$resultData['authorized_status']) {
			if(isset($_SESSION[$keySessionData]['getAccessToken']) && ($_SESSION[$keySessionData]['getAccessToken'])) {
			} else {
				if (isset($input_parameters['code']) && $input_parameters['code']) {
				} else {
					$staticVarData = PepVN_Data::staticVar_GetData($keyStaticVar, false);
					if(isset($staticVarData[$keySessionData]['getAccessToken']) && ($staticVarData[$keySessionData]['getAccessToken'])) {
						$_SESSION[$keySessionData] = $staticVarData[$keySessionData];
					}
				}
			}
		}
		
		if(!$resultData['authorized_status']) {
			
			if(isset($_SESSION[$keySessionData]['getAccessToken']) && ($_SESSION[$keySessionData]['getAccessToken'])) {
				
				if($this->googleClientObj) {
					try {
						$this->googleClientObj->setAccessToken($_SESSION[$keySessionData]['getAccessToken']);
						
						$this->googleServiceDriveObj = new Google_Service_Drive($this->googleClientObj);
						
						if($this->googleClientObj->isAccessTokenExpired()) {
							$this->refreshToken(array());
						}
					} catch ( Exception $e ) {
						$resultData['notice']['error'][] = $e->getMessage();
					}
					
					
				}
			}
		}
		
		
		
		if(!$resultData['authorized_status']) {
			try {
				if(isset($this->googleServiceDriveObj->about) && ($this->googleServiceDriveObj->about)) {
					$about = $this->googleServiceDriveObj->about->get();
					if($about) {
						$about_GetName = $about->getName();
						if(
							(false !== $about_GetName)
							&& (null !== $about_GetName)
							&& (strlen($about_GetName) > 0)
						) {
							$resultData['authorized_status'] = true;
						}
					}
				}
			} catch (Exception $e) {
				$resultData['notice']['error'][] = $e->getMessage();
			}
		}
		
		if(!$resultData['authorized_status']) {
			$this->refreshToken(array());
		}
		
		
		if(!$resultData['authorized_status']) {
			try {
				if(isset($this->googleServiceDriveObj->about) && ($this->googleServiceDriveObj->about)) {
					$about = $this->googleServiceDriveObj->about->get();
					if($about) {
						$about_GetName = $about->getName();
						if(
							(false !== $about_GetName)
							&& (null !== $about_GetName)
							&& (strlen($about_GetName) > 0)
						) {
							$resultData['authorized_status'] = true;
						}
					}
				}
			} catch (Exception $e) {
				$resultData['notice']['error'][] = $e->getMessage();
			}
		}
		
		
		
		if(!$resultData['authorized_status']) {
		
			if(isset($_SESSION[$keySessionData]['authUrl']) && ($_SESSION[$keySessionData]['authUrl'])) {
				$resultData['authorize_url'] = $_SESSION[$keySessionData]['authUrl'];
			} else {
				$authUrl = $this->googleClientObj->createAuthUrl();
				
				$_SESSION[$keySessionData]['authUrl'] = $authUrl;
			}
			
		}
		
		if($resultData['authorized_status']) {
			$this->isAuthorizedAccount = true;
		}
		
		
		//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($resultData, $_SESSION[$keySessionData]),'</pre>';
		
		return $resultData;

	}
	
	
	
	
	private function google_Init($input_parameters)
	{
		$resultData = array();
		$resultData['authorized_status'] = true;
		if(!$this->isAuthorizedAccount) {
			$resultData['authorized_status'] = false;
			
			$rsOne = $this->authorizeAccount(array());
			$resultData = PepVN_Data::mergeArrays(array(
				$resultData
				,$rsOne
			));
		}
		return $resultData;
	}
	
	private function googleDrive_Init($input_parameters)
	{
		$resultData = array();
		
		$rsOne = $this->google_Init($input_parameters);
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		if(!$this->googleServiceDriveObj) {
			if($this->googleClientObj) {
				$this->googleServiceDriveObj = new Google_Service_Drive($this->googleClientObj);
			}
		}
		
		return $resultData;
	}
	
	
	public function getAuthUrl()
	{
		$resultData = array();
		$resultData['authorize_url'] = false;
		
		$keySessionData = $this->keySessionData;
		
		$rsOne = $this->google_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		if(isset($_SESSION[$keySessionData]['authUrl']) && ($_SESSION[$keySessionData]['authUrl'])) {
			$resultData['authorize_url'] = $_SESSION[$keySessionData]['authUrl'];
		}
		
		return $resultData;
	}
	
	
	
	public function googleDrive_GetAbout($input_parameters)
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		try {
		
			$about = $this->googleServiceDriveObj->about->get();
			$resultData['getName'] = $about->getName();
			$resultData['getQuotaBytesTotal'] = $about->getQuotaBytesTotal();
			$resultData['getQuotaBytesUsed'] = $about->getQuotaBytesUsed();
			$resultData['getMaxUploadSizes'] = $about->getMaxUploadSizes();
			
		} catch (Exception $e) {
			$resultData['notice']['error'][] = $e->getMessage();
		}
		
		return $resultData;
	}
	
	
	
	public function googleDrive_GetFile($input_file_id, $input_destination_file_path, $input_options = false)
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		$resultData['status'] = false;
		$resultData['data'] = '';
		
		if($this->isAuthorizedAccount) {
			$fileObj = $this->googleServiceDriveObj->files->get($input_file_id);
			if($fileObj) {
				$downloadUrl = $fileObj->getDownloadUrl();
				if ($downloadUrl) {
					
					$request = new Google_Http_Request($downloadUrl, 'GET', null, null);

					$signHttpRequest = $this->googleClientObj->getAuth()->sign($request);
					$httpRequest = $this->googleClientObj->getIo()->makeRequest($signHttpRequest);
					
					if ($httpRequest->getResponseHttpCode() == 200) {
				
						$resultData['data'] = $httpRequest->getResponseBody();
						
						if($resultData['data']) {
							if($input_destination_file_path) {
								$input_destination_file_path = PepVN_Data::createFolder($input_destination_file_path);
								if($input_destination_file_path) {
									@file_put_contents($input_destination_file_path, $resultData['data']);
									//echo ' <h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($resultData),'</pre> '; exit();
									if(file_exists($input_destination_file_path)) {
										$resultData['data'] = $input_destination_file_path;
									}
								}
							}
						}
						
					}
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	
	public function googleDrive_DeleteFile($input_file_id, $input_options = false)
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		$resultData['status'] = false;
				
		if($this->isAuthorizedAccount) {
			$rsOne = false;
			try {
				$rsOne = $this->googleServiceDriveObj->files->delete($input_file_id);
				$resultData['status'] = true;
			} catch (Exception $e) {
				$resultData['notice']['error'][] = $e->getMessage();
				$rsOne = false;
			}
			
		
		}
		
		return $resultData;
	}
	
	
	
	
	public function googleDrive_CreateFile($input_source_file_path, $input_destination_file_path, $input_options = false)
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		$resultData['status'] = false;
		
		$resultData['file_created']['result'] = false;
		
		if($this->isAuthorizedAccount) {
			//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($input_source_file_path, $input_destination_file_path),'</pre>';
			$input_destination_folder_path = PepVN_Data::getFolderPath($input_destination_file_path);
			if($input_destination_folder_path) {
				$input_destination_folder_path = PepVN_Data::fixPath($input_destination_folder_path);
				if($input_destination_folder_path) {
					if($input_source_file_path) {
						//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($input_source_file_path, $input_destination_file_path),'</pre>';
						if(PepVN_Data::isAllowRead($input_source_file_path)) {
							$input_destination_file_basename = basename($input_destination_file_path);
							//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($input_destination_file_basename),'</pre>';
							if($input_destination_file_basename) {								
								$rsOne = $this->googleDrive_CreateFolder($input_destination_folder_path, array());
								//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($rsOne),'</pre>';
								if(isset($rsOne['last_folders_created']['folder_id']) && $rsOne['last_folders_created']['folder_id']) {
									
									$sourceFileHandle = fopen($input_source_file_path,'rb');
									if($sourceFileHandle) {
										$chunkSizeBytes = 1 * 1024 * 1024; //x MB
										
										$googleFileObj = new Google_Service_Drive_DriveFile();
										$googleFileObj->title = $input_destination_file_basename;
										$googleFileObj->description = $input_destination_file_basename;
										
										$googleParentOfFileObj = new Google_Service_Drive_ParentReference();
										$googleParentOfFileObj->setId($rsOne['last_folders_created']['folder_id']);
										$googleFileObj->setParents(array($googleParentOfFileObj));
										
										
										
										$googleClientObj = $this->googleClientObj;
										$googleClientObj->setDefer(true);
										$googleServiceDriveObj = new Google_Service_Drive($googleClientObj);
										
										$googleRequest = $googleServiceDriveObj->files->insert($googleFileObj);

										$googleMedia = new Google_Http_MediaFileUpload(
											$googleClientObj,
											$googleRequest,
											null,	//mimeType
											null,	//data
											true,	//resumable
											$chunkSizeBytes	//chunkSize
										);
										$googleMedia->setFileSize(filesize($input_source_file_path));
										
										
										$nextChunkStatus = false;
										while (!$nextChunkStatus && !feof($sourceFileHandle)) {
											$chunkData = fread($sourceFileHandle, $chunkSizeBytes);
											$nextChunkStatus = $googleMedia->nextChunk($chunkData);
										}
										
										

										// The final value of $status will be the data from the API for the object
										// that has been uploaded.
										$googleResult = false;
										if ($nextChunkStatus != false) {
											$googleResult = $nextChunkStatus;
										}

										fclose($sourceFileHandle);
										$sourceFileHandle = 0;
										
										$resultData['file_created']['result'] = $googleResult;
										//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($googleResult),'</pre>';
									}
									
								}
							}
								
							
						}
					}
				}
			}
			
			
			
			
		}
		
		return $resultData;
	}
	
	
	public function googleDrive_CreateFolder($input_folder_path, $input_options = false)
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		$resultData['status'] = false;
		
		$resultData['folders_created'] = array();
		$resultData['last_folders_created'] = false;
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
		));
		
		if($this->isAuthorizedAccount) {
			
			$input_folder_path = PepVN_Data::fixPath($input_folder_path);
			
			$input_folder_path = explode('/',$input_folder_path);
			
			$input_folder_path = PepVN_Data::cleanArray($input_folder_path);
			
			if(count($input_folder_path) > 0) {
				
				$lastFolderId = false;
				
				$checkStatus1 = true;
				
				foreach($input_folder_path as $valueOne) {
					
					$folder_name = trim($valueOne);
					
					if(strlen($folder_name) > 0) {
						//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($this->cacheData[$keyCache1][$folder_name]),'</pre>';
						if(isset($this->cacheData[$keyCache1][$folder_name]) && ($this->cacheData[$keyCache1][$folder_name])) {
							
							$resultData['folders_created'][] = $this->cacheData[$keyCache1][$folder_name];
							$resultData['last_folders_created'] = $this->cacheData[$keyCache1][$folder_name];
							
						} else {
							
							$checkStatus2 = true;
							
							//create folder
							$folder_mime = 'application/vnd.google-apps.folder';
							
							
							$rsOne = $this->googleDrive_GetFirstAvailableObject(implode(' and ', array(
								'(mimeType = "'.$folder_mime.'")'
								,'(title = "'.$folder_name.'")'
								,'(trashed = false)'
							)));
							
							if($rsOne['data']) {
								if(isset($rsOne['data']->id) && $rsOne['data']->id) {
									$lastFolderId = $rsOne['data']->id;
									
									$resultData['folders_created'][] = array(
										'folder_id' => $rsOne['data']->id
										,'folder_name' => $folder_name
									);
									
									$resultData['last_folders_created'] = array(
										'folder_id' => $rsOne['data']->id
										,'folder_name' => $folder_name
									);
									
									$this->cacheData[$keyCache1][$folder_name] = array(
										'folder_id' => $rsOne['data']->id
										,'folder_name' => $folder_name
									);
									$checkStatus2 = false;
								}
							}
							
							if($checkStatus2) {
								
								$folderObj = new Google_Service_Drive_DriveFile();
								$folderObj->setTitle($folder_name);
								$folderObj->setMimeType($folder_mime);
								$folderObj->setDescription($folder_name);
								
								if($lastFolderId) {
									$folderParentObj = new Google_Service_Drive_ParentReference();
									$folderParentObj->setId($lastFolderId);
									$folderObj->setParents(array($folderParentObj));
								}
								
								$insertData = false;
								
								try {
									$insertData = $this->googleServiceDriveObj->files->insert($folderObj);
								} catch (Exception $e) {
									$resultData['notice']['error'][] = $e->getMessage();
								}
								
								if($insertData && isset($insertData->id) && ($insertData->id)) {
									$lastFolderId  = $insertData->id;
									
									$resultData['folders_created'][] = array(
										'folder_id' => $insertData->id
										,'folder_name' => $folder_name
									);
									$resultData['last_folders_created'] = array(
										'folder_id' => $insertData->id
										,'folder_name' => $folder_name
									);
									
									$this->cacheData[$keyCache1][$folder_name] = array(
										'folder_id' => $insertData->id
										,'folder_name' => $folder_name
									);
								} else {
									$checkStatus1 = false;
									break;
								}
								
							}
						}
					}
				}
				
				if($checkStatus1) {
					$resultData['status'] = true;
				}
			}
		}
		
		if($resultData['status']) {
			if(isset($resultData['notice']['error'])) {
				unset($resultData['notice']['error']);
			}
		}
		
		return $resultData;
	}
	
	
	
	
	
	public function googleDrive_GetFirstAvailableObject($input_query) 
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		
		$resultData['data'] = false;
		if($this->isAuthorizedAccount) {
			
			$rsOne = $this->googleDrive_FindObject($input_query);
			
			$resultData = PepVN_Data::mergeArrays(array(
				$resultData
				,$rsOne
			));
			$resultData['data'] = false;
			if(isset($rsOne['data']['list_objects'][0]) && $rsOne['data']['list_objects'][0]) {
				if(isset($rsOne['data']['list_objects'][0]) && $rsOne['data']['list_objects'][0]->id) {
					$resultData['data'] = $rsOne['data']['list_objects'][0];
				}
			}
			
		}
		
		
		return $resultData;
		
	}
	
	public function googleDrive_FindObject($input_query) 
	{
		$resultData = array();
		
		$rsOne = $this->googleDrive_Init(array());
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsOne
		));
		
		
		$resultData['data'] = false;
		
		if($this->isAuthorizedAccount) {
			
			$rsOne = $this->googleDrive_RetrieveAllFiles(array(
				'q' => $input_query
			));
			$resultData = PepVN_Data::mergeArrays(array(
				$resultData
				,$rsOne
			));
		}
		
		return $resultData;
	}
	
	
	
	
	
	/**
	* Retrieve a list of File resources.
	*
	* @param Google_DriveService $service Drive API service instance.
	* @return Array List of Google_DriveFile resources.
	*/
	private function googleDrive_RetrieveAllFiles($input_parameters) 
	{
		
		$resultData = array();
		
		$resultData['data']['list_objects'] = false;
		
		if($this->isAuthorizedAccount) {
			
			$resultData['data']['list_objects'] = array();
			$pageToken = NULL;

			do {
				try {
					$checkStatus1 = false;
					
					$parameters = array();
					if ($pageToken) {
						$parameters['pageToken'] = $pageToken;
					}
					if(isset($input_parameters['q'])) {
						$parameters['q'] = $input_parameters['q'];
					}
					$googleServiceDriveObj = $this->googleServiceDriveObj;
					$files = $googleServiceDriveObj->files->listFiles($parameters);
					if($files) {
					
						if(method_exists($files,'getItems')) {
							
							$checkStatus1 = true;
							
							$rsGetItems = $files->getItems();
							if($rsGetItems && is_array($rsGetItems)) {
								
								$resultData['data']['list_objects'] = array_merge($resultData['data']['list_objects'], $rsGetItems);
								
							}
						}
						
						if(method_exists($files,'getNextPageToken')) {
							$checkStatus1 = true;
							$pageToken = $files->getNextPageToken();
						}
					}
					if(!$checkStatus1) {
						$pageToken = NULL;
					}
				} catch (Exception $e) {
					$resultData['notice']['error'][] = $e->getMessage();
					$pageToken = NULL;
				}
				
				sleep(1);
			} while ($pageToken);
		}
		
		return $resultData;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}//class PepVN_Dropbox


endif; //if ( !class_exists('PepVN_Dropbox') )



