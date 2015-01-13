<?php
namespace framework\base;
class App{
	
	static protected function init(){
		Config::init( BASE_PATH );
		Config::loadConfig( CONFIG_PATH . 'global.php' );
		Config::loadConfig( CONFIG_PATH . Config::get('ENV') . '.php' );
		
		date_default_timezone_set( Config::get('TIMEZONE') );
		
		//error display
		if ( Config::get('DEBUG') ) {
			ini_set("display_errors", 1);
			error_reporting( E_ALL ^ E_NOTICE );
		} else {
			ini_set("display_errors", 0);
			error_reporting(0);
		}	
	}
	
	static public function run(){
		try{			
			self::init();
			
			Hook::init(BASE_PATH);
			Hook::listen('appBegin');

			Hook::listen('routeParseUrl', array( Config::get('REWRITE_RULE'), Config::get('REWRITE_ON')));
			
			//default route
			if( !defined('APP_NAME') || !defined('CONTROLLER_NAME') || !defined('ACTION_NAME')){
				Route::parseUrl( Config::get('REWRITE_RULE'), Config::get('REWRITE_ON') );
			}
			
			//execute action
			$controller = '\app\\'. APP_NAME .'\controller\\'. CONTROLLER_NAME .'Controller';
			$action = ACTION_NAME;

			if( !class_exists($controller) ) {
				throw new \Exception("Controller '{$controller}' not found", 404);
			}
			$obj = new $controller();
			if( !method_exists($obj, $action) ){
				throw new \Exception("Action '{$controller}::{$action}()' not found", 404);
			}
			
			Hook::listen('actionBefore', array($obj, $action));
			$obj ->$action();
			Hook::listen('actionAfter', array($obj, $action));
			
		} catch( \Exception $e ){
			Hook::listen('appError', array($e));
		}
		
		Hook::listen('appEnd');
	}
}