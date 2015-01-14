<?php
namespace framework\base\db;
class MysqlDriver implements DbInterface{
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
		return $this->query("SELECT {$field} FROM {$table} {$condition['_where']} {$order} {$limit}", $condition['_bindParams']);		
	}
	
	public function query($sql, array $params = array()){
		$this->_bindParams( $sql, $params, $this->_getReadLink());
		$query = mysql_query( $this->getSql(), $this->_getReadLink() );
		if($query){
			$data = array();
			while($row = mysql_fetch_array($query, MYSQL_ASSOC)){
				$data[] = $row;
			}
			return $data;
		}
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. mysql_error(), 500);
	}
	
	public function execute($sql, array $params = array()){
		$this->_bindParams( $sql, $params, $this->_getWriteLink());
		$query = mysql_query( $this->getSql(), $this->_getWriteLink() );
		if($query){
			return mysql_affected_rows( $this->_getWriteLink() );
		}
		throw new \Exception('Database SQL: "' . $this->getSql(). '". ErrorInfo: '. mysql_error(), 500);
	}
	
	public function insert($table, array $data){
		$values = array();
		foreach($data as $k=>$v){
			$keys[] = "`{$k}`"; 
			$values[":{$k}"] = $v; 
			$marks[] = ":{$k}";
		}
		$table = $this->_table($table);
		$this->execute("INSERT INTO {$table} (".implode(', ', $keys).") VALUES (".implode(', ', $marks).")", $values);
		return mysql_insert_id( $this->_getWriteLink() );
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
		foreach($this->sqlMeta['params'] as $k=>$v ){
			$sql = str_replace($k, "'" . mysql_real_escape_string($v, $this->sqlMeta['link']) . "'", $sql);
		}
		return $sql;
	}
	
	public function beginTransaction(){
		return $this->execute('SET AUTOCOMMIT=0');
	}
	
	public function commit(){
		return $this->execute('COMMIT');
	}
	
	public function rollBack(){
		return $this->execute('ROLLBACK');
	}
	
	private function _bindParams($sql, array $params, $link=null){
		$this->sqlMeta = array('sql'=>$sql, 'params'=>$params, 'link'=>$link);
	}

	private function _table($table){
		return (false===strpos($table, ' '))? "`{$table}`": $table;
	}
	
	private function _where( array $condition ){
		$result = array( '_where' => '', '_bindParams' => array() );	 		
		$sql = null;
		$sqlArr = array();
		$params = array();
		foreach( $condition as $k => $v ){
			//处理join与混合多条件
			if(!is_numeric($k)){
				if(strpos($k, '.')){
					$sqlArr[] = "{$k} = :{$k}";
				}else{
					$sqlArr[] = "`{$k}` = :{$k}";
				}
				$params[":{$k}"] = $v;
			}else{
				$sqlArr[] = $v;
			}
		}
		if(!$sql) $sql = implode(' AND ', $sqlArr);

		if($sql) $result['_where'] = " WHERE ". $sql;
		
		$result['_bindParams'] = $params;		
		return $result;
	}
					
	private  function _connect( $isMaster = true ) {
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
		
		$link =null;
		foreach($dbArr as $db) {
			if( $link = @mysql_connect($db['DB_HOST'] . ':' . $db['DB_PORT'], $db['DB_USER'], $db['DB_PWD']) ){
				break;
			}
		}
		
		if(!$link){
			throw new \Exception('connect database error :'.mysql_error(), 500);
		}

		$version = mysql_get_server_info($link);
		if($version > '4.1') {
			mysql_query("SET character_set_connection = " . $db['DB_CHARSET'] . ", character_set_results = " . $db['DB_CHARSET'] . ", character_set_client = binary", $link);		
				
			if($version > '5.0.1') {
				mysql_query("SET sql_mode = ''", $link);
			}
		}		
        mysql_select_db($db['DB_NAME'], $link);
        return $link;
	}

    private function _getReadLink() {
		if( !isset( $this->readLink ) ) {
			try{
				$this->readLink = $this->_connect( false );
			}catch(Exception $e){
				$this->readLink = $this->_getWriteLink();
			}			
		}
		return $this->readLink;
    }
	
    private function _getWriteLink() {
        if( !isset( $this->writeLink ) ) {
            $this->writeLink = $this->_connect( true );
        }
		return $this->writeLink;
    }
	
	public function __destruct() {
		if($this->_writeLink) {
			@mysql_close($this->_writeLink);
		}
		if($this->_readLink) {
			@mysql_close($this->_readLink);
		}
	} 
}