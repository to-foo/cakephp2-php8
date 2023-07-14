SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `advances_data_reports` (
  `advance_id` int(11) NOT NULL DEFAULT '0',
  `advances_data_id` int(11) NOT NULL DEFAULT '0',
  `reportnumber_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;
