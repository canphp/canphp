<?php
namespace framework\base;
class Model{
	protected $config =array();
    protected $options = array('field'=>'*','where'=>array(),'order'=>'','limit'=>'','data'=>array());
	protected $database = 'default';	
	protected $table = '';
	protected static $objArr = array();
	
    public function __construct( $database = 'default' ) {
		if( $database ){
			$this->database = $database;
		}
		$this->config = Config::get('DB.' . $this->database);
		if( empty($this->config) || !isset($this->config['DB_TYPE']) ) {
			throw new \Exception($this->database.' database config error', 500);
		}
		$this->table($this->table);
    }
			
    public function query($sql, $params = array()) {
        $sql = trim($sql);
		if ( empty($sql) ) return array();
		$sql = str_replace('{pre}', $this->config['DB_PREFIX'], $sql);
		return $this->getDb()->query($sql, $params);	
    }

    public function execute($sql, $params = array()) {
        $sql = trim($sql);
		if ( empty($sql) ) return 0;
		$sql = str_replace('{pre}', $this->config['DB_PREFIX'], $sql);
		return $this->getDb()->execute($sql, $params); 
    }
	
    public function find() {
		$this->limit(1);
		$data = $this->select();
		return isset($data[0]) ? $data[0] : array();
     }	 

     public function select() {
		$condition = $this->options['where'];
		$this->options['where'] = '';
		
		$field = $this->options['field'];
		if( empty($field) ) $field  = '*'; 
		$this->options['field'] = '*';
		
		$order = $this->options['order'];
		$this->options['order'] = '';

		$limit = $this->options['limit'];
		$this->options['limit'] = '';
		
		return $this->getDb()->select($this->table, $condition, $field, $order, $limit);		
     }
	
    public function insert() {
		if( empty($this->options['data']) || !is_array($this->options['data']) ){ 
			return false;
		}		
		$data = $this->options['data'];
		$this->options['data']= array();		
		return $this->getDb()->insert($this->table, $data);
    }
	
    public function update() {
		if( empty($this->options['where']) ) {
			return false;
		}	
		if( empty($this->options['data']) || !is_array($this->options['data']) ) {
			return false;
		}
		
		$condition = $this->options['where'];
		$this->options['where']= '';
		
		$data = $this->options['data'];
		$this->options['data']= array();	

		return $this->getDb()->update($this->table, $condition, $data);
    }
	
    public function delete() {
		if( empty($this->options['where']) ) {
			return false;
		}	
		
		$condition = $this->options['where'];
		$this->options['where']= array();	

		return $this->getDb()->delete($this->table, $condition);
    }

	public function count() {
		$condition = $this->options['where'];
		$this->options['where']= '';	
		return $this->getDb()->count($this->table, $condition);
	}
	
	public function getFields() {
		return $this->getDb()->getFields($this->table);
	}
	
    public function getSql() {
        return $this->getDb()->getSql();
    }

	public function beginTransaction() {
        return $this->getDb()->beginTransaction();
    }
	
	public function commit() {
        return $this->getDb()->commit();
    }
	
	public function rollBack() {
        return $this->getDb()->rollBack();
    }

	public function table($table, $ignorePre = false) {
		$this->table = $ignorePre ? $table : $this->config['DB_PREFIX'] . $table;
		return $this;
	}

	public function field($field) {
		$this->options['field'] = $field;
		return $this;
	}

	public function data(array $data) {
		$this->options['data'] = $data;
		return $this;
	}

	public function where(array $where) {
		$this->options['where'] = $where;
		return $this;
	}	

	public function order($order) {
		$this->options['order'] = $order;
		return $this;
	}

	public function limit($limit) {
		$this->options['limit'] = $limit;
		return $this;
	}	
	
	public function cache($expire = 1800){
		$cache = new Cache($this->config['DB_CACHE']);
		$cache->proxyObj = $this;
		$cache->proxyExpire = $expire;
		return $cache;
	}
	
    public function clear() {
		$cache = new Cache($this->config['DB_CACHE']);
		return $cache->clear();
    }
	
	protected function getDb() {
		if( empty(self::$objArr[$this->database]) ){
			$dbDriver = __NAMESPACE__.'\db\\' . ucfirst( $this->config['DB_TYPE'] ).'Driver';
			self::$objArr[$this->database] = new $dbDriver( $this->config );
		}
		return self::$objArr[$this->database];
	}	
}