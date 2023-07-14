SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE IF NOT EXISTS `examiner_testingcomps` ( `id` INT NOT NULL AUTO_INCREMENT , `testingcomp_id` INT NOT NULL , `examiner_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;


INSERT INTO `examiner_testingcomps` (`examiner_id`, `testingcomp_id`)
SELECT `id`, `testingcomp_id`
FROM `examiners`
where not exists(
  select `examiner_id`, `testingcomp_id`
  from `examiner_testingcomps`
  where `examiner_testingcomps`.`examiner_id` = `examiners`.`id`
  and `examiner_testingcomps`.`testingcomp_id` = `examiners`.`testingcomp_id`
);

COMMIT;
