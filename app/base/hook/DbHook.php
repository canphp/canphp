<?php

/**
 * 数据库钩子
 */

namespace app\base\hook;

class DbHook {

	/**
	 * 查询开始
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return void
	 */
	public function dbQueryBegin($sql, $params) {

	}
	
	/**
	 * 查询结束
	 * @param  string $sql    SQL语句
	 * @param  array  $data   返回数据
	 * @return void
	 */
	public function dbQueryEnd($sql, $data) {

	}

	/**
	 * 执行开始
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return void
	 */
	public function dbExecuteBegin($sql, $params) {

	}	
	
	/**
	 * 执行结束
	 * @param  string   $sql            SQL语句
	 * @param  boolean  $affectedRows   执行结果
	 * @return void
	 */
	public function dbExecuteEnd($sql, $affectedRows) {
		
	}

	/**
	 * 错误处理
	 * @param  string $sql SQL语句
	 * @param  string $err 错误信息
	 * @return void
	 */
	public function dbException($sql, $err){

	}
}