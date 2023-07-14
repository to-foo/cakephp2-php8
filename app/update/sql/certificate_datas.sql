SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `certificate_datas`
CHANGE `certificate_id` `certificate_id` INT(11) NOT NULL DEFAULT '0',
CHANGE `examiner_id` `examiner_id` INT(11) NOT NULL DEFAULT '0',
CHANGE `testingmethod` `testingmethod` VARCHAR(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
CHANGE `certified` `certified` TINYINT(4) NOT NULL DEFAULT '0',
CHANGE `certified_file` `certified_file` VARCHAR(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
CHANGE `deleted` `deleted` TINYINT(11) NOT NULL DEFAULT '0',
CHANGE `active` `active` TINYINT(11) NOT NULL DEFAULT '0',
CHANGE `user_id` `user_id` INT(11) NOT NULL DEFAULT '0',
CHANGE `remark` `remark` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;

COMMIT;
