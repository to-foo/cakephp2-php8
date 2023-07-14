<?php
App::import('Vendor','report');
// neues Objekt anlegen
$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-16', false);

$tcpdf->SetAutoPageBreak(false, 0);

foreach($pdfData as $_pdfData)
{
	$reportimages = $_pdfData['reportimages'];
	$version = $_pdfData['version'];
	$Verfahren = $_pdfData['Verfahren'];
	$data = $_pdfData['data'];
	$settings = $_pdfData['settings'];
	$user = $_pdfData['user'];
	$changes = $_pdfData['revChanges'];

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

	// Seitenumbruch
	$define['QM_PAGE_BREAKE'] = $settings->$ReportPdf->settings->QM_PAGE_BREAKE - $PageBreakShift;
	$define['QM_START_FOOTER'] = $settings->$ReportPdf->settings->QM_START_FOOTER - $PageBreakShift;
	$define['QM_LINE_HEIGHT'] = $settings->$ReportPdf->settings->QM_LINE_HEIGHT;

	// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
	$tcpdf->endPage();
	// neue Seiteneinstellungen eintragen um neuen Header zu schreiben
	$tcpdf->writeSettings($define);
	//$tcpdf->setPageOrientation($define['PDF_PAGE_ORIENTATION'], false, $define['PDF_MARGIN_BOTTOM']);
	$tcpdf->setPageUnit($define['PDF_UNIT']);

	// die Prüfberichtsbezeichnung
	$report = $data->Topproject->identification.'_'.$data->Reportnumber->year.'_'.$data->Reportnumber->number;
	$reportID = $data->Topproject->identification.'-'.$data->Reportnumber->year.'-'.$data->Reportnumber->number;

	// Einstellungen und Daten aus dem Controller importieren
	if(isset($settings->$ReportPdf->settings->QM_QRCODE_REPORT->show) && $settings->$ReportPdf->settings->QM_QRCODE_REPORT->show == 1){
/*
	$tcpdf->QrCodeUrl = $this->Html->url('/', true) . 'reportnumbers/view/'.
				trim($data->Reportnumber->topproject_id).'/'.
				trim($data->Reportnumber->equipment_type_id).'/'.
				trim($data->Reportnumber->equipment_id).'/'.
				trim($data->Reportnumber->order_id).'/'.
				trim($data->Reportnumber->report_id).'/'.
				trim($data->Reportnumber->id);
*/
	$tcpdf->QrCodeUrl = 'http://192.168.10.155:8080/qm_systems/qmsystems/demo/reportnumbers/view/'.
				trim($data->Reportnumber->topproject_id).'/'.
				trim($data->Reportnumber->equipment_type_id).'/'.
				trim($data->Reportnumber->equipment_id).'/'.
				trim($data->Reportnumber->order_id).'/'.
				trim($data->Reportnumber->report_id).'/'.
				trim($data->Reportnumber->id);

	}
	
	$tcpdf->printversion = $version;
	$tcpdf->xdata = $data;
	$tcpdf->xchanges = $changes;
	$tcpdf->xuser = $user;
	$tcpdf->xsettings = $settings;
	$tcpdf->Verfahren = $Verfahren;
	$tcpdf->HaederEnd = 0;
	$tcpdf->reportID = $reportID;
	$tcpdf->ReportImages = $reportimages;
	$tcpdf->SetCreator($define['PDF_CREATOR']);
	$tcpdf->SetAuthor('MBQ Qualitätssicherung');
	$tcpdf->SetTitle('ZfP Prüfbericht');
	$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
	$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
	$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
	$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);
	$tcpdf->ReportPdf = $ReportPdf;
	$tcpdf->ReportEvaluation = $ReportEvaluation;
	$tcpdf->MarginLeftPlus = 0;

	// Filmverbrauch nach Filmegröße auslesen
	if($Verfahren == 'Rt')
		$tcpdf->RateOfFilmConsumption = $this->Pdf->RateOfFilmConsumption($data);

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
	$tcpdf->style0 = array('width' => 0.4, 'color' => array(0, 0, 0));
	$tcpdf->style1 = array('width' => 0.3, 'color' => array(0, 0, 0));
	$tcpdf->style2 = array('width' => 0.1, 'color' => array(0, 0, 0));
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


	$Reports = 'Report'.$tcpdf->Verfahren.'Evaluation';

	// Die Werte aus den Daten und den Setting weden zusammengeführt
	// in einem neuen Array, es wird die Höhe der Zellen berechnet
	$ReportsHaed[0] = $this->Pdf->MakeHaederData($tcpdf,2); if(empty($ReportsHaed[0])) unset($ReportsHaed[0]);
	$ReportsHaed[1] = $this->Pdf->MakeHaederData($tcpdf,3); if(empty($ReportsHaed[1])) unset($ReportsHaed[1]);

	$ReportsBody = $this->Pdf->MakeBodyData($tcpdf);

	// das Reportsarray  nochmal neu sortieren
	$ReportsHaedSort = array();

	$ReportsData = array();
	foreach($ReportsHaed as $x=>$val) {
		foreach($val as $_ReportsHaed){
			$ReportsHaedSort[$x][] = $_ReportsHaed;
		}

		$ReportsData[$x] = $this->Pdf->MakeEvolutionData($tcpdf,$ReportsHaed[$x]);
	}

	/*
	for($x = 0; $x < count($ReportsHaed); $x++){
		foreach($ReportsHaed[$x] as $_ReportsHaed){
			$ReportsHaedSort[$x][] = $_ReportsHaed;
		}
	}

	// Die Prüfergebisse werden aus dem Array in ein eigenens Array übertragen
	for($x = 0; $x < count($ReportsHaed); $x++){
		$ReportsData[$x] = $this->Pdf->MakeEvolutionData($tcpdf,$ReportsHaed[$x]);
	}
	// Die Auswertungsdaten müssen nach Naht und  Nahtabschnitt sortiert werden
	for($x = 0; $x < count($ReportsHaed); $x++){
	}

	 */


	// Die Nahtaufteilung soll nur bei Rt-Prüfung stattfinden
	if($Verfahren == 'Rt'){
		$sortingWelds = $this->Pdf->WeldSorting($ReportsData);
	}
	else{
		$sortingWelds = $ReportsData;
	}

	$tcpdf->reportnumber = 'Prüfbericht-Nr./report-no '.$this->Pdf->ConstructReportName($tcpdf->xdata);

	// set font
	// add a page
	$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
	$tcpdf->SetY($tcpdf->HaederEnd);

	// nur für PT und MT
	// Die zweite Zeile der Messpunkte soll nur bei Fehlern erscheinen

	if(count($sortingWelds) > 0 && ($tcpdf->Verfahren == 'Pt' || $tcpdf->Verfahren == 'Mt') && $tcpdf->printversion == 1){
		reset($sortingWelds);
		foreach($sortingWelds[key($sortingWelds)] as $_key =>$_sortingWelds){
			$ShowArea2 = 0;

			// Fehlerbeschreibung löschen, wenn weder Nacharbeit noch Ausbessern angekreuzt sind
			$displayErrors = array_filter(
								array_map(
									function($elem) {
										return
											array_search($elem['key'], array('result_02', 'result_03')) !== false
											&& (trim($elem['data'])=='true' || trim($elem['data'])=='1');
									},
									$_sortingWelds
								)
							);

			if(empty($displayErrors)) unset($sortingWelds[1][$_key]);
			/*
			// Für Fehlerfreie Nähte die Fehlerbeschreibung löschen
			foreach($_sortingWelds as $__key => $__sortingWelds){
				if($__sortingWelds['key'] == 'result_01' && ($__sortingWelds['data'] == 'true' || $__sortingWelds['data'] == '1')){
					unset($sortingWelds[1][$_key]);
				}
			}
			*/
		}
	}

	//pr($sortingWelds[1][0]);
	//Anfang Datenhaeder
	// Haedline der Ergebnisse
	$tcpdf->SetFont('helveticaB', 'B', 7);
	// Datenhaeder Daten ausgeben
	//$reportHeadCount = 0;

	//foreach($ReportsHaed as $x=>$val) {
	//for($x = 0; $x < count($ReportsHaed); $x++){
		// Wenn Daten vorhanden sind
		if(isset($val) && count($val) > 0){
		//if(count($sortingWelds) > 0){

			$fontStart = 'helvetica';
			$fontStart = 'calibri';
			$fontweightStart = 'N';
			$fontheigthStart = 8;
			$font = 'calibri';
			$fontweight = 'N';
			$fontheigth = 7;
			$tcpdf->SetFont($font, $fontweight, $fontheigth);
			$startY = $tcpdf->GetY();
			$nextY = 0;
			$maxY = array();
			$lineArray = array();
			$CellHeight = 6;
			$CellHeightData = 5;
			$foundWelds = array();
			foreach($sortingWelds as $key => $ReportsData){

				// Wenn nix da ist wir die Schleife abgebrochen
				if(count($ReportsData) == 0){
					break;
				}

				@$this->Pdf->PrintEvolutionHaeder($tcpdf,$ReportsHaedSort[$key],$key);

				// wenn das Rohdatenprotokoll gedruck wird werden noch ein paar leere Zeilen eingefügt
				if($tcpdf->printversion == 2){
					$reportEmptyCells = array();
					$datenrows = count($ReportsData);

					// Das letzte Datenarray wird kopiert und mehrmals am Schluss des Datenarrys eingefügt
					foreach($ReportsData as $keyFull => $_ReportsFullData){
						for($x = $datenrows; $x < $datenrows + 4; $x++){
							$ReportsData[$x] = $_ReportsFullData;
						}

					}
				}

			$ReportsDataCount = 1;

			$tcpdf->SetX($define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus);

			$this->Pdf->WeldStatistic($ReportsData);

			foreach($ReportsData as $_ReportsKey=>$_ReportsData) {

				$filterWeld = array_reduce(
					array_filter(
						array_map(
							function($elem) {
								return array_search($elem['key'], array('result_02', 'result_03')) !== false
								&& array_search(trim($elem['data']), array('true','1')) !== false;
							},
							$_ReportsData
						)
					),
					function($prev, $curr) {return $prev||$curr;},
					false
				);

				$dataShort = join('|', array_map(function($elem) {return $elem['key'].':'.trim($elem['data']);}, $_ReportsData));

				if($filterWeld && $key==0 && array_search($dataShort, $foundWelds) !== false) continue;

				$foundWelds[] = $dataShort;

					//if($key == 0 && $tcpdf->Verfahren == 'Rfa'){
					if(
						$_ReportsKey == 0
						&& isset($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header)
						&& (
							trim($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header) == '1'
							|| strtolower(trim($tcpdf->xsettings->$ReportPdf->settings->use_first_evaluation_as_header)) == 'true'
						)
					) {
						continue;
					}

					foreach($_ReportsData as $_key => $__ReportsData){
						$_data = trim($__ReportsData['data']);

						if($_data == '' && $version == 1){
							$_data = ' ';
						}
						// beim Rohdatenprotokoll wird der Strich weggelassen
						if($_data == '' && $version == 2){
							$_data = ' ';
						}
						// in den zusätzlich einfügten Zeilen des Rohdatenprotokolls sollen keine Werte erscheinen
						if($_data != '' && $version == 2 && $key >= $datenrows){
							$_data = ' ';
						}

						// Schriftformatierung holen
						list($fontstyle, $fontname) = $this->Pdf->GetFontFormatting(array('formatting'=>array(
							'bold' => trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontbold),
							'italic' => trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontitalic),
							'underline' => trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontunderline),
						)));

						// bei einer Checkbox muss der Wert umgewandelt werden
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->fieldtype))
						{
							if($tcpdf->printversion == 2) {
								$_data = '';
							} else {
								$_data = trim($_data);
								switch(strtolower(trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->fieldtype)))
								{
									case 'checkbox':
										if(empty($_data) || $_data == 'false') $_data = '';
										else $_data = 'X';
										break;

									case 'radio':
										$radiooptions = $this->Pdf->radiodefault;
										if(
											!empty($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->radiooption)
											&& count($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->radiooption->value) > 0
										)
										{
											$radiooptions = array();

											foreach($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->radiooption->value as $_radiooptions){
												array_push($radiooptions, trim($_radiooptions));
											}
										}

										$_data = $radiooptions[(int)$_data];
										break;
								}
							}
						}

						/*
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->fieldtype) && trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->fieldtype) == 'checkbox'){

							if($_data == 'false'){
								$_data = '';
							}
							if($_data == '0'){
								$_data = '';
							}
							if($_data == 'true'){
								$_data = 'X';
							}
							if($_data == '1'){
								$_data = 'X';
							}
						}
						*/

						// Wenn ein Linienstyle vorhanden ist
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->lines->data) && $tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->lines->data != ''){

							$LineStyle = 'style' . trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->lines->data);
						}
						else {
							$LineStyle = 'style6';
						}

						// Wenn ein Sonderfarbe vorhanden ist
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontcolordata) && $tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontcolordata != ''){
							$tcpdf->SetTextColor (
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontcolordata->color[0]),
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontcolordata->color[1]),
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontcolordata->color[2])
									);
						}
						else {
							$tcpdf->SetTextColor (0,0,0);
						}

						// Wenn ein Hintergrundfarbe vorhanden ist
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->bgcolorlabel) && $tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->bgcolorlabel != ''){
							$tcpdf->SetFillColor (
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->bgcolorlabel->color[0]),
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->bgcolorlabel->color[1]),
									trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->bgcolorlabel->color[2])
									);
						}
						else {
							$tcpdf->SetFillColor (255,255,255);
						}

						// Wenn ein Fontstärke vorhanden ist
						if(isset($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontsize) && $tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontsize != ''){
							$fontheigth = trim($tcpdf->xsettings->$ReportEvaluation->$__ReportsData['key']->pdf->fontsize);
						}
						else {
							$fontheigth = $fontheigthStart;
						}

						while(mb_detect_encoding(utf8_decode($_data)) != 'ASCII') {
							$_data = utf8_decode(ConnectionManager::getDataSource('default')->setUTF8Encoding($_data));
						}

						$_data = utf8_encode($_data);

						$tcpdf->SetFont($fontname[1], $fontstyle[1], $fontheigth);
						$tcpdf->MultiCell(
							$__ReportsData['width'],
							$CellHeightData,
							$_data,
							$tcpdf->$LineStyle,
							'L',
							false,
							0,
							$tcpdf->GetX(),
							$tcpdf->GetY(),
							true,
							0,
							false,
							true,
							$CellHeightData,
							'T',
							true
						);
						$tcpdf->SetFont($font, $fontweight, $fontheigth);

						// X-Positionen aufzeichen für die vertikalen Linien
						$lineArray[] = $tcpdf->GetX();
					}

					$tcpdf->Ln();
					$tcpdf->SetX($define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus);

					// auf Seitenumbruch testen
					if($tcpdf->GetY() >= $define['QM_PAGE_BREAKE']){


						// Sind noch Datenzeilen vorhanden wird die neue Seite hinzugefügt
						// wenn nicht, wird abgebrochen
						if($ReportsDataCount == count($ReportsData)){
	//						$tcpdf->Ln();
							$tcpdf->SetY($tcpdf->GetY(),true,false);
							break;
						}
						elseif($ReportsDataCount < count($ReportsData)){

							$tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);
							$tcpdf->Ln();
							$tcpdf->SetY($tcpdf->GetY() + $nextY,true,false);

							$tcpdf->AddPage();

							$BreakKey = intval($key);
							//$BreakKey++;

							$tcpdf->SetY($tcpdf->HaederEnd);

							//if($BreakKey < count($ReportsData)){
								$headers = isset($ReportsHaedSort[$BreakKey]) ? $ReportsHaedSort[$BreakKey] : $ReportsHaedSort[$BreakKey % count($ReportsHaedSort)]; //reset($ReportsHaedSort);
								$this->Pdf->PrintEvolutionHaeder($tcpdf, $headers ,isset($ReportsHaedSort[$BreakKey]) ? $BreakKey : $BreakKey % count($ReportsHaedSort));
							//}

							// Die letzte Linie muss per Hand gezeichnet werden
							$tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);

							$startY = $tcpdf->GetY();
							$nextY = 0;

						}
					}
				$ReportsDataCount++;
				}

				$x++;
				//$reportHeadCount++;
			}

			if(trim($tcpdf->xsettings->EvolutionImageHeight) > $tcpdf->GetY()){
				$tcpdf->SetY(trim($tcpdf->xsettings->EvolutionImageHeight));
			}

			$tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), $tcpdf->style1);
		}
	//}

	// z.B. die Bemerkungen
	$this->Pdf->PrintBodyData($tcpdf,$ReportsBody);

	if($_pdfData['allow_children'])
		$this->Pdf->PrintAttachedReports($this, $tcpdf, $pdfData);

	//$tcpdf->Multicell($tcpdf->getX(), $tcpdf->getY(), 'Test');
	// Wenn Bilder vorhanden sind werden diese eins pro Seite ausgegeben
	if(count($tcpdf->ReportImages) > 0){
		foreach($tcpdf->ReportImages as $_ReportImages){

	//		$imagepfad  = '../files/'.$tcpdf->xdata->Topproject->id.'/images/'.$tcpdf->xdata->Reportnumber->id;
			$imagepfad = ROOT . DS . 'app' .DS . 'files' . DS . $tcpdf->xdata->Reportnumber->topproject_id . DS . 'images' .DS . $tcpdf->xdata->Reportnumber->id;

/*
			if(file_exists($imagepfad. DS .$_ReportImages['Reportimage']['name'])){
				$imageinfo = getimagesize($imagepfad. DS .$_ReportImages['Reportimage']['name']);
			}
*/
			if(!((bool)$img = $this->Image->get($imagepfad. DS .$_ReportImages['Reportimage']['name'], false, array('quality' => 50,'width' => 2000)))) {
				$this->log('Bild '.$imagepfad. DS .$_ReportImages['Reportimage']['name'].' nicht gefunden.');
				continue;
			}

			$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
			$tcpdf->SetY($tcpdf->HaederEnd);


			$imageW = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - 10;
			$imageh = $define['QM_PAGE_BREAKE'] - $tcpdf->GetY() - 5;

			$tcpdf->Image(
				//$imagepfad. DS .$_ReportImages['Reportimage']['name'],
				'@'.$img,
				$define['PDF_MARGIN_LEFT'] + 5,
				$tcpdf->GetY() + 5,
				$imageW , // Breite
				$imageh, // Höhe
				'JPEG',
				'', // Link
				'M', // Ausrichtung
				false, // resize
				150, //  dpi
				'C', // Ausrichtung auf Seite
				false, // Bild als Maske
				false, // Maske oder Testumfluss glaube ich
				'LTRB', // der Rand
				true, // passt das Bild an Dimensionen bleiben
				false, // wenn das Bild nicht angezeit werden soll
				true, // Bild an Seitengröße anpassen
				false, // Alternativtext
				0 // altervatives Bild 
			); 

			// Die Höhe des Bildes holen um den Text darunter zusetzen
			$lastImageHeight = ($tcpdf->getImageRBY());

			if($_ReportImages['Reportimage']['discription'] != ''){
				$startX = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'];
				$tcpdf->MultiCell($startX,0,$_ReportImages['Reportimage']['discription'],0,'L',false,0,20,$lastImageHeight,true,0,false,true,0,'T',true);
			}
		}
	}
}

header('Content-Type: application/pdf');

header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.'report_'.$report.'.pdf'.'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_end_flush();
set_time_limit(0);
	
echo $tcpdf->Output('report_'.$report.'.pdf', 'D');