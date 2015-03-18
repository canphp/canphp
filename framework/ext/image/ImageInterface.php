<?php

/**
 * 图像处理驱动接口
 */

namespace framework\ext\image;

Interface ImageInterface {

	/**
	 * 构建函数
	 * @param array $img 图片路径
	 */
	public function __construct($img);

	/**
     * 缩放图片
     * @param  integer $width  图片宽度
     * @param  integer $height 图片高度
     * @param  string $type    缩放类型
     * @return object
     */
	public function thumb($width, $height, $type = 'scale');

	/**
     * 图片水印
     * @param  string  $source 水印图片
     * @param  integer $locate 水印位置
     * @param  integer $alpha  水印透明度
     * @return object
     */
    public function water($source, $locate = 0, $alpha=80);

	/**
     * 输出图片
     * @param  string $filename 文件名
     * @param  string $type     图片类型
     * @return boolean
     */
    public function output($filename, $type = null);

    /**
     * 获取错误信息
     */
    public function getError();

    /**
     * 获取图片信息
     */
    public function getInfo();
		
}