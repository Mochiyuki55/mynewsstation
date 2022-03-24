-- ---
-- Globals
-- ---

-- SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
-- SET FOREIGN_KEY_CHECKS=0;

-- ---
-- Table 'user'
--
-- ---

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(30) NOT NULL,
  `user_email` VARCHAR(200) NOT NULL,
  `user_password` VARCHAR(60) NOT NULL,
  `process_hour` INTEGER NOT NULL,
  `last_process_time` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `twitter_consumer_key` VARCHAR(200) NULL DEFAULT NULL,
  `twitter_consumer_secret` VARCHAR(200) NULL DEFAULT NULL,
  `twitter_access_token` VARCHAR(200) NULL DEFAULT NULL,
  `twitter_access_token_secret` VARCHAR(200) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'news'
--
-- ---

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `query` VARCHAR(200) NULL DEFAULT NULL,
  `date_from` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `date_to` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  `language` VARCHAR(10) NULL DEFAULT NULL,
  `sort_by` VARCHAR(20) NULL DEFAULT NULL,
  `news_api` VARCHAR(200) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'auto_login'
--
-- ---

DROP TABLE IF EXISTS `auto_login`;

CREATE TABLE `auto_login` (
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `c_key` VARCHAR(40) NOT NULL,
  `expire` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'admin'
--
-- ---

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `admin_account` VARCHAR(20) NOT NULL,
  `admin_password` VARCHAR(20) NOT NULL,
  `notice` MEDIUMTEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

-- ---
-- Table 'cron_log'
--
-- ---

DROP TABLE IF EXISTS `cron_log`;

CREATE TABLE `cron_log` (
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `user_id` INTEGER NOT NULL,
  `message` VARCHAR(1000) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
);

-- ---
-- Foreign Keys
-- ---


-- ---
-- Table Properties
-- ---

-- ALTER TABLE `user` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
-- ALTER TABLE `auto_login` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
-- ALTER TABLE `admin` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
-- ALTER TABLE `cron_log` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- ---
-- Test Data
-- ---

-- INSERT INTO `user` (`id`,`user_name`,`user_email`,`user_password`,`process_hour`,`last_process_time`,`twitter_consumer_key`,`twitter_consumer_secret`,`twitter_access_token`,`twitter_access_token_secret`,`created_at`,`updated_at`) VALUES
-- ('','','','','','','','','','','','');
-- INSERT INTO `auto_login` (`id`,`user_id`,`c_key`,`expire`,`created_at`,`updated_at`) VALUES
-- ('','','','','','');
-- INSERT INTO `admin` (`id`,`admin_account`,`admin_password`,`notice`,`created_at`,`updated_at`) VALUES
-- ('','','','','','');
-- INSERT INTO `cron_log` (`id`,`user_id`,`message`,`created_at`,`updated_at`) VALUES
-- ('','','','','');
