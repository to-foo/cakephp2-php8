<?php
App::import('Vendor','tcpdf/tcpdf');

class XTCPDF  extends TCPDF
{
//	var $xheadertext  = 'PDF creado using CakePHP y TCPDF';
	var $xheadercolor = array(255,255,255);
//	var $xfootertext  = 'Copyright © %d XXXXXXXXXXX. All rights reserved.';
        var $ReportTitleExtra = null;
	var $xdata  = array();
	var $xsettings  = array();
	var $Verfahren  = null;
	var $dataArray = array();
	var $xfooterfon;
	var $xfooterfontsize = 8 ;
	public $bodyStart = array();
	public $defs = null;
	public $reportnumber;
	var $qr = null;
	var $ReportPdf = null;
	public $forcePrintHeaders = true;
	public $hiddenFields = array();
	public $printHeader = true;
	public $RateOfFilmConsumption = null;
	public $WeldDimensions = null;

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

function writeSettings($defs) {
	$this->defs = $defs;
	$this->qr = null;
	$this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
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

public function insertSearchBarcode() {

	if(Configure::check('BarcodeSearchScanner') == false) return false;
	if(Configure::read('BarcodeSearchScanner') == false) return false;
	if(empty($this->SecID)) return false;

	$style = array(
		'border' => false,
		'hpadding' => '0',
		'vpadding' => '0',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => false,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4
		);

	$this->write1DBarcode($this->SecID, 'C39', $this->defs['QM_CELL_LAYOUT_CLEAR'] - 85, $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'] + 2, 85, 5, 0.4, $style, 'T');
}

function Header() {

			list($r, $b, $g) = $this->xheadercolor;
			// Daten vorbereiten
			$Verfahren = $this->Verfahren;
			$ReportPDfSettings = 'Report'.$Verfahren.'Pdf';
			$Report[1] = 'Report'.$Verfahren.'Generally';
			$Report[2] = 'Report'.$Verfahren.'Specific';
			$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
			$ReportPdf = 'Report'.$Verfahren.'Pdf';
			$width = array();
			$imageArray = array();
			$imageArrayEvaluation = array();
			$lastImageHeight = 0;
			$StandartWidthLabel = 25;
			$StandartWidthData = 50;
			$cellheight = 4.5;
			if(!isset($this->printversion) || empty($this->printversion)) $this->printversion = 1;

			// Die Werte aus den Daten und den Setting weden zusammengeführt
			// in einem neuen Array, es wird die Höhe der Zellen berechnet
			foreach($Report as $Reports){
				foreach($this->xsettings->$Reports as $_Report){
					foreach($_Report as $__Report){

                                            						if(Configure::read('WriteSignatory') == true){
							if(isset($__Report->pdf->signatory->show) && $__Report->pdf->signatory->show == 1){
								$SignatorySettings[] = $__Report->pdf->signatory;
							}
						}
						// Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
						if(isset($__Report->pdf->description->p) && $__Report->pdf->description->p != ''){
							$discriptionArray = null;
							$xxx = 0;
							foreach($__Report->pdf->description->p as $_p){
								// um das XML-Objekt loszuwerden
								if($xxx > 0){$discriptionArray .= "\n";}
								$discriptionArray .= $_p;
								$xxx++;
							}
						}
						else {
							$discriptionArray = trim($__Report->pdf->description);
						}

						// Test ob Objekt an mehreren Stellen angezeigt werden soll
//						if($__Report->pdf->output){}
						$hidden = array_search(trim($__Report->model).'.'.trim($__Report->key), $this->hiddenFields) !== false;
						$place = explode(' ',$__Report->pdf->output);
						foreach($place as $_place){
							if($_place == 1){
							if($__Report->pdf->output == 1){
								$key = trim($__Report->key);

								$dataArray[$Reports][trim($__Report->pdf->sorting)]['description'] = $discriptionArray;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim($this->xdata->$Reports->$key);
								if(isset($__Report->pdf->child) && trim($__Report->pdf->child) != '') {
									if(isset($__Report->pdf->child_spacer)) $spacer = trim($__Report->pdf->child_spacer);
									if(empty($spacer)) $spacer = ' ';

									$datavalue = '';

									foreach(explode(' ',trim($__Report->pdf->child)) as $child) {
										$datavalue = trim($datavalue.$spacer.trim($this->xdata->$Reports->$child),$spacer);
									}

									if(!empty($datavalue)) $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = $datavalue;
								}

								if(isset($__Report->multiselect->model) && trim($__Report->multiselect->model) != '') {
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = preg_replace('/[\r\n]+/', ', ', trim($dataArray[$Reports][trim($__Report->pdf->sorting)]['data']));
								}

								// die Breite von Label- und Datenzelle wird ermittelt
								$width = explode(' ',trim($__Report->pdf->positioning->width));
								$fontcolorlabel = $__Report->pdf->fontcolorlabel;
								$bgcolorlabel = $__Report->pdf->bgcolorlabel;
								$fontcolordata = $__Report->pdf->fontcolordata;
								$bgcolordata = $__Report->pdf->bgcolordata;
								$cellheight = trim($__Report->pdf->positioning->height);

								if(!isset($width[0])){$width[0] = $StandartWidthLabel;}
								if(!isset($width[1])){$width[1] = $StandartWidthData;}

								$dataArray[$Reports][trim($__Report->pdf->sorting)]['clear'] = 0;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['break'] = trim($__Report->pdf->positioning->break);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['measure'] = trim($__Report->pdf->measure);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontsize'] = trim($__Report->pdf->fontsize);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdescription'] = trim($__Report->pdf->lines->description);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['textalign'] = trim($__Report->pdf->textalign);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata'] = trim($__Report->pdf->lines->data);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['fieldtype'] = $hidden ? 'string' : trim($__Report->fieldtype);
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['radiooption'] = $hidden ? array() : $__Report->radiooption;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_label'] = $width[0];
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_data'] = $width[1];
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolorlabel'] = $hidden ? json_decode('{"color":[255, 255, 255]}') : $fontcolorlabel;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolorlabel'] = $hidden ? json_decode('{"color":[255, 255, 255]}') : $bgcolorlabel;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolordata'] = $hidden ? json_decode('{"color":[255, 255, 255]}') : $fontcolordata;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolordata'] = $hidden ? json_decode('{"color":[255, 255, 255]}') : $bgcolordata;
								$dataArray[$Reports][trim($__Report->pdf->sorting)]['cellheight'] = $cellheight;
								}
							break;
							}
						}
					}
				}

				// nach den Bilder schauen



				if(isset($this->xsettings->$ReportPDfSettings->images) && !empty($this->xsettings->$ReportPDfSettings->images)){
					foreach($this->xsettings->$ReportPDfSettings->images->children() as $_images){

						if(trim($_images->position->area) == $Reports){

							$images = array();

							if(!empty($_images->type)) $images['type'] = trim($_images->type);
							else continue;

							if(empty($_images->file)) continue;

							if(!file_exists(Configure::read('root_folder') . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'])) continue;
							else $images['file'] = Configure::read('root_folder') . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'];

							if(!empty($_images->dimension)) $dimension = explode(' ',$_images->dimension);
							else continue;

							if(!empty($_images->position->float)) $position = explode(' ',$_images->position->float);
							else continue;

							if(!empty($_images->position->absolut)) $absolut = explode(' ',$_images->position->absolut);
							else continue;

							if(isset($dimension)){
								$images['width'] = $dimension[0];
								$images['height'] = $dimension[1];
							}
							else continue;

							if(isset($position)){
								$images['vertikal'] = $position[0];
								$images['horizontal'] = $position[1];
							}
							else continue;

							if(isset($absolut)){
								$images['x'] = $absolut[0];
								$images['y'] = $absolut[1];
							}
							else continue;

							$imageArray[$Reports][] = $images;
						}
					}
				}

			}

			// Das neue Array nach der vorgegeben Reihenfolge sortieren
			if(isset($Report[1])){@ksort($dataArray[$Report[1]]);}
			if(isset($Report[2])){@ksort($dataArray[$Report[2]]);}

			$datenArraySort = array();
			// Die Felder werden nach ihrer Reihenfolge sortiert
			foreach($Report as $_Report){
				if($dataArray[$_Report] > 0){
					foreach($dataArray[$_Report] as $_dataArray){
						$datenArraySort[$_Report][] = $_dataArray;
					}
				}
			}

			// die Variable für den Umbruch wird auf den Defaultwert gesetzt
			$current_clear = $this->defs['PDF_MARGIN_LEFT'];
			$maxCellheigth = 0;
			$datenArraySortRow = array();
			$datenArrayMaxHeight = array();
			$xx = 0;

			// Die Daten werden in Zeilen und Spalten aufgeteilt
			foreach($Report as $_Report){
				if(isset($datenArraySort[$_Report])){
				for($x = 0; $x < count($datenArraySort[$_Report]); $x++){
					// Wenn die Gesamtbreite zu groß wird erfolgt ein Umbruch
					$current_clear = $current_clear + $datenArraySort[$_Report][$x]['width_label'] + $datenArraySort[$_Report][$x]['width_data'];
					$datenArraySort[$_Report][$x]['clear'] = $x;

					$datenArraySortRow[$_Report][$xx][] = $datenArraySort[$_Report][$x];
//					$datenArraySortRow[$_Report][$xx][] = $datenArraySort[$_Report][$x];

					if($datenArraySort[$_Report][$x]['break'] == 1){
						$xx++;
						$maxCellheigth = 0;
						$current_clear = $this->defs['PDF_MARGIN_LEFT'];
					}
				}
				$xx = 0;
				}
			}

			$this->setCellPaddings($this->defs['QM_CELL_PADDING_L'], $this->defs['QM_CELL_PADDING_T'], $this->defs['QM_CELL_PADDING_R'], $this->defs['QM_CELL_PADDING_B']);

			// Umrandungen
			//oben horizontal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style0);
			//unten horizontal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);
			//links vertikal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);
			//rechts vertikal
			$this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style0);

			$this->insertSearchBarcode();

			$this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);

