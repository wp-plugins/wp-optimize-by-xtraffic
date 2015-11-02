<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Model\WpOptions
	,WpPepVN\Utils
	,WpPepVN\Text
	,WpPepVN\Hash
	,WpPepVN\DependencyInjectionInterface
	,WPOptimizeByxTraffic\Application\Service\DbFullText
;

class AnalyzeText
{
	const OPTION_NAME = 'alz_tx';
	
	protected static $_tempData = array();
	
	public $di;
	
	protected $_dbFullText = false;
	
	protected static $_weightOfFields = array(
		'post_title' => 16
		,'post_name' => 2
		,'post_excerpt' => 6
		//,'post_content' => 1
	);
	
    public function __construct(DependencyInjectionInterface $di) 
    {
		$this->di = $di;
		
		$this->_dbFullText = new DbFullText();
		
	}
	
	public static function getDefaultOption()
	{
		return array(
			
		);
	}
	
	public static function getOption($cache_status = true)
	{
	
		$options = WpOptions::get_option(self::OPTION_NAME,array(),array(
			'cache_status' => $cache_status
		));
		
		$options = array_merge(self::getDefaultOption(),$options);
		
		return $options;
	}
	
	public static function updateOption($data)
	{
		$data = array_merge(self::getOption(false), $data);
		return WpOptions::update_option(self::OPTION_NAME,$data);
	}
	
	public function add_db_index($input_parameters = false) 
	{
		global $wpdb;
		
		if(!$input_parameters) {
			$input_parameters = array();
		}
		
		$options = self::getOption();
		
		$tableName = $wpdb->posts;
		$tableName = (string)$tableName;
		
		$fieldsNameNeedAddFullTextIndex = array(
			'post_title'
			,'post_excerpt'
			,'post_content'
			,'post_name'
		);
		
		$fieldsNameNeedAddBtreeIndex = array(
			'post_title' => 250
			,'post_excerpt' => 250
			,'post_name' => 0
			,'post_status' => 0
			,'post_type' => 0
		);
		
		$rsOne = $this->_dbFullText->addIndexToTableFields($tableName, $fieldsNameNeedAddFullTextIndex, 'fulltext');
		
		foreach($fieldsNameNeedAddFullTextIndex as $key => $value) {
			if(isset($rsOne[$value])) {
				unset($fieldsNameNeedAddFullTextIndex[$key]);
			}
		}
		
		if(!empty($fieldsNameNeedAddBtreeIndex)) {
			foreach($fieldsNameNeedAddBtreeIndex as $key => $value) {
				$rsOne = $this->_dbFullText->addIndexToTableFields($tableName, $key, 'btree', $value);
			}
		}
		
		if(!empty($fieldsNameNeedAddFullTextIndex)) {
			$options['db_has_fulltext_status'] = 'n';
		} else {
			$options['db_has_fulltext_status'] = 'y';
		}
		
		self::updateOption($options);
		
		return true;
		
	}
	
	
	
