<?php 
namespace WPOptimizeByxTraffic\Application\Service;

use WpPepVN\TempData
	, WpPepVN\Text
	, WpPepVN\Utils
	, WpPepVN\System
	, WPOptimizeByxTraffic\Application\Service\TempDataAndCacheFile
	, WPOptimizeByxTraffic\Application\Service\PepVN_Data
;

/*
*	This class extends class \WpPepVN\TempData so all result is cached in var _tempData
*/

class WpExtend extends TempData
{
	private static $_wpextend_tempData = array();
	
	public function __construct() 
    {
		parent::__construct();
	}
    
    public function init() 
    {
        
    }
	
	private function _function_exists($name)
	{
		$k = 'fcex' . $name;
		
		if(isset(self::$_wpextend_tempData[$k])) {
			return self::$_wpextend_tempData[$k];
		} else {
			self::$_wpextend_tempData[$k] = System::function_exists($name);
			return self::$_wpextend_tempData[$k];
		}
	}
	
	private function _wpextend_call_methods($method, $args)
	{
		if($this->_function_exists($method)) {
			
			$key = $this->_tempData_hashKey(array($method, $args));
			
			$bag = $this->_bag;
			
			if(!isset(self::$_tempData[$bag][$key])) {
				self::$_tempData[$bag][$key] = call_user_func_array($method, $args);
				return self::$_tempData[$bag][$key];
			} else {
				return self::$_tempData[$bag][$key];
			}
			
		} else {
			return null;
		}
		
	}
	
	public function __call($method,$args)
    {
		return $this->_wpextend_call_methods($method,$args);
    }
    
	public function is_subdirectory_install()
	{
		if(strlen($this->site_url()) > strlen($this->home_url())) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getABSPATH()
	{
		$path = ABSPATH;
		$siteUrl = $this->site_url();
		$homeUrl = $this->home_url();
		$diff = str_replace($homeUrl, '', $siteUrl);
		$diff = trim($diff,DIRECTORY_SEPARATOR);

		$pos = strrpos($path, $diff);

		if($pos !== false){
			$path = substr_replace($path, '', $pos, strlen($diff));
			$path = trim($path,DIRECTORY_SEPARATOR);
			$path = DIRECTORY_SEPARATOR.$path.DIRECTORY_SEPARATOR;
		}
		
		return $path;
	}
	
	public function isCurrentUserCanManagePlugin()
	{
		$status = false;
		
		if($this->is_user_logged_in()) {
			
			if($this->is_multisite()) {
				if(
					$this->current_user_can('manage_network_plugins')
				) {
					$status = true;
				}
			} else {
				if($this->current_user_can('activate_plugins')) {
					if($this->current_user_can('delete_plugins')) {
						if($this->current_user_can('install_plugins')) {
							if($this->current_user_can('update_plugins')) {
								$status = true;
							}
						}
					}
				}
			}
		}
		
		return $status;
	}
	
	
	public static function getMySQLVersion() 
	{
		global $wpdb;
		
		$rsOne = $wpdb->get_results('SHOW VARIABLES LIKE "%version%"');
		
		if($rsOne) {
			
			foreach($rsOne as $valueOne) {

				if($valueOne) {
				
					if(isset($valueOne->Variable_name) && $valueOne->Variable_name && isset($valueOne->Value) && $valueOne->Value) {
						
						$variableName = $valueOne->Variable_name;
						
						$variableName = (string)$variableName;
						$variableName = trim($variableName);
						$variableName = strtolower($variableName);
						
						if('version' === $variableName1) {
							$variableValue = $valueOne->Value;
							return $variableValue;
						}
					}
				}
			}
		}
		
		return false;
	}
	
	public function parsePostData($post)
	{
		if(is_object($post)) {
			$post = (array)$post;
		}
		
		$post['ID'] = (int)$post['ID'];
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__
			,__METHOD__
			, $post['ID']
		));
		
