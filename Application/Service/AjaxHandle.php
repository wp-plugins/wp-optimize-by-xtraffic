<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Model\WpOptions
	,WPOptimizeByxTraffic\Application\Service\Cronjob as ServiceCronjob
	,WpPepVN\Utils
	,WpPepVN\Hash
	,WpPepVN\DependencyInjectionInterface
;

class AjaxHandle
{
	
	protected static $_tempData = array();
	
	public $di;
	
    public function __construct(DependencyInjectionInterface $di) 
    {
		$this->di = $di;
		
	}
    
	public function run() 
    {
		
		$wpExtend = $this->di->getShared('wpExtend');
		$hook = $this->di->getShared('hook');
		
		$resultData = array(
			'status' => 1
		);
		
		$dataSent = PepVN_Data::getDataSent();
		
		if($dataSent && isset($dataSent['localTimeSent']) && $dataSent['localTimeSent']) {
			
			if(isset($dataSent['preview_optimize_traffic_modules']) && $dataSent['preview_optimize_traffic_modules']) {
				
				if($wpExtend->is_admin()) {
					if($wpExtend->isCurrentUserCanManagePlugin()) {
						$optimizeTraffic = $this->di->getShared('optimizeTraffic');
						
						$rsOne = $optimizeTraffic->preview_optimize_traffic_modules($dataSent);
						
						$resultData = Utils::mergeArrays(array(
							$resultData
							,$rsOne
						));
						
						unset($rsOne);
					}
				}
				
			}
			
		}
		
		
		if($hook->has_filter('ajax')) {
			$rsOne = $hook->apply_filters('ajax', $dataSent);
			if(is_array($rsOne)) {
				$resultData = Utils::mergeArrays(array(
					$resultData
					,$rsOne
				));
			}
			unset($rsOne);
		}
		
		
		if(isset($dataSent['cronjob']['status']) && $dataSent['cronjob']['status']) {
			$cronjob = new ServiceCronjob($this->di);
			$cronjob->run();
		}
		
		if(
			isset($resultData['notice']['success'])
			&& ($resultData['notice']['success'])
			&& is_array($resultData['notice']['success'])
		) {
			$resultData['notice']['success'] = array_unique($resultData['notice']['success']);
		}
		
		if(
			isset($resultData['notice']['error'])
			&& ($resultData['notice']['error'])
			&& is_array($resultData['notice']['error'])
		) {
			$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		}
		
		PepVN_Data::encodeResponseData($resultData,true);
		
		unset($resultData);
		
		ob_end_flush();
		
		exit();
		
	}
	
	
}

