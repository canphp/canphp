<?php

/**
 * 缓存类
 */

namespace framework\base;

class Cache {

	/**
	 * 缓存配置
	 * @var array
	 */
	protected $config = array();

	/**
	 * 配置名
	 * @var string
	 */
	protected $cache = 'default';

	/**
	 * 代理对象
	 * @var null
	 */
	public $proxyObj = null;

	/**
	 * 代理缓存时间
	 * @var integer
	 */
	public $proxyExpire = 1800;

	/**
	 * 缓存对象
	 * @var array
	 */
	protected static $objArr = array();
	
	/**
	 * 构建函数
	 * @param string $cache 缓存配置名
	 */
    public function __construct( $cache = 'default' ) {
		if( $cache ){
			$this->cache = $cache;
		}
		$this->config = Config::get('CACHE.' . $this->cache);
		if( empty($this->config) || !isset($this->config['CACHE_TYPE']) ) {
			throw new \Exception($this->cache.' cache config error', 500);
		}
    }

    /**
     * 回调驱动
     * @param  string $method 回调方法
     * @param  array  $args   回调参数
     * @return object
     */
	public function __call($method, $args) {
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