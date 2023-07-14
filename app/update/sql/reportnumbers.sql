SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `reportnumbers` ADD INDEX(`topproject_id`);
ALTER TABLE `reportnumbers` ADD INDEX(`delete`);
ALTER TABLE `reportnumbers` ADD INDEX(`parent_id`);
ALTER TABLE `reportnumbers` ADD INDEX(`testingmethod_id`);
ALTER TABLE `reportnumbers` ADD INDEX(`modified_user_id`);
COMMIT;
