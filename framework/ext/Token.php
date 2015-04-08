<?php

/**
 * 加解密类
 */

namespace framework\ext;

class Encrypter {


	private $key;

    private $iv;

    /**
	 * 构建函数
	 * @param array $data 验证数据
	 */
	public function __construct($key) {
		$this->key = hash('MD5', $key, true);
        $this->iv = chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);
	}

	/**
	 * 加密
	 * @param  string $value 待加密字符串
	 * @return string
	 */
	public function encrypt($value) {
		$str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CBC, $this->iv);
		return base64_encode($str);
	}

	/**
	 * 解密
	 * @param  string $value 待解密字符串
	 * @return string
	 */
	public function decrypt($value) {
		$str = base64_decode($value);
		return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_CBC, $this->iv);
	}

}
