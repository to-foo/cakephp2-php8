SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `expediting_reportnumbers` (
  `expediting_id` int(11) NOT NULL,
  `reportnumber_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

COMMIT;
