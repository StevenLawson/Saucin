CREATE DATABASE IF NOT EXISTS `saucin`

USE `saucin`;

CREATE TABLE IF NOT EXISTS `urls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(50) NOT NULL,
  `lookup_key` varbinary(50) NOT NULL,
  `target_url` varchar(2000) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `is_custom` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lookup_key` (`lookup_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_ip` varchar(50) NOT NULL,
  `num_urls` int(10) unsigned NOT NULL DEFAULT '0',
  `banned` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
