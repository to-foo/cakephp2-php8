SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lauf_nr` int(11) NOT NULL,
  `topproject_id` int(11) NOT NULL,
  `cascade_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `planner` varchar(50) NOT NULL,
  `contact_person` varchar(200) NOT NULL,
  `contact_person_mail` varchar(250) NOT NULL,
  `unit` varchar(10) NOT NULL,
  `equipment` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `equipment_typ` varchar(100) NOT NULL,
  `count` int(11) NOT NULL,
  `count_ordered` int(11) NOT NULL,
  `kategorie` varchar(10) NOT NULL,
  `tech_requis` varchar(10) NOT NULL,
  `peculiarity` text NOT NULL,
  `spare_part_list` text NOT NULL,
  `material_id` varchar(200) NOT NULL,
  `area_of_responsibility` varchar(10) NOT NULL,
  `modi_no` varchar(50) NOT NULL,
  `delivery_no` varchar(20) NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `supplier_project_no` varchar(50) NOT NULL,
  `order_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `deleted` tinyint(2) NOT NULL,
  `status` int(11) NOT NULL,
  `stop_mail` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;
