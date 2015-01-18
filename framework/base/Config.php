<?php
namespace framework\base;
class Config {		
	static protected $config = array();
	
	static public function init($basePath=''){
		self::$config = array(
			'ENV' => 'development',
			'DEBUG' => true,	
			'LOG_ON' => false,
			'LOG_PATH' => $basePath . 'data/log/', 
			'TIMEZONE' => 'PRC', 
			
			'REWRITE_ON' =>false,
			'REWRITE_RULE' =>array(
				//'<app>/<c>/<a>'=>'<app>/<c>/<a>',
			),
			
			'DEFAULT_APP' => 'main',
			'DEFAULT_CONTROLLER' => 'Default',
			'DEFAULT_ACTION' => 'index',
			
			'DB'=>array(
				'default'=>array(								
						'DB_TYPE' => 'MysqlPdo',
						'DB_HOST' => 'localhost',
						'DB_USER' => 'root',
						'DB_PWD' => '',
						'DB_PORT' => 3306,
						'DB_NAME' => 'cp',
						'DB_CHARSET' => 'utf8',
						'DB_PREFIX' => '',
						'DB_CACHE' => 'DB_CACHE',						
						'DB_SLAVE' => array(),
						/* 
						'DB_SLAVE' => array(
											array(
													'DB_HOST' => '127.0.0.1',
												),
											array(
													'DB_HOST' => '127.0.0.2',
												),
										),
						*/							
					),				
			),
			
			'TPL'=>array(
				'TPL_PATH' => $basePath,
				'TPL_SUFFIX' => '.html',
				'TPL_CACHE' => 'TPL_CACHE',
				'TPL_DEPR' => '_',					
			),
			
			'CACHE'=>array(
				'TPL_CACHE' => array(
					'CACHE_TYPE' => 'FileCache',
					'CACHE_PATH' => $basePath . 'data/cache/',
					'GROUP' => 'tpl',
					'HASH_DEEP' => 0,
				),
				
				'DB_CACHE' => array(
					'CACHE_TYPE' => 'FileCache',
					'CACHE_PATH' => $basePath . 'data/cache/',
					'GROUP' => 'db',
					'HASH_DEEP' => 2,
				),
			),
			
			'STORAGE'=>array(
				'default'=>array('STORAGE_TYPE'=>'File'),
			),				
		);
	}
		
	static public function loadConfig($file){
		if( !file_exists($file) ){
			throw new \Exception("Config file '{$file}' not found", 500); 
		}
		$config = require($file);
		foreach($config as $k=>$v){
			if( is_array($v) ){
				if( !isset(self::$config[$k]) ) self::$config[$k] = array();
				self::$config[$k] = array_merge((array)self::$config[$k], $config[$k]);
			}else{
				self::$config[$k] = $v;
			}
		}
	}
	
	static public function get($key=NULL){
		if( empty($key) ) return self::$config;
		$arr = explode('.', $key);
		switch( count($arr) ){
			case 1 : 
				if( isset(self::$config[ $arr[0] ])) {
					return self::$config[ $arr[0] ];
				}
				break;
			case 2 : 
				if( isset(self::$config[ $arr[0] ][ $arr[1] ])) {
					return self::$config[ $arr[0] ][ $arr[1] ];
				}
				break;
			case 3 : 
				if( isset(self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ])) {
					return self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ];
				}
				break;						
			default: break;
		}
		return NULL;
	}
	
	static public function set($key, $value){
		$arr = explode('.', $key);
		switch( count($arr) ){
			case 1 : 
				self::$config[ $arr[0] ] = $value;
				break;
			case 2 : 
				self::$config[ $arr[0] ][ $arr[1] ] = $value;
				break;
			case 3 : 
				self::$config[ $arr[0] ][ $arr[1] ][ $arr[2] ] = $value;
				break;					
			default: return false;
		}
		return true;
	}
}