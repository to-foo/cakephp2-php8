<?php
App::import('Vendor','tcpdf/tcpdf');
class XTCPDF extends TCPDF
{
	function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibri.ttf', 'TrueTypeUnicode', '', 32);
	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrib.ttf', 'TrueTypeUnicode', '', 32);
	$this->AddFont('calibri', 'B', 'calibrib.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrii.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'I', 'calibrii.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibriz.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'BI', 'calibriz.php');
	}

	function Header() {
	}

	function Footer() {
	}
}
