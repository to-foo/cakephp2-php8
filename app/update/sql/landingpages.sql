SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `mps_mbq`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `landingpages`
--

CREATE TABLE IF NOT EXISTS `landingpages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingcomp_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `landingpages_data`
--

CREATE TABLE IF NOT EXISTS `landingpages_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `landingpages_id` int(11) NOT NULL DEFAULT '0',
  `landingpages_template_id` int(11) NOT NULL DEFAULT '0',
  `controller` varchar(200) NOT NULL,
  `action` varchar(200) NOT NULL,
  `place` varchar(200) NOT NULL,
  `options` varchar(200) NOT NULL,
  `modul` varchar(200) NOT NULL,
  `config` varchar(100) NOT NULL,
  `sorting` int(11) NOT NULL DEFAULT '0',
  `position` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `landingpages_templates`
--

CREATE TABLE IF NOT EXISTS `landingpages_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(200) NOT NULL,
  `action` varchar(200) NOT NULL,
  `place` varchar(200) NOT NULL,
  `options` varchar(200) NOT NULL,
  `modul` varchar(200) NOT NULL,
  `config` varchar(100) NOT NULL,
  `sorting` int(11) NOT NULL DEFAULT '0',
  `position` tinyint(4) NOT NULL DEFAULT '0',
  `deu` varchar(200) NOT NULL,
  `eng` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `landingpages_templates`
--

INSERT INTO `landingpages_templates` (`id`, `controller`, `action`, `place`, `options`, `modul`, `config`, `sorting`, `position`, `deu`, `eng`) VALUES
(1, 'topprojects', 'index', 'large_window', '', '', '', 0, 1, 'Projekte', 'Projects'),
(2, 'topprojects', 'last_ten', 'last_projects', '', 'LastProjectUrl', '', 0, 2, 'Meine letzten Projekte', 'Last projects'),
(3, 'reportnumbers', 'last_ten', 'last_reports', '', 'LastReportstUrl', '', 0, 2, 'Meine letzten Prüfberichte', 'My last reports'),
(4, 'monitorings', 'index', 'last_contact', 'contact', 'LastMonitorings', 'MonitoringManagerWidget', 0, 2, 'Monitorings', 'Monitorings'),
(5, 'advances', 'edit', 'last_advances', '', 'LastAdvances', 'AdvanceManagerWidget', 0, 2, 'Progress', 'Progress'),
(6, 'suppliers', 'index', 'last_expediting', '', 'LastExpediting', 'ExpeditingManagerWidget', 0, 2, 'Expeditings', 'Expeditings'),
(7, 'examiners', 'index', 'last_certificates', '', 'LastCertificates', 'CertifcateManagerWidget', 0, 2, 'Zertifizierungen Termine und Aufgaben', 'Certificats dates and tasks'),
(8, 'devices', 'index', 'last_devices', '', 'LastDevices', 'DeviceManagerWidget', 0, 2, 'Geräte Termine und Aufgaben', 'Devices dates and tasks'),
(9, 'examiners', 'index', 'large_window', '', '', '', 0, 1, 'Zertifikate', 'Certificates');
COMMIT;
