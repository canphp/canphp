<?php

/**
 * 公共模型类
 */

namespace framework\base;

class Model {

	/**
	 * 模型配置
	 * @var array
	 */
	protected $config =array();

	/**
	 * 操作参数
	 * @var array
	 */
	protected $options = array(
							'table' => '',
							'field' => '*',
							'where' => array(),
							'order' => '',
							'limit' => '',
							'data' => array(),
							'pager' => array(),
				);

	/**
	 * 数据库配置名
	 * @var string
	 */
	protected $database = 'default';

	/**
	 * 操作表
	 * @var string
	 */
	protected $table = '';	

	/**
	 * 当前表
	 * @var null
	 */
	protected $trueTable = null;

	/**
	 * 驱动对象
	 * @var array
	 */
	protected static $objArr = array();

	/**
	 * 分页信息
	 * @var null
	 */
	public $pager = null;

	/**
	 * 初始化
	 * @param string $database 模型配置名
	 */
	public function __construct( $database = 'default' ) {
		if( $database ){
			$this->database = $database;
		}
		$this->config = Config::get('DB.' . $this->database);
		if( empty($this->config) || !isset($this->config['DB_TYPE']) ) {
			throw new \Exception($this->database.' database config error', 500);
		}
		$this->table = (null==$this->trueTable) ? $this->config['DB_PREFIX'].$this->table : $this->trueTable;
		$this->trueTable = $this->table;
		$this->table($this->trueTable, true);
	}

	/**
	 * 执行SQL查询
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return mixed
	 */
	public function query($sql, $params = array()) {
		$sql = trim($sql);
		if ( empty($sql) ) return array();
		$sql = str_replace('{pre}', $this->config['DB_PREFIX'], $sql);
		return $this->getDb()->query($sql, $params);	
	}

	/**
	 * 执行SQL读写
	 * @param  string $sql    SQL语句
	 * @param  array  $params 过滤参数
	 * @return mixed
	 */
	public function execute($sql, $params = array()) {
		$sql = trim($sql);
		if ( empty($sql) ) return 0;
		$sql = str_replace('{pre}', $this->config['DB_PREFIX'], $sql);
		return $this->getDb()->execute($sql, $params); 
	}

	/**
	 * 查询一条数据
	 * @return array
	 */
	public function find() {
		$this->limit(1);
		$data = $this->select();
		return isset($data[0]) ? $data[0] : array();
 	}

 	/**
 	 * 查询多条数据
 	 * @return array
 	 */
	public function select() {		
		$field = $this->options['field'];
		if( empty($field) ) $field  = '*'; 
		$this->options['field'] = '*';
		
		$order = $this->options['order'];
		$this->options['order'] = '';

		$limit = $this->options['limit'];
		$this->options['limit'] = '';
		
		$table = $this->_getTable();
		$where = $this->_getWhere();
		
		//Pagination
		if( !empty($this->options['pager']) ){
			$count = $this->getDb()->Count($table, $where);
			$this->_pager($this->options['pager']['page'], $this->options['pager']['pageSize'], 
						$this->options['pager']['scope'], $count);
			$this->options['pager'] = array();
			$limit = $this->pager['offset'] . ',' . $this->pager['limit'];
		}
		
		return $this->getDb()->select($table, $where, $field, $order, $limit);		
 	}

 	/**
 	 * 插入数据
 	 * @return integer
 	 */
	public function insert() {
		if( empty($this->options['data']) || !is_array($this->options['data']) ) return false;
		
		return $this->getDb()->insert($this->_getTable(), $this->_getData());
	}

	/**
	 * 更新数据
	 * @return boolean
	 */
	public function update() {
		if( empty($this->options['where']) || !is_array($this->options['where'])  ) return false;
		if( empty($this->options['data']) || !is_array($this->options['data']) ) return false;
				
		return $this->getDb()->update($this->_getTable(), $this->_getWhere(), $this->_getData());
	}
	
	/**
	 * 删除数据
	 * @return boolean
	 */
	public function delete() {
		if( empty($this->options['where']) || !is_array($this->options['where'])  ) return false;

		return $this->getDb()->delete($this->_getTable(), $this->_getWhere());
	}

	/**
	 * 统计数据
	 * @return integer
	 */
	public function count() {
		return $this->getDb()->count($this->_getTable(), $this->_getWhere());
	}

	/**
	 * 获取表字段
	 * @return array
	 */
	public function getFields() {
		return $this->getDb()->getFields( $this->_getTable() );
	}

	/**
	 * 获取最后查询语句
	 * @return string
	 */
	public function getSql() {
		return $this->getDb()->getSql();
	}

	/**
	 * 启动事务
	 * @return boolean
	 */
	public function beginTransaction() {
		return $this->getDb()->beginTransaction();
	}

	/**
	 * 提交事务
	 * @return boolean
	 */
	public function commit() {
		return $this->getDb()->commit();
	}
	
