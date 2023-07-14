SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

update welder_monitorings set expiration_date = DATE_ADD(first_registration, INTERVAL recertification_in_year * 365 DAY) where expiration_date is null;
ALTER TABLE `welder_monitorings` CHANGE `recertification_in_year` `recertification_in_year` DECIMAL(11,0) NOT NULL DEFAULT '0';

COMMIT;
