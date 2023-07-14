SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `advances_cascades_testingcomps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL DEFAULT '0',
  `cascade_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `testingcomp_id` (`testingcomp_id`),
  KEY `cascade_id` (`cascade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

COMMIT;
