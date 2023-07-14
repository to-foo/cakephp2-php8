<?php
App::import('Vendor','tcpdf/tcpdf');

class XTCPDF  extends TCPDF
{
//	var $xheadertext  = 'PDF creado using CakePHP y TCPDF';
	var $xheadercolor = array(255,255,255);
//	var $xfootertext  = 'Copyright © %d XXXXXXXXXXX. All rights reserved.';
	var $xdata  = array();
	var $xchanges = array();
	var $xsettings  = array();
	var $Verfahren  = null;
	var $dataArray = array();
	var $xfooterfon;
	var $xfooterfontsize = 8 ;
	var $reportDeleted = false;
	public $defs = null;
	var $qr = null;
	public $bodyStart = array();
	public $forcePrintHeaders = false;

function __construct($orientation='L', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {

	parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibri.ttf', 'TrueTypeUnicode', '', 32);
	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrib.ttf', 'TrueTypeUnicode', '', 32);
	$this->AddFont('calibri', 'B', 'calibrib.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrii.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'I', 'calibrii.php');

	TCPDF_FONTS::addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibriz.ttf', 'TrueTypeUnicode', '', 96);
	$this->AddFont('calibri', 'BI', 'calibriz.php');
}

function __call($name, $arguments) {
	$method = null;
	$_margins = $this->getMargins();
	if(preg_match('/^get(left|right|top|bottom|header|footer)(padding|margin)$/', strtolower($name), $method)) {
		if(strtolower($method[2])=='margin') {
			return $_margins[strtolower($method[1])];
		} elseif($method[2] == 'padding') {
			if(array_search(strtolower($method[1]), array('left','right','top','bottom')) !== false) {
				return $_margins['padding_'.strtolower($method[1])];
			}
		}
	}

	return parent::_call($name, $arguments);
}

protected function _getFontFormatting($item, $fonts=array()) {
	$fonts = array_merge(array(
		'title' => array(
			'n'=>'calibri',
			'b'=>'calibrib',
			'i'=>'calibrii',
			'bi'=>'calibriz',
		),
		'data' => array(
			'n'=>'calibri',
			'b'=>'calibrib',
			'i'=>'calibrii',
			'bi'=>'calibriz',
		)
	), $fonts);

	$style = array('', '');
	$font = array('', '');
	if(isset($item['formatting']['bold']) && !empty($item['formatting']['bold']))
	{
		$bold = explode(' ', $item['formatting']['bold']);
		if(count($bold) == 1) $bold[1] = $bold[0];
		$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'B' : '';
		$font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'b' : '';
		$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'B' : '';
		$font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'b' : '';
	}

	if(isset($item['formatting']['italic']) && !empty($item['formatting']['italic']))
	{
		$bold = explode(' ', $item['formatting']['italic']);
		if(count($bold) == 1) $bold[1] = $bold[0];
		$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'I' : '';
		$font[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'i' : '';
		$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'I' : '';
		$font[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'i' : '';
	}

	if(isset($item['formatting']['underline']) && !empty($item['formatting']['underline']))
	{
		$bold = explode(' ', $item['formatting']['underline']);
		if(count($bold) == 1) $bold[1] = $bold[0];
		$style[0] .= !(empty($bold[0]) || $bold[0] == 'false') ? 'U' : '';
		$style[1] .= !(empty($bold[1]) || $bold[1] == 'false') ? 'U' : '';
	}

	$fonts = array_values($fonts);

	foreach($font as $num=>$fontstyle) {
		if(empty($fontstyle)) $fontstyle = 'n';

		$font[$num] = $fonts[$num][$fontstyle];
	}

	return array($style, $font);
}


	function writeSettings($defs) {
		$this->defs = $defs;
		$this->qr = null;
		$this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
	}

	public function insertPageNumbers() {
	}

	function Header() {

		if(file_exists(APP . WEBROOT_DIR . DS . 'logo' . DS . 'logo.svg')){
			$Logo = APP . WEBROOT_DIR . DS . 'logo' . DS . 'logo.svg';
			$this->ImageSVG($Logo, $x=15, $y=10, $w='', $h=15, $link='', $align='', $palign='', $border=0, $fitonpage=false);
		}

		$this->SetFont('calibri', 'n', 24);

		$this->MultiCell(
								0,
								0,
								setUTF8('Expediting'),
								0,
								'L',
								0,
								1,
								80,
								13,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
	}

	function Footer() {
	}
}
