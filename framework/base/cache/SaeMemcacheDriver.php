<?php
namespace framework\base\cache;

class SaeMemcacheDriver implements CacheInterface{
	protected $mmc = NULL;
    protected $group = ''; 
    protected $ver = 0;
	
    public function __construct( $config = array() ) {
		$this->mmc = memcache_init();
		$this->group = $config['GROUP'];
		$this->ver = intval( memcache_get($this->mmc, $this->group.'_ver') ); 
    }

    public function get($key) {
		$expire = memcache_get($this->mmc, $this->group.'_'.$this->ver.'_time_'.$key);
		if(intval($expire) > time() ) {
			 return memcache_get($this->mmc, $this->group.'_'.$this->ver.'_'.$key);
		} else {
			return false;
		}
    }
	
    public function set($key, $value, $expire = 1800) {
		$expire = ($expire == -1)? time()+365*24*3600 : time() + $expire;
		memcache_set($this->mmc, $this->group.'_'.$this->ver.'_time_'.$key, $expire);
		return memcache_set($this->mmc, $this->group.'_'.$this->ver.'_'.$key, $value);
    }
	
	public function inc($key, $value = 1) {
		return $this->set($key, intval($this->get($key)) + intval($value), -1);
    }
	
	public function des($key, $value = 1) {
		return $this->set($key, intval($this->get($key)) - intval($value), -1);
    }
	
	public function del($key) {
		return $this->set($key, '', 0);
	}
	
    public function clear() {
		return  memcache_set($this->mmc, $this->group.'_ver', $this->ver+1); 
    }
	
}