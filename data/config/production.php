<?php 
return array (
	'DEBUG' => false,	
		
	//route config
	'REWRITE_ON' => 'false', 		
	'REWRITE_RULE' =>array(
		//'<app>/<c>/<a>'=>'<app>/<c>/<a>',
	),
	
	//db config
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