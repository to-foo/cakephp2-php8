<?php
App::import('Vendor','report_workload');
App::import('Controller', 'Examiners');

$define = array();
$ReportPdf = 'WorkloadSettings';

$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
$define['K_BLANK_IMAGE'] = '_blank.png';
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
$define['QM_PAGE_BREAKE'] = $settings->$ReportPdf->settings->QM_PAGE_BREAKE;
$define['QM_START_FOOTER'] = $settings->$ReportPdf->settings->QM_START_FOOTER;
$define['QM_LINE_HEIGHT'] = $settings->$ReportPdf->settings->QM_LINE_HEIGHT;

$tcpdf = new XTCPDF($define['PDF_PAGE_ORIENTATION'], $define['PDF_UNIT'], 'A4', true, 'UTF-8');
$tcpdf->xuser = $user;
$tcpdf->xdata = $settings;
$tcpdf->xsettings = $settings;
$tcpdf->ReportPdf = $ReportPdf;

$tcpdf->writeSettings($define);

// Linien
$tcpdf->style0 = array('width' => 0.50, 'color' => array(0, 0, 0));			

$tcpdf->SetCreator($define['PDF_CREATOR']);
$tcpdf->SetAuthor('Dekra GmbH');
$tcpdf->SetTitle('ZfP Prüfbericht');
$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);

// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
$tcpdf->endPage();
$tcpdf->AddPage();
$tcpdf->SetY($tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);


// Seiteninhalte
//pr($examiner);

// MultiCell ($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)
$tcpdf->SetFont('calibrib', 7);
//$cols = ($define['QM_PAGE_BREAKE']-$define['PDF_MARGIN_LEFT']-$define['QM_CELL_PADDING_L']-$define['QM_CELL_PADDING_R']) / 8;
$cols = array(22, 58, 43, 20, 20, 20, 20, 67);
$border = array('RB'=>array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))); 

$tcpdf->SetFillColor(240, 240, 240);
$height = $tcpdf->GetStringHeight($cols[0], __('Day'), true, true, '', $border);
$height = 7;
$tcpdf->MultiCell($cols[0], $height*2, __('Day'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[1], $height*2, __('Order'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[2], $height*2, __('Report'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[3], $height*2, __('Waiting Time Start'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[4], $height*2, __('Waiting Time End'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[5], $height*2, __('Testing Time Start'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[6], $height*2, __('Testing Time End'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->MultiCell($cols[7], $height*2, __('Remarks'), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height*2, 'M', true);
$tcpdf->ln();

$tcpdf->SetFont('calibri');
foreach($examiner['ExaminerTime'] as $time) {
	$tcpdf->SetY($tcpdf->GetY()+$define['QM_CELL_PADDING_T']);
	$testing_start = strtotime($time['ExaminerTime']['testing_time_start']);
	$testing_end = strtotime($time['ExaminerTime']['testing_time_end']);
	
	$waiting_start = isset($time['ExaminerTime']['waiting_time_start']) ? strtotime($time['ExaminerTime']['waiting_time_start']) : null;
	$waiting_end = isset($time['ExaminerTime']['waiting_time_end']) ? strtotime($time['ExaminerTime']['waiting_time_end']) : null;
	
	$auftrag = '';
	if(isset($time['Reportnumber']['Order']['auftrags_nr'])) {
		$auftrag = trim($time['Reportnumber']['Order']['auftrags_nr']);
		if(isset($time['Reportnumber']['Order']['kks'])) {
			$auftrag .= ' / '.$time['Reportnumber']['Order']['kks'];
		}
	}
	
	$reportName = '';
	if(isset($time['Reportnumber'])) {
		$reportName = @$this->Pdf->ConstructReportName($time['Reportnumber']);
	}
	$reportName = trim($reportName, '/');
	
	if($tcpdf->GetY() >= $define['QM_PAGE_BREAKE']) {
		$tcpdf->AddPage();
		$tcpdf->SetY($tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);
	}
	
	$tcpdf->MultiCell($cols[0], $height, date('d.m.Y', $testing_start), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[1], $height, $auftrag, $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[2], $height, $reportName, $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[3], $height, empty($waiting_start) ? '' : date('H:i', $waiting_start), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[4], $height, empty($waiting_end) ? '' : date('H:i', $waiting_end), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[5], $height, empty($testing_start) ? '' : date('H:i', $testing_start), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[6], $height, empty($testing_end) ? '' : date('H:i', $testing_end), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);
	$tcpdf->MultiCell($cols[7], $height, trim($time['ExaminerTime']['remarks']), $border, 'C', true, 0, $tcpdf->GetX()+$define['QM_CELL_PADDING_L'], $tcpdf->GetY(), true, 0, false, true, $height, 'M', true);	

	$tcpdf->ln();
}

// Settings für Hochformat schreiben
$define['PDF_PAGE_ORIENTATION'] = 'P';
$define['PDF_MARGIN_HEADER'] = 10;
$define['PDF_MARGIN_FOOTER'] = 10;
$define['PDF_MARGIN_TOP'] = 14;
$define['PDF_MARGIN_BOTTOM'] = 20;
$define['PDF_MARGIN_LEFT'] = 20;
$define['PDF_MARGIN_RIGHT'] = 15;

// Cellpadding definieren
$define['QM_CELL_PADDING_T'] = 0.25;
$define['QM_CELL_PADDING_R'] = 0.25;
$define['QM_CELL_PADDING_B'] = 0.25;
$define['QM_CELL_PADDING_L'] = 0.25;
$define['QM_CELL_LAYOUT_LINE_TOP'] = 10;
$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = 280;
$define['QM_CELL_LAYOUT_WIDTH'] = 180;
$define['QM_CELL_LAYOUT_CLEAR'] = 200;

// Seitenumbruch
$define['QM_PAGE_BREAKE'] = 245;
$define['QM_START_FOOTER'] = 255;
$define['QM_LINE_HEIGHT'] = 3;

$tcpdf->writeSettings($define);
$date = new DateTime();

foreach($testings as $month=>$test) {
	$tcpdf->AddPage($define['PDF_PAGE_ORIENTATION']);

	$width = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_L'] - $define['QM_CELL_PADDING_R'];
	$height = ($define['QM_CELL_LAYOUT_LINE_BOTTOM'] - $tcpdf->HeaderEnd - $define['QM_CELL_PADDING_T']) / 2;
	$date->setDate($date->format('Y'), $month, 1);
	
	if(isset($waitings->$month)) {
		$wait = $waitings->$month;
		$tcpdf->MultiCell(
			$width,
			0,
			__('Waiting time for %s', __($date->format('F')).' '.$date->format('Y')),
			0,
			'L',
			false,
			1,
			$define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_L'] + 5,
			$tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']
		);
		$tcpdf->WriteHtmlCell(
			$width,
			0,
			$define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'],
			$tcpdf->GetY()+2,
			'<img src="data:image/png;base64,'.$wait[2].'" />'
		);
	} else {
		$tcpdf->MultiCell(
			$width,
			0,
			__('No waiting time data available for %s', __($date->format('F')).' '.$date->format('Y')),
			0,
			'L',
			false,
			1,
			$define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_L'] + 5,
			$tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']
		);
	}

	$tcpdf->MultiCell(
		$width,
		0,
		__('Testing time for %s', __($date->format('F')).' '.$date->format('Y')),
		0,
		'L',
		false,
		1,
		$define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_L'] + 5,
		$tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T'] + $height
	);
	$tcpdf->WriteHtmlCell(
		$width,
		0,
		$define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'],
		$tcpdf->GetY()+2,
		'<img src="data:image/png;base64,'.$test[2].'" />'
	);
}


$tcpdf->Output(str_replace(' ', '_', __('Examiner workload %s', $examiner['Examiner']['name'])).'.pdf', 'D');