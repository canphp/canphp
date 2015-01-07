<?php
namespace framework\base\cache;

class MemcachedDriver implements CacheInterface{
	protected $mmc = NULL;
    protected $group = ''; 
    protected $ver = 0;
	
    public function __construct( $config = array() ) {
		$this->mmc = new Memcached;
		
		if( empty($config) ) {
			$config['MEM_SERVER'] = array(array('127.0.0.1', 11211));
			$config['MEM_GROUP'] = '';
		}
		
		foreach($config['MEM_SERVER'] as $v) {
			call_user_func_array(array($this->mmc, 'addServer'), $v);
		}
		
		if( isset($config['MEM_GROUP']) ){
			$this->group = $config['MEM_GROUP'];
		}
		$this->ver = intval( $this->mmc->get($this->group.'_ver') );
    }

    public function get($key) {
		return $this->mmc->get($this->group.'_'.$this->ver.'_'.$key);
    }
	
    public function set($key, $value, $expire = 1800) {
		return $this->mmc->set($this->group.'_'.$this->ver.'_'.$key, $value, $expire);
    }
	
	public function inc($key, $value = 1) {
		 return $this->mmc->increment($this->group.'_'.$this->ver.'_'.$key, $value);
    }
	
	public function des($key, $value = 1) {
		 return $this->mmc->decrement($this->group.'_'.$this->ver.'_'.$key, $value);
    }
	
	public function del($key) {
		return $this->mmc->delete($this->group.'_'.$this->ver.'_'.$key);
	}
	
    public function clear() {
        return  $this->mmc->set($this->group.'_ver', $this->ver+1); 
    }	
}