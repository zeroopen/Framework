<?php

return array(
	'driver' => 'mysql', //数据库类型
	'group' => 1, //连接数据库使用的组，false为自动选择组连接
	'info' => array(
		1 => array(
			'host' => 'localhost', //数据库地址
			'user' => 'root', //数据库用户
			'password' => '', //数据库密码
			'port' => 3306, //数据库端口
			'name' => 'test', //数据库名称
			'charset' => 'utf8', //数据库编码
			'prefix' => 'pre_', //数据库表名前缀
			'slaves' => array(
			),
		),
		2 => array(
		),
	),
);