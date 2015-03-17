<?php

/**
 * 数据库驱动接口
 */

namespace framework\base\db;

interface DbInterface {
	
	/**
	 * 构建函数
	 * @param array $config 数据库配置
	 */
	public function __construct($config);

	/**
	 * 执行SQL查询
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return mixed
	 */
	public function query($sql, array $params);
	
	/**
	 * 执行SQL读写
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return mixed
	 */
	public function execute($sql, array $params);
	
	/**
	 * 数据查询
	 * @param  string $table     表名
	 * @param  array  $condition 查询条件
	 * @param  string $field     查询字段
	 * @param  string $order     排序条件
	 * @param  string $limit     查询数量
	 * @return array
	 */
	public function select($table, array $condition, $field, $order, $limit);
	
	/**
	 * 插入数据
	 * @param  string $table 表名
	 * @param  string  $data  插入数据
	 * @return number
	 */
	public function insert($table, array $data);
	
	/**
	 * 更新数据
	 * @param  string $table     表名
	 * @param  array  $condition 条件
	 * @param  array  $data      更新数据
	 * @return boolean
	 */
	public function update($table, array $condition, array $data);
	
	/**
	 * 删除数据
	 * @param  string $table     表名
	 * @param  array  $condition 条件
	 * @return boolean
	 */
	public function delete($table, array $condition);

	/**
	 * 查询统计
	 * @param  string $table     表名
	 * @param  array  $condition 条件
	 * @return number            
	 */
	public function count($table, array $condition);	
	
	/**
	 * 获取表字段
	 * @param  string $table 表名
	 * @return array
	 */
	public function getFields($table);
	
	/**
	 * 获取最后执行sql
	 * @return string
	 */
	public function getSql();
	
	/**
	 * 事务开始
	 * @return boolean
	 */
	public function beginTransaction();
	
	/**
	 * 事务提交
	 * @return boolean
	 */
	public function commit();
	
	/**
	 * 事务回滚
	 * @return boolean
	 */
	public function rollBack();
}