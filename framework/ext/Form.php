<?php

/**
 * 表单类
 */

namespace framework\ext;

class Form {

	/**
     * 表单数据
     * @var array
     */
	protected $data = array();

	/**
	 * 错误消息
	 * @var string
	 */
	protected $errorMsg = '';

	/**
	 * 构建函数
	 * @param array $data 验证数据
	 */
	public function __construct($data = array()) {
		if(empty($data)) {
			$data = array_merge((array)$_GET, (array)$_POST);
		}
		if(is_array($data)){
			array_walk_recursive($data, function(&$v, $k){$v = trim(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'));} );
		}
		$this->data = $data;
	}

	/**
	 * 获取请求值
	 * @param  string $name    键名
	 * @param  string $default 默认值
	 * @return mixed
	 */
	public function getVal($name = null, $default = null){
		if(empty($name)){
			return $this->data;
		}
		if(!isset($this->data[$name])){
			return $default;
		}
		return $this->data[$name];
	}

	/**
	 * 判断数组
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isArray($field){
		if(is_array($this->data[$field])){
			if(empty($this->data[$field])){
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}

	/**
	 * 判断不为空
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isEmpty($field){
		if(!empty($this->data[$field])){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 判断邮箱
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isEmail($field){
		$this->isPreg('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $field);
	}

	/**
	 * 判断网址
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isUrl($field){
		$this->isPreg('/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/', $field);
	}

	/**
	 * 判断货币
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isCurrency($field){
		$this->isPreg('/^\d+(\.\d+)?$/', $field);
	}

	/**
	 * 判断数字
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isNumber($field){
		$this->isPreg('/^\d+$/', $field);
	}

	/**
	 * 判断区号
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isZip($field){
		$this->isPreg('/^\d{6}$/', $field);
	}

	/**
	 * 判断整数
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isInteger($field){
		$this->isPreg('/^[-\+]?\d+$/', $field);
	}

	/**
	 * 判断整数
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isDouble($field){
		$this->isPreg('/^[-\+]?\d+$/', $field);
	}

	/**
	 * 判断英文
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isEnglish($field){
		$this->isPreg('/^[A-Za-z]+$/', $field);
	}

	/**
	 * 判断长度
	 * @param  sting  $field 字段名
	 * @return boolean
	 */
	public function isLength($field, $len){
		$length  =  mb_strlen($this->data[$field],'utf-8');
		if(strpos($rule,',')) {
            list($min,$max)   =  explode(',',$rule);
            if($length >= $min && $length <= $max){
                return true;
            }
        }else{
            if($length == $rule){
                return false;
            }
        }
	}

	/**
	 * 判断正则
	 * @param  sting  $rule 规则
	 * @return boolean
	 */
	public function isPreg($rule, $field){
		if(preg_match($rule, $this->data[$field]) === 1){
            return true;
        }else{
        	return false;
        }
	}
}