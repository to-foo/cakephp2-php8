ALTER TABLE `reportimages`
ADD `size` INT NOT NULL DEFAULT '100'
AFTER `print`,
ADD `max_size` TINYINT NOT NULL DEFAULT '0'
AFTER `size`,
ADD `border` TINYINT NOT NULL DEFAULT '1'
AFTER `max_size`,
ADD `position` TINYINT NOT NULL DEFAULT '0'
AFTER `border`;

ALTER TABLE `reportimages`
ADD `sorting` INT NOT NULL DEFAULT '0'
AFTER `position`;
