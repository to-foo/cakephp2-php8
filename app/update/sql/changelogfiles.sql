CREATE TABLE IF NOT EXISTS `changelogfiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `changelog_id` int NOT NULL,
  `changelog_data_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `base_filename` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;
/*test*/

TRUNCATE `changelogfiles`;

insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('6','2','pruefbericht_verschieben_1.png','1655967585-4211be07986d-1053-4f43-ab49-71c6a09c56f3.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('7','2','ueberarbeitung_zeitraeume_1.PNG','1655967585-4741eeec6ba5-0e37-4f8a-bf1b-5c29551de636.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('6','2','pruefbericht_verschieben_2.png','1655967585-5219be07986d-1053-4f43-ab49-71c6a09c56f3.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('10','2','geräte_suche_3.png','1655967585-5609bcaf4b0d-f586-45da-aee9-e2014fca1a3d.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('10','2','geräte_suche_1.png','1655967585-6007bcaf4b0d-f586-45da-aee9-e2014fca1a3d.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('10','2','geräte_suche_2.png','1655967585-6382bcaf4b0d-f586-45da-aee9-e2014fca1a3d.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('12','4','logos.png','1669208257-1622826667bd-0347-4409-9502-d3f14f746c32.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('13','4','stempel.png','1669209501-85063db28e51-bd32-4783-90c2-de1267b5ca1b.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('14','4','pruefbereiche.png','1669210169-3788e7fe9fa5-b28e-4c75-b2d3-cb03983951b8.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('15','4','template_01.png','1669211081-98986fd52e97-3040-49de-bffe-a1e586584458.png');
insert into `changelogfiles`(`changelog_data_id`,`changelog_id`,`base_filename`,`filename`) values('15','4','template_02.png','1669211082-02496fd52e97-3040-49de-bffe-a1e586584458.png');