	public function search_posts($input_parameters) 
	{
		
		$classMethodKey = Hash::crc32b(__CLASS__ . __METHOD__);
		
		$options = self::getOption();
		
		$resultData = array();
		
		if(!isset($input_parameters['post_types'])) {
			$input_parameters['post_types'] = array(
				'post'
				,'page'
			);
		}
		
		$keyword = $input_parameters['keyword'];
		
		$keyword = preg_replace('#[\'"\\\]+#i',' ',$keyword);
		$keyword = preg_replace('#[\s]+#is',' ',$keyword);
		
		$keyword = trim($keyword);
		
		$input_parameters['keyword'] = $keyword;
		
		$keyCache1 = array(
			$classMethodKey
			,$input_parameters
		);
		
		if(!isset($options['db_has_fulltext_status'])) {
			$options['db_has_fulltext_status'] = 'n';
		}
		
		if(isset($options['db_has_fulltext_status']) && ('y' === $options['db_has_fulltext_status'])) {
			$keyCache1[] = 'db_has_fulltext_status';
		}
		
		$keyCache1 = Utils::hashKey($keyCache1);
		
		$resultData = TempDataAndCacheFile::get_cache($keyCache1,true);
		
		if(null === $resultData) {
			
			global $wpdb;
			
			$wpExtend = $this->di->getShared('wpExtend');
			
			$resultData = array();
			
			$weightOfFields = self::$_weightOfFields;
			
			$tablePostName = $wpdb->posts;
		
			$queryString_Where_PostType = array();
			
			foreach($input_parameters['post_types'] as $keyOne => $valueOne) {
				if($valueOne) {
					$valueOne = trim($valueOne);
					if($valueOne) {
						$queryString_Where_PostType[] = ' ( post_type = "'.$valueOne.'" ) ';
					}
				}
				
			}
			
			$queryString_Where_PostType = implode(' OR ',$queryString_Where_PostType);
			$queryString_Where_PostType = trim($queryString_Where_PostType);
			
			$options['db_has_fulltext_status'] = 'n';
			
			if('y' === $options['db_has_fulltext_status']) {
				
				$weightOfFields['post_content'] = 1;
				
				$totalWeightOfFields = 0;
				
				$arrayScoreMatchString = array();
				
				foreach($weightOfFields as $field => $weight) {
					$arrayScoreMatchString[] = ' ( ( MATCH(`'.$field.'`) AGAINST("'.$keyword.'" IN NATURAL LANGUAGE MODE) ) * '.$weightOfFields[$field].' ) ';
					$totalWeightOfFields += $weight;
				}
				
				$totalWeightOfFields = (int)$totalWeightOfFields;
				
				$queryString1 = '
SELECT ID , post_title , (
	('.implode(' + ',$arrayScoreMatchString).') / '.$totalWeightOfFields.'
) AS wpxtraffic_score
FROM '.$tablePostName.'
WHERE ( ( post_status = "publish") AND !(post_password > "") 
	'; 
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) '; 
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
ORDER BY wpxtraffic_score DESC 
LIMIT 0,3 
	';
			} else {
				
				$arrayScoreMatchString = array();
				
				$keywordLength = strlen($keyword);
				
				foreach($weightOfFields as $field => $weight) {
					$arrayScoreMatchString[] = '( IFNULL((ROUND((LENGTH(LOWER('.$field.'))-LENGTH(REPLACE(LOWER('.$field.'), "'.$keyword.'", "")))/'.$keywordLength.')),0) * '.$weight.')';
				}
				
				$queryString1 = '
SELECT ID , post_title , (
	('.implode(' + ',$arrayScoreMatchString).')
) AS wpxtraffic_score
FROM '.$tablePostName.'
WHERE ( ( post_status = "publish") AND !(post_password > "") 
';
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) ';
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
ORDER BY wpxtraffic_score DESC 
LIMIT 0,3
';
			}
			
			$rsOne = $wpdb->get_results($queryString1);
			
			if($rsOne) {
				
				foreach($rsOne as $keyOne => $valueOne) {
					if($valueOne) {
						if(isset($valueOne->wpxtraffic_score)) {
							$valueOne->wpxtraffic_score = (int)$valueOne->wpxtraffic_score;
							if($valueOne->wpxtraffic_score > 0) {
								$postId = (int)$valueOne->ID;
								
								if($postId) {
									$postLink = $wpExtend->get_permalink( $postId, false );
									
									if($postLink) {
										
										$postLink = trailingslashit($postLink);
										
										$resultData[$postId] = array(
											'post_id' => $postId,
											'post_title' => $valueOne->post_title,
											'post_link' => $postLink,
											'wpxtraffic_score' => $valueOne->wpxtraffic_score
										);
									}
								}
								
								
							}
							
						}
					}
				}
			}
			
			TempDataAndCacheFile::set_cache($keyCache1, $resultData,true);
		}
		
		return $resultData;
		
	}
	
