<?php

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_Base.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeImages') ) :


class WPOptimizeByxTraffic_OptimizeImages extends WPOptimizeByxTraffic_Base 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
		
	}
	
	
	
	
	
	
	

	function optimize_images_process($text)
	{

		
		$keyCacheProcessText = PepVN_Data::createKey(array(
			__METHOD__
			,$text
			,'process_text'
		));
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessText);
		
		if($valueTemp) {
			return $valueTemp;
		}
		
		
		global $wpdb, $post;
		
		if(($post->post_type == 'post') || ($post->post_type == 'page')) {
			
		} else {
			return $text;
		}
		
		$parametersPrimary = array();
		
		$patternsEscaped = array();
		
		$options = $this->get_options();
		
		if(!isset($options['optimize_images_alttext'])) {
			$options['optimize_images_alttext'] = '';
		}
		if(!isset($options['optimize_images_titletext'])) {
			$options['optimize_images_titletext'] = '';
		}
		
		$optimize_images_alttext = trim($options['optimize_images_alttext']);
		$optimize_images_titletext = trim($options['optimize_images_titletext']);
		
		if($optimize_images_alttext || $optimize_images_titletext) {
		
		} else {
			return $text;
		}
		
		$patterns1 = array();
		
		$patterns1['%title'] = trim($post->post_title);
		$patterns1['%category'] = '';
		$patterns1['%tags'] = '';
		$patterns1['%img_title'] = '';
		$patterns1['%img_alt'] = '';
		$patterns1['%img_name'] = '';
		
		$postcats = get_the_category();
		if ($postcats) {
			foreach($postcats as $cat) {
				$patterns1['%category'] .= ' '.trim($cat->slug).' ';
			}
		}
		
		$posttags = get_the_tags();
		if ($posttags) {
			foreach($posttags as $tag) {
				$patterns1['%tags'] .= ' '.$tag->name.' ';
			}
		}
		
		
		foreach($patterns1 as $keyOne => $valueOne) {
			$valueOne = PepVN_Data::cleanKeyword($valueOne);
			$patterns1[$keyOne] = $valueOne;
		}
		
		
		$patternsReplace = array();
		
		
		if(preg_match_all('#<img[^>]+\\\?>#i', $text, $matched1)) {
			
			if(isset($matched1[0]) && is_array($matched1[0]) && (count($matched1[0])>0)) {
				
				foreach($matched1[0] as $keyOne => $valueOne) {
					
					$oldImgTag = $valueOne;
					$newImgTag = $valueOne;
					
					$imgTitle1 = '';
					$imgAlt1 = '';
					$imgName = '';
					
					if(preg_match('#title=(\'|")([^"\']+)\1#i',$valueOne,$matched2)) {
						if(isset($matched2[2]) && $matched2[2]) {
							$imgTitle1 = trim($matched2[2]);
						}
						
					}
					
					if(preg_match('#alt=(\'|")([^"\']+)\1#i',$valueOne,$matched2)) {
						if(isset($matched2[2]) && $matched2[2]) {
							$imgAlt1 = trim($matched2[2]);
						}
						
					}
					
					if(preg_match('#src=(\'|")(https?://[^"\']+)\1#i',$valueOne,$matched2)) {
						if(isset($matched2[2]) && $matched2[2]) {
							$imgName = trim($matched2[2]);
						}
						
					}
					
					$imgName = trim($imgName);
					if($imgName) {
						$imgInfo = pathinfo($imgName);
						if(isset($imgInfo['filename'])) {
							$imgName = $imgInfo['filename'];
						}
					}
					$imgName = trim($imgName);
					
					
					$imgTitle1 = PepVN_Data::cleanKeyword($imgTitle1);
					$imgAlt1 = PepVN_Data::cleanKeyword($imgAlt1);
					$imgName = PepVN_Data::cleanKeyword($imgName);
					
					
					$patterns2 = $patterns1;
					$patterns2['%img_title'] = $imgTitle1;
					$patterns2['%img_alt'] = $imgAlt1;
					$patterns2['%img_name'] = $imgName;
					
					$optimize_images_alttext1 = str_replace(array_keys($patterns2),array_values($patterns2),$optimize_images_alttext);
					$optimize_images_alttext1 = PepVN_Data::reduceSpace($optimize_images_alttext1);
					
					if($optimize_images_alttext1) {
						$newImgTag = preg_replace('#alt=(\'|")([^"\']+)?\1#i','',$newImgTag);
						if('on' != $options['optimize_images_override_alt']) {
							$optimize_images_alttext1 = $imgAlt1;
						}
						$newImgTag = preg_replace('#<img(.+)#is', '<img alt="'.$optimize_images_alttext1.'" \\1', $newImgTag);
					}
					
					
					$optimize_images_titletext1 = str_replace(array_keys($patterns2),array_values($patterns2),$optimize_images_titletext);
					$optimize_images_titletext1 = PepVN_Data::reduceSpace($optimize_images_titletext1);
					if($optimize_images_titletext1) {
						
						$newImgTag = preg_replace('#title=(\'|")([^"\']+)?\1#i','',$newImgTag);
						if('on' != $options['optimize_images_override_title']) {
							$optimize_images_titletext1 = $imgTitle1;
						}
						$newImgTag = preg_replace('#<img(.+)#is', '<img title="'.$optimize_images_titletext1.'" \\1', $newImgTag);
						
					}
					
					
					if($oldImgTag !== $newImgTag) {
						$patternsReplace[$oldImgTag] = $newImgTag;
					}
					
				}
			
			}
			
			
			if(count($patternsReplace)>0) {
				$text = str_replace(array_keys($patternsReplace),array_values($patternsReplace),$text);
			}
			
		}
		
		
		$text = trim($text);
		
		$this->cacheObj->set_cache($keyCacheProcessText,$text);
		
		return $text;

	} 

	
	
	

		
	function optimize_images_the_content_filter($text) 
	{
		
		$text = $this->optimize_images_process($text);
		
		return $text;
	}
	

	function optimize_images_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];	

		$optimize_images_override_alt = $options['optimize_images_override_alt'] == 'on' ? 'checked':'';
		$optimize_images_override_title = $options['optimize_images_override_title'] == 'on' ? 'checked':'';
		$optimize_images_alttext = $options['optimize_images_alttext'];
		$optimize_images_titletext = $options['optimize_images_titletext'];
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
		
		echo <<<END

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	
	<h2>WP Optimize By xTraffic (Optimize Images)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
				<form name="WPOptimizeByxTraffic" action="$action_url" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="optimize_images_submitted" value="1" /> 
						
						<h2>Overview "Optimize Images"</h2>
						
						<p>"Optimize Images" automatically adds alt and title attributes to all images in all posts specified by parameters below.</p>
						
						<p>
							You can enter any text in the field including these special tags :
						</p>
						
						<ul>
							<li>%title : post title</li>
							<li>%category : post category</li>
							<li>%tags : post tags</li>
							<li>%img_name : image file name (without extension)</li>
							<li>%img_title : image title attributes</li>
							<li>%img_alt : image alt attributes</li>
						</ul>
						
						<p></p>
						
						<h2>Setting</h2>
						
						<ul>
							<li>
								<label for="optimize_images_alttext"> ALT attribute (example: %img_name %title) </label>&nbsp;<br />
								<input type="text" name="optimize_images_alttext"  value="$optimize_images_alttext" title="ALT attribute" />
							</li>
							
							<li>
								<label for="optimize_images_titletext"> TITLE attribute (example: %img_name photo) </label>&nbsp;<br />
								<input type="text" name="optimize_images_titletext" value="$optimize_images_titletext" title="TITLE attribute" />
							</li>
							
							<li> &nbsp; </li>
							
							<li>
								
								<input type="checkbox" name="optimize_images_override_alt"  $optimize_images_override_alt /> &nbsp; 
								<label for="optimize_images_override_alt"> Override default Wordpress image alt tag (recommended) </label> 
							</li>
							<li>
								
								<input type="checkbox" name="optimize_images_override_title"  $optimize_images_override_title /> &nbsp; 
								<label for="optimize_images_override_title"> Override default Wordpress image title </label> 
							</li>
							
							
						</ul>
						
						<h4>Example</h4>
						
						<p>In a post titled <b>"Landscape Pictures"</b> there is a images named <b>"forest.jpg"</b></p>

						<p>Setting alt attribute to <b>"%img_name %title"</b> will set <b>alt="forest Landscape Pictures"</b></p>
						<p>Setting title attribute to <b>"%img_name image"</b> will set <b>title="forest image"</b></p>
						
						<br />
						
						<div class="submit"><input type="submit" name="Submit" value="Update options" class="button-primary" /></div>
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

END;
		
		
	}
	
	

	
	


}//class WPOptimizeByxTraffic_OptimizeImages

endif; //if ( !class_exists('WPOptimizeByxTraffic_OptimizeImages') )



?>