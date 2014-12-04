<?php

require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_Base.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeImages') ) :

class WPOptimizeByxTraffic_OptimizeImages extends WPOptimizeByxTraffic_Base 
{
	
	public $hostHasToolToProcessImage = false;
	
	
	protected $imgExtensionsAllow = array(
		'jpg'
		,'png'
		,'gif'
		,'jpeg'
	);
	
	function __construct() 
	{
	
		parent::__construct();
		
		
		$this->pepvn_UploadsImgFolderPath = WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_PEPVN;
		$this->pepvn_UploadsImgFolderUrl = WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_URL_PEPVN;
		if(!file_exists($this->pepvn_UploadsImgFolderPath)) {
			
			PepVN_Data::createFolder($this->pepvn_UploadsImgFolderPath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($this->pepvn_UploadsImgFolderPath,WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP,WPOPTIMIZEBYXTRAFFIC_CHMOD);
		}
		
		
		$this->pepvn_UploadsPreviewImgFolderPath = $this->pepvn_UploadsImgFolderPath . 'preview/';
		if(!file_exists($this->pepvn_UploadsPreviewImgFolderPath)) {
			PepVN_Data::createFolder($this->pepvn_UploadsPreviewImgFolderPath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($this->pepvn_UploadsPreviewImgFolderPath,WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP,WPOPTIMIZEBYXTRAFFIC_CHMOD);
		}
		
		
		$this->pepvn_ImgFolderCachePath = WPOPTIMIZEBYXTRAFFIC_CACHE_FILES_PATH;
		if(!file_exists($this->pepvn_ImgFolderCachePath)) {
			
			PepVN_Data::createFolder($this->pepvn_ImgFolderCachePath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
			PepVN_Data::chmod($this->pepvn_ImgFolderCachePath,WPOPTIMIZEBYXTRAFFIC_UPLOADS_FOLDER_PATH_WP,WPOPTIMIZEBYXTRAFFIC_CHMOD);
		}
		
		$this->fontFolderPath = WPOPTIMIZEBYXTRAFFIC_PATH.'inc/fonts/';
		
		$this->hostHasToolToProcessImage = false;
		/*
		if(function_exists('extension_loaded')) {
			if (extension_loaded('gd') || extension_loaded('gd2')) {
				$this->hostHasToolToProcessImage = true;
			}
		}
		*/
		if(function_exists('gd_info')) {
			$this->hostHasToolToProcessImage = true;
		}
		
	}
	
	
	public function optimize_images_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		
		/*
		*	20 : error optimize image file - can't process image file
		*	21 : error preview optimized image file - still can process image file in front end
		*/
		$resultData['notice']['error_no'] = array();	
		
		
		
		
		$rsTemp = $this->base_check_system_ready();
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsTemp
		));
		
		
		if(!function_exists('gd_info')) {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server need to install the GD library to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"<b>Optimize Image File</b>" (<a href="http://php.net/manual/en/book.image.php" target="_blank"><b>'.__('details here',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b></a>)</div>';
			$resultData['notice']['error_no'][] = 20;
		}
		
		if(
			isset($this->pepvn_UploadsImgFolderPath)
			&& $this->pepvn_UploadsImgFolderPath
			&& file_exists($this->pepvn_UploadsImgFolderPath)
			&& PepVN_Data::isAllowReadAndWrite($this->pepvn_UploadsImgFolderPath)
		) {
			
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$this->pepvn_UploadsImgFolderPath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Image File</b>"</div>';
			$resultData['notice']['error_no'][] = 20;
		}
		
		
		if(
			isset($this->pepvn_ImgFolderCachePath)
			&& $this->pepvn_ImgFolderCachePath
			&& file_exists($this->pepvn_ImgFolderCachePath)
			&& PepVN_Data::isAllowReadAndWrite($this->pepvn_ImgFolderCachePath)
		) {
			
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$this->pepvn_ImgFolderCachePath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Image File</b>"</div>';
			$resultData['notice']['error_no'][] = 20;
		}
		
		
		
		if(
			isset($this->fontFolderPath)
			&& $this->fontFolderPath
			&& file_exists($this->fontFolderPath)
			&& PepVN_Data::is_readable($this->fontFolderPath)
		) {
			
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$this->fontFolderPath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Optimize Image File</b>"</div>';
			$resultData['notice']['error_no'][] = 20;
		}
		
		
		
		if(
			isset($this->pepvn_UploadsPreviewImgFolderPath)
			&& $this->pepvn_UploadsPreviewImgFolderPath
			&& file_exists($this->pepvn_UploadsPreviewImgFolderPath)
			&& PepVN_Data::isAllowReadAndWrite($this->pepvn_UploadsPreviewImgFolderPath)
		) {
			
		} else {
			$resultData['notice']['error'][] = '<div class="update-nag fade"><b>'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_NAME.'</b> : '.__('Your server must set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>'.__('readable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> & <u>'.__('writable',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</u> '.__('folder',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>'.$this->pepvn_UploadsPreviewImgFolderPath.'</b>" '.__('to use',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<b>Preview Optimize Image File</b>"</div>';
			$resultData['notice']['error_no'][] = 21;
		}
		
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	
	private function optimize_images_getImageFileInfo($file)
	{
		
		$resultData = PepVN_Images::getImageInfo($file, false);
		
		return $resultData;
	}
	
	
	
	private function optimize_images_openImage($filePath)
	{
		
		$resultData = PepVN_Images::getImageInfo($file, true);
		if(isset($resultData['image_resource']) && $resultData['image_resource']) {
			return $resultData['image_resource'];
		}
		
		return false;
		
	}
	
	
	
	private function optimize_images_fixFileName($file_name)
	{
		$file_name = preg_replace('#['.PepVN_Data::preg_quote('~`!@#$%^&*()=[]{}|:;\'\"<>,/?').']+#is',' ',$file_name);
		$file_name = preg_replace('#\.+#is','.',$file_name);
		$file_name = trim($file_name);
		$file_name = preg_replace('#[ \t]+#is','-',$file_name);
		
		
		return $file_name;
	}

	private function optimize_images_IsImageFilePathCanProcess($fileName)
	{
		
		$resultData = false;
		
		if($fileName && file_exists($fileName)) {
	
			$rsOne = getimagesize($fileName);
			if(isset($rsOne[0]) && $rsOne[0]) {
				$rsOne[0] = (int)$rsOne[0];
				if($rsOne[0] > 10) {
					if(isset($rsOne[1]) && $rsOne[1]) {
						$rsOne[1] = (int)$rsOne[1];
						if($rsOne[1] > 10) {
							
							if(!PepVN_Images::isAnimation($fileName)) {
								$resultData = true;
							}
							
						}
					}
				}
				
			}
			
		}
		
		return $resultData;
	}
	
	
	
	public function optimize_images_clean_unuse_image($input_parameters)
	{
		if(
			isset($input_parameters['optimized_image_folder_path'])
			&& $input_parameters['optimized_image_folder_path']
			&& isset($input_parameters['key_file'])
			&& $input_parameters['key_file']
			
		) {
			
			
			$globPaths1 = $input_parameters['optimized_image_folder_path'] . '*.*';
			$globPaths1 = glob($globPaths1);
			
			if($globPaths1 && is_array($globPaths1) && (count($globPaths1)>0)) {
				
				foreach ($globPaths1 as $filename1) {
					
					if($filename1 && file_exists($filename1)) {
						if(PepVN_Data::isImg($filename1)) {
							if(!preg_match('#-'.$input_parameters['key_file'].'(\.|\-)#',$filename1)) {
								@unlink($filename1);
							}
						}
					}
					
				}
			}
			
			
		}
	}
	
	
	
	
	
	public function optimize_images_process_image($input_parameters)
	{
		$resultData = array();
		$resultData['image_original_file_path'] = false;
		$resultData['image_optimized_file_path'] = false;
		$resultData['image_optimized_file_url'] = false;
		
		
		$rsTemp = $this->optimize_images_check_system_ready();
		if(in_array(20,$rsTemp['notice']['error_no'])) {
			return $resultData;
		}
		
		
		
		$options = false;
		$paramsWatermarkOptions = false;
		
		if(isset($input_parameters['options']) && $input_parameters['options']) {
			$options = $input_parameters['options'];
		}
		
		if(isset($input_parameters['paramsWatermarkOptions']) && $input_parameters['paramsWatermarkOptions']) {
			$paramsWatermarkOptions = $input_parameters['paramsWatermarkOptions'];
		}
		
		$input_parameters['options'] = '';
		$input_parameters['paramsWatermarkOptions'] = '';
		
		if(!$options) {
		
			$options = $this->get_options(array(
				'cache_status' => 1
			));
			
			
		}
		
		
		if(isset($options['optimize_images_optimize_image_file_enable']) && $options['optimize_images_optimize_image_file_enable']) {
		} else {
			return $resultData;
		}
		
		
		if(!$paramsWatermarkOptions) {
		
			$rsSettingWatermarksFirstOptions = $this->optimize_images_setting_watermarks_first_options(array(
				'options' => $options
			));
			
			$paramsWatermarkOptions = $rsSettingWatermarksFirstOptions['paramsWatermarkOptions'];
			$options = $rsSettingWatermarksFirstOptions['options'];
			
			$rsSettingWatermarksFirstOptions = '';
		}
		
		
		if(isset($input_parameters['plusOptions']) && is_array($input_parameters['plusOptions'])) {
			$options = PepVN_Data::mergeArrays(array(
				$options
				,$input_parameters['plusOptions']
			));
		}
		
		
		$checkStatus1 = false;
		
		if(isset($paramsWatermarkOptions['text']) && $paramsWatermarkOptions['text']) {
			if(is_array($paramsWatermarkOptions['text'])) {
				if(count($paramsWatermarkOptions['text'])>0) {
					$checkStatus1 = true;
				}
			}
		}
		
		if(!$checkStatus1) {
			$paramsWatermarkOptions['text'] = false;
		}
		
		
		$checkStatus1 = false;
		
		if(isset($paramsWatermarkOptions['image']) && $paramsWatermarkOptions['image']) {
			if(is_array($paramsWatermarkOptions['image'])) {
				if(count($paramsWatermarkOptions['image'])>0) {
					$checkStatus1 = true;
				}
			}
		}
		
		if(!$checkStatus1) {
			$paramsWatermarkOptions['image'] = false;
		}
		
		if(isset($options['optimize_images_watermarks_enable']) && $options['optimize_images_watermarks_enable']) {
		} else {
			$paramsWatermarkOptions['text'] = false;
			$paramsWatermarkOptions['image'] = false;
		}
		
		$isProcessStatus = false;
		
		if(($paramsWatermarkOptions['text']) || ($paramsWatermarkOptions['image'])) {
			$isProcessStatus = true;
		}
		
		$options['optimize_images_image_quality_value'] = abs((int)$options['optimize_images_image_quality_value']);
		if(($options['optimize_images_image_quality_value'] >= 10) && ($options['optimize_images_image_quality_value'] < 100)) {
			$isProcessStatus = true;
		}
		
		$options['optimize_images_rename_img_filename_value'] = trim($options['optimize_images_rename_img_filename_value']);
		if($options['optimize_images_rename_img_filename_value']) {
			$isProcessStatus = true;
		}
		
		if($isProcessStatus) {
			if(isset($input_parameters['original_image_src']) && $input_parameters['original_image_src']) {
				
			} else {
				$isProcessStatus = false;
			}
		}
		
		if($isProcessStatus) {
			$options['optimize_images_maximum_files_handled_each_request'] = (int)$options['optimize_images_maximum_files_handled_each_request'];
			if($options['optimize_images_maximum_files_handled_each_request']>0) {
				if(PepVN_Data::$defaultParams['optimize_images']['number_images_processed_request'] >= $options['optimize_images_maximum_files_handled_each_request']) {
					$isProcessStatus = false;
				}
			}
		}
		
		
		if($isProcessStatus) {
			if(!isset($input_parameters['optimized_image_file_name'])) {
				$input_parameters['optimized_image_file_name'] = '';
			}
			if(!$input_parameters['optimized_image_file_name']) {
				$imgPathInfo = pathinfo($input_parameters['original_image_src']);
				if(isset($imgPathInfo['filename']) && $imgPathInfo['filename']) {
					$input_parameters['optimized_image_file_name'] = trim($imgPathInfo['filename']);
				}
			}
			
			if(!$input_parameters['optimized_image_file_name']) {
				$isProcessStatus = false;
			}
		}
		
		if(!$isProcessStatus) {
			return $resultData;
		}
		
		
		
		
		
		
		if(!isset($input_parameters['resize_max_width'])) {
			$input_parameters['resize_max_width'] = 0;
		}
		$input_parameters['resize_max_width'] = (int)$input_parameters['resize_max_width'];
		$input_parameters['resize_max_width'] = abs($input_parameters['resize_max_width']);
		if(!isset($input_parameters['resize_max_height'])) {
			$input_parameters['resize_max_height'] = 0;
		}
		$input_parameters['resize_max_height'] = (int)$input_parameters['resize_max_height'];
		$input_parameters['resize_max_height'] = abs($input_parameters['resize_max_height']);
		
		if(!isset($input_parameters['optimized_image_folder_path'])) {
			$input_parameters['optimized_image_folder_path'] = '';
		}
		
		if(!$input_parameters['optimized_image_folder_path']) {
		
			$input_parameters['optimized_image_folder_path'] = $this->pepvn_UploadsImgFolderPath;
			
			$imgKey1 = array();
			$imgKey1[] = $this->fullDomainName;
			$imgKey1[] = $input_parameters['original_image_src'];
			$imgKey1 = PepVN_Data::createKey($imgKey1);
			$imgKey1 = PepVN_Data::mhash($imgKey1,8);
			
			$input_parameters['optimized_image_folder_path'] .= $imgKey1 . DIRECTORY_SEPARATOR;
			
		}
		

		$keyConfigsProcessedData = '';
		
		if(isset($options['optimize_images_handle_again_files_different_configuration_enable']) && ('on' === $options['optimize_images_handle_again_files_different_configuration_enable'])) {
			
			$fieldsKeysProcessedData = array(
				'optimize_images_watermarks_enable'
				,'optimize_images_watermarks_watermark_position'
				,'optimize_images_watermarks_watermark_type'
				,'optimize_images_watermarks_watermark_text_value'
				,'optimize_images_watermarks_watermark_text_font_name'
				,'optimize_images_watermarks_watermark_text_size'
				,'optimize_images_watermarks_watermark_text_color'
				,'optimize_images_watermarks_watermark_text_margin_x'
				,'optimize_images_watermarks_watermark_text_margin_y'
				,'optimize_images_watermarks_watermark_text_opacity_value'
				,'optimize_images_watermarks_watermark_text_background_enable'
				,'optimize_images_watermarks_watermark_text_background_color'
				,'optimize_images_watermarks_watermark_image_url'
				,'optimize_images_watermarks_watermark_image_width'
				,'optimize_images_watermarks_watermark_image_margin_x'
				,'optimize_images_watermarks_watermark_image_margin_y'
				,'optimize_images_image_quality_value'
				,'optimize_images_rename_img_filename_value'
			);
			
			$keyConfigsProcessedData = array();
			foreach($fieldsKeysProcessedData as $value1) {
				if($value1) {
					if(isset($options[$value1])) {
						$keyConfigsProcessedData[$value1] = $options[$value1];
					}
				}
			}
			
			
			$keyConfigsProcessedData = PepVN_Data::createKey($keyConfigsProcessedData);
		}
		
		$keyConfigsProcessedData = trim($keyConfigsProcessedData);
		
		$keyFileConfigsProcessedData = $keyConfigsProcessedData;
		$keyFileConfigsProcessedData = md5($keyFileConfigsProcessedData);
		$keyFileConfigsProcessedData = PepVN_Data::mhash($keyFileConfigsProcessedData,2);
		
		$input_parameters['optimized_image_file_name'] = PepVN_Data::replaceSpecialChar($input_parameters['optimized_image_file_name'],' ');
		$input_parameters['optimized_image_file_name'] = PepVN_Data::removeVietnameseSign($input_parameters['optimized_image_file_name']);
		$input_parameters['optimized_image_file_name'] = PepVN_Data::reduceSpace($input_parameters['optimized_image_file_name']);
		$input_parameters['optimized_image_file_name'] = trim($input_parameters['optimized_image_file_name']);
		$input_parameters['optimized_image_file_name'] = preg_replace('#[ \t]+#i','-',$input_parameters['optimized_image_file_name']);
		
		$imgOptimizedFilePathExists1  = false;
		$input_parameters['optimized_image_file_name'] = $this->optimize_images_fixFileName($input_parameters['optimized_image_file_name']);
		$imgOptimizedFilePath1 = $input_parameters['optimized_image_folder_path'] . $input_parameters['optimized_image_file_name'] . '-' . $keyFileConfigsProcessedData;
		
		if( 
			($input_parameters['resize_max_width']>0)
			|| ($input_parameters['resize_max_height']>0)
		) {
			$imgOptimizedFilePath1 .= '-'.$input_parameters['resize_max_width'].'x'.$input_parameters['resize_max_height'];
		}
		
		foreach($this->imgExtensionsAllow as $keyTwo => $valueTwo) {
			if($valueTwo) {
				$valueTwoTemp1 = $imgOptimizedFilePath1.'.'.$valueTwo;
				if(file_exists($valueTwoTemp1)) {
					if(filesize($valueTwoTemp1)>0) {
						$imgOptimizedFilePathExists1 = $valueTwoTemp1;
						break;
					}
				}
			
			}
		}
		
		if($imgOptimizedFilePathExists1) {
			$resultData['image_optimized_file_path'] = $imgOptimizedFilePathExists1;
			$resultData['image_optimized_file_url'] = str_replace($this->pepvn_UploadsImgFolderPath,$this->pepvn_UploadsImgFolderUrl,$resultData['image_optimized_file_path']);
		}
		
		
		
		$checkStatus1 = false;
		
		if(isset($input_parameters['action']) && $input_parameters['action']) {
			if('do_process_image' === $input_parameters['action']) {
				$checkStatus1 = true;
			}
		}
		
		if(!$checkStatus1) {
			return $resultData;
		}
		
		if(isset($input_parameters['original_image_src']) && $input_parameters['original_image_src']) {
			//if(preg_match('#^https?://.+#i', $input_parameters['original_image_src'], $matched1)) {
			if(PepVN_Data::isUrl($input_parameters['original_image_src'])) {
				
				$imgOptimizedFilePath = false;
				
				
				
				if($input_parameters['optimized_image_folder_path'] && file_exists($input_parameters['optimized_image_folder_path'])) {
				} else {
					PepVN_Data::createFolder($input_parameters['optimized_image_folder_path'], WPOPTIMIZEBYXTRAFFIC_CHMOD);
					PepVN_Data::chmod($input_parameters['optimized_image_folder_path'],WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
					
				}
				
				if(file_exists($input_parameters['optimized_image_folder_path'])  && PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($input_parameters['optimized_image_folder_path']))) {
					
					$originalImageSrcMd5 = md5($input_parameters['original_image_src']);
					
					$filePathStoreConfigsProcessedData = $input_parameters['optimized_image_folder_path'].'processed_data.txt';
					
					$processedData = array();
					
					if($keyConfigsProcessedData) {
						if(file_exists($filePathStoreConfigsProcessedData)) {
							$valueTemp = @file_get_contents($filePathStoreConfigsProcessedData);
							if($valueTemp) {
								$valueTemp = unserialize($valueTemp);
								if($valueTemp && is_array($valueTemp)) {
									$processedData = $valueTemp;
								}
							}
						}
						
					}
					
					
					$self_ImgExtensionsAllow = $this->imgExtensionsAllow;
					foreach($self_ImgExtensionsAllow as $value1) {
						
						$checkStatus1 = false;
						
						$imgOptimizedFilePath2 = $imgOptimizedFilePath1.'.'.$value1;
						
						if($imgOptimizedFilePath2) {
							if(file_exists($imgOptimizedFilePath2)) {
								if(filesize($imgOptimizedFilePath2)>0) {
									$checkStatus1 = true;
									if($keyConfigsProcessedData) {
										$checkStatus1 = false;
										
										if(isset($processedData[$originalImageSrcMd5])) {
											if($processedData[$originalImageSrcMd5] === $keyConfigsProcessedData) {
												$checkStatus1 = true;
											}
										}
									}
									
								} else {
									$filemtimeTemp1 = filemtime($imgOptimizedFilePath2);
									if($filemtimeTemp1) {
										$filemtimeTemp1 = (int)$filemtimeTemp1;
										if((time() - $filemtimeTemp1) <= (86400 * 7)) {
											return $resultData;
										} else {
											unlink($imgOptimizedFilePath2);
										}
									}
								}
								
							}
						}
						
						if($checkStatus1) {
							$resultData['image_optimized_file_path'] = $imgOptimizedFilePath2;
							$resultData['image_optimized_file_url'] = str_replace($this->pepvn_UploadsImgFolderPath,$this->pepvn_UploadsImgFolderUrl,$resultData['image_optimized_file_path']);
							break;
						}
					}
					
					
					if($keyConfigsProcessedData) {
						$processedData[$originalImageSrcMd5] = $keyConfigsProcessedData;
						@file_put_contents($filePathStoreConfigsProcessedData,serialize($processedData));
					}
					
					
					if(!$resultData['image_optimized_file_path']) {
					
						$imgFolderCachePath = $this->pepvn_ImgFolderCachePath;
						
						if($imgFolderCachePath && file_exists($imgFolderCachePath)) {
						} else {
							
										
							PepVN_Data::createFolder($imgFolderCachePath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
							PepVN_Data::chmod($imgFolderCachePath,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
							
						}
						
						if($imgFolderCachePath && file_exists($imgFolderCachePath)  && PepVN_Data::isAllowReadAndWrite(PepVN_Data::getFolderPath($imgFolderCachePath))) {
							
					
						
						
							$imgOriginalCacheFilePath = $imgFolderCachePath . DIRECTORY_SEPARATOR;
							$imgOriginalCacheFilePath .= md5($input_parameters['original_image_src']).'.txt'; 
							
							if(!file_exists($imgOriginalCacheFilePath)) {
								@file_put_contents($imgOriginalCacheFilePath,'');
								$imgSrcContent = $this->quickGetUrlContent($input_parameters['original_image_src']);
								
								if($imgSrcContent) {
									@file_put_contents($imgOriginalCacheFilePath,$imgSrcContent);
								}
							}
							
							
							if(file_exists($imgOriginalCacheFilePath)) {
								$valueTemp1 = filesize($imgOriginalCacheFilePath);
								if($valueTemp1 && ($valueTemp1>0)) {
									
									if($this->optimize_images_IsImageFilePathCanProcess($imgOriginalCacheFilePath)) {
										
										$imgOriginalCacheFile_RsGetImageFileInfo = $this->optimize_images_getImageFileInfo($imgOriginalCacheFilePath);
										
										if(isset($imgOriginalCacheFile_RsGetImageFileInfo['image_type']) && $imgOriginalCacheFile_RsGetImageFileInfo['image_type']) {
											
											PepVN_Data::$defaultParams['optimize_images']['number_images_processed_request']++;
											
											$imgOptimizedFilePath1 .= '.'.$imgOriginalCacheFile_RsGetImageFileInfo['image_type'];
											
											if(!file_exists($imgOptimizedFilePath1)) {
												@file_put_contents($imgOptimizedFilePath1,'');
											}
											
											
											
											$isCanProcessFileStatus1 = true;
											
											if(
												($options['optimize_images_file_minimum_width_height']>0) 
												|| ($options['optimize_images_file_maximum_width_height']>0) 
											) {
												
												$isCanProcessFileStatus1 = false;
												
												if(
													isset($imgOriginalCacheFile_RsGetImageFileInfo['width'])
													&& ($imgOriginalCacheFile_RsGetImageFileInfo['width'] > 0)
													&& isset($imgOriginalCacheFile_RsGetImageFileInfo['height'])
													&& ($imgOriginalCacheFile_RsGetImageFileInfo['height'] > 0)
												) {
													
													$isCanProcessFileStatus1 = true;
													
													if($isCanProcessFileStatus1) {
														if($options['optimize_images_file_minimum_width_height']>0) {
															if(
																($imgOriginalCacheFile_RsGetImageFileInfo['width'] >= $options['optimize_images_file_minimum_width_height'])
																&& ($imgOriginalCacheFile_RsGetImageFileInfo['height'] >= $options['optimize_images_file_minimum_width_height'])
															) {
																
															} else {
																$isCanProcessFileStatus1 = false;
															}
														}
													}
													
													if($isCanProcessFileStatus1) {
														if($options['optimize_images_file_maximum_width_height']>0) {
															if(
																($imgOriginalCacheFile_RsGetImageFileInfo['width'] <= $options['optimize_images_file_maximum_width_height'])
																&& ($imgOriginalCacheFile_RsGetImageFileInfo['height'] <= $options['optimize_images_file_maximum_width_height'])
															) {
																
															} else {
																$isCanProcessFileStatus1 = false;
															}
														}
													}
												}
												
												
											}
											
											if(!$isCanProcessFileStatus1) {
												return $resultData;
											}
											
											
											$resultData['image_original_file_path'] = $imgOriginalCacheFilePath;
											
											
											$pepVN_PHPImage = new PepVN_PHPImage($imgOriginalCacheFilePath);
											if($options['optimize_images_image_quality_value']>90) {
												$options['optimize_images_image_quality_value'] = 90;
											}
											$pepVN_PHPImage->setQuality($options['optimize_images_image_quality_value']);
											
											
											$imgOriginalCacheFile_RatioWidthPerHeight = $imgOriginalCacheFile_RsGetImageFileInfo['width'] / $imgOriginalCacheFile_RsGetImageFileInfo['height'];
											$imgOriginalCacheFile_RatioWidthPerHeight = (float)$imgOriginalCacheFile_RatioWidthPerHeight;
											

											$resizeImgNewWidth = 0;
											$resizeImgNewHeight = 0;
											
											$isCanWatermarkStatus1 = true;
											
											if(
												($input_parameters['resize_max_width']>0)
												|| ($input_parameters['resize_max_height']>0)
											) {
												if($isCanWatermarkStatus1) {
													if($options['optimize_images_file_minimum_width_height']>0) {
														if(
															($input_parameters['resize_max_width'] >= $options['optimize_images_file_minimum_width_height'])
															&& ($input_parameters['resize_max_height'] >= $options['optimize_images_file_minimum_width_height'])
														) {
															
														} else {
															$isCanWatermarkStatus1 = false;
														}
													}
												}
												
												if($isCanWatermarkStatus1) {
													if($options['optimize_images_file_maximum_width_height']>0) {
														if(
															($input_parameters['resize_max_width'] <= $options['optimize_images_file_maximum_width_height'])
															&& ($input_parameters['resize_max_height'] <= $options['optimize_images_file_maximum_width_height'])
														) {
															
														} else {
															$isCanWatermarkStatus1 = false;
														}
													}
												}
												
												
												$resizeImgNewWidth = $input_parameters['resize_max_width'];
												$resizeImgNewHeight = $input_parameters['resize_max_height'];
												
												if($resizeImgNewWidth>$imgOriginalCacheFile_RsGetImageFileInfo['width']) {
													$resizeImgNewWidth = $imgOriginalCacheFile_RsGetImageFileInfo['width'];
												}
												
												if($resizeImgNewHeight>$imgOriginalCacheFile_RsGetImageFileInfo['height']) {
													$resizeImgNewHeight = $imgOriginalCacheFile_RsGetImageFileInfo['height'];
												}
												
												if($resizeImgNewWidth>0) {
													if(0 === $resizeImgNewHeight) {
														$resizeImgNewHeight = ceil($resizeImgNewWidth / $imgOriginalCacheFile_RatioWidthPerHeight);
													}
												}
												
												if($resizeImgNewHeight>0) {
													if(0 === $resizeImgNewWidth) {
														$resizeImgNewWidth = ceil($resizeImgNewHeight * $imgOriginalCacheFile_RatioWidthPerHeight);
													}
												}
												
												
												if(
													($resizeImgNewWidth>0)
													&& ($resizeImgNewHeight>0)
												) {
													$pepVN_PHPImage->resize($resizeImgNewWidth, $resizeImgNewHeight, false, false);
												}
												
											}

											
											
											if(
												($resizeImgNewWidth>0)
												&& ($resizeImgNewHeight>0)
											) {
												$targetImgNeedWatermark_Width = $resizeImgNewWidth;
												$targetImgNeedWatermark_Height = $resizeImgNewHeight;
											} else {
												$targetImgNeedWatermark_Width = $imgOriginalCacheFile_RsGetImageFileInfo[0];
												$targetImgNeedWatermark_Height = $imgOriginalCacheFile_RsGetImageFileInfo[1];
											}
											

											
											if($paramsWatermarkOptions['text'] && $isCanWatermarkStatus1) {
												
												$watermarkTextBoxWidth = 0;
												$watermarkTextBoxHeight = 0;
												$textFontSize = 12;
												$boxPaddingX = 0;
												$boxPaddingY = 0;
												
												if($paramsWatermarkOptions['text']) {
													
													if(isset($paramsWatermarkOptions['text']['fontSize']) && $paramsWatermarkOptions['text']['fontSize']) {
														$paramsWatermarkOptions['text']['fontSize'] = (int)$paramsWatermarkOptions['text']['fontSize'];
														if($paramsWatermarkOptions['text']['fontSize']>0) {
															$textFontSize = $paramsWatermarkOptions['text']['fontSize'];
														}
													}
													
													
													if(isset($options['optimize_images_watermarks_watermark_text_size']) && $options['optimize_images_watermarks_watermark_text_size']) {
														if(false !== stripos($options['optimize_images_watermarks_watermark_text_size'],'%')) {
															$valueTemp = $options['optimize_images_watermarks_watermark_text_size'];
															$valueTemp = preg_replace('#[^0-9]+#i','',$valueTemp);
															$valueTemp = trim($valueTemp);
															$valueTemp = (int)$valueTemp;
															$valueTemp = abs($valueTemp);
															if($valueTemp>100) {
																$valueTemp = 100;
															}
															
															$watermarkTextBoxWidth = $targetImgNeedWatermark_Width * ($valueTemp / 100);
															$watermarkTextBoxWidth = floor($watermarkTextBoxWidth);
															$watermarkTextBoxWidth = (int)$watermarkTextBoxWidth;
															
															
															$watermarkTextBoxHeight = $imgOriginalCacheFile_RsGetImageFileInfo[1] * ($valueTemp / 100);
															$watermarkTextBoxHeight = floor($watermarkTextBoxHeight);
															$watermarkTextBoxHeight = (int)$watermarkTextBoxHeight;
															
															$textFontSize = PepVN_Images::fitTextSizeToBoxSize(
																0	//fontSize
																,0	//angle
																,$paramsWatermarkOptions['text']['fontFile']	//fontFile
																,$options['optimize_images_watermarks_watermark_text_value']	//text
																,$watermarkTextBoxWidth	//box_width
																,$watermarkTextBoxHeight	//box_height
															);
															
															
															
														}
													}
													
												}
												
												$textFontSize = (int)$textFontSize;
												if($textFontSize > 0) {
													$paramsWatermarkOptions['text']['fontSize'] = $textFontSize;
													
													$rsCalculateText = PepVN_Images::calculateText(
														$textFontSize	//fontSize
														,0	//angle
														,$paramsWatermarkOptions['text']['fontFile']	//fontFile
														,$options['optimize_images_watermarks_watermark_text_value']	//text
													);
													
													$watermarkTextBoxWidth = $rsCalculateText['actualWidth'];
													$watermarkTextBoxHeight = $rsCalculateText['actualHeight'];
												}
												
												$watermarkTextBoxWidth = (int)$watermarkTextBoxWidth;
												$watermarkTextBoxHeight = (int)$watermarkTextBoxHeight;
												
												if(($watermarkTextBoxWidth > 0) && ($watermarkTextBoxHeight > 0)) {
													if(isset($paramsWatermarkOptions['text']['boxColor']) && $paramsWatermarkOptions['text']['boxColor']) {
														
														$boxPaddingX = $paramsWatermarkOptions['text']['boxPaddingX'];
														if(false !== stripos($boxPaddingX,'%')) {
															$boxPaddingX = preg_replace('#[^0-9]+#is','',$boxPaddingX);
															$boxPaddingX = abs((int)$boxPaddingX);
															$boxPaddingX = $watermarkTextBoxWidth * ($boxPaddingX / 100);
														}
														
														$boxPaddingX = abs((int)$boxPaddingX);
														
														$watermarkTextBoxWidth += ($boxPaddingX * 2);
														
														
														
														$boxPaddingY = $paramsWatermarkOptions['text']['boxPaddingY'];
														if(false !== stripos($boxPaddingY,'%')) {
															$boxPaddingY = preg_replace('#[^0-9]+#is','',$boxPaddingY);
															$boxPaddingY = abs((int)$boxPaddingY);
															$boxPaddingY = $watermarkTextBoxWidth * ($boxPaddingY / 100);
														}
														
														$boxPaddingY = abs((int)$boxPaddingY);
														
														$watermarkTextBoxHeight += ($boxPaddingY * 2);
														
													}
												}
												
												
												$options['optimize_images_watermarks_watermark_text_margin_x'] = (int)$options['optimize_images_watermarks_watermark_text_margin_x'];
												$options['optimize_images_watermarks_watermark_text_margin_y'] = (int)$options['optimize_images_watermarks_watermark_text_margin_y'];
												
												if(isset($options['optimize_images_watermarks_watermark_position']) && $options['optimize_images_watermarks_watermark_position']) {
													$options_optimize_images_watermarks_watermark_position = $options['optimize_images_watermarks_watermark_position'];
													$options_optimize_images_watermarks_watermark_position = (array)$options_optimize_images_watermarks_watermark_position;
													foreach($options_optimize_images_watermarks_watermark_position as $value1) {
														if($value1) {
															$value1 = trim($value1);
															if($value1) {
															
																if($paramsWatermarkOptions['text']) {
																	
																	if(($watermarkTextBoxWidth > 0) && ($watermarkTextBoxHeight > 0)) {
																		
																		$paramsWatermarkOptions_Temp1 = $paramsWatermarkOptions['text'];
																		
																		if(false !== stripos($value1,'top_')) {
																			$paramsWatermarkOptions_Temp1['y'] = 0 + abs((int)$options['optimize_images_watermarks_watermark_text_margin_y']);
																		} else if(false !== stripos($value1,'middle_')) {
																			$paramsWatermarkOptions_Temp1['y'] = floor(($imgOriginalCacheFile_RsGetImageFileInfo[1] - $watermarkTextBoxHeight)/2) + $options['optimize_images_watermarks_watermark_text_margin_y'];
																		} else if(false !== stripos($value1,'bottom_')) {
																			$paramsWatermarkOptions_Temp1['y'] = $imgOriginalCacheFile_RsGetImageFileInfo[1] - $watermarkTextBoxHeight - abs((int)$options['optimize_images_watermarks_watermark_text_margin_y']);
																		}
																		
																		
																		if(false !== stripos($value1,'_left')) {
																			$paramsWatermarkOptions_Temp1['x'] = 0 + abs((int)$options['optimize_images_watermarks_watermark_text_margin_x']);
																		} else if(false !== stripos($value1,'_center')) {
																			$paramsWatermarkOptions_Temp1['x'] = floor(abs($targetImgNeedWatermark_Width - $watermarkTextBoxWidth)/2) + $options['optimize_images_watermarks_watermark_text_margin_x'];
																		} else if(false !== stripos($value1,'_right')) {
																			$paramsWatermarkOptions_Temp1['x'] = $targetImgNeedWatermark_Width - $watermarkTextBoxWidth - abs((int)$options['optimize_images_watermarks_watermark_text_margin_x']);
																		}
																		
																		$paramsWatermarkOptions_Temp1['boxPaddingX'] = abs((int)$boxPaddingX); 
																		$paramsWatermarkOptions_Temp1['boxPaddingY'] = abs((int)$boxPaddingY); 
																		
																		$pepVN_PHPImage->text($options['optimize_images_watermarks_watermark_text_value'], $paramsWatermarkOptions_Temp1);
																		
																		
																		
																	
																	
																	}
																	
																	
																}
															}
															
														}
													
														
													}//loop watermark_position
												}
												
											}
											
											
											if($paramsWatermarkOptions['image'] && $isCanWatermarkStatus1) {
											
												if(isset($paramsWatermarkOptions['image']['watermark_image_file_path']) && $paramsWatermarkOptions['image']['watermark_image_file_path']) {
												
													$options['optimize_images_watermarks_watermark_image_margin_x'] = (int)$options['optimize_images_watermarks_watermark_image_margin_x'];
													$options['optimize_images_watermarks_watermark_image_margin_y'] = (int)$options['optimize_images_watermarks_watermark_image_margin_y'];
													
													$watermarkActualBoxWidth = 0;
													$watermarkActualBoxHeight = 0;
													
													$watermarkNewBoxWidth = 0;
													$watermarkNewBoxHeight = 0;
													
													$watermarkImg_RsGetImageInfo = PepVN_Images::getImageInfo($paramsWatermarkOptions['image']['watermark_image_file_path'],true);
													
													if(isset($watermarkImg_RsGetImageInfo['image_resource']) && $watermarkImg_RsGetImageInfo['image_resource']) {
														$watermarkActualBoxWidth = $watermarkImg_RsGetImageInfo['width'];
														$watermarkActualBoxHeight = $watermarkImg_RsGetImageInfo['height'];
														
													}
													
													$watermarkImg_Resource = false;
													
													$watermarkActualBoxWidth = (int)$watermarkActualBoxWidth;
													$watermarkActualBoxHeight = (int)$watermarkActualBoxHeight;
													
													$watermarkActualBoxRatio_WidthPerHeight = 0;
													
													$watermarkNewBoxWidth = $watermarkActualBoxWidth;
													$watermarkNewBoxHeight = $watermarkActualBoxHeight;
													
													$watermarkImageResource = false;
													
													if(($watermarkActualBoxWidth>0) && ($watermarkActualBoxHeight>0)) {
														
														$watermarkActualBoxRatio_WidthPerHeight = $watermarkActualBoxWidth / $watermarkNewBoxHeight;
														$watermarkActualBoxRatio_WidthPerHeight = (float)$watermarkActualBoxRatio_WidthPerHeight;
														
														if(isset($options['optimize_images_watermarks_watermark_image_width']) && $options['optimize_images_watermarks_watermark_image_width']) {
															
															if(false !== stripos($options['optimize_images_watermarks_watermark_image_width'],'%')) {
																
																$percentNewSize = $options['optimize_images_watermarks_watermark_image_width'];
																$percentNewSize = preg_replace('#[^0-9]+#i','',$percentNewSize);
																$percentNewSize = abs((int)$percentNewSize);
																if($percentNewSize>100) {
																	$percentNewSize = 100;
																}
																
																$watermarkNewBoxWidth = floor($targetImgNeedWatermark_Width * ($percentNewSize/100));
																$watermarkNewBoxWidth = (int)$watermarkNewBoxWidth;
																
																$watermarkNewBoxHeight = floor($watermarkNewBoxWidth / $watermarkActualBoxRatio_WidthPerHeight);
																$watermarkNewBoxHeight = (int)$watermarkNewBoxHeight;
																
															} else {
																$options['optimize_images_watermarks_watermark_image_width'] = abs((int)$options['optimize_images_watermarks_watermark_image_width']);
																if($options['optimize_images_watermarks_watermark_image_width']>0) {
																	
																	$watermarkNewBoxWidth = $options['optimize_images_watermarks_watermark_image_width'];
																	
																	$watermarkNewBoxHeight = floor($watermarkNewBoxWidth / $watermarkActualBoxRatio_WidthPerHeight);
																	$watermarkNewBoxHeight = (int)$watermarkNewBoxHeight;
																}
																
															}
														}
														
														$watermarkImg_Resource = PepVN_Images::create_blank_transparent_image_resource($watermarkNewBoxWidth,$watermarkNewBoxHeight);
														@imagecopyresampled(
															$watermarkImg_Resource,	//dst_image
															$watermarkImg_RsGetImageInfo['image_resource'],	//src_image
															0,	//dst_x
															0,	//dst_y
															0,	//src_x
															0,	//src_y
															$watermarkNewBoxWidth,	//dst_w
															$watermarkNewBoxHeight,	//dst_h
															$watermarkActualBoxWidth,	//src_w
															$watermarkActualBoxHeight	//src_h
														);
														
														if($watermarkImg_Resource && is_resource($watermarkImg_Resource) && isset($options['optimize_images_watermarks_watermark_position']) && $options['optimize_images_watermarks_watermark_position']) {
															$options_optimize_images_watermarks_watermark_position = $options['optimize_images_watermarks_watermark_position'];
															$options_optimize_images_watermarks_watermark_position = (array)$options_optimize_images_watermarks_watermark_position;
															foreach($options_optimize_images_watermarks_watermark_position as $value1) {
																if($value1) {
																	$value1 = trim($value1);
																	if($value1) {
																		
																		$paramsWatermarkOptions_Temp1 = array();
																		$paramsWatermarkOptions_Temp1['x'] = 0;
																		$paramsWatermarkOptions_Temp1['y'] = 0;
																		
																		if(false !== stripos($value1,'top_')) {
																			$paramsWatermarkOptions_Temp1['y'] = 0 + abs((int)$options['optimize_images_watermarks_watermark_image_margin_y']);
																		} else if(false !== stripos($value1,'middle_')) {
																			$paramsWatermarkOptions_Temp1['y'] = floor(($imgOriginalCacheFile_RsGetImageFileInfo[1] - $watermarkNewBoxHeight)/2) + $options['optimize_images_watermarks_watermark_image_margin_y'];
																		} else if(false !== stripos($value1,'bottom_')) {
																			$paramsWatermarkOptions_Temp1['y'] = $imgOriginalCacheFile_RsGetImageFileInfo[1] - $watermarkNewBoxHeight - abs((int)$options['optimize_images_watermarks_watermark_image_margin_y']);
																		}
																		
																		
																		if(false !== stripos($value1,'_left')) {
																			$paramsWatermarkOptions_Temp1['x'] = 0 + abs((int)$options['optimize_images_watermarks_watermark_image_margin_x']);
																		} else if(false !== stripos($value1,'_center')) {
																			$paramsWatermarkOptions_Temp1['x'] = floor(abs($targetImgNeedWatermark_Width - $watermarkNewBoxWidth)/2) + $options['optimize_images_watermarks_watermark_image_margin_x'];
																		} else if(false !== stripos($value1,'_right')) {
																			$paramsWatermarkOptions_Temp1['x'] = $targetImgNeedWatermark_Width - $watermarkNewBoxWidth - abs((int)$options['optimize_images_watermarks_watermark_image_margin_x']);
																		}
																																			
																		
																		$pepVN_PHPImage->drawFromResource(
																			$watermarkImg_Resource
																			,$paramsWatermarkOptions_Temp1
																		);
																		
																	}
																	
																}
															
																
															}//loop watermark_position
														}
														
														
														
														
														
														
														
													}
													
													
													if(
														isset($watermarkImg_RsGetImageInfo['image_resource'])
														&& $watermarkImg_RsGetImageInfo['image_resource'] 
														&& is_resource($watermarkImg_RsGetImageInfo['image_resource'])
													) {
														imagedestroy($watermarkImg_RsGetImageInfo['image_resource']);
													}
													$watermarkImg_RsGetImageInfo = false;
													
													if(
														$watermarkImg_Resource
														&& is_resource($watermarkImg_Resource)
													) {
														imagedestroy($watermarkImg_Resource);
													}
													$watermarkImg_Resource = false;
												}
												
												
											}
											
											
											
											$pepVN_PHPImage->save($imgOptimizedFilePath1,false,true);
											$pepVN_PHPImage->cleanup();
											
										}
										
										$valueTemp1 = $imgOptimizedFilePath1;
										
										if($valueTemp1 && file_exists($valueTemp1)) {
											$valueTemp2 = filesize($valueTemp1);
											if($valueTemp2 && ($valueTemp2>0)) {
												
												$resultData['image_optimized_file_path'] = $valueTemp1;
												$resultData['image_optimized_file_url'] = str_replace($this->pepvn_UploadsImgFolderPath,$this->pepvn_UploadsImgFolderUrl,$resultData['image_optimized_file_path']);
												
											}
											
										}
										
									}
									
								}
							}
							
							
						}
						
					}
					
					
					if($resultData['image_optimized_file_path']) {
						
						if($keyConfigsProcessedData) {
						
							if(isset($options['optimize_images_remove_files_available_different_configuration_enable']) && ('on' === $options['optimize_images_remove_files_available_different_configuration_enable'])) {
								
								$this->optimize_images_clean_unuse_image(array(
									'optimized_image_folder_path' => $input_parameters['optimized_image_folder_path']
									,'key_file' => $keyFileConfigsProcessedData
								));
								
							}
						}
						
					}
					
					
					
				}
				
				
			
			
			}
		
		}
		
		
		
		
		
		
		
		
		
		return $resultData;
	}
	
	
	
	public function optimize_images_setting_watermarks_first_options($input_parameters)
	{
		$resultData = array(
		);
		
		$options = false;
		
		if(isset($input_parameters['options']) && $input_parameters['options']) {
			$options = $input_parameters['options'];
			$input_parameters['options'] = '';
		}
		
		if(!$options) {
			$options = $this->get_options(array(
				'cache_status' => 1
			));
		}
		
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,$options
			,'optimize_images_setting_watermarks_first_options'
		));
		
		if(isset($this->baseCacheData[$keyCache1]) && $this->baseCacheData[$keyCache1]) {
			return $this->baseCacheData[$keyCache1];
		}
		
		$options['optimize_images_watermarks_watermark_text_background_opacity_value'] = 100;
		$options['optimize_images_watermarks_watermark_opacity_value'] = 100;
		
		$options['optimize_images_watermarks_watermark_text_outline_enable'] = '';
		
		
		
		//setting watermark
		$paramsWatermarkOptions = array();
		$paramsWatermarkOptions['text'] = array(
		);
		
		$paramsWatermarkOptions['image'] = array(
		);
		
		
		if(isset($options['optimize_images_watermarks_watermark_type']) && $options['optimize_images_watermarks_watermark_type']) {
			
			$options['optimize_images_watermarks_watermark_type'] = (array)$options['optimize_images_watermarks_watermark_type'];
			
			if(isset($options['optimize_images_watermarks_watermark_position']) && $options['optimize_images_watermarks_watermark_position']) {
				$options['optimize_images_watermarks_watermark_position'] = (array)$options['optimize_images_watermarks_watermark_position'];
				$options['optimize_images_watermarks_watermark_position'] = implode(';',$options['optimize_images_watermarks_watermark_position']);
				$options['optimize_images_watermarks_watermark_position'] = PepVN_Data::explode(';',$options['optimize_images_watermarks_watermark_position']);
				$options['optimize_images_watermarks_watermark_position'] = PepVN_Data::cleanArray($options['optimize_images_watermarks_watermark_position']);
				if($options['optimize_images_watermarks_watermark_position']) {
					$options['optimize_images_watermarks_watermark_position'] = array_unique($options['optimize_images_watermarks_watermark_position']);
					$options['optimize_images_watermarks_watermark_position'] = array_values($options['optimize_images_watermarks_watermark_position']);
					if(count($options['optimize_images_watermarks_watermark_position'])>0) {
						
						$options['optimize_images_watermarks_watermark_opacity_value'] = (int)$options['optimize_images_watermarks_watermark_opacity_value'];
						$options['optimize_images_watermarks_watermark_opacity_value'] = abs($options['optimize_images_watermarks_watermark_opacity_value']);
						if($options['optimize_images_watermarks_watermark_opacity_value'] > 0) {
							
							if($options['optimize_images_watermarks_watermark_opacity_value'] > 100) {
								$options['optimize_images_watermarks_watermark_opacity_value'] = 100;
							}
							
							if(in_array('text',$options['optimize_images_watermarks_watermark_type'])) {
								
								if(isset($options['optimize_images_watermarks_watermark_text_value']) && $options['optimize_images_watermarks_watermark_text_value']) {
									$options['optimize_images_watermarks_watermark_text_value'] = trim($options['optimize_images_watermarks_watermark_text_value']);
									if($options['optimize_images_watermarks_watermark_text_value']) {
									
										if(isset($options['optimize_images_watermarks_watermark_text_font_name']) && $options['optimize_images_watermarks_watermark_text_font_name']) {
											$options['optimize_images_watermarks_watermark_text_font_name'] = preg_replace('#[^a-z0-9\-\_]+#i','',$options['optimize_images_watermarks_watermark_text_font_name']);
											$options['optimize_images_watermarks_watermark_text_font_name'] = strtolower($options['optimize_images_watermarks_watermark_text_font_name']);
											$options['optimize_images_watermarks_watermark_text_font_name'] = trim($options['optimize_images_watermarks_watermark_text_font_name']);
											if($options['optimize_images_watermarks_watermark_text_font_name']) {
											
												if(isset($options['optimize_images_watermarks_watermark_text_size']) && $options['optimize_images_watermarks_watermark_text_size']) {
													$options['optimize_images_watermarks_watermark_text_size'] = trim($options['optimize_images_watermarks_watermark_text_size']);
													if($options['optimize_images_watermarks_watermark_text_size']) {
													
														if(isset($options['optimize_images_watermarks_watermark_text_color']) && $options['optimize_images_watermarks_watermark_text_color']) {
															$options['optimize_images_watermarks_watermark_text_color'] = preg_replace('#[^a-z0-9]+#i','',$options['optimize_images_watermarks_watermark_text_color']);
															$options['optimize_images_watermarks_watermark_text_color'] = strtolower($options['optimize_images_watermarks_watermark_text_color']);
															$options['optimize_images_watermarks_watermark_text_color'] = trim($options['optimize_images_watermarks_watermark_text_color']);
															if($options['optimize_images_watermarks_watermark_text_color']) {
																
																if(isset($options['optimize_images_watermarks_watermark_text_opacity_value']) && $options['optimize_images_watermarks_watermark_text_opacity_value']) {
																	$options['optimize_images_watermarks_watermark_text_opacity_value'] = trim($options['optimize_images_watermarks_watermark_text_opacity_value']);
																	if($options['optimize_images_watermarks_watermark_text_opacity_value']) {
																	
																		$options['optimize_images_watermarks_watermark_text_opacity_value'] = (int)$options['optimize_images_watermarks_watermark_text_opacity_value'];
																		$options['optimize_images_watermarks_watermark_text_opacity_value'] = abs($options['optimize_images_watermarks_watermark_text_opacity_value']);
																		if($options['optimize_images_watermarks_watermark_text_opacity_value']>100) {
																			$options['optimize_images_watermarks_watermark_text_opacity_value'] = 100;
																		}
																		
																		
																		
																		
																		$paramsWatermarkOptions['text'] = array(
																			'fontSize' => 12,
																			'fontColor' => PepVN_Data::hex2rgb('#'.$options['optimize_images_watermarks_watermark_text_color']),
																			'opacity' => ($options['optimize_images_watermarks_watermark_text_opacity_value']/100),//0.66
																			'x' => 10,
																			'y' => 10,
																			'width' => null,
																			'height' => null, 
																			'alignHorizontal' => 'center', 
																			'alignVertical' => 'center',
																			'angle' => 0,
																			'strokeWidth' => 0,
																			'strokeColor' => '',
																			'fontFile' => $this->fontFolderPath.$options['optimize_images_watermarks_watermark_text_font_name'].'.ttf',
																			'autoFit' => false,
																			
																			'boxColor' => 0,
																			'boxOpacity' => ($options['optimize_images_watermarks_watermark_text_opacity_value']/100),
																			'boxPaddingX' => '3%',
																			'boxPaddingY' => '4%', 
																			
																			'debug' => false 
																		);
																		
																		
																		
																		if(isset($options['optimize_images_watermarks_watermark_text_size']) && $options['optimize_images_watermarks_watermark_text_size']) {
																			$options['optimize_images_watermarks_watermark_text_size'] = preg_replace('#[^0-9\%]+#i','',$options['optimize_images_watermarks_watermark_text_size']);
																			$options['optimize_images_watermarks_watermark_text_size'] = trim($options['optimize_images_watermarks_watermark_text_size']);
																			if(false === stripos($options['optimize_images_watermarks_watermark_text_size'],'%')) {
																				$paramsWatermarkOptions['text']['fontSize'] = (int)$options['optimize_images_watermarks_watermark_text_size'];
																			}
																		}
																		
																		
																		
																		if(isset($options['optimize_images_watermarks_watermark_text_background_enable']) && $options['optimize_images_watermarks_watermark_text_background_enable']) {
																			
																			if(isset($options['optimize_images_watermarks_watermark_text_background_color']) && $options['optimize_images_watermarks_watermark_text_background_color']) {
																				$options['optimize_images_watermarks_watermark_text_background_color'] = preg_replace('#[^a-z0-9]+#i','',$options['optimize_images_watermarks_watermark_text_background_color']);
																				$options['optimize_images_watermarks_watermark_text_background_color'] = strtolower($options['optimize_images_watermarks_watermark_text_background_color']);
																				$options['optimize_images_watermarks_watermark_text_background_color'] = trim($options['optimize_images_watermarks_watermark_text_background_color']);
																				if($options['optimize_images_watermarks_watermark_text_background_color']) {
																					
																					$paramsWatermarkOptions['text']['boxColor'] = PepVN_Data::hex2rgb('#'.$options['optimize_images_watermarks_watermark_text_background_color']);
																					
																					if(isset($options['optimize_images_watermarks_watermark_text_background_opacity_value']) && $options['optimize_images_watermarks_watermark_text_background_opacity_value']) {
																						$options['optimize_images_watermarks_watermark_text_background_opacity_value'] = trim($options['optimize_images_watermarks_watermark_text_background_opacity_value']);
																						if($options['optimize_images_watermarks_watermark_text_background_opacity_value']) {
																						
																							$options['optimize_images_watermarks_watermark_text_background_opacity_value'] = (int)$options['optimize_images_watermarks_watermark_text_background_opacity_value'];
																							$options['optimize_images_watermarks_watermark_text_background_opacity_value'] = abs($options['optimize_images_watermarks_watermark_text_background_opacity_value']);
																							if($options['optimize_images_watermarks_watermark_text_background_opacity_value']>100) {
																								$options['optimize_images_watermarks_watermark_text_background_opacity_value'] = 100;
																							}
																							
																							
																							$paramsWatermarkOptions['text']['boxOpacity'] = $options['optimize_images_watermarks_watermark_text_opacity_value'] / 100;
																							
																						}
																					}
																					
																					
																				}
																			}
																		}//optimize_images_watermarks_watermark_text_background_enable
																		
																		
																		
																		
																		if(isset($options['optimize_images_watermarks_watermark_text_outline_enable']) && $options['optimize_images_watermarks_watermark_text_outline_enable']) {
																			
																			
																			if(isset($options['optimize_images_watermarks_watermark_text_outline_width']) && $options['optimize_images_watermarks_watermark_text_outline_width']) {
																				$options['optimize_images_watermarks_watermark_text_outline_width'] = preg_replace('#[^0-9]+#i','',$options['optimize_images_watermarks_watermark_text_outline_width']);
																				$options['optimize_images_watermarks_watermark_text_outline_width'] = (int)$options['optimize_images_watermarks_watermark_text_outline_width'];
																				if($options['optimize_images_watermarks_watermark_text_outline_width']>0) {
																				
																					if(isset($options['optimize_images_watermarks_watermark_text_outline_color']) && $options['optimize_images_watermarks_watermark_text_outline_color']) {
																						$options['optimize_images_watermarks_watermark_text_outline_color'] = preg_replace('#[^a-z0-9]+#i','',$options['optimize_images_watermarks_watermark_text_outline_color']);
																						$options['optimize_images_watermarks_watermark_text_outline_color'] = strtolower($options['optimize_images_watermarks_watermark_text_outline_color']);
																						$options['optimize_images_watermarks_watermark_text_outline_color'] = trim($options['optimize_images_watermarks_watermark_text_outline_color']);
																						if($options['optimize_images_watermarks_watermark_text_outline_color']) {
																							
																							$paramsWatermarkOptions['text']['strokeWidth'] = $options['optimize_images_watermarks_watermark_text_outline_width'];
																							$paramsWatermarkOptions['text']['strokeColor'] = PepVN_Data::hex2rgb('#'.$options['optimize_images_watermarks_watermark_text_outline_color']);
																							
																						}
																					}
																				
																				
																				}
																			}
																			
																			
																			
																		}//optimize_images_watermarks_watermark_text_outline_enable
																	
																	
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
								
								
							}//type text 
							
							
							if(in_array('image',$options['optimize_images_watermarks_watermark_type'])) {
							
								
								if(isset($options['optimize_images_watermarks_watermark_image_url']) && $options['optimize_images_watermarks_watermark_image_url']) {
									$options['optimize_images_watermarks_watermark_image_url'] = trim($options['optimize_images_watermarks_watermark_image_url']);
									if($options['optimize_images_watermarks_watermark_image_url']) {
									
										if(PepVN_Data::isUrl($options['optimize_images_watermarks_watermark_image_url']) && PepVN_Data::isImg($options['optimize_images_watermarks_watermark_image_url'])) {
										
											$imgWatermarkFilePath1 = $this->pepvn_ImgFolderCachePath;
											$imgWatermarkFilePath1 .= md5($options['optimize_images_watermarks_watermark_image_url']).'.txt';
											
											if(!file_exists($imgWatermarkFilePath1)) {
												@file_put_contents($imgWatermarkFilePath1,'');
												$valueTemp1 = $this->quickGetUrlContent($options['optimize_images_watermarks_watermark_image_url']);
												
												if($valueTemp1) {
													@file_put_contents($imgWatermarkFilePath1,$valueTemp1);
												}
											}
											
											
											if(file_exists($imgWatermarkFilePath1)) {
												$valueTemp1 = filesize($imgWatermarkFilePath1);
												if($valueTemp1 && ($valueTemp1>0)) {
													
													if($this->optimize_images_IsImageFilePathCanProcess($imgWatermarkFilePath1)) {
														
														$paramsWatermarkOptions['image']['watermark_image_file_path'] = $imgWatermarkFilePath1;
														
														
														
														if(isset($options['optimize_images_watermarks_watermark_image_width']) && $options['optimize_images_watermarks_watermark_image_width']) {
															$options['optimize_images_watermarks_watermark_image_width'] = preg_replace('#[^0-9\%]+#i','',$options['optimize_images_watermarks_watermark_image_width']);
															$options['optimize_images_watermarks_watermark_image_width'] = trim($options['optimize_images_watermarks_watermark_image_width']);
															if(false === stripos($options['optimize_images_watermarks_watermark_image_width'],'%')) {
																$options['optimize_images_watermarks_watermark_image_width'] = (int)$options['optimize_images_watermarks_watermark_image_width'];
															}
														}
														
														if(isset($options['optimize_images_watermarks_watermark_image_margin_x']) && $options['optimize_images_watermarks_watermark_image_margin_x']) {
															$options['optimize_images_watermarks_watermark_image_margin_x'] = preg_replace('#[^0-9]+#i','',$options['optimize_images_watermarks_watermark_image_margin_x']);
															$options['optimize_images_watermarks_watermark_image_margin_x'] = trim($options['optimize_images_watermarks_watermark_image_margin_x']);
															$options['optimize_images_watermarks_watermark_image_margin_x'] = (int)$options['optimize_images_watermarks_watermark_image_margin_x'];
															$options['optimize_images_watermarks_watermark_image_margin_x'] = abs($options['optimize_images_watermarks_watermark_image_margin_x']);
															
														}
														
														if(isset($options['optimize_images_watermarks_watermark_image_margin_y']) && $options['optimize_images_watermarks_watermark_image_margin_y']) {
															$options['optimize_images_watermarks_watermark_image_margin_y'] = preg_replace('#[^0-9]+#i','',$options['optimize_images_watermarks_watermark_image_margin_y']);
															$options['optimize_images_watermarks_watermark_image_margin_y'] = trim($options['optimize_images_watermarks_watermark_image_margin_y']);
															$options['optimize_images_watermarks_watermark_image_margin_y'] = (int)$options['optimize_images_watermarks_watermark_image_margin_y'];
															$options['optimize_images_watermarks_watermark_image_margin_y'] = abs($options['optimize_images_watermarks_watermark_image_margin_y']);
															
														}
														
														
														
													}
												}
											}
														
										}
									}
								}
								
							
							
							}//type image
							
							
							
							
						}
						
						
						
						
					}
				}
				
			}
			
			
			
			
			
			
		}
		
		
		
		
		$options['optimize_images_image_quality_value'] = abs((int)$options['optimize_images_image_quality_value']);
		if(($options['optimize_images_image_quality_value'] >= 10) && ($options['optimize_images_image_quality_value'] <= 100)) {
		} else {
			$options['optimize_images_image_quality_value'] = 100;
		}
		
		
		$options['optimize_images_rename_img_filename_value'] = (string)$options['optimize_images_rename_img_filename_value'];
		$options['optimize_images_rename_img_filename_value'] = trim($options['optimize_images_rename_img_filename_value']);
		
		
		$options['optimize_images_maximum_files_handled_each_request'] = abs((int)$options['optimize_images_maximum_files_handled_each_request']);
			
		$resultData['options'] = $options;
		$resultData['paramsWatermarkOptions'] = $paramsWatermarkOptions;
		
		$this->baseCacheData[$keyCache1] = $resultData;
		
		
		return $resultData;
	}
	
	
	
	public function optimize_images_process_text($text)
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
			
		}
		
		
		
		$currentPostId = 0;
		if(isset($post->ID) && $post->ID) {
			$currentPostId = $post->ID;
		}
		
		$currentPostId = (int)$currentPostId;
		if(!$currentPostId) {
			if(function_exists('get_the_ID')) {
				$valueTemp = get_the_ID();
				$valueTemp = (int)$valueTemp;
				if($valueTemp) {
					$currentPostId = $valueTemp;
				}
			}
		}
		
		
		
		$parametersPrimary = array();
		
		$patternsEscaped = array();
		
		$options = $this->get_options(array(
			'cache_status' => 1
		));
		
		if(!isset($options['optimize_images_alttext'])) {
			$options['optimize_images_alttext'] = '';
		}
		if(!isset($options['optimize_images_titletext'])) {
			$options['optimize_images_titletext'] = '';
		}
		
		$optimize_images_alttext = trim($options['optimize_images_alttext']);
		$optimize_images_titletext = trim($options['optimize_images_titletext']);
		
		
		
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
		$patternsReplaceImgSrc = array();
		$patternsPregReplace = array();
		
		
		
		
		$rsSettingWatermarksFirstOptions = $this->optimize_images_setting_watermarks_first_options(array(
			'options' => $options
		));
		
		$paramsWatermarkOptions = $rsSettingWatermarksFirstOptions['paramsWatermarkOptions'];
		$options = $rsSettingWatermarksFirstOptions['options'];
		
		$rsSettingWatermarksFirstOptions = '';
		
		
		$options['optimize_images_rename_img_filename_value'] = trim($options['optimize_images_rename_img_filename_value']);
		
		
		
		if($this->hostHasToolToProcessImage) {
		
			preg_match_all('#<(\'|\")(https?:)?//'.PepVN_Data::preg_quote($this->fullDomainName).'[^\'\"]+\.('.implode('|',$this->imgExtensionsAllow).')\??[^\'\"]*?\1#is',$text,$matched1);
			
			if(isset($matched1[0]) && $matched1[0] && (!PepVN_Data::isEmptyArray($matched1[0]))) {
				foreach($allTargetElements as $key1 => $value1) {
					if($value1) {
					
						preg_match('#(\'|\")((https?:)?//[^\"\']+)\1#i',$value1,$matched2);
						
						if(isset($matched2[2]) && $matched2[2]) {
							
							$matched2[2] = trim($matched2[2]);
							if($matched2[2]) {
								
								$imgName1 = WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG;
								
								$imgInfo1 = pathinfo($matched2[2]);
								if(isset($imgInfo1['filename'])) {
									$imgName1 = $imgInfo1['filename'];
								}
								
								$rsProcessImage1 = $this->optimize_images_process_image(array(
									'optimized_image_file_name' => trim($imgName1)
									,'original_image_src' => $matched2[2]
									,'action' => 'do_process_image'
								));
								
								
								if($rsProcessImage1['image_optimized_file_url']) {
								
									$valueTemp1 = '//'.PepVN_Data::removeProtocolUrl($matched2[2]);
									$valueTemp2 = '//'.PepVN_Data::removeProtocolUrl($rsProcessImage1['image_optimized_file_url']);
									
									$patternsReplaceImgSrc[$valueTemp1] = $valueTemp2;
									
									
								}
								
								
								
							}
						}
					}
				}
				
			}
			
		}
		
		
		if(preg_match_all('#<img[^>]+\\\?>#i', $text, $matched1)) {
			
			if(isset($matched1[0]) && is_array($matched1[0]) && (count($matched1[0])>0)) {
				
				foreach($matched1[0] as $keyOne => $valueOne) {
					
					$oldImgTag = $valueOne;
					$newImgTag = $valueOne;
					
					$imgTitle1 = '';
					$imgAlt1 = '';
					$imgName = '';
					$imgFullName = '';
					
					$imgSrc = '';
					$imgInfo = false; 
					
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
							$imgSrc = trim($matched2[2]);
							$imgName = trim($matched2[2]);
						}
						
					}
					
					$imgName = trim($imgName);
					if($imgName) {
						$imgInfo = pathinfo($imgName);
						if(isset($imgInfo['filename'])) {
							$imgName = $imgInfo['filename'];
							$imgFullName = $imgInfo['basename'];
							
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
					
					
					
					
					
					if($imgSrc) {
						
						//Begin Process Watermark Image 
						
						$checkStatus2 = false;
						
						if($this->hostHasToolToProcessImage) {
							if(PepVN_Data::isUrl($imgSrc)) { 
								$checkStatus2 = true;
							}
							
						}
						
						if($checkStatus2) {
							
							$imgNewName = $imgName;
							
							
							if($options['optimize_images_rename_img_filename_value']) {
								$optimize_images_rename_img_filename_value = $options['optimize_images_rename_img_filename_value'];
								
								$patterns2 = $patterns1;
								$patterns2['%img_title'] = $imgTitle1;
								$patterns2['%img_alt'] = $imgAlt1;
								$patterns2['%img_name'] = $imgName;
								
								$optimize_images_rename_img_filename_value = str_replace(array_keys($patterns2),array_values($patterns2),$optimize_images_rename_img_filename_value);
								$optimize_images_rename_img_filename_value = PepVN_Data::reduceSpace($optimize_images_rename_img_filename_value);
								
								
								if($optimize_images_rename_img_filename_value) {
									$imgNewName = $optimize_images_rename_img_filename_value;
								}
								
							}
							
							$imgOptimizedFilePathExists1 = false;
							
							$rsProcessImage1 = $this->optimize_images_process_image(array(
								'optimized_image_file_name' => $imgNewName
								,'original_image_src' => $imgSrc
								,'options' => $options
								,'paramsWatermarkOptions' => $paramsWatermarkOptions
							));
							
							
							if($rsProcessImage1['image_optimized_file_path']) {
								$imgOptimizedFilePathExists1 = $rsProcessImage1['image_optimized_file_path'];
							}
							
							if(!$imgOptimizedFilePathExists1) {
							
								if(isset($options['optimize_images_optimize_image_file_enable']) && $options['optimize_images_optimize_image_file_enable']) {
									
									$rsProcessImage1 = $this->optimize_images_process_image(array(
										'optimized_image_file_name' => $imgNewName
										,'original_image_src' => $imgSrc
										,'options' => $options
										,'paramsWatermarkOptions' => $paramsWatermarkOptions
										,'action' => 'do_process_image'
									));
									
									
									if($rsProcessImage1['image_optimized_file_path']) {
										$imgOptimizedFilePathExists1 = $rsProcessImage1['image_optimized_file_path'];
									}
									
								}
								
							}
							
							
							if($imgOptimizedFilePathExists1) {
								if(file_exists($imgOptimizedFilePathExists1)) {
									if(filesize($imgOptimizedFilePathExists1)>0) {
										$imgSrc2 = str_replace($this->pepvn_UploadsImgFolderPath,$this->pepvn_UploadsImgFolderUrl,$imgOptimizedFilePathExists1);
										
										$newImgTag = str_replace($imgSrc,$imgSrc2,$newImgTag);
										
										$patternsReplaceImgSrc[$imgSrc] = $imgSrc2;
										
										//$oldImgSrc2 = preg_replace('#\-[0-9]+x[0-9]+\.(jpe?g|gif|png)$#i','',$imgSrc);
										//$patternsPregReplace['#'.PepVN_Data::preg_quote($oldImgSrc2).'(\-[0-9]+x[0-9]+)?\.(jpe?g|gif|png)#i'] = $imgSrc2;
									}
								}
							}
							
						}
						
					}
					
					if($oldImgTag !== $newImgTag) {
						$patternsReplace[$oldImgTag] = $newImgTag; 
					}
					
				}
			
			}
			
			
			if(count($patternsReplace)>0) {
				$text = str_replace(array_keys($patternsReplace),array_values($patternsReplace),$text);
			}
			
			if(count($patternsPregReplace)>0) {
				$text = preg_replace(array_keys($patternsPregReplace),array_values($patternsPregReplace),$text);
			}
			
			
			if(count($patternsReplaceImgSrc)>0) {
				$imgNewName = preg_replace('#[ \t]+#i','-',$imgNewName);
				foreach($patternsReplaceImgSrc as $keyOne => $valueOne) {
					$text = preg_replace('#([\'\"\s \t]*?)'.PepVN_Data::preg_quote($keyOne).'([\'\"\s \t]*?)#is','\1'.$valueOne.'\2',$text);
				}
				//$text = str_replace(array_keys($patternsReplaceImgSrc),array_values($patternsReplaceImgSrc),$text);
				
			}
			
			
		}
		
		
		if($options['optimize_images_images_lazy_load_enable']) {
			$text = $this->optimize_images_process_allimagestags_lazyload($text,0);
		}
		
		$text = trim($text);
		
		$this->cacheObj->set_cache($keyCacheProcessText,$text);
		
		return $text;

	} 

	/*
	*	$type : 1 : front page ; 0 : posts,pages
	*/
	
	public function optimize_images_process_allimagestags_lazyload($text,$type)
	{
		
		$keyCacheProcessText = PepVN_Data::createKey(array(
			__METHOD__
			,$text
			,$type
			,'optimize_images_process_allimagestags_lazyload'
		));
		
		$valueTemp = $this->cacheObj->get_cache($keyCacheProcessText);
		
		if($valueTemp) {
			return $valueTemp;
		}
		
		
		
		$patternsReplace = array();
		$patternsEscaped = array();
		
		
		
		$rsOne = PepVN_Data::escapeHtmlTagsAndContents($text,'script');
		$text = $rsOne['content'];
		if(count($rsOne['patterns'])>0) {
			$patternsEscaped = array_merge($patternsEscaped, $rsOne['patterns']);
		}
		$rsOne = false;
		
		
		
		/*
		if(0 === $type) {
			$patternMatch = '#<img[^>]+\\\?>#';
		} else {
			$patternMatch = '#<img[^>]+class=(\"|\')[^\'\"]*?(wp-post-image)\\\?>#'; 
		}
		*/
		
		$patternMatch = '#<img[^>]+\\\?>#';
		
		if(preg_match_all($patternMatch, $text, $matched1)) {
			
			if(isset($matched1[0]) && is_array($matched1[0]) && (count($matched1[0])>0)) {
				
				foreach($matched1[0] as $keyOne => $valueOne) {
					
					$rsOne = $this->optimize_images_process_imagetag_lazyload(array(
						'img_tag' => $valueOne
					));
					
					if($rsOne['img_tag']) {
						$patternsReplace[$valueOne] = $rsOne['img_tag']; 
					}
				}
			}
			
		}
		
		if(count($patternsReplace)>0) {
			$text = str_replace(array_keys($patternsReplace),array_values($patternsReplace),$text);
		}
		
		
		if(count($patternsEscaped)>0) {
			$text = str_replace(array_values($patternsEscaped), array_keys($patternsEscaped), $text);
		}
		
		$this->cacheObj->set_cache($keyCacheProcessText,$text);
		
		return $text;
		
	}
	
	
	
	
	public function optimize_images_process_imagetag_lazyload($input_parameters)
	{
		$resultData = array();
		$resultData['img_tag'] = false;
		
		
		$checkStatus1 = false;
		
		$imgSrcOriginal = false;
		
		if(isset($input_parameters['img_tag']) && $input_parameters['img_tag']) {
			
			preg_match('#<img[^>]+src=(\'|\")((https?:)?//[^\'\"]+\.('.implode('|',$this->imgExtensionsAllow).')\??[^\'\"]*?)\1#is',$input_parameters['img_tag'],$matched1);
			
			if(isset($matched1[2]) && $matched1[2]) {
				
				$checkStatus1 = true;
				$imgSrcOriginal = $matched1[2];
			}
		}
		
		if($checkStatus1) {
			if(preg_match('#class=(\'|\")[^\'\"]*?wpxtrlzld[^\'\"]*?#is',$input_parameters['img_tag'],$matched2)) {
				$checkStatus1 = false;
			}
		}
		
		
		if($checkStatus1) {
			$input_parameters['img_tag'] = preg_replace('#src=(\'|\")[^\'\"]*?\1#i',' ',$input_parameters['img_tag']);
			$input_parameters['img_tag'] = preg_replace('#data-original=(\'|\")[^\'\"]*?\1#i',' ',$input_parameters['img_tag']);
			
			$input_parameters['img_tag'] = preg_replace('#<img#i','<img src="'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL.'images/transparent-1x1.gif" ',$input_parameters['img_tag']);
			$input_parameters['img_tag'] = preg_replace('#<img#i','<img data-original="'.$imgSrcOriginal.'" ',$input_parameters['img_tag']);
			
			if(preg_match('#class=(\'|\")#is',$input_parameters['img_tag'],$matched2)) {
				$input_parameters['img_tag'] = preg_replace('#class=(\'|\")#i','class=$1 wpxtrlzld ',$input_parameters['img_tag']);
			} else {
				$input_parameters['img_tag'] = preg_replace('#<img#i','<img class="wpxtrlzld" ',$input_parameters['img_tag']);
			}
			
			$resultData['img_tag'] = $input_parameters['img_tag'];
		}
		
		
		return $resultData;
	}
	
	
	
	public function optimize_images_the_content_filter($text) 
	{
		if(is_single() || is_page() || is_singular()) {
			
		} else {
			return $text;
		}
		
		$text = $this->optimize_images_process_text($text);
		
		return $text;
	}
	
	
	
	
	
	public function optimize_images_preview_processed_image() 
	{
	
		$resultData = array(
			'status' => 1
		);
		
		$checkStatus1 = false;
		
		$dataSent = PepVN_Data::getDataSent();
		if($dataSent && isset($dataSent['localTimeSent']) && $dataSent['localTimeSent']) {
			
			foreach($dataSent as $key1 => $value1) {
				$keyTemp1 = preg_replace('#\[\]$#i','', $key1);
				if($keyTemp1 != $key1) {
					unset($dataSent[$key1]);
					$dataSent[$keyTemp1] = $value1;
				}
			}
		
		
			if(isset($dataSent['optimize_images_watermarks_preview_processed_image_example_image_url']) && $dataSent['optimize_images_watermarks_preview_processed_image_example_image_url']) {
				$imgSrc = $dataSent['optimize_images_watermarks_preview_processed_image_example_image_url'];
				if(PepVN_Data::isUrl($imgSrc)) {
				
					if($this->hostHasToolToProcessImage) {
						
						if(PepVN_Data::isImg($imgSrc)) {
						
							$rsSettingWatermarksFirstOptions = $this->optimize_images_setting_watermarks_first_options(array(
								'options' => $dataSent
							));
							
							$paramsWatermarkOptions = $rsSettingWatermarksFirstOptions['paramsWatermarkOptions'];
							$options = $rsSettingWatermarksFirstOptions['options'];
							
							$preview_Key = PepVN_Data::createKey(array(
								$imgSrc
								,'options' => $options
								,'paramsWatermarkOptions' => $paramsWatermarkOptions
							));
							
							$preview_FolderPath = $this->pepvn_UploadsPreviewImgFolderPath;
							
							PepVN_Data::createFolder($preview_FolderPath, WPOPTIMIZEBYXTRAFFIC_CHMOD);
							PepVN_Data::chmod($preview_FolderPath,WPOPTIMIZEBYXTRAFFIC_PATH,WPOPTIMIZEBYXTRAFFIC_CHMOD);
							
							
							if($preview_FolderPath && file_exists($preview_FolderPath)) {
								
								$rsProcessImage1 = $this->optimize_images_process_image(array(
									'optimized_image_folder_path' => $preview_FolderPath
									,'optimized_image_file_name' => 'preview_'.$preview_Key
									,'original_image_src' => $imgSrc
									,'options' => $options
									,'paramsWatermarkOptions' => $paramsWatermarkOptions
									,'action' => 'do_process_image'
								));
								
								if($rsProcessImage1['image_optimized_file_path']) {
									$imgSrc2 = str_replace($this->pepvn_UploadsImgFolderPath,$this->pepvn_UploadsImgFolderUrl,$rsProcessImage1['image_optimized_file_path']);
									$imgSrc2 .= '?xtrts='.time().mt_rand();
									
									
									$resultData['img_processed_url'] = $imgSrc2;
								}
							}
							
							
							
						}
						
					}
					
				}
				
			}
		}
		
		echo PepVN_Data::encodeResponseData($resultData);
		
	}
	
	

					
	

	public function optimize_images_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
	

		$action_url = $_SERVER['REQUEST_URI'];	

		$optimize_images_override_alt = $options['optimize_images_override_alt'] == 'on' ? 'checked':'';
		$optimize_images_override_title = $options['optimize_images_override_title'] == 'on' ? 'checked':'';
		$optimize_images_alttext = $options['optimize_images_alttext'];
		$optimize_images_titletext = $options['optimize_images_titletext'];
		
		
		
		
		$optimize_images_optimize_image_file_enable = $options['optimize_images_optimize_image_file_enable'] === 'on' ? 'checked':'';
		
		
		//Images lazy load
		$optimize_images_images_lazy_load_enable = $options['optimize_images_images_lazy_load_enable'] === 'on' ? 'checked':'';
		$optimize_images_images_lazy_load_frontpage_enable = $options['optimize_images_images_lazy_load_frontpage_enable'] === 'on' ? 'checked':''; 
		
		
		
		//Watermark Options
		$optimize_images_watermarks_enable = $options['optimize_images_watermarks_enable'] === 'on' ? 'checked':'';
		$optimize_images_watermarks_watermark_position = $options['optimize_images_watermarks_watermark_position'];
		$optimize_images_watermarks_watermark_opacity_value = $options['optimize_images_watermarks_watermark_opacity_value'];
		$optimize_images_watermarks_watermark_type = $options['optimize_images_watermarks_watermark_type'];
		
		$optimize_images_file_minimum_width_height = (int)$options['optimize_images_file_minimum_width_height'];
		$optimize_images_file_maximum_width_height = (int)$options['optimize_images_file_maximum_width_height'];
		
		
		$optimize_images_watermarks_watermark_text_value = $options['optimize_images_watermarks_watermark_text_value'];
		$optimize_images_watermarks_watermark_text_font_name = $options['optimize_images_watermarks_watermark_text_font_name'];
		$optimize_images_watermarks_watermark_text_size = $options['optimize_images_watermarks_watermark_text_size'];
		$optimize_images_watermarks_watermark_text_color = $options['optimize_images_watermarks_watermark_text_color'];
		$optimize_images_watermarks_watermark_text_margin_x = $options['optimize_images_watermarks_watermark_text_margin_x'];
		$optimize_images_watermarks_watermark_text_margin_y = $options['optimize_images_watermarks_watermark_text_margin_y'];
		$optimize_images_watermarks_watermark_text_opacity_value = $options['optimize_images_watermarks_watermark_text_opacity_value'];
		
		$optimize_images_watermarks_watermark_text_background_enable = $options['optimize_images_watermarks_watermark_text_background_enable'] === 'on' ? 'checked':'';
		$optimize_images_watermarks_watermark_text_background_color = $options['optimize_images_watermarks_watermark_text_background_color'];
		$optimize_images_watermarks_watermark_text_background_opacity_value = $options['optimize_images_watermarks_watermark_text_background_opacity_value'];
		
		$optimize_images_watermarks_watermark_text_outline_enable = $options['optimize_images_watermarks_watermark_text_outline_enable'] === 'on' ? 'checked':'';
		$optimize_images_watermarks_watermark_text_outline_color = $options['optimize_images_watermarks_watermark_text_outline_color'];
		$optimize_images_watermarks_watermark_text_outline_width = $options['optimize_images_watermarks_watermark_text_outline_width'];
		
		$optimize_images_watermarks_watermark_image_url = $options['optimize_images_watermarks_watermark_image_url'];
		$optimize_images_watermarks_watermark_image_width = $options['optimize_images_watermarks_watermark_image_width'];
		$optimize_images_watermarks_watermark_image_margin_x = $options['optimize_images_watermarks_watermark_image_margin_x'];
		$optimize_images_watermarks_watermark_image_margin_y = $options['optimize_images_watermarks_watermark_image_margin_y'];
		
		$optimize_images_image_quality_value = $options['optimize_images_image_quality_value'];
		$optimize_images_image_quality_value = abs((int)$optimize_images_image_quality_value);
		if(($optimize_images_image_quality_value >= 10) && ($optimize_images_image_quality_value <= 100)) {
		} else {
			$optimize_images_image_quality_value = 100;
		}
		
		
		$optimize_images_rename_img_filename_value = $options['optimize_images_rename_img_filename_value'];
		
		$optimize_images_maximum_files_handled_each_request = $options['optimize_images_maximum_files_handled_each_request'];
		$optimize_images_maximum_files_handled_each_request = abs((int)$optimize_images_maximum_files_handled_each_request);
		
		$optimize_images_handle_again_files_different_configuration_enable = $options['optimize_images_handle_again_files_different_configuration_enable'] === 'on' ? 'checked':'';
		
		$optimize_images_remove_files_available_different_configuration_enable = $options['optimize_images_remove_files_available_different_configuration_enable'] === 'on' ? 'checked':'';
		
		
		
		
		
		
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG );
		
		
		
		
		$watermark_positions = array(
			'x' => array('left', 'center', 'right'),
			'y' => array('top', 'middle', 'bottom')
		);
		
		$optimize_images_watermarks_watermark_position = (array)$optimize_images_watermarks_watermark_position;
		$optimize_images_watermarks_watermark_position = implode(';',$optimize_images_watermarks_watermark_position);
		$optimize_images_watermarks_watermark_position = trim($optimize_images_watermarks_watermark_position);
		if($optimize_images_watermarks_watermark_position) {
			$optimize_images_watermarks_watermark_position = ';'.$optimize_images_watermarks_watermark_position.';'; 
		}
		
		
		
		$watermark_positions_table = '
		<table id="optimize_images_watermarks_watermark_position" border="0" align="" cellpadding="0" cellspacing="0" >';
		
		foreach($watermark_positions['y'] as $y) {
			$watermark_positions_table .= '
			<tr>';
			foreach($watermark_positions['x'] as $x) {
				$watermark_position = $y . '_' . $x;
				$watermark_positions_table .= '
				<td title="'.strtoupper($y . ' - ' . $x).'">
					<input name="optimize_images_watermarks_watermark_position[]" type="checkbox" value="'.$watermark_position.'"'.((false !== stripos($optimize_images_watermarks_watermark_position,$watermark_position)) ? ' checked ' : '').' />
				</td>';
			}
			$watermark_positions_table .= '
			</tr>';
		}
		
		$watermark_positions_table .= '
		</table>';
		
		
		
		
		$watermark_text_fonts_html = '
		<select name="optimize_images_watermarks_watermark_text_font_name">';
		
		$watermark_fonts = array(
			'arial' => 'Arial'
			,'arial_black' => 'Arial Black'
			,'verdana' => 'Verdana'
			,'times_new_roman' => 'Times New Roman'
			,'trebuchet_ms' => 'Trebuchet MS'
			,'tahoma' => 'Tahoma'
			,'impact' => 'Impact'
			,'georgia' => 'Georgia'
			,'courier_new' => 'Courier New'
			,'comic_sans_ms' => 'Comic Sans MS'
		);
		
		
		foreach($watermark_fonts as $key1 => $value1) {
			$watermark_text_fonts_html .= '
			<option value="'.$key1.'" '.(($optimize_images_watermarks_watermark_text_font_name === $key1) ? ' selected ' : '').' >'.$value1.'</option>';
		}
		
		$watermark_text_fonts_html .= '
		</select>';
		
		
		
		
		
		$optimize_images_watermarks_watermark_type = (array)$optimize_images_watermarks_watermark_type;
		$optimize_images_watermarks_watermark_type = implode(';',$optimize_images_watermarks_watermark_type);
		$optimize_images_watermarks_watermark_type = trim($optimize_images_watermarks_watermark_type);
		if($optimize_images_watermarks_watermark_type) {
			$optimize_images_watermarks_watermark_type = ';'.$optimize_images_watermarks_watermark_type.';';
		}
		
		$watermark_type_html = '
		<ul>
			<li>
				<input type="checkbox" name="optimize_images_watermarks_watermark_type[]" value="text" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_watermarks_watermark_text_type_container"  '.( (false !== stripos($optimize_images_watermarks_watermark_type,'text')) ? ' checked ' : '').' /> &nbsp; 
				<label for="optimize_images_watermarks_watermark_type"> Text</label> 
			</li>
			<li>
				<input type="checkbox" name="optimize_images_watermarks_watermark_type[]" value="image" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_watermarks_watermark_image_type_container" '.( (false !== stripos($optimize_images_watermarks_watermark_type,'image')) ? ' checked ' : '').' /> &nbsp;  
				<label for="optimize_images_watermarks_watermark_type"> Image ('.__('Your Logo',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).')</label> 
			</li>
		</ul>';
		
		
		
		
		echo '
<script language="javascript" type="text/javascript">
	var wpOptimizeByxTraffic_Request_Nonce = "',$nonce,'";
</script>
		';
		
		
		
		
		
		
		
		$rsTemp = $this->optimize_images_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		echo '

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	
	<h2>WP Optimize By xTraffic (Optimize Images)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		',$this->base_get_sponsorsblock('vertical_01'),'
		<div id="mainblock" style="width:710px">

			<div class="dbx-content">
			
				<form name="WPOptimizeByxTraffic" action="',$action_url,'" method="post">
					  <input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
						
						<input type="hidden" name="submitted" value="1" /> 
						<input type="hidden" name="optimize_images_submitted" value="1" /> 
						
						<!--
						<h2>Overview "Optimize Images"</h2>
						
						<p>"Optimize Images" automatically adds alt and title attributes to all images in all posts specified by parameters below.</p>
						-->
						
						
						<div class="xtraffic_tabs_nav">
							<a href="#xtraffic_tabs_content1" class="">Optimize ALT/TITLE</a> 
							<a href="#xtraffic_tabs_content2" class="active">Optimize Image File</a>
						</div>
						
						<div id="xtraffic_tabs_content1" class="xtraffic_tabs_contents">

							<h3>Optimize ALT/TITLE</h3>
							
							<p>
								',__('You can enter any text in the field including these special tags',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
							</p>
							
							<ul>
								<li>%title : ',__('post title',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
								<li>%category : ',__('post category',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
								<li>%tags : ',__('post tags',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
								<li>%img_name : ',__('image file name (without extension)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
								<li>%img_title : ',__('image title attributes',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
								<li>%img_alt : ',__('image alt attributes',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
							</ul>
							
							<p></p>
							
							<ul>
								<li>
									<label for="optimize_images_alttext"> ',__('ALT attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': %img_name %title) </label>&nbsp;<br />
									<input type="text" name="optimize_images_alttext"  value="',$optimize_images_alttext,'" title="',__('ALT attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" />
								</li>
								
								<li>
									<label for="optimize_images_titletext"> ',__('TITLE attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': %img_name photo) </label>&nbsp;<br />
									<input type="text" name="optimize_images_titletext" value="',$optimize_images_titletext,'" title="',__('TITLE attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" />
								</li>
								
								<li> &nbsp; </li>
								
								<li>
									<input type="checkbox" name="optimize_images_override_alt"  ',$optimize_images_override_alt,' /> &nbsp; 
									<label for="optimize_images_override_alt"> ',__('Override default Wordpress image alt tag',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Recommended',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),') </label> 
								</li>
								
								<li>
									
									<input type="checkbox" name="optimize_images_override_title" ',$optimize_images_override_title,' /> &nbsp; 
									<label for="optimize_images_override_title"> ',__('Override default Wordpress image title',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' </label> 
								</li>
								
								
							</ul>
							
							<h4>',__('Example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ALT/TITLE</h4>
							
							<p>',__('In a post titled',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"Landscape Pictures"</b> ',__('there is a image named',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"forest.jpg"</b></p>

							<p>',__('Setting alt attribute to',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"%img_name %title"</b> ',__('will set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>alt="forest Landscape Pictures"</b></p>
							<p>',__('Setting title attribute to',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"%img_name image"</b> ',__('will set',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>title="forest image"</b></p>
							
							<br />
							
						</div>
						
						<div id="xtraffic_tabs_content2" class="xtraffic_tabs_contents">
							
							<h4>Enable/Disable "Optimize Image File"</h4>
							
							<ul>
								
								<li>
									<input type="checkbox" name="optimize_images_optimize_image_file_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_optimize_file_container" ',$optimize_images_optimize_image_file_enable,' /> &nbsp; 
									<label for="optimize_images_optimize_image_file_enable"> ',__('Enable "Optimize Image File"',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label> 
								</li>
								
							</ul>
							
							<br /><hr /><br />
							
							<div id="optimize_images_optimize_file_container">
							
								<ul>
								
									<li>
										
										<h3>',__('Images Lazy Load',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h3>
										<ul>
											<li>
												<input type="checkbox" name="optimize_images_images_lazy_load_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_images_lazy_load_frontpage_container" ',$optimize_images_images_lazy_load_enable,' /> &nbsp; 
												<label for="optimize_images_images_lazy_load_enable"> ',__('Enable "Images Lazy Load"',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label> 
												
												<div id="optimize_images_images_lazy_load_frontpage_container">
							
													<ul>
													
														<li>
															<input type="checkbox" name="optimize_images_images_lazy_load_frontpage_enable" ',$optimize_images_images_lazy_load_frontpage_enable,' /> &nbsp; 
															<label for="optimize_images_images_lazy_load_frontpage_enable"> ',__('Enable "Images Lazy Load" in Front Page',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label> 
															<p style="margin-bottom: 3%;" class="description">',__('Front Page include : Home page, category page, tag page, author page, date page, archives page,...',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
														</li>
													</ul>
											</li>
											
										</ul>
										
									</li>
									
									<li>
										
										<h3>',__('Images Limit Size (Width & Height)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h3>
										<ul>
											<li>
												<label for="optimize_images_file_minimum_width_height">',__('Miminum Images\'s size (width & height) will be processed',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :  </label> &nbsp;
												<input type="text" name="optimize_images_file_minimum_width_height" class="" value="',$optimize_images_file_minimum_width_height,'" style="width: 60px;" />&nbsp;px (pixel)
												
											</li>
											<li>
												<label for="optimize_images_file_maximum_width_height"> ',__('Maximum Images\'s size (width & height) will be processed',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </label> &nbsp; 
												<input type="text" name="optimize_images_file_maximum_width_height" class="" value="',$optimize_images_file_maximum_width_height,'" style="width: 60px;" />&nbsp;px (pixel)
												
											</li>
										</ul>
										
									</li>
									
									
									
									
									
								</ul>
								
								<h3>WATERMARK</h3>
								
								<ul>
									
									<li>
										<input type="checkbox" name="optimize_images_watermarks_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_watermarks_container" ',$optimize_images_watermarks_enable,' /> &nbsp; 
										<label for="optimize_images_watermarks_enable"> ',__('Enable Watermark Images',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label> 
									</li>
									
								</ul>
								
								<div style="margin-top: 4%;" id="optimize_images_watermarks_container" class="wpoptimizebyxtraffic_show_hide_container">
									<div>
										<h5>
											',__('Watermark Position',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
										</h5>
										<div>
											',$watermark_positions_table,'
										</div>
									</div>							
									<br />
									
									
									<!--
									<div>
										<h5>Watermark Opacity :</h5>
										<div>
											<ul>
												<li>
													<input type="text" class="" 
														
														data-slider="true"
														data-slider-step="1"
														data-slider-range="0,100"
														
														id="optimize_images_watermarks_watermark_opacity_value"
														name="optimize_images_watermarks_watermark_opacity_value" 
														value="',$optimize_images_watermarks_watermark_opacity_value,'"
														title="" style="" 
													/>
												</li>
											</ul>
										</div>
									</div>
									<br />
									-->
									
									
									
									<div>
										<h5>',__('Watermark Type',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h5>
										<div>
											',$watermark_type_html,'
										</div>
									</div>
									
									<br /> 
									
									
									<div id="optimize_images_watermarks_watermark_text_type_container" class="wpoptimizebyxtraffic_show_hide_container">
										<br /><hr /><br />
										<h5>',__('Type Text Watermark',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5><br />
										
										<div>
											<h6>
												',__('Watermark text value',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
											</h6>
											<div>
												<input type="text" name="optimize_images_watermarks_watermark_text_value"  value="',$optimize_images_watermarks_watermark_text_value,'" />
											</div>
										</div>							
										<br />
										
										<div>
											<h6>
												Fonts :
											</h6>
											<div>
												',$watermark_text_fonts_html,'
											</div>
										</div>							
										<br />
										
										<div>
											<h6>
												',__('Text size',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
											</h6>
											<div>
												<input type="text" name="optimize_images_watermarks_watermark_text_size"  value="',$optimize_images_watermarks_watermark_text_size,'" style="width:50px" /> (pt/%)
												<p class="description">',__('In case you set Text size by percent (Ex: 20%), plugin will create watermark having its width is about 20% of Image\'s width',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
												<br />
											</div>
										</div>
										<br />
										
										<div>
											<h6>
												',__('Text color',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
											</h6>
											<div>
												# <input type="text" class="wpoptimizebyxtraffic_color_picker" name="optimize_images_watermarks_watermark_text_color" value="',$optimize_images_watermarks_watermark_text_color,'" /> 
											</div>
										</div>
										<br />
										
										
										<div>
											<h6>',__('Text Margin',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h6>
											<div>
												<ul>
													<li>
														<label for="optimize_images_watermarks_watermark_text_margin_x"> x : </label>&nbsp;<input type="text" name="optimize_images_watermarks_watermark_text_margin_x"  value="',$optimize_images_watermarks_watermark_text_margin_x,'" title="" style="width: 35px;" />&nbsp;px
													</li>
													<li>
														<label for="optimize_images_watermarks_watermark_text_margin_y"> y : </label>&nbsp;<input type="text" name="optimize_images_watermarks_watermark_text_margin_y"  value="',$optimize_images_watermarks_watermark_text_margin_y,'" title="" style="width: 35px;" />&nbsp;px
													</li>
												</ul>
											</div>
										</div>
										<br />
										
										
										<div>
											<h6>',__('Text Opacity',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h6>
											<div>
												<ul>
													<li>
														<input type="text" 
															data-slider="true"
															data-slider-step="1"
															data-slider-range="0,100"
															id=""
															name="optimize_images_watermarks_watermark_text_opacity_value" 
															value="',$optimize_images_watermarks_watermark_text_opacity_value,'" title="" style="" 
														/>
													</li>
												</ul>
											</div>
										</div>
										<br />
										
										
										
										<div>
											<h6>',__('Text background',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :</h6>
											<div>
												<ul>
													<li>
														<input type="checkbox" name="optimize_images_watermarks_watermark_text_background_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_watermarks_watermark_text_background_container"  ',$optimize_images_watermarks_watermark_text_background_enable,' /> &nbsp; 
														<label for="optimize_images_watermarks_watermark_text_background_enable"> ',__('Enable Watermark Text Background',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</label> 
													</li>
												</ul>
											</div>
										</div>
										<br /> 
										
										
										<div id="optimize_images_watermarks_watermark_text_background_container" class="wpoptimizebyxtraffic_show_hide_container" >
										
											<div>
												<h6>
													',__('Background color',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
												</h6>
												<div>
													# <input type="text" class="wpoptimizebyxtraffic_color_picker" name="optimize_images_watermarks_watermark_text_background_color" value="',$optimize_images_watermarks_watermark_text_background_color,'" /> 
												</div>
											</div>
											<br />
											
												
											<!--
											<div>
												<h6>Background Opacity :</h6>
												<div>
													<ul>
														<li>
															<input type="text" 
																data-slider="true"
																data-slider-step="1"
																data-slider-range="0,100"
																id=""
																name="optimize_images_watermarks_watermark_text_background_opacity_value" 
																value="',$optimize_images_watermarks_watermark_text_background_opacity_value,'" title="" style="" 
															/>
															
														</li>
													</ul>
												</div>
											</div>
											<br /> 
											-->
										
										
										</div>
										
										
										
										
										
										<!--
										<div>
											<h6>Text outline :</h6>
											<div>
												<ul>
													<li>
														<input type="checkbox" name="optimize_images_watermarks_watermark_text_outline_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_watermarks_watermark_text_outline_container" ',$optimize_images_watermarks_watermark_text_outline_enable,' /> &nbsp; 
														<label for="optimize_images_watermarks_watermark_text_outline_enable"> Enable Watermark Text Outline</label> 
													</li>
												</ul>
											</div>
										</div>
										<br /> 
										
										
										<div id="optimize_images_watermarks_watermark_text_outline_container" class="wpoptimizebyxtraffic_show_hide_container" >
											<div>
												<h6>
													Outline color :
												</h6>
												<div>
													# <input type="text" class="wpoptimizebyxtraffic_color_picker" name="optimize_images_watermarks_watermark_text_outline_color" value="',$optimize_images_watermarks_watermark_text_outline_color,'" /> 
												</div>
											</div>
											<br />
											
												
												
											<div>
												<h6>
													Outline width :
												</h6>
												<div>
													<input type="text" name="optimize_images_watermarks_watermark_text_outline_width"  value="',$optimize_images_watermarks_watermark_text_outline_width,'" style="width:50px" /> (px)
												</div>
											</div>
											<br />
											
											
											
											
										
										
										</div>
										-->
										
										
										
										
									</div><!-- optimize_images_watermarks_watermark_text_type_container -->
									
									
									<div id="optimize_images_watermarks_watermark_image_type_container" class="wpoptimizebyxtraffic_show_hide_container">
										<br /><hr /><br />
										<h5>',__('Type Image Watermark (Your Logo)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5><br />
										
										<div>
											<h6>
												',__('Watermark image url (your logo url)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
											</h6>
											<div>
												<input type="text" name="optimize_images_watermarks_watermark_image_url"  value="',$optimize_images_watermarks_watermark_image_url,'" />
											</div>
										</div>
										<br />
										
										
										<div>
											<h6>
												',__('Watermark image size (your logo)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
											</h6>
											
											<div>
												<ul>
													<li>
														<label for="optimize_images_watermarks_watermark_image_width"> ',__('Width',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </label>&nbsp;<input type="text" name="optimize_images_watermarks_watermark_image_width"  value="',$optimize_images_watermarks_watermark_image_width,'" title="" style="width: 55px;" />&nbsp;(px/%)
														<p class="description">',__('In the case of you set "Watermark image size" is number percent (Ex: 20%), plugin will resize watermark (your logo) has width is 20% of Image\'s width',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
													</li>
												</ul>
											</div>
											
										</div>
										<br />
										
										
										<div>
											<h6>Margin :</h6> 
											<div>
												<ul>
													<li>
														<label for="optimize_images_watermarks_watermark_image_margin_x"> x : </label>&nbsp;<input type="text" name="optimize_images_watermarks_watermark_image_margin_x"  value="',$optimize_images_watermarks_watermark_image_margin_x,'" title="" style="width: 35px;" />&nbsp;px
													</li>
													<li>
														<label for="optimize_images_watermarks_watermark_image_margin_y"> y : </label>&nbsp;<input type="text" name="optimize_images_watermarks_watermark_image_margin_y"  value="',$optimize_images_watermarks_watermark_image_margin_y,'" title="" style="width: 35px;" />&nbsp;px
													</li>
												</ul>
											</div>
										</div>
										<br /> 
										
										
									</div><!-- optimize_images_watermarks_watermark_image_type_container -->
									
									
									
								</div><!-- optimize_images_watermarks_container -->
								
								<div class="wpoptimizebyxtraffic_fixed wpoptimizebyxtraffic_bottom_right" id="wpoptimizebyxtraffic_preview_processed_image">
									<div>
										<h6>',__('Preview Processed Image',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' ( <a href="#show_hide">',__('Show/Hide',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</a> )</h6> 
										<div class="wpoptimizebyxtraffic_preview_processed_image_content">
											<ul>
												<li>
													<label for="optimize_images_watermarks_preview_processed_image_example_image_url"> ',__('Example image url',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </label><br />
													<input type="text" 
														name="optimize_images_watermarks_preview_processed_image_example_image_url"  
														value="'.WPOPTIMIZEBYXTRAFFIC_PLUGIN_URL.'images/demo.jpg" 
														title="" style="" 
													/>
												</li>
												
												<li class="wpoptimizebyxtraffic_preview_process_image_img">
													
												</li>
												
												<li class="wpoptimizebyxtraffic_preview_process_image_buttons">
													<button type="button" class="button-primary wpoptimizebyxtraffic_do_preview">',__('Preview',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</button>
												</li>
											</ul>
										</div>
									</div>
									
								</div>
								
								
								<hr />
								<h3>',__('Optimize Image Quality',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
								
								<ul>
									
									<li>
										
										<input type="text" 
											data-slider="true"
											data-slider-step="1"
											data-slider-range="10,100"
											id=""
											name="optimize_images_image_quality_value" 
											value="',$optimize_images_image_quality_value,'" title="',__('Image Quality Value (10 to 100)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" style="" 
										/>
									</li>
								</ul>
								<p class="description">',__('To reduce the size of image file, you can reduce value in image\'s Quality Bar above. If you set a value of 100, your image keeps the original quality and file size. ( Best value recommended is from 80 to 90 )',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
								<br />
								
								<hr />
								<div>
									<h3>',__('Rename Image Filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
									
									<p>
										',__('You can enter any text in the field including these special tags',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' :
									</p>
									
									<ul>
										<li>%title : ',__('post title',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
										<li>%category : ',__('post category',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
										<li>%tags : ',__('post tags',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
										<li>%img_name : ',__('image file name (without extension)',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
										<li>%img_title : ',__('image title attributes',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
										<li>%img_alt : ',__('image alt attributes',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</li>
									</ul>
									
									<p></p>
									
									<ul>
										<li>
											<label for="optimize_images_rename_img_filename_value"><b> ',__('New Image Filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),': %img_name %title) </b></label>&nbsp;<br />
											<input type="text" name="optimize_images_rename_img_filename_value"  value="',$optimize_images_rename_img_filename_value,'" title="',__('ALT attribute',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" />
											<p class="description">',__('Leave a blank if you want to keep image\'s original filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
										</li>
										
									</ul>
									
									<h4>',__('Example',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : </h4> 
									
									<p>',__('In a post titled',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"Landscape Pictures"</b> ',__('there is a image named',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"forest.jpg"</b></p>

									<p>',__('Setting New Image Filename to',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"%title %img_name"</b> ',__('will set new image\'s filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : <b>"Landscape-Pictures-forest.jpg"</b></p>
									<p>',__('Setting New Image Filename to',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' <b>"xTraffic %img_name %title"</b> ',__('will set new image\'s filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' : <b>"xTraffic-forest-Landscape-Pictures.jpg"</b></p>
									
									<br />
									
								</div>
								
								
								<hr />
								<h3>',__('Performance',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h3>
								
								<ul>
									
									<li>
										<h5 style="margin-bottom: 10px;margin-top: 20px;">',__('The maximum number of files are handled for each request',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h5>
										<input type="text" name="optimize_images_maximum_files_handled_each_request"  value="',$optimize_images_maximum_files_handled_each_request,'" />
										<p class="description">',__('In the case of your hosting is in low performance, you should set the maximum number of files handled for each query to avoid overloading your hosting. In case you leave a blank or set a value of  0, plugin will handle all files that are not been handled yet',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</p>
									</li>
									
									<li>
										<br />
										<h6 style="margin-bottom: 10px;margin-top: 20px;"><input type="checkbox" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_images_handle_again_files_different_configuration_enable_container" name="optimize_images_handle_again_files_different_configuration_enable" ',$optimize_images_handle_again_files_different_configuration_enable,' /> &nbsp; ',__('Enable to reprocess files which have different configuration with its current configuration',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
										<p class="description">',__('In case you change configuration, plugin will check and reprocess all processed files that are different with the current configuration, by overwriting old file if file has the same filename or create a new file if the filename is different',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' (',__('Set at',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),' "',__('Rename Image Filename',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'")</p>
										<div id="optimize_images_handle_again_files_different_configuration_enable_container" class="wpoptimizebyxtraffic_show_hide_container">
											<h6 style="margin-bottom: 10px;margin-top: 20px;"><input type="checkbox" name="optimize_images_remove_files_available_different_configuration_enable" ',$optimize_images_remove_files_available_different_configuration_enable,' /> &nbsp; ',__('Enable to remove old files (if available) that are different with the current configuration',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'</h6>
										</div>
									</li>
								</ul>
								
								<br />
								
							</div>
							
							
						</div><!-- /xtraffic_tabs_contents -->
						
							
						<div class="submit"><input type="submit" name="Submit" value="',__('Update Options',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" class="button-primary" /></div>
						
				</form>
			</div>

			<br/><br/>
			
		</div>

	</div>
	
</div>

';
		
		
	}
	
	

	
	


}//class WPOptimizeByxTraffic_OptimizeImages 

endif; //if ( !class_exists('WPOptimizeByxTraffic_OptimizeImages') )