			$headerSections = array();
			if($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->SHOW_ADRESS == 1){
				$Firmenadresse  = null;

				foreach($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->items as $_items){
					$thistItem = trim($_items->item);
					$Firmenadresse .= trim($this->xdata->Testingcomp->$thistItem);
					if(trim($_items->break) == 1 && trim($this->xdata->Testingcomp->$thistItem) != ''){
						$Firmenadresse .= "\n";
					}
					elseif(trim($this->xdata->Testingcomp->$thistItem) != '') {
						$Firmenadresse .= " ";
					}
					$thistItem = null;
				}

				if(true || $this->xdata->Testingcomp->id != 1){
					// Logo Prüfunternehmen
					if(file_exists(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png') && $this->xdata->Testingcomp->id != 1){

						$this->Image(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png',
							intval($this->xsettings->$ReportPdf->settings->QM_LOGO_X) + 1,
							intval($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y) + 1,
							intval($this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH),
							intval($this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT),
							'PNG',
							false,
							'',
							false,
							300,
							'',
							false,
							false,
							0,
							'LT',
							false,
							false
						);
					}

					$headerSections['initiator']['offset']['x'] = $this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH + 3;
					$headerSections['initiator']['content'] = $Firmenadresse;
				} else {
					$username =
						$this->xuser['name']. "\n".
						$this->xuser['Testingcomp']['strasse']. "\n".
						$this->xuser['Testingcomp']['plz'] ." " .
						$this->xuser['Testingcomp']['ort'] . "\n".
						$this->xuser['Testingcomp']['email'];
					$headerSections['initiator']['content'] = $username;
				}

				$headerSections['initiator']['fontsize'] = 7;
			}

			// Logo Wacker
			if(file_exists(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png')){
				$size = getimagesize(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png');

				$this->Image(
					Configure::read('company_logo_folder').DS.'1'.DS.'logo.png',
					$this->defs['PDF_MARGIN_LEFT'],
					$this->defs['PDF_MARGIN_TOP']-7,
					30,
					0,
					'PNG',
					'',
					'',
					false,
					150,
					'',
					false,
					false,
					0,
					false,
					false,
					false
				);
			}

			// Titel
			$TestreportName = __("Report", true) . "\n" . $this->xdata->Testingmethod->verfahren;

			if(isset($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE) && !empty($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE) ){
				$TestreportName = trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE);
			}
// Prüfer Wacker
			// Reportnummer erzeugen
			if(empty($this->reportnumber))
			{
				'<b>'.$this->reportnumber = trim($this->xdata->Report->identification) . " / ";
				$this->reportnumber .= trim($this->xdata->Reportnumber->number) . " / ";
				$this->reportnumber .= trim($this->xdata->Reportnumber->year).'</b>';
			} else {
				$this->reportnumber = preg_replace('#([^/]+)/([^/]+)/([^/]+)/([^/]+)#', '$1 / <b>$2/$3/$4</b>', $this->reportnumber);
			}

			$PrueferWacker = __("examiner no.", true);
			$Wacker = "\n";
			$Wacker .= '<b>'.$this->reportnumber.'</b>'.PHP_EOL;
			$Wacker .=  __('Page', true) . " ".$this->getAliasNumPage()." ". __('of', true) ." ".$this->getAliasNbPages();

			array_push($headerSections, array(
				//'x'=>$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X + 32,
				'offset' => array(
					'x'=>@$headerSections['initiator']['offset']['x'],
					'y'=>0
				),
				'w'=>0,
				'h'=>0,
				'align'=>'L',
				'valign'=>'T',
				'break'=>0,
				'content'=>trim($headerSections['initiator']['content']),
				'fontsize'=>$headerSections['initiator']['fontsize'],
				'html'=>false
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
				'content'=>trim($TestreportName),
				'html'=>false
			));

                        if(isset ($this->ReportTitleExtra) && !empty($this->ReportTitleExtra)) {
                        $rgbred = array(255,0,0);
                        array_push($headerSections, array(
				'offset'=>array(
					'x'=>0,
					'y'=>3
				),
				'w'=>0,
				'h'=>0,
				'align'=>'C',
				'valign'=>'T',
				'break'=>0,
				'content'=>trim($this->ReportTitleExtra),
				'html'=>false,
                                'warncolor' => true,
			));
                        }

			array_push($headerSections, array(
				'offset'=>array(
					'x'=>0,
					'y'=>0
				),
				'w'=>0,
				'h'=>0,
				'align'=>'L',
				'valign'=>'T',
				'break'=>0,
				'content'=>trim($Wacker),
				'html'=>true
			));

		//			$this->SetFont('freeserif', '', 12, true);
		//			$text = "Dieser Text hat 10".$this->unichr(0x207b).$this->unichr(0x207f)." Zeichen und so gut wie keine Länge, aber die ist trotzdem noch länger als die Textbox im PDF-Dokument.";

			// Nur numerische Headerfelder zulassen
			$headerSections = array_filter(array_intersect_key($headerSections, array_flip(array_filter(array_keys($headerSections), 'is_numeric'))));

			// Umbruch bei letztem Headerfeld erzwingen
			end($headerSections);
			$headerSections[key($headerSections)]['break'] = 1;
/*
print '<pre>';
var_dump($headerSections);
print '</pre>';
die('tot');
*/
			// Seitenbreite gleichm��ig aufteilen
			$sectWidth = ($this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT']) / count($headerSections) - $this->defs['QM_CELL_PADDING_L'] - $this->defs['QM_CELL_PADDING_R'];
			$maxH = 0;
			// Header eintragen
			foreach($headerSections as $id => &$section) {

				if(isset($section['fontsize'])) $this->setFontSize($section['fontsize']);
				else $this->SetFontSize(11);

 /*
				while(mb_detect_encoding(utf8_decode($section['content'])) != 'ASCII') {
//					$section['content'] = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($section['content']));
				}
*/
//pr(mb_detect_encoding($section['content']));


				isset($section['warncolor']) ?$this->SetTextColor(255, 0, 0):'';

//				$section['content'] = utf8_encode($section['content']);

				if(!isset($section['html'])) $section['html'] = false;

				if($section['html']) {
					$this->writeHTMLCell(
						$sectWidth-$section['offset']['x'],
						$section['h'] + $this->defs['QM_CELL_PADDING_B'] - $section['offset']['y'],
						$this->defs['PDF_MARGIN_LEFT'] + $this->defs['QM_CELL_PADDING_L'] + $id*($sectWidth + $this->defs['QM_CELL_PADDING_L'] + $this->defs['QM_CELL_PADDING_R']) + $section['offset']['x'],
						$this->defs['QM_CELL_LAYOUT_LINE_TOP'] + $section['offset']['y'] + $this->defs['QM_CELL_PADDING_T'],
						preg_replace('/[\r\n]+/', '<br>', trim($section['content'])),
						0,
						1,
						false,
						true,
						$section['align'],
						true
					);
				} else {
					$this->MultiCell(
						$sectWidth-$section['offset']['x'],
						$section['h'] + $this->defs['QM_CELL_PADDING_B'] - $section['offset']['y'],
						trim($section['content']),
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
				}
                               isset($section['warncolor']) ? $this->SetTextColor(0, 0, 0):'';
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

		//			$this->MultiCell(55, 5, 'Seite '.$this->getAliasNumPage().' von '.$this->getAliasNbPages(),0,'L',false,0,141,$this->defs['QM_CELL_LAYOUT_LINE_TOP'],true,0,false,true,5,'M',true);
		//			$this->MultiCell(30, 5, 'Seite '.$this->getAliasNumPage().' von '.$this->getAliasNbPages(),0,'C',false,0,98,10,true,0,false,true,5,'M',true);


			//Linie vertikal 1 Haeder
		//			$this->Line(78, $this->defs['QM_CELL_LAYOUT_LINE_TOP'], 78, $this->GetY(), $this->style0);
			//Linie vertikal 2 Haeder
		//			$this->Line(140, $this->defs['QM_CELL_LAYOUT_LINE_TOP'], 140, $this->GetY(), $this->style0);
			//Linie horizontal Anbschluss Haeder

		//			$this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $maxH, $this->defs['QM_CELL_LAYOUT_CLEAR'], $maxH, $this->style0);

			// QR-Code
			// false entfernen, um die Funktion zu aktivieren
			if(false && isset($this->qr)) {
				if($this->qr['type'][1] == '2D') {
					$qr_height = $maxH-$this->defs['PDF_MARGIN_TOP']+2;
					$qr_height = ($maxH-$this->defs['PDF_MARGIN_TOP']+2) * 0.75;

					$this->write2DBarcode(
						$this->qr['code'],
						$this->qr['type'][2],
						$this->defs['QM_CELL_LAYOUT_CLEAR']-$qr_height-1,
						$this->defs['PDF_MARGIN_TOP']+1,
						$qr_height,
						$qr_height,
						array(
							'border'=>false,
							'fgcolor'=>$this->qr['fgcolor'],
							'bgcolor'=>$this->qr['bgcolor'],
							'position'=>'S',
						),
						'N'
					);
				} elseif($this->qr['type'][1] == '1D') {
					$qr_height = $maxH-$this->defs['PDF_MARGIN_TOP']-1;
					$qr_height = ($maxH-$this->defs['PDF_MARGIN_TOP']-1) * 0.75;

					$this->write1DBarcode(
						$this->qr['code'],
						$this->qr['type'][2],
						$this->defs['QM_CELL_LAYOUT_CLEAR']-$qr_height-0.5,
						$this->defs['PDF_MARGIN_TOP']+1.5,
						$qr_height,
						$qr_height,
						0.4,
						array(
							'border'=>false,
							'fgcolor'=>$this->qr['fgcolor'],
							'bgcolor'=>$this->qr['bgcolor'],
							'position'=>'S',
						),
						'N'
					);
				}
			}
			$this->SetY($maxH);

			// Beginn Kopfdaten
			$this->SetFont('helvetica', '', 9);

			// Hier beginnt die Datenausgabe
			$this->SetY($this->GetY(),true,false);
			$this->SetX($this->defs['PDF_MARGIN_LEFT'],true,false);
			$StartY =  $this->GetY();
			$MaxY =  array();
			$MaximalY =  0;
			$count = 0;
			$theLines =	0;

			foreach($Report as $_Report){
				$MaxY = array();
				$MaximalY = 0;
				$cellheight = 4.5;

				// Ab Seite zwei sollen nur noch die allgemeinen Daten ausgegeben werden
				if(!$this->forcePrintHeaders && $count == 0 && $this->numpages > 1){
					break;
				}
				$this->forcePrintHeaders = false;
				if(isset($datenArraySortRow[$_Report])){

					// mögliche Bilder einlesen
					if(isset($imageArray[$_Report])){
						foreach($imageArray[$_Report] as $_imageArray){

						// Wenn die Ausgabe von Infos ab der zweiten Seite unterdrückt werden soll
							if(
								!$this->forcePrintHeaders &&
								Configure::check('HeaderOnlyFirstPage') &&
								Configure::read('HeaderOnlyFirstPage') == true && $this->numpages > 1
							) continue;

							if(
								!$this->forcePrintHeaders &&
								preg_match('/Generally$/', $_key) &&
								Configure::check('HeaderOnlyFirstPageGenerally') &&
								Configure::read('HeaderOnlyFirstPageGenerally') == true && $this->numpages > 1
							) continue;


							if(
								!$this->forcePrintHeaders && preg_match('/Specific$/', $_key) &&
								Configure::check('HeaderOnlyFirstPageSpecific') &&
								Configure::read('HeaderOnlyFirstPageSpecific') == true && $this->numpages > 1
							) continue;


								$this_x = 0;
								$this_y = 0;

								// Wenn das Bild unterhalb eingefügt wird, muss der Y-Punkt gesetzt werden
								if($_imageArray['x'] > 0){
									$this_x = $_imageArray['x'];
								}
								if($_imageArray['y'] > 0){
									$this_y = $_imageArray['y'];
								}
								if($_imageArray['x'] == 0 && $_imageArray['y'] == 0){
									if($_imageArray['vertikal'] == 'T') $this_y = $AreaStartY[$_Report] + 8;
									if($_imageArray['horizontal'] == 'R') $this_x = $this->defs['QM_CELL_LAYOUT_WIDTH'] - $_imageArray['width'];
								}

								$this->ImageEps(
										$_imageArray['file'],
										$this_x,
										$this_y,
										$_imageArray['height'],
										$_imageArray['width'],
										'',
										true,
										'',
										'',
										0,
										true
								);


						}


					}

				if($_Report == 'ReportUtSpecific') {
					$hasSpecific = !!count(array_filter(array_slice($datenArraySortRow[$_Report], 3, 8), function($row) { return !empty($row[0]['data']); }));
				}

				// Die Daten ausgeben
				for($x = 0; $x < count($datenArraySortRow[$_Report]); $x++){
					if($_Report == 'ReportUtSpecific') {
						if(!$hasSpecific) {
							$x = count($datenArraySortRow[$_Report])-1;		// Überschrift "Prüfergebnis noch anzeigen"
						} else if(empty($datenArraySortRow[$_Report][$x][0]['data']) && $x > 2 && $x < count($datenArraySortRow[$_Report])-1) {
							// Wenn Zeile keine Überschrift oder Tabellenkopf (2 Zeilen) ist, überspringen
							continue;
						}
					} elseif($_Report == 'ReportTtSpecific' && $this->getNumPages()==1) {
						$this->bodyStart[1] = $this->GetY();
						$this->addPage();
						$this->SetY($maxH);
					}

					$heightesLineHeight = 0;

					for($xx = 0; $xx < count($datenArraySortRow[$_Report][$x]); $xx++){
						// es gibt Unterschiede in der Darstellung zwischen Objekt und Prüfdaten
						$theFillColor = false;
						$theBorderStyleData = 0;
						$this->SetTextColor(0, 0, 0);
						$this->SetFillColor(250,250,250);

						$theFillColor = true;

						// Wenn angegeben Textfarbe setzten
						if(isset($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel'])){
							$this->SetTextColor(
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[0]),
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[1]),
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[2])
							);
						}

						// Wenn angegeben hintergrundfarbe setzten
						if(isset($datenArraySortRow[$_Report][$x][$xx]['bgcolorlabel'])){
							$this->SetFillColor(
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolorlabel']->color[0]),
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolorlabel']->color[1]),
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolorlabel']->color[2])
							);
						}

						// Wenn ein Zeilenumbruch vorhanden ist (nur |br|, andere Leerzeichen und Umbrüche werden mit Space ersetzt)
						$discriptionArray = explode('|br|', preg_replace('/[\n]+/', ', ', $datenArraySortRow[$_Report][$x][$xx]['description']));

						if(count($discriptionArray) == 1){
							$discription = $datenArraySortRow[$_Report][$x][$xx]['description'];
						}
						elseif(count($discriptionArray) > 1){

							$discription = null;

							$discCount = 1;
							foreach($discriptionArray as $_discriptionArray){
								$discription .= $_discriptionArray;
								if($discCount < count($discriptionArray)){
									$discription .= "\n";
								}
								$discCount++;
							}
						}
						else {
						}

//						if($discription != ''){$discription .= ':';}
						$discriptionData = $datenArraySortRow[$_Report][$x][$xx]['data'];

						$MaxY[$x][$xx]['StartY'] = $this->GetY();

						// Wenn eine Schriftgröße angegeben wird
						if($datenArraySortRow[$_Report][$x][$xx]['fontsize'] != ''){
							$fontsize = $datenArraySortRow[$_Report][$x][$xx]['fontsize'];
						}
						else {
							$fontsize = 8;
						}

						$this->SetFont('helveticaB', 'B', $fontsize);

						if($datenArraySortRow[$_Report][$x][$xx]['cellheight'] != ''){
							$cellheight = $datenArraySortRow[$_Report][$x][$xx]['cellheight'];
						}

						if($datenArraySortRow[$_Report][$x][$xx]['linesdescription'] != ''){
							$style = 'style'.$datenArraySortRow[$_Report][$x][$xx]['linesdescription'];
							$theBorderStyleDisc = $this->$style;

							if($datenArraySortRow[$_Report][$x][$xx]['linesdescription'] == '0'){
								$theBorderStyleDisc = 0;
							}
						}

						// Die Linine für die Daten schreiben
						if($datenArraySortRow[$_Report][$x][$xx]['linesdescription'] != ''){

							$theLines =	0;

							$theStyle =	'LineStyle'.$datenArraySortRow[$_Report][$x][$xx]['linesdescription'];
							$theLines =	'LineLines'.$datenArraySortRow[$_Report][$x][$xx]['linesdescription'];
							$theLinesThickness =	'LineLineThickness'.$datenArraySortRow[$_Report][$x][$xx]['linesdescription'];

							// Wenn ein Style vorhanden ist
							if(isset($this->$theStyle)){

								// Die Dicke der stärksten Linie herausfinden
								// diese muss beim Zeilenumbruch zur Y-Pos hinzugerechnet werde
								if($theLinesThickness != ''){
									if($theLinesThickness > $heightesLineHeight){
										$heightesLineHeight = $theLinesThickness;
									}
								}

								$this->SetLineStyle($this->$theStyle);
							}

							// Wenn ein Zusammensetzung der Linien vorhanden ist
							if(isset($this->$theLines)){
								$theLines =	$this->$theLines;
							}
							else {
								$theLines =	0;
							}
						}

						// Testausrichtung
						$textalign = 'R';
						if($datenArraySortRow[$_Report][$x][$xx]['textalign'] != ''){
							$textalign = $datenArraySortRow[$_Report][$x][$xx]['textalign'];
						}


						$this->SetCellPadding(0.5);
						// Das Feld für die Bezeichnung
						if($datenArraySortRow[$_Report][$x][$xx]['width_label'] != 0){
							$this->MultiCell(
								$datenArraySortRow[$_Report][$x][$xx]['width_label'],
								$cellheight,
								$discription,
								$theLines,
								$textalign,
								true,
								0,
								$this->GetX(),
								$this->GetY(),
								true,
								0,
								false,
								true,
								$cellheight,
								'M',
								true
							);
						}
						$theLines = 0;

						// Das Feld mit dem Inhalt
						$this->SetFont('helvetica', 'BI', $fontsize);
						$this->SetFont('calibri', 'BI', $fontsize);

						// Wenn angegeben Textfarbe setzten
						if(isset($datenArraySortRow[$_Report][$x][$xx]['fontcolordata'])){
							$this->SetTextColor(
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[0]),
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[1]),
								trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[2])
							);
						}

						// Wenn angegeben hintergrundfarbe setzten
						if(isset($datenArraySortRow[$_Report][$x][$xx]['bgcolordata'])){
							$this->SetFillColor(
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolordata']->color[0]),
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolordata']->color[1]),
								trim($datenArraySortRow[$_Report][$x][$xx]['bgcolordata']->color[2])
							);
						}

						// bei einer Checkbox muss der Wert umgewandelt werden
						if(
							isset($datenArraySortRow[$_Report][$x][$xx]['fieldtype']) &&
							trim($datenArraySortRow[$_Report][$x][$xx]['fieldtype']) == 'checkbox'
						){

							if($discriptionData == 'false'){
								$discriptionData = '';
							}
							if($discriptionData == '0'){
								$discriptionData = '';
							}
							if($discriptionData == 'true'){
								$discriptionData = 'X';
							}
							if($discriptionData == '1'){
								$discriptionData = 'X';
							}
						}

						if(
							isset($datenArraySortRow[$_Report][$x][$xx]['fieldtype']) &&
							trim($datenArraySortRow[$_Report][$x][$xx]['fieldtype']) == 'radio'
						){
							if(
								isset($datenArraySortRow[$_Report][$x][$xx]['radiooption']) &&
								$datenArraySortRow[$_Report][$x][$xx]['radiooption']->children()->count() > 0
							){
								//if($discriptionData != ''){
								foreach($datenArraySortRow[$_Report][$x][$xx]['radiooption'] as $_radiokey => $_radiovalue){
									$radio_x = 0;
									foreach($_radiovalue as $__radiokey => $__radiovalue){
										if($radio_x == intval($discriptionData)){
//											pr(trim($__radiovalue));
											$discriptionData = trim($__radiovalue);
											break;
											}
											$radio_x++;
										}
									}
								//}
							}
							else {
								$discriptionData = $this->defs['radiodefault'][intval($discriptionData)];
							}
						}

						$discriptionData = trim(preg_replace('/[0]+[\.:]+[0]+$/', '', $discriptionData));

						// Wenn Daten vorhanden sind und eine Maßeinheit da ist, wird dies angefügt
						if($discriptionData != '' && $datenArraySortRow[$_Report][$x][$xx]['measure']){

							// Wenn die Masseinheit per Hand eingetragen wurde, wird Sie hier entfernt
							$discriptionData = str_replace($datenArraySortRow[$_Report][$x][$xx]['measure'] ,'' , $discriptionData);

							$discriptionData .= ' '.$datenArraySortRow[$_Report][$x][$xx]['measure'];
						}

						// Die Linine für die Daten schreiben
						if($datenArraySortRow[$_Report][$x][$xx]['linesdata'] != ''){

							$theLines =	0;

							$theStyle =	'LineStyle'.$datenArraySortRow[$_Report][$x][$xx]['linesdata'];
							$theLines =	'LineLines'.$datenArraySortRow[$_Report][$x][$xx]['linesdata'];
							$theLinesThickness =	'LineLineThickness'.$datenArraySortRow[$_Report][$x][$xx]['linesdata'];

							// Wenn ein Style vorhanden ist
							if(isset($this->$theStyle)){

								// Die Dicke der stärksten Linie herausfinden
								// diese muss beim Zeilenumbruch zur Y-Pos hinzugerechnet werde
								if($theLinesThickness != ''){
									if($theLinesThickness > $heightesLineHeight){
										$heightesLineHeight = $theLinesThickness;
									}
								}

								$this->SetLineStyle($this->$theStyle);
							}

							// Wenn ein Zusammensetzung der Linien vorhanden ist
							if(isset($this->$theLines)){
								$theLines =	$this->$theLines;
							}
							else {
								$theLines =	0;
							}
						}

						if($datenArraySortRow[$_Report][$x][$xx]['width_data'] > 0){
							$discriptionData = str_replace('|br|', PHP_EOL, $discriptionData);

							$discriptionData = preg_replace('/\xc3\x83/', "\xc3\x9f", $discriptionData);		// ß
							$discriptionData = preg_replace('/\xe2\x8c\x80/', "\xc3\x98", $discriptionData);	// ⌀
							$discriptionData = preg_replace('/\xc2\xb0/',"\xe2\x81\xb0", $discriptionData);		// superscript 0
							//$discriptionData = preg_replace('/\xc2\xb9/',"\xc2\xb9", $discriptionData);		// superscript 1
							//$discriptionData = preg_replace('/\xc2\xb2/',"\xc2\xb2", $discriptionData);		// superscript 2
							//$discriptionData = preg_replace('/\xc2\xb3/',"\xc2\xb3", $discriptionData);		// superscript 3
							$discriptionData = preg_replace('/\xc3\xa2\xc2/',"\xe2\x81", $discriptionData);	// superscript 4-9,+-()=

							//pr($discriptionData);
							//pr(join('-', array_map(function($char){return $char.'('.dechex(ord($char)).')';}, str_split($discriptionData))));
													// Testausrichtung
							$textalign = 'C';
							if($datenArraySortRow[$_Report][$x][$xx]['textalign'] != ''){
								$textalign = $datenArraySortRow[$_Report][$x][$xx]['textalign'];
							}



							$this->MultiCell(
								$datenArraySortRow[$_Report][$x][$xx]['width_data'],
								$cellheight,
								$discriptionData,
								$theLines,
								$textalign,
								$theFillColor,
								0,
								$this->GetX(),
								$this->GetY(),
								true,
									0,
								false,
								true,
								$cellheight,
								'M',
								true
							);
						}

						$textalign = 'C';

						$this->startTransaction();
						$this->Ln();
						$heightesLineHeight = max($this->GetY(), $heightesLineHeight);
						$this->rollbackTransaction(true);

						$theStyle =	null;
						$theLines =	null;
						$theLinesThickness = null;
					$this->SetX($this->GetX() + 0.15,true,false);
					}
				//$this->Ln();
				$this->SetY($heightesLineHeight + 0.15,true,false);
				$heightesLineHeight = 0;
				}
					// Wenn das eingefügte Bild größer ist als die letzte Zellenhöhe
					if($lastImageHeight > $this->GetY()){
//						pr($lastImageHeight);
//						pr($this->GetY());
						$this->SetY($lastImageHeight,true,false);
					}

				}

			// Die letzte Linie muss per Hand gezeichnet werden
			$this->SetY($this->GetY());
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style0);
			$count++;
			}



		$this->bodyStart[$this->getPage()] = $this->HaederEnd = $this->GetY()+0.5;
		}

