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
		$this->data = $this->filterData($data);	}

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

	/**
	 * html转换字符串
	 * @param  string $html HTML内容
	 * @return string
	 */
	public function htmlEncode($html){
		return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * 字符串转换html
	 * @param  string $html HTML内容
	 * @return string
	 */
	public function htmlDecode($html){
		return html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * 清理HTML
	 * @param  string $html HTML内容
	 * @return string
	 */
	public function filterHtml($html){
		$html = $this->htmlDecode($html);
		return strip_tags($str);
	}

	/**
	 * 过滤非HTTP协议
	 * @param  string $uri URI地址
	 * @return string
	 */
	public function filterUri($uri) {
		$uri = $this->htmlDecode($uri);
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
	 * @param  string $html HTML内容
	 * @return string
	 */
	public function filterXss($html, $allowedTags = array(), $allowedStyleProperties = array()) {
		static $xss;
		if(!isset($xss)) {
			$xss = new \framework\ext\Xss();
		}
		$html = $this->htmlDecode($html);
		return $xss->filter($html, $allowedTags, $allowedStyleProperties);
	}

	/**
	 * 获取生成令牌
	 * @param  string $key 生成密钥
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