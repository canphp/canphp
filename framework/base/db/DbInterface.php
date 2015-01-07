<?php
namespace framework\base\db;

interface DbInterface{
	
	public function __construct($config);

	public function query($sql, array $params);
	
	//return affected rows
	public function execute($sql, array $params);
	
	//return array
	public function select($table, array $condition, $field, $order, $limit);
	
	//return insert_id
	public function insert($table, array $data);
	
	//return affected rows
	public function update($table, array $condition, array $data);
	
	//return affected rows
	public function delete($table, array $condition);

	public function count($table, array $condition);	
	

	public function getFields($table);
	
	//get last sql
	public function getSql();
	
	public function beginTransaction();
	
	public function commit();
	
	public function rollBack();
}