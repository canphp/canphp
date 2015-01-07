<?php
namespace framework\base\db;
//db驱动类接口
interface DbInterface{
	
	//构造函数，传递配置
	public function __construct($config);

	//查询sql，返回二维数组
	public function query($sql, array $params);
	
	//执行sql，返回影响行数
	public function execute($sql, array $params);
	
	//查询 返回二维数组
	public function select($table, array $condition, $field, $order, $limit);
	
	//插入，返回插入id
	public function insert($table, array $data);
	
	//更新，返回影响行数
	public function update($table, array $condition, array $data);
	
	//删除，返回影响行数
	public function delete($table, array $condition);

	//统计，返回行数
	public function count($table, array $condition);	
	
	//获取表字段
	public function getFields($table);
	
	//获取最后执行的sql语句，用于调试
	public function getSql();
	
	//开始事务
	public function beginTransaction();
	
	//提交事务
	public function commit();
	
	//回滚事务
	public function rollBack();
}