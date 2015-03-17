<?php

/**
 * 图像处理类
 */

namespace framework\ext;

class Image {

    /**
     * 图像文件
     * @var string
     */
    protected $img;

    /**
     * 图像驱动
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
     * @param string $img 图片路径
     * @param string $driver 图片驱动
     */
    public function __construct($img, $driver = 'gd') {
        $this->img = $img;
        $this->driver = $driver;
    }

    /**
     * 回调驱动
     * @param  string $method 回调方法
     * @param  array  $args   回调参数
     * @return object
     */
    public function __call($method, $args){
        if( !isset(self::$objArr[$this->image]) ){        
            $imageDriver = __NAMESPACE__.'\image\\' . ucfirst( $this->driver ).'Driver';
            if( !class_exists($imageDriver) ) {
                throw new \Exception("Image Driver '{$imageDriver}' not found'", 500);
            }
            self::$objArr[$this->image] = new $imageDriver( $this->img );
        }
        return call_user_func_array(array(self::$objArr[$this->image], $method), $args);      
    }

}