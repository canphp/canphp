<?php
namespace app\base\controller;
class AdminController extends BaseController{
	protected $appID = 'admin';

	public function __construct(){
		if( !isset( $_SESSION )) session_start();
		$appID = config('appID');
		$this->appID = empty($appID) ? $this->appID : $appID;
		$this->checkLogin();
		parent::__construct();
	}
		
	protected function checkLogin(){
		//不需要登录验证的页面
		$noLogin = array(
						'default'=>array('login','verify'),
				);
		
		//如果当前访问是无需登录验证，则直接返回		
		if( isset($noLogin[CONTROLLER_NAME]) && in_array(ACTION_NAME, $noLogin[CONTROLLER_NAME]) ){
			return true;
		}
		
		//没有登录,则跳转到登录页面
		if( !$this->isLogin() ){
			$this->redirect( url('admin/default/login') );
		}
		return true;
	}
	
	//判断是否登录
	protected function isLogin(){
		if( empty( $_SESSION[ $this->appID . '_userInfo' ] ) ){
			return false;
		}else{
			$this->userInfo = $_SESSION[ $this->appID . '_userInfo' ];
			return true;
		}
	}
	
	//设置登录
	protected function setLogin( $userInfo ){
		$_SESSION[ $this->appID . '_userInfo' ] = $userInfo;
	}
	
	//退出登录
	protected function clearLogin( $url='' ){
		$_SESSION[ $this->appID . '_userInfo' ] = NULL;
		if( !empty($url) ){
			$this->redirect( $url );
		}
		return true;
	}
}