-- phpMyAdmin SQL Dump
-- version 3.4.8
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1:3306
-- 生成日期: 2015 年 08 月 24 日 23:05
-- 服务器版本: 5.5.30
-- PHP 版本: 5.4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8;

--
-- 数据库: `third_login`
--

-- --------------------------------------------------------

--
-- 表的结构 `app`
--

CREATE TABLE IF NOT EXISTS `app` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `name` varchar(100) NOT NULL COMMENT '应用名称',
  `app_id` varchar(32) NOT NULL COMMENT '本系统分配的APPID',
  `app_key` varchar(32) NOT NULL COMMENT '本系统分配的APPKEY',
  `site_url` varchar(1000) DEFAULT NULL COMMENT '站点地址',
  `bound` text COMMENT '域名绑定',
  `status` tinyint(4) DEFAULT NULL COMMENT '状态',
  `note` text COMMENT '备注',
  `token_url` varchar(1000) DEFAULT NULL COMMENT '回调地址',
  `receriver_url` varchar(1000) DEFAULT NULL COMMENT '转发地址',
  `bind_url` varchar(1000) DEFAULT NULL COMMENT '绑定处理地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- 表的结构 `app_media`
--

CREATE TABLE IF NOT EXISTS `app_media` (
  `app_id` int(11) NOT NULL COMMENT '应用id',
  `media_id` int(11) NOT NULL COMMENT '第三方媒体id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态1可用0不可用',
  `APPID` varchar(50) DEFAULT NULL COMMENT '第三方ID',
  `APPKEY` varchar(50) DEFAULT NULL COMMENT '第三方KEY',
  `AppInfo` varchar(400) DEFAULT NULL COMMENT '第三方app信息(json格式)',
  PRIMARY KEY (`app_id`,`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `dic_media`
--

CREATE TABLE IF NOT EXISTS `dic_media` (
  `id` int(11) NOT NULL COMMENT '媒体ID',
  `name` varchar(50) NOT NULL COMMENT '媒体名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '媒体状态1可用0不可用',
  `def_APPID` varchar(50) DEFAULT NULL COMMENT '默认的第三方ID',
  `def_APPKEY` varchar(50) DEFAULT NULL COMMENT '默认的第三方KEY',
  `def_AppInfo` varchar(400) DEFAULT NULL COMMENT '默认的App的信息(json格式)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `login_media_qihoo_token`
--

CREATE TABLE IF NOT EXISTS `login_media_qihoo_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `login_token` varchar(32) NOT NULL COMMENT 'login_token.token即本系统产生的登录token',
  `qihoo_access_token` varchar(32) NOT NULL COMMENT '360返回的授权token',
  `qihoo_uid` varchar(100) NOT NULL COMMENT '360的用户唯一标识',
  `qihoo_expires_in` int(11) DEFAULT NULL COMMENT '失效期单位秒',
  `qihoo_refresh_token` varchar(100) DEFAULT NULL COMMENT '在授权自动续期步骤中，获取新的Access_Token时需要提供的参数。',
  `scope` varchar(50) DEFAULT NULL COMMENT '授权范围',
  `note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `login_media_qqweibo_token`
--

CREATE TABLE IF NOT EXISTS `login_media_qqweibo_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `login_token` varchar(32) NOT NULL COMMENT 'login_token.token',
  `qqweibo_access_token` varchar(100) NOT NULL COMMENT '授权令牌',
  `qqweibo_openid` varchar(100) NOT NULL COMMENT 'QQweibo唯一标识',
  `qqweibo_expire_in` int(11) DEFAULT NULL COMMENT '失效期单位秒',
  `qqweibo_refresh_token` varchar(100) DEFAULT NULL COMMENT '在授权自动续期步骤中，获取新的Access_Token时需要提供的参数。',
  `note` text COMMENT 'http://wiki.connect.qq.com/%E4%BD%BF%E7%94%A8authorization_code%E8%8E%B7%E5%8F%96access_token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `login_media_qq_token`
--

CREATE TABLE IF NOT EXISTS `login_media_qq_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `login_token` varchar(32) NOT NULL COMMENT 'login_token.token',
  `qq_access_token` varchar(100) NOT NULL COMMENT '授权令牌',
  `qq_openid` varchar(100) NOT NULL COMMENT 'QQ唯一标识',
  `qq_expires_in` int(11) DEFAULT NULL COMMENT '失效期单位秒',
  `qq_refresh_token` varchar(100) DEFAULT NULL COMMENT '在授权自动续期步骤中，获取新的Access_Token时需要提供的参数。',
  `note` text COMMENT 'http://wiki.connect.qq.com/%E4%BD%BF%E7%94%A8authorization_code%E8%8E%B7%E5%8F%96access_token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `login_media_qq_token`
--


--
-- 表的结构 `login_media_renren_token`
--

CREATE TABLE IF NOT EXISTS `login_media_renren_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `token_type` varchar(32) NOT NULL COMMENT 'token类型',
  `access_token` varchar(100) NOT NULL COMMENT 'login后获取的token',
  `uid` bigint(20) DEFAULT NULL COMMENT '用户唯一标识',
  `refresh_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `login_media_weibo_token`
--

CREATE TABLE IF NOT EXISTS `login_media_weibo_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `login_token` varchar(32) NOT NULL COMMENT 'login_token.token',
  `weibo_access_token` varchar(100) NOT NULL COMMENT '授权令牌',
  `weibo_uid` varchar(100) NOT NULL COMMENT 'weibo唯一标识',
  `weibo_expires_in` int(11) DEFAULT NULL COMMENT '失效期单位秒',
  `weibo_refresh_token` varchar(100) DEFAULT NULL COMMENT '在授权自动续期步骤中，获取新的Access_Token时需要提供的参数。',
  `note` text COMMENT 'http://open.weibo.com/wiki/SDK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `login_token`
--

CREATE TABLE IF NOT EXISTS `login_token` (
  `c_time` datetime NOT NULL COMMENT '创建时间',
  `e_time` datetime NOT NULL COMMENT '过期时间',
  `u_time` datetime DEFAULT NULL COMMENT '使用时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  `token` varchar(32) NOT NULL COMMENT '验证码',
  `app_id` varchar(32) NOT NULL COMMENT 'app.app_id',
  `media_id` int(11) NOT NULL COMMENT '媒体ID(dic_media.id)',
  `media_user_id` bigint(11) NOT NULL COMMENT '媒体用户id(media_user.mediaUserID)',
  PRIMARY KEY (`c_time`,`token`,`media_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 表的结构 `media_qihoo_user`
--

CREATE TABLE IF NOT EXISTS `media_qihoo_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '360用户存于系统的ID',
  `uid` varchar(100) NOT NULL COMMENT '360用户唯一标识',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;


--
-- 表的结构 `media_qqweibo_user`
--

CREATE TABLE IF NOT EXISTS `media_qqweibo_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'QQ用户存于系统的ID',
  `openid` varchar(100) NOT NULL COMMENT 'QQ用户唯一标识',
  `APPID` varchar(50) NOT NULL COMMENT '第三方ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid_APPID` (`openid`,`APPID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;


--
-- 表的结构 `media_qq_user`
--

CREATE TABLE IF NOT EXISTS `media_qq_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'QQ用户存于系统的ID',
  `openid` varchar(100) NOT NULL COMMENT 'QQ用户唯一标识',
  `APPID` varchar(50) NOT NULL COMMENT '第三方ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid_APPID` (`openid`,`APPID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=320 ;


--
-- 表的结构 `media_renren_user`
--

CREATE TABLE IF NOT EXISTS `media_renren_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'renren用户存于系统的ID',
  `uid` varchar(100) NOT NULL COMMENT 'renren用户唯一标识',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;


--
-- 表的结构 `media_user`
--

CREATE TABLE IF NOT EXISTS `media_user` (
  `mediaID` int(11) DEFAULT NULL,
  `screenName` varchar(100) DEFAULT NULL,
  `profileImageUrl` varchar(500) DEFAULT NULL,
  `mediaUserID` bigint(20) NOT NULL COMMENT 'media_id*10000000000+user_id',
  PRIMARY KEY (`mediaUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 表的结构 `media_weibo_user`
--

CREATE TABLE IF NOT EXISTS `media_weibo_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'weibo用户存于系统的ID',
  `uid` varchar(100) NOT NULL COMMENT 'weibo用户唯一标识',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;
