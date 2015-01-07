<?php
namespace framework\base;
class Storage{
	protected $config =array();
	protected $storage = 'default';
	protected static $objArr = array();
	
    public function __construct( $storage = 'default' ) {
		if( $storage ){
			$this->storage = $storage;
		}
		$this->config = Config::get('STORAGE.' . $this->storage);
		if( empty($this->config) || !isset($this->config['STORAGE_TYPE']) ) {
			throw new \Exception($this->storage.' storage config error', 500);
		}
    }

	public function __call($method, $args){
		if( !isset(self::$objArr[$this->storage]) ){		
			$storageDriver = __NAMESPACE__.'\storage\\' . ucfirst( $this->config['STORAGE_TYPE'] ).'Driver';
			if( !class_exists($storageDriver) ) {
				throw new \Exception("Storage Driver '{$storageDriver}' not found'", 500);
			}	
			self::$objArr[$this->storage] = new $storageDriver( $this->config );
		}
		return call_user_func_array(array(self::$objArr[$this->storage], $method), $args);		
	}
}