SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `advances_data_dependencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingcomp_id` int(11) NOT NULL DEFAULT '0',
  `advance_id` int(11) NOT NULL DEFAULT '0',
  `cascade_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `advances_type_id` int(11) NOT NULL DEFAULT '0',
  `advances_data_id` int(11) NOT NULL DEFAULT '0',
  `field` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `detail` text NOT NULL,
  `value` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=offen,1=okay,2=error',
  `remark` text,
  `global` tinyint(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `dimension` varchar(255) DEFAULT NULL,
  `corrosion` varchar(255) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `access` varchar(255) DEFAULT NULL,
  `insulating` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `temperature` varchar(255) DEFAULT NULL,
  `medium` varchar(255) DEFAULT NULL,
  `pruefdruck` varchar(255) DEFAULT NULL,
  `ersatzpruefung` varchar(255) DEFAULT NULL,
  `grund_fuer_ersatz` varchar(255) DEFAULT NULL,
  `pruefung` varchar(255) DEFAULT NULL,
  `herstell_nr` varchar(255) DEFAULT NULL,
  `rev` varchar(255) DEFAULT NULL,
  `time_in_h` varchar(255) DEFAULT NULL,
  `pre_zfp` varchar(255) DEFAULT NULL,
  `zfp_im_ta` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cascade_id` (`cascade_id`),
  KEY `order_id` (`order_id`),
  KEY `advances_data_id` (`advances_data_id`),
  KEY `advances_type_id` (`advances_type_id`),
  KEY `id` (`id`),
  KEY `advance_id` (`advance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;
