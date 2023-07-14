SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE device_certificates CHANGE `recertification_in_year` `recertification_in_year` DECIMAL(11,2) NOT NULL DEFAULT '0';

COMMIT;
