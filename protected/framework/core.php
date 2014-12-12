<?php	
if( !defined('ROOT_PATH') ) define('ROOT_PATH', realpath('./').DIRECTORY_SEPARATOR);
if( !defined('BASE_PATH') ) define('BASE_PATH', realpath('./protected').DIRECTORY_SEPARATOR);
if( !defined('ROOT_URL') ) define('ROOT_URL',  rtrim(dirname($_SERVER["SCRIPT_NAME"]), '\\/').'/');
if( !defined('PUBLIC_URL') ) define('PUBLIC_URL', ROOT_URL . 'public/');
if( !defined('CONFIG_PATH') ) define('CONFIG_PATH', BASE_PATH.'data/config/');

use framework\base\Config;
use framework\base\Route;
use framework\base\App;

function config($key=NULL, $value=NULL){
	if( func_num_args() <= 1 ){
		return Config::get($key);
	}else{
		return Config::set($key);
	}
}

function url($route='default/index', $params=array()){
	return Route::url($route, $params);
}

function model($model, $app='', $forceInstance=false){
	static $objArr = array();
	if( empty($app) ) $app = APP_NAME;
	
	$class = "\\app\\{$app}\\model\\{$model}";
	if( isset($objArr[$class]) && false==$forceInstance ){
		return $objArr[$class];
	}
	if( !class_exists($class) ) {
		throw new \Exception("Model '{$class}' not found'", 500);
	}		
	return $objArr[$class] = new $class();
}

spl_autoload_register(function($class){
	static $fileList = array();
	$prefixes =array(
		'framework' => realpath(__DIR__.'/../').DIRECTORY_SEPARATOR,
		'app' => BASE_PATH,
	);

	$class = ltrim($class, '\\');
	if (false !== ($pos = strrpos($class, '\\')) ){
		$namespace = substr($class, 0, $pos);
		$className = substr($class, $pos + 1);
		
		foreach ($prefixes as $prefix => $baseDir){
			if (0 !== strpos($namespace, $prefix)){
				continue;
			}
			
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