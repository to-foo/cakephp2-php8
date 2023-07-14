<?php
App::import('Vendor','tcpdf/tcpdf');
class XTCPDF extends TCPDF
{
	protected $defs = array();

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

	function writeSettings($defs) {
		$this->defs = $defs;
	}

	function Header() {
		// Umrandungen
		//oben horizontal
		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style0);
		//unten horizontal
		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);
		//links vertikal
		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);
		//rechts vertikal
		$this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);
		$this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);

		$headerSections = array();
		$headerSections = array(
			'initiator' => array(
				'fontsize' => 7,
				'content' => null
			)
		);

		$Page =  __('Page', true) . " ".$this->getAliasNumPage()." ". __('of', true) ." ".$this->getAliasNbPages();

		array_push($headerSections, array(
			//'x'=>$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X + 32,
			'offset' => array(
				'x'=>5,
				'y'=>10
			),
			'w'=>0,
			'h'=>0,
			'align'=>'L',
			'valign'=>'T',
			'break'=>0,
			'content'=>trim($headerSections['initiator']['content']),
			'fontsize'=>@$headerSections['initiator']['fontsize']
		));

		array_push($headerSections, array(
			'offset'=>array(
				'x'=>0,
				'y'=>2
			),
			'w'=>0,
			'h'=>0,
			'align'=>'C',
			'valign'=>'T',
			'break'=>0,
			'content'=>__('Search results'),
		));

		array_push($headerSections, array(
			'offset'=>array(
				'x'=>5,
				'y'=>4
			),
			'w'=>0,
			'h'=>0,
			'align'=>'L',
			'valign'=>'T',
			'break'=>0,
			'content'=>trim($Page),
//				'content'=> null
			//'fontsize'=> 8
		));

//			$this->SetFont('freeserif', '', 12, true);
//			$text = "Dieser Text hat 10".$this->unichr(0x207b).$this->unichr(0x207f)." Zeichen und so gut wie keine Länge, aber die ist trotzdem noch länger als die Textbox im PDF-Dokument.";

		// Nur numerische Headerfelder zulassen
		$headerSections = array_filter(array_intersect_key($headerSections, array_flip(array_filter(array_keys($headerSections), 'is_numeric'))));

		// Umbruch bei letztem Headerfeld erzwingen
		end($headerSections);
		$headerSections[key($headerSections)]['break'] = 1;

		// Seitenbreite gleichm��ig aufteilen
		$sectWidth = ($this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT']) / count($headerSections) - $this->defs['QM_CELL_PADDING_L'] - $this->defs['QM_CELL_PADDING_R'];

		$maxH = 0;

		// Header eintragen
		foreach($headerSections as $id=>&$section) {
			if(isset($section['color']) && !empty($section['color']) && is_array($section['color'])) $this->SetColorArray('text', $section['color']);
			else $this->SetColorArray('text', array(0, 0, 0));

			if(isset($section['fontsize']) && !empty($section['fontsize'])) $this->setFontSize($section['fontsize']);
			else $this->SetFontSize(11);

			while(mb_detect_encoding(utf8_decode($section['content'])) != 'ASCII') {
//				$section['content'] = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($section['content']));
			}

			$section['content'] = utf8_encode($section['content']);

			$this->MultiCell(
				$sectWidth-$section['offset']['x'],
				$section['h'] + $this->defs['QM_CELL_PADDING_B'] - $section['offset']['y'],
				$section['content'],
				0,
				$section['align'],
				false,
				1,
				$this->defs['PDF_MARGIN_LEFT'] + $this->defs['QM_CELL_PADDING_L'] + $id*($sectWidth + $this->defs['QM_CELL_PADDING_L'] + $this->defs['QM_CELL_PADDING_R']) + $section['offset']['x'],
				$this->defs['QM_CELL_LAYOUT_LINE_TOP'] + $section['offset']['y'] + $this->defs['QM_CELL_PADDING_T'],
				true,
				0,
				false,
				true,
				25,
				$section['valign'],
				true
			);

			$maxH = max($maxH, $this->GetY());
		}

		// senkrechte Trennlinien Zeichnen
		// geht erst nach Headerinhalten, da Zellenh�he variabel ist
		$headerSections = array_slice($headerSections, 0, count($headerSections)-1);
		foreach($headerSections as $id=>$section) {
			$this->Line(
				$this->defs['PDF_MARGIN_LEFT'] +  ($id+1)*($sectWidth + $this->defs['QM_CELL_PADDING_L'] + $this->defs['QM_CELL_PADDING_R']),
				$this->defs['QM_CELL_LAYOUT_LINE_TOP'],
				$this->defs['PDF_MARGIN_LEFT'] +  ($id+1)*($sectWidth + $this->defs['QM_CELL_PADDING_L'] + $this->defs['QM_CELL_PADDING_R']),
				$maxH,
				$this->style0
			);

		}

		$this->Line($this->defs['PDF_MARGIN_LEFT'], $maxH, $this->defs['QM_CELL_LAYOUT_CLEAR'], $maxH, $this->style0);

		// Hier beginnt die Datenausgabe
		$this->SetY($maxH);
		$this->SetX($this->defs['PDF_MARGIN_LEFT']);

		$this->HeaderEnd = $this->GetY();
	}

	function Footer() {
	}
}
