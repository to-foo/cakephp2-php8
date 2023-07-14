SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE welder_monitoring_datas CHANGE `recertification_in_year` `recertification_in_year` DECIMAL(11,0) NOT NULL DEFAULT '0';
update welder_monitoring_datas set expiration_date = DATE_ADD(certified_date, INTERVAL recertification_in_year * 365 DAY) where expiration_date is null;

COMMIT;