	/**
	 * 回滚事务
	 * @return boolean
	 */
	public function rollBack() {
		return $this->getDb()->rollBack();
	}

	/**
	 * 设置表
	 * @param  string  $table     表名
	 * @param  boolean $ignorePre 独立前缀
	 * @return object
	 */
	public function table($table, $ignorePre = false) {
		$this->options['table'] = $ignorePre ? $table : $this->config['DB_PREFIX'] . $table;
		return $this;
	}

	/**
	 * 联操作表
	 * @param  string $join 联接表语句
	 * @param  string $way  联接类型
	 * @return object
	 */
	public function join($join, $way='inner') {
		$join = str_replace('{pre}', $this->config['DB_PREFIX'], $join);
		$this->options['table'] = " {$this->options['table']} {$way} join {$join} ";
		return $this;
	}

	/**
	 * 查询字段
	 * @param  string $field 查询表指定字段
	 * @return object
	 */
	public function field($field) {
		$this->options['field'] = $field;
		return $this;
	}

	/**
	 * 操作数据
	 * @param  array  $data 插入或者更新数据数组
	 * @return object
	 */
	public function data(array $data = array()) {
		$this->options['data'] = $data;
		return $this;
	}

	/**
	 * 查询条件
	 * @param  array  $where 条件数组
	 * @return object
	 */
	public function where(array $where = array()) {
		$this->options['where'] = $where;
		return $this;
	}		

	/**
	 * 排序规则
	 * @param  string $order 查询结果排序
	 * @return object
	 */
	public function order($order) {
		$this->options['order'] = $order;
		return $this;
	}

	/**
	 * 查询数量
	 * @param  integer $limit 查询结果数量
	 * @return object
	 */
	public function limit($limit) {
		$this->options['limit'] = $limit;
		return $this;
	}	

	/**
	 * 查询分页
	 * @param  integer $page     当前页数
	 * @param  integer $pageSize 每页数量
	 * @param  integer $scope    页数范围
	 * @return object
	 */
	public function pager($page, $pageSize = 10, $scope = 10) {
		$page = max(intval($page), 1);
		$this->options['pager'] = compact(array('page', 'pageSize', 'scope'));
		return $this;
	}

	/**
	 * 查询缓存
	 * @param  integer $expire 缓存时间：秒
	 * @return boolean
	 */
	public function cache($expire = 1800) {
		$cache = new Cache($this->config['DB_CACHE']);
		$cache->proxyObj = $this;
		$cache->proxyExpire = $expire;
		return $cache;
	}

	/**
	 * 清空缓存
	 * @return boolean
	 */
	public function clear() {
		$cache = new Cache($this->config['DB_CACHE']);
		return $cache->clear();
	}

	/**
	 * 获取驱动对象
	 * @return object
	 */
	protected function getDb() {
		if( empty(self::$objArr[$this->database]) ){
			$dbDriver = __NAMESPACE__.'\db\\' . ucfirst( $this->config['DB_TYPE'] ).'Driver';
			self::$objArr[$this->database] = new $dbDriver( $this->config );
		}
		return self::$objArr[$this->database];
	}

	/**
	 * 获取表名
	 * @return string
	 */
	protected function _getTable() {
		$table = $this->options['table'];
		$this->options['table'] = $this->table;
		return $table;
	}

	/**
	 * 获取条件
	 * @return array
	 */
	protected function _getWhere() {
		$where = $this->options['where'];
		$this->options['where']= array();	
		return $where;
	}

	/**
	 * 获取数据
	 * @return array
	 */
	protected function _getData() {
		$data = $this->options['data'];
		$this->options['data']= array();
		return $data;
	}

	/**
	 * 设置分页
	 * @return void
	 */
	protected function _pager($page, $pageSize = 10, $scope = 10, $total) {		
		$page = max(intval($page), 1);
		$totalPage = ceil( $total / $pageSize );
		
		$this->pager = array(		
			'page'=> $page,			
			'pageSize'   => $pageSize,
			'scope'   => $scope,
			'totalPage'  => $totalPage,
			'totalCount' => $total,
			'firstPage'  => 1,
			'prevPage'   => ( ( 1 == $page ) ? 1 : ($page - 1) ),
			'nextPage'   => ( ( $page == $totalPage ) ? $totalPage : ($page + 1)),
			'lastPage'   => $totalPage,			
			'allPages'   => array(),
			'offset'      => ($page - 1) * $pageSize,
			'limit'       => $pageSize,
		);
		
		if($totalPage <= $scope ){
			$this->pager['allPages'] = range(1, $totalPage);
		}elseif( $page <= $scope/2) {
			$this->pager['allPages'] = range(1, $scope);
		}elseif( $page <= $totalPage - $scope/2 ){
			$right = $page + (int)($scope/2);
			$this->pager['allPages'] = range($right-$scope+1, $right);
		}else{
			$this->pager['allPages'] = range($totalPage-$scope+1, $totalPage);
		}
	}
}