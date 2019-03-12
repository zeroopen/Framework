DROP TABLE IF EXISTS `/*{prefix}*/admin`;
CREATE TABLE `/*{prefix}*/admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台帐号id',
  `admin` varchar(100) NOT NULL DEFAULT '' COMMENT '账号',
  `password` char(128) NOT NULL DEFAULT '' COMMENT '密码',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态：0=禁用，1=正常',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='后台帐号表';

DROP TABLE IF EXISTS `/*{prefix}*/comment`;
CREATE TABLE `/*{prefix}*/comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `tid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '关联id',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '关联类型：0=应用，1=文章',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '评论内容',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0=屏蔽，1=正常',
  `sum` int(11) NOT NULL DEFAULT '0' COMMENT '子评论数',
  `like` int(11) NOT NULL DEFAULT '0' COMMENT '赞',
  PRIMARY KEY (`id`),
  KEY `id_type` (`tid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论表';

DROP TABLE IF EXISTS `/*{prefix}*/comment_like`;
CREATE TABLE `/*{prefix}*/comment_like` (
  `id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论id',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论点赞表';

DROP TABLE IF EXISTS `/*{prefix}*/comment_reply`;
CREATE TABLE `/*{prefix}*/comment_reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回复id',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '回复内容',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '回复时间',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0=屏蔽，1=正常',
  `reply_uid` int(11) NOT NULL DEFAULT '0' COMMENT '回复id',
  `reply_uname` varchar(50) NOT NULL DEFAULT '' COMMENT '回复昵称',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='评论回复表';

DROP TABLE IF EXISTS `/*{prefix}*/member`;
CREATE TABLE `/*{prefix}*/member` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` char(128) NOT NULL COMMENT '密码',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `ip` varchar(20) NOT NULL DEFAULT '0' COMMENT 'ip地址',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帐号状态：0=正常，1=冻结',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `nickname` varchar(50) NOT NULL COMMENT '用户昵称',
  `avatar` varchar(255) NOT NULL COMMENT '用户头像',
  `point` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户积分',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户表';

DROP TABLE IF EXISTS `/*{prefix}*/member_email`;
CREATE TABLE `/*{prefix}*/member_email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '验证id',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `code` char(6) NOT NULL DEFAULT '0' COMMENT '验证码',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '邮箱状态',
  `error_num` int(2) NOT NULL DEFAULT '0' COMMENT '错误次数',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '发送时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '失效时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邮箱验证表';

DROP TABLE IF EXISTS `/*{prefix}*/member_error`;
CREATE TABLE `/*{prefix}*/member_error` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '错误id',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `num` int(4) NOT NULL DEFAULT '0' COMMENT '错误次数',
  `lock` int(2) NOT NULL DEFAULT '0' COMMENT '锁定次数',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
  `ip` varchar(20) NOT NULL DEFAULT '0' COMMENT 'ip地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='登录error表';

DROP TABLE IF EXISTS `/*{prefix}*/member_token`;
CREATE TABLE `/*{prefix}*/member_token` (
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `token` char(32) NOT NULL DEFAULT '' COMMENT 'token',
  `time` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户token表';

DROP TABLE IF EXISTS `/*{prefix}*/article`;
CREATE TABLE `/*{prefix}*/article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '发布者',
  `title` varchar(400) NOT NULL DEFAULT '' COMMENT '文章标题',
  `content` text NOT NULL COMMENT '内容',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '发布时间',
  `visitors` int(11) NOT NULL DEFAULT '0' COMMENT '游客数',
  `like` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章表';