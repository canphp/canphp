<?php
namespace framework\base;
class Controller{
	public $layout = NULL; //layout view
	
	public function assign($name, $value=NULL){
		return $this->_getView()->assign( $name, $value);
	}
	
	public function display($tpl = '', $return = false, $isTpl = true ){
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
	
	public function isPost(){
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
	
	public function redirect( $url, $code=302) {
		header('location:' . $url, true, $code);
		exit;
	}
	
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
	
	protected function _getView(){
		static $view;		
		if( !isset($view) ){
			$view = new Template( Config::get('TPL') );
		}		
		return $view;
	}
}