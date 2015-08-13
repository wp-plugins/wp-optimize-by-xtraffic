<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Service\Mobile_Detect
	,WpPepVN\TempData
;

class Device extends TempData
{
	public $mobileDetectObject = false;
	
	public function __construct() 
    {
		parent::__construct();
		$this->mobileDetectObject = new Mobile_Detect();
		
	}
    
	public function isMobile() 
    {
		return $this->mobileDetectObject->isMobile();
	}
	
	public function isTablet() 
    {
		return $this->mobileDetectObject->isTablet();
	}
	
	public function is($data) 
    {
		// Alternative to magic methods. $detect->is('iphone');
		return $this->mobileDetectObject->is($data);
	}
	
	public function version($data) 
    {
		// Find the version of component. $detect->version('Android');
		return $this->mobileDetectObject->version($data);
	}
	
	public function match($data) 
    {
		// Additional match method. $detect->match('regex.*here');
		return $this->mobileDetectObject->match($data);
	}
	
	public function get_device_screen_width()
	{
		
		$device_screen_width = 0;	//pixel
	
		$cookieKey = 'xtrdvscwd';
		$screenWidthCookie = false;
		if(isset($_COOKIE[$cookieKey]) && $_COOKIE[$cookieKey]) {
			$screenWidthCookie = $_COOKIE[$cookieKey];
			$screenWidthCookie = (int)$screenWidthCookie;
		}
		
		if(
			$screenWidthCookie
			&& ($screenWidthCookie>0)
		) {
			$device_screen_width = $screenWidthCookie;
		} else {
			
			$httpUserAgent = '';
			
			if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']) {
				$httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
			}
			
			$deviceVersion = false;
			
			$isGooglePageSpeedStatus = false;
			if(false !== stripos($httpUserAgent, 'Google Page Speed')) {
				$isGooglePageSpeedStatus = true;
			}
			
			if ( $this->isMobile() ) { //mobile or tablet
				if($this->isTablet()) {
					
					$device_screen_width = 960;
					
					if($this->is('Kindle')) {	//Kindle
						$device_screen_width = 1024;
					} else if($this->is('iPad')) {	//iPad
						$device_screen_width = 1024;
						
						$deviceVersion = $this->version('iPad');
						
						if($deviceVersion) {
							if(0 === stripos($deviceVersion,'4_')) {	//iPad 1/2/mini
								$device_screen_width = 2048;
							}
						}
					} else if(false !== stripos($httpUserAgent,'Nexus 10')) { //Nexus 10
						$device_screen_width = 2560;
					} else if(false !== stripos($httpUserAgent,'Nexus 7')) { //Nexus 7
						$device_screen_width = 1280;
					}
					
				} else {	//is mobile phone
					
					$device_screen_width = 320;
					
					if($isGooglePageSpeedStatus) {
						$device_screen_width = 320;
					} else if($this->is('iPhone')) {
						
						$device_screen_width = 320;
						
						$deviceVersion = $this->version('iPhone');
						if($deviceVersion) {
							if(0 === stripos($deviceVersion,'8_')) { //iPhone 6
								$device_screen_width = 750;
							} else if(0 === stripos($deviceVersion,'4_')) { //iPhone 4
								$device_screen_width = 640;
							}
						}
					} else if(false !== stripos($httpUserAgent,'BB10; Touch')) { //BlackBerry Z10
						$device_screen_width = 768; 
					} else if(false !== stripos($httpUserAgent,'Nexus 4')) { //Nexus 4
						$device_screen_width = 768;
					} else if(false !== stripos($httpUserAgent,'Nexus 5')) { //Nexus 5
						$device_screen_width = 1080;
					} else if(false !== stripos($httpUserAgent,'Nexus S')) { //Nexus S
						$device_screen_width = 480;
					} else if(false !== stripos($httpUserAgent,'Nokia')) { //Nokia
						$device_screen_width = 360;
						if(false !== stripos($httpUserAgent,'Lumia')) { //Nokia Lumia
							$device_screen_width = 480;
						}
					}
				}
				
			} else {	//desktop
				if($isGooglePageSpeedStatus) {
					$device_screen_width = 1024;
				}
			}
			
		}
		
		$device_screen_width = (int)$device_screen_width;
		
		return $device_screen_width;
	}
	
}