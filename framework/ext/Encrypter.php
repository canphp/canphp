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
	public function encrypt($str) {
		$size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
        $str = $this->pkcs5Pad($str, $size);
        $data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        return base64_encode($data);
	}

	/**
	 * 解密
	 * @param  string $value 待解密字符串
	 * @return string
	 */
	public function decrypt($value) {
		$str = base64_decode($value);
		$str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv );
		return $this->pkcs5Unpad($str);
	}


    /**
     * 处理加密码
     */
    protected function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
  
    /**
     * 处理解密码
     */
    protected function pkcs5Unpad($text)
    {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }

	/**
	 * 生成UUID
	 * @param  string $value 待解密字符串
	 * @return string
	 */
	public function getId() {
        if (function_exists('uuid_create') && !function_exists('uuid_make')) {
            $id = uuid_create(UUID_TYPE_DEFAULT);
        } elseif (function_exists('com_create_guid')) {
            $id = strtolower(trim(com_create_guid(), '{}'));
        } else {
            $id = $this->createId();
        }
        return $id;
    }

    /**
     * 创建UUID
     * @return string
     */
    protected function createId() {
        $salt = substr(hash('sha256', microtime(true) . mt_rand()), 0, 64);
        $hex  = substr(hash('sha256', $salt), 0, 32);
        $time_low = substr($hex, 0, 8);
        $time_mid = substr($hex, 8, 4);
        $time_hi_and_version = base_convert(substr($hex, 12, 4), 16, 10);
        $time_hi_and_version &= 0x0FFF;
        $time_hi_and_version |= (4 << 12);
        $clock_seq_hi_and_reserved = base_convert(substr($hex, 16, 4), 16, 10);
        $clock_seq_hi_and_reserved &= 0x3F;
        $clock_seq_hi_and_reserved |= 0x80;
        $clock_seq_low = substr($hex, 20, 2);
        $nodes = substr($hex, 20);
        $uuid  = sprintf('%s-%s-%04x-%02x%02x-%s',
                    $time_low, $time_mid,
                    $time_hi_and_version, $clock_seq_hi_and_reserved,
                    $clock_seq_low, $nodes
                );

        return $uuid;
    }

    /**
     * 验证是否符合uuid规范
     */
    public function isId($uuid) {
        return preg_match("/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/", $uuid);
    }

}
