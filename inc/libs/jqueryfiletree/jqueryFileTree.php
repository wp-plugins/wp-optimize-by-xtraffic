<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//
if ( defined( 'WPOPTIMIZEBYXTRAFFIC_PLUGIN_INIT' ) ) {
	
	if(!isset($_POST['dir'])) {
		$_POST['dir'] = '';
	}
	
	$_POST['dir'] = (string)$_POST['dir'];
	$_POST['dir'] = urldecode($_POST['dir']);
	
	$root = '';
	
	$fileTree_KeyCache = md5(__FILE__ . $root . $_POST['dir']); 
	
	
	$fileTree_ResultData = PepVN_Data::$cacheObject->get_cache($fileTree_KeyCache);
	if(!$fileTree_ResultData) {
		
		$fileTree_ResultData = '';
		
		if( file_exists($root . $_POST['dir']) ) {
			$files = scandir($root . $_POST['dir']);
			natcasesort($files);
			if( count($files) > 2 ) { /* The 2 accounts for . and .. */
				$fileTree_ResultData .= '<ul class="jqueryFileTree" style="display: none;">';
				// All dirs
				foreach( $files as $file ) {
					if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
						$fileTree_ResultData .= '<li class="directory collapsed"><a href="#" rel="' . htmlentities($_POST['dir'] . $file) . '/">' . htmlentities($file) . '</a></li>';
					}
				}
				// All files
				foreach( $files as $file ) {
					if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
						$ext = preg_replace('/^.*\./', '', $file);
						$fileTree_ResultData .= '<li class="file ext_'.$ext.'"><a href="#" rel="' . htmlentities($_POST['dir'] . $file) . '">' . htmlentities($file) . '</a></li>';
					}
				}
				$fileTree_ResultData .= '</ul>';
			}
		}
		
		PepVN_Data::$cacheObject->set_cache($fileTree_KeyCache, $fileTree_ResultData);
		
		
	}
	
	echo $fileTree_ResultData;

}

?>