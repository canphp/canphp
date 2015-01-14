<?php 
return array (
	'DEBUG' => true,
	
	//route config
	'REWRITE_ON' => 'false', 		
	'REWRITE_RULE' =>array(
		//'<app>/<c>/<a>'=>'<app>/<c>/<a>',
	),
	
	//db config
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