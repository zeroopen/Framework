<?php

// 网站配置
return array(
	'name' => 'Site', //网站名称
	'default' => array(
		'status' => 1, //是否开启网站
		'logo' => '/static/common/image/logo.png', //网站logo
		'view' => 'Default' //网站默认视图
	),
	'mobile' => array(
		'status' => 1, //是否开启手机版
		'logo' => '/static/common/image/logo_m.png', //手机版logo
		'url' => '', //手机版地址，无需加网络协议
		'view' => 'Mobile' //手机版视图
	),
	'client' => array(
		'status' => 0, //是否开启客户端
		'sign' => 'ok', //客户端签名
	)
);