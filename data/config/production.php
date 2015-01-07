<?php 
return array (
	//错误调试与日志配置
	'DEBUG' => false,	//是否开启调试模式
	'LOG_ON' => true,	//是否开启出错信息保存到文件
	'ERROR_URL' => '', //出错跳转地址
		
	//网址与路由配置
	'URL_BASE' => '/', //设置网址域名				
	'URL_REWRITE' =>array(
	
	),
	
	//数据库配置
	'DB'=>array(
		'default' => 
			array (
				'DB_TYPE' => 'mysql',
				'DB_HOST' => 'localhost',
				'DB_USER' => 'root',
				'DB_PWD' => '123456',
				'DB_PORT' => '3306',
				'DB_NAME' => '123',
				'DB_CHARSET' => 'utf8',
				'DB_PREFIX' => 'cp_',
			),
	),
);