SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


ALTER TABLE `cascades` ADD INDEX(`parent`);
ALTER TABLE `cascades` CHANGE `parent` `parent` INT NOT NULL DEFAULT '0';
UPDATE `cascades` SET `parent` = '99999' WHERE `cascades`.`id` = 18;
UPDATE `cascades` SET `discription` = 'Area 2ac' WHERE `cascades`.`id` = 4;
UPDATE `cascadegroups_cascades` SET `cascade_group_id` = '15' WHERE `cascadegroups_cascades`.`cascade_group_id` = 13;
UPDATE `advances_cascades` SET `deleted` = '1' WHERE `advances_cascades`.`cascade_id` IN(727,728,2428,729,918,736,737,738);
UPDATE `advances_data` SET `deleted` = '1' WHERE `advances_data`.`cascade_id` IN(727,728,2428,729,918,736,737,738);



DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2471;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2442;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 505;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 506;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2443;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2472;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 200;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 201;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 154;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 153;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 152;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 151;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 150;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2343;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2342;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 293;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2341;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 774;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 847;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 773;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 323;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 772;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 322;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 384;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 793;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 383;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 2386;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 382;
DELETE FROM `cascadegroups_cascades` WHERE `cascadegroups_cascades`.`id` = 290;

ALTER TABLE `cascadegroups_cascades` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
SELECT `cascade_id`, COUNT(*) AS `count` FROM cascadegroups_cascades GROUP BY `cascade_id` ORDER BY `count` DESC;


UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 859;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 860;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 861;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 862;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 863;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 864;
UPDATE `cascades` SET `parent` = '35' WHERE `cascades`.`id` = 865;
