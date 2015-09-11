<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WpPepVN\DependencyInjection
	,WpPepVN\Mvc\Controller as MvcController
;

class ControllerBase extends MvcController
{
	public function __construct() 
    {
		parent::__construct();
	}
	
	public function init(DependencyInjection $di) 
    {
		parent::init($di);
		
		$this->view->translate = $this->di->getShared('translate');
	}
	
	protected function _addNoticeSavedSuccess() 
    {
		$this->view->adminNotice->add_notice($this->view->translate->_('Options were saved successfully.'), 'success');
	}
	
	protected function _doAfterUpdateOptions() 
    {
		
		$cacheManager = $this->di->getShared('cacheManager');
		$cacheManager->registerCleanCache();
	}
}