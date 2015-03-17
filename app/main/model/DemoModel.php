<?php
namespace app\main\model;
class DemoModel extends \app\base\model\BaseModel{
	protected $table = 'test';
	
	public function getTitle(){
		return '默认首页';
	}
	
	public function getHello(){
		return 'Hello, 欢迎使用CPAPP';
	}

}