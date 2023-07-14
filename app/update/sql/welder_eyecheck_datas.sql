SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

update welder_eyecheck_datas set expiration_date = DATE_ADD(certified_date, INTERVAL recertification_in_year * 365 DAY) where expiration_date is null;

COMMIT;
