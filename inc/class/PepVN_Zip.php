<?php

/*
 * PepVN_Zip v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
 
 

 
if (!defined('PCLZIP_TEMPORARY_DIR')) {
	$valueTemp = WPOPTIMIZEBYXTRAFFIC_CACHE_PATH . 'files' . DIRECTORY_SEPARATOR;
	define( 'PCLZIP_TEMPORARY_DIR', $valueTemp );
}
 
if ( !class_exists('PclZip') ) {
	require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/libs/pclzip.lib.php');
}


if ( !class_exists('PepVN_Zip') ) :


class PepVN_Zip
{

	public $initStatus = false;
	private $method = false;
	private $zipperObj = false;
	
	
	public function __construct($input_parameters)
	{
		
		if ( class_exists('PclZip') ) {
			$this->method = 'pclzip';
			$this->zipperObj = new PclZip($input_parameters['zip_path']);
		}
		
		if($this->method && $this->zipperObj) {
			$this->initStatus = true;
		}
		
		
		
	}
	
	
	public function add($input_parameters)
	{
		if(isset($input_parameters['add_path']) && $input_parameters['add_path']) {
			$input_parameters['add_path'] = (array)$input_parameters['add_path'];
			
			if(isset($input_parameters['remove_prefix_path']) && $input_parameters['remove_prefix_path']) {
				$this->zipperObj->add($input_parameters['add_path'],PCLZIP_OPT_REMOVE_PATH,$input_parameters['remove_prefix_path'],PCLZIP_OPT_ADD_TEMP_FILE_ON);
			} else {
				$this->zipperObj->add($input_parameters['add_path'],PCLZIP_OPT_ADD_TEMP_FILE_ON);
			}
		}
		
	}
	
	public function getZipperObj()
	{
		if($this->initStatus) {
			if($this->zipperObj) {
				return $this->zipperObj;
			}
		}
		
		return false;
	}
	
	

}//class PepVN_Zip


endif; //if ( !class_exists('PepVN_Zip') )



