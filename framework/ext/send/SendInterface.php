<?php

/**
 * 消息发送驱动接口
 */

namespace framework\ext\send;

Interface SendInterface {

	/**
     * 构建函数
     * @param array $config 驱动配置
     */
    public function __construct($config);

	/**
     * 推送消息
     * @param  string  $to      收信人
     * @param  string  $title   标题
     * @param  string  $content 内容
     * @param  array   $data    其他数据
     * @return array
     */
    public function push($to, $title, $content, $time = '', $data = array());

    /**
     * 获取错误信息
     */
    public function getError();
		
}