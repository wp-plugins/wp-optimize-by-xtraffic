<?php

/*
 * PepVN_FTP v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
 
 


if ( !class_exists('PepVN_FTP') ) :

class PepVN_FTP
{
	
	private $ftpConnectionIdObj = false;
	private $bufferSizeBytesForFTPUploadFile = 0;
	private $connectionMethod = '';
	private $sftpConnectionObj = false;
	
	
	function __construct($input_parameters)
	{
		//global $wpOptimizeByxTraffic;
		
		$ftpTimeout = 30 * 60;
		
		/*
		if($wpOptimizeByxTraffic) {
			$valueTemp = $wpOptimizeByxTraffic->base_get_available_server_time_for_run();
			if($valueTemp > 0) {
				$valueTemp = $valueTemp * 0.8;
				$valueTemp = ceil($valueTemp);
				$ftpTimeout = $valueTemp;
			}
		}
		*/
		
		$this->bufferSizeBytesForFTPUploadFile = 2 * 1024 * 1024;
		
		$ftpTimeout = (int)$ftpTimeout;
		
		$isLoginSuccessStatus = false;
		
		$checkStatus1 = false;
		
		if(function_exists('ftp_connect')) {
			if(
				isset($input_parameters['ftp_host'])
				&& ($input_parameters['ftp_host'])
				
				&& isset($input_parameters['ftp_username'])
				&& ($input_parameters['ftp_username'])
				
				&& isset($input_parameters['ftp_password'])
				&& ($input_parameters['ftp_password'])
			) {
				$checkStatus1 = true;
				
				$this->connectionMethod = 'ftp';
				
				if(
					isset($input_parameters['ftp_sftp'])
					&& ($input_parameters['ftp_sftp'])
				) {
					if(
						function_exists('ssh2_connect')
						&& function_exists('ssh2_auth_password')
					) {
						$this->connectionMethod = 'sftp';
					} else {
						$checkStatus1 = false;
					}
				} else if(
					isset($input_parameters['ftp_ssl'])
					&& ($input_parameters['ftp_ssl'])
				) {
					if(
						extension_loaded('openssl')
						&& function_exists('ftp_ssl_connect')
						&& function_exists('openssl_open')
					) {
						$this->connectionMethod = 'ftp';
					} else {
						$checkStatus1 = false;
					}
				}
				
				
			}
		}
		
		//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($checkStatus1,$input_parameters,function_exists('ssh2_connect')),'</pre>';
		if($checkStatus1) {
		
			if(!isset($input_parameters['ftp_port'])) {
				if('sftp' === $this->connectionMethod) {
					$input_parameters['ftp_port'] = '21';
				} else {
					$input_parameters['ftp_port'] = '21';
				}
			}
			$input_parameters['ftp_port'] = (string)$input_parameters['ftp_port'];
			
			
			if(!isset($input_parameters['ftp_timeout'])) {
				$input_parameters['ftp_timeout'] = $ftpTimeout;
			}
			$input_parameters['ftp_timeout'] = abs((int)$input_parameters['ftp_timeout']);
			if($input_parameters['ftp_timeout'] < 15) {
				$input_parameters['ftp_timeout'] = 15;
			}
			
			if('sftp' === $this->connectionMethod) {
				$this->sftpConnectionObj = ssh2_connect($input_parameters['ftp_host'], $input_parameters['ftp_port']);
				if($this->sftpConnectionObj) {
					if(ssh2_auth_password($this->sftpConnectionObj, $input_parameters['ftp_username'], $input_parameters['ftp_password'])) {
						$this->ftpConnectionIdObj = ssh2_sftp($this->sftpConnectionObj);
						if($this->ftpConnectionIdObj) {
							$isLoginSuccessStatus = false;
						}
					}
				}
				
			} else {
				
				if(
					isset($input_parameters['ftp_ssl'])
					&& ($input_parameters['ftp_ssl'])
				) {
					$this->ftpConnectionIdObj = @ftp_ssl_connect(
						$input_parameters['ftp_host']
						, $input_parameters['ftp_port']
						, $input_parameters['ftp_timeout']
					);
					//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($this->ftpConnectionIdObj),'</pre>';
				} else {
					$this->ftpConnectionIdObj = @ftp_connect(
						$input_parameters['ftp_host']
						, (int)$input_parameters['ftp_port']
						, $input_parameters['ftp_timeout']
					);
				}
				
				//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($this->ftpConnectionIdObj),'</pre>';
				
				if($this->ftpConnectionIdObj) {
					
					if($ftpTimeout > 0) {
						ftp_set_option($this->ftpConnectionIdObj, FTP_TIMEOUT_SEC, $ftpTimeout);
					}
					
					if(
						isset($input_parameters['ftp_passive_mode'])
						&& ($input_parameters['ftp_passive_mode'])
					) {
						@ftp_pasv($this->ftpConnectionIdObj , true );
					}
					
					if(@ftp_login($this->ftpConnectionIdObj, $input_parameters['ftp_username'], $input_parameters['ftp_password'])) {
						$isLoginSuccessStatus = true;
					} else {
						@ftp_close($this->ftpConnectionIdObj);
					}
				}
			}
		}
		
		
		
		
		if($isLoginSuccessStatus) {
			
			
			
			/*
			if($wpOptimizeByxTraffic) {
				$this->bufferSizeBytesForFTPUploadFile = $wpOptimizeByxTraffic->base_get_safe_buffer_size_bytes_for_read_write_data();
				$this->bufferSizeBytesForFTPUploadFile = abs((int)$this->bufferSizeBytesForFTPUploadFile);
				if($this->bufferSizeBytesForFTPUploadFile < 1024) {
					$this->bufferSizeBytesForFTPUploadFile = 1024;
				}
				
			}
			*/
			
		} else {
			return false;
		}
		
	}
	
	public function ftp_close()
	{
		
		if('sftp' === $this->connectionMethod) {
			if($this->sftpConnectionObj) {
				if(
					function_exists('ssh2_exec')
				) {
					ssh2_exec($this->sftpConnectionObj,'exit');
				}
			}
			
			unset($this->sftpConnectionObj);
			
		} else {
			if($this->ftpConnectionIdObj) {
				@ftp_close($this->ftpConnectionIdObj);
			}
		}
		
		unset($this->ftpConnectionIdObj);
	}
	
	public function ftp_upload($input_parameters)
	{
		//global $wpOptimizeByxTraffic;
		
		$resultData = array();
		$resultData['upload_status'] = false;
		$resultData['source_file_position'] = 0;
		
		if($this->ftpConnectionIdObj) {
			if(isset($input_parameters['source_file_path']) && $input_parameters['source_file_path']) {
				if(isset($input_parameters['destination_file_path']) && $input_parameters['destination_file_path']) {
					if(PepVN_Data::isAllowRead($input_parameters['source_file_path'])) {
						$sourceFileSize = filesize($input_parameters['source_file_path']);
						$sourceFileSize = (int)$sourceFileSize;
					
						$bufferSizeBytes = $this->bufferSizeBytesForFTPUploadFile;
						
						if(!isset($input_parameters['source_file_position'])) {
							$input_parameters['source_file_position'] = 0;
						}
						
						$arrayDirsDestination = $input_parameters['destination_file_path'];
						$arrayDirsDestination = dirname($arrayDirsDestination);
						$arrayDirsDestination = PepVN_Data::fixPath($arrayDirsDestination);
						$arrayDirsDestination = explode(DIRECTORY_SEPARATOR,$arrayDirsDestination);
						$arrayDirsDestination = PepVN_Data::cleanArray($arrayDirsDestination);
						
						
						
						if('sftp' === $this->connectionMethod) {
							
							if(count($arrayDirsDestination) > 0) {
								$dirNeedCreate = '';
								foreach($arrayDirsDestination as $valueOne) {
									$dirNeedCreate .= DIRECTORY_SEPARATOR . $valueOne;
									ssh2_sftp_mkdir($this->ftpConnectionIdObj, $dirNeedCreate, 0666, true);
								}
							}
							
							
							
							$uploadStatus = @ssh2_scp_send($this->sftpConnectionObj, $input_parameters['source_file_path'], $input_parameters['destination_file_path'], 0644);
							if($uploadStatus) {
								sleep(1);
								$destinationStatInfo = @ssh2_sftp_stat($this->ftpConnectionIdObj, $input_parameters['destination_file_path']);
								if(isset($destinationStatInfo['size']) && $destinationStatInfo['size']) {
									$destinationStatInfo['size'] = (int)$destinationStatInfo['size'];
									if($destinationStatInfo['size'] === $sourceFileSize) {
										$resultData['source_file_position'] = $sourceFileSize;
									}
								}
								
								
							}
															
						} else {
						
							if(count($arrayDirsDestination) > 0) {
								$dirNeedCreate = '';
								foreach($arrayDirsDestination as $valueOne) {
									$dirNeedCreate .= DIRECTORY_SEPARATOR . $valueOne;
									$ftpNlist1 = ftp_nlist($this->ftpConnectionIdObj, $dirNeedCreate);
									if(false === $ftpNlist1) {
										ftp_mkdir($this->ftpConnectionIdObj, $dirNeedCreate);
									}
								}
							}
							
							$ftpFputStatus = ftp_put(
								$this->ftpConnectionIdObj
								, $input_parameters['destination_file_path']
								, $input_parameters['source_file_path']
								, FTP_BINARY
							);
							
							if($ftpFputStatus) {
								$resultData['upload_status'] = true;
							}
							
						}
						
						
					}
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	
	
	
	
	public function ftp_download($input_parameters)
	{
		//global $wpOptimizeByxTraffic;
		
		$resultData = array();
		
		$resultData['file_path'] = '';
		$resultData['file_size'] = 0;
		
		if($this->ftpConnectionIdObj) {
			if(isset($input_parameters['source_file_path']) && $input_parameters['source_file_path']) {
				if(isset($input_parameters['destination_file_path']) && $input_parameters['destination_file_path']) {
					$input_parameters['destination_file_path'] = PepVN_Data::createFolder($input_parameters['destination_file_path']);
					
					if($input_parameters['destination_file_path']) {
						$downloadStatus = false;
						
						if('sftp' === $this->connectionMethod) {
							
							$downloadStatus = @ssh2_scp_recv($this->sftpConnectionObj, $input_parameters['source_file_path'], $input_parameters['destination_file_path']);
							
							
						} else {
							$downloadStatus = ftp_get($this->ftpConnectionIdObj, $input_parameters['destination_file_path'], $input_parameters['source_file_path'], FTP_BINARY);
						}
						
						if($downloadStatus) {
							if(file_exists($input_parameters['destination_file_path'])) {
								$resultData['file_path'] = $input_parameters['destination_file_path'];
								$resultData['file_size'] = filesize($input_parameters['destination_file_path']);
							}
						}
					}
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	
	
	public function ftp_delete($input_parameters)
	{
		//global $wpOptimizeByxTraffic;
		
		$resultData = array();
		
		$resultData['status'] = false;
		
		
		if($this->ftpConnectionIdObj) {
			if(isset($input_parameters['file_path']) && $input_parameters['file_path']) {
				
				if('sftp' === $this->connectionMethod) {
					
					$status = ssh2_sftp_unlink($this->ftpConnectionIdObj, $input_parameters['file_path']);
					//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($status),'</pre>';
					if($status) {
						$resultData['status'] = true;
					}
					
				} else {
					$status = @ftp_delete($this->ftpConnectionIdObj, $input_parameters['file_path']);
					if ($status) {
						$resultData['status'] = true;
					}
					
				}
			}
		}
		
		return $resultData;
	}
	
	
	
	
	
}//class PepVN_FTP


endif; //if ( !class_exists('PepVN_FTP') )



