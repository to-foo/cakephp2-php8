SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `acos`;
CREATE TABLE IF NOT EXISTS `acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=575 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `acos`
--

-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Erstellungszeit: 05. Mrz 2021 um 07:26
-- Server-Version: 5.7.26
-- PHP-Version: 7.3.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `mps_progress`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `acos`
--

DROP TABLE IF EXISTS `acos`;
CREATE TABLE IF NOT EXISTS `acos` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=586 DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `acos`
--

INSERT INTO `acos` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(1, NULL, NULL, NULL, 'ROOT', 1, 1158),
(2, 1, NULL, NULL, 'Topprojects', 2, 31),
(3, 2, NULL, NULL, 'index', 3, 4),
(4, 2, NULL, NULL, 'view', 5, 6),
(5, 2, NULL, NULL, 'add', 7, 8),
(6, 2, NULL, NULL, 'edit', 9, 10),
(7, 2, NULL, NULL, 'delete', 11, 12),
(8, 1, NULL, NULL, 'Dropdowns', 32, 67),
(9, 8, NULL, NULL, 'index', 33, 34),
(10, 8, NULL, NULL, 'edit', 35, 36),
(11, 8, NULL, NULL, 'dropdownindex', 37, 38),
(12, 8, NULL, NULL, 'linking', 39, 40),
(13, 8, NULL, NULL, 'dellinking', 41, 42),
(14, 8, NULL, NULL, 'dropdownedit', 43, 44),
(15, 8, NULL, NULL, 'dropdownadd', 45, 46),
(16, 8, NULL, NULL, 'dropdowndelete', 47, 48),
(17, 1, NULL, NULL, 'Orders', 68, 93),
(18, 17, NULL, NULL, 'index', 69, 70),
(19, 17, NULL, NULL, 'overview', 71, 72),
(20, 17, NULL, NULL, 'reports', 73, 74),
(21, 17, NULL, NULL, 'status', 75, 76),
(22, 17, NULL, NULL, 'search', 77, 78),
(23, 17, NULL, NULL, 'quicksearch', 79, 80),
(24, 17, NULL, NULL, 'view', 81, 82),
(25, 17, NULL, NULL, 'select', 83, 84),
(26, 17, NULL, NULL, 'create', 85, 86),
(27, 17, NULL, NULL, 'add', 87, 88),
(28, 17, NULL, NULL, 'edit', 89, 90),
(29, 17, NULL, NULL, 'delete', 91, 92),
(30, 1, NULL, NULL, 'Qualifications', 94, 111),
(31, 30, NULL, NULL, 'index', 95, 96),
(32, 30, NULL, NULL, 'view', 97, 98),
(33, 30, NULL, NULL, 'add', 99, 100),
(34, 30, NULL, NULL, 'addforexaminierer', 101, 102),
(35, 30, NULL, NULL, 'editforexaminierer', 103, 104),
(36, 30, NULL, NULL, 'deleteforexaminierer', 105, 106),
(37, 30, NULL, NULL, 'edit', 107, 108),
(38, 30, NULL, NULL, 'delete', 109, 110),
(39, 1, NULL, NULL, 'Reportnumbers', 112, 279),
(40, 39, NULL, NULL, 'index', 113, 114),
(41, 39, NULL, NULL, 'show', 115, 116),
(42, 39, NULL, NULL, 'search', 117, 118),
(43, 39, NULL, NULL, 'view', 119, 120),
(44, 39, NULL, NULL, 'add', 121, 122),
(45, 39, NULL, NULL, 'edit', 123, 124),
(46, 39, NULL, NULL, 'duplicat', 125, 126),
(47, 39, NULL, NULL, 'editevalution', 127, 128),
(48, 39, NULL, NULL, 'duplicatevalution', 129, 130),
(49, 39, NULL, NULL, 'pdf', 131, 132),
(50, 39, NULL, NULL, 'images', 133, 134),
(51, 39, NULL, NULL, 'imagediscription', 135, 136),
(52, 39, NULL, NULL, 'files', 137, 138),
(53, 39, NULL, NULL, 'delfile', 139, 140),
(54, 39, NULL, NULL, 'status1', 141, 142),
(55, 39, NULL, NULL, 'status2', 143, 144),
(56, 39, NULL, NULL, 'status', 145, 146),
(57, 39, NULL, NULL, 'delete', 147, 148),
(58, 39, NULL, NULL, 'deleteevalution', 149, 150),
(59, 39, NULL, NULL, 'weldassistent', 151, 152),
(60, 39, NULL, NULL, 'editable', 153, 154),
(61, 39, NULL, NULL, 'editableUp', 155, 156),
(62, 39, NULL, NULL, 'history', 157, 158),
(63, 39, NULL, NULL, 'settings', 159, 160),
(64, 1, NULL, NULL, 'Reports', 280, 293),
(65, 64, NULL, NULL, 'index', 281, 282),
(66, 64, NULL, NULL, 'view', 283, 284),
(67, 64, NULL, NULL, 'add', 285, 286),
(68, 64, NULL, NULL, 'edit', 287, 288),
(69, 64, NULL, NULL, 'delete', 289, 290),
(70, 1, NULL, NULL, 'Rolls', 294, 305),
(71, 70, NULL, NULL, 'index', 295, 296),
(72, 70, NULL, NULL, 'view', 297, 298),
(73, 70, NULL, NULL, 'add', 299, 300),
(74, 70, NULL, NULL, 'edit', 301, 302),
(75, 70, NULL, NULL, 'delete', 303, 304),
(77, 1, NULL, NULL, 'Specialcharacters', 308, 319),
(78, 77, NULL, NULL, 'index', 309, 310),
(79, 77, NULL, NULL, 'add', 311, 312),
(80, 77, NULL, NULL, 'edit', 313, 314),
(81, 77, NULL, NULL, 'delete', 315, 316),
(82, 77, NULL, NULL, 'listing', 317, 318),
(83, 1, NULL, NULL, 'Testingcomps', 320, 337),
(84, 83, NULL, NULL, 'index', 321, 322),
(85, 83, NULL, NULL, 'view', 323, 324),
(86, 83, NULL, NULL, 'add', 325, 326),
(87, 83, NULL, NULL, 'edit', 327, 328),
(88, 83, NULL, NULL, 'delete', 329, 330),
(89, 1, NULL, NULL, 'Testingmethods', 338, 351),
(90, 89, NULL, NULL, 'index', 339, 340),
(91, 89, NULL, NULL, 'view', 341, 342),
(92, 89, NULL, NULL, 'add', 343, 344),
(93, 89, NULL, NULL, 'edit', 345, 346),
(94, 89, NULL, NULL, 'delete', 347, 348),
(95, 89, NULL, NULL, 'listing', 349, 350),
(96, 2, NULL, NULL, 'settings', 13, 14),
(97, 1, NULL, NULL, 'Users', 352, 377),
(98, 97, NULL, NULL, 'login', 353, 354),
(99, 97, NULL, NULL, 'loggedin', 355, 356),
(100, 97, NULL, NULL, 'logout', 357, 358),
(101, 97, NULL, NULL, 'index', 359, 360),
(102, 97, NULL, NULL, 'view', 361, 362),
(103, 97, NULL, NULL, 'add', 363, 364),
(104, 97, NULL, NULL, 'edit', 365, 366),
(105, 97, NULL, NULL, 'delete', 367, 368),
(106, 1, NULL, NULL, 'Invoices', 378, 415),
(107, 106, NULL, NULL, 'overview', 379, 380),
(108, 106, NULL, NULL, 'invoice', 381, 382),
(109, 106, NULL, NULL, 'invoicedata', 383, 384),
(110, 106, NULL, NULL, 'create', 385, 386),
(111, 106, NULL, NULL, 'add', 387, 388),
(112, 106, NULL, NULL, 'number', 389, 390),
(113, 106, NULL, NULL, 'date', 391, 392),
(114, 106, NULL, NULL, 'editadditional', 393, 394),
(115, 106, NULL, NULL, 'editstandard', 395, 396),
(116, 106, NULL, NULL, 'deleteadditional', 397, 398),
(117, 106, NULL, NULL, 'save', 399, 400),
(118, 106, NULL, NULL, 'waitings', 401, 402),
(119, 106, NULL, NULL, 'change', 403, 404),
(120, 106, NULL, NULL, 'remark', 405, 406),
(121, 106, NULL, NULL, 'workreason', 407, 408),
(122, 106, NULL, NULL, 'waitingtest', 409, 410),
(123, 106, NULL, NULL, 'export', 411, 412),
(124, 1, NULL, NULL, 'Rolls', 416, 417),
(126, 125, NULL, NULL, 'index', 419, 420),
(127, 125, NULL, NULL, 'settings', 421, 422),
(131, 1, NULL, NULL, 'Authorizes', 430, 433),
(132, 131, NULL, NULL, 'index', 431, 432),
(133, 39, NULL, NULL, 'getfile', 161, 162),
(134, 39, NULL, NULL, 'quicksearch', 163, 164),
(135, 39, NULL, NULL, 'addchild', 165, 166),
(136, 39, NULL, NULL, 'removechild', 167, 168),
(137, 39, NULL, NULL, 'setparent', 169, 170),
(138, 39, NULL, NULL, 'searchReportList', 171, 172),
(139, 39, NULL, NULL, 'printweldlabel', 173, 174),
(140, 97, NULL, NULL, 'redirectafterlogin', 369, 370),
(141, 83, NULL, NULL, 'setLogo', 331, 332),
(142, 1, NULL, NULL, 'Examiners', 434, 579),
(143, 142, NULL, NULL, 'index', 435, 436),
(144, 142, NULL, NULL, 'view', 437, 438),
(145, 142, NULL, NULL, 'add', 439, 440),
(146, 142, NULL, NULL, 'edit', 441, 442),
(147, 142, NULL, NULL, 'delete', 443, 444),
(148, 142, NULL, NULL, 'list_workload', 445, 446),
(149, 142, NULL, NULL, 'add_workload', 447, 448),
(150, 142, NULL, NULL, 'edit_workload', 449, 450),
(151, 142, NULL, NULL, 'delete_workload', 451, 452),
(152, 142, NULL, NULL, 'list_examiner_workload', 453, 454),
(153, 39, NULL, NULL, 'results', 175, 176),
(154, 2, NULL, NULL, 'search', 15, 16),
(155, 142, NULL, NULL, 'printworkload', 455, 456),
(156, 142, NULL, NULL, 'duplicate_workload', 457, 458),
(157, 1, NULL, NULL, 'Equipments', 580, 595),
(158, 1, NULL, NULL, 'EquipmentTypes', 596, 619),
(159, 157, NULL, NULL, 'index', 581, 582),
(160, 157, NULL, NULL, 'add', 583, 584),
(161, 157, NULL, NULL, 'edit', 585, 586),
(162, 157, NULL, NULL, 'view', 587, 588),
(163, 157, NULL, NULL, 'delete', 589, 590),
(164, 158, NULL, NULL, 'index', 597, 598),
(165, 158, NULL, NULL, 'view', 599, 600),
(166, 158, NULL, NULL, 'add', 601, 602),
(167, 158, NULL, NULL, 'edit', 603, 604),
(168, 158, NULL, NULL, 'delete', 605, 606),
(169, 158, NULL, NULL, 'show', 607, 608),
(170, 158, NULL, NULL, 'overview', 609, 610),
(171, 39, NULL, NULL, 'image', 177, 178),
(172, 106, NULL, NULL, 'printinvoice', 413, 414),
(173, 39, NULL, NULL, 'en1435', 179, 180),
(174, 1, NULL, NULL, 'Statistics', 620, 645),
(175, 174, NULL, NULL, 'index', 621, 622),
(176, 174, NULL, NULL, 'errors', 623, 624),
(177, 174, NULL, NULL, 'diagram', 625, 626),
(178, 174, NULL, NULL, 'one', 627, 628),
(179, 1, NULL, NULL, 'Dependencies', 646, 659),
(180, 179, NULL, NULL, 'index', 647, 648),
(181, 179, NULL, NULL, 'add', 649, 650),
(182, 179, NULL, NULL, 'edit', 651, 652),
(183, 179, NULL, NULL, 'delete', 653, 654),
(184, 179, NULL, NULL, 'get', 655, 656),
(185, 39, NULL, NULL, 'versionize', 181, 182),
(186, 179, NULL, NULL, 'overview', 657, 658),
(187, 1, NULL, NULL, 'Developments', 660, 691),
(188, 187, NULL, NULL, 'index', 661, 662),
(189, 187, NULL, NULL, 'overview', 663, 664),
(190, 187, NULL, NULL, 'advance', 665, 666),
(191, 187, NULL, NULL, 'result', 667, 668),
(192, 187, NULL, NULL, 'add', 669, 670),
(193, 187, NULL, NULL, 'edit', 671, 672),
(194, 187, NULL, NULL, 'create', 673, 674),
(195, 187, NULL, NULL, 'show', 675, 676),
(196, 187, NULL, NULL, 'diagramm', 677, 678),
(197, 39, NULL, NULL, 'editsession', 183, 184),
(198, 39, NULL, NULL, 'errors', 185, 186),
(199, 187, NULL, NULL, 'advanceadd', 679, 680),
(200, 187, NULL, NULL, 'advanceedit', 681, 682),
(201, 187, NULL, NULL, 'advancedel', 683, 684),
(202, 187, NULL, NULL, 'change', 685, 686),
(203, 187, NULL, NULL, 'orders', 687, 688),
(204, 1, NULL, NULL, 'Orders', 692, 723),
(205, 204, NULL, NULL, 'edit', 693, 694),
(206, 204, NULL, NULL, 'add', 695, 696),
(207, 187, NULL, NULL, 'orderdetails', 689, 690),
(208, 204, NULL, NULL, 'status', 697, 698),
(209, 97, NULL, NULL, 'password', 371, 372),
(210, 204, NULL, NULL, 'search', 699, 700),
(211, 204, NULL, NULL, 'view', 701, 702),
(212, 204, NULL, NULL, 'file', 703, 704),
(213, 204, NULL, NULL, 'files', 705, 706),
(214, 204, NULL, NULL, 'getfile', 707, 708),
(215, 204, NULL, NULL, 'delfile', 709, 710),
(216, 204, NULL, NULL, 'move', 711, 712),
(217, 39, NULL, NULL, 'printresult', 187, 188),
(218, 83, NULL, NULL, 'add', 333, 334),
(219, 142, NULL, NULL, 'overview', 459, 460),
(220, 142, NULL, NULL, 'zertificats', 461, 462),
(221, 142, NULL, NULL, 'visiontests', 463, 464),
(222, 39, NULL, NULL, 'videos', 189, 190),
(223, 39, NULL, NULL, 'video', 191, 192),
(224, 39, NULL, NULL, 'printqrcode', 193, 194),
(225, 39, NULL, NULL, 'showqrcode', 195, 196),
(226, 39, NULL, NULL, 'signExaminer', 197, 198),
(227, 39, NULL, NULL, 'getSign', 199, 200),
(228, 39, NULL, NULL, 'sign', 201, 202),
(229, 39, NULL, NULL, 'signSupervisor', 203, 204),
(230, 39, NULL, NULL, 'signThirdPart', 205, 206),
(231, 204, NULL, NULL, 'create', 713, 714),
(232, 204, NULL, NULL, 'delete', 715, 716),
(233, 2, NULL, NULL, 'status', 17, 18),
(234, 2, NULL, NULL, 'reopen', 19, 20),
(235, 2, NULL, NULL, 'deleted', 21, 22),
(236, 2, NULL, NULL, 'restore', 23, 24),
(237, 157, NULL, NULL, 'move', 591, 592),
(238, 158, NULL, NULL, 'move', 613, 614),
(239, 39, NULL, NULL, 'move', 207, 208),
(240, 2, NULL, NULL, 'quicksearch', 25, 26),
(241, 157, NULL, NULL, 'quicksearch', 593, 594),
(242, 158, NULL, NULL, 'quicksearch', 615, 616),
(243, 158, NULL, NULL, 'quicksearchtype', 617, 618),
(244, 2, NULL, NULL, 'subdevisions', 27, 28),
(245, 39, NULL, NULL, 'removesignExaminer', 209, 210),
(246, 39, NULL, NULL, 'removesignSupervisor', 211, 212),
(247, 39, NULL, NULL, 'removesignThirdPart', 213, 214),
(248, 39, NULL, NULL, 'removeSign', 215, 216),
(249, 39, NULL, NULL, 'testingAreas', 217, 218),
(250, 39, NULL, NULL, 'massActions', 219, 220),
(251, 39, NULL, NULL, 'save', 221, 222),
(252, 39, NULL, NULL, 'evaluation', 223, 224),
(253, 64, NULL, NULL, 'save', 291, 292),
(254, 2, NULL, NULL, 'quickreportsearch', 29, 30),
(255, 39, NULL, NULL, 'last_ten', 225, 226),
(256, 174, NULL, NULL, 'exportcsv', 629, 630),
(257, 174, NULL, NULL, 'exportpdf', 631, 632),
(258, 39, NULL, NULL, 'assignedReports', 227, 228),
(259, 39, NULL, NULL, 'repairs', 229, 230),
(260, 142, NULL, NULL, 'save', 465, 466),
(261, 142, NULL, NULL, 'geteyecheckfile', 467, 468),
(262, 142, NULL, NULL, 'eyecheckfile', 469, 470),
(263, 142, NULL, NULL, 'editeyecheck', 471, 472),
(264, 142, NULL, NULL, 'eyecheck', 473, 474),
(265, 142, NULL, NULL, 'eyechecks', 475, 476),
(266, 142, NULL, NULL, 'removecertificate', 477, 478),
(267, 142, NULL, NULL, 'newcertificate', 479, 480),
(268, 142, NULL, NULL, 'replacecertificate', 481, 482),
(269, 142, NULL, NULL, 'delcertificate', 483, 484),
(270, 142, NULL, NULL, 'delcertificatefile', 485, 486),
(271, 142, NULL, NULL, 'getcertificatefile', 487, 488),
(272, 142, NULL, NULL, 'certificatefile', 489, 490),
(273, 142, NULL, NULL, 'editcertificate', 491, 492),
(274, 142, NULL, NULL, 'certificate', 493, 494),
(275, 142, NULL, NULL, 'certificates', 495, 496),
(276, 142, NULL, NULL, 'quicksearch', 497, 498),
(277, 142, NULL, NULL, 'summary', 499, 500),
(278, 142, NULL, NULL, 'history', 501, 502),
(279, 142, NULL, NULL, 'neweyecheck', 503, 504),
(280, 142, NULL, NULL, 'deleyecheckfile', 505, 506),
(281, 142, NULL, NULL, 'replaceeyecheck', 507, 508),
(282, 142, NULL, NULL, 'email_certificate', 509, 510),
(283, 142, NULL, NULL, 'email_eyecheck', 511, 512),
(284, 1, NULL, NULL, 'Devices', 724, 789),
(285, 284, NULL, NULL, 'index', 725, 726),
(286, 284, NULL, NULL, 'overview', 727, 728),
(287, 284, NULL, NULL, 'view', 729, 730),
(288, 284, NULL, NULL, 'edit', 731, 732),
(289, 284, NULL, NULL, 'monitorings', 733, 734),
(290, 284, NULL, NULL, 'echecks', 735, 736),
(291, 284, NULL, NULL, 'add', 737, 738),
(292, 284, NULL, NULL, 'save', 739, 740),
(293, 284, NULL, NULL, 'duplicate', 741, 742),
(294, 284, NULL, NULL, 'quicksearch', 743, 744),
(295, 284, NULL, NULL, 'del', 745, 746),
(296, 284, NULL, NULL, 'barcode', 747, 748),
(297, 142, NULL, NULL, 'certificatesfiles', 513, 514),
(298, 142, NULL, NULL, 'certificatefilesdescription', 515, 516),
(299, 142, NULL, NULL, 'getcertificatefiles', 517, 518),
(300, 142, NULL, NULL, 'delcertificatefiles', 519, 520),
(301, 284, NULL, NULL, 'files', 749, 750),
(302, 142, NULL, NULL, 'eyecheckfiles', 521, 522),
(303, 142, NULL, NULL, 'eyecheckfilesdescription', 523, 524),
(304, 142, NULL, NULL, 'geteyecheckfiles', 525, 526),
(305, 142, NULL, NULL, 'deleyecheckfiles', 527, 528),
(306, 284, NULL, NULL, 'devicefilesdescription', 751, 752),
(307, 284, NULL, NULL, 'getfiles', 753, 754),
(308, 284, NULL, NULL, 'deldevicefiles', 755, 756),
(309, 284, NULL, NULL, 'monitoring', 757, 758),
(310, 284, NULL, NULL, 'editmonitoring', 759, 760),
(311, 284, NULL, NULL, 'editcertificate', 761, 762),
(312, 284, NULL, NULL, 'certificatefile', 763, 764),
(313, 284, NULL, NULL, 'getcertificatefile', 765, 766),
(314, 284, NULL, NULL, 'delcertificatefile', 767, 768),
(315, 284, NULL, NULL, 'addmonitoring', 769, 770),
(316, 284, NULL, NULL, 'summary', 771, 772),
(317, 284, NULL, NULL, 'email_certificate', 773, 774),
(318, 142, NULL, NULL, 'askcertification', 529, 530),
(319, 284, NULL, NULL, 'replacemonitoring', 775, 776),
(320, 284, NULL, NULL, 'delmonitoring', 777, 778),
(321, 39, NULL, NULL, 'enforce', 231, 232),
(322, 39, NULL, NULL, 'resetevaluation', 233, 234),
(323, 39, NULL, NULL, 'pdf_wkn', 235, 236),
(324, 1, NULL, NULL, 'Dump', 790, 793),
(325, 324, NULL, NULL, 'setdropdowntestingmethod', 791, 792),
(326, 39, NULL, NULL, 'refresh', 237, 238),
(327, 97, NULL, NULL, 'quicksearch', 373, 374),
(329, 97, NULL, NULL, 'save', 375, 376),
(330, 204, NULL, NULL, 'save', 717, 718),
(331, 83, NULL, NULL, 'search', 335, 336),
(332, 142, NULL, NULL, 'historyeyecheck', 531, 532),
(333, 142, NULL, NULL, 'singlesummary', 533, 534),
(334, 1, NULL, NULL, 'Documents', 794, 847),
(335, 334, NULL, NULL, 'index', 795, 796),
(336, 334, NULL, NULL, 'overview', 797, 798),
(337, 334, NULL, NULL, 'view', 799, 800),
(338, 334, NULL, NULL, 'edit', 801, 802),
(339, 334, NULL, NULL, 'monitorings', 803, 804),
(340, 334, NULL, NULL, 'add', 805, 806),
(341, 334, NULL, NULL, 'save', 807, 808),
(342, 334, NULL, NULL, 'quicksearch', 809, 810),
(343, 334, NULL, NULL, 'del', 811, 812),
(344, 334, NULL, NULL, 'files', 813, 814),
(345, 334, NULL, NULL, 'dokumentfilesdescription', 815, 816),
(346, 334, NULL, NULL, 'getfiles', 817, 818),
(347, 334, NULL, NULL, 'deldocumentfiles', 819, 820),
(348, 334, NULL, NULL, 'monitorings', 821, 822),
(349, 334, NULL, NULL, 'editmonitoring', 823, 824),
(350, 334, NULL, NULL, 'editcertificate', 825, 826),
(351, 334, NULL, NULL, 'certificatefile', 827, 828),
(352, 334, NULL, NULL, 'getcertificatefile', 829, 830),
(353, 334, NULL, NULL, 'delcertificatefile', 831, 832),
(354, 334, NULL, NULL, 'addmonitoring', 833, 834),
(355, 334, NULL, NULL, 'summary', 835, 836),
(356, 334, NULL, NULL, 'email_certificate', 837, 838),
(357, 334, NULL, NULL, 'replacemonitoring', 839, 840),
(358, 334, NULL, NULL, 'delmonitoring', 841, 842),
(359, 142, NULL, NULL, 'qualifications', 535, 536),
(360, 142, NULL, NULL, 'qualification', 537, 538),
(361, 284, NULL, NULL, 'pdf', 779, 780),
(362, 142, NULL, NULL, 'qualificationnew', 539, 540),
(363, 142, NULL, NULL, 'qualificationdel', 541, 542),
(364, 142, NULL, NULL, 'pdf', 543, 544),
(365, 1, NULL, NULL, 'DeviceTestingmethods', 848, 865),
(366, 365, NULL, NULL, 'index', 849, 850),
(367, 365, NULL, NULL, 'addd', 851, 852),
(368, 365, NULL, NULL, 'add', 853, 854),
(369, 365, NULL, NULL, 'delete', 855, 856),
(370, 365, NULL, NULL, 'overview', 857, 858),
(371, 365, NULL, NULL, 'edit', 859, 860),
(374, 284, NULL, NULL, 'pdfinv', 781, 782),
(375, 284, NULL, NULL, 'sessionstorage', 783, 784),
(376, 142, NULL, NULL, 'sessionstorage', 545, 546),
(377, 204, NULL, NULL, 'pdf', 719, 720),
(378, 284, NULL, NULL, 'search', 785, 786),
(379, 1, NULL, NULL, 'Externs', 866, 875),
(380, 379, NULL, NULL, 'incomming', 867, 868),
(381, 379, NULL, NULL, 'externreports', 869, 870),
(382, 284, NULL, NULL, 'autocomplete', 787, 788),
(383, 39, NULL, NULL, 'export', 239, 240),
(384, 39, NULL, NULL, 'upload', 241, 242),
(385, 39, NULL, NULL, 'signtransport', 243, 244),
(386, 379, NULL, NULL, 'index', 871, 872),
(387, 379, NULL, NULL, 'externreports', 873, 874),
(388, 142, NULL, NULL, 'search', 547, 548),
(389, 334, NULL, NULL, 'search', 843, 844),
(390, 142, NULL, NULL, 'addmonitoring', 549, 550),
(391, 142, NULL, NULL, 'replacemonitoring', 551, 552),
(392, 142, NULL, NULL, 'editmonitoring', 553, 554),
(393, 142, NULL, NULL, 'delmonitoring', 555, 556),
(394, 142, NULL, NULL, 'monitorings', 557, 558),
(395, 142, NULL, NULL, 'monitoringfile', 559, 560),
(396, 142, NULL, NULL, 'getmonitoringfile', 561, 562),
(397, 142, NULL, NULL, 'autocomplete', 563, 564),
(398, 334, NULL, NULL, 'autocomplete', 845, 846),
(399, 204, NULL, NULL, 'duplicat', 721, 722),
(400, 39, NULL, NULL, 'printresultcsv', 245, 246),
(401, 142, NULL, NULL, 'files', 565, 566),
(402, 142, NULL, NULL, 'getfiles', 567, 568),
(403, 142, NULL, NULL, 'getfiles', 569, 570),
(404, 39, NULL, NULL, 'printresultcsv', 247, 248),
(405, 142, NULL, NULL, 'pdfinv', 571, 572),
(406, 142, NULL, NULL, 'delexaminerfiles', 573, 574),
(407, 39, NULL, NULL, 'revision', 249, 250),
(408, 39, NULL, NULL, 'showevisions', 251, 252),
(409, 39, NULL, NULL, 'showrevisions', 253, 254),
(412, 39, NULL, NULL, 'printrevisions', 255, 256),
(413, 39, NULL, NULL, 'emailreport', 257, 258),
(414, 39, NULL, NULL, 'signFourPart', 259, 260),
(426, 1, NULL, NULL, 'Expeditings', 876, 891),
(427, 426, NULL, NULL, 'index', 877, 878),
(428, 426, NULL, NULL, 'add', 879, 880),
(429, 426, NULL, NULL, 'edit', 881, 882),
(430, 426, NULL, NULL, 'delete', 883, 884),
(431, 426, NULL, NULL, 'overview', 885, 886),
(432, 426, NULL, NULL, 'detail', 887, 888),
(433, 426, NULL, NULL, 'editdetail', 889, 890),
(434, 1, NULL, NULL, 'Cascades', 892, 901),
(435, 434, NULL, NULL, 'index', 893, 894),
(436, 434, NULL, NULL, 'add', 895, 896),
(437, 434, NULL, NULL, 'edit', 897, 898),
(438, 434, NULL, NULL, 'delete', 899, 900),
(439, 39, NULL, NULL, 'filediscription', 261, 262),
(440, 1, NULL, NULL, 'Searchings', 902, 907),
(441, 440, NULL, NULL, 'index', 903, 904),
(442, 440, NULL, NULL, 'search', 905, 906),
(443, 39, NULL, NULL, 'refreshrevision', 263, 264),
(444, 1, NULL, NULL, 'Welders', 908, 1081),
(445, 444, NULL, NULL, 'index', 909, 910),
(446, 444, NULL, NULL, 'add', 911, 912),
(447, 444, NULL, NULL, 'edit', 913, 914),
(448, 444, NULL, NULL, 'delete', 915, 916),
(449, 444, NULL, NULL, 'overview', 917, 918),
(450, 444, NULL, NULL, 'qualifications', 919, 920),
(451, 444, NULL, NULL, 'qualification', 921, 922),
(452, 444, NULL, NULL, 'neweyecheck', 923, 924),
(453, 444, NULL, NULL, 'pdf', 925, 926),
(454, 444, NULL, NULL, 'files', 927, 928),
(455, 444, NULL, NULL, 'qualificationnew', 929, 930),
(456, 444, NULL, NULL, 'certificates', 931, 932),
(457, 444, NULL, NULL, 'addmonitoring', 933, 934),
(458, 444, NULL, NULL, 'askcertification', 935, 936),
(459, 444, NULL, NULL, 'autocomplete', 937, 938),
(460, 444, NULL, NULL, 'certificatefile', 939, 940),
(461, 444, NULL, NULL, 'certificatefiledescription', 941, 942),
(462, 444, NULL, NULL, 'certificatefiledescriptiondelcertificate', 943, 944),
(463, 444, NULL, NULL, 'certificatefiledescriptiondelcertificatefiles', 945, 946),
(464, 444, NULL, NULL, 'deleyecheckfile', 947, 948),
(465, 444, NULL, NULL, 'editcertificate', 949, 950),
(466, 444, NULL, NULL, 'qualificationdel', 951, 952),
(467, 444, NULL, NULL, 'certificates', 953, 954),
(468, 444, NULL, NULL, 'certificate', 955, 956),
(469, 444, NULL, NULL, 'delcertificate', 957, 958),
(470, 444, NULL, NULL, 'delcertificatefile', 959, 960),
(471, 444, NULL, NULL, 'delcertificatefiles', 961, 962),
(472, 444, NULL, NULL, 'deleyecheckfiles', 963, 964),
(473, 444, NULL, NULL, 'delwelderfiles', 965, 966),
(474, 444, NULL, NULL, 'editeyecheck', 967, 968),
(475, 444, NULL, NULL, 'editmonitoring', 969, 970),
(476, 444, NULL, NULL, 'email_certificate', 971, 972),
(477, 444, NULL, NULL, 'email_eyecheck', 973, 974),
(478, 444, NULL, NULL, 'eyecheckfile', 975, 976),
(479, 444, NULL, NULL, 'eyecheckfiles', 977, 978),
(480, 444, NULL, NULL, 'eyecheckfilesdescription', 979, 980),
(481, 444, NULL, NULL, 'eyechecks', 981, 982),
(482, 444, NULL, NULL, 'files', 983, 984),
(483, 444, NULL, NULL, 'fileupload', 985, 986),
(484, 444, NULL, NULL, 'getcertificatefile', 987, 988),
(485, 444, NULL, NULL, 'getcertificatefiles', 989, 990),
(486, 444, NULL, NULL, 'geteyecheckfile', 991, 992),
(487, 444, NULL, NULL, 'geteyecheckfiles', 993, 994),
(488, 444, NULL, NULL, 'getfile', 995, 996),
(489, 444, NULL, NULL, 'getfiles', 997, 998),
(490, 444, NULL, NULL, 'history', 999, 1000),
(491, 444, NULL, NULL, 'historyeyecheck', 1001, 1002),
(492, 444, NULL, NULL, 'list_welder_workload', 1003, 1004),
(493, 444, NULL, NULL, 'list_workload', 1005, 1006),
(494, 444, NULL, NULL, 'monitoringfile', 1007, 1008),
(495, 444, NULL, NULL, 'monitorings', 1009, 1010),
(496, 444, NULL, NULL, 'neweyecheck', 1011, 1012),
(497, 444, NULL, NULL, 'pdfinv', 1013, 1014),
(498, 444, NULL, NULL, 'printworkload', 1015, 1016),
(499, 444, NULL, NULL, 'quicksearch', 1017, 1018),
(500, 444, NULL, NULL, 'removecertificate', 1019, 1020),
(501, 444, NULL, NULL, 'replacecertificate', 1021, 1022),
(502, 444, NULL, NULL, 'replaceeyecheck', 1023, 1024),
(503, 444, NULL, NULL, 'replacemonitoring', 1025, 1026),
(504, 444, NULL, NULL, 'save', 1027, 1028),
(505, 444, NULL, NULL, 'search', 1029, 1030),
(506, 444, NULL, NULL, 'sessionstorage', 1031, 1032),
(507, 444, NULL, NULL, 'single_eyecheck_summary', 1033, 1034),
(508, 444, NULL, NULL, 'singlesummary', 1035, 1036),
(509, 444, NULL, NULL, 'summary', 1037, 1038),
(510, 444, NULL, NULL, 'testgraph', 1039, 1040),
(511, 444, NULL, NULL, 'view', 1041, 1042),
(512, 444, NULL, NULL, 'visiontests', 1043, 1044),
(513, 444, NULL, NULL, 'editcertificate', 1045, 1046),
(514, 444, NULL, NULL, 'eycheck', 1047, 1048),
(515, 444, NULL, NULL, 'eyecheck', 1049, 1050),
(516, 444, NULL, NULL, 'certificatfiles', 1051, 1052),
(517, 444, NULL, NULL, 'certificatesfiles', 1053, 1054),
(518, 444, NULL, NULL, 'delmonitoring', 1055, 1056),
(519, 444, NULL, NULL, 'getmonitoringfile', 1057, 1058),
(520, 1, NULL, NULL, 'Weldingmethods', 1082, 1093),
(521, 520, NULL, NULL, 'index', 1083, 1084),
(522, 520, NULL, NULL, 'add', 1085, 1086),
(523, 520, NULL, NULL, 'edit', 1087, 1088),
(524, 520, NULL, NULL, 'delete', 1089, 1090),
(525, 520, NULL, NULL, 'view', 1091, 1092),
(526, 1, NULL, NULL, 'Searchings', 1094, 1111),
(527, 526, NULL, NULL, 'search', 1095, 1096),
(528, 526, NULL, NULL, 'auto', 1097, 1098),
(529, 526, NULL, NULL, 'update', 1099, 1100),
(530, 526, NULL, NULL, 'results', 1101, 1102),
(531, 526, NULL, NULL, 'insertdata', 1103, 1104),
(532, 1, NULL, NULL, 'Suppliers', 1112, 1115),
(533, 1, NULL, NULL, 'Expeditings', 1116, 1123),
(534, 533, NULL, NULL, 'index', 1117, 1118),
(535, 532, NULL, NULL, 'index', 1113, 1114),
(536, 533, NULL, NULL, 'detail', 1119, 1120),
(537, 533, NULL, NULL, 'shortview', 1121, 1122),
(538, 444, NULL, NULL, 'indexcomp', 1059, 1060),
(539, 444, NULL, NULL, 'files', 1061, 1062),
(540, 444, NULL, NULL, 'weldingcompinfo', 1063, 1064),
(541, 444, NULL, NULL, 'indexpart', 1065, 1066),
(542, 444, NULL, NULL, 'filesweldingcomp', 1067, 1068),
(543, 444, NULL, NULL, 'getweldercompfiles', 1069, 1070),
(544, 444, NULL, NULL, 'weldingcompfilesdescription', 1071, 1072),
(545, 39, NULL, NULL, 'modulchilddata', 265, 266),
(546, 444, NULL, NULL, 'setpicture', 1073, 1074),
(547, 444, NULL, NULL, 'welderfilesdescription', 1075, 1076),
(548, 444, NULL, NULL, 'getlasttests', 1077, 1078),
(549, 39, NULL, NULL, 'repair', 269, 270),
(550, 526, NULL, NULL, 'statistic', 1105, 1106),
(551, 39, NULL, NULL, 'exportallsigns', 271, 272),
(552, 526, NULL, NULL, 'pdf', 1107, 1108),
(553, 526, NULL, NULL, 'csv', 1109, 1110),
(554, 39, NULL, NULL, 'imagecount', 273, 274),
(555, 444, NULL, NULL, 'createweldertest', 1079, 1080),
(556, 39, NULL, NULL, 'testinstruction', 275, 276),
(557, 1, NULL, NULL, 'Testinginstructions', 1124, 1137),
(558, 557, NULL, NULL, 'index', 1125, 1126),
(559, 557, NULL, NULL, 'edit', 1127, 1128),
(560, 557, NULL, NULL, 'editdata', 1129, 1130),
(561, 557, NULL, NULL, 'adddata', 1131, 1132),
(562, 557, NULL, NULL, 'add', 1133, 1134),
(563, 557, NULL, NULL, 'reason', 1135, 1136),
(564, 8, NULL, NULL, 'master', 49, 50),
(565, 8, NULL, NULL, 'masteradd', 51, 52),
(566, 8, NULL, NULL, 'masteredit', 53, 54),
(567, 8, NULL, NULL, 'masteradddata', 55, 56),
(568, 8, NULL, NULL, 'mastereditdata', 57, 58),
(569, 142, NULL, NULL, 'sendoverview', 575, 576),
(570, 142, NULL, NULL, 'printoverview', 577, 578),
(571, 8, NULL, NULL, 'masterdependency', 59, 60),
(572, 8, NULL, NULL, 'editdependency', 61, 62),
(573, 8, NULL, NULL, 'deletedependency', 63, 64),
(574, 8, NULL, NULL, 'masterdeletedata', 65, 66),
(575, 1, NULL, NULL, 'Advances', 1138, 1157),
(576, 575, NULL, NULL, 'index', 1139, 1140),
(577, 575, NULL, NULL, 'json_scheme', 1141, 1142),
(578, 575, NULL, NULL, 'advance', 1143, 1144),
(579, 575, NULL, NULL, 'advance_delete', 1145, 1146),
(580, 575, NULL, NULL, 'advance_add', 1147, 1148),
(581, 575, NULL, NULL, 'advance_settings', 1149, 1150),
(582, 575, NULL, NULL, 'order_edit', 1151, 1152),
(583, 575, NULL, NULL, 'reload_statistic', 1153, 1154),
(584, 575, NULL, NULL, 'checklist', 1155, 1156),
(585, 39, NULL, NULL, 'collectpdf', 277, 278);

CREATE TABLE IF NOT EXISTS `testinginstructions_authorizations` (
  `topproject_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL DEFAULT '0',
  `testingmethod_id` int(11) NOT NULL DEFAULT '0',
  `testingstruction_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `testinginstructions_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testinginstruction_id` int(11) NOT NULL,
  `model` varchar(255) CHARACTER SET utf8 NOT NULL,
  `field` varchar(255) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `value` varchar(255) CHARACTER SET utf8 NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  `max` varchar(255) CHARACTER SET utf8 NOT NULL,
  `min` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Spalte type: 0=Hinweis, 1=Wert kann übernommen werden, 2=Wert exakt übernehmen';

CREATE TABLE IF NOT EXISTS `testinginstructions_irregularities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testinginstruction_id` int(11) NOT NULL DEFAULT '0',
  `testinginstruction_data_id` int(11) NOT NULL DEFAULT '0',
  `reportnumber_id` int(11) NOT NULL DEFAULT '0',
  `reason` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cascadegroups_cascades` (
  `cascade_group_id` int(11) NOT NULL,
  `cascade_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cascade_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deu` varchar(200) DEFAULT NULL,
  `eng` varchar(200) DEFAULT NULL,
  `xml_name` varchar(255) DEFAULT NULL,
  `model` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `modul` varchar(255) DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `dependencies` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dropdowns_masters_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters_report` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters_testingcomp` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters_testingmethod` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `testingmethod_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `dropdowns_masters_topproject` (
  `topproject_id` int(11) NOT NULL DEFAULT '0',
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `orders` CHANGE `status` `status` INT(11) NULL DEFAULT '0';
DELETE FROM `dropdowns_values` WHERE `dropdowns_values`.`dropdown_id` = 0;

CREATE TABLE IF NOT EXISTS `dropdowns_masters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `modul` varchar(255) DEFAULT NULL,
  `field` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `dependencies` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_data`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dropdowns_masters_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_dependencies`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_dependencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingcomp_id` int(11) NOT NULL,
  `dropdowns_masters_id` int(11) NOT NULL,
  `dropdowns_masters_data_id` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `global` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_dependencies_fields`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_dependencies_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testingcomp_id` int(11) NOT NULL,
  `dropdowns_masters_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `field` varchar(255) NOT NULL,
  `field_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_report`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_report` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_testingcomp`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_testingcomp` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `testingcomp_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_testingmethod`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_testingmethod` (
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0',
  `testingmethod_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dropdowns_masters_topproject`
--

CREATE TABLE IF NOT EXISTS `dropdowns_masters_topproject` (
  `topproject_id` int(11) NOT NULL DEFAULT '0',
  `dropdowns_masters_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `reportimages` CHANGE `discription` `discription` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `reportimages` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

COMMIT;

ALTER TABLE `dropdowns_masters` ADD `dependencies` TINYINT NULL AFTER `deleted`; 
