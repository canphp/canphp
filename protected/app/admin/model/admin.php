<?php
namespace app\admin\model;
class admin extends \app\base\model\BaseModel{
	protected $table = 'admin';
	
	public function getUserInfo( $username ){
		//return $this->find( array('username'=>$username) );
		$user_array = array(
							array(
								'username'=>'admin',
								'password'=>'123456',
							),
							array(
								'username'=>'canphp',
								'password'=>'asdfgh',
							),
						);
						
		foreach($user_array as $userInfo){
			if( $username == $userInfo['username']){
				return $userInfo;
			}
		}						
		return false;
	}

}