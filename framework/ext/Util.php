<?php
namespace framework\ext;

class Util{
	//html代码输入
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


	// 获取客户端IP地址
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

	//中文字符串截取
	public static function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
		switch($charset){
			case 'utf-8':$char_len=3;break;
			case 'UTF8':$char_len=3;break;
			default:$char_len=2;
		}
		//小于指定长度，直接返回
		if(strlen($str)<=($length*$char_len)){	
			return $str;
		}
		if(function_exists("mb_substr")){   
			$slice= mb_substr($str, $start, $length, $charset);
		} else if(function_exists('iconv_substr')){
			$slice=iconv_substr($str,$start,$length,$charset);
		} else { 
		    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		if($suffix) return $slice."…";
		return $slice;
	}

	//检查是否是正确的邮箱地址，是则返回true，否则返回false
	public static function isEmail($user_email){
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false){
			if (preg_match($chars, $user_email)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	// 检查字符串是否是UTF8编码,是返回true,否则返回false
	public static function isUtf8($string){
		if( !empty($string) ) {
			$ret = json_encode( array('code'=>$string) );
			if( $ret=='{"code":null}') {
				return false;
			}
		}
		return true;
	}

	// 自动转换字符集 支持数组转换
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

	//加密函数，可用cpDecode()函数解密，$data：待加密的字符串或数组；$key：密钥；$expire 过期时间
	public static function cpEncode($data,$key='',$expire = 0){
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

	//cpEncode之后的解密函数，$string待解密的字符串，$key，密钥
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

	//遍历删除目录和目录下所有文件
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