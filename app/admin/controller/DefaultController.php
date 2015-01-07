<?php
namespace app\admin\controller;
use framework\ext\Image;
use framework\ext\Check;

class DefaultController extends \app\base\controller\AdminController{
	
	public function index(){
		//$this->leftMenu = api('*', 'getMenu');
		$this->title = config('admin_title'); 
		$this->display();
	}
	
	public function welcome(){
		$this->display();
	}
	
	//登录
	public function login(){
		if( !$this->isPost() ){
			$this->title = config('login_title');
			$this->footer = config('login_footer');
			$this->display();
		}else{
			$result = array('status'=>0, 'msg'=>'登录失败');
			
			$username = trim( $_POST['username'] );
			$password = trim( $_POST['password'] );
			$checkcode = trim( $_POST['checkcode'] );
			
			$msg = Check::rule( array(
						array( Check::must($username), '请输入用户名'),
						array( Check::must($password), '请输入密码'),
						array( Check::must($checkcode), '请输入验证码'),
						array( Check::same($checkcode, $_SESSION['verify']), '验证码不对'),
					));
					
			if( true === $msg ){
				$userInfo = model('admin')->getUserInfo( $username );
				if( !empty($userInfo) && $userInfo['password'] == $password){
					$this->setLogin( $userInfo );
					$result['status'] = 1;
					$msg = '登录成功';
				}else{
					$msg = '用户名或密码不对';
				}
			}
			
			$result['msg'] = $msg;
			echo  json_encode($result);
		}
	}
	
	//退出登录
	public function logout(){
		$this->clearLogin( url('default/login') );
	}
	
	//生成验证码
	public function verify(){
		Image::buildImageVerify();
	}
	
	//更新缓存
	public function clearCache(){
		api('*', 'clearCache');
		$this->alert('缓存更新成功');	
	}
		
}