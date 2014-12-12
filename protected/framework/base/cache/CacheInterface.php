<?php
namespace framework\base\cache;

Interface CacheInterface {

    public function get($key);
	
    public function set($key, $value, $expire = 1800);
	
	public function inc($key, $value = 1);
	
	public function des($key, $value = 1);
	
	public function del($key);
	
    public function clear();
		
}