		$tmp = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null !== $tmp) {
			return $tmp;
		}
		
		if(!isset($post['post_excerpt']) || !$post['post_excerpt']) {
			$post['post_excerpt'] = $post['post_content'];
		}
		
		$post['post_excerpt'] = Text::removeShortcode($post['post_excerpt'], ' ');
		
		$post['post_excerpt'] = strip_tags($post['post_excerpt']);
		$post['post_excerpt'] = Text::removeLine($post['post_excerpt'], ' ');
		$post['post_excerpt'] = Text::reduceLine($post['post_excerpt'], ' ');
		$post['post_excerpt'] = explode(' ',$post['post_excerpt'], 250);
		if(isset($post['post_excerpt'][251])) {
			$post['post_excerpt'][251] = '...';
		}
		$post['post_excerpt'] = implode(' ',$post['post_excerpt']);
		
		$post['postPermalink'] = $this->get_permalink($post['ID']);
		
		$post['postImages'] = array();
		
		preg_match_all('#<img[^>]+src=(\'|\")([^\'\"]+)\1[^>]+\/?>#is',$post['post_content'],$matched1);
		if(isset($matched1[2]) && $matched1[2]) {
			foreach($matched1[2] as $key1 => $value1) {
				$post['postImages'][] = array(
					'src' => $value1
				);
			}
		}
		
		$post['postThumbnailId'] = 0;
		$post['postThumbnailUrl'] = '';
		
		$post_thumbnail_id = $this->get_post_thumbnail_id($post['ID']);
		if($post_thumbnail_id) {
			$post['postThumbnailId'] = $post_thumbnail_id;
			$post_thumbnail_url = $this->wp_get_attachment_url($post_thumbnail_id);
			if($post_thumbnail_url) {
				$post['postThumbnailUrl'] = $post_thumbnail_url;
				
				$post['postImages'][] = array(
					'src' => $post_thumbnail_url
				);
			}
		}
		$post['postThumbnailId'] = (int)$post['postThumbnailId'];
		$post['postThumbnailUrl'] = trim($post['postThumbnailUrl']);
		
		
		$post['postAttachments'] = array();
		
		$attachments = get_posts( array(
			'post_type' => 'attachment',
			'posts_per_page' => -1,
			'post_parent' => $post['ID']
		));

		if ( $attachments ) {
			foreach ( $attachments as $key1 => $attachment ) {
				unset($attachments[$key1]);
				
				$tmp = array(
					'ID' => $attachment->ID
					,'post_mime_type' => $attachment->post_mime_type
					,'post_title' => $attachment->post_title
				);
				
				$tmp['attachment_url'] = $this->wp_get_attachment_url($attachment->ID);
				
				$tmp['metadata'] = wp_get_attachment_metadata($attachment->ID, true);
				
				$post['postAttachments'][$attachment->ID] = $tmp;
				
				unset($attachment,$tmp);
			}
			
		}
		
		$post['postContentRawText'] = $post['post_content'];
		$post['postContentRawText'] = strip_tags($post['postContentRawText']);
		$post['postContentRawText'] = Text::reduceLine($post['postContentRawText']);
		$post['postContentRawText'] = Text::reduceSpace($post['postContentRawText']);
		
		//unset($post['post_content']);
		PepVN_Data::$cacheObject->set_cache($keyCache1, $post);
		
		return $post;
	}
	
	public function getAndParsePostByPostId($post_id)
	{
		$post_id = (int)$post_id;
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__
			,__METHOD__
			,$post_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			$resultData = $this->get_post($post_id);
			if($resultData && isset($resultData->ID) && $resultData->ID) {
				$resultData = $this->parsePostData($resultData);
			} else {
				$resultData = false;
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
		}
		
		return $resultData;
	}
	
	public function getTermsByPostId($post_id)
	{
		$post_id = (int)$post_id;
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__
			,__METHOD__
			,$post_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$groupsTerms = array();
			
			if($post_id > 0) {
				
				$groupsTerms['tags'] = $this->get_the_tags($post_id);
				$groupsTerms['category'] = $this->get_the_category($post_id);
				
				foreach($groupsTerms as $keyOne => $valueOne) {
					unset($groupsTerms[$keyOne]);
					if ($valueOne) {
						
						foreach($valueOne as $keyTwo => $valueTwo) {
							unset($valueOne[$keyTwo]);
							if($valueTwo) {
								
								if ($valueTwo && (!is_wp_error($valueTwo))) {
								
									if(isset($valueTwo->term_id) && $valueTwo->term_id) {
										$valueTwo->term_id = (int)$valueTwo->term_id;
										$linkTerm = '';
										if('tags' === $keyOne) {
											$linkTerm = $this->get_tag_link($valueTwo->term_id);
										} else if('category' === $keyOne) {
											$linkTerm = $this->get_category_link($valueTwo->term_id);
										}
										
										$rsTermData = array(
											'name' => $valueTwo->name
											,'term_id' => $valueTwo->term_id
											,'link' => $linkTerm
											,'slug' => ''
											,'termType' => $keyOne
										);
										
										if(isset($valueTwo->slug)) {
											$rsTermData['slug'] = $valueTwo->slug;
										}
										
										$resultData[] = $rsTermData;
										
									}
									
								}
							}
						}
					}
				}
				
				unset($groupsTerms);
				
				$rsGetAllAvailableTaxonomies = $this->get_taxonomies(
					array(
					  'public'   => true
					)
					, 'objects'
					, 'and'
				);
				
				$arrayTaxonomiesNameExclude = array(
					'category'
					,'post_tag'
				);
				
				foreach($rsGetAllAvailableTaxonomies as $keyOne => $valueOne) {
					unset($rsGetAllAvailableTaxonomies[$keyOne]);
					if($valueOne) {
						if(isset($valueOne->name) && $valueOne->name) {
							if(!in_array($valueOne->name, $arrayTaxonomiesNameExclude)) {
								$rsGetTheTerms = $this->get_the_terms($post_id,$valueOne->name);
								if($rsGetTheTerms) {
									if(is_array($rsGetTheTerms) && (!empty($rsGetTheTerms))) {
										foreach($rsGetTheTerms as $keyTwo => $valueTwo) {
											unset($rsGetTheTerms[$keyTwo]);
											if($valueTwo) {
												if(isset($valueTwo->name) && $valueTwo->name) {
													
													$rsTermData = array(
														'name' => $valueTwo->name
														,'term_id' => $valueTwo->term_id
														,'link' => ''
														,'slug' => ''
														,'termType' => $valueOne->name
													);
													
													if(isset($valueTwo->slug)) {
														$rsTermData['slug'] = $valueTwo->slug;
													}
													
													$resultData[] = $rsTermData;
												}
											}
										}
									}
								}
							}
							
						}
					}
				}
				
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
		}
		
		return $resultData;
	}
	
	public function getAndParseCategories($input_term_id = 0)
	{
		$input_term_id = (int)$input_term_id;
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$input_term_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$terms = $this->get_categories($input_term_id);
			
			if ($terms) {
				foreach($terms as $index => $term) {
					unset($terms[$index]);
					if($term) {
						if(isset($term->term_id) && $term->term_id) {
							$term->term_id = (int)$term->term_id;
							$resultData[$term->name] = array(
								'name' => $term->name
								,'term_id' => $term->term_id
								,'categoryLink' => $this->get_category_link($term->term_id)
							);
						}
					}
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	
	public function getAndParseTags($input_term_id = 0)
	{
		$input_term_id = (int)$input_term_id;
		
		$keyCache1 = Utils::hashKey(array(
			__CLASS__ . __METHOD__
			,$input_term_id 
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		
		if(null === $resultData) {
			
			$resultData = array();
			
			$terms = $this->get_tags($input_term_id);
			
			if ($terms) {
				foreach($terms as $index => $term) {
					unset($terms[$index]);
					if($term) {
						if(isset($term->term_id) && $term->term_id) {
							$term->term_id = (int)$term->term_id;
							$resultData[$term->name] = array(
								'name' => $term->name
								,'term_id' => $term->term_id
								,'tagLink' => $this->get_tag_link($term->term_id)
							);
							
						}
					}
				}
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
			
			
		}
		
		return $resultData;
	}
	
	public function getWpOptimizeByxTrafficPluginPromotionInfo()
	{
		$resultData = array();
		
		$resultData['data'] = array(
			'plugin_name' => WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_NAME
			, 'plugin_version' => WP_OPTIMIZE_BY_XTRAFFIC_PLUGIN_VERSION
			, 'plugin_wp_url' => 'https://wordpress.org/plugins/wp-optimize-by-xtraffic/'
			, 'current_site_domain' => PepVN_Data::$defaultParams['fullDomainName']
			, 'current_time_mysql' => $this->current_time('mysql', $this->get_option('gmt_offset'))
		);
		
		$resultData['html_comment_text'] = '<!-- 
+ This website has been optimized by plugin "'.$resultData['data']['plugin_name'].'".
+ Served from : '.$resultData['data']['current_site_domain'].' @ '.$resultData['data']['current_time_mysql'].' by "'.$resultData['data']['plugin_name'].'".
+ Learn more here : '.$resultData['data']['plugin_wp_url'].'
-->';
		
		return $resultData;
	}
	
	public function isWpAjax()
	{
		if(defined('DOING_AJAX') && DOING_AJAX) {
			return true;
		}
		
		return false;
	}
	
	public function isRequestIsAutoSavePosts()
	{
		$resultData = false;
		
		if(isset($_POST['data']['wp_autosave']['post_id'])) {
			$resultData = true;
		}
		
		return $resultData;
	}
}