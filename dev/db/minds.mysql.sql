-- #####################################################
-- IMPORTENT
-- #####################################################


-- #####################################################
-- STRUCT PART
-- #####################################################
DROP SCHEMA IF EXISTS `minds`;
CREATE SCHEMA `minds`; -- DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `minds`;
SET NAMES utf8;

-- -----------------------------------------------------
-- Table t_admin			管理员
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_admin`;
CREATE TABLE `t_admin`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`name`			VARCHAR(50) NOT NULL ,
	`pass`			VARCHAR(50) NOT NULL ,
	`roleid`		BIGINT NOT NULL DEFAULT 0 ,
	`lasttime`		DATETIME DEFAULT NULL , -- 最后登录
	`ip`			BIGINT NOT NULL DEFAULT 0 ,
	`act`			TINYINT(1) UNSIGNED DEFAULT 1 , -- 能够登录 0:否 1:是
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table t_role				管理员角色
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_role`;
CREATE TABLE `t_role`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`name`			VARCHAR(50) NOT NULL ,
	`permission`	TEXT ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table t_user				用户
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_user`;
CREATE TABLE `t_user`
(
  `id`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
  `user`			VARCHAR(50) NOT NULL , -- user (暂时不用)
  `pass`			VARCHAR(50) NOT NULL ,
  `mobile`			VARCHAR(50) NOT NULL ,
  `valid`			VARCHAR(50) NOT NULL , -- sms auth
  `nick`			VARCHAR(50) NOT NULL ,
  `sex`				TINYINT(1) UNSIGNED DEFAULT 1 , -- 性别 0:女 1:男
  `avatar`			VARCHAR(50) DEFAULT '' , -- 头像
  -- 以下为用户真实信息
  `name`			VARCHAR(50) DEFAULT '' , -- 姓名
  `email`			VARCHAR(50) DEFAULT '' ,
  `birthday`		DATE DEFAULT NULL ,
  `hobby`			VARCHAR(50) DEFAULT '' , -- 爱好
  `lasttime`		DATETIME DEFAULT NULL , -- 最后登录
  `act`				TINYINT(1) UNSIGNED DEFAULT 1 , -- 能够使用 0:否 1:是
  `time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100001;


-- -----------------------------------------------------
-- Table t_mblog			微博
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_mblog` CASCADE;
CREATE TABLE `t_mblog`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`mid`			TINYINT UNSIGNED NOT NULL DEFAULT 1 , -- mode id: 1:贴子 2:广告 3:通知
	`tid`			TINYINT UNSIGNED NOT NULL DEFAULT 1 , -- type id: 1:text 2:picture 3:voice 4:video
	`uid`			BIGINT NOT NULL DEFAULT 0 ,
	`hid`			TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 , -- 是否隐身 0:否 1:是
	`content`		VARCHAR(2000) DEFAULT '' ,
	`topics`		VARCHAR(200) DEFAULT '' , -- 话题标签
	`url`			VARCHAR(200) DEFAULT '' , -- image url or voice url or video url
	`size`			INTEGER NOT NULL DEFAULT 0 , -- maybe: image size
	`width`			INTEGER NOT NULL DEFAULT 0 , -- width
	`height`		INTEGER NOT NULL DEFAULT 0 , -- height
	`up`			INTEGER NOT NULL DEFAULT 0 , -- 支持数
	`down`			INTEGER NOT NULL DEFAULT 0 , -- 反对数
	`shares`		INTEGER NOT NULL DEFAULT 0 , -- 共享数
	`comments`		INTEGER NOT NULL DEFAULT 0 , -- 评论数
	`approves`		INTEGER NOT NULL DEFAULT 0 , -- 审核数
	`populars`		TEXT , -- 最受欢迎评论ID列表
	`sid`			TINYINT UNSIGNED NOT NULL DEFAULT 1 , -- stateid: 1:待审核 2:审核通过 3:审核未通过
	`act`			TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (id)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table t_mblog_comment		微博评论
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_mblog_comment` CASCADE;
CREATE TABLE `t_mblog_comment`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`uid`			BIGINT NOT NULL DEFAULT 0 ,
	`bid`			BIGINT NOT NULL DEFAULT 0 , -- mblog id
	`content`		VARCHAR(2000) DEFAULT '' ,
	`floor`			INTEGER NOT NULL DEFAULT 1 , -- 楼层数
	`up`			INTEGER NOT NULL DEFAULT 0 , -- 支持数
	`down`			INTEGER NOT NULL DEFAULT 0 , -- 反对数
	`approves`		INTEGER NOT NULL DEFAULT 0 , -- 审核数
	`sid`			TINYINT UNSIGNED NOT NULL DEFAULT 1 , -- stateid: 1:待审核 2:审核通过 3:审核未通过
	`act`			TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (id)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table t_feedback			意见反馈
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_feedback` CASCADE;
CREATE TABLE `t_feedback`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`uid`			BIGINT DEFAULT 0 ,
	`contact`		VARCHAR(50) DEFAULT '' , -- 联系人
	`content`		VARCHAR(500) DEFAULT '' , -- 反馈内容
	`sid`			TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 , -- 状态 1:未读 2:已读
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (id)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table t_version			版本管理
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_version`;
CREATE TABLE `t_version`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`tid`			TINYINT UNSIGNED NOT NULL DEFAULT 1 , -- 类型 1:android 2:ios
	`version`		INTEGER NOT NULL DEFAULT 0 , -- 版本id
	`name`			VARCHAR(50) NOT NULL ,
	`url`			VARCHAR(200) NOT NULL ,
	`description`	TEXT ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;

-- ######################################################
-- 日志表
-- ######################################################

-- -----------------------------------------------------
-- Table t_log_sms			短信记录
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_log_sms` CASCADE;
CREATE TABLE `t_log_sms`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`tid`			TINYINT NOT NULL DEFAULT 0 , -- typeid: 1:message 2:join 3:forget
	`mobile`		VARCHAR(50) NOT NULL ,
	`valid`			VARCHAR(50) NOT NULL , -- 验证码
	`expire`		DATETIME NOT NULL , -- 过期时间
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table t_log_login			管理员登录日志
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_log_login` CASCADE;
CREATE TABLE `t_log_login`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`aid`			BIGINT NOT NULL DEFAULT 0 ,
	`ip`			BIGINT NOT NULL DEFAULT 0 ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table t_log_event		数据事件日志
-- -----------------------------------------------------
DROP TABLE IF EXISTS `t_log_event` CASCADE;
CREATE TABLE `t_log_event`
(
	`id`			BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE ,
	`event`			TEXT ,
	`spent`			FLOAT DEFAULT 0 ,
	`memory`		INTEGER DEFAULT 0 ,
	`time`			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
	PRIMARY KEY (`id`)
)
ENGINE = MyISAM  DEFAULT CHARSET=utf8;


-- #####################################################
-- DATA PART
-- #####################################################

-- -----------------------------------------------------
-- Insert t_admin
-- -----------------------------------------------------
INSERT INTO t_admin(`name`, `pass`, `roleid`) VALUES('admin', md5('111111'), 1);

-- -----------------------------------------------------
-- Insert t_role
-- -----------------------------------------------------
INSERT INTO t_role(`name`) VALUES('超级管理员');


-- -----------------------------------------------------
-- Insert t_user
-- -----------------------------------------------------
INSERT INTO t_user(`user`, `mobile`, `pass`, `valid`, `nick`, `sex`) VALUES('', '13776771331', md5('111111'), '0415', 'momo', 1);
