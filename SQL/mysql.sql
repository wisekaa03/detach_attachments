CREATE TABLE IF NOT EXISTS `attachments` (
  `cache_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(128) CHARACTER SET ascii NOT NULL,
  `fname` varchar(256) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `data` longtext NOT NULL,
  `downloads` int(10) unsigned NOT NULL DEFAULT '0',
  `recipients` text,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`cache_id`),
  KEY `created_index` (`created`),
  KEY `user_cache_index` (`user_id`,`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `attachments`
  ADD CONSTRAINT `user_id_fk_attacments` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;