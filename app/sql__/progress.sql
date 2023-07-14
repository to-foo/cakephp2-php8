SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `mps_mbq`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters`
--

CREATE TABLE IF NOT EXISTS `progresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `modul` varchar(255) DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `dependencies` tinyint(4) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `progresses_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_data`
--

CREATE TABLE IF NOT EXISTS `progresses_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `progresses_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_dependencies`
--

CREATE TABLE IF NOT EXISTS `progresses_data_dependencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingcomp_id` int(11) NOT NULL,
  `progresses_id` int(11) NOT NULL,
  `progresses_data_id` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `global` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_report`
--

CREATE TABLE IF NOT EXISTS `progresses_cascade` (
  `progresses_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_testingcomp`
--

CREATE TABLE IF NOT EXISTS `progresses_testingcomp` (
  `progresses_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_topproject`
--

CREATE TABLE IF NOT EXISTS `progresses_topproject` (
  `progresses_id` int(11) NOT NULL DEFAULT '0',
  `topproject_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



COMMIT;
