<?php
namespace framework\base\db;
use framework\base\Hook;
class MysqlPdoDriver implements DbInterface {
	protected $config =array();
	protected $writeLink = NULL;
	protected $readLink = NULL;
	protected $sqlMeta = array('sql'=>'', 'params'=>array(), 'link'=>NULL);
	
	public function __construct( $config = array() ){
		$this->config = $config;
	}

	public function select($table, array $condition = array(), $field='*', $order=NULL, $limit=NULL){
		$field = !empty($field) ? $field : '*';
		$order = !empty($order) ? ' ORDER BY '.$order : '';
		$limit = !empty($limit) ? ' LIMIT '.$limit : '';
		$table = $this->_table($table);
		$condition = $this->_where($condition);
		return $this->query("SELECT {$field} FROM {$table} {$condition['_where']} $order $limit", $condition['_bindParams']);		
	}
	
	public function query($sql, array $params = array()){
		$sth = $this->_bindParams( $sql, $params, $this->_getReadLink());
		Hook::listen('dbQueryBegin', array($sql, $params));
		if( $sth->execute() ) {
			$data = $sth->fetchAll(\PDO::FETCH_ASSOC);
			Hook::listen('dbQueryEnd', array($this->getSql(), $data));
			return $data;
		}
		
		$err = $sth->errorInfo();
		Hook::listen('dbException', array($this->getSql(), $err[2]));
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. $err[2], 500);
	}
	
	public function execute($sql, array $params = array()){
		$sth = $this->_bindParams( $sql, $params, $this->_getWriteLink() );
		Hook::listen('dbExecuteBegin', array($sql, $params));
		if( $sth->execute() ) {
			$affectedRows = $sth->rowCount();
			Hook::listen('dbExecuteEnd', array($this->getSql(), $affectedRows));
			return $affectedRows;
		}
		
		$err = $sth->errorInfo();
		Hook::listen('dbException', array($this->getSql(), $err[2]));
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. $err[2], 500);
	}
	
	public function insert($table, array $data = array()){
		$table = $this->_table($table);
		$values = array();
		foreach($data as $k=>$v){
			$keys[] = "`{$k}`"; 
			$values[":{$k}"] = $v; 
			$marks[] = ":{$k}";
		}
		$status = $this->execute("INSERT INTO {$table} (".implode(', ', $keys).") VALUES (".implode(', ', $marks).")", $values);
		$id = $this->_getWriteLink()->lastInsertId();
		if($id){
			return $id;
		}else{
			return $status;
		}
	}
	
	public function update($table, array $condition = array(), array $data = array()){
		if( empty($condition) ) return false;
		$values = array();
		foreach ($data as $k=>$v){
			$keys[] = "`{$k}`=:__{$k}";
			$values[":__{$k}"] = $v;			
		}
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		return $this->execute("UPDATE {$table} SET ".implode(', ', $keys) . $condition['_where'], $condition['_bindParams'] + $values);
	}
	
	public function delete($table, array $condition = array() ){
		if( empty($condition) ) return false;
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		return $this->execute("DELETE FROM {$table} {$condition['_where']}", $condition['_bindParams']);
	}

	public function count($table, array $condition = array()) {
		$table = $this->_table($table);
		$condition = $this->_where( $condition );
		$count = $this->query("SELECT COUNT(*) AS __total FROM {$table} ".$condition['_where'], $condition['_bindParams']);
		return isset($count[0]['__total']) && $count[0]['__total'] ? $count[0]['__total'] : 0;
	}
	
	public function getFields($table) {
		$table = $this->_table($table);
		return  $this->query("SHOW FULL FIELDS FROM {$table}");
	}
	
	public function getSql(){
		$sql = $this->sqlMeta['sql'];
		$arr = $this->sqlMeta['params'];
		uksort($arr, function($a, $b){ return strlen($b)-strlen($a);} );
		foreach($arr as $k=>$v ){
			$sql = str_replace($k, $this->sqlMeta['link']->quote($v), $sql);
		}
		return $sql;
	}
	
	public function beginTransaction(){
		return $this->_getWriteLink()->beginTransaction();
	}
	
	public function commit(){
		return $this->_getWriteLink()->commit();
	}
	
	public function rollBack(){
		return $this->_getWriteLink()->rollBack();
	}
	
	protected function _bindParams($sql, array $params, $link=null){
		$this->sqlMeta = array('sql'=>$sql, 'params'=>$params, 'link'=>$link);
		$sth = $link->prepare($sql);		
		foreach($params as $k=>$v){
			$sth->bindValue($k, $v);
		}				
		return $sth;
	}

	protected function _table($table){
		return (false===strpos($table, ' '))? "`{$table}`": $table;
	}
	
	protected function _where( array $condition ){
		$result = array( '_where' => '', '_bindParams' => array() );	 		
		$sql = null; 
		$sqlArr = array();
		$params = array();		
		foreach( $condition as $k => $v ){
			if(!is_numeric($k)){
				if( false===strpos($k, ':') ){
					$k = str_replace('`', '', $k);				
					$key = ':__'.str_replace('.', '_', $k);
					$field = '`'.str_replace('.', '`.`', $k).'`';					
					$sqlArr[] = "{$field} = {$key}";
				}else{
					$key = $k;
				}
				$params[$key] = $v;
			}else{
				$sqlArr[] = $v;
			}
		}
		if(!$sql) $sql = implode(' AND ', $sqlArr);

		if($sql) $result['_where'] = " WHERE ". $sql;
		
		$result['_bindParams'] = $params;		
		return $result;
	}
	
	protected  function _connect( $isMaster = true ) {
		$dbArr = array();
		if( false==$isMaster && !empty($this->config['DB_SLAVE']) ) {	
			$master = $this->config;
			unset($master['DB_SLAVE']);
			foreach($this->config['DB_SLAVE'] as $k=>$v) {
				$dbArr[] = array_merge($master, $this->config['DB_SLAVE'][$k]);
			}
			shuffle($dbArr);
		} else {
			$dbArr[] = $this->config;
		}

		$pdo = null;
		$error = '';
		foreach($dbArr as $db) {
			$dsn = "mysql:host={$db['DB_HOST']};port={$db['DB_PORT']};dbname={$db['DB_NAME']};charset={$db['DB_CHARSET']}";
			try{
				$pdo = new \PDO($dsn, $db['DB_USER'], $db['DB_PWD']);
				break;
			}catch(PDOException $e){
				$error = $e->getMessage();
			}
		}
		
		if(!$pdo){
			throw new \Exception('connect database error :'.$error, 500);
		}
		$pdo->exec("set names {$db['DB_CHARSET']}");
		return $pdo;
	}

    protected function _getReadLink() {
		if( !isset( $this->readLink ) ) {
			try{
				$this->readLink = $this->_connect( false );
			}catch( \Exception $e){
				$this->readLink = $this->_getWriteLink();
			}			
		}
		return $this->readLink;
    }
	
    protected function _getWriteLink() {
        if( !isset( $this->writeLink ) ) {
            $this->writeLink = $this->_connect( true );
        }
		return $this->writeLink;
    }
	
	public function __destruct() {
		if($this->writeLink) {
			$this->writeLink = NULL;
		}
		if($this->readLink) {
			$this->readLink = NULL;
		}
	}	
}