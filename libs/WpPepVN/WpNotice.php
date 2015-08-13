<?php 
namespace WpPepVN;

use WpPepVN\Utils
;

class WpNotice 
{
	
    protected static $_notices = array();
	
    protected static $_statistics = array();
	
	protected static $_tempData = array();
	
	private $_bag = '';
    
    public function __construct($bag = 0) 
    {
		if(!$bag) {
			$bag = mt_rand(1,900000000);
		}
		
		$this->_bag = $bag;
		
		self::$_notices[$bag] = array();
	}
    
    public function add_notice(
        $text   //Notice Text
        , $type //Type of notice : success, error , warning, info
        , $options = array()   //options : array
    ) {
		$text = trim($text);
		
		if($text) {
			$bag = $this->_bag;
				
			$key = Utils::hashKey(array($bag,$text,$type,$options));
			
			self::$_notices[$this->_bag][$key] = array(
				'text' => $text
				,'type' => $type
				,'options' => $options
			);
			
			if(!isset(self::$_statistics[$bag]['type'][$type])) {
				self::$_statistics[$bag]['type'][$type] = 0;
			}
			
			self::$_statistics[$bag]['type'][$type]++;
		}
		
	}
    
    public function get_all_notice() 
    {
        return self::$_notices[$this->_bag];
    }
    
	public function has_type($type) 
    {
		$bag = $this->_bag;
		return isset(self::$_statistics[$bag]['type'][$type]);
	}
	
	public function count_type($type) 
    {
		$bag = $this->_bag;
		
		if(isset(self::$_statistics[$bag]['type'][$type])) {
			return self::$_statistics[$bag]['type'][$type];
		}
		
		return 0;
	}
    
    public function render($data) 
    {
		$k = crc32($data['text']);
		
		if(isset(self::$_tempData['rendered'][$k])) {
			return '';
		}
		
		self::$_tempData['rendered'][$k] = 1;
		
		$class = '';
		
        if('success' === $data['type']) {
			$class = 'updated';
		} else if('info' === $data['type']) {
			$class = 'updated';
		} else if('warning' === $data['type']) {
			$class = 'update-nag';
		} else if('error' === $data['type']) {
			$class = 'error';
		}
		
		return '<div class="'.$class.'" style="padding: 1%;">'.$data['text'].'</div>';
    }
    
    private function _render_all() 
    {
		$result = '';
		
		$bag = $this->_bag;
		
        foreach(self::$_notices[$bag] as $key => $data) {
			unset(self::$_notices[$bag][$key]);
			$result .= $this->render($data);
			unset($key,$data);
		}
		
		return $result;
    }
    
    public function show_all() 
    {
        echo $this->_render_all();
    }
}