	public function find_related_posts($input_parameters) 
	{
		
		
		$resultData = array();
		
		$classMethodKey = Hash::crc32b(__CLASS__ . __METHOD__);
		
		if(!isset($input_parameters['post_types'])) {
			$input_parameters['post_types'] = array(
				'post'
				,'page'
			);
		}
		
		$input_parameters['post_types'] = PepVN_Data::cleanArray($input_parameters['post_types']);
		
		if(empty($input_parameters['post_types'])) {
			return $resultData;
		}
		
		/*
			input_parameters['keywords'] = array(
				keyword a => (float)w
			);
		*/
		
		$keywords = array();
		
		$valueTemp = (array)$input_parameters['keywords'];
		
		foreach($valueTemp as $key1 => $value1) {
			$key1 = preg_replace('#[\'\"\\\]+#i',' ',$key1);
			
			$key1 = preg_replace('#[\s]+#is',' ',$key1);
			
			$key1 = self::cleanRawTextForProcessSearch($key1);
			
			$key1 = trim($key1);
			if($key1) {
				$keywords[$key1] = (float)$value1;
			}
		}
		
		if(empty($keywords)) {
			return $resultData;
		}
		
		global $wpdb;
		
		$options = self::getOption();
		
		$keywords = array_slice($keywords,0,30);	//max 30 keywords
		$input_parameters['keywords'] = $keywords;
		unset($keywords);
		
		if(!isset($input_parameters['limit'])) {
			$input_parameters['limit'] = 10; 
		}
		$input_parameters['limit'] = (int)$input_parameters['limit'];
		
		if(!isset($input_parameters['exclude_posts_ids'])) {
			$input_parameters['exclude_posts_ids'] = array();
		}
		$input_parameters['exclude_posts_ids'] = (array)$input_parameters['exclude_posts_ids'];
		foreach($input_parameters['exclude_posts_ids'] as $key1 => $value1) {
			$input_parameters['exclude_posts_ids'][$key1] = (int)$value1;
		}
		$input_parameters['exclude_posts_ids'] = array_unique($input_parameters['exclude_posts_ids']);
		arsort($input_parameters['exclude_posts_ids']);
		$input_parameters['exclude_posts_ids'] = array_values($input_parameters['exclude_posts_ids']);
		
		if(!isset($input_parameters['post_id_less_than'])) {
			$input_parameters['post_id_less_than'] = 0;
		}
		$input_parameters['post_id_less_than'] = (int)$input_parameters['post_id_less_than'];
		
		$keyCache1 = array(
			$classMethodKey
			,$input_parameters
		);
		
		
		if(!isset($options['db_has_fulltext_status'])) {
			$options['db_has_fulltext_status'] = 'n';
		}
		
		if(isset($options['db_has_fulltext_status']) && ('y' === $options['db_has_fulltext_status'])) {
			$keyCache1[] = 'db_has_fulltext_status';
		}
		
		$keyCache1 = Utils::hashKey($keyCache1);
		
		$resultData = TempDataAndCacheFile::get_cache($keyCache1,true);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$wpExtend = $this->di->getShared('wpExtend');
			
			$weightOfFields = self::$_weightOfFields;
			
			$tablePostName = $wpdb->posts;
			
			$combinedKeywords = array(
				'kw' => ''
				,'w' => 0
			);
			foreach($input_parameters['keywords'] as $key1 => $value1) {
				$combinedKeywords['kw'] .= ' '.$key1;
				$combinedKeywords['w'] += $value1;
			}
			
			$combinedKeywords['kw'] = trim($combinedKeywords['kw']);
			if($combinedKeywords['kw']) {
				$combinedKeywords['w'] = $combinedKeywords['w'] / count($input_parameters['keywords']);
				$input_parameters['keywords'][$combinedKeywords['kw']] = $combinedKeywords['w'];
			}
			
			$queryString_Where_PostType = array();
			
			foreach($input_parameters['post_types'] as $keyOne => $valueOne) {
				if($valueOne) {
					$valueOne = trim($valueOne);
					if($valueOne) {
						$queryString_Where_PostType[] = ' ( `'.$tablePostName.'`.`post_type` = "'.$valueOne.'" ) ';
					}
				}
				
			}
			
			$queryString_Where_PostType = implode(' OR ',$queryString_Where_PostType);
			$queryString_Where_PostType = trim($queryString_Where_PostType);
			
			$totalKeywordWeight = 1;
			
			if('y' === $options['db_has_fulltext_status']) {
				
				$weightOfFields['post_content'] = 2;
				
				$arrayScoreMatchString = array();
				
				foreach($input_parameters['keywords'] as $keyword => $keywordWeight) {
					
					foreach($weightOfFields as $field => $weight) {
						$arrayScoreMatchString[] = ' ( ( ( MATCH(`'.$tablePostName.'`.`'.$field.'`) AGAINST("'.$keyword.'" IN NATURAL LANGUAGE MODE) ) * '.$weightOfFields[$field].' ) * '.(float)$keywordWeight.')';
					}
					
					$totalKeywordWeight += $keywordWeight;
				}
				
				$queryString1 = '
SELECT `'.$tablePostName.'`.`ID` , `'.$tablePostName.'`.`post_title` , `'.$tablePostName.'`.`post_content` , (
	('.implode(' + ',$arrayScoreMatchString).') / 1
) AS wpxtraffic_score
FROM '.$tablePostName.'
WHERE ( ( `'.$tablePostName.'`.`post_status` = "publish" ) AND !(`'.$tablePostName.'`.`post_password` > "") 
	'; 
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) '; 
				}
				
				if(!empty($input_parameters['exclude_posts_ids'])) {
					$queryString1 .= ' AND ( `'.$tablePostName.'`.`ID` NOT IN ('.implode(',',$input_parameters['exclude_posts_ids']).') ) '; 
				}
				
				if(
					($input_parameters['post_id_less_than']>1)
					&& ($input_parameters['post_id_less_than'] > $input_parameters['limit'])
				) {
					$queryString1 .= ' AND ( `'.$tablePostName.'`.`ID` < '.$input_parameters['post_id_less_than'].' ) '; 
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
GROUP BY `'.$tablePostName.'`.`ID`
ORDER BY wpxtraffic_score DESC 
';
			} else {
				
				$arrayScoreMatchString = array();
				
				foreach($input_parameters['keywords'] as $keyword => $keywordWeight) {
					$keywordLength = strlen($keyword);
					foreach($weightOfFields as $field => $weight) {
						$arrayScoreMatchString[] = ' ( ( IFNULL((ROUND((LENGTH(LOWER(`'.$tablePostName.'`.`'.$field.'`))-LENGTH(REPLACE(LOWER(`'.$tablePostName.'`.`'.$field.'`), "'.$keyword.'", "")))/'.$keywordLength.')),0) * '.$weight.') * '.(float)$keywordWeight.') ';
					}
					$totalKeywordWeight += $keywordWeight / 1.2;
				}
				
				$queryString1 = '
SELECT `'.$tablePostName.'`.`ID` , `'.$tablePostName.'`.`post_title` , `'.$tablePostName.'`.`post_content`, (
	('.implode(' + ',$arrayScoreMatchString).')
) AS wpxtraffic_score
FROM '.$tablePostName.'
WHERE ( ( `'.$tablePostName.'`.`post_status` = "publish" ) AND !(`'.$tablePostName.'`.`post_password` > "") 
';
				
				if($queryString_Where_PostType) {
					$queryString1 .= ' AND ( '.$queryString_Where_PostType.' ) ';
				}
				
				if(!empty($input_parameters['exclude_posts_ids'])) {
					$queryString1 .= ' AND ( `'.$tablePostName.'`.`ID` NOT IN ('.implode(',',$input_parameters['exclude_posts_ids']).') ) '; 
				}
				
				if(
					($input_parameters['post_id_less_than']>1)
					&& ($input_parameters['post_id_less_than'] > $input_parameters['limit'])
				) {
					$queryString1 .= ' AND ( `'.$tablePostName.'`.`ID` < '.$input_parameters['post_id_less_than'].' ) '; 
				}
				
				$queryString1 .= ' ) ';
				
				$queryString1 .= ' 
GROUP BY `'.$tablePostName.'`.`ID` 
ORDER BY wpxtraffic_score DESC 
';
			}
			
			$queryString1 .= ' LIMIT 0,'.($input_parameters['limit']).' ';
			
			$rsOne = $wpdb->get_results($queryString1);
			
			unset($queryString1);
			
			if($rsOne) {
				foreach($rsOne as $keyOne => $valueOne) {
					unset($rsOne[$keyOne]);
					if($valueOne) {
						if(isset($valueOne->wpxtraffic_score)) {
							$valueOne->wpxtraffic_score = (float)$valueOne->wpxtraffic_score / $totalKeywordWeight;
							if($valueOne->wpxtraffic_score >= 1) {
								
								$postId = (int)$valueOne->ID;
								
								if($postId) {
									/*
									$foundKeywordStatus = false;
									
									
									$valueTemp1 = $valueOne->post_title;
									$valueTemp1 = $this->cleanRawTextForProcessSearch($valueTemp1);
									foreach($input_parameters['keywords'] as $key1 => $value1) {
										if(false !== stripos($valueTemp1, $key1)) {
											$foundKeywordStatus = true;
											break;
										}
									}
									
									if(!$foundKeywordStatus) {
										$valueTemp1 = $valueOne->post_content;
										$valueTemp1 = PepVN_Data::mb_substr($valueTemp1, 0 ,500);
										$valueTemp1 = $this->cleanRawTextForProcessSearch($valueTemp1);
										foreach($input_parameters['keywords'] as $key1 => $value1) {
											if(false !== stripos($valueTemp1, $key1)) {
												$foundKeywordStatus = true;
												break;
											}
										}
									}
									*/
									$foundKeywordStatus = true;
									
									if($foundKeywordStatus) {
									
										$postLink = $wpExtend->get_permalink( $postId, false );
										
										if($postLink) {
											
											$postLink = trailingslashit($postLink);
											
											$resultData[$postId] = array(
												'post_id' => $postId,
												'post_title' => $valueOne->post_title,
												'post_link' => $postLink,
												'wpxtraffic_score' => $valueOne->wpxtraffic_score
												
											);
										}
										
									}
								}
								
								
							}
							
						}
					}
				}
			}
			
			unset($rsOne);
			
			TempDataAndCacheFile::set_cache($keyCache1, $resultData,true);
		}
		
		return $resultData;
		
	}
	
