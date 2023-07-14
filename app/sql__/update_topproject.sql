SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `topprojects` CHANGE `subdivision` `subdivision` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `topprojects` CHANGE `status` `status` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `topprojects` ADD `add_master_dropdowns` TINYINT NOT NULL DEFAULT '0' AFTER `email`;
ALTER TABLE `topprojects` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

CREATE TABLE dropdowns_masters_topprojects LIKE dropdowns_masters_topproject; 
INSERT INTO dropdowns_masters_topprojects SELECT * FROM dropdowns_masters_topproject;

COMMIT;
