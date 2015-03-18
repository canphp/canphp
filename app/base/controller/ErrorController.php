<?php

/**
 * 错误处理
 */

namespace app\base\controller;

class ErrorController extends BaseController {
	
	/**
	 * 404错误
	 * @param  object $e 错误对象
	 * @return void
	 */
	public function error404($e=null) {
		header('HTTP/1.1 404 Not Found'); 
		header("status: 404 Not Found");
		$this->error($e);
	}
	
	/**
	 * 错误输出
	 * @param  object $e 错误对象
	 * @return void
	 */
	public function error($e=null){
		if(false!==stripos(get_class($e), 'Exception')) {
			$this->errorMessage = $e->getMessage();
			$this->errorCode = $e->getCode();
			$this->errorFile = $e->getFile();
			$this->errorLine = $e->getLine();
			$this->trace = $e->getTrace();		
		}
				
		//关闭调试或者是线上版本，不显示详细错误
		if(false==config('DEBUG') || 'production'==config('ENV')) {
			$tpl = 'error_production';
			//记录错误日志
			
		}else{
			$tpl = 'error_development';
		}
		$this->display('app/base/view/'.$tpl);
	}	
}