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

	public function Header() {
// $this->xdata['deviceinfo']['adress'].
		$Y_start = 10;

		if(isset($this->xdata['deviceinfo']['logo'])){
		$this->Image(
			$this->xdata['deviceinfo']['logo'],
			20, //x
			$Y_start, //y
			30, // width
			0, // height
			'PNG',
			'', // Link
			'N', // Ausrichtung
			false, // resize
			150, //  dpi
			false, // Ausrichtung auf Seite
			false, // Bild als Maske
			false, // Maske oder Testumfluss glaube ich
			false, // der Rand
			true, // passt das Bild an Dimensionen bleiben
			false, // wenn das Bild nicht angezeit werden soll
			true, // Bild an Seitengröße anpassen
			false, // Alternativtext
			0 // altervatives Bild
		);
		}

	$this->SetFont('calibri','n','9');
	$this->MultiCell(
		50,
		20,
		$this->xdata['deviceinfo']['adress'],
		0,
		'L',
		0,
		1,
		60,
		$Y_start + 2,
		true,
		0,
		false,
		true,
		0,
		false,
		20
	);
	$this->MultiCell(
		50,
		20,
		$this->xdata['deviceinfo']['contact'],
		0,
		'L',
		0,
		1,
		120,
		$Y_start + 2,
		true,
		0,
		false,
		true,
		0,
		false,
		20
	);

	$this->Line(
		20,
		35,
		180,
		35,
		$this->style1
		);

	$headline  = null;
	if(isset($this->xdata['deviceinfo']['info']['Device']['device_type']))$headline .= $this->xdata['deviceinfo']['info']['Device']['device_type'];
	if(isset($this->xdata['deviceinfo']['info']['Device']['name']))$headline .= ' ' . $this->xdata['deviceinfo']['info']['Device']['name'];
	if(isset($this->xdata['deviceinfo']['info']['Device']['intern_no']))$headline .= ' ('.$this->xdata['deviceinfo']['info']['Device']['intern_no'].')';

	$this->SetFont('calibri','b','16');
	$this->MultiCell(
		0,
		0,
		$headline,
		0,
		'L',
		0,
		1,
		20,
		40,
		true,
		0,
		false,
		true,
		0,
		false,
		20
	);

	}

	function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Seite '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

	}
}
