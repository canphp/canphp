<?php
namespace app\base\hook;
class AppHook{
	public $startTime = 0;
	public function appBegin(){
		$this->startTime = microtime(true);
	}
	
	public function appEnd(){
		//echo microtime(true) - $this->startTime ;
	}

	public function appError($e){
		if( 404 == $e->getCode() ){
			$action = 'error404';
		}else{
			$action = 'error';
		}
		obj('app\base\controller\ErrorController')->$action($e);
	}	
	
	public function routeParseUrl($rewriteRule, $rewriteOn){
		
	}

	public function actionBefore($obj, $action){
		
	}

	public function actionAfter($obj, $action){
		
	}
}