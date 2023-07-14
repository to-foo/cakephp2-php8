-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 26. Aug 2021 um 12:20
-- Server-Version: 10.1.44-MariaDB-0+deb9u1
-- PHP-Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `Alstom-DB`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `certifcatefiles`
--

DROP TABLE IF EXISTS `certifcatefiles`;
CREATE TABLE `certifcatefiles` (
  `id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `originally_filename` varchar(250) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT '0',
  `basename` varchar(200) NOT NULL,
  `description` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `certificates`
--

DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `sector` varchar(20) NOT NULL,
  `certificat` varchar(200) NOT NULL,
  `third_part` varchar(200) NOT NULL,
  `testingmethod` varchar(200) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `level` int(11) NOT NULL,
  `first_registration` date DEFAULT NULL,
  `first_certification` int(11) NOT NULL,
  `recertification_in_year` decimal(11,0) NOT NULL,
  `renewal_in_year` decimal(11,0) NOT NULL,
  `horizon` decimal(10,0) DEFAULT NULL,
  `supervisor` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `certificate_data_active` tinyint(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `certificates`
--

INSERT INTO `certificates` (`id`, `examiner_id`, `sector`, `certificat`, `third_part`, `testingmethod`, `exam_date`, `level`, `first_registration`, `first_certification`, `recertification_in_year`, `renewal_in_year`, `horizon`, `supervisor`, `created`, `modified`, `deleted`, `active`, `user_id`, `certificate_data_active`) VALUES
(1, 46, 'Is', '0000', 'DGZfP', 'Rt', NULL, 2, '2021-01-26', 4, '10', '5', '1', 1, '2021-08-26 08:16:07', '2021-08-26 08:17:02', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `certificate_datas`
--

DROP TABLE IF EXISTS `certificate_datas`;
CREATE TABLE `certificate_datas` (
  `id` int(11) NOT NULL,
  `certificate_id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `testingmethod` varchar(20) NOT NULL,
  `first_certification` tinyint(4) NOT NULL DEFAULT '0',
  `certified` tinyint(4) NOT NULL,
  `recertification_in_year` int(11) NOT NULL,
  `renewal_in_year` int(11) NOT NULL,
  `horizon` int(11) NOT NULL DEFAULT '0',
  `first_registration` date DEFAULT NULL,
  `certified_date` date DEFAULT NULL,
  `certified_file` varchar(200) NOT NULL,
  `apply_for_recertification` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `remark` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `certificate_datas`
--

INSERT INTO `certificate_datas` (`id`, `certificate_id`, `examiner_id`, `testingmethod`, `first_certification`, `certified`, `recertification_in_year`, `renewal_in_year`, `horizon`, `first_registration`, `certified_date`, `certified_file`, `apply_for_recertification`, `created`, `modified`, `deleted`, `active`, `user_id`, `remark`) VALUES
(1, 1, 46, 'Rt', 4, 1, 10, 5, 1, '2021-12-26', '2021-08-26', '46_1_1_1629958650.pdf', 0, '2021-08-26 08:16:07', '2021-08-26 08:17:30', 0, 1, 1, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `devices`
--

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `producer` varchar(255) NOT NULL,
  `measure` text NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `device_type` varchar(255) NOT NULL,
  `barcode` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `year_built` int(11) NOT NULL,
  `year_purchase` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `registration_no` varchar(200) NOT NULL,
  `intern_no` varchar(255) NOT NULL,
  `working_place` varchar(250) NOT NULL,
  `first_registration` date DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `remark` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `devices`
--

INSERT INTO `devices` (`id`, `testingcomp_id`, `producer`, `measure`, `supplier`, `device_type`, `barcode`, `name`, `year_built`, `year_purchase`, `price`, `registration_no`, `intern_no`, `working_place`, `first_registration`, `created`, `modified`, `deleted`, `active`, `user_id`, `examiner_id`, `remark`) VALUES
(100, 1, 'Krautkrämer', 'Charakterisierung und Größenabschätzung von Anzeigen (Echohöhe)', '', 'Ultraschallgeräte', 0, 'USM 25S', 2006, 2008, '0.00', 'X8L974e', '145', 'Hettstedt', '2008-06-22', '2017-08-31 10:37:57', '2021-08-25 15:15:56', 0, 1, 1, 0, 'Lager');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `devices_testingmethods_devices`
--

DROP TABLE IF EXISTS `devices_testingmethods_devices`;
CREATE TABLE `devices_testingmethods_devices` (
  `testingmethod_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `devices_testingmethods_devices`
--

INSERT INTO `devices_testingmethods_devices` (`testingmethod_id`, `device_id`) VALUES
(9, 2),
(8, 3),
(8, 4),
(8, 5),
(11, 6),
(9, 12),
(9, 13),
(9, 14),
(8, 15),
(8, 16),
(11, 17),
(11, 18),
(8, 19),
(11, 21),
(9, 22),
(9, 23),
(9, 24),
(9, 25),
(8, 26),
(9, 27),
(9, 28),
(9, 29),
(8, 30),
(9, 31),
(9, 32),
(9, 33),
(9, 34),
(9, 35),
(9, 36),
(9, 37),
(9, 38),
(9, 39),
(9, 40),
(9, 41),
(9, 42),
(11, 43),
(11, 44),
(11, 45),
(11, 46),
(8, 47),
(9, 48),
(8, 49),
(8, 50),
(8, 51),
(8, 52),
(8, 53),
(8, 54),
(8, 55),
(8, 56),
(8, 57),
(8, 58),
(8, 59),
(8, 60),
(8, 61),
(9, 62),
(9, 63),
(9, 64),
(9, 65),
(7, 66),
(9, 67),
(9, 68),
(9, 69),
(9, 70),
(9, 71),
(9, 73),
(9, 74),
(9, 75),
(7, 76),
(9, 77),
(9, 78),
(9, 79),
(9, 80),
(6, 81),
(9, 82),
(9, 83),
(6, 84),
(9, 85),
(6, 86),
(6, 87),
(9, 88),
(9, 89),
(6, 90),
(7, 93),
(6, 94),
(6, 92),
(6, 95),
(6, 96),
(6, 97),
(7, 98),
(7, 99),
(3, 100),
(9, 101),
(3, 102),
(3, 103),
(7, 104),
(3, 105),
(9, 106),
(9, 107),
(9, 108),
(7, 109),
(9, 110),
(7, 111),
(9, 112),
(9, 113),
(9, 114),
(9, 115),
(7, 116),
(9, 117),
(8, 118),
(9, 119),
(7, 120),
(9, 121),
(9, 122),
(12, 123),
(9, 124),
(9, 125),
(9, 126),
(9, 127),
(8, 128),
(7, 129),
(9, 130),
(8, 131),
(8, 132),
(8, 133),
(9, 135),
(9, 134),
(9, 136),
(9, 137),
(7, 138),
(8, 139),
(8, 140),
(8, 141),
(9, 142),
(7, 143),
(8, 144),
(8, 145),
(8, 146),
(7, 147),
(8, 149),
(9, 154),
(9, 155),
(9, 156),
(9, 157),
(9, 158),
(9, 159),
(9, 161),
(9, 162),
(9, 163),
(13, 164),
(7, 165),
(8, 166),
(9, 167),
(9, 169),
(9, 170),
(9, 171),
(9, 172),
(9, 173),
(9, 174),
(9, 176),
(9, 177),
(9, 178),
(9, 179),
(9, 181),
(9, 182),
(9, 183),
(9, 184),
(9, 185),
(6, 186),
(9, 187),
(7, 188),
(7, 190),
(7, 191),
(7, 192),
(3, 193),
(6, 194),
(11, 195),
(7, 196),
(7, 197),
(7, 199),
(7, 200),
(7, 201),
(7, 202),
(7, 203),
(7, 204),
(7, 206),
(7, 208),
(7, 209),
(7, 210),
(8, 212),
(8, 213),
(9, 214),
(9, 215),
(9, 216),
(9, 217),
(9, 219),
(9, 220),
(5, 11),
(5, 10),
(5, 9),
(5, 8),
(5, 7),
(11, 221),
(3, 222),
(3, 223),
(3, 224),
(3, 225),
(9, 226),
(9, 227),
(9, 228),
(9, 230),
(9, 231),
(6, 232),
(3, 233),
(3, 234),
(3, 236),
(9, 238),
(7, 241),
(5, 243),
(5, 244),
(9, 245),
(9, 246),
(8, 248),
(8, 249),
(8, 250),
(8, 251),
(9, 253),
(9, 254),
(9, 255),
(9, 256),
(9, 257),
(9, 258),
(3, 259),
(8, 260),
(6, 261),
(9, 263),
(6, 264),
(9, 265),
(9, 266),
(9, 267),
(9, 268),
(9, 269),
(9, 271),
(8, 272),
(12, 273),
(9, 276),
(9, 277),
(9, 278),
(3, 279),
(8, 280),
(8, 281),
(8, 282),
(9, 283),
(9, 284),
(7, 285),
(8, 287),
(6, 288),
(9, 289),
(7, 292),
(6, 293),
(6, 294),
(8, 295),
(8, 297),
(9, 298),
(3, 299),
(3, 300),
(3, 301),
(3, 302),
(3, 304),
(3, 305),
(6, 306),
(1, 307),
(1, 308),
(9, 309),
(9, 311),
(9, 312),
(1, 313),
(9, 314),
(9, 315),
(9, 316),
(9, 317),
(9, 318),
(9, 319),
(9, 321),
(9, 322),
(8, 323),
(1, 325),
(8, 327),
(7, 328),
(8, 329),
(9, 330),
(9, 331),
(9, 332),
(3, 334),
(8, 335),
(8, 336),
(9, 337),
(8, 338),
(8, 339),
(9, 340),
(9, 341),
(9, 342),
(7, 344),
(9, 345),
(9, 346),
(7, 347),
(9, 351),
(7, 352),
(9, 353),
(9, 354),
(9, 355),
(3, 356),
(3, 357),
(9, 358),
(9, 359),
(7, 360),
(7, 361),
(8, 362),
(6, 363),
(8, 364),
(8, 365),
(9, 366),
(9, 367),
(6, 368),
(7, 369),
(9, 370),
(9, 371),
(7, 372),
(9, 373),
(9, 374),
(7, 375),
(7, 376),
(9, 377),
(7, 378),
(7, 379),
(7, 380),
(9, 381),
(9, 382),
(9, 383),
(7, 384),
(7, 385),
(9, 386),
(8, 387),
(7, 388),
(7, 389),
(7, 390),
(7, 391),
(7, 392),
(7, 393),
(9, 394),
(7, 395),
(9, 396),
(9, 397),
(9, 398),
(9, 399),
(9, 400),
(7, 401),
(7, 402),
(7, 404),
(7, 405),
(9, 406),
(9, 407),
(7, 408),
(7, 409),
(9, 410),
(7, 411),
(8, 412),
(9, 413),
(9, 414),
(9, 415),
(3, 416),
(7, 417),
(9, 418),
(7, 419),
(9, 420),
(9, 421),
(9, 422),
(9, 423),
(9, 424),
(9, 425),
(8, 426),
(7, 427),
(7, 428),
(7, 429),
(7, 430),
(9, 431),
(9, 432),
(9, 433),
(7, 434),
(6, 435),
(6, 436),
(7, 437),
(7, 438),
(7, 439),
(7, 440),
(7, 445),
(9, 446),
(7, 447),
(9, 448),
(9, 449),
(9, 450),
(9, 451),
(7, 452),
(9, 455),
(9, 457),
(9, 458),
(7, 459),
(7, 460),
(9, 461),
(9, 462),
(7, 465),
(7, 466),
(7, 467),
(7, 468),
(7, 469),
(7, 470),
(7, 471),
(7, 472),
(7, 473),
(7, 474),
(7, 475),
(7, 476),
(7, 477),
(7, 478),
(7, 479),
(7, 480),
(7, 481),
(7, 482),
(7, 483),
(7, 484),
(7, 485),
(7, 486),
(7, 487),
(7, 488),
(7, 489),
(7, 490),
(7, 493),
(7, 494),
(7, 495),
(7, 496),
(7, 497),
(7, 498),
(7, 499),
(7, 500),
(7, 501),
(7, 502),
(7, 503),
(7, 504),
(7, 505),
(7, 506),
(7, 507),
(7, 508),
(7, 509),
(7, 510),
(7, 511),
(7, 512),
(7, 513),
(7, 514),
(7, 515),
(7, 516),
(7, 517),
(7, 518),
(7, 519),
(7, 520),
(9, 521),
(8, 522),
(8, 523),
(9, 524),
(8, 525),
(9, 526),
(9, 527),
(9, 528),
(9, 529),
(9, 530),
(9, 531),
(9, 532),
(9, 533),
(9, 534),
(9, 535),
(7, 536),
(9, 537),
(9, 538),
(9, 539),
(8, 540),
(9, 541),
(3, 542),
(3, 543),
(3, 544),
(3, 545),
(7, 546),
(7, 547),
(7, 548),
(7, 549),
(7, 550),
(9, 551),
(9, 552),
(9, 553),
(9, 554),
(9, 555),
(9, 556),
(9, 557),
(9, 558),
(8, 559),
(7, 560),
(6, 561),
(6, 562),
(9, 564),
(9, 565),
(9, 566),
(3, 567),
(3, 568),
(7, 569),
(9, 570),
(8, 572),
(8, 573),
(8, 574),
(6, 575),
(6, 576),
(6, 577),
(1, 578),
(3, 579),
(3, 580),
(3, 581),
(6, 582),
(9, 583),
(9, 584),
(9, 585),
(9, 586),
(6, 587),
(6, 591),
(9, 592),
(9, 593),
(9, 594),
(9, 595),
(9, 596),
(9, 597),
(9, 598),
(9, 599),
(8, 600),
(3, 601),
(8, 602),
(7, 603),
(7, 604),
(7, 605),
(7, 606),
(7, 607),
(7, 608),
(7, 609),
(7, 610),
(7, 612),
(7, 613),
(7, 614),
(7, 615),
(7, 616),
(7, 617),
(7, 618),
(14, 619),
(9, 621),
(9, 622),
(9, 623),
(7, 625),
(7, 626),
(7, 627),
(9, 628),
(9, 629),
(9, 630),
(9, 631),
(9, 632),
(9, 633),
(9, 634),
(9, 635),
(9, 636),
(9, 637),
(9, 638),
(9, 639),
(9, 640),
(9, 641),
(9, 642),
(9, 643),
(9, 644),
(9, 645),
(9, 646),
(9, 647),
(9, 648),
(9, 649),
(7, 650),
(33, 491),
(7, 651),
(7, 652),
(33, 464),
(11, 654),
(33, 653),
(11, 211),
(11, 324),
(11, 348),
(11, 349),
(11, 350),
(11, 655),
(11, 239),
(11, 240),
(33, 291),
(6, 563),
(1, 151),
(1, 152),
(3, 656),
(32, 571),
(3, 657),
(32, 333),
(3, 658),
(3, 659),
(32, 588),
(30, 589),
(1, 153),
(7, 611),
(7, 620),
(11, 660),
(0, 20),
(9, 661),
(9, 662),
(9, 663),
(6, 664),
(9, 665),
(9, 666),
(6, 667),
(5, 668),
(9, 669),
(1, 670),
(9, 671),
(9, 672),
(9, 673),
(9, 674),
(9, 675),
(8, 676),
(8, 678),
(8, 679),
(8, 680),
(8, 681),
(8, 682),
(8, 683),
(9, 684),
(44, 343),
(9, 685),
(9, 686),
(44, 72),
(33, 205),
(33, 274),
(33, 403),
(44, 235),
(44, 687),
(9, 688),
(7, 689),
(8, 690),
(8, 691),
(9, 692),
(9, 693),
(9, 694),
(9, 695),
(9, 696),
(9, 697),
(1, 698),
(11, 699),
(11, 700),
(11, 701),
(9, 702),
(9, 703),
(11, 704),
(11, 705),
(11, 706),
(11, 707),
(7, 708),
(6, 91),
(7, 710),
(1, 712),
(7, 713),
(8, 714),
(7, 715),
(33, 709),
(33, 711),
(6, 716),
(33, 441),
(33, 442),
(33, 443),
(33, 444),
(33, 453),
(33, 454),
(33, 456),
(7, 717),
(7, 718),
(7, 719),
(7, 720),
(7, 721),
(7, 722),
(7, 723),
(7, 724),
(7, 725),
(7, 726),
(3, 727),
(9, 728),
(8, 729),
(7, 730),
(6, 731),
(11, 732),
(11, 733),
(0, 1),
(9, 734),
(3, 735),
(8, 736),
(8, 737),
(8, 738),
(8, 739),
(9, 740),
(7, 741),
(33, 492),
(9, 742),
(9, 743),
(8, 744),
(9, 745),
(8, 746),
(44, 237),
(1, 747),
(30, 748),
(6, 749),
(0, 750),
(8, 751),
(8, 752),
(8, 753),
(8, 754),
(8, 755),
(9, 756),
(9, 757),
(7, 758),
(9, 759),
(8, 760),
(9, 761),
(9, 762),
(9, 763),
(7, 764),
(8, 765),
(9, 766),
(30, 767),
(9, 768),
(8, 769),
(9, 770),
(8, 771),
(8, 772),
(8, 773),
(9, 774),
(8, 775),
(8, 776),
(8, 777),
(0, 778),
(3, 779),
(9, 780),
(3, 781),
(7, 782),
(9, 783),
(9, 784),
(9, 785),
(9, 786),
(9, 787),
(7, 788),
(6, 789),
(7, 790),
(8, 792),
(8, 793),
(8, 794),
(8, 795),
(9, 796),
(4, 797),
(8, 798),
(8, 799),
(7, 800),
(7, 801),
(7, 802),
(7, 803),
(7, 804),
(3, 808),
(3, 809),
(9, 810),
(8, 811),
(8, 812),
(8, 813),
(8, 814),
(7, 816),
(6, 818),
(8, 819),
(6, 820),
(9, 821),
(9, 822),
(9, 823),
(9, 824),
(6, 825),
(9, 826),
(9, 827),
(9, 828),
(9, 829),
(9, 831),
(7, 807),
(7, 290),
(9, 832),
(9, 833),
(9, 834),
(9, 835),
(44, 830),
(33, 463),
(9, 836),
(12, 838),
(9, 839),
(3, 840),
(3, 841),
(9, 842),
(9, 843),
(9, 844),
(9, 845),
(9, 846),
(9, 847),
(9, 848),
(9, 849),
(9, 850),
(9, 851),
(9, 852),
(9, 853),
(9, 854),
(8, 855),
(8, 856),
(8, 857),
(7, 858),
(7, 859),
(9, 860),
(9, 861),
(9, 862),
(9, 863),
(9, 864),
(9, 865),
(9, 866),
(9, 867),
(8, 868),
(8, 869),
(8, 870),
(8, 871),
(8, 872),
(8, 873),
(8, 874),
(8, 875),
(8, 876),
(3, 877),
(9, 878),
(9, 879),
(6, 590),
(9, 880),
(9, 881),
(9, 882),
(11, 806),
(11, 817),
(11, 815),
(11, 805),
(11, 791),
(9, 883),
(9, 885),
(9, 886),
(9, 887),
(8, 888),
(9, 889),
(9, 890),
(8, 891),
(8, 892),
(7, 893),
(8, 894),
(8, 895),
(8, 896),
(8, 897),
(8, 898),
(8, 899),
(9, 900),
(8, 901),
(8, 902),
(8, 903),
(9, 904),
(9, 905),
(8, 677),
(4, 906),
(9, 907),
(8, 908),
(6, 909),
(8, 911),
(8, 912),
(9, 913),
(8, 914),
(9, 915),
(8, 916),
(9, 917),
(9, 918),
(7, 919),
(0, 910),
(44, 884),
(44, 837),
(44, 624),
(8, 920),
(8, 921),
(9, 922),
(3, 923),
(3, 924),
(3, 925),
(9, 926),
(9, 927),
(9, 928),
(3, 929),
(3, 930),
(3, 931),
(3, 932),
(3, 933),
(9, 934),
(9, 935),
(8, 936),
(33, 148),
(33, 303),
(33, 150),
(33, 160),
(33, 168),
(33, 175),
(33, 180),
(33, 189),
(33, 198),
(33, 207),
(33, 218),
(33, 229),
(33, 242),
(33, 247),
(33, 252),
(33, 262),
(33, 270),
(33, 275),
(33, 286),
(33, 296),
(33, 310),
(33, 320),
(33, 326),
(6, 937),
(8, 938),
(8, 939),
(8, 940),
(9, 941),
(9, 942),
(9, 943),
(9, 944),
(7, 945),
(9, 946),
(9, 947),
(9, 948);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `device_certificates`
--

DROP TABLE IF EXISTS `device_certificates`;
CREATE TABLE `device_certificates` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `certificat` varchar(200) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `measuring_range` varchar(255) NOT NULL,
  `third_part` varchar(200) NOT NULL,
  `first_registration` date DEFAULT NULL,
  `first_certification` int(11) NOT NULL DEFAULT '0',
  `renewal_in_year` int(11) NOT NULL,
  `recertification_in_year` decimal(11,2) NOT NULL,
  `extern_intern` tinyint(2) NOT NULL DEFAULT '0',
  `adress` text NOT NULL,
  `file` tinyint(2) NOT NULL DEFAULT '0',
  `horizon` decimal(10,0) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `cert_parameter` varchar(200) NOT NULL,
  `metrological_traceability` varchar(200) NOT NULL,
  `specification` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `device_certificates`
--

INSERT INTO `device_certificates` (`id`, `device_id`, `testingcomp_id`, `certificat`, `price`, `measuring_range`, `third_part`, `first_registration`, `first_certification`, `renewal_in_year`, `recertification_in_year`, `extern_intern`, `adress`, `file`, `horizon`, `created`, `modified`, `deleted`, `active`, `user_id`, `cert_parameter`, `metrological_traceability`, `specification`) VALUES
(1, 100, 1, 'Überprüfung DGUV Vorschrift 3', '0.00', '', '', '2021-03-03', 0, 0, '1.00', 0, '', 1, '1', '2021-08-26 08:20:27', '2021-08-26 08:20:27', 0, 1, 1, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `device_certificate_datas`
--

DROP TABLE IF EXISTS `device_certificate_datas`;
CREATE TABLE `device_certificate_datas` (
  `id` int(11) NOT NULL,
  `specification` varchar(200) NOT NULL,
  `metrological_traceability` varchar(200) NOT NULL,
  `cert_parameter` varchar(200) NOT NULL,
  `device_id` int(11) NOT NULL,
  `device_certificate_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `certificat` varchar(255) NOT NULL,
  `first_certification` tinyint(4) NOT NULL DEFAULT '0',
  `certified` tinyint(4) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `measuring_range` varchar(255) NOT NULL,
  `renewal_in_year` int(11) NOT NULL,
  `recertification_in_year` decimal(11,2) NOT NULL,
  `horizon` int(11) NOT NULL DEFAULT '0',
  `first_registration` date DEFAULT NULL,
  `certified_date` date DEFAULT NULL,
  `certified_file` varchar(200) NOT NULL,
  `apply_for_recertification` tinyint(4) NOT NULL DEFAULT '0',
  `extern_intern` tinyint(4) NOT NULL DEFAULT '0',
  `adress` text NOT NULL,
  `file` tinyint(2) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `device_certificate_datas`
--

INSERT INTO `device_certificate_datas` (`id`, `specification`, `metrological_traceability`, `cert_parameter`, `device_id`, `device_certificate_id`, `testingcomp_id`, `certificat`, `first_certification`, `certified`, `price`, `measuring_range`, `renewal_in_year`, `recertification_in_year`, `horizon`, `first_registration`, `certified_date`, `certified_file`, `apply_for_recertification`, `extern_intern`, `adress`, `file`, `created`, `modified`, `deleted`, `active`, `user_id`) VALUES
(1, '', '', '', 100, 1, 1, 'Überprüfung DGUV Vorschrift 3', 0, 1, '0.00', '', 0, '1.00', 1, '2021-03-03', '2021-03-03', '100_1_1_1629958836.pdf', 0, 0, '', 1, '2021-08-26 08:20:27', '2021-08-26 08:20:36', 0, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `device_datas`
--

DROP TABLE IF EXISTS `device_datas`;
CREATE TABLE `device_datas` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `testingmethod` varchar(20) NOT NULL,
  `first_certification` tinyint(4) NOT NULL DEFAULT '0',
  `certified` tinyint(4) NOT NULL,
  `recertification_in_year` int(11) NOT NULL,
  `horizon` int(11) NOT NULL DEFAULT '0',
  `first_registration` date DEFAULT NULL,
  `certified_date` date DEFAULT NULL,
  `certified_file` varchar(200) NOT NULL,
  `apply_for_recertification` tinyint(4) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `device_testingmethods`
--

DROP TABLE IF EXISTS `device_testingmethods`;
CREATE TABLE `device_testingmethods` (
  `id` int(11) NOT NULL,
  `verfahren` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `active` tinyint(5) NOT NULL,
  `testingmethod_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `device_testingmethods`
--

INSERT INTO `device_testingmethods` (`id`, `verfahren`, `created`, `modified`, `active`, `testingmethod_id`, `deleted`) VALUES
(1, 'VT-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 5, 0),
(2, 'SK-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 6, 0),
(3, 'UT-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 32, 0),
(4, 'ET-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 20, 0),
(5, 'PT-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 31, 0),
(6, 'MT-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 30, 0),
(7, 'RT-Prüfung', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 33, 0),
(8, 'IT-Produkte', '2017-04-12 00:00:00', '2017-04-12 00:00:00', 1, 0, 0),
(9, 'Allgemein', '2017-05-03 09:52:26', '2017-05-03 09:52:26', 1, 44, 0),
(10, 'Testkat', '2017-08-22 16:06:53', '2017-08-22 16:06:53', 1, 0, 0),
(11, 'Strahlenschutz', '2017-08-23 16:03:46', '2017-08-23 16:03:46', 1, 0, 0),
(12, 'Härteprüfung', '2017-08-31 11:59:47', '2017-08-31 11:59:47', 1, 0, 0),
(13, 'Schichtdicke', '2017-09-05 12:13:31', '2017-09-05 12:13:31', 1, 0, 0),
(14, 'Vakuumprüfung', '2017-10-05 15:12:33', '2017-10-05 15:12:33', 1, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `examinerfiles`
--

DROP TABLE IF EXISTS `examinerfiles`;
CREATE TABLE `examinerfiles` (
  `id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `originally_filename` varchar(250) NOT NULL,
  `file_size` int(11) NOT NULL,
  `basename` varchar(200) NOT NULL,
  `description` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `examinerfiles`
--

INSERT INTO `examinerfiles` (`id`, `testingcomp_id`, `examiner_id`, `parent_id`, `name`, `originally_filename`, `file_size`, `basename`, `description`, `user_id`, `created`, `modified`, `deleted`) VALUES
(1, 1, 0, 19, '0_19_1525254179.pdf', 'Zeugnis RT1.pdf', 210431, '19/documents/', 'ZeugnisRT1', 78, '2018-05-02 11:42:59', '2018-05-02 11:42:59', 0),
(2, 1, 0, 2, '0_2_1526564865.pdf', 'Strahlenschutz (2018-2023).pdf', 226785, '2/documents/', 'Strahlenschutz20182023', 78, '2018-05-17 15:47:45', '2018-07-10 14:36:47', 1),
(3, 1, 0, 9, '0_9_1528109435.pdf', 'Zeugnis Zusatzmodul UT 1 M1 (04 2018 - 04 2023).pdf', 184642, '9/documents/', 'ZeugnisZusatzmodulUT1M1042018042023', 78, '2018-06-04 12:50:35', '2018-06-04 12:50:35', 0),
(4, 1, 0, 3, '0_3_1528281490.pdf', 'Fachkraft für Arbeitssicherheit.pdf', 141547, '3/documents/', 'FachkraftfrArbeitssicherheit', 78, '2018-06-06 12:38:10', '2018-06-06 12:38:10', 0),
(5, 1, 0, 3, '0_3_1528281624.pdf', 'Zeugnis Ingenieur.pdf', 110212, '3/documents/', 'ZeugnisIngenieur', 78, '2018-06-06 12:40:24', '2018-06-06 12:40:24', 0),
(6, 1, 0, 26, '0_26_1528282307.pdf', '20180606130050301.pdf', 164074, '26/documents/', '20180606130050301', 78, '2018-06-06 12:51:47', '2018-06-06 12:51:47', 0),
(7, 1, 0, 4, '0_4_1528288452.pdf', 'Zeugnis Kfz-Schlosser.pdf', 85237, '4/documents/', 'ZeugnisKfzSchlosser', 78, '2018-06-06 14:34:12', '2018-06-06 14:34:12', 0),
(8, 1, 0, 6, '0_6_1528359910.pdf', 'Zeugnis Werkstoffprüfer.pdf', 77783, '6/documents/', 'ZeugnisWerkstoffprfer', 78, '2018-06-07 10:25:10', '2018-06-07 10:25:10', 0),
(9, 1, 0, 22, '0_22_1528366464.pdf', 'Zeugnis Bürokaufmann.pdf', 57684, '22/documents/', 'ZeugnisBrokaufmann', 78, '2018-06-07 12:14:24', '2018-06-07 12:14:24', 0),
(10, 1, 0, 9, '0_9_1528369332.pdf', 'Facharbeiter.pdf', 49284, '9/documents/', 'Facharbeiter', 78, '2018-06-07 13:02:12', '2018-06-07 13:02:12', 0),
(11, 1, 0, 21, '0_21_1528970130.pdf', '3_8_8_1527575888(1).pdf', 115048, '21/documents/', '38815275758881', 81, '2018-06-14 11:55:30', '2018-06-14 11:55:30', 0),
(12, 1, 0, 21, '0_21_1528970448.pdf', '3_8_8_1527575888(1).pdf', 115048, '21/documents/', '38815275758881', 81, '2018-06-14 12:00:48', '2018-06-14 12:00:48', 0),
(13, 1, 0, 21, '0_21_1528970664.pdf', 'report_(24).pdf', 116863, '21/documents/', 'report24', 81, '2018-06-14 12:04:24', '2018-06-14 12:04:24', 0),
(14, 1, 0, 21, '0_21_1528970778.pdf', 'Order(10).pdf', 68273, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/21/documents/', 'Order10', 81, '2018-06-14 12:06:18', '2018-06-14 12:06:18', 0),
(15, 1, 0, 2, '0_2_1530173860.pdf', '21_45_45_1525343527.pdf', 330826, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/2/documents/', '2145451525343527', 81, '2018-06-28 10:17:40', '2018-06-28 10:19:00', 1),
(16, 1, 0, 29, '0_29_1535972894.pdf', 'FA für Rohrleitungselemente.pdf', 37352, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/29/documents/', 'FAfrRohrleitungselemente', 78, '2018-09-03 13:08:14', '2018-09-03 13:08:14', 0),
(17, 1, 0, 29, '0_29_1535973021.pdf', 'Facharbeiter-Urkunde.pdf', 215199, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/29/documents/', 'FacharbeiterUrkunde', 78, '2018-09-03 13:10:21', '2018-09-03 13:10:21', 0),
(18, 1, 0, 12, '0_12_1536909308.pdf', 'Bestellung Befähigte Person Druckbehälter.pdf', 262348, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/12/documents/', 'BestellungBefhigtePersonDruckbehlter', 78, '2018-09-14 09:15:08', '2018-09-14 09:15:08', 0),
(19, 1, 0, 3, '0_3_1542707305.pdf', 'Begutachter Werkstoffe und Werkstofftechnik (2018).pdf', 171932, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/3/documents/', 'BegutachterWerkstoffeundWerkstofftechnik2018', 78, '2018-11-20 10:48:25', '2018-11-20 10:48:25', 0),
(20, 1, 0, 3, '0_3_1542707442.pdf', 'Fachbegutachter Prüflaboratorien.pdf', 288147, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/3/documents/', 'FachbegutachterPrflaboratorien', 78, '2018-11-20 10:50:42', '2018-11-20 10:50:42', 0),
(21, 1, 0, 30, '0_30_1542710186.pdf', 'Fachkundebescheinigung Strahlenschutz.pdf', 35098, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/30/documents/', 'FachkundebescheinigungStrahlenschutz', 78, '2018-11-20 11:36:26', '2018-11-20 11:36:26', 0),
(22, 1, 0, 28, '0_28_1543319241.pdf', 'Eheurkunde.pdf', 22877, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/28/documents/', 'Eheurkunde', 78, '2018-11-27 12:47:21', '2018-11-27 12:47:21', 0),
(23, 1, 0, 28, '0_28_1543319253.pdf', 'Bescheinigung über Namensänderung.pdf', 29474, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/28/documents/', 'BescheinigungberNamensnderung', 78, '2018-11-27 12:47:33', '2018-11-27 12:47:33', 0),
(24, 1, 0, 9, '0_9_1543320416.pdf', 'Zeugnis MT1 (Bahn - Rezerti 2018).pdf', 231322, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/9/documents/', 'ZeugnisMT1BahnRezerti2018', 78, '2018-11-27 13:06:56', '2018-11-27 13:06:56', 0),
(25, 1, 0, 8, '0_8_1543321027.pdf', 'Teilnahmebescheinigung VT1 (Bahn).pdf', 203573, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/8/documents/', 'TeilnahmebescheinigungVT1Bahn', 78, '2018-11-27 13:17:07', '2018-11-27 13:17:07', 0),
(26, 1, 0, 14, '0_14_1544444965.pdf', 'Ingenieurzeugnis Seite 1.pdf', 121851, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/14/documents/', 'IngenieurzeugnisSeite1', 78, '2018-12-10 13:29:25', '2018-12-10 13:29:25', 0),
(27, 1, 0, 14, '0_14_1544444986.pdf', 'Ingenieurzeugnis Seite 2.pdf', 157369, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/14/documents/', 'IngenieurzeugnisSeite2', 78, '2018-12-10 13:29:46', '2018-12-10 13:29:46', 0),
(28, 1, 0, 14, '0_14_1544445107.pdf', 'Gleichwertigkeit von Bildungsabschlüssen.pdf', 183804, '/homepages/27/d244115160/htdocs/qmsystems/mps/data_mbq/examiners/14/documents/', 'GleichwertigkeitvonBildungsabschlssen', 78, '2018-12-10 13:31:47', '2018-12-10 13:31:47', 0),
(29, 1, 0, 33, '0_33_1551092937.pdf', 'Zeugnis ZfP Grundlagenkenntnisse Stufe 3.pdf', 271969, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/33/documents/', 'ZeugnisZfPGrundlagenkenntnisseStufe3', 78, '2019-02-25 12:08:57', '2019-02-25 12:08:57', 0),
(30, 1, 0, 33, '0_33_1551095261.pdf', 'Fachkundebescheinigung Strahlenschutz.pdf', 401580, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/33/documents/', 'FachkundebescheinigungStrahlenschutz', 78, '2019-02-25 12:47:41', '2019-02-25 12:47:41', 0),
(31, 1, 0, 35, '0_35_1552396538.pdf', 'Bedienerausweis Hebebühnen.pdf', 382791, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/35/documents/', 'BedienerausweisHebebhnen', 78, '2019-03-12 14:15:38', '2019-03-12 14:15:38', 0),
(32, 1, 0, 33, '0_33_1558351750.pdf', 'Zeugnis PT2.pdf', 311043, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/33/documents/', 'ZeugnisPT2', 78, '2019-05-20 13:29:10', '2019-05-20 13:29:10', 0),
(33, 1, 0, 33, '0_33_1558351758.pdf', 'Zeugnis UT3.pdf', 306035, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/33/documents/', 'ZeugnisUT3', 78, '2019-05-20 13:29:18', '2019-05-20 13:29:18', 0),
(34, 1, 0, 2, '0_2_1558430324.pdf', 'Zeugnis UT1 (Bahn).pdf', 282663, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/2/documents/', 'ZeugnisUT1Bahn', 78, '2019-05-21 11:18:44', '2019-05-21 11:18:44', 0),
(35, 1, 0, 7, '0_7_1559813615.pdf', 'Zeugnis Rezertifizierung MT1 Bahn (2019).pdf', 187643, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/7/documents/', 'ZeugnisRezertifizierungMT1Bahn2019', 78, '2019-06-06 11:33:35', '2019-06-06 11:33:35', 0),
(36, 1, 0, 28, '0_28_1563445630.pdf', 'Fachkundebescheinigung Strahlenschutz.pdf', 396012, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/28/documents/', 'FachkundebescheinigungStrahlenschutz', 78, '2019-07-18 12:27:10', '2019-07-18 12:27:10', 0),
(37, 1, 0, 25, '0_25_1563445779.pdf', 'Fachkundebescheinigung Strahlenschutz.pdf', 391453, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/25/documents/', 'FachkundebescheinigungStrahlenschutz', 78, '2019-07-18 12:29:39', '2019-07-18 12:29:39', 0),
(38, 1, 0, 29, '0_29_1563445912.pdf', 'Fachkundebescheinigung Strahlenschutz.pdf', 391453, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'FachkundebescheinigungStrahlenschutz', 78, '2019-07-18 12:31:52', '2019-07-18 12:31:52', 0),
(39, 1, 0, 29, '0_29_1564998329.pdf', 'Zeugnis ET2.pdf', 41753, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisET2', 78, '2019-08-05 11:45:29', '2019-08-05 11:45:29', 0),
(40, 1, 0, 29, '0_29_1564998340.pdf', 'Zeugnis MT2.pdf', 267596, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisMT2', 78, '2019-08-05 11:45:40', '2019-08-05 11:45:40', 0),
(41, 1, 0, 29, '0_29_1564998352.pdf', 'Zeugnis PT2.pdf', 259402, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisPT2', 78, '2019-08-05 11:45:52', '2019-08-05 11:45:52', 0),
(42, 1, 0, 29, '0_29_1564998366.pdf', 'Zeugnis RT2 (F).pdf', 315604, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisRT2F', 78, '2019-08-05 11:46:06', '2019-08-05 11:46:06', 0),
(43, 1, 0, 29, '0_29_1564998382.pdf', 'Zeugnis UT2.pdf', 275087, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisUT2', 78, '2019-08-05 11:46:22', '2019-08-05 11:46:22', 0),
(44, 1, 0, 29, '0_29_1564998395.pdf', 'Zeugnis VT2.pdf', 268718, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/29/documents/', 'ZeugnisVT2', 78, '2019-08-05 11:46:35', '2019-08-05 11:46:35', 0),
(45, 1, 0, 38, '0_38_1576147285.pdf', 'Berufsausbildung Industriemechaniker (1995).pdf', 268002, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/38/documents/', 'BerufsausbildungIndustriemechaniker1995', 78, '2019-12-12 11:41:25', '2019-12-12 11:41:25', 0),
(46, 1, 0, 33, '0_33_1576231927.pdf', 'Befähigte Person für Druckbehälter.pdf', 285963, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/33/documents/', 'BefhigtePersonfrDruckbehlter', 78, '2019-12-13 11:12:07', '2019-12-13 11:12:07', 0),
(47, 1, 0, 8, '0_8_1576235142.pdf', 'Zeugnis UT1 M1 (Bahn 2019).pdf', 249829, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/8/documents/', 'ZeugnisUT1M1Bahn2019', 78, '2019-12-13 12:05:42', '2019-12-13 12:05:42', 0),
(48, 1, 0, 8, '0_8_1576235248.pdf', 'Teilnahmebescheinigung UT2 (Bahn 2019).pdf', 184879, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/8/documents/', 'TeilnahmebescheinigungUT2Bahn2019', 78, '2019-12-13 12:07:28', '2019-12-13 12:07:28', 0),
(49, 1, 0, 2, '0_2_1576236969.pdf', 'Zeugnis MT1 (Bahn 2019).pdf', 285804, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/2/documents/', 'ZeugnisMT1Bahn2019', 78, '2019-12-13 12:36:09', '2019-12-13 12:36:09', 0),
(50, 1, 0, 25, '0_25_1576237870.pdf', 'Zeugnis MT 2.pdf', 312771, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mbq/examiners/25/documents/', 'ZeugnisMT2', 78, '2019-12-13 12:51:10', '2019-12-13 12:51:10', 0),
(51, 1, 0, 3, '0_3_1581586238.pdf', 'Teilnahmebescheinigung Auditierung von Managementsystemen.pdf', 339354, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/3/documents/', 'TeilnahmebescheinigungAuditierungvonManagementsystemen', 78, '2020-02-13 10:30:38', '2020-02-13 10:30:38', 0),
(52, 1, 0, 40, '0_40_1584358962.pdf', 'Urkunde Master of Science.pdf', 250177, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/40/documents/', 'UrkundeMasterofScience', 78, '2020-03-16 12:42:42', '2020-03-16 12:42:42', 0),
(53, 1, 0, 40, '0_40_1584358970.pdf', 'Zertifikat Technisches Englisch.pdf', 472555, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/40/documents/', 'ZertifikatTechnischesEnglisch', 78, '2020-03-16 12:42:50', '2020-03-16 12:42:50', 0),
(54, 1, 0, 40, '0_40_1584358979.pdf', 'Zeugnis Masterprüfung.pdf', 525906, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/40/documents/', 'ZeugnisMasterprfung', 78, '2020-03-16 12:42:59', '2020-03-16 12:42:59', 0),
(55, 1, 0, 2, '0_2_1585558112.pdf', 'Zeugnis UT1 M1 (Bahn).pdf', 261963, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/2/documents/', 'ZeugnisUT1M1Bahn', 78, '2020-03-30 10:48:32', '2020-03-30 10:48:32', 0),
(56, 1, 0, 33, '0_33_1592400501.pdf', 'Diplom.pdf', 186941, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/33/documents/', 'Diplom', 78, '2020-06-17 15:28:21', '2020-06-17 15:28:21', 0),
(57, 1, 0, 41, '0_41_1592555786.pdf', 'Zeugnis ET 1.pdf', 435917, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisET1', 78, '2020-06-19 10:36:26', '2020-06-19 10:36:26', 0),
(58, 1, 0, 41, '0_41_1592555798.pdf', 'Zeugnis ET 2.pdf', 435099, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisET2', 78, '2020-06-19 10:36:38', '2020-06-19 10:36:38', 0),
(59, 1, 0, 41, '0_41_1592555810.pdf', 'Zeugnis MT 2.pdf', 445604, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisMT2', 78, '2020-06-19 10:36:50', '2020-06-19 10:36:50', 0),
(60, 1, 0, 41, '0_41_1592555818.pdf', 'Zeugnis PT 2.pdf', 426446, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisPT2', 78, '2020-06-19 10:36:58', '2020-06-19 10:36:58', 0),
(61, 1, 0, 41, '0_41_1592555828.pdf', 'Zeugnis RT 1.pdf', 429650, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisRT1', 78, '2020-06-19 10:37:08', '2020-06-19 10:37:08', 0),
(62, 1, 0, 41, '0_41_1592555836.pdf', 'Zeugnis RT 2.pdf', 790157, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisRT2', 78, '2020-06-19 10:37:16', '2020-06-19 10:37:16', 0),
(63, 1, 0, 41, '0_41_1592555846.pdf', 'Zeugnis UT 1.pdf', 431531, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisUT1', 78, '2020-06-19 10:37:26', '2020-06-19 10:37:26', 0),
(64, 1, 0, 41, '0_41_1592555854.pdf', 'Zeugnis UT 2.pdf', 433422, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisUT2', 78, '2020-06-19 10:37:34', '2020-06-19 10:37:34', 0),
(65, 1, 0, 41, '0_41_1592555866.pdf', 'Zeugnis VT.pdf', 314503, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/41/documents/', 'ZeugnisVT', 78, '2020-06-19 10:37:46', '2020-06-19 10:37:46', 0),
(66, 1, 0, 40, '0_40_1595325445.pdf', 'Teilnahmebestätigung Office 2016 Excel Aufbau.pdf', 231894, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/40/documents/', 'TeilnahmebesttigungOffice2016ExcelAufbau', 78, '2020-07-21 11:57:25', '2020-07-21 11:57:25', 0),
(67, 1, 0, 43, '0_43_1595329677.pdf', 'Teilnahmebescheinigung MT3.pdf', 328238, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/43/documents/', 'TeilnahmebescheinigungMT3', 78, '2020-07-21 13:07:57', '2020-07-21 13:07:57', 0),
(68, 1, 0, 43, '0_43_1595329686.pdf', 'Zeugnis MT3.pdf', 308775, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/43/documents/', 'ZeugnisMT3', 78, '2020-07-21 13:08:06', '2020-07-21 13:08:06', 0),
(69, 1, 0, 43, '0_43_1595330827.pdf', 'Zeugnis MT2.pdf', 317504, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/43/documents/', 'ZeugnisMT2', 78, '2020-07-21 13:27:07', '2020-07-21 13:27:07', 0),
(70, 1, 0, 43, '0_43_1595330838.pdf', 'Zeugnis UT2.pdf', 321301, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/43/documents/', 'ZeugnisUT2', 78, '2020-07-21 13:27:18', '2020-07-21 13:27:18', 0),
(71, 1, 0, 43, '0_43_1595330847.pdf', 'Zeugnis VT2.pdf', 316726, '/kunden/homepages/27/d244115160/htdocs/apps_data/data_mps/examiners/43/documents/', 'ZeugnisVT2', 78, '2020-07-21 13:27:27', '2020-07-21 13:27:27', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `examinermonitoringfiles`
--

DROP TABLE IF EXISTS `examinermonitoringfiles`;
CREATE TABLE `examinermonitoringfiles` (
  `id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `originally_filename` varchar(250) NOT NULL,
  `file_size` int(11) NOT NULL,
  `basename` varchar(200) NOT NULL,
  `description` varchar(250) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `examiners`
--

DROP TABLE IF EXISTS `examiners`;
CREATE TABLE `examiners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `working_place` varchar(255) DEFAULT NULL,
  `testingcomp_id` int(11) DEFAULT NULL,
  `da_no` int(11) NOT NULL DEFAULT '0',
  `date_of_birth` date DEFAULT NULL,
  `place` varchar(200) NOT NULL,
  `street` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `device_id` int(11) NOT NULL,
  `job` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `examiners`
--

INSERT INTO `examiners` (`id`, `name`, `first_name`, `working_place`, `testingcomp_id`, `da_no`, `date_of_birth`, `place`, `street`, `created`, `modified`, `deleted`, `active`, `device_id`, `job`) VALUES
(46, 'Prüfer', 'Test', 'DS', 1, 0, '1981-08-25', 'Ort', 'Straße', '2021-08-25 15:17:05', '2021-08-25 15:17:05', 0, 1, 0, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `examiner_monitorings`
--

DROP TABLE IF EXISTS `examiner_monitorings`;
CREATE TABLE `examiner_monitorings` (
  `id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `certificat` varchar(200) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `measuring_range` varchar(255) NOT NULL,
  `third_part` varchar(200) NOT NULL,
  `first_registration` date DEFAULT NULL,
  `first_certification` int(11) NOT NULL DEFAULT '0',
  `renewal_in_year` int(11) NOT NULL,
  `recertification_in_year` decimal(11,2) NOT NULL,
  `extern_intern` tinyint(4) NOT NULL DEFAULT '0',
  `adress` text NOT NULL,
  `file` tinyint(2) NOT NULL DEFAULT '0',
  `horizon` decimal(10,0) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL,
  `specification` varchar(255) NOT NULL,
  `metrological_traceability` varchar(255) NOT NULL,
  `cert_parameter` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `examiner_monitorings`
--

INSERT INTO `examiner_monitorings` (`id`, `examiner_id`, `testingcomp_id`, `certificat`, `price`, `measuring_range`, `third_part`, `first_registration`, `first_certification`, `renewal_in_year`, `recertification_in_year`, `extern_intern`, `adress`, `file`, `horizon`, `created`, `modified`, `deleted`, `active`, `user_id`, `specification`, `metrological_traceability`, `cert_parameter`) VALUES
(1, 46, 1, 'AMD G20', '0.00', '', '', '2021-08-02', 0, 0, '1.00', 0, '', 1, '1', '2021-08-26 08:18:12', '2021-08-26 08:18:12', 0, 1, 1, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `examiner_monitoring_datas`
--

DROP TABLE IF EXISTS `examiner_monitoring_datas`;
CREATE TABLE `examiner_monitoring_datas` (
  `id` int(11) NOT NULL,
  `examiner_id` int(11) NOT NULL,
  `examiner_monitoring_id` int(11) NOT NULL,
  `testingcomp_id` int(11) NOT NULL,
  `certificat` varchar(255) NOT NULL,
  `first_certification` tinyint(4) NOT NULL DEFAULT '0',
  `certified` tinyint(4) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `measuring_range` varchar(255) NOT NULL,
  `renewal_in_year` int(11) NOT NULL,
  `recertification_in_year` decimal(11,2) NOT NULL,
  `horizon` int(11) NOT NULL DEFAULT '0',
  `first_registration` date DEFAULT NULL,
  `certified_date` date DEFAULT NULL,
  `certified_file` varchar(200) NOT NULL,
  `apply_for_recertification` tinyint(4) NOT NULL DEFAULT '0',
  `extern_intern` tinyint(4) NOT NULL DEFAULT '0',
  `adress` text NOT NULL,
  `file` tinyint(2) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specification` varchar(255) NOT NULL,
  `metrological_traceability` varchar(255) NOT NULL,
  `cert_parameter` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `examiner_monitoring_datas`
--

INSERT INTO `examiner_monitoring_datas` (`id`, `examiner_id`, `examiner_monitoring_id`, `testingcomp_id`, `certificat`, `first_certification`, `certified`, `price`, `measuring_range`, `renewal_in_year`, `recertification_in_year`, `horizon`, `first_registration`, `certified_date`, `certified_file`, `apply_for_recertification`, `extern_intern`, `adress`, `file`, `created`, `modified`, `deleted`, `active`, `user_id`, `specification`, `metrological_traceability`, `cert_parameter`) VALUES
(1, 46, 1, 1, 'AMD G20', 0, 1, '0.00', '', 0, '1.00', 1, '2021-08-02', '2021-08-02', '46_1_1_0_64115500_1629958702_6127322e9c88b.pdf', 0, 0, '', 1, '2021-08-26 08:18:12', '2021-08-26 08:18:22', 0, 1, 1, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `testinginstructions`
--

DROP TABLE IF EXISTS `testinginstructions`;
CREATE TABLE `testinginstructions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `testingmethod_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET latin1,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `testinginstructions`
--

INSERT INTO `testinginstructions` (`id`, `name`, `testingmethod_id`, `user_id`, `description`, `created`, `modified`, `status`, `deleted`) VALUES
(1, 'Testanweisung', 1, 1, 'Prüfanweisung für eine Durchstrahlungsprüfung', '2020-09-29 11:33:14', '2021-08-26 10:00:28', 1, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `testinginstructions_data`
--

DROP TABLE IF EXISTS `testinginstructions_data`;
CREATE TABLE `testinginstructions_data` (
  `id` int(11) NOT NULL,
  `testinginstruction_id` int(11) NOT NULL,
  `model` varchar(255) CHARACTER SET latin1 NOT NULL,
  `field` varchar(255) CHARACTER SET latin1 NOT NULL,
  `description` text CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1 NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  `max` varchar(255) CHARACTER SET latin1 NOT NULL,
  `min` varchar(255) CHARACTER SET latin1 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Spalte type: 0=Hinweis, 1=Wert kann übernommen werden, 2=Wert exakt übernehmen';

--
-- Daten für Tabelle `testinginstructions_data`
--

INSERT INTO `testinginstructions_data` (`id`, `testinginstruction_id`, `model`, `field`, `description`, `value`, `type`, `remark`, `max`, `min`) VALUES
(1, 1, 'ReportMtGenerally', 'examination_object', 'Erzeugnisform/Bezeichnung des Bauteils', 'Blech/Schweißnaht/Nummer ist zu dokumentieren', 0, 'Die Bezeichnung oder Beschreibung des Prüfobjektes eintragen', '', ''),
(2, 1, 'ReportMtSpecific', 'examitantion_surface', 'Oberflächenzustand', 'glatte saubere Oberfläche, frei von Schweißspritzern', 0, 'Der Wert dieses Feldes kann abweichen, in der Bedeutung aber nicht', '', ''),
(3, 1, 'ReportMtGenerally', 'material', 'Werkstoff', 'S235JR', 2, 'Pflichtfeld es kann nur der angegebene Werkstoff verwendet werden', '', ''),
(4, 1, 'ReportMtSpecific', 'examitantion_surface', 'Fertigungszustand/Anlass der Prüfung', 'endbearbeitet/Endabnahme', 0, '', '', ''),
(5, 1, 'ReportMtGenerally', 'scope_of_testing', 'Prüfumfang', '100%', 2, 'Pflichtfeld, bitte keinen anderen Wert als den angegebenen verwenden', '', ''),
(18, 1, 'ReportMtSpecific', 'examination_temperatur', 'Die Temparatur des Prüfobjektes', 'Minimum 5C°, Maximum 25C°', 1, '', '', ''),
(7, 1, 'ReportMtSpecific', 'test_execution', 'Prüfung nach', 'DIN EN ISO 17638 (2017-03)', 2, 'Es sind keine anderen Normen zugelassen', '', ''),
(8, 1, 'ReportMtSpecific', 'test_assessment', 'Bewertung nach', 'DIN EN ISO 23278 (2015-06)', 2, 'Es ist nur diese Bewertungsnorm zugelassen', '', ''),
(9, 1, 'ReportMtSpecific', 'evaluation_acc_to_step', 'Zulässigkeitsgrenze', '2', 2, 'Bitte keine andere Zulässigkeitsgrenze verwenden', '', ''),
(10, 1, 'ReportMtEvaluation', 'indication_length', 'Registriergrenze(n) Test Änderung', 'linienartige Anzeigen: l > 1,5 mm, nichtlinienartige Anzeigen: d > 3,0 mm', 0, 'In Linie angeordnete, unterbrochenne Anzeigen werden als eine einzige Anzeige angesehen, wenn der Abstand kleiner als die Länge der kleineren daneben liegenden Anzeige', '', ''),
(11, 1, 'ReportMtEvaluation', 'distance_of_indication', 'Zulässigkeitsgrenze(n)', 'linienartige Anzeigen: 1,5 mm nichtlinienartige Anzeigen: 3,0 mm', 0, 'Hinweis zur Fehlerbeschreibung in der Auswertung', '', ''),
(13, 1, 'ReportMtGenerally', 'welding_process', 'Schweißverfahren nur WIG zugelassen', 'WIG', 2, '', '', ''),
(21, 21, 'ReportRtGenerally', 'material', 'nicht 39', 'St 38', 2, '', '', ''),
(20, 1, 'ReportMtGenerally', 'place_of_test', 'Nur hier', 'Hettstedt', 2, '', '', ''),
(22, 1, 'ReportRtGenerally', 'examination_area', 'SN+WEZ', 'SN+WEZ', 0, '', '', ''),
(23, 1, 'ReportRtSpecific', 'focal_spot_size', '3x3mm', '3x3mm', 0, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `testingmethods`
--

DROP TABLE IF EXISTS `testingmethods`;
CREATE TABLE `testingmethods` (
  `id` int(11) NOT NULL,
  `value` varchar(10) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `verfahren` varchar(100) DEFAULT NULL,
  `invoice` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `allow_children` tinyint(1) NOT NULL DEFAULT '0',
  `version` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `testingmethods`
--

INSERT INTO `testingmethods` (`id`, `value`, `name`, `verfahren`, `invoice`, `enabled`, `allow_children`, `version`) VALUES
(1, 'rt', 'RT', 'Durchstrahlungsprüfung ', 'rt', 1, 0, 3),
(5, 'mt', 'MT', 'Magnetpulverprüfung', 'mt', 1, 0, 3),
(6, 'pt', 'PT', 'Farbeindringprüfung', 'pt', 1, 0, 2),
(32, 'utwd', 'UTWD', 'Ultraschallprüfung Wandstärke', 'utwd', 1, 0, 1),
(20, 'ut', 'UT', 'Ultraschallprüfung', 'ut', 1, 0, 3),
(31, 'ht', 'HT', 'Härteprüfung', 'ht', 1, 0, 2),
(30, 'rtwd', 'RTWD', 'Durchstrahlungsprüfung Wandstärke', 'rtwd', 1, 0, 3),
(34, 'vt', 'VT', 'Visuelle Prüfung', 'vt', 1, 0, 3),
(37, 'lt', 'LT', 'Vakuumprüfung', 'lt', 1, 0, 2),
(38, 'ptasme', 'PTASME', 'Farbeindringprüfung ASME', 'ptasme', 1, 0, 1),
(41, 'rtasme', 'RTASME', 'Durchstrahlungsprüfung ASME', 'rtasme', 1, 0, 1),
(44, 'pmi', 'PMI', 'Verwechslungsprüfung', 'pmi', 1, 0, 3);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `certifcatefiles`
--
ALTER TABLE `certifcatefiles`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `certificate_datas`
--
ALTER TABLE `certificate_datas`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `device_certificates`
--
ALTER TABLE `device_certificates`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `device_certificate_datas`
--
ALTER TABLE `device_certificate_datas`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `device_testingmethods`
--
ALTER TABLE `device_testingmethods`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `examinerfiles`
--
ALTER TABLE `examinerfiles`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `examinermonitoringfiles`
--
ALTER TABLE `examinermonitoringfiles`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `examiners`
--
ALTER TABLE `examiners`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `examiner_monitorings`
--
ALTER TABLE `examiner_monitorings`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `examiner_monitoring_datas`
--
ALTER TABLE `examiner_monitoring_datas`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `testinginstructions`
--
ALTER TABLE `testinginstructions`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `testinginstructions_data`
--
ALTER TABLE `testinginstructions_data`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `testingmethods`
--
ALTER TABLE `testingmethods`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `certifcatefiles`
--
ALTER TABLE `certifcatefiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `certificate_datas`
--
ALTER TABLE `certificate_datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT für Tabelle `device_certificates`
--
ALTER TABLE `device_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `device_certificate_datas`
--
ALTER TABLE `device_certificate_datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `device_testingmethods`
--
ALTER TABLE `device_testingmethods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT für Tabelle `examinerfiles`
--
ALTER TABLE `examinerfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT für Tabelle `examinermonitoringfiles`
--
ALTER TABLE `examinermonitoringfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `examiners`
--
ALTER TABLE `examiners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT für Tabelle `examiner_monitorings`
--
ALTER TABLE `examiner_monitorings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `examiner_monitoring_datas`
--
ALTER TABLE `examiner_monitoring_datas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `testinginstructions`
--
ALTER TABLE `testinginstructions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `testinginstructions_data`
--
ALTER TABLE `testinginstructions_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT für Tabelle `testingmethods`
--
ALTER TABLE `testingmethods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
