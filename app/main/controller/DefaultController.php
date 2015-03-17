<?php
namespace app\main\controller;
class DefaultController extends \app\base\controller\BaseController{
	
	public function index(){
		$this->title = obj('Demo')->getTitle();
		$this->hello = obj('Demo')->getHello();
		$this->display();
	}
}