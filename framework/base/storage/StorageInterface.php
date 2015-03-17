<?php

/**
 * 文件存储驱动接口
 */

namespace framework\base\storage;

Interface StorageInterface {

	/**
	 * 读取文件
	 * @param  string $name 文件名
	 * @return string
	 */
    public function read($name);
	
    /**
     * 写入文件
     * @param  string $name    文件名
     * @param  string $content 文件内容
     * @param  array  $option  写入参数
     * @return boolean
     */
    public function write($name, $content, $option);
	
	/**
	 * 追加内容
	 * @param  string $name    文件名
	 * @param  string $content 追加内容
	 * @return boolean
	 */
	public function append($name, $content);
	
	/**
	 * 删除文件
	 * @param  string $name 文件名
	 * @return boolean
	 */
	public function delete($name);

	/**
	 * 判断文件存在
	 * @param  string  $name 文件名
	 * @return boolean
	 */
	public function isExists($name);	
	
	/**
	 * 移动文件
	 * @param  string $oldName 原文件名/路径
	 * @param  string $newName 新路径名/目录
	 * @return boolean
	 */
	public function move($oldName, $newName);	
}