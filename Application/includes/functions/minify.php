<?php 

use WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\JSMin
	, WPOptimizeByxTraffic\Application\Service\CSSmin
	, WPOptimizeByxTraffic\Application\Service\CSSFixer
	, WPOptimizeByxTraffic\Application\Service\Minify_HTML
	, WpPepVN\Utils
;

function pepvn_MinifyJavascript($input_data) 
{
	$input_data = (array)$input_data;
	$input_data = implode(PHP_EOL,$input_data);
	$input_data = (string)$input_data;
	$input_data = trim($input_data);
	
	$keyCache1 = Utils::hashKey(array(
		'pepvn_MinifyJavascript'
		,$input_data
	));
	
	$tmp = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);

	if(null !== $tmp) {
		return $tmp;
	}
	
	$rsOne = PepVN_Data::escapeByPattern($input_data,array(
		'pattern' => '#[\+\-]+[ \t\s]+[\+\-]+#is'
		,'target_patterns' => array(
			0
		)
		,'wrap_target_patterns' => '+'
	));
	$input_data = $rsOne['content'];
	unset($rsOne['content']);
	
	
	/*
	$pepVN_JavaScriptPacker = null;$pepVN_JavaScriptPacker = new PepVN_JavaScriptPacker($input_data, 'Normal', true, false);
	$input_data = $pepVN_JavaScriptPacker->pack();
	$pepVN_JavaScriptPacker=0;unset($pepVN_JavaScriptPacker);
	*/
	
	$input_data = JSMin::minify($input_data);
	
	if(!PepVN_Data::isEmptyArray($rsOne['patterns'])) {
		$input_data = str_replace(array_values($rsOne['patterns']),array_keys($rsOne['patterns']),$input_data);
	}
	unset($rsOne);
	
	$input_data = trim($input_data);
	
	PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $input_data);
	
	return $input_data;
	
}

function pepvn_MinifyCss($input_data)
{
	$input_data = (array)$input_data;
	$input_data = implode(PHP_EOL,$input_data);
	$input_data = (string)$input_data;
	$input_data = trim($input_data);
	
	$keyCache1 = Utils::hashKey(array(
		'pepvn_MinifyCss_Ref'
		,$input_data
	));
	
	$tmp = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);

	if(null !== $tmp) {
		return $tmp;
	}
	
	$cssMin = new CSSmin();
	$input_data = $cssMin->run($input_data,FALSE);
	$input_data = trim($input_data);
	unset($cssMin);
	
	PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $input_data);
	
	return $input_data;
}

function pepvn_MinifyHtml($input_data)
{
	$input_data = (array)$input_data;
	$input_data = implode(PHP_EOL,$input_data);
	$input_data = (string)$input_data;
	$input_data = trim($input_data);
	
	$keyCache1 = Utils::hashKey(array(
		'pepvn_MinifyHtml'
		,$input_data
	));
	
	$tmp = PepVN_Data::$cachePermanentObject->get_cache($keyCache1);

	if(null !== $tmp) {
		return $tmp;
	}
	
	$findAndReplace1 = array();
	
	$rsOne = PepVN_Data::escapeHtmlTagsAndContents($input_data,'pre;code;textarea;input');
	$input_data = $rsOne['content'];
	unset($rsOne['content']);
	
	if(!empty($rsOne['patterns'])) {
		$findAndReplace1 = array_merge($findAndReplace1, $rsOne['patterns']);
	}
	
	unset($rsOne);
	
	$input_data = Minify_HTML::minify($input_data, array(
		'jsCleanComments' => true
		,'cssMinifier' => 'pepvn_MinifyCss'
		,'jsMinifier' => 'pepvn_MinifyJavascript'
	));
	
	$findAndReplace2 = array();
	
	preg_match_all('#<(script|style)[^><]*?>.*?</\1>#is',$input_data,$matched1);
	
    if(!empty($matched1[0])) {
		$matched1 = $matched1[0];
        foreach($matched1 as $key1 => $value1) {
			unset($matched1[$key1]);
			
            $findAndReplace2[$value1] = '__'.hash('crc32b',md5($value1)).'__';
        }
    }
	$matched1 = 0;
	
	if(!empty($findAndReplace2)) {
		
		$input_data = str_replace(array_keys($findAndReplace2),array_values($findAndReplace2),$input_data); 
		
		$findAndReplace1 = array_merge($findAndReplace1,$findAndReplace2);
		
		unset($findAndReplace2);
	}
	
	$patterns1 = array(
		'#>[\s \t]+<#is' => '><'
		,'#[\s \t]+#is' => ' '
	);
	
	$input_data = preg_replace(array_keys($patterns1),array_values($patterns1), $input_data);
	
	if(!empty($findAndReplace1)) {
		$input_data = str_replace(array_values($findAndReplace1),array_keys($findAndReplace1),$input_data);
		unset($findAndReplace1);
	}
	
	$input_data = trim($input_data);
	
	PepVN_Data::$cachePermanentObject->set_cache($keyCache1, $input_data);
	
	return $input_data;
	
}

