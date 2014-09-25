<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeSpeed.php');


if ( !class_exists('WPOptimizeByxTraffic_HeaderFooter') ) :


class WPOptimizeByxTraffic_HeaderFooter extends WPOptimizeByxTraffic_OptimizeSpeed 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
		
		
	}
	
	
	
	public function header_footer_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		$resultData['notice']['error_no'] = array();
		
		
		$rsTemp = $this->base_check_system_ready();
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsTemp
		));
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	
	public function header_footer_the_content_filter($text) 
	{
		if (!is_singular()) {
			return $text;
		}
	
		$options = $this->get_options(array(
			'cache_status' => 1
		));
				
		if(!isset($options['header_footer_code_add_before_articles_all'])) {
			$options['header_footer_code_add_before_articles_all'] = '';
		}
		$options['header_footer_code_add_before_articles_all'] = trim($options['header_footer_code_add_before_articles_all']);
		if(!isset($options['header_footer_code_add_after_articles_all'])) {
			$options['header_footer_code_add_after_articles_all'] = '';
		}
		$options['header_footer_code_add_after_articles_all'] = trim($options['header_footer_code_add_after_articles_all']);
		
		if($options['header_footer_code_add_before_articles_all'] || $options['header_footer_code_add_after_articles_all']) {
			
			if($options['header_footer_code_add_before_articles_all']) {
				$text = $options['header_footer_code_add_before_articles_all'] . $text;
			}
			
			if($options['header_footer_code_add_after_articles_all']) {
				$text = $text . $options['header_footer_code_add_after_articles_all'];
			}
			
		}
		
		return $text;
	}
	
	
	
	
	public function header_footer_the_head_filter() 
	{
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(!isset($options['header_footer_code_add_head_home'])) {
			$options['header_footer_code_add_head_home'] = '';
		}
		$options['header_footer_code_add_head_home'] = trim($options['header_footer_code_add_head_home']);
		
		if(!isset($options['header_footer_code_add_head_all'])) {
			$options['header_footer_code_add_head_all'] = '';
		}
		$options['header_footer_code_add_head_all'] = trim($options['header_footer_code_add_head_all']);
		
		if($options['header_footer_code_add_head_home'] || $options['header_footer_code_add_head_all']) {
			
			if($options['header_footer_code_add_head_home']) {
				if (is_home()) {
					echo $options['header_footer_code_add_head_home'];
				}
			}
			
			if($options['header_footer_code_add_head_all']) {
				echo $options['header_footer_code_add_head_all'];
			}
			
		}
		
	}
	
	
	
	public function header_footer_the_footer_filter() 
	{
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(!isset($options['header_footer_code_add_footer_home'])) {
			$options['header_footer_code_add_footer_home'] = '';
		}
		$options['header_footer_code_add_footer_home'] = trim($options['header_footer_code_add_footer_home']);
		
		if(!isset($options['header_footer_code_add_footer_all'])) {
			$options['header_footer_code_add_footer_all'] = '';
		}
		$options['header_footer_code_add_footer_all'] = trim($options['header_footer_code_add_footer_all']);
		
		if($options['header_footer_code_add_footer_home'] || $options['header_footer_code_add_footer_all']) {
			
			if($options['header_footer_code_add_footer_home']) {
				if (is_home()) {
					echo $options['header_footer_code_add_footer_home'];
				}
			}
			
			if($options['header_footer_code_add_footer_all']) {
				echo $options['header_footer_code_add_footer_all'];
			}
			
		}
		
	}
	
	

	public function header_footer_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];	

		$header_footer_code_add_head_home = $options['header_footer_code_add_head_home'];
		$header_footer_code_add_footer_home = $options['header_footer_code_add_footer_home'];
		$header_footer_code_add_head_all = $options['header_footer_code_add_head_all'];
		$header_footer_code_add_footer_all = $options['header_footer_code_add_footer_all'];
		$header_footer_code_add_before_articles_all = $options['header_footer_code_add_before_articles_all'];
		$header_footer_code_add_after_articles_all = $options['header_footer_code_add_after_articles_all'];
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
		
		
		$rsTemp = $this->header_footer_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2>WP Optimize By xTraffic (Header & Footer)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="header_footer_submitted" value="1" /> 
						
						<h3>Header</h3>
						
						<h6>',__('Code to be added on HEAD tag of the HOME PAGE ONLY',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </h6>
						<textarea name="header_footer_code_add_head_home" id="header_footer_code_add_head_home" rows="10" cols="90"  >',$header_footer_code_add_head_home,'</textarea>
						<br /><hr/><br />
						
						<h6>',__('Code to be added on HEAD tag of EVERY PAGES',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </h6>
						<textarea name="header_footer_code_add_head_all" id="header_footer_code_add_head_all" rows="10" cols="90"  >',$header_footer_code_add_head_all,'</textarea>
						<br /><hr/><br />
						
						
						
						
						<h3>Footer</h3>
						
						<h6>',__('Code to be added BEFORE THE END of the HOME PAGE ONLY',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </h6>
						<textarea name="header_footer_code_add_footer_home" id="header_footer_code_add_footer_home" rows="10" cols="90"  >',$header_footer_code_add_footer_home,'</textarea>
						<br /><hr/><br />
						
						
						<h6>',__('Code to be added BEFORE THE END of EVERY PAGES',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </h6>
						<textarea name="header_footer_code_add_footer_all" id="header_footer_code_add_footer_all" rows="10" cols="90"  >',$header_footer_code_add_footer_all,'</textarea>
						<br /><hr/><br />
						
						
						
						
						<h3>Article</h3>
						
						<h6>',__('Code to be inserted BEFORE each ARTICLE',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (Posts/Pages) : </h6>
						<textarea name="header_footer_code_add_before_articles_all" id="header_footer_code_add_before_articles_all" rows="10" cols="90"  >',$header_footer_code_add_before_articles_all,'</textarea>
						<br /><hr/><br />
						
						<h6>',__('Code to be inserted AFTER each ARTICLE',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (Posts/Pages) : </h6>
						<textarea name="header_footer_code_add_after_articles_all" id="header_footer_code_add_after_articles_all" rows="10" cols="90"  >',$header_footer_code_add_after_articles_all,'</textarea>
						<br /><hr/><br />
						
						<div class="submit"><input type="submit" name="Submit" value="',__('Update Options',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" class="button-primary" /></div>
						
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

'; 
		
		
	}
	
	


}//class WPOptimizeByxTraffic

endif; //if ( !class_exists('WPOptimizeByxTraffic') )



?>