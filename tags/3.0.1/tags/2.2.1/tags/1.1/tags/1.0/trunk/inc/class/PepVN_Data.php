<?php

/*
 * PepVN_Data v1.0
 *
 * By PEP.VN
 * http://pep.vn/
 *
 * Free to use and abuse under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 */
 
 
 

if ( !class_exists('PepVN_Data') ) :


class PepVN_Data 
{

	public static $defaultParams = false;
	public static $cacheData = false;
	
	function __construct()
	{
		self::setDefaultParams(); 
	}
	
	
	public static function setDefaultParams()
	{
		if(!self::$defaultParams) {
			
			self::$defaultParams['status'] = 1;  
			
		}
	}
		
	public static function randomHash() 
	{

		$rsData = mt_rand().'_'.time().'_'.mt_rand();

		$rsData = trim($rsData);

		$rsData = strtolower(md5($rsData));

		return $rsData;

	}


	public static function hashMd5($input_data)
	{
		$input_data = serialize($input_data);
		$input_data = (string)$input_data;
		$input_data = preg_replace('#\s+#is','',$input_data);
		$input_data = md5($input_data);
		return $input_data;
	}
	
	public static function createKey($input_data)
	{
		$input_data = serialize($input_data); 
		$input_data = (string)$input_data;
		$input_data = preg_replace('#\s+#is','',$input_data);
		$input_data = md5($input_data);
		return $input_data;
	}

	
	public static function explode($input_delimiter, $input_data) 
	{
		$input_delimiter = $input_delimiter;
		if(preg_match('#[,;]+#i',$input_delimiter)) {
			$input_delimiter = preg_replace('#[;,]+#i',';',$input_delimiter);
		}
		
		$input_data = explode(';',$input_data);
		
		return $input_data;
		
	}
	
	public static function splitAndCleanKeywords($input_data) 
	{
		$resultData = array();
		$input_data = (array)$input_data;
		$input_data = implode(';',$input_data);
		$input_data = preg_replace('#[;,]+#i',';',$input_data);
		$input_data = preg_replace('#\s+#i',' ',$input_data);
		$input_data = explode(';',$input_data);
		foreach($input_data as $valueOne) {
			$valueOne = trim($valueOne);
			if($valueOne) {
				$resultData[] = $valueOne;
			}
		}
		
		return $resultData;
		
	}
	
	
	public static function reduceSpace($input_data) 
	{
		$input_data = preg_replace('#\s+#i',' ',$input_data);
		$input_data = trim($input_data);
		return $input_data;
	}
	
	public static function cleanKeyword($input_data) 
	{
		$input_data = preg_replace('#[\'\"]+#i',' ',$input_data);
		$input_data = self::reduceSpace($input_data);
		return $input_data;
	}
	
	public static function cleanPregPatternsArray($input_data) 
	{
		$input_data = (array)$input_data;
		$input_data = implode(';',$input_data);
		$input_data = preg_replace('#[\,\;]+#',';',$input_data);
		$input_data = explode(';',$input_data);
		$input_data = self::cleanArray($input_data);
		foreach($input_data as $keyOne => $valueOne) {
			$input_data[$keyOne] = preg_quote($valueOne, '#');
		}
		
		return $input_data;
		
	}
	
	public static function cleanArray($input_data) 
	{
		$resultData = array();
		
		$input_data = (array)$input_data;
		foreach($input_data as $value1) {
			$value1 = trim($value1);
			if(strlen($value1)>0) {
				$resultData[] = $value1;
			}
		}
		
		$resultData = array_unique($resultData);
		
		return $resultData;
		
	}
	
	public static function escapeHtmlTags($input_content) 
	{

		$input_content = (string)$input_content;

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);

		$patternsEscape1 = array();

		$matched1 = false;
		preg_match_all('/<a[^><]+>.*?<\/a>/i',$input_content,$matched1);
		if(isset($matched1[0]) && $matched1[0]) {
			if(count($matched1[0])>0) {
				foreach($matched1[0] as $key1 => $value1) {
					$search1 = $value1;
					$replace1 = md5($value1);
					$replace1 = str_split($replace1);
					$replace1 = implode('_',$replace1);

					$patternsEscape1[$search1] = '______'.$replace1.'______';
				}
			}
		}
		

		$matched1 = false;
		preg_match_all('#<[^><]+>#i',$input_content,$matched1);
		if(isset($matched1[0]) && $matched1[0]) {
			if(count($matched1[0])>0) {
				foreach($matched1[0] as $key1 => $value1) {
					$search1 = $value1;
					$replace1 = md5($value1);
					$replace1 = str_split($replace1);
					$replace1 = implode('_',$replace1);

					$patternsEscape1[$search1] = '______'.$replace1.'______';
				}
			}
		}

		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}


	
	public static function escapeHtmlTagsAndContents($input_content, $input_tags) 
	{

		$input_content = (string)$input_content;
		
		$input_tags = (array)$input_tags;
		$input_tags = implode(';',$input_tags);
		$input_tags = preg_replace('#[\,\;]+#',';',$input_tags);
		$input_tags = explode(';',$input_tags);
		$input_tags = self::cleanArray($input_tags);
		

		$resultData = array(
			'content' => $input_content
			,'patterns' => array()
		);

		$patternsEscape1 = array();
		
		if(count($input_tags)>0) {
			foreach($input_tags as $tagName) {
				$tagName = preg_replace('#[^a-z0-9]+#','',$tagName);
				$tagName = trim($tagName);
				if($tagName) {
					$matched1 = false;
					preg_match_all('/<'.$tagName.'[^><]+>.*?<\/'.$tagName.'>/i',$input_content,$matched1);
					if(isset($matched1[0]) && $matched1[0]) {
						if(count($matched1[0])>0) {
							foreach($matched1[0] as $key1 => $value1) {
								$search1 = $value1;
								$replace1 = md5($value1);
								$replace1 = str_split($replace1);
								$replace1 = implode('_',$replace1);

								$patternsEscape1[$search1] = '______'.$replace1.'______';
							}
						}
					}
				}
			}
		}
		
		if(count($patternsEscape1)>0) {
			$input_content = str_replace(array_keys($patternsEscape1),array_values($patternsEscape1),$input_content);
			$resultData['content'] = $input_content;
			$resultData['patterns'] = $patternsEscape1;

		}

		return $resultData;

	}
	
	
	
	public static function rrmdir($dir) {
		if($dir) {
			$dir = realpath($dir);
			if($dir) {
				if(file_exists($dir)) {
					if (is_dir($dir)) {
						$objects = scandir($dir);
						foreach ($objects as $object) {
							if ($object != "." && $object != "..") {
								if (filetype($dir."/".$object) == "dir") {
									self::rrmdir($dir."/".$object); 
								} else {
									unlink   ($dir."/".$object);
								}
							}
						}
						reset($objects);
						rmdir($dir);
					}
				}
			}
		}
		
	}



}//class PepVN_Data

endif; //if ( !class_exists('PepVN_Data') )


?>
