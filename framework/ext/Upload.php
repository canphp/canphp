<?php

/**
 * 上传类
 */

namespace framework\ext;

class Upload {

	/**
	 * 上传配置
	 * @var array
	 */
	protected $config = array(
        'maxSize'       =>  1048576, //上传的文件大小限制 默认10M
        'allowExts'     =>  array(), //允许的文件后缀
        'rootPath'      =>  './upload/', //上传根路径
        'savePath'      =>  '', //保存路径
        'saveRule'      =>  'md5_file', //命名规则
        'driver'        =>	'Local',
        'driverConfig'  =>  array(),
    );

    /**
     * 上传文件信息
     * @var array
     */
	protected $uploadFileInfo = array();

	/**
	 * 错误消息
	 * @var string
	 */
	protected $errorMsg = '';

	/**
	 * 构建函数
	 * @param array $config 上传配置
	 */
	public function __construct($config = array()) {
		$this->config = array_merge($this->config, $config);
		$this->setDriver();
	}

	/**
	 * 上传配置
	 * @param  string $key 上传字段
	 * @return boolean
	 */
	public function upload($key = '') {
		if(empty($_FILES)) {
			$this->errorMsg = '没有文件上传！';
			return false;
		}
		if(empty($key)) {
			$files = $_FILES;
		} else {
			$files[$key] = $_FILES[$key];
		}
		//上传根目录检查
		if(!$this->uploader->rootPath($this->config['rootPath'])){
            $this->errorMsg = $this->uploader->getError();
            return false;
        }
        //上传目录检查
		$savePath = $this->config['rootPath'] . $this->config['savePath'];
		if(!$this->uploader->checkPath($savePath)){
            $this->errorMsg = $this->uploader->getError();
            return false;
        }
        $num = 0;
		foreach($files as $key =>$file) {
			if( $file['error'] == 4 ) continue;
			$saveRuleFunc = $this->config['saveRule'];
			$pathinfo = pathinfo($file['name']);
			$file['key'] = $key;
			$file['extension'] = strtolower( $pathinfo['extension'] );
			$file['savepath'] = $savePath;
			$file['savename'] = $saveRuleFunc( $file['tmp_name'] ) . '.' . $file['extension'];
			$file['driver'] = $this->config['driver'];
			//检查文件类型大小和合法性
			if (!$this->check($file)) {
				return false;
			}
			//存储文件
			$info = $this->uploader->saveFile($file, $config);
			if(!$info){
				$this->errorMsg = $this->uploader->getError();
				return false;
			}
			$this->uploadFileInfo[$num] = $info;
			$this->uploadFileInfo[$key] = $info;
		}
		return true;
	}

	/**
	 * 检测文件合法性
	 * @param  string $file 文件名
	 * @return boolean
	 */
	protected function check($file) {
		//文件上传失败
		if($file['error'] !== 0) {
			$this->errorMsg= '文件上传失败！';
			return false;
		}	
		//检查文件类型
		$this->allowExts = array_map('strtolower', $this->config['allowExts']);		
		if( !in_array($file['extension'], $this->config['allowExts'])) {
			$this->errorMsg = '上传文件类型不允许！';
			return false;
		}
		//检查文件大小
		if ($file['size'] > $this->config['maxSize']) {
			$this->errorMsg = '上传文件大小超出限制！';
			return false;
		}
		//检查是否合法上传
		if(!is_uploaded_file($file['tmp_name'])) {
			$this->errorMsg = '非法上传文件！';
			return false;
		}
		// 如果是图像文件 检测文件格式
		if( in_array($file['extension'], array('gif','jpg','jpeg','bmp','png','swf')) && false === getimagesize($file['tmp_name']) ) {
			$this->errorMsg = '非法图像文件！';
			return false;
		}
		//检查通过，返回true
		return true;
	}

	/**
	 * 设置驱动
	 */
	protected function setDriver() {
		$uploadDriver = __NAMESPACE__.'\upload\\' . ucfirst( $this->config['driver'] ).'Driver';
		$this->uploader = new $uploadDriver($this->config);
		if(!$this->uploader){
            throw new \Exception("Upload Driver '{$this->config['driver']}' not found'", 500);
        }
    }

    /**
     * 获取上传文件信息
     * @return array
     */
	public function getUploadFileInfo() {
		return $this->uploadFileInfo;
	}

    /**
     * 获取框架错误
     * @return string
     */
    public function getError(){
        return $this->errorMsg;
    }

}