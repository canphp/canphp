<?php	
if( !defined('ROOT_PATH') ) define('ROOT_PATH', realpath('./').DIRECTORY_SEPARATOR);
if( !defined('BASE_PATH') ) define('BASE_PATH', realpath('./').DIRECTORY_SEPARATOR);
if( !defined('CONFIG_PATH') ) define('CONFIG_PATH', BASE_PATH.'data/config/');
if( !defined('ROOT_URL') ) define('ROOT_URL',  rtrim(dirname($_SERVER["SCRIPT_NAME"]), '\\/').'/');
if( !defined('PUBLIC_URL') ) define('PUBLIC_URL', ROOT_URL . 'public/');

use framework\base\Config;
use framework\base\Route;
use framework\base\App;

function config($key=NULL, $value=NULL){
	if( func_num_args() <= 1 ){
		return Config::get($key);
	}else{
		return Config::set($key, $value);
	}
}

function url($route=null, $params=array()){
	return Route::url($route, $params);
}

function model($model, $app='', $forceInstance=false){
	return obj($model, $app, '', '', $forceInstance);
}

function obj($class, $app='', $args=array(), $file='', $forceInstance=false){
	static $objArr = array();
	if( empty($app) ) $app = APP_NAME;

	if( isset($objArr[$class]) && false==$forceInstance ) return $objArr[$class];
	if( !empty($file) ) require_once($file);
		
	$nsArr = array(
		"", //global
		"\\app\\{$app}\\model",
		"\\app\\{$app}\\lib",
		"\\framework\\ext",
		"\\framework\\base",
	);
	
	foreach($nsArr as $ns){
		$nsClass = $ns.'\\'.$class;
		
		if( class_exists($nsClass) ){
			if(empty($args)){
				$objArr[$class]=new $nsClass();
			}else{
				$objArr[$class]=call_user_func_array(array(new \ReflectionClass($nsClass), 'newInstance'), $args);
			}		
		} 
	}
	if( !isset($objArr[$class]) ) throw new \Exception("Class '{$class}' not found'", 500);
	
	return $objArr[$class];
}


spl_autoload_register(function($class){
	static $fileList = array();
	$prefixes =array(
		'framework' => realpath(__DIR__.'/../').DIRECTORY_SEPARATOR,
		'app' => BASE_PATH,
		'*'=>BASE_PATH,
	);

	$class = ltrim($class, '\\');
	if (false !== ($pos = strrpos($class, '\\')) ){
		$namespace = substr($class, 0, $pos);
		$className = substr($class, $pos + 1);
		
		foreach ($prefixes as $prefix => $baseDir){
			if ( '*'!==$prefix && 0!==strpos($namespace, $prefix) ) continue;
			
			//file path case-insensitive
			$fileDIR = $baseDir.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
			if( !isset($fileList[$fileDIR]) ){
				$fileList[$fileDIR] = array();
				foreach(glob($fileDIR.'*.php') as $file){
					$fileList[$fileDIR][] = $file;
				}
			}
			
			$fileBase = $baseDir.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.$className;
			foreach($fileList[$fileDIR] as $file){
				if( false!==stripos($file, $fileBase) ){
					require $file;
					return true;				
				}
			}							
		}           
	}
	return false;
});

App::run();