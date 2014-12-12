<?php 
return array (
	//错误调试与日志配置
	'DEBUG' => true,	//是否开启调试模式
	'LOG_ON' => false,	//是否开启出错信息保存到文件
	'ERROR_URL' => '', //出错跳转地址
		
	//网址与路由配置
	'URL_BASE' => '/', //设置网址域名				
	'URL_REWRITE' =>array(
	
	),
	
	//数据库配置
	'DB'=>array(
		'default' => 
			array (
				'DB_TYPE' => 'mysqlpdo',
				'DB_HOST' => 'localhost',
				'DB_USER' => 'root',
				'DB_PWD' => 'asdfgh',
				'DB_PORT' => '3306',
				'DB_NAME' => 'cp',
				'DB_CHARSET' => 'utf8',
				'DB_PREFIX' => '',
			),
	),
);