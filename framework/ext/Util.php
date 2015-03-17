<?php

/**
 * 常用工具库
 */

namespace framework\ext;

class Util {

	/**
	 * HTML代码过滤
	 * @param  string $str 字符串
	 * @return string
	 */
	public static function escapeHtml($str){
		$search = array ("'<script[^>]*?>.*?</script>'si",  // 去掉 javascript
						 "'<iframe[^>]*?>.*?</iframe>'si", // 去掉iframe
						);
		$replace = array ("",
						  "",
						);			  
		$str = preg_replace ($search, $replace, $str);
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	   return $str;
	}


	/**
	 * 获取来访IP
	 * @return string
	 */
	public static function getIp(){
	   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
		   $ip = getenv("HTTP_CLIENT_IP");
	   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
		   $ip = getenv("HTTP_X_FORWARDED_FOR");
	   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
		   $ip = getenv("REMOTE_ADDR");
	   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
		   $ip = $_SERVER['REMOTE_ADDR'];
	   else
		   $ip = "unknown";
	   return $ip;
	}

	/**
	 * 中英文字符串截取
	 * @param  string  $str     字符串
	 * @param  integer $start   起始长度
	 * @param  integer $length  截取长度
	 * @param  string  $charset 字符编码
	 * @param  boolean $suffix  截取后缀
	 * @return string
	 */
	public static function msubstr($str, $start = 0, $length, $charset="utf-8", $suffix = true){
		if($charset!='utf-8'){
            $str = mb_convert_encoding($str,'utf8',$charset);
        }
        $osLen = mb_strlen($str);
        if($osLen <= $length){
            return $str;
        }
        $string = mb_substr($str,$start,$length,'utf8');
        $sLen = mb_strlen($string,'utf8');
        $bLen = strlen($string);
        $sCharCount = (3*$sLen-$bLen)/2;
        if($osLen<=$sCharCount+$length){
            $arr = preg_split('/(?<!^)(?!$)/u',mb_substr($str,$length+1,$osLen,'utf8')); //将中英混合字符串分割成数组（UTF8下有效）
        }else {
            $arr = preg_split('/(?<!^)(?!$)/u',mb_substr($str,$length+1,$sCharCount,'utf8'));
        }
        foreach($arr as $value){
            if(ord($value)<128 && ord($value)>0){
                $sCharCount = $sCharCount-1;
            }else {
                $sCharCount = $sCharCount-2;
            }
            if($sCharCount<=0){
                break;
            }
            $string.=$value;
        }
        return $string;
		if($suffix) return $string."…";
		return $string;
	}

	/**
	 * 判断UTF-8
	 * @param  string  $string 字符串
	 * @return boolean
	 */
	public static function isUtf8($string){
		if( !empty($string) ) {
			$ret = json_encode( array('code'=>$string) );
			if( $ret=='{"code":null}') {
				return false;
			}
		}
		return true;
	}

	/**
	 * 字符串转码
	 * @param  string $fContents 字符串
	 * @param  string $from      原始编码
	 * @param  string $to        目标编码
	 * @return string
	 */
	public static function auto_charset($fContents,$from='gbk',$to='utf-8'){
		$from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
		$to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
		if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
			//如果编码相同或者非字符串标量则不转换
			return $fContents;
		}
		if(is_string($fContents) ) {
			if(function_exists('mb_convert_encoding')){
				return mb_convert_encoding ($fContents, $to, $from);
			}elseif(function_exists('iconv')){
				return iconv($from,$to,$fContents);
			}else{
				return $fContents;
			}
		}
		elseif(is_array($fContents)){
			foreach ( $fContents as $key => $val ) {
				$_key =     self::auto_charset($key,$from,$to);
				$fContents[$_key] = self::auto_charset($val,$from,$to);
				if($key != $_key )
					unset($fContents[$key]);
			}
			return $fContents;
		}
		else{
			return $fContents;
		}
	}

	/**
	 * 加密函数
	 * @param  mixed   $data   加密数据
	 * @param  string  $key    密匙
	 * @param  integer $expire 失效时间
	 * @return string
	 */
	public static function cpEncode($data, $key='', $expire = 0){
		$string=serialize($data);
		$ckey_length = 4;
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = substr(md5(microtime()), -$ckey_length);

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		
		$string =  sprintf('%010d', $expire ? $expire + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		return $keyc.str_replace('=', '', base64_encode($result));		
	}

	/**
	 * 解密函数
	 * @param  string $string 加密字符串
	 * @param  string $key    密匙
	 * @return mixed
	 */
	public static function cpDecode($string,$key=''){
		$ckey_length = 4;
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = substr($string, 0, $ckey_length);
		
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		
		$string =  base64_decode(substr($string, $ckey_length));
		$string_length = strlen($string);
		
		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return unserialize(substr($result, 26));
		}else{
			return '';
		}	
	}

	/**
	 * 删除目录所有文件
	 * @param  string $dir 路径
	 * @return boolean
	 */
	public static function delDir($dir){
		if (!is_dir($dir)){
			return false;
		}
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false){
			if ($file != "." && $file != ".."){
				is_dir("$dir/$file")? self::delDir("$dir/$file") : @unlink("$dir/$file");
			}
		}
		if (readdir($handle) == false){
			closedir($handle);
			@rmdir($dir);
		}
	}
}