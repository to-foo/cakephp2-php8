<?php
App::import('Vendor','report_wkn');

// neues Objekt anlegen
$tcpdf = new WKNTCPDF('L', 'mm', 'A3', true, 'UTF-16', false);

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetPrintHeader(true);
$tcpdf->SetPrintFooter(false);

$_pdfData = reset($pdfData);
// foreach($pdfData as $_pdfData)
//{
	$reportimages = $_pdfData['reportimages'];
	$version = $_pdfData['version'];
	$Verfahren = $_pdfData['Verfahren'];
	$data = $_pdfData['data'];
	$settings = $_pdfData['settings'];
	$user = $_pdfData['user'];
	$hiddenFields = $_pdfData['hiddenFields'];
	$pdfFont = 'calibri';

	$ReportPdf = 'Report'.$Verfahren.'Pdf';
//	pr($settings->$ReportPdf->settings); die();

	$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	//pr($settings->$ReportPdf->settings->PDF_PAGE_FORMAT);
	//die Höhe der Datenzellen im Footer herausfinden, um den Seitenumbruch zu verschieben
	$settingsArray[0] = 'Report'.$Verfahren.'Generally';
	$settingsArray[1] = 'Report'.$Verfahren.'Specific';
	$settingsArray[2] = 'Report'.$Verfahren.'Evaluation';

	$PageBreakShift = 0;
	foreach($settingsArray as $_settingsArray){
		foreach($settings->$_settingsArray as $_settings){
			foreach($_settings as $__settings){
				if($__settings->pdf->output == 5 && $__settings->pdf->positioning->x != ''){
					if($__settings->pdf->positioning->height > $PageBreakShift){
						$PageBreakShift = $__settings->pdf->positioning->height;
					}
				}
			}
		}
	}

	$settingsArray = array();

	$define = array();

	$define['radiodefault'] = $this->Pdf->radiodefault;
	$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
	$define['K_BLANK_IMAGE'] = '_blank.png';
	$define['PDF_BARCODE'] = $settings->$ReportPdf->settings->PDF_BARCODE;
	$define['HEAD_MAGNIFICATION'] = floatval($settings->$ReportPdf->settings->HEAD_MAGNIFICATION);
	$define['K_CELL_HEIGHT_RATIO'] = floatval($settings->$ReportPdf->settings->K_CELL_HEIGHT_RATIO);
	$define['K_TITLE_MAGNIFICATION'] = floatval($settings->$ReportPdf->settings->K_TITLE_MAGNIFICATION);
	$define['K_SMALL_RATIO'] = $settings->$ReportPdf->settings->K_SMALL_RATIO;
	$define['K_THAI_TOPCHARS'] = $settings->$ReportPdf->settings->K_THAI_TOPCHARS;
	$define['K_TCPDF_CALLS_IN_HTML'] = $settings->$ReportPdf->settings->K_TCPDF_CALLS_IN_HTML;
	$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $settings->$ReportPdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
	$define['PDF_PAGE_FORMAT'] = $settings->$ReportPdf->settings->PDF_PAGE_FORMAT;
	$define['PDF_PAGE_ORIENTATION'] = $settings->$ReportPdf->settings->PDF_PAGE_ORIENTATION;
	$define['PDF_CREATOR'] = $settings->$ReportPdf->settings->PDF_CREATOR;
	$define['PDF_AUTHOR'] = $settings->$ReportPdf->settings->PDF_AUTHOR;
	$define['PDF_HEADER_TITLE'] = $settings->$ReportPdf->settings->PDF_HEADER_TITLE;
	$define['PDF_HEADER_STRING'] = $settings->$ReportPdf->settings->PDF_HEADER_STRING;
	$define['PDF_UNIT'] = $settings->$ReportPdf->settings->PDF_UNIT;
	$define['PDF_MARGIN_HEADER'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_HEADER);
	$define['PDF_MARGIN_FOOTER'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_FOOTER);
	$define['PDF_MARGIN_TOP'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_TOP);
	$define['PDF_MARGIN_BOTTOM'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_BOTTOM);
	$define['PDF_MARGIN_LEFT'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_LEFT);
	$define['PDF_MARGIN_RIGHT'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_RIGHT);
	$define['PDF_FONT_NAME_MAIN'] = $settings->$ReportPdf->settings->PDF_FONT_NAME_MAIN;
	$define['PDF_FONT_SIZE_MAIN'] = $settings->$ReportPdf->settings->PDF_FONT_SIZE_MAIN;
	$define['PDF_FONT_NAME_DATA'] = $settings->$ReportPdf->settings->PDF_FONT_NAME_DATA;
	$define['PDF_FONT_SIZE_DATA'] = $settings->$ReportPdf->settings->PDF_FONT_SIZE_DATA;
	$define['PDF_FONT_MONOSPACED'] = $settings->$ReportPdf->settings->PDF_FONT_MONOSPACED;
	$define['PDF_IMAGE_SCALE_RATIO'] = floatval($settings->$ReportPdf->settings->PDF_IMAGE_SCALE_RATIO);

	// Cellpadding definieren
	$define['QM_CELL_PADDING_T'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_T);
	$define['QM_CELL_PADDING_R'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_R);
	$define['QM_CELL_PADDING_B'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_B);
	$define['QM_CELL_PADDING_L'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_L);
	$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_LINE_TOP);
	$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
	$define['QM_CELL_LAYOUT_WIDTH'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_WIDTH);
	$define['QM_CELL_LAYOUT_CLEAR'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_CLEAR);
	$define['QM_IMAGE_CAPTION'] = $settings->$ReportPdf->settings->QM_IMAGE_CAPTION;

	// Seitenumbruch
	$define['QM_PAGE_BREAKE'] = $settings->$ReportPdf->settings->QM_PAGE_BREAKE - $PageBreakShift;
	$define['QM_START_FOOTER'] = $settings->$ReportPdf->settings->QM_START_FOOTER - $PageBreakShift;
	$define['QM_LINE_HEIGHT'] = $settings->$ReportPdf->settings->QM_LINE_HEIGHT;

	$define = array_map(function($elem) { if(is_array($elem)) return $elem; return trim($elem); }, $define);
	// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
	$tcpdf->endPage();
	// neue Seiteneinstellungen eintragen um neuen Header zu schreiben
	$tcpdf->writeSettings($define);
	$tcpdf->forcePrintHeaders = true;
	//$tcpdf->setPageOrientation($define['PDF_PAGE_ORIENTATION'], false, $define['PDF_MARGIN_BOTTOM']);
	$tcpdf->setPageUnit($define['PDF_UNIT']);

	// die Prüfberichtsbezeichnung
	$report = $data->Topproject->identification.'_'.$data->Reportnumber->year.'_'.$data->Reportnumber->number;
	$reportID = $data->Topproject->identification.'-'.$data->Reportnumber->year.'-'.$data->Reportnumber->number;

	// Einstellungen und Daten aus dem Controller importieren
	$tcpdf->printversion = $version;
	$tcpdf->xdata = $data;
	$tcpdf->xuser = $user;
	$tcpdf->xsettings = $settings;
	$tcpdf->hiddenFields = $hiddenFields;
	$tcpdf->Verfahren = $Verfahren;
	$tcpdf->HaederEnd = 0;
	$tcpdf->reportID = $reportID;
	$tcpdf->ReportImages = $reportimages;
	$tcpdf->SetCreator($define['PDF_CREATOR']);
	$tcpdf->SetAuthor('Wacker GmbH');
	$tcpdf->SetTitle('ZfP Prüfbericht');
	$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
	$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
	$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
	$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);
	$tcpdf->ReportPdf = $ReportPdf;
	$tcpdf->ReportEvaluation = $ReportEvaluation;
	$tcpdf->MarginLeftPlus = 0;

	if(!empty($define['PDF_BARCODE']))
	{
		$qr = (array)$define['PDF_BARCODE'];
		if(preg_match('/([12]D)\/([A-Z\+0-9,]+)/',$qr['type'], $qr['type']))
		{
			if(preg_match('/([A-Za-z]+)(.([A-Za-z\_]+))?+/', $qr['field'], $qr['field']))
			{
				if(isset($qr['fgcolor']->color)) {
					$qr['fgcolor'] = (array)$qr['fgcolor'];
				} else {
					$qr['fgcolor'] = array('color'=>array(0,0,0));
				}

				if(isset($qr['bgcolor']->color)) {
					$qr['bgcolor'] = (array)$qr['bgcolor'];
				} else {
					$qr['bgcolor'] = array('color'=>false);
				}

				if(isset($data->$qr['field'][1]->$qr['field'][3])) {
					$tcpdf->qr = array(
						'code'=>trim($data->$qr['field'][1]->$qr['field'][3]),
						'bgcolor'=>$qr['bgcolor']['color'],
						'fgcolor'=>$qr['fgcolor']['color'],
						'type'=>$qr['type']
					);
				}
			}
		}
	}

	// Linien
	$tcpdf->style0 = array('width' => 0.50, 'color' => array(0, 0, 0));
	$tcpdf->style1 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;
	$tcpdf->style2 = array('B' => array('width' => 0.1, 'color' => array(0, 0, 0)));
	$tcpdf->style3 = array('width' => 0.1, 'color' => array(0, 0, 0));
	$tcpdf->style4 = array('B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.05,
										'color' => array(0, 0, 0)
									),
								) ;
	$tcpdf->style5 = array('B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									),
								) ;
	$tcpdf->style6 = array('R' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style7 = array(	'L' => 0,
							'T' => 0,
							'R' => 0,
							'B' => 0
								) ;

	$tcpdf->style8 = array('R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									)
								) ;

	$tcpdf->style9 = array('B' => array(
							'width' => 0.15,
							'color' => array(0, 0, 0)
							)
						) ;

	$tcpdf->style10 = array('R' => array(
								'width' => 0.2,
								'color' => array(0, 0, 0)
							),
							'B' => array(
								'width' => 0.3,
								'color' => array(0, 0, 0)
							)
						) ;
	$tcpdf->style11 = array('R' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style12 = array('R' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style13 = array('B' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style14 = array('L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;
	$tcpdf->style15 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(255, 255, 255)
									),
						) ;
	$tcpdf->style16 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(255, 255, 255)
									),
						) ;
	$tcpdf->style17 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;

	$tcpdf->style18 = array('L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;
	$tcpdf->style19 = array('L' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;

	$tcpdf->styleno = array('B' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'L' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'R' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'T' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
						) ;

	// Die neuen Bezeichnungen für die Linine
	$tcpdf->LineStyle1 = array(	'width' => 0.15,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines1 = 'TBRL';

	$tcpdf->LineStyle2 = array(	'width' => 0.3,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines2 = 'TBRL';

	$tcpdf->LineStyle7 = array(	'width' => 0.15,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines7 = 'B';
	$tcpdf->LineLineThickness7 = 0.15;

	$tcpdf->LineStyle8 = array(
								'width' => 0.15,
								'cap' => 'butt',
								'join' => 'miter',
								'dash' => 0,
								'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines8 = 'BR';
	$tcpdf->LineLineThickness8 = 0.15;

	$tcpdf->LineStyle9 = array(
								'width' => 0.15,
								'cap' => 'butt',
								'join' => 'miter',
								'dash' => 0,
								'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines9 = 'TRL';
	$tcpdf->LineLineThickness9 = 0.15;

	$tcpdf->LineLines10 = 'RB';
	$tcpdf->LineLineThickness10 = 0.15;

	$tcpdf->LineLines11 = 'B';
	$tcpdf->LineLineThickness11 = 0.15;

	$tcpdf->reportnumber = trim($data->Report->identification).'/'.$this->Pdf->ConstructReportName($tcpdf->xdata);

	list($r, $b, $g) = $tcpdf->xheadercolor;

	// Daten vorbereiten
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
		foreach($settings->$Reports as $_Report){
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
				$hidden = array_search(trim($__Report->model).'.'.trim($__Report->key), $hiddenFields) !== false;
				$place = explode(' ',$__Report->pdf->output);
				foreach($place as $_place){
					if($_place == 1){
					if($__Report->pdf->output == 1){
						$key = trim($__Report->key);

						$dataArray[$Reports][trim($__Report->pdf->sorting)]['description'] = $discriptionArray;
						$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim($data->$Reports->$key);
						if(isset($__Report->pdf->child) && trim($__Report->pdf->child) != '') {
							if(isset($__Report->pdf->child_spacer)) $spacer = trim($__Report->pdf->child_spacer);
							if(empty($spacer)) $spacer = ' ';

							$datavalue = '';

							foreach(explode(' ',trim($__Report->pdf->child)) as $child) {
								$datavalue = trim($datavalue.$spacer.trim($data->$Reports->$child),$spacer);
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
	$current_clear = $define['PDF_MARGIN_LEFT'];
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

			if($datenArraySort[$_Report][$x]['break'] == 1){
				$xx++;
				$maxCellheigth = 0;
				$current_clear = $define['PDF_MARGIN_LEFT'];
			}
		}
		$xx = 0;
		}
	}

	// add a page
	$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);

	// ----------------------------------------------------------------------------
	// ---																		---
	// ---							Vordruck Start								---
	// ---																		---
	// ----------------------------------------------------------------------------

	$tcpdf->SetFillColor(230,230,230);

	// Halbe Seite ist 50% vom halben Blatt
//	$half = (($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT']) / 2 - $define['PDF_MARGIN_LEFT'] - $define['PDF_MARGIN_RIGHT']) / 2;
	$half = (($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT']) /2 - $define['PDF_MARGIN_LEFT'] ) / 2;

	// Linestyle setzen
	$tcpdf->SetLineStyle ($linestyle = array(
	    'width' => 0.3,
	    'cap' => 'butt',
	    'join' => 'miter',
	    'dash' => 0,
	    'color' => array(0,0,0)
	));

	$tcpdf->SetFont($pdfFont, '', 12);
	//$tcpdf->setFontSize(12);

	$tcpdf->Multicell($half, 6, __('Participiants'), 1, 'C', true, 1, $define['PDF_MARGIN_LEFT'] + $half + 12, $tcpdf->HaederEnd, true, 0, false, true, 6, 'M', true);
	$startY = $tcpdf->GetY();
	$tcpdf->Multicell($half / 3, 6, __('Workshop').':', 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $half + 13, $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);
	$tcpdf->Multicell($half / 3 * 2, 6, trim($data->$Report[1]->participant_workshop), 0, 'L', false, 1, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);

	// Prüfberichtsnummer einfügen
	$tcpdf->SetFont($pdfFont.'b', 'B', 16);
	$tcpdf->Multicell(187, 10, $tcpdf->reportnumber, 0, 'L', false, 0, $tcpdf->GetLeftMargin() + 12);
	$tcpdf->SetFont($pdfFont,'', 12);

	$tcpdf->Multicell($half / 3, 6, __('ZI-T-W-W').':', 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $half + 13, $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);
	$tcpdf->Multicell($half / 3 * 2, 6, trim($data->$Report[1]->participant_zitww), 0, 'L', false, 1, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);
	$tcpdf->Multicell($half / 3, 6, __('TÜV/TAW').':', 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $half + 13, $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);
	$tcpdf->Multicell($half / 3 * 2, 6, trim($data->$Report[1]->participant_tuev_taw), 0, 'L', false, 1, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);

	$tcpdf->Rect($define['PDF_MARGIN_LEFT'] + $half + 12, $startY, $half, $tcpdf->GetY() - $startY, 'LBRT', array('all'=>$linestyle));
	$tcpdf->Ln(2);

	$tcpdf->Multicell($half, 6, __('Protocol'), 1, 'C', true, 0, $define['PDF_MARGIN_LEFT'] + 12, $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);
	$tcpdf->Multicell($half, 6, __('Conducted testings'), 1, 'C', true, 2, $define['PDF_MARGIN_LEFT'] + $half + 12, $tcpdf->GetY(), true, 0, false, true, 6, 'M', true);

	$methods = array_chunk($_methods = array_keys($_pdfData['methods']), 4);
	$width = 0;

	while(($startY = $tcpdf->GetY()) < $define['QM_PAGE_BREAKE']-50) {
		// Schrift für Prüfverfahren etwas verkleinern, damit diese ins Feld passen
		$tcpdf->SetFontSize(10);
		$offset = 12;

		foreach($methods as $line) {
			$width = $width > 0 ? min($width, $half / count($line) / 3) : $half / count($line) / 3;
			for($i=0; $i<count($line); ++$i) {
				$tcpdf->rect($define['PDF_MARGIN_LEFT'] + $i*($width*3) + $offset, $tcpdf->GetY(), $width, $width);
				if($line[$i] == end($_methods)) {
					$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $i*($width*3)+$width*1.2 + $offset, $tcpdf->GetY()+$width, $define['PDF_MARGIN_LEFT'] + $i*($width*3)+$width*1.2+$width*1.5 + $offset, $tcpdf->GetY()+$width);
				} else {
					$tcpdf->Multicell($width*($_pdfData['methods'][$line[$i]] == null ? 3 : 1.5), 8, $line[$i], 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $i*($width*3)+$width*1.2 + $offset, $tcpdf->GetY(), true, 0, false, false, 8, 'M', true);
					if($_pdfData['methods'][$line[$i]] == null) $offset = $width*1.2 + $offset; else $offset = 12;
				}
			}
			$tcpdf->SetY($tcpdf->GetY() + $width + 2);
		}

		$offset = 12;
		$tcpdf->SetFontSize(12);
		$tcpdf->SetX($define['PDF_MARGIN_LEFT'] + 1 + $offset);

		$width = $tcpdf->GetStringWidth(__('Testing areas').':') + 1;
		$tcpdf->Multicell($width, 6, __('Testing areas').':', 0 , 'L', false, 0);
		$tcpdf->Line($tcpdf->GetX(), $tcpdf->GetY()+4, $half + $define['PDF_MARGIN_LEFT'] - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();
		$tcpdf->Line($tcpdf->GetX() + $offset, $tcpdf->GetY()+4, $half + $define['PDF_MARGIN_LEFT'] - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();
		$tcpdf->Line($tcpdf->GetX() + $offset, $tcpdf->GetY()+4, $half + $define['PDF_MARGIN_LEFT'] - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();

		$width = 5.5;
		$tcpdf->rect($define['PDF_MARGIN_LEFT'] + $offset, $tcpdf->GetY(), $width, $width);
		$tcpdf->Multicell($width*5.5, $width, __('TÜV / TAW'), 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $width + 1 + $offset, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);
		$tcpdf->rect($define['PDF_MARGIN_LEFT'] + $half/2 + $offset, $tcpdf->GetY(), $width, $width);
		$tcpdf->Multicell($width*5.5, $width, __('Capable person'), 0, 'L', false, 1, $define['PDF_MARGIN_LEFT'] + $half/2 + $width + 1 + $offset, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);

		$maxY = $tcpdf->GetY();

		$tcpdf->SetXY($define['PDF_MARGIN_LEFT'] + $half + 1 + $offset, $startY + 3);
		$width = $tcpdf->GetStringWidth(__('Results').':') + 1 + $offset;
		$tcpdf->Multicell($width, 6, __('Results').':', 0, 'L', false, 0);
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + $width + 1, $startY + 7, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $startY + 7);
		$tcpdf->Ln();
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + 1 + $offset, $tcpdf->GetY() + 4, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + 1 + $offset, $tcpdf->GetY() + 4, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + 1 + $offset, $tcpdf->GetY() + 4, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $tcpdf->GetY()+4);
		$tcpdf->Ln();

		$width = $tcpdf->GetStringWidth(__('Date').':') + 1 + $offset;
		$tcpdf->Multicell($width, 6, __('Date').':', 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $half + 1 + $offset);
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + $width + 1, $tcpdf->GetY() + 4, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $tcpdf->GetY() + 4);
		$tcpdf->Ln();

		$width = $tcpdf->GetStringWidth(__('Examiner').':') + 1 + $offset;
		$tcpdf->Multicell($width, 6, __('Examiner').':', 0, 'L', false, 0, $define['PDF_MARGIN_LEFT'] + $half + 1 + $offset);
		$tcpdf->Line($define['PDF_MARGIN_LEFT'] + $half + $width + 1, $tcpdf->GetY() + 4, $define['PDF_MARGIN_LEFT'] + 2*$half - 1 + $offset, $tcpdf->GetY() + 4);
		$tcpdf->Ln();

		// Rahmen um beide Textinhalte zeichnen
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT'] + $offset, $maxY);
		$tcpdf->rect($define['PDF_MARGIN_LEFT'] + $offset, $startY, $half,$maxY-$startY, 'LBRT', array('all'=>$linestyle));
		$tcpdf->rect($define['PDF_MARGIN_LEFT']+$half + $offset, $startY, $half, $maxY-$startY, 'LBRT', array('all'=>$linestyle));
		//$tcpdf->SetY($tcpdf->GetY() + 30);
	}

	// Legende und Seitentrennlinie auf die Hälfte des Blattes zeichnen
	$tcpdf->Multicell($half*2, $define['QM_PAGE_BREAKE'] - $tcpdf->GetY(), str_replace('&', ', ', urldecode(http_build_query($_pdfData['methods']))), 0, 'L', false, 0, $tcpdf->GetX(), $tcpdf->GetY()+3);
	$tcpdf->Line(2 * $half + $define['PDF_MARGIN_LEFT'] + $define['PDF_MARGIN_RIGHT'] + 10, $tcpdf->HaederEnd, 2 * $half + $define['PDF_MARGIN_LEFT'] + $define['PDF_MARGIN_RIGHT'] + 10, $define['QM_PAGE_BREAKE']);

	// ----------------------------------------------------------------------------
	// ---																		---
	// ---							Vordruck Ende								---
	// ---																		---
	// ----------------------------------------------------------------------------

	// Linken Seitenanfang auf rechte Seite des Blattes Setzen und Start der Datenübernahme
	$tcpdf->SetLeftMargin(2*$define['PDF_MARGIN_LEFT'] + 2*$half + $define['PDF_MARGIN_RIGHT']);
	$tcpdf->SetXY(2*$define['PDF_MARGIN_LEFT'] + 2*$half + $define['PDF_MARGIN_RIGHT'], $tcpdf->HaederEnd);
	$tcpdf->defs['PDF_MARGIN_LEFT'] = 2*$define['PDF_MARGIN_LEFT'] + 2*$half + $define['PDF_MARGIN_RIGHT'];

	$tcpdf->SetFont($pdfFont.'b', 'B', 16);
	$x = $tcpdf->GetX();
	$tcpdf->Multicell(187, 10, $define['PDF_HEADER_TITLE'], 0, 'C', false, 0, $x);
	$tcpdf->Multicell(187, 10, date('d.m.Y', strtotime($data->Reportnumber->modified)), 0, 'R', false, 1, $x);
//	$tcpdf->Multicell(2*$half, 10, __('for tanks, machines and steel construction'), 0, 'C', true, 1);
	$tcpdf->Multicell(187, 10, __('for tanks, machines and steel construction'), 0, 'C', false, 1);

	$startY = $tcpdf->GetY();

	// Hier beginnt die Datenausgabe
	$tcpdf->SetY($tcpdf->GetY(),true,false);
	$StartY =  $tcpdf->GetY();
	$MaxY =  array();
	$MaximalY =  0;
	$count = 0;
	$theLines =	0;

	foreach($Report as $_Report){
		$MaxY = array();
		$MaximalY = 0;
		$cellheight = 4.5;

		if(isset($datenArraySortRow[$_Report])){

		// Die Daten ausgeben
		for($x = 0; $x < count($datenArraySortRow[$_Report]); $x++){
			$heightesLineHeight = 0;

			for($xx = 0; $xx < count($datenArraySortRow[$_Report][$x]); $xx++){
				// es gibt Unterschiede in der Darstellung zwischen Objekt und Prüfdaten
				$theFillColor = false;
				$theBorderStyleData = 0;
				$tcpdf->SetTextColor(0, 0, 0);
				$tcpdf->SetFillColor(250,250,250);

				$theFillColor = true;

				// Wenn angegeben Textfarbe setzten
				if(isset($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel'])){
					$tcpdf->SetTextColor(
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[0]),
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[1]),
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolorlabel']->color[2])
					);
				}

				// Wenn angegeben hintergrundfarbe setzten
				if(isset($datenArraySortRow[$_Report][$x][$xx]['bgcolorlabel'])){
					$tcpdf->SetFillColor(
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

//						if($discription != ''){$discription .= ':';}
				$discriptionData = $datenArraySortRow[$_Report][$x][$xx]['data'];

				$MaxY[$x][$xx]['StartY'] = $tcpdf->GetY();

				// Wenn eine Schriftgröße angegeben wird
				if($datenArraySortRow[$_Report][$x][$xx]['fontsize'] != ''){
					$fontsize = $datenArraySortRow[$_Report][$x][$xx]['fontsize'];
				}
				else {
					$fontsize = 8;
				}

				//$tcpdf->SetFont('helveticaB', 'B', $fontsize);
				$tcpdf->SetFont($pdfFont.'b', 'B', $fontsize);


				if($datenArraySortRow[$_Report][$x][$xx]['cellheight'] != ''){
					$cellheight = $datenArraySortRow[$_Report][$x][$xx]['cellheight'];
				}

				if($datenArraySortRow[$_Report][$x][$xx]['linesdescription'] != ''){
					$style = 'style'.$datenArraySortRow[$_Report][$x][$xx]['linesdescription'];
					$theBorderStyleDisc = $tcpdf->$style;

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
					if(isset($tcpdf->$theStyle)){

						// Die Dicke der stärksten Linie herausfinden
						// diese muss beim Zeilenumbruch zur Y-Pos hinzugerechnet werde
						if($theLinesThickness != ''){
							if($theLinesThickness > $heightesLineHeight){
								$heightesLineHeight = $theLinesThickness;
							}
						}

						$tcpdf->SetLineStyle($tcpdf->$theStyle);
					}

					// Wenn ein Zusammensetzung der Linien vorhanden ist
					if(isset($tcpdf->$theLines)){
						$theLines =	$tcpdf->$theLines;
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


				$tcpdf->SetCellPadding(0.5);
				// Das Feld für die Bezeichnung
				if($datenArraySortRow[$_Report][$x][$xx]['width_label'] != 0){
					$tcpdf->MultiCell(
						$datenArraySortRow[$_Report][$x][$xx]['width_label'],
						$cellheight,
						$discription,
						$theLines,
						$textalign,
						true,
						0,
						$tcpdf->GetX(),
						$tcpdf->GetY(),
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
				//$tcpdf->SetFont('helvetica', 'BI', $fontsize);
				//$tcpdf->SetFont('calibri', 'BI', $fontsize);
				$tcpdf->SetFont($pdfFont.'b', 'B', $fontsize);


				// Wenn angegeben Textfarbe setzten
				if(isset($datenArraySortRow[$_Report][$x][$xx]['fontcolordata'])){
					$tcpdf->SetTextColor(
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[0]),
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[1]),
						trim($datenArraySortRow[$_Report][$x][$xx]['fontcolordata']->color[2])
					);
				}

				// Wenn angegeben hintergrundfarbe setzten
				if(isset($datenArraySortRow[$_Report][$x][$xx]['bgcolordata'])){
					$tcpdf->SetFillColor(
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
						$discriptionData = $define['radiodefault'][intval($discriptionData)];
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
					if(isset($tcpdf->$theStyle)){

						// Die Dicke der stärksten Linie herausfinden
						// diese muss beim Zeilenumbruch zur Y-Pos hinzugerechnet werde
						if($theLinesThickness != ''){
							if($theLinesThickness > $heightesLineHeight){
								$heightesLineHeight = $theLinesThickness;
							}
						}

						$tcpdf->SetLineStyle($tcpdf->$theStyle);
					}

					// Wenn ein Zusammensetzung der Linien vorhanden ist
					if(isset($tcpdf->$theLines)){
						$theLines =	$tcpdf->$theLines;
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


					$tcpdf->MultiCell(
						$datenArraySortRow[$_Report][$x][$xx]['width_data'],
						$cellheight,
						$discriptionData,
						$theLines,
						'C',
						$theFillColor,
						0,
						$tcpdf->GetX(),
						$tcpdf->GetY(),
						true,
							0,
						false,
						true,
						$cellheight,
						'M',
						true
					);
				}

				$tcpdf->startTransaction();
				$tcpdf->Ln();
				$heightesLineHeight = max($tcpdf->GetY(), $heightesLineHeight);
				$tcpdf->rollbackTransaction(true);

				$theStyle =	null;
				$theLines =	null;
				$theLinesThickness = null;
				$tcpdf->SetX($tcpdf->GetX() + 0.15,true,false);
			}
		//$tcpdf->Ln();
		$tcpdf->SetY($heightesLineHeight + 0.15,true,false);
		$heightesLineHeight = 0;
		}
			// Wenn das eingefügte Bild größer ist als die letzte Zellenhöhe
			if($lastImageHeight > $tcpdf->GetY()){
//						pr($lastImageHeight);
//						pr($tcpdf->GetY());
				$tcpdf->SetY($lastImageHeight,true,false);
			}

		}

	$count++;
	}

	$tcpdf->Line($tcpdf->GetX(), $startY, $tcpdf->GetX()+186.9, $startY);
	$tcpdf->Line($tcpdf->GetX(), $startY, $tcpdf->GetX(), $tcpdf->GetY());


	// Body ausgeben
	$tcpdf->Ln();
	$body = $this->Pdf->MakeBodyData($tcpdf);
	$this->Pdf->PrintBodyData($tcpdf, $body);

	$tcpdf->Ln();
//}

	// ----------------------------------------------------------------------------
	// ---																		---
	// ---							Datenausgabe Ende							---
	// ---																		---
	// ----------------------------------------------------------------------------

	$tcpdf->SetFillColor(230,230,230);

	/* Werkstoffnachweisliste */

	$widths =	array(0.05,			0.06,		0.14,			0.1,			0.469,			0.08,								0.09);
	$captions = array(__('Pos.'),	__('Num.'), __('Caption'),	__('Material'), __('Stamping'), trim(__('Certificate %s', ' ')),	__('BP / TÜV'));

	$tcpdf->SetFontSize(12);
	$height = 27*$tcpdf->GetStringHeight(2*$half, 'Testzeile zur Bestimmung der Höhe');

	$tcpdf->SetXY($startX = $tcpdf->GetX(), $startY = $define['QM_PAGE_BREAKE']-$height);

	$tcpdf->Multicell(2*$half * floatval(array_sum($widths)), 6, __('Material certification'), 1, 'C', true, 1, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 6, 'M', true);
	for($i=0; $i<count($widths); ++$i) {
		$tcpdf->Multicell((2*$half)*$widths[$i], 6, $captions[$i], 1, 'C', true, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 6, 'M', true);
	}
	$tcpdf->Ln();

	//while($tcpdf->GetY() < $define['QM_PAGE_BREAKE']-5) {
	for($j=0; $j<8; ++$j) {
		for($i=0; $i<count($widths); ++$i) {
			$tcpdf->Multicell((2*$half)*$widths[$i], 12, '', 1, 'C', false, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 6, 'M', true);
		}
		$tcpdf->Ln();
	}

	$tcpdf->Ln(3);

	/* Schweißnahtliste */

	$ltRb = array(
		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'T' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'B' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$lR = array(
		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
//		'T' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
//		'B' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$ltrb = array(
		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'T' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'B' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$lr = array(
		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
//		'T' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
//		'B' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$ltb = array(
		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'T' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
//		'R' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'B' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$trb = array(
//		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'T' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'B' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$tRb = array(
//		'L' => array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'T' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'R' => array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
		'B' => array('width' => 0, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))
	);

	$startX = $tcpdf->GetX();
	$startY = $tcpdf->GetY();
	$_half = ($define['QM_CELL_LAYOUT_CLEAR']-$startX) / 2;
	$tcpdf->Multicell($_half, 6, __('Welding details'), $ltRb, 'C', true, 0);
	$tcpdf->Multicell($_half, 6, __('List of welds'), $ltrb, 'C', true, 1);

	$tcpdf->Multicell($_half/2, 6, __('Welding method'), $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2, 6, __('Welding addition'), $ltRb, 'C', false, 0);
//	$tcpdf->Multicell($_half/2, 6, __('Main welds'), 1, 'C', false, 0);
//	$tcpdf->Multicell($_half/2, 6, __('Weld clip'), 1, 'C', false, 1);
	$tcpdf->Multicell($_half/2*0.4, 6, __('Weld no.'), $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, __('Welder'), $ltRb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, __('Weld no.'), $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, __('Welder'), $ltrb, 'C', false, 1);

	$tcpdf->Multicell($_half/2, 6, '', $lr, 'C', false, 0);
	$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 6, $tcpdf->GetX() + $_half/2 - 1, $tcpdf->GetY() + 6, 1);
	$tcpdf->Multicell($_half/2, 6, '', $lR, 'C', false, 0);
//	$tcpdf->Multicell($_half/2*0.4, 6, __('Weld no.'), 1, 'C', false, 0);
//	$tcpdf->Multicell($_half/2*0.6, 6, __('Welder'), 1, 'C', false, 0);
//	$tcpdf->Multicell($_half/2*0.4, 6, __('Weld no.'), 1, 'C', false, 0);
//	$tcpdf->Multicell($_half/2*0.6, 6, __('Welder'), 1, 'C', false, 1);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltRb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltrb, 'C', false, 1);

	$methods = array_map('trim', $tcpdf->xsettings->xpath($Report[1].'/welding_method/select/static/value'));
	$method_found = false;

	$data_method = trim($data->$Report[1]->welding_method);
	if(is_string($data_method)) $data_method = array_map('trim', preg_split('/[\r\n,\|]+/', $data_method));

	foreach($methods as $_method) {
		$tcpdf->Rect($tcpdf->GetX() + 1, $tcpdf->GetY(), 6, 6);
		if(($pos = array_search(trim($_method), (array)$data_method)) !== false) {
			unset($data_method[$pos]);

			$method_found = true;
			$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 1, $tcpdf->GetX() + 6, $tcpdf->GetY() + 5, 1);
			$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 5, $tcpdf->GetX() + 6, $tcpdf->GetY() + 1, 1);
		}
		$tcpdf->Multicell($_half/2, 6, '        '.$_method, $lr, 'L', false, 0);
		$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 6, $tcpdf->GetX() + $_half/2 - 1, $tcpdf->GetY() + 6, 1);
		$tcpdf->Multicell($_half/2, 6, '', $lR, 'C', false, 0);
		$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
		$tcpdf->Multicell($_half/2*0.6, 6, '', $ltRb, 'C', false, 0);
		$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
		$tcpdf->Multicell($_half/2*0.6, 6, '', $ltrb, 'C', false, 1);
	}

	$tcpdf->Rect($tcpdf->GetX() + 1, $tcpdf->GetY(), 6, 6);

/*
	if(!$method_found) {
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 1, $tcpdf->GetX() + 6, $tcpdf->GetY() + 5, 1);
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 5, $tcpdf->GetX() + 6, $tcpdf->GetY() + 1, 1);

		if(!empty($data_method)) {
			$tcpdf->Multicell($_half/2, 6, '        '.trim(join(',', $data_method)), $lr, 'L', false, 0);
		} else {
			$tcpdf->Line($tcpdf->GetX() + 8, $tcpdf->GetY() + 5.5, $tcpdf->GetX() + $_half/2 - 1, $tcpdf->GetY() + 5.5, 1);
			$tcpdf->Multicell($_half/2, 6, '', $lr, 'L', false, 0);
		}
	} else {
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 1, $tcpdf->GetX() + 6, $tcpdf->GetY() + 5, 1);
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 5, $tcpdf->GetX() + 6, $tcpdf->GetY() + 1, 1);

		$tcpdf->Multicell($_half/2, 6, '        '.trim(join(',', $data_method)), $lr, 'L', false, 0);
	}
*/

	if(!empty($data_method)) {
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 1, $tcpdf->GetX() + 6, $tcpdf->GetY() + 5, 1);
		$tcpdf->Line($tcpdf->GetX() + 2, $tcpdf->GetY() + 5, $tcpdf->GetX() + 6, $tcpdf->GetY() + 1, 1);

		$tcpdf->Multicell($_half/2, 6, '        '.trim(join(',', $data_method)), $lr, 'L', false, 0);
	} else {
		$tcpdf->Line($tcpdf->GetX() + 8, $tcpdf->GetY() + 5.5, $tcpdf->GetX() + $_half/2 - 1, $tcpdf->GetY() + 5.5, 1);
		$tcpdf->Multicell($_half/2, 6, '', $lr, 'L', false, 0);
	}

	$tcpdf->Multicell($_half/2, 6, '', $lR, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltRb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltrb, 'C', false, 1);

	$tcpdf->Multicell($_half, 6, __('Preheat'), $ltRb, 'C', true, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltRb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltrb, 'C', false, 1);

	$tcpdf->Multicell($_half/8 * 2, 6, __('Position').':', $ltb, 'L', false, 0);
	$tcpdf->Multicell($_half/8 * 2, 6, trim($data->$Report[1]->preheat_position), $trb, 'L', false, 0);
	$tcpdf->Multicell($_half/8 * 2 + 1, 6, __('Temperature').':', $ltb, 'L', false, 0);
	$tcpdf->Multicell($_half/8 * 2 - 1, 6, trim($data->$Report[1]->preheat_temperature), $tRb, 'L', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltRb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.4, 6, '', $ltrb, 'C', false, 0);
	$tcpdf->Multicell($_half/2*0.6, 6, '', $ltrb, 'C', false, 1);

	// Seite 2
	$tcpdf->SetLeftMargin($define['PDF_MARGIN_LEFT']);
	$tcpdf->defs = $define;
	$tcpdf->AddPage();

//	$tcpdf->Line(2 * $half + $define['PDF_MARGIN_LEFT'] + $define['PDF_MARGIN_RIGHT'], $tcpdf->HaederEnd, 2 * $half + $define['PDF_MARGIN_LEFT'] + $define['PDF_MARGIN_RIGHT'], $define['QM_PAGE_BREAKE']);
	$tcpdf->Line(201, $tcpdf->HaederEnd, 201, $define['QM_PAGE_BREAKE']);

	$tcpdf->Multicell(2*$half, 6, date('d.m.Y', strtotime($data->Reportnumber->modified)), 0, 'R');
	//$width = $tcpdf->GetStringWidth()
	//$tcpdf->Multicell(100, 6)

/*
	$widths = array(0.07,0.07,0.28,0.28,0.12,0.18);
	$captions = array(__('Pos.'), __('Num.'), __('Caption'), __('Material'), __('Batch no.'), __('Certificate %s', PHP_EOL.'DIN-EN10204'));
	$tcpdf->Multicell(2*$half, 8, __('Material certification'), 1, 'C', true, 1, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 8, 'M', true);
	for($i=0; $i<count($widths); ++$i) {
		$tcpdf->Multicell((2*$half)*$widths[$i], 10, $captions[$i], 1, 'C', true, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 10, 'M', true);
	}
	$tcpdf->Ln();

	while($tcpdf->GetY() < $define['QM_PAGE_BREAKE']-5) {
		for($i=0; $i<count($widths); ++$i) {
			$tcpdf->Multicell((2*$half)*$widths[$i], 8, '', 1, 'C', false, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 8, 'M', true);
		}
		$tcpdf->Ln();
	}
*/

	$tcpdf->Line($tcpdf->GetX(), $tcpdf->GetY(), $tcpdf->GetX()+2*$half, $tcpdf->GetY());
	//$tcpdf->SetFont('Calibri','U');
	$tcpdf->SetFont($pdfFont, 'U');

	$tcpdf->Multicell(2*$half, 6, __('Inhouse post').':', 0, 'L');
	//$tcpdf->SetFont('Calibri','');
	$tcpdf->SetFont($pdfFont, '');

	$widths = array(0.25, 0.25, 0.25, 0.25);
	$captions = array(__('Receiver'), __('Organiz. unit'), __('Post circle'), __('LP'));
	$_data = array($data->$Report[1]->housepost_receiver, $data->$Report[1]->housepost_unit, $data->$Report[1]->housepost_postcircle, $data->$Report[1]->housepost_lp);

	for($i=0; $i<count($widths); ++$i) {
		$tcpdf->Multicell((2*$half)*$widths[$i], 10, $captions[$i], 1, 'C', true, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 10, 'M', true);
	}
	$tcpdf->Ln();
	$tcpdf->Line($tcpdf->GetX(), $tcpdf->GetY(), $tcpdf->GetX()+2*$half, $tcpdf->GetY());

	for($i=0; $i<count($widths); ++$i) {
		$tcpdf->Multicell((2*$half)*$widths[$i], 8, trim($_data[$i]), 1, 'C', false, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, false, 8, 'M', true);
	}
	$tcpdf->Ln();
	$tcpdf->Ln();

	$tcpdf->Line($tcpdf->GetX(), $tcpdf->GetY(), $tcpdf->GetX()+2*$half, $tcpdf->GetY());
	$tcpdf->Ln();

	// Dokumentation

	//$tcpdf->SetFont('calibri','BU', 20);
	$tcpdf->SetFont($pdfFont, 'BU', 20);

	$tcpdf->Multicell(2*$half, 8, __('Documentation'), 0, 'C');

//	$tcpdf->SetFont('calibri','', 12);
	$tcpdf->SetFont($pdfFont, '', 12);

	$capt = trim($settings->{'Report'.$Verfahren.'Generally'}->factory_no->pdf->description);
	$width = $tcpdf->GetStringWidth($capt) + 2;
	$tcpdf->Multicell($width, 6, $capt, 0, 'L', false, 0);
	$tcpdf->Multicell(2*$half-$width, 6, $data->{'Report'.$Verfahren.'Generally'}->factory_no, 0, 'L', false, 1);
	$tcpdf->Ln();

	$capt = trim($settings->{'Report'.$Verfahren.'Generally'}->manufacturer->pdf->description);
	$width = $tcpdf->GetStringWidth($capt) + 2;
	$tcpdf->Multicell($width, 6, $capt, 0, 'L', false, 0);
	$tcpdf->Multicell(2*$half-$width, 6, $data->{'Report'.$Verfahren.'Generally'}->manufacturer, 0, 'L', false, 1);
	$tcpdf->Ln();

	$capt = trim($settings->{'Report'.$Verfahren.'Generally'}->description->pdf->description);
	$width = $tcpdf->GetStringWidth($capt) + 2;
	$tcpdf->Multicell($width, 6, $capt, 0, 'L', false, 0);
	$tcpdf->Multicell(2*$half-$width, 6, $data->{'Report'.$Verfahren.'Generally'}->description, 0, 'L', false, 1);
	$tcpdf->Ln();

	$tcpdf->Line($tcpdf->GetX(), $tcpdf->GetY(), $tcpdf->GetX()+2*$half, $tcpdf->GetY());
	$tcpdf->Ln();
	// Kreuzfelder Typ
	$width = 12;

	$startY = $tcpdf->GetY();
	$tcpdf->rect($tcpdf->GetX(), $tcpdf->GetY(), $width, $width);
	if(intval($data->{'Report'.$Verfahren.'Generally'}->repair)) {
		$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 1, $tcpdf->GetX()+$width - 1, $tcpdf->GetY() + $width - 1);
		$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + $width - 1, $tcpdf->GetX() + $width - 1, $tcpdf->GetY() + 1);
	}
	$capt = $settings->xpath('Report'.$Verfahren.'Generally/repair/pdf/description');
	$tcpdf->Multicell($half/2, $width, trim($capt[0],':'), 0, 'L', false, 1, $tcpdf->GetX()+$width+2, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);

	$capt = $settings->xpath('Report'.$Verfahren.'Generally/type/radiooption/value');
	for($i=0; $i<count($capt); ++$i) {
		$tcpdf->Ln();
		//if($i == 0) $startY = $tcpdf->GetY();
		$tcpdf->rect($tcpdf->GetX(), $tcpdf->GetY(), $width, $width);

		if($i == intval($data->{'Report'.$Verfahren.'Generally'}->type)) {
			$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 1, $tcpdf->GetX()+$width - 1, $tcpdf->GetY() + $width - 1);
			$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + $width - 1, $tcpdf->GetX() + $width - 1, $tcpdf->GetY() + 1);
		}

		$tcpdf->Multicell($half/2, $width, trim($capt[$i]), 0, 'L', false, 1, $tcpdf->GetX()+$width+2, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);
	}
	$endY = $tcpdf->GetY();

	$tcpdf->SetY($startY);
	$tcpdf->Rect($tcpdf->GetX() + $half + 4, $tcpdf->GetY(), $width, $width);
	$tcpdf->Multicell($half, $width, __('Filing'), 0, 'L', false, 1, $half+2*$width+6, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);

	$tcpdf->Ln();
	$_width = $tcpdf->GetStringWidth(__('Forward to').':') + 2;
	$tcpdf->Rect($tcpdf->GetX() + $half + 4, $tcpdf->GetY(), $width, $width);
	$tcpdf->Multicell($_width, $width, __('Forward to').':', 0, 'L', false, 0, $half+2*$width+6, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);
	$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 8, 2*$half + $width, $tcpdf->GetY() + 8);

	$tcpdf->Ln(2*$width);
	$_width = $tcpdf->GetStringWidth(__('Remarks').':') + 2;
	$tcpdf->Rect($tcpdf->GetX() + $half + 4, $tcpdf->GetY(), $width, $width);
	$tcpdf->Multicell($_width, $width, __('Remarks').':', 0, 'L', false, 0, $half+2*$width+6, $tcpdf->GetY(), true, 0, false, false, $width, 'M', true);
	$tcpdf->Line($tcpdf->GetX() + 1, $tcpdf->GetY() + 8, 2*$half + $width, $tcpdf->GetY() + 8);
	$tcpdf->Ln();
	$tcpdf->Line($half + 2*$width + 6, $tcpdf->GetY() + 8, 2*$half + $width, $tcpdf->GetY() + 8);
	$tcpdf->Ln();
	$tcpdf->Line($half + 2*$width + 6, $tcpdf->GetY() + 8, 2*$half + $width, $tcpdf->GetY() + 8);

	$endY = max($endY, $tcpdf->GetY());
	$tcpdf->Ln();
	$tcpdf->Line($tcpdf->GetX() + $half, $startY, $tcpdf->GetX() + $half, $endY);

//	for($i=0; $i<)
//pr($data->{'Report'.$Verfahren.'Generally'}->type);

$out = $tcpdf->Output('report_.pdf', 'S');
echo $out;
return;
