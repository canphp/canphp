<?php
namespace app\main\model;
class Demo extends \app\base\model\BaseModel{
	protected $table = 'test';
	public function getTitle(){
		//$ret = $this->getFields();
		//echo $this->data( array('name'=>time()) )->insert(  );
		//echo $this->data(array('name'=>'16') )->where( array('id'=>16) )->update();
		//echo $this->data(array('name'=>'16') )->count();
		//echo $this->getSql();
		//print_r($ret);

		return '默认首页';
	}
	
	public function getHello(){
		return 'Hello, 欢迎使用CPAPP';
	}

}