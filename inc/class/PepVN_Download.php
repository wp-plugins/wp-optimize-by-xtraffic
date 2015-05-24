<?php

/*
 * PepVN_Download v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
 
 


if ( !class_exists('PepVN_Download') ) :


class PepVN_Download
{

	public $max_bandwidth = 0;
	public $max_speed = 0;	// bytes/seconds
	public $file_mime = 'application/octet-stream';
	
	public $can_resume_status = true;
	public $resume_status = false;
	public $buffer_size = 0; //bytes;
	
	public $seek_start = 0;
	public $seek_end = -1;
	
	private $filename = '';
	private $data_modified = '';
	
	private $total_bandwidth_downloaded = 0;
	private $serverProtocol = 'HTTP/1.0';
	
	
	function __construct($input_parameters = false)
	{
		$this->serverProtocol = 'HTTP/1.0';
		if(isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL']) {
			$this->serverProtocol = $_SERVER['SERVER_PROTOCOL'];
		}
		$this->serverProtocol = (string)$this->serverProtocol;
		
		
		
	}
	
	
	private function init() 
	{
		$status1 = @set_time_limit(0);
		if(!$status1) {
			$status1 = @ini_set('max_execution_time','0');
		}
		
		$status1 = @ini_set('memory_limit','-1');
		
		
		global $HTTP_SERVER_VARS;
		
		
		if (
			$this->can_resume_status
			&& (
				isset($_SERVER['HTTP_RANGE']) 
				|| isset($HTTP_SERVER_VARS['HTTP_RANGE'])
			)
		) {
			
			if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
				$seek_range = substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes='));
			} else {
				$seek_range = substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));
			}
			
			$range = explode('-',$seek_range);
			
			$range[0] = (int)$range[0];
			if ($range[0] > 0) {
				$this->seek_start = (int)$range[0];
			}
			
			$range[1] = (int)$range[1];
			if ($range[1] > 0) {
				$this->seek_end = (int)$range[1];
			}
			
			$this->resume_status = true;
			
		} else {
			$this->seek_start = 0;
			$this->seek_end = -1;
		}
		
	}
	
	
	/**
	 * Flush header
	 **/
	private function flushHeader($input_parameters) 
	{
		
		header( 'Cache-Control: public' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Type: ' . $input_parameters['file_mime']);
		header( 'Accept-Ranges: bytes' );
		header('Content-Disposition: attachment; filename="' . $input_parameters['file_name'] . '"');
		header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $input_parameters['modified']));
		
		if ($this->resume_status) {
			header($this->serverProtocol.' 206 Partial Content');
			header('Status: 206 Partial Content');
			header('Accept-Ranges: bytes');
			header('Content-Range: bytes '.$input_parameters['seek_start'].'-'.$input_parameters['seek_end'].'/'.$input_parameters['size']);
			header('Content-Length: ' . ($input_parameters['seek_end'] - $input_parameters['seek_start'] + 1));
		} else {
			header('Content-Length: '.$input_parameters['size']);
		}
		
	}
	
	
	private function flushHeader404() 
	{
		header($this->serverProtocol.' 404 Not Found', true, 404);
	}
	
	/**
	 * Flush download file content
	 **/
	public function downloadFile($input_file_path) 
	{
		$this->init();
		
		$checkStatus1 = false;
		
		if (
			$input_file_path
			&& is_readable($input_file_path) 
			&& is_file($input_file_path)
		) {
			$checkStatus1 = true;
		}
		
		$this->total_bandwidth_downloaded = 0;
		
		if($checkStatus1) {
		
			$fileModifiedTime = filemtime($input_file_path);
			
			$seek_start = $this->seek_start;
			$seek_end = $this->seek_end;
			
			//do some clean up
			@ob_start();
			@ob_end_clean();
			$old_ignore_user_abort = ignore_user_abort(true);
			
			$fileSize = filesize($input_file_path);
			
			if ($seek_start > ($fileSize - 1)) {
				$seek_start = 0;
			}
			
			$fileBasename = basename($input_file_path);
			
			
			$fileHandle = @fopen($input_file_path,'rb');
			
			if($fileHandle) {
				
				$max_speed = $this->max_speed;	// bytes per seconds
				$max_speed = (int)$max_speed;
				
				$buffer_size = $this->buffer_size;	//bytes
				$buffer_size = (int)$buffer_size;
				
				if($max_speed > 0) {
					if($max_speed < 1024) {
						$max_speed = 1024;
					}
				}
				
				if(!$buffer_size) {
					if($max_speed > 0) {
						$buffer_size = $max_speed / 2;
						$buffer_size = (int)$buffer_size;
					} else {
						$buffer_size = 1 * 1024 * 1024; // bytes; x KB
					}
				}
				
				$buffer_size = (int)$buffer_size;
				$max_speed = (int)$max_speed;
				
				if ($seek_start > 0) {
					fseek($fileHandle , $seek_start);
				}
				
				if ($seek_end < $seek_start) {
					$seek_end = $fileSize - 1;
				}
				
				$this->flushHeader(array(
					'file_mime' => 'application/octet-stream'
					,'file_name' => $fileBasename
					,'modified' => $fileModifiedTime
					,'seek_start' => $seek_start
					,'seek_end' => $seek_end
					,'size' => $fileSize
				));
				
				
				$sizeFlush = $seek_end - $seek_start + 1;
				
				$timeStartDownload = microtime(true);
				$timeStartDownload = (float)$timeStartDownload;
				
				while (
					(
						(!connection_aborted())
						|| (0 == connection_status())
					)
					&& ($sizeFlush > 0)
				) {
					if ($sizeFlush < $buffer_size) {
						echo @fread($fileHandle , $sizeFlush);
						$this->total_bandwidth_downloaded += $sizeFlush;
					} else {
						echo @fread($fileHandle , $buffer_size);
						$this->total_bandwidth_downloaded += $buffer_size;
					}
					
					$sizeFlush -= $buffer_size;
					
					@ob_end_flush(); 
					@ob_flush(); 
					@flush(); 
					@ob_start();
					
					if($max_speed > 0) {
						$periodTimeSpend = abs((float)microtime(true) - $timeStartDownload);
						//echo ' <h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($periodTimeSpend,$timeStartDownload),'</pre> '; 
						if($periodTimeSpend > 0) {
							$speedDownload = $this->total_bandwidth_downloaded / $periodTimeSpend;
							$speedDownload = (int)$speedDownload;
							//echo ' <h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($speedDownload),'</pre> '; 
							if($speedDownload > 0) {
								$ratioMaxSpeedPerSpeedDownload = $max_speed / $speedDownload;
								$ratioMaxSpeedPerSpeedDownload = (float)$ratioMaxSpeedPerSpeedDownload;
								//echo ' <h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($ratioMaxSpeedPerSpeedDownload),'</pre> '; 
								if($ratioMaxSpeedPerSpeedDownload < 1) {
									$timeSecondsNeedSleep = (1 - $ratioMaxSpeedPerSpeedDownload);
									$timeSecondsNeedSleep = (float)$timeSecondsNeedSleep;
									$timeSecondsNeedSleep = $timeSecondsNeedSleep * 1000000;
									$timeSecondsNeedSleep = (int)$timeSecondsNeedSleep;
									//echo ' <h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($timeSecondsNeedSleep),'</pre> '; 
									usleep($timeSecondsNeedSleep);
								}
							}
						}
					}
					
				}
				
				fclose($fileHandle);
				
			}
			
			$fileHandle = 0;
			
			ignore_user_abort($old_ignore_user_abort);
			
		} else {
			$this->flushHeader404();
		}
			
		exit(); die();
		
		
	}
	
	
}//class PepVN_Download


endif; //if ( !class_exists('PepVN_Download') )



