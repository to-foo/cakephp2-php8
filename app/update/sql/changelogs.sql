CREATE TABLE IF NOT EXISTS `changelogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `log_date` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

COMMIT;
/*test*/
TRUNCATE `changelogs`;

insert into `changelogs`(`log_date`,`id`,`created`,`modified`) values('2022-06-26','2','2022-06-23T08:59:45','2022-06-23T08:59:45');
insert into `changelogs`(`log_date`,`id`,`created`,`modified`) values('2022-11-21','4','2022-11-23T13:57:37','2022-11-23T13:57:37');
