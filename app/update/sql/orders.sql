SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `orders` CHANGE `partner_company` `partner_company` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `orders` CHANGE `customer_representative` `customer_representative` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `orders` CHANGE `approval_organisation` `approval_organisation` VARCHAR(255) NULL DEFAULT NULL;

COMMIT;
