<?php
App::import('Vendor','tcpdf/tcpdf');

class XTCPDF extends TCPDF
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

  public function _getFontFormatting($item, $fonts=array()) {
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

/* //$this->xdata['revisioninfo']['adress'].
		$Y_start = 10;

		if(isset($this->xdata['revisioninfo']['logo'])){
		$this->Image(
			$this->xdata['revisioninfo']['logo'],
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
		$this->xdata['revisioninfo']['adress'],
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
		$this->xdata['revisioninfo']['contact'],
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

            $headline  = null;
            $headline = __('Revision History');
			$this->SetFont('calibri','b','20');
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
		20);
        $this->SetY($this->GetY() + 2);
	}*/

	function Footer() {


				$datafooter = $this->xsettings->{'Report'.'Pdf'}->additional->datafooter;
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
	}}

function Header() {

			$this->reportDeleted = $this->xdata->Reportnumber->delete == 'true' || $this->xdata->Reportnumber->delete == '1';
			list($r, $b, $g) = $this->xheadercolor;
			// Daten vorbereiten
			// Daten vorbereiten
			$Verfahren = $this->Verfahren;
			$ReportPDfSettings = 'ReportPdf';
			$Report[1] = 'Report'.$Verfahren.'Generally';
			$Report[2] = 'Report'.$Verfahren.'Specific';
			$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
			$ReportPdf = 'ReportPdf';
			$width = array();
			$imageArray = array();
			$imageArrayEvaluation = array();
			$lastImageHeight = 0;
			$StandartWidthLabel = 25;
			$StandartWidthData = 50;
			$cellheight = 4.5;
			$SignatorySettings = array();

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
						$place = explode(' ',$__Report->pdf->output);
						foreach($place as $_place){
							if($_place == 1){
								if($__Report->pdf->output == 1){
									$key = trim($__Report->key);

									$dataArray[$Reports][trim($__Report->pdf->sorting)]['description'] = $discriptionArray;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim($this->xdata->$Reports->$key);

									// die Breite von Label- und Datenzelle wird ermittelt
									$width = explode(' ',trim($__Report->pdf->positioning->width));
									$fontcolorlabel = $__Report->pdf->fontcolorlabel;
									$bgcolorlabel = $__Report->pdf->bgcolorlabel;
									$fontcolordata = $__Report->pdf->fontcolordata;
									$bgcolordata = $__Report->pdf->bgcolordata;
									$cellheight = trim($__Report->pdf->positioning->height);

									// Wenn ein Zelleninhalt aus der XML-Datei kommt
									if(isset($__Report->pdf->alternativvalue)){
										$alternativvalue_array = explode('|br|',trim($__Report->pdf->alternativvalue));
										if(count($alternativvalue_array) > 1){
											$alternativvalue = implode("\n",$alternativvalue_array);
										}
										else {
											$alternativvalue = trim($__Report->pdf->alternativvalue);
										}

										$dataArray[$Reports][trim($__Report->pdf->sorting)]['alternativvalue'] = $alternativvalue;
									}

									if(!isset($width[0])){$width[0] = $StandartWidthLabel;}
									if(!isset($width[1])){$width[1] = $StandartWidthData;}

									$dataArray[$Reports][trim($__Report->pdf->sorting)]['revision'] = 0;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['clear'] = 0;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['headline'] = trim($__Report->pdf->headline);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['break'] = trim($__Report->pdf->positioning->break);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['measure'] = trim($__Report->pdf->measure);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontsize'] = trim($__Report->pdf->fontsize);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdescription'] = trim($__Report->pdf->lines->description);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['textalign'] = trim($__Report->pdf->textalign);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata'] = trim($__Report->pdf->lines->data);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata_right'] = trim($__Report->pdf->lines->position->right);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['linesdata_bottom'] = trim($__Report->pdf->lines->position->bottom);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fieldtype'] = trim($__Report->fieldtype);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['mother'] = trim($__Report->pdf->mother);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['child'] = trim($__Report->pdf->child);
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['radiooption'] = $__Report->radiooption;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_label'] = $width[0];
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['width_data'] = $width[1];
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolorlabel'] = $fontcolorlabel;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolorlabel'] = $bgcolorlabel;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['fontcolordata'] = $fontcolordata;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['bgcolordata'] = $bgcolordata;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['cellheight'] = $cellheight;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['hidden'] = false;
									$dataArray[$Reports][trim($__Report->pdf->sorting)]['formatting'] = array(
										'bold' => trim($__Report->pdf->fontbold),
										'italic' => trim($__Report->pdf->fontitalic),
										'underline' => trim($__Report->pdf->fontunderline)
									);

									if(!empty($this->RevisionsList)){
										 if(isset($this->RevisionsList['overview'][trim($__Report->model)][trim($__Report->key)]) && $this->RevisionsList['overview'][trim($__Report->model)][trim($__Report->key)] == true){
											$dataArray[$Reports][trim($__Report->pdf->sorting)]['revision'] = 1;
										 }
									}

									$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = preg_replace('/\s+/',' ', $dataArray[$Reports][trim($__Report->pdf->sorting)]['data']);

									if($__Report->key == 'auftraggeber_adress') {
										$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = preg_replace('#[,|;]+#', PHP_EOL, $dataArray[$Reports][trim($__Report->pdf->sorting)]['data']);
										$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = join(PHP_EOL, array_map('trim', explode(PHP_EOL, $dataArray[$Reports][trim($__Report->pdf->sorting)]['data'])));
									}

									// Testen, ob das Feld ausgeblendet werden soll
									if(isset($__Report->pdf->hidable) && (trim($__Report->pdf->hidable) == '1' || trim($__Report->pdf->hidable) == 'x' || trim($__Report->pdf->hidable) == 'true')) {
										foreach($this->hiddenFields as $field) {
											if($field['HiddenField']['model'] == trim($__Report->model) && $field['HiddenField']['field'] == trim($__Report->key)) {
												$dataArray[$Reports][trim($__Report->pdf->sorting)]['hidden'] = true;
												break;
											}
										}
									}

									// Wenn mehrere Felder zusammengefasst werden sollen
									if($dataArray[$Reports][trim($__Report->pdf->sorting)]['child'] == 1){
										foreach($this->xsettings->$Reports as $_xsettingsMother){
											foreach($_xsettingsMother as $__xsettingsMother){
												if(trim($__Report->key) == trim($__xsettingsMother->pdf->mother)){
													$motherKey = trim($__xsettingsMother->key);
													$dataArray[$Reports][trim($__Report->pdf->sorting)]['data'] = trim(
														$dataArray[$Reports][trim($__Report->pdf->sorting)]['data']. '/' .
														trim($this->xdata->$Reports->$motherKey)
													,'/');
													$motherKey = NULL;
												}
											}
										}
									}
								}
								break;
							}
						}
					}
				}

				// nach den Bilder schauen