function Footer() {

		if(trim($this->xsettings->{'Report'.($this->Verfahren).'Pdf'}->settings->QM_SHOW_FOOTER) == 'false' || trim($this->xsettings->{'Report'.($this->Verfahren).'Pdf'}->settings->QM_SHOW_FOOTER) == '0') return;

		$printFooter = true;
		if(Configure::read('FooterOnlyFirstPage') == true && $this->numpages > 1) $printFooter = false;
	//	if($this->forcePrintHeaders) $printFooter = true;

		if(!$printFooter) return;
//	if($this->numpages > 1) return;
		$Verfahren = $this->Verfahren;
		$Report[1] = 'Report'.$Verfahren.'Generally';
		$dataArray = array();
		$width = array();
		$StandartWidthLabel = 25;
		$StandartWidthData = 50;
		$StandartCellHeight = 6;
		$cellheight = 4;
		$_ReportsFooterheight = 0;

		$this->SetY($this->defs['QM_START_FOOTER']);
		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_START_FOOTER'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_START_FOOTER'], $this->style0);

		$Reports[0] = 'Report'.$this->Verfahren.'Generally';
		$Reports[1] = 'Report'.$this->Verfahren.'Specific';
		$Reports[2] = 'Report'.$this->Verfahren.'Evaluation';
		$ReportsFooter = array();

		$this->SetCellPadding(1);

		foreach($Reports as $_Reports){
			foreach($this->xsettings->$_Reports as $_xsettings){
				foreach($_xsettings as $__xsettings){
					if($__xsettings->pdf->output == 5){

						// Wenn die Bezeichnung ein Array ist, bei Zeilenumbrüchen
						if($__xsettings->pdf->description->p != ''){
							$discriptionArray = array();
							$row = null;
							foreach($__xsettings->pdf->description->p as $_p){
								// um das XML-Objekt loszuwerden
								$row .= $_p;
					 			$discriptionArray[] = $row;
								$row = null;
							}
						}
						else {
							$discriptionArray = trim($__xsettings->pdf->description);
						}

						// Die Daten anhand des Keys aus dem Datenarray holen
						if(isset($__xsettings->key)){

							$DataKey = trim($__xsettings->key);

							if($__xsettings->pdf->positioning->height != ''){
								$CellHeight = $__xsettings->pdf->positioning->height;
							}
							else{
								$CellHeight = $StandartCellHeight;
							}
							// hier werden die Ausgabedaten gesammelt, die im Fluss angeordnet sind
							if($__xsettings->pdf->positioning->x[0] == ''){
								$width = explode(' ',trim($__xsettings->pdf->positioning->width));
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['data'] = $this->xdata->$_Reports->$DataKey;
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['key'] = $DataKey;
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['sorting'] = trim($__xsettings->pdf->sorting);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['description'] = $discriptionArray;
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['widthLabel'] = $width[0];
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['widthData'] = $width[1];
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['height'] = $CellHeight;
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['x'] = trim($__xsettings->pdf->positioning->x);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['rotation'] = trim($__xsettings->pdf->positioning->rotation);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['mother'] = trim($__xsettings->pdf->mother);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['child'] = trim($__xsettings->pdf->child);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['fontsize'] = trim($__xsettings->pdf->fontsize);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['break'] = trim($__xsettings->pdf->positioning->break);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['textalign'] = trim($__xsettings->pdf->textalign);


								// Wenn mehrere Felder zusammengefasst werden sollen
								if($ReportsFooter[trim($__xsettings->pdf->sorting)]['child'] == 1){
									foreach($this->xsettings->$_Reports as $_xsettingsMother){
										foreach($_xsettingsMother as $__xsettingsMother){
											if(trim($__xsettingsMother->pdf->mother) == $DataKey){
												$DataKeyMother = trim($__xsettingsMother->key);
												$ReportsFooter[trim($__xsettings->pdf->sorting)]['data'][0] = trim($ReportsFooter[trim($__xsettings->pdf->sorting)]['data'].'; '.trim($this->xdata->$_Reports->$DataKeyMother));
											}
										}
									}
								}
							}
							// hier werden die Ausgabedaten gesammelt, die einen festen X-Punkt haben
							if($__xsettings->pdf->positioning->x[0] != ''){
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['data'] = $this->xdata->$_Reports->$DataKey;
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['key'] = $DataKey;
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['description'] = $discriptionArray;
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['width'] = trim($__xsettings->pdf->positioning->width);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['height'] = trim($__xsettings->pdf->positioning->height);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['x'] = trim($__xsettings->pdf->positioning->x);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontsize'] = trim($__xsettings->pdf->fontsize);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['textalign'] = trim($__xsettings->pdf->textalign);
							}
						}
					}
				}
			}
		}


		// Das Array nach der Ausgabereihenfolge sortieren
		@ksort($ReportsFooter);
		@ksort($ReportsOverFooter);
		$testx = 0;

		// nochmal den Y-Startpuntk festlegen
		$this->SetY($this->defs['QM_START_FOOTER']);

		// feste Daten ausgeben
		if(isset($ReportsOverFooter)){

			foreach($ReportsOverFooter as $_ReportsOverFooter){
				$_output = null;

				// Wenn der Zelleninhalt mehrere Zeilen (Arrays) umfasst, werden diese in Zeilenumbrüche gewandelt
				if(is_array($_ReportsOverFooter['description'])){
					$x = 0;
					foreach($_ReportsOverFooter['description'] as $__description){
						if($x > 0){
							$_output .= "\n";
						}
					$_output .= $__description;
					$x++;
					}
				}
				else {
					$_output = $_ReportsOverFooter['description'];
				}

				// Überschrift und Daten werden zusammengefasst
				if($_ReportsOverFooter['data'][0] != ''){
					$_output .= "\n".$_ReportsOverFooter['data'][0];
				}

				// Schriftgröße festlegen wenn angegeben
				if($_ReportsOverFooter['fontsize'] != ''){
					$this->SetFont('helvetica', '', $_ReportsOverFooter['fontsize']);
				}
				else {
					$this->SetFont('helvetica', '', 8);
				}

/*
MultiCell( $w, $h, $txt, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false,
$autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false )
*/

				// Daten ausgeben

				$this->MultiCell(
					intval($_ReportsOverFooter['width']),
					intval($_ReportsOverFooter['height']),
					$_output,
					0,
					$_ReportsOverFooter['textalign'],
					false,
					2,
					$this->defs['PDF_MARGIN_LEFT'] + $_ReportsOverFooter['x'],
					$this->defs['QM_START_FOOTER'],
					true,
					0,
					0,
					true,
					intval($_ReportsOverFooter['height']),
					'T',
					1
				);

			}
		}

		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style3);

		// Flussdaten ausgeben
		foreach($ReportsFooter as $_ReportsFooter){

			// erste X-Position festlegen
			if($testx == 0){
				$x_pos = $this->defs['PDF_MARGIN_LEFT'] + floatval($_ReportsFooter['x']);
			}

//			if($testx == 2){break;}

			$_output = null;
			// Korrektur für die erste Linie
			$_ReportsFooterheight = $_ReportsFooter['height'];
			// Wenn der Zelleninhalt mehrere Zeilen (Arrays) umfasst, werden diese in Zeilenumbrüche gewandelt
			if(is_array($_ReportsFooter['description'])){
				$x = 0;
				foreach($_ReportsFooter['description'] as $__description){
					if($x > 0){
						$_output .= "|br|";
					}
				$_output .= $__description;
				$x++;
				}
			}
			else {
				$_output = $_ReportsFooter['description'];
			}

			// Schriftgröße festlegen wenn angegeben
			if($_ReportsFooter['fontsize'] != ''){
				$this->SetFont('helvetica', '', $_ReportsFooter['fontsize']);
			}
			else {
				$this->SetFont('helvetica', '', 9);
			}

			// Bezeichnung ausgeben
			$this->setCellPadding(0.25);
			$this->MultiCell(
				$_ReportsFooter['widthLabel'],
				$_ReportsFooter['height'],
				$_output,
				'',
				'L',
				false,
				0,
				$x_pos,
				$this->GetY(),
				true,
				0,
				false,
				true,
				$_ReportsFooter['height'],
				'T',
				true
			);

			if($_ReportsFooter['data'][0] == ''){
				$_data = ' ';
			}
			else {
				$_data = $_ReportsFooter['data'][0];
			}
			if($_data == '0000-00-00'){
				$_data = ' ';
			}

			// das muss muss für den allgemeinen Gebrauch noch angepasst werden
			if($_ReportsFooter['key'] == 'examiner' || $_ReportsFooter['key'] == 'supervision' || $_ReportsFooter['key'] == 'third_part'|| $_ReportsFooter['key'] == 'supervisor_company'){
				$SignatoryPosition[$_ReportsFooter['key']]['x'] = $x_pos;
				$SignatoryPosition[$_ReportsFooter['key']]['y'] = $this->GetY();
			}

			// Daten ausgeben
			$x_pos = $x_pos + $_ReportsFooter['widthLabel'];
			$this->setCellPadding(0.25);
/*
			while(mb_detect_encoding(utf8_decode($_data)) != 'ASCII') {
//				$_data = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($_data));
			}
*/
			$_data = $_data;

			$this->MultiCell(
				$_ReportsFooter['widthData'],
				$_ReportsFooter['height'],
				$_data,
				'R',
				$_ReportsFooter['textalign'],
				false,
				0,
				$x_pos,
				$this->GetY(),
				true,
				0,
				false,
				true,
				$_ReportsFooter['height'],
				'T',
				true
			);
			$x_pos = $x_pos + $_ReportsFooter['widthData'];

			// bei Zeilenumbruch
			if($_ReportsFooter['break'] == 1){
				$this->Ln();
				$x_pos = $this->defs['PDF_MARGIN_LEFT'] + floatval($_ReportsFooter['x']);
			}


		$testx++;
		}

		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style0);

		if(isset($this->RateOfFilmConsumption)){
			$this->SetFont('helvetica', '', 7);
			$this->SetY($this->defs['QM_START_FOOTER'] + 5);
			$this->text(233,$this->GetY(), __('Rate of film consumption', true));
			$this->Ln();

			foreach($this->RateOfFilmConsumption as $_key => $_RateOfFilmConsumption){
				$this->text(233,$this->GetY(), count($_RateOfFilmConsumption) . " " . __('Films', true) . " " . $_key);
				$this->Ln();
			}
		}

		if(isset($this->WeldDimensions)){
			$this->SetFont('helvetica', '', 7);
			$this->SetY($this->defs['QM_START_FOOTER'] + 5);
			$this->text(258, $this->GetY(), __('Dimension', true));
			$this->Ln();

			foreach($this->WeldDimensions as $_key => $_RateOfFilmConsumption){
				$this->text(258,$this->GetY(), (is_array($_RateOfFilmConsumption) ? count(array_unique($_RateOfFilmConsumption)) : $_RateOfFilmConsumption). 'x '.  $_key);
				$this->Ln();
			}
		}

	if(isset($this->Signature) && !empty($this->Signature)){
//var_dump($this->Signature); die();
			foreach($this->Signature as $_key => $_signature){

				if(!isset($_signature['Sign']['signatory_name'])) continue;
				if(!isset($_signature['Sign']['sign_output']['image'])) continue;

				$signatory_x = 5;
				if(isset($SignatoryPosition[$_signature['Sign']['signatory_name']]['x'])) $signatory_x = $SignatoryPosition[$_signature['Sign']['signatory_name']]['x'];
				elseif(isset($_signature['Sign']['signatory_x'])) $signatory_x = $_signature['Sign']['signatory_x'];

				if(isset($_signature['Sign']['offset_x'])) $signatory_x += $_signature['Sign']['offset_x'];

				$sinatory_offset_y = 0;
				$signatory_y = 0;
				if(isset($SignatoryPosition[$_signature['Sign']['signatory_name']]['y'])) $signatory_y = $SignatoryPosition[$_signature['Sign']['signatory_name']]['y'] - ($_signature['Sign']['height'] / 2);

				if(isset($_signature['Sign']['offset_y'])) $signatory_y += $_signature['Sign']['offset_y'];

				$this->Image(
					'@'.base64_decode($_signature['Sign']['sign_output']['image']),
					$signatory_x, // X
					$signatory_y, // Y
					$_signature['Sign']['width'], // Breite
					$_signature['Sign']['height'], // Höhe
					'png',
					$signatory_x . ' ' . $signatory_y . ' ' . $_signature['Sign']['height'] , // Link
					'', // Ausrichtung
					false, // resize
					$_signature['Sign']['dpi'], //  dpi
					'', // 'L', // Ausrichtung auf Seite
					false, // Bild als Maske
					false, // Maske oder Testumfluss glaube ich
					false, // der Rand
					true, // passt das Bild an Dimensionen bleiben
					1, // wenn das Bild nicht angezeit werden soll
					false, // Bild an Seitengröße anpassen
					false, // Alternativtext
					0 // altervatives Bild
				);
			}
		}
	}
}
