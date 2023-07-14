CREATE TABLE IF NOT EXISTS `ticketwelds_testingmethods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingmethod_id` int(11) NOT NULL,
  `ticketweld_id` varchar(11) NOT NULL,
  `ticket_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
