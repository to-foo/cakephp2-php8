<?php
App::import('Vendor','tcpdf/tcpdf');

class XTCPDF  extends TCPDF
{
//	var $xheadertext  = 'PDF creado using CakePHP y TCPDF';
	var $xheadercolor = array(255,255,255);
//	var $xfootertext  = 'Copyright © %d XXXXXXXXXXX. All rights reserved.';
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

function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
	parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

	$this->fontlist[] = $this->addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibri.ttf', 'TrueTypeUnicode', '', 32);
	$this->fontlist[] = $this->addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrib.ttf', 'TrueTypeUnicode', '', 32);
	$this->fontlist[] = $this->addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibrii.ttf', 'TrueTypeUnicode', '', 96);
	$this->fontlist[] = $this->addTTFfont(APP.'Config'.DS.'Fonts'.DS.'calibriz.ttf', 'TrueTypeUnicode', '', 96);

}

function writeSettings($defs) {
	$this->defs = $defs;
	$this->qr = null;
	$this->xfooterfon = $defs['PDF_FONT_NAME_MAIN'];
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

			// Die Werte aus den Daten und den Setting weden zusammengeführt
			// in einem neuen Array, es wird die Höhe der Zellen berechnet
			foreach($Report as $Reports){
				foreach($this->xsettings->$Reports as $_Report){
					foreach($_Report as $__Report){

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
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['headline'] = trim($__Report->pdf->headline);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['break'] = trim($__Report->pdf->positioning->break);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['measure'] = trim($__Report->pdf->measure);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontsize'] = trim($__Report->pdf->fontsize);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdescription'] = trim($__Report->pdf->lines->description);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['textalign'] = trim($__Report->pdf->textalign);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata'] = trim($__Report->pdf->lines->data);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fieldtype'] = trim($__Report->fieldtype);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['radiooption'] = $__Report->radiooption;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_label'] = $width[0];
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_data'] = $width[1];
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolorlabel'] = $fontcolorlabel;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolorlabel'] = $bgcolorlabel;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolordata'] = $fontcolordata;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolordata'] = $bgcolordata;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['cellheight'] = $cellheight;
								}
								break;
							}
						}
					}
				}

				// nach den Bilder schauen
				if(isset($this->xsettings->$ReportPDfSettings->images)){
					foreach($this->xsettings->$ReportPDfSettings->images as $_images){
						foreach($_images as $__images){
							if($__images->position->area == $Reports){
								$___images = array();
								$___images['type'] = $__images->type;
								$___images['file'] = '../files/1/'.$this->xdata->Testingmethod->id.'/'.$__images->file.'.'.$__images->type;
								$dimension = explode(' ',$__images->dimension);
								$position = explode(' ',$__images->position->float);
								$___images['width'] = $dimension[0];
								$___images['height'] = $dimension[1];
								$___images['vertikal'] = $position[0];
								$___images['horizontal'] = $position[1];
								$imageArray[$Reports][] = $___images;
							}
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
/*
					if($datenArraySort[$_Report][$x]['break'] != 1){
						if($current_clear >= $this->defs['QM_CELL_LAYOUT_CLEAR']){
							$xx++;
							$maxCellheigth = 0;
							$current_clear = $this->defs['PDF_MARGIN_LEFT'];
						}
					}
*/
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
			$this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);
		/*
					// Logo Prüfunternehmen
					if(file_exists(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png') && $this->xdata->Testingcomp->id != 1){
						$this->Image(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png',
						$this->xsettings->$ReportPdf->settings->QM_LOGO_X,
						$this->xsettings->$ReportPdf->settings->QM_LOGO_Y,
						$this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT,
						$this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH,
						'PNG', false, '', false, '', '', false, false, 0, false, false, false);
					}
		*/
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
						$this->xsettings->$ReportPdf->settings->QM_LOGO_X + 1,
						$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y + 1,
						$this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH,
						$this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT,
						'PNG', false, '', false, '', '', false, false, 0, 'LT', false, false);
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

				$this->Image(Configure::read('company_logo_folder').DS.'1'.DS.'logo.png',
				$this->defs['PDF_MARGIN_LEFT'],
				$this->defs['PDF_MARGIN_TOP']-7,
				30,
				0,
				'PNG', false, '', false, '', '', false, false, 0, false, false, false);
			}

			// Titel
			$TestreportName = __("Report", true) . "\n" . $this->xdata->Testingmethod->verfahren;

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

			$PrueferWacker = __("examiner no. Wacker", true);
			$Wacker = "Wacker Chemie AG\n";
			$Wacker .= $this->reportnumber.PHP_EOL;
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

			// Seitenbreite gleichm��ig aufteilen
			$sectWidth = ($this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT']) / count($headerSections) - $this->defs['QM_CELL_PADDING_L'] - $this->defs['QM_CELL_PADDING_R'];

			$maxH = 0;
			// Header eintragen
			foreach($headerSections as $id=>&$section) {
				if(isset($section['fontsize'])) $this->setFontSize($section['fontsize']);
				else $this->SetFontSize(11);

				while(mb_detect_encoding(utf8_decode($section['content'])) != 'ASCII') {
//					$section['content'] = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($section['content']));
				}

				$section['content'] = utf8_encode($section['content']);
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
							if($_imageArray['type'][0] == 'eps'){
								// Wenn das Bild unterhalb eingefügt wird, muss der Y-Punkt gesetzt werden
								if($_imageArray['vertikal'] == 'T'){
									$this->ImageEps(
										$_imageArray['file'],
										0,
										$this->GetY() + 2,
										$_imageArray['height'],
										$_imageArray['width'],
										'',
										true,
										'',
										$_imageArray['horizontal'],
										0,
										false,
										false
									);
								}
							}
						}
						$lastImageHeight = ($this->getImageRBY());
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

						if(isset($datenArraySortRow[$_Report][$x][$xx]['headline']) && $datenArraySortRow[$_Report][$x][$xx]['headline'] == 1){
//							pr($___datenArraySortRow['description']);

							$this->SetFont('calibri', 'n', 10);
							$this->SetFillColor(180,180,180);
							$this->MultiCell(
								$this->defs['QM_CELL_LAYOUT_CLEAR'] - $this->defs['PDF_MARGIN_LEFT'],
								0,
								$datenArraySortRow[$_Report][$x][$xx]['description'],
								0,
								'L',
								1,
								1,
								$this->GetX(),
								$this->GetY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
								);


								// horizontale Linie
								$this->Line(
									$this->defs['PDF_MARGIN_LEFT'],
									$this->GetY(),
									$this->defs['QM_CELL_LAYOUT_CLEAR'],
									$this->GetY(),
									$this->style2
								);
							
								$this_y_zeile_top = $this->GetY();
								$this_y_zeile = $this->GetY();
								$this_y_zeile_max = $this->GetY();
								$this_x_zeile =	$this->GetX();

							continue;
						}



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
						$textalign = 'R';
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
							$discriptionData = preg_replace('/\xc2\xb0/',"\xe2\x81\xb0", $discriptionData);		// superscript 0
							//$discriptionData = preg_replace('/\xc2\xb9/',"\xc2\xb9", $discriptionData);		// superscript 1
							//$discriptionData = preg_replace('/\xc2\xb2/',"\xc2\xb2", $discriptionData);		// superscript 2
							//$discriptionData = preg_replace('/\xc2\xb3/',"\xc2\xb3", $discriptionData);		// superscript 3
							$discriptionData = preg_replace('/\xc3\xa2\xc2/',"\xe2\x81", $discriptionData);	// superscript 4-9,+-()=

							//pr($discriptionData);
							//pr(join('-', array_map(function($char){return $char.'('.dechex(ord($char)).')';}, str_split($discriptionData))));


							$this->MultiCell(
								$datenArraySortRow[$_Report][$x][$xx]['width_data'],
								$cellheight,
								$discriptionData,
								$theLines,
								'C',
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

			// mögliche Bilder einlesen
			if(isset($imageArray[$_Report])){
				foreach($imageArray[$_Report] as $_imageArray){
					if($_imageArray['type'][0] == 'eps'){
						// Wenn das Bild unterhalb eingefügt wird, muss der Y-Punkt gesetzt werden
						if($_imageArray['vertikal'] == 'B'){
							$this->ImageEps(
								$_imageArray['file'],
								0,
								$this->GetY() + 2,
								$_imageArray['height'],
								$_imageArray['width'],
								'',
								true,
								'',
								$_imageArray['horizontal'],
								0,
								false,
								false
							);
						}
					}
				}
				$lastImageHeight = ($this->getImageRBY());
				$this->SetY($lastImageHeight + 2);
				$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style1);
			}

		$this->bodyStart[$this->getPage()] = $this->HaederEnd = $this->GetY();
		}

