<?php
namespace app\main\controller;
class DefaultController extends \app\base\controller\BaseController{
	
	public function index(){
		$this->title = model('Demo')->getTitle();
		$this->hello = model('Demo')->getHello();
		model('Demo')->join('a')->find();
		$this->display();
	}
}