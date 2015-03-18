<?php

/**
 * 框架启动钩子
 */

namespace app\base\hook;

class AppHook {

	/**
	 * 开始时间
	 * @var integer
	 */
	public $startTime = 0;

	/**
	 * 框架启动
	 * @return void
	 */
	public function appBegin() {
		$this->startTime = microtime(true);
	}

	/**
	 * 框架结束
	 * @return void
	 */
	public function appEnd() {
		//echo microtime(true) - $this->startTime ;
	}

	/**
	 * 框架错误
	 * @param  objcet $e 错误对象
	 * @return viod
	 */
	public function appError($e) {
		if(404 == $e->getCode()) {
			$action = 'error404';
		}else{
			$action = 'error';
		}
		obj('base/Error','controller')->$action($e);
	}

	/**
	 * 路由解析
	 * @param  array   $rewriteRule 路由规则
	 * @param  boolean $rewriteOn   路由开关
	 * @return void
	 */
	public function routeParseUrl($rewriteRule, $rewriteOn) {
		
	}

	/**
	 * 方法开始
	 * @param  objcet $obj    操作对象
	 * @param  string $action 方法名
	 * @return void
	 */
	public function actionBefore($obj, $action) {
		
	}

	/**
	 * 方法结束
	 * @param  objcet $obj    操作对象
	 * @param  string $action 方法名
	 * @return void
	 */
	public function actionAfter($obj, $action) {
		
	}
}