function Footer() {
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
		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_START_FOOTER'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_START_FOOTER'], $this->style1);

		$Reports[0] = 'Report'.$this->Verfahren.'Generally';
		$Reports[1] = 'Report'.$this->Verfahren.'Specific';
		$Reports[2] = 'Report'.$this->Verfahren.'Evaluation';
		$ReportsFooter = array();
		$Signatory = array();

		foreach($Reports as $_Reports){
			foreach($this->xsettings->$_Reports as $_xsettings){
				foreach($_xsettings as $__xsettings){
					// Digitale Unterschriften sammeln
					if(isset($__xsettings->pdf->signatory->show) && $__xsettings->pdf->signatory->show == 1){
						$Signatory[] = $__xsettings->pdf->signatory;
					}

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
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata'] = trim($__xsettings->pdf->lines->data);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata_right'] = trim($__xsettings->pdf->lines->position->right);
								$ReportsFooter[trim($__xsettings->pdf->sorting)]['linesdata_bottom'] = trim($__xsettings->pdf->lines->position->bottom);



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
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontbold'] = trim($__xsettings->pdf->fontbold);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontitalic'] = trim($__xsettings->pdf->fontitalic);
								$ReportsOverFooter[trim($__xsettings->pdf->sorting)]['fontunderline'] = trim($__xsettings->pdf->fontunderline);
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
					$this->SetFontSize($_ReportsOverFooter['fontsize']);
				}
				else {
					$this->SetFontSize(8);
				}

				// Daten ausgeben
				while(mb_detect_encoding(utf8_decode($_output)) != 'ASCII') {
//					$_output = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($_output));
				}

				$_output = utf8_encode($_output);
		$this->SetCellPadding(1);
				$this->MultiCell(
					$_ReportsOverFooter['width'],
					$_ReportsOverFooter['height'],
					$_output,
					'R',
					$_ReportsOverFooter['textalign'],
					false,
					2,
					$this->defs['PDF_MARGIN_LEFT'] + $_ReportsOverFooter['x'],
					$this->defs['QM_START_FOOTER'],
					true,
					0,
					false,
					true,
					$_ReportsOverFooter['height'],
					'T',
					true
				);
			}

			$this->SetX($this->defs['PDF_MARGIN_LEFT']);
		}

		$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->GetY(), $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->GetY(), $this->style1);
		// Zeilenumbrücke einfügen
		$rows = array();
		foreach($ReportsFooter as $_ReportsFooter){
			$row[] = $_ReportsFooter;
			if($_ReportsFooter['break'] == 1){
				$rows[] = $row;
				$row = array();
			}
		}

		// Flussdaten ausgeben
		foreach($rows as $_rows){
			$this_y_zeile_top = $this->GetY();
			$line_vertical = array();
			$this_y_zeile = $this->GetY();
			$this_y_zeile_max = $this->GetY();
			$this_x_zeile =	$this->GetX();

			foreach($_rows as $__rows){

				$_data = null;

				if(trim($__rows['data'][0]) == ''){
					$_data = ' ';
				}
				else {
					$_data = trim($__rows['data'][0]);
				}
				if($_data == '0000-00-00'){
					$_data = ' ';
				}

				if($__rows['widthData'] > 0){
					$this->SetFont('calibri', 'n', 8);
					$this->SetCellPadding(1);

					$this->MultiCell(
					$__rows['widthData'],
					$__rows['height'],
					$_data,
					0,
					'L',
					0,
					1,
					$this_x_zeile,
					$this_y_zeile,
					true,
					0,
					false,
					true,
					0,
					'T',
					true
					);
				}

				// aktuelle Zeilenhöhe speichern wenn diese höher ist als max
				if($this->GetY() > $this_y_zeile_max){
					$this_y_zeile_max = $this->GetY();
				}

				$this_x = $this_x_zeile;
				$this_x_zeile = $this_x_zeile + $__rows['widthData'];

				$line_vertical[] = array(
									'x_1' => $this_x,
									'x_2' => $this_x + $__rows['widthData'],
									'right' => trim($__rows['linesdata_right']),
									'bottom' => trim($__rows['linesdata_bottom']),
								);


			}

			$this->Ln();
			$this->SetY($this_y_zeile_max - ($cellheight / 2));
			$this->SetX($this->defs['PDF_MARGIN_LEFT']);

			$this_y_zeile = $this->GetY();
			$this_y_zeile_max = $this->GetY();
			$this_x_zeile =	$this->GetX();

			// Bezeichnungen auslesen
			foreach($_rows as $__rows){
					$description = $__rows['description'];
					$this->SetFont('calibri', 'I', 6);
					$this->SetCellPadding(1);
					$description = str_replace('|br|', PHP_EOL, $description);
					$this->MultiCell(
						$__rows['widthData'],
						0,
						$description,
						0,
						'L',
						0,
						1,
						$this_x_zeile,
						$this_y_zeile,
						true,
						0,
						false,
						true,
						0,
						'T',
						true
					);

				// aktuelle Zeilenhöhe speichern wenn diese höher ist als max
				if($this->GetY() > $this_y_zeile_max){
					$this_y_zeile_max = $this->GetY();
				}

				$this_x_zeile = $this_x_zeile + $__rows['widthData'];
			}

			// Linien
			foreach($line_vertical as $_line_vertical){

				// horizontale Linie
				if($_line_vertical['bottom'] == 1){
					$this->Line(
						$_line_vertical['x_1'],
						$this_y_zeile_max,
						$_line_vertical['x_2'],
						$this_y_zeile_max,
						$this->style2
					);
				}

				// vertikale Linie
				if($_line_vertical['right'] == 1){
					$this->Line(
						$_line_vertical['x_2'],
						$this_y_zeile_top,
						$_line_vertical['x_2'],
						$this_y_zeile_max,
						$this->style2
					);
				}
			}

			$this->Ln();
			$this->SetY($this_y_zeile_max);

/*

			// aktuelle Zeilenhöhe speichern wenn diese höher ist als max
			if($this->GetY() > $this_y_zeile_max){
				$this_y_zeile_max = $this->GetY();
			}

			$this_x = $this_x_zeile;
			$this_x_zeile = $this_x_zeile + $___datenArraySortRow['width_data'];

			$line_vertical[] = array(
								'x_1' => $this_x,
								'x_2' => $this_x + $___datenArraySortRow['width_data'],
								'right' => trim($___datenArraySortRow['linesdata_right']),
								'bottom' => trim($___datenArraySortRow['linesdata_bottom']),
							);
*/
		}


		// Zusätzliche Textzeilen unter dem Footer aus XML lesen und ausgeben
		$datafooter = $this->xsettings->{'Report'.$Verfahren.'Pdf'}->additional->datafooter;
		if(isset($datafooter) && is_object($datafooter) && count($datafooter->children()) > 0) {
			$this->setFillColor(127,0,0);
			foreach($datafooter->xpath('element')  as $element) {
				$data = array(
					'text' => trim($element->text),
					'size' => intval($element->fontsize),
					'style' => preg_replace('/[^IBU]+/', '', strtoupper(trim($element->fontstyle))),
					'width' => floatval(str_replace(',','.',trim($element->position->w))),
					'x' => floatval(str_replace(',','.',trim($element->position->x))),
					'y' => floatval(str_replace(',','.',trim($element->position->y))),
					'w' => floatval(str_replace(',','.',trim($element->position->w))),
					'h' => floatval(str_replace(',','.',trim($element->position->h))),
					'break' => (bool)(preg_match('/(1|true|x)/i', trim($element->position->ln)))
				);

				$this->setFont('calibri', $data['style'], $data['size']);
				$this->Multicell($data['w'], $data['h'], $data['text'], 0, 'L', false, $data['break'] ? 1 : 0, $data['x'], $data['y'], true, 0, false, true, $data['h'], 'T', true);
				//pr($data);
			}
		}
	}
}