	public function cleanArrayKeywords($keywords) 
	{
		$resultData = array();
		
		$keywords = (array)$keywords;
		$keywords = implode(';',$keywords);
		$keywords = preg_replace('#[\,\;]+#is',';',$keywords);
		$keywords = explode(';',$keywords);
		foreach($keywords as $key1 => $value1) {
			unset($keywords[$key1]);
			$value1 = PepVN_Data::cleanKeyword($value1);
			if($value1) {
				$resultData[] = $value1;
			}
		}
		
		return $resultData;
	}
	
	
	private function _frequencyOfAppearanceKeywordsInText_PrepareKeywords($keywords) 
	{
		
		$classMethodKey = Hash::crc32b(__CLASS__ . __METHOD__);
		
		$keyCache1 = Utils::hashKey(array(
			$classMethodKey
			,$keywords
		));
		
		$resultData = TempDataAndCacheFile::get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$keywords = self::cleanArrayKeywords($keywords);
			
			foreach($keywords as $key1 => $value1) {
				unset($keywords[$key1]);
				$resultData[$value1] = explode(' ',$value1);
			}
			
			TempDataAndCacheFile::set_cache($keyCache1,$resultData);
			
		}
		
		return $resultData;
	}
	
	public function frequencyOfAppearanceKeywordsInText($keywords,$text) 
	{
		$classMethodKey = Hash::crc32b(__CLASS__ . __METHOD__);
		
		$keyCache1 = Utils::hashKey(array(
			$classMethodKey
			,$keywords
			,$text
		));
		
		$resultData = TempDataAndCacheFile::get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$text = self::analysisKeyword_PrepareContents($text);
			
			$text = explode(' ',$text);
			$wordsCountInText = array_count_values($text);
			unset($text);
			
			$keywords = $this->_frequencyOfAppearanceKeywordsInText_PrepareKeywords($keywords);
			
			foreach($keywords as $key1 => $value1) {
				unset($keywords[$key1]);
				
				$resultData[$key1] = 0;
				
				foreach($value1 as $key2 => $value2) {
					if(isset($wordsCountInText[$value2])) {
						$resultData[$key1] += $wordsCountInText[$value2];
					}
				}
				
				if($resultData[$key1]) {
					$resultData[$key1] = $resultData[$key1] * strlen($key1) * 1.8;
				}
				
			}
			
			TempDataAndCacheFile::set_cache($keyCache1, $resultData);
		}
		
		return $resultData;
	}
	
	public static function decodeText($input_text)
	{
		$input_text = rawurldecode($input_text);
		$input_text = html_entity_decode($input_text, ENT_QUOTES, 'UTF-8');
		return $input_text;
	}
	
	static function analysisKeyword_RemovePunctuations($input_text, $input_excepts = false)
	{
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$input_text
			,$input_excepts
		));
		
		$tmp = TempDataAndCacheFile::get_cache($keyCache1);
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		$punctuations = array(
			',', ')', '(',"'", '"',
			'<', '>', '!', '?',
			'[', ']', '+', '=', '#', '$', ';'
			,':','-','.','–','-'
			,'$', '&quot;', '&copy;', '&gt;', '&lt;', 
			'&nbsp;', '&trade;', '&reg;', ';', 
			chr(10), chr(13), chr(9)
		);
		
		if($input_excepts) {
			$input_excepts = (array)$input_excepts;
			$punctuations1 = array();
			foreach($punctuations as $key1 => $value1) {
				if(!in_array($value1,$input_excepts)) {
					$punctuations1[] = $value1;
				}
			}
			$punctuations = $punctuations1;$punctuations1 = false;
		}
		
		$punctuations = array_unique($punctuations);
		
		$input_text = str_replace($punctuations, ' ', $input_text);
		
		TempDataAndCacheFile::set_cache($keyCache1, $input_text);
		
		return $input_text;
	}
	
	public static function analysisKeyword_PrepareContents($text)
	{
	
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$text
		));
		
		$tmp = TempDataAndCacheFile::get_cache($keyCache1);
		if(null !== $tmp) {
			return $tmp;
		}
		
		$text = (array)$text;
		$text = implode(' ',$text);
		$text = self::decodeText($text);
		$text = strip_tags($text);
		
		//$text = PepVN_Data::strtolower($text);
		
		$text = self::analysisKeyword_RemovePunctuations($text);
		
		$text = Text::reduceSpace($text);
		
		TempDataAndCacheFile::set_cache($keyCache1, $text);
	
		return $text;
	}
	
	public static function analysisKeyword_OccureFilter($array_count_values, $min_occur)
	{
		$min_occur_sub = $min_occur - 1;
		
		$occur_filtered = array();
		
		foreach($array_count_values as $word => $occured) {
			if($occured > $min_occur_sub) {
				$occur_filtered[$word] = $occured;
			}
		}

		return $occur_filtered;
	}
	
	public static function analysisKeyword_GetKeywordsFromText($input_parameters)
	{
		//ini_set('max_execution_time', 10);
		
		$inputExplodedContents = false;
		$resultAnalysis = false;
		$common = array();
		
		$checkStatusOne = false;  
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$input_parameters
		));
		
		$resultData = TempDataAndCacheFile::get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$resultData['data'] = false;
		
			if(isset($input_parameters['contents'])) {
				$input_parameters['contents'] = (array)$input_parameters['contents'];
				$input_parameters['contents'] = implode(' ',$input_parameters['contents']);
				$input_parameters['contents'] = trim($input_parameters['contents']);
				if($input_parameters['contents']) {
					$inputExplodedContents = self::analysisKeyword_PrepareContents($input_parameters['contents']);
					if($inputExplodedContents) {
						$inputExplodedContents = explode(' ', $inputExplodedContents);
						unset($input_parameters['contents']);  
						$checkStatusOne = true;
					}
				}
			}
			
			if($checkStatusOne) {
				$inputMinWord = 0;
				$inputMaxWord = 0;
				$inputMinOccur = 0;
				$inputMinCharEachWord = 0;
				
				if(isset($input_parameters['min_word']) && $input_parameters['min_word']) {
					$inputMinWord =  $input_parameters['min_word'];
				}
				
				if(isset($input_parameters['max_word']) && $input_parameters['max_word']) {
					$inputMaxWord =  $input_parameters['max_word'];
				}
				
				if(isset($input_parameters['min_occur']) && $input_parameters['min_occur']) {
					$inputMinOccur =  $input_parameters['min_occur'];
				}
				
				if(isset($input_parameters['min_char_each_word']) && $input_parameters['min_char_each_word']) {
					$inputMinCharEachWord =  $input_parameters['min_char_each_word'];
				}
				
				$inputMinWord = (int)$inputMinWord;
				$inputMaxWord = (int)$inputMaxWord;
				$inputMinOccur = (int)$inputMinOccur;
				$inputMinCharEachWord = (int)$inputMinCharEachWord;
				
				if(!$inputMinWord) {
					$inputMinWord = 1;//min word each keyword
				}
				if(!$inputMaxWord) {
					$inputMaxWord = 3;//max word each keyword
				}
				if(!$inputMinOccur) {
					$inputMinOccur = 2;//number appear
				}
				if(!$inputMinCharEachWord) {
					$inputMinCharEachWord = 3;// min char each word
				}
				
				if($inputMinWord>$inputMaxWord) {
					$inputMinWord = $inputMaxWord;
				}
			}
			
			if($checkStatusOne) { 
				$countExplodedContents = count($inputExplodedContents);
				for($iOne = 0; $iOne < $countExplodedContents; ++$iOne) {
					
					for($iTwo = $inputMinWord; $iTwo <= $inputMaxWord; ++$iTwo) {
						$minCharOfPhrase = ($inputMinCharEachWord * $iTwo) + ($iTwo - 1);
						
						$phraseNeedAnalysis = '';
						$maxIThree = $iOne + $iTwo;
						for($iThree = $iOne; $iThree < $maxIThree; ++$iThree) {
							if(isset($inputExplodedContents[$iThree])) {
								$wordTemp = trim($inputExplodedContents[$iThree]);
								if(isset($wordTemp[0])) {
									$phraseNeedAnalysis .= ' '.$wordTemp;
								}
							} else {
								break 2;
							}
						}
						
						$phraseNeedAnalysis = trim($phraseNeedAnalysis);
						
						if((mb_strlen($phraseNeedAnalysis, 'UTF-8') >= $minCharOfPhrase)  && (!isset($common[$phraseNeedAnalysis]))  && (!is_numeric($phraseNeedAnalysis))) {
							$resultAnalysis[$iTwo][] = $phraseNeedAnalysis;
						}
					}
					
				}
			}
			
			if(is_array($resultAnalysis)) {
				reset($resultAnalysis);
				foreach($resultAnalysis as $keyOne => $valueOne) {
					$valueOne = array_count_values($valueOne);
					
					if($inputMinOccur>1) {
						$valueOne = self::analysisKeyword_OccureFilter($valueOne, $inputMinOccur);
					}
					
					if(!empty($valueOne)) {
						arsort($valueOne);
						$resultAnalysis[$keyOne] = $valueOne;
					} else {
						unset($resultAnalysis[$keyOne]);
					}
				}
				krsort($resultAnalysis);
			}
			
			$resultData['data'] = $resultAnalysis; 
			
			TempDataAndCacheFile::set_cache($keyCache1, $resultData);
		}
		
		return $resultData;
		
	}
	
	
	public static function cleanRawTextForProcessSearch($input_text)
	{
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$input_text
		));
		
		$tmp = TempDataAndCacheFile::get_cache($keyCache1);
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		$input_text = (array)$input_text;
		$input_text = implode(' ',$input_text);
		
		$input_text = self::decodeText($input_text);
		
		$input_text = strip_tags($input_text);
		$input_text = PepVN_Data::strtolower($input_text);
		
		$input_text = self::analysisKeyword_RemovePunctuations($input_text);
		
		$input_text = Text::replaceSpecialChar($input_text);
		
		$input_text = PepVN_Data::reduceSpace($input_text);
		
		TempDataAndCacheFile::set_cache($keyCache1, $input_text);
		
		return $input_text;
	}
	
	
}

