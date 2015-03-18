<?php

/**
 * 消息发送类
 */

namespace framework\ext;

class Send {

    /**
     * 存储配置
     * @var array
     */
    protected $config =array(
        'driver'        =>  'Email',
        'driverConfig'  =>  array(),
    );

    /**
     * 驱动
     * @var string
     */
    protected $driver;

    /**
     * 驱动对象
     * @var array
     */
    protected static $objArr = array();

    /**
     * 构建函数
     * @param array $config 驱动配置
     */
    public function __construct($config) {
        $this->config = $config;
        if( empty($this->config) || !isset($this->config['driver']) ) {
            throw new \Exception('send config error', 500);
        }
    }

    /**
     * 回调驱动
     * @param  string $method 回调方法
     * @param  array  $args   回调参数
     * @return object
     */
    public function __call($method, $args){
        if( !isset(self::$objArr[$this->send]) ){
            $sendDriver = __NAMESPACE__.'\send\\' . ucfirst( $this->config['driver'] ).'Driver';
            if( !class_exists($sendDriver) ) {
                throw new \Exception("Send Driver '{$sendDriver}' not found'", 500);
            }
            self::$objArr[$this->send] = new $sendDriver( $this->config['driverConfig'] );
        }
        return call_user_func_array(array(self::$objArr[$this->send], $method), $args);      
    }

}