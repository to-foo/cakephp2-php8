SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `expediting_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topproject_id` int(11) NOT NULL DEFAULT '0',
  `cascade_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `supplier_id` int(11) NOT NULL DEFAULT '0',
  `expediting_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `expediting_type_id` int(11) NOT NULL DEFAULT '0',
  `date_soll` date DEFAULT NULL,
  `date_ist` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `karenz` int(11) NOT NULL DEFAULT '0',
  `discription` varchar(255) NOT NULL,
  `remark` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),d
  KEY `supplier_id` (`supplier_id`),
  KEY `expediting_id` (`expediting_id`),
  KEY `testingcomp_id` (`testingcomp_id`),
  KEY `order_id` (`order_id`),
  KEY `cascade_id` (`cascade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;

COMMIT;
