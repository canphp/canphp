<?php
namespace framework\base;
class Template {
	protected $config =array();
	protected $vars = array();
	protected $_replace = array('str'=>array(), 'reg'=>array());
	
	public function __construct($config) {
		$this->config = $config;
		$this->assign('__Template', $this);
		$this->cache = new Cache( $this->config['TPL_CACHE'] );
	}
	
	public function assign($name, $value = '') {
		if( is_array($name) ){
			foreach($name as $k => $v){
				$this->vars[$k] = $v;
			}
		} else {
			$this->vars[$name] = $value;
		}
	}

	public function display($tpl = '', $return = false, $isTpl = true ) {
		if( $return ){
			if ( ob_get_level() ){
				ob_end_flush();
				flush(); 
			} 
			ob_start();
		}
		
		extract($this->vars, EXTR_OVERWRITE);
		eval('?>' . $this->compile( $tpl, $isTpl));
		
		if( $return ){
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}	
	
	public function addTags($tags = array(), $reg = false) {
		$flag = $reg ? 'reg' : 'str';
		$this->_replace[$flag] = array_merge($this->_replace[$flag], $tags);
	}
	
	public function compile( $tpl, $isTpl = true ) {
		if( $isTpl ){
			$tplFile = $this->config['TPL_PATH'] . $tpl . $this->config['TPL_SUFFIX'];
			if ( !file_exists($tplFile) ) {
				throw new \Exception("Template file '{$tplFile}' not found", 500);
			}
			$tplKey = md5(realpath($tplFile));				
		} else {
			$tplKey = md5($tpl);
		}

		$ret = unserialize( $this->cache->get( $tplKey ) );	
		if ( empty($ret['template']) || ($isTpl&&filemtime($tplFile)>($ret['compile_time'])) ) {
			$template = $isTpl ? file_get_contents( $tplFile ) : $tpl;
			$template = str_replace(array_keys($this->_replace['str']), array_values($this->_replace['str']), $template);
			$template = preg_replace(array_keys($this->_replace['reg']), array_values($this->_replace['reg']), $template);
			$extObj = new \app\base\util\Template();
			$template = $extObj->tpl($template);
			$ret = array('template'=>$template, 'compile_time'=>time());
			$this->cache->set( $tplKey, serialize($ret), 86400*365);
		}	
		return $ret['template'];
	}
}