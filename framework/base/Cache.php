<?php
namespace framework\base;
class Cache{
	protected $config =array();
	protected $cache = 'default';
	public $proxyObj=null;
	public $proxyExpire=1800;
	protected static $objArr = array();
	
    public function __construct( $cache = 'default' ) {
		if( $cache ){
			$this->cache = $cache;
		}
		$this->config = Config::get('CACHE.' . $this->cache);
		if( empty($this->config) || !isset($this->config['CACHE_TYPE']) ) {
			throw new \Exception($this->cache.' cache config error', 500);
		}
    }

	public function __call($method, $args){
		if( !isset(self::$objArr[$this->cache]) ){		
			$cacheDriver = __NAMESPACE__.'\cache\\' . ucfirst( $this->config['CACHE_TYPE'] ).'Driver';
			if( !class_exists($cacheDriver) ) {
				throw new \Exception("Cache Driver '{$cacheDriver}' not found'", 500);
			}	
			self::$objArr[$this->cache] = new $cacheDriver( $this->config );
		}
		
		if( $this->proxyObj ){ //proxy mode
			$key = md5( get_class($this->proxyObj) . '_'.$method.'_' . var_export($args) );
			$value = self::$objArr[$this->cache]->get($key);
			if( false===$value ){
				$value = call_user_func_array(array($this->proxyObj, $method), $args);
				self::$objArr[$this->cache]->set($key, $value, $this->proxyExpire);
			}
			return $value;
		}else{
			return call_user_func_array(array(self::$objArr[$this->cache], $method), $args);
		}		
	}
}