<?php

/**
 * 无限分类
 */

namespace framework\ext;

class Category {	

	/**
	 * 原始数据
	 * @var array
	 */
	private $rawList=array();
	
	/**
	 * 格式化数据
	 * @var array
	 */
	private $formatList=array();

	/**
	 * 分类样式
	 * @var array
	 */
	private $icon = array('│','├','└');

	/**
	 * 映射字段
	 * @var array
	 */
	private $field=array();

	/**
	 * 构建函数
	 * @param array $field 字段映射
	 */
	public function __construct($field=array()) {	
		$this->field['id']=isset($field['0'])?$field['0']:'id';
		$this->field['pid']=isset($field['1'])?$field['1']:'pid';
		$this->field['title']=isset($field['2'])?$field['2']:'title';
		$this->field['fulltitle']=isset($field['3'])?$field['3']:'fulltitle';
	}

	/**
	 * 获取同级分类
	 * @param  integer $pid  上级分类
	 * @param  array  $data  分类数组
	 * @return array
	 */
	public function getChild($pid,$data=array()) {
			$childs=array();
			if(empty($data))
			{
				$data=$this->rawList;
			}
			foreach($data as $Category)
			{
				if($Category[$this->field['pid']]==$pid)
					$childs[]=$Category;
			}
			return $childs;
	}
	
	/**
	 * 获取树形分类
	 * @param  array   $data 分类数组
	 * @param  integer $id   起始上级分类
	 * @return array
	 */
	public function getTree($data,$id=0) {
		//数据为空，则返回
		if(empty($data))
			return false;
			
		$this->rawList=array();
		$this->formatList=array();
		$this->rawList=$data;
		$this->_searchList($id);
		return $this->formatList;
	}

	/**
	 * 获取分类路径
	 * @param  array  $data 分类数组
	 * @param  integer $id  当前分类
	 * @return array
	 */
	public function getPath($data,$id) {

		$this->rawList=$data;
		while(1){
			$id=$this->_getPid($id);
			if($id==0){
				break;
			}
		}
		return array_reverse($this->formatList);
	}

	/**
	 * 递归分类
	 * @param  integer $id    上级分类ID
	 * @param  string  $space 空格
	 * @return void
	 */
	private function _searchList($id=0, $space="") {
		//下级分类的数组
		$childs=$this->getChild($id);
		//如果没下级分类，结束递归
		if(!($n=count($childs)))
			return;
		$cnt=1;
		//循环所有的下级分类
		for($i=0;$i<$n;$i++)
		{
				$pre="";
				$pad="";
				if($n==$cnt)
				{
					$pre=$this->icon[2];
				}
				else
				{
					$pre=$this->icon[1];
					$pad=$space?$this->icon[0]:"";
				}
				$childs[$i][$this->field['fulltitle']]=($space?$space.$pre:"").$childs[$i][$this->field['title']];
				$this->formatList[]=$childs[$i];
				//递归下一级分类
				$this->_searchList($childs[$i][$this->field['id']],$space.$pad."&nbsp;&nbsp;");
				$cnt++;
		}
	}
	
	/**
	 * 获取PID
	 * @param  integer $id 当前ID
	 * @return integer
	 */
	private function _getPid($id) {
		foreach($this->rawList as $key=>$value) {
			if($this->rawList[$key][$this->field['id']]==$id)
			{
				$this->formatList[]=$this->rawList[$key];
				return $this->rawList[$key][$this->field['pid']];
			}
		}
		return 0;
	
	}

}