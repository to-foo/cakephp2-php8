CREATE TABLE IF NOT EXISTS `changelog_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `changelog_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `identifier` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

COMMIT;


TRUNCATE `changelog_datas`;

insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Prüfberichte verschieben','Die offenen Prüfberichte können beim Bearbeiten über die Felder "Kaskade", "Auftrag" und "Prüfberichtsmappe" verschoben werden.','update','be07986d-1053-4f43-ab49-71c6a09c56f3','2','6');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Überarbeitung der Zeiträume in der Prüferverwaltung','In der Prüferverwaltung wurden die Zeiträume überarbeitet.
Der Nutzer trägt statt der Periode das Ablaufdatum ein.','change','eeec6ba5-0e37-4f8a-bf1b-5c29551de636','2','7');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Dem Prüfer einer Benutzergruppe oder einem Benutzer zuordnen','Der Prüfer kann einer Benutzergruppe oder einem Benutzer zugeordnet werden.
Somit kann die Sichtbarkeit der Datensätze eingeschränkt werden.','update','f9c8b8d4-a2bd-470b-8da8-fbdf93d17349','2','8');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Überarbeitung Dropdowns','Die Dropdowns wurden überarbeitet. Ein Dropdownfeld kann über den Schalter "Dieses Dropdownfeld löschen" entfernt werden.','change','a18f06be-647d-48ba-bcbb-2a298194aaff','2','9');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Suche nach Geräten und Überwachungen in der Geräteverwaltung','In der Geräteverwaltung ist es dem Nutzer möglich, nach Geräten und Überwachungen zu suchen.','update','bcaf4b0d-f586-45da-aee9-e2014fca1a3d','2','10');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Zusätzliche Logos','Im Druckmenü kann das Mitdrucken von zusätzlichen Logos (falls vorhanden) an- oder ausgeschalten werden. Durch das Anklicken des Logos, wird die Darstellung in der PDF-Datei aktiviert oder deaktiviert. Nach dem Schließen eines Prüfberichts ist diese Funktion nicht mehr verfügbar.','update','826667bd-0347-4409-9502-d3f14f746c32','4','12');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Prüferstempel','In der Prüfer/Zertifikatsverwaltung können Prüferstempel, in Form einer SVG-Datei, hinterlegt werden. Diese Stempel werden auf dem PDF-Dokument ausgegeben.','update','3db28e51-bd32-4783-90c2-de1267b5ca1b','4','13');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Schnellbearbeitung der Auswertungsbereiche','Die Schnellbearbeitung der Auswertungsbereiche wurde erweitert. In der Auswertungstabelle können jetzt mehrere Werte eines Prüfbereichs, mit einem Arbeitsgang geändert werden. In der Tabelle kann nun auch Naht- und Prüfbereichsbezeichnung bearbeitet werden.','update','e7fe9fa5-b28e-4c75-b2d3-cb03983951b8','4','14');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Prüfberichtsvorlagen','Implementierung eines Template-Managers, in dem oft verwendete Prüfberichte und Nahtbereiche als Vorlagen gespeichert werden können.','update','6fd52e97-3040-49de-bffe-a1e586584458','4','15');
insert into `changelog_datas`(`title`,`content`,`category`,`identifier`,`changelog_id`,`id`) values('Pdf-Ausgabe iPad','Die PDF-Dateien der Prüfberichte werden auf dem iPad wieder korrekt ausgegeben.','bugfix','bc4e294d-0a68-4fe8-8c51-acd23cf49e33','4','18');
