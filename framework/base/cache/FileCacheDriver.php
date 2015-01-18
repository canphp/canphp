<?php
namespace framework\base\cache;

class FileCacheDriver implements CacheInterface{
	protected $config = array();

    public function __construct( $config = array() ) {
		$this->config = array(
								'CACHE_PATH' => 'data/cache/',
								'GROUP' => 'tmp',
								'HASH_DEEP' => 0,								
							);
		$this->config = array_merge($this->config, (array)$config);
    }

    public function get( $key ){
		$content = @file_get_contents( $this->_getFilePath($key) );
		if( empty($content) ) return false;
		
		$expire  =  (int) substr($content, 13, 12);
		if( time() >= $expire ) return false;

		$md5Sign  =  substr($content, 25, 32);
		$content   =  substr($content, 57);
		if( $md5Sign != md5($content) ) return false;
		
		return @unserialize($content);
    }
	

    public function set($key, $value, $expire = 1800){		
        $value = serialize($value);
		$md5Sign = md5($value);
		$expire = time() + $expire;		
        $content    = '<?php exit;?>' . sprintf('%012d', $expire) . $md5Sign . $value;		
       
	   return @file_put_contents($this->_getFilePath($key, true), $content, LOCK_EX);
    }
	
	public function inc($key, $value = 1){
		 return $this->set($key, intval($this->get($key)) + intval($value), -1);
    }
	
	public function des($key, $value = 1){
		 return $this->set($key, intval($this->get($key)) - intval($value), -1);
    }
	
	public function del($key) {
		return @unlink( $this->_getFilePath($key) );
	}
	
    public function clear( $dir='' ) {
        if( empty($dir) ) {
			$dir = $this->config['CACHE_PATH'] . '/' . $this->config['GROUP'] . '/';
			$dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
		}
		if ( !is_dir($dir) ) return false;

		$handle = opendir($dir);
		while ( ($file = readdir($handle)) !== false ){
			if ( '.' != $file && '..' != $file ){
				is_dir("$dir/$file")? $this->clear("$dir/$file") : @unlink("$dir/$file");
			}
		}
		if ( readdir($handle) == false ){
			closedir($handle);
			@rmdir($dir);
		}
    }
	
	private function _getFilePath($key, $isCreatePath = false){
		$key = md5($key);
		
		$dir = $this->config['CACHE_PATH'] . '/' . $this->config['GROUP'] . '/';
		for($i=0; $i<$this->config['HASH_DEEP']; $i++){
			$dir = $dir. substr($key, $i*2, 2).'/';
		}
		$dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
		
		if ( !file_exists($dir) ) {
			if ( !@mkdir($dir, 0777, true) ){
				throw new \Exception("Can not create dir '{$dir}'", 500);
			 }             
		}
		if ( !is_writable($dir) ) @chmod($dir, 0777);
		
		return $dir. $key . '.php';;
	}
}