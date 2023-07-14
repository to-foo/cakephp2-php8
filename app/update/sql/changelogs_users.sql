CREATE TABLE IF NOT EXISTS `changelogs_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `changelog_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;
