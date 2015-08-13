<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Model\WpOptions
	,WpPepVN\Utils
	,WpPepVN\DependencyInjectionInterface
	,WPOptimizeByxTraffic\Application\Service\PepVN_Data
;

class Dashboard
{
	const OPTION_NAME = 'dashboard';
	
	protected static $_tempData = array();
	
	public $di;
	
    public function __construct(DependencyInjectionInterface $di) 
    {
		$this->di = $di;
	}
    
	public function initFrontend() 
    {
        
	}
	
	
	public function initBackend() 
    {
		
	}
	
	public static function getDefaultOption()
	{
		return array(
			'last_time_plugin_activation' => 0
		);
	}
	
	public static function getOption($cache_status = true)
	{
	
		return WpOptions::get_option(self::OPTION_NAME,self::getDefaultOption(),array(
			'cache_status' => $cache_status
		));
	}
	
	public static function updateOption($data)
	{
		return WpOptions::update_option(self::OPTION_NAME,$data);
	}
	
	public function on_plugin_activation()
	{
		self::updateOption(array(
			'last_time_plugin_activation' => PepVN_Data::$defaultParams['requestTime']
		));
	}
}
