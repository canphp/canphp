<?php

/**
 * 公共控制器
 */

namespace framework\base;

class Controller {

	/**
	 * 公共布局
	 * @var null 模板路径
	 */
	public $layout = NULL;

	/**
	 * 模板赋值
	 * @param  string $name  变量名
	 * @param  mixed  $value 变量值
	 * @return void
	 */
	public function assign($name, $value=NULL) {
		return $this->_getView()->assign( $name, $value);
	}

	/**
	 * 模板输出
	 * @param  string  $tpl    模板名
	 * @param  boolean $return 返回模板内容
	 * @param  boolean $isTpl  是否模板文件
	 * @return mixed
	 */
	public function display($tpl = '', $return = false, $isTpl = true) {
		if( $isTpl ){
			if( empty($tpl) ){
				$tpl = 'app/'.APP_NAME . '/view/' . strtolower(CONTROLLER_NAME) . config('TPL.TPL_DEPR') . strtolower(ACTION_NAME);
			}
			if( $this->layout ){
				$this->__template_file = $tpl;
				$tpl = $this->layout;
			}
		}	
		$this->_getView()->assign( get_object_vars($this));
		return $this->_getView()->display($tpl, $return, $isTpl);
	}

	/**
	 * 判断post提交
	 * @return boolean
	 */
	public function isPost(){
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
	 * 判断get提交
	 * @return boolean
	 */
	public function isGet(){
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}

	/**
	 * 判断ajax提交
	 * @return boolean
	 */
	public function isAjax(){
		if(((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 页面跳转
	 * @param  string  $url  跳转地址
	 * @param  integer $code 跳转代码
	 * @return void
	 */
	public function redirect( $url, $code = 302) {
		header('location:' . $url, true, $code);
		exit;
	}

	/**
	 * JS窗口提示
	 * @param  string $msg     提示消息
	 * @param  string $url     跳转URL
	 * @param  string $charset 页面编码
	 * @return void
	 */
	public function alert($msg, $url = NULL, $charset='utf-8'){
		header("Content-type: text/html; charset={$charset}"); 
		$alert_msg="alert('$msg');";
		if( empty($url) ) {
			$go_url = 'history.go(-1);';
		}else{
			$go_url = "window.location.href = '{$url}'";
		}
		echo "<script>$alert_msg $go_url</script>";
		exit;
	}

	/**
	 * 请求过滤post、get
	 * 
	 * @param  string $name    参数名
	 * @param  string $default 默认值
	 * @return [type]
	 */
	public function arg($name=null, $default = null) {
		static $args;
		if( !$args ){
			$args = array_merge((array)$_GET, (array)$_POST);
		}
		if( null==$name ) return $args;
		if( !isset($args[$name]) ) return $default;
		$arg = $args[$name];
		if( is_array($arg) ){
			array_walk($arg, function(&$v, $k){$v = trim(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));} );
		}else{
			$arg = trim(htmlspecialchars($arg, ENT_QUOTES, 'UTF-8'));
		}
		return $arg;
	}

	/**
	 * 获取模板对象
	 * @return object
	 */
	protected function _getView(){
		static $view;		
		if( !isset($view) ){
			$view = new Template( Config::get('TPL') );
		}		
		return $view;
	}
}