<?php

/**
 * 框架钩子类
 */

namespace framework\base;

class Hook {

	/**
	 * 钩子列表
	 * @var array
	 */
	static public $tags = array();
	
	/**
	 * 初始化钩子
	 * @param  string $basePath 钩子目录
	 * @return boolean
	 */
	static public function init($basePath='') {
		$dir = str_replace('/', DIRECTORY_SEPARATOR, $basePath.'app/base/hook/');
		foreach(glob($dir . '*.php') as $file){
			$pos = strrpos($file, DIRECTORY_SEPARATOR);
			if( false === $pos ) continue;
			
			$class = substr($file, $pos + 1, -4);		
			$class = "\\app\\base\\hook\\{$class}";
			
			$methods = get_class_methods($class);
			foreach((array)$methods as $method){
				self::$tags[$method][] = $class;
			}	
		}
	}

	/**
	 * 执行钩子
	 * @param  string $tag     钩子名
	 * @param  array  $params  执行参数
	 * @param  mixed  $result  钩子返回
	 * @return boolean
	 */
	static public function listen($tag, $params=array(), &$result=null) {
		if( !isset(self::$tags[$tag]) ) return false;
		foreach(self::$tags[$tag] as $class){
			$result = self::exec($class, $tag, $params);
			if(false === $result) {
				break;
			}						
		}
		return true;
	}
	
	/**
	 * 执行类
	 * @param  string $class  类名
	 * @param  string $method 方法名
	 * @param  array  $params 参数
	 * @return object
	 */
	static protected function exec($class, $method, $params) {
		static $objArr = array();
		if( !isset($objArr[$class]) ){
			$objArr[$class]= new $class();
		}
		return call_user_func_array(array($objArr[$class], $method), (array)$params);
	}
}