/*
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
*/

				if(isset($this->xsettings->$ReportPDfSettings->images) && !empty($this->xsettings->$ReportPDfSettings->images)){
					foreach($this->xsettings->$ReportPDfSettings->images->children() as $_images){
						if(trim($_images->position->area) == $Reports){

							$images = array();

							if(!empty($_images->type)) $images['type'] = trim($_images->type);
							else continue;

							if(empty($_images->file)) continue;

							if(!file_exists(Configure::read('root_folder') . DS . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'])) continue;
							else $images['file'] = Configure::read('root_folder') . DS . 'pdf_images' . DS . trim($this->xdata->Reportnumber->testingmethod_id) . DS . trim($this->xdata->Reportnumber->version) . DS . trim($_images->file) . '.' . $images['type'];

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

			$current_clear = 0;
			$xx = 0;




			$y = 0;
			$sectionHeadline = array(
					0 => trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_1->VALUE),
					1 => trim($this->xsettings->$ReportPdf->settings->QM_HEADSUBLINE_2->VALUE),
			);



			if(isset($this->QrCodeUrl)){

				$QRstyle = array(
    				'border' => 0,
    				'vpadding' => 0,
    				'hpadding' => 0,
    				'fgcolor' => array(0,0,0),
   					'bgcolor' => false,
					'module_width' => 1,
					'module_height' => 1
				);

				$this->write2DBarcode(
				$this->QrCodeUrl,
				$this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->quality,
				$this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->x,
				$this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->y,
				$this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->width,
				$this->xsettings->$ReportPdf->settings->QM_QRCODE_REPORT->height,
				$QRstyle,
				'N');
			}

			// Überschrift 1
			if(trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE) != ''){
				$this->SetFont('calibri', 'n', 14);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ReportPdf->settings->QM_HEADLINE_01->POS_X,
								$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}
			// zweite Überschrift z.B. englisch
			if(trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->VALUE) != ''){
				$this->SetFont('calibri', 'n', 10);
				$this->MultiCell(
								0,
								0,
								trim($this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->VALUE),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->POS_X,
								$this->GetY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}

                                if(trim($this->reportnumber) != ''){
				$this->SetFont('calibri', 'n', 10);
				$this->MultiCell(
								0,
								0,
								trim($this->reportnumber),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ReportPdf->settings->QM_HEADLINE_02->POS_X,
								$this->GetY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}

			// Firmenlogo
			// Firmenlogo
			if($this->printversion < 3){
				if(file_exists(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png') && $this->xdata->Testingcomp->id != 0){
					$this->Image(Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'logo.png',
					intval($this->xsettings->$ReportPdf->settings->QM_LOGO_X),
					intval($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y),
					intval($this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT),
					intval($this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH),
					'PNG', false, '', false, 300, '', false, false, 0, false, false, false);
				} else {

					$CompanyLogoFolder = Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS;

					if ($handle = opendir($CompanyLogoFolder)) {

					    while (false !== ($entry = readdir($handle))) {
									if(file_exists($CompanyLogoFolder . $entry) && filetype($CompanyLogoFolder . $entry) == 'file'){

										$LogoInfo = mime_content_type($CompanyLogoFolder . $entry);

										switch($LogoInfo){
											case 'image/svg+xml':
											$this->ImageSVG(
												$CompanyLogoFolder . $entry,
												$this->xsettings->$ReportPdf->settings->QM_LOGO_X,
												$this->xsettings->$ReportPdf->settings->QM_LOGO_Y,
												$this->xsettings->$ReportPdf->settings->QM_LOGO_WIDTH,
												$this->xsettings->$ReportPdf->settings->QM_LOGO_HEIGHT,
												'',
												'',
												'',
												0,
												false);
											break;
										}
									}
					    }

					    closedir($handle);
					}
				}

				// Zusätzliche Logos einfügen
				if(isset($this->xsettings->$ReportPdf->settings->QM_ADDITIONAL_LOGOS->LOGO)) {

					$CompanyLogoFolder = Configure::read('company_logo_folder').trim($this->xdata->Testingcomp->id).DS.'additional'.DS;

					foreach($this->xsettings->$ReportPdf->settings->QM_ADDITIONAL_LOGOS->LOGO as $_additional_logos){
						$AdditionalLogoPath = $CompanyLogoFolder . trim($_additional_logos->LOGO_NAME);

						if(!file_exists($AdditionalLogoPath)) continue;

						$LogoInfo = mime_content_type($AdditionalLogoPath);

						switch($LogoInfo){
							case 'image/svg+xml':
							$this->ImageSVG(
								$AdditionalLogoPath,
								intval($_additional_logos->POS_X),
								intval($_additional_logos->POS_Y),
								intval($_additional_logos->POS_W),
								intval($_additional_logos->POS_H),
								'',
								'',
								'',
								0,
								false);
								break;

								case 'image/png':
								$this->Image(
									$AdditionalLogoPath,
									intval($_additional_logos->POS_X),
									intval($_additional_logos->POS_Y),
									intval($_additional_logos->POS_H),
									intval($_additional_logos->POS_W),
									'PNG', false, '', false, 300, '', false, false, 0, false, false, false);
								break;
						}
					}
				}
			}

			if($this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->SHOW_ADRESS == 1){
				$Firmenadresse  = null;
				$Firmenadresse  .=  trim($this->xdata->Testingcomp->firmenname) . PHP_EOL;
				$Firmenadresse  .= 	trim($this->xdata->Testingcomp->plz) . " ";
				$Firmenadresse  .= 	trim($this->xdata->Testingcomp->ort) . PHP_EOL;
				$Firmenadresse  .= 	trim($this->xdata->Testingcomp->strasse) . PHP_EOL;
				if(trim($this->xdata->Testingcomp->telefon) != '') $Firmenadresse  .= 'Tel. ' . trim($this->xdata->Testingcomp->telefon) . ', ';
				if(trim($this->xdata->Testingcomp->telefax) != '') $Firmenadresse  .= 'Fax. ' . trim($this->xdata->Testingcomp->telefax) . PHP_EOL;
				if(trim($this->xdata->Testingcomp->email) != '') $Firmenadresse  .= trim($this->xdata->Testingcomp->email);
				if(trim($this->xdata->Testingcomp->internet) != '') $Firmenadresse  .= ', ' . trim($this->xdata->Testingcomp->internet);

				$Firmenadresse = preg_replace('/[\s,]+$/', '', $Firmenadresse);

				if($this->printversion == 3) $Firmenadresse = null;

				$this->SetFont('calibri', 'n', 7);
				$this->MultiCell(
								0,
								0,
								($Firmenadresse),
								0,
								'L',
								0,
								1,
								$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_X,
								$this->xsettings->$ReportPdf->settings->QM_FIRM_ADRESS_FORMAT->POS_Y,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
			}

			$this->setCellPaddings($this->defs['QM_CELL_PADDING_L'], $this->defs['QM_CELL_PADDING_T'], $this->defs['QM_CELL_PADDING_R'], $this->defs['QM_CELL_PADDING_B']);

			// Umrandungen
			//oben horizontal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->style1);
			//unten horizontal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
			//links vertikal
			$this->Line($this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['PDF_MARGIN_LEFT'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
			//rechts vertikal
			$this->Line($this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_TOP'], $this->defs['QM_CELL_LAYOUT_CLEAR'], $this->defs['QM_CELL_LAYOUT_LINE_BOTTOM'], $this->style1);
			$this->SetY($this->defs['QM_CELL_LAYOUT_LINE_TOP']);

			$this->SetLeftMargin($this->defs['PDF_MARGIN_LEFT']);

			$AreaStartY = array();



					$this->Line(
						$this->defs['PDF_MARGIN_LEFT'],
						$this->GetY(),
						$this->defs['QM_CELL_LAYOUT_CLEAR'],
						$this->GetY(),
						$this->style1
					);



			// mögliche Bilder einlesen


/*
pr($imageArray);
pr($SectionStartArray);
die();
*/
			$this->bodyStart[$this->getPage()] = $this->HaederEnd = $this->GetY();

		// Falls vorhanden Unterschriften ausgeben
/*
			if(Configure::read('WriteSignatory') == true){
				if(isset($this->Signature) && count($this->Signature) > 0){
					foreach($SignatorySettings as $_key => $_SignatorySettings){
						if(trim($_SignatorySettings->show) == 1){

							$signatory_key = trim($_SignatorySettings->pfad);

							if(!isset($this->Signature[$signatory_key])) continue;

							if(file_exists($this->Signature[$signatory_key]['pfad'] . $this->Signature[$signatory_key]['temp_name'])){

							$img_width = trim($_SignatorySettings->position->width);
							$img_heigth = trim($_SignatorySettings->position->height);

							if($img_width > 0 && $this->Signature[$signatory_key]['img_real_width'] < $img_width){
								$img_width = $this->Signature[$signatory_key]['img_real_width'];
							}
							if($img_heigth > 0 && $this->Signature[$signatory_key]['img_real_heigth'] < $img_heigth){
								$img_heigth = $this->Signature[$signatory_key]['img_real_heigth'];
							}

							$this->Image(
								$this->Signature[$signatory_key]['pfad'] . $this->Signature[$signatory_key]['temp_name'],
								trim($_SignatorySettings->position->x), //x
								trim($_SignatorySettings->position->y), //y
								$img_width, // width
								$img_heigth, // height
								'PNG',
								'', // Link
								'N', // Ausrichtung
								false, // resize
								$this->Signature[$signatory_key]['dpi'], //  dpi
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


							unlink($this->Signature[$signatory_key]['pfad'] . $this->Signature[$signatory_key]['temp_name']);
							$pfad = null;
							}
						}
					}
				}
			}
*/
		}
}
