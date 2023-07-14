SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE examiner_monitorings CHANGE `renewal_in_year` `renewal_in_year` int(11) NOT NULL DEFAULT '0';
ALTER TABLE examiner_monitorings CHANGE `recertification_in_year` `recertification_in_year` DECIMAL(11,2) NOT NULL DEFAULT '0';

COMMIT;
