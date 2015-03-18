<?php

/**
 * 默认控制器
 */

namespace app\main\controller;

class DefaultController extends \app\base\controller\BaseController {
	
	/**
	 * 首页
	 */
	public function index() {
		$this->title = obj('Demo')->getTitle();
		$this->hello = obj('Demo')->getHello();
		$this->display();
	}

	public function upload() {
		$this->display();
	}
}