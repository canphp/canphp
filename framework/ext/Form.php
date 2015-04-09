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
	 * @param array $data 表单数据
	 */
	public function __construct($data = array()) {
		if(empty($data)) {
			$data = array_merge((array)$_GET, (array)$_POST);
		}
		$this->data = $this->filterData($data);	
	}

	/**
	 * 过滤数据
	 * @param  array $data 数据
	 * @return array
	 */
	protected function filterData($data) {
		if (is_array($data)){
	        foreach ($data as $k=>$v){
	            $data[$k] = $this->filterData($v);
	        }
	        return $data;
	    }else{
	    	//还原自动转义
	    	if(get_magic_quotes_gpc()) {
	    		$data = stripslashes($data);
	    	}
	    	return $this->htmlEncode($data);
	        
	    }
	}

	/**
	 * 设置表单数据
	 * @param array $data 表单数据
	 */
	public function setData($data = array()) {
		$this->data = $data;
	}

	/**
	 * 获取字段名或变量
	 * @param array $data 表单数据
	 */
	public function getData($field, $type = 0) {
		if($type){
			$data = $field;
		}else{
			$data = $this->data[$field];
		}
		return $data;
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
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isArray($field, $type = 0){
		$data = $this->getData($field, $type);
		if(is_array($data)){
			if(empty($data)){
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
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isEmpty($field, $type = 0){
		$data = $this->getData($field, $type);
		if(!empty($data)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 判断邮箱
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isEmail($field, $type = 0){
		$this->isPreg('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $field, $type);
	}

	/**
	 * 判断网址
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isUrl($field, $type = 0){
		$this->isPreg('/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/', $field, $type);
	}

	/**
	 * 判断货币
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isCurrency($field, $type = 0){
		$this->isPreg('/^\d+(\.\d+)?$/', $field, $type);
	}

	/**
	 * 判断数字
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isNumber($field, $type = 0){
		$this->isPreg('/^\d+$/', $field, $type);
	}

	/**
	 * 判断区号
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isZip($field, $type = 0){
		$this->isPreg('/^\d{6}$/', $field, $type);
	}

	/**
	 * 判断整数
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isInteger($field, $type = 0){
		$this->isPreg('/^[-\+]?\d+$/', $field, $type);
	}

	/**
	 * 判断浮点数
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isDouble($field, $type = 0){
		$this->isPreg('/^[-\+]?\d+$/', $field, $type);
	}

	/**
	 * 判断英文
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isEnglish($field, $type = 0){
		$this->isPreg('/^[A-Za-z]+$/', $field);
	}

	/**
	 * 判断长度
	 * @param  sting  $field 字段名
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isLength($field, $len, $type = 0){
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
	 * @param   sting $field 字段
	 * @param  type   $type 字段类型
	 * @return boolean
	 */
	public function isPreg($rule, $field, $type = 0){
		$data = $this->getData($field, $type);
		if(preg_match($rule, $data) === 1){
            return true;
        }else{
        	return false;
        }
	}

	/**
	 * html转换字符串
	 * @param  string $field 字段名/HTML内容
	 * @param  type   $type 字段类型
	 * @return string
	 */
	public function htmlEncode($field, $type = 0){
		$data = $this->getData($field, $type);
		return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * 字符串转换html
	 * @param  string $field 字段名/HTML内容
	 * @param  type   $type 字段类型
	 * @return string
	 */
	public function htmlDecode($field, $type = 0){
		$data = $this->getData($field, $type);
		return html_entity_decode($data, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * 清理HTML
	 * @param  string $field 字段名/HTML内容
	 * @param  type   $type 字段类型
	 * @return string
	 */
	public function filterHtml($field, $type = 0){
		$data = $this->getData($field, $type);
		$html = $this->htmlDecode($data, 1);
		return strip_tags($html);
	}

	/**
	 * 过滤非HTTP协议
	 * @param  string $field 字段名/URI地址
	 * @param  type   $type 字段类型
	 * @return string
	 */
	public function filterUri($field, $type = 0) {
		$data = $this->getData($field, $type);
		$uri = $this->htmlDecode($data, 1);
		$allowed_protocols = array('http' => true, 'https' => true);
        do {
            $before = $uri;
            $colonpos = strpos($uri, ':');
            if ($colonpos > 0) {
                $protocol = substr($uri, 0, $colonpos);
                if (preg_match('![/?#]!', $protocol)) {
                    break;
                }
                if (!isset($allowed_protocols[strtolower($protocol)])) {
                    $uri = substr($uri, $colonpos + 1);
                }
            }
        } while ($before != $uri);

        return $uri;
	}

	/**
	 * 过滤XSS
	 * @param  string $field 字段名/HTML内容
	 * @return string
	 */
	public function filterXss($field, $allowedTags = array(), $allowedStyleProperties = array(), $type = 0) {
		static $xss;
		if(!isset($xss)) {
			$xss = new \framework\ext\Xss();
		}
		$data = $this->getData($field, $type);
		$html = $this->htmlDecode($data, 1);
		return $xss->filter($html, $allowedTags, $allowedStyleProperties);
	}

	/**
	 * 获取生成令牌
	 * @param  string $key 密钥
	 * @return string
	 */
	public function tokenGet($key) {
		static $encrypter;
		if(!isset($encrypter)) {
			$encrypter = new \framework\ext\Encrypter($key);
		}
		return $encrypter->encrypt($encrypter->getId());
	}

	/**
	 * 验证令牌
	 * @param  string $str 提交令牌
	 * @param  string $key 密钥
	 * @return string
	 */
	public function tokenVerify($str, $key) {
		static $encrypter;
		if(!isset($encrypter)) {
			$encrypter = new \framework\ext\Encrypter($key);
		}
		$code = $encrypter->decrypt($str);
		if(!$encrypter->isId($uuid)){
			return false;
		}
		return $code;

	}

}