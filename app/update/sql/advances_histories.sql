SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `advances_histories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advance_id` int(11) NOT NULL DEFAULT '0',
  `cascade_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `advances_type_id` int(11) NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  `advance` varchar(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;
