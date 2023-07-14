<?php
App::import('Vendor','statistics_results');

$define = array();
$ReportPdf = 'searchPdf';

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
$tcpdf->xuser = CakeSession::read('Auth.user');
$tcpdf->xdata = $settings;
$tcpdf->xsettings = $settings;
$tcpdf->ReportPdf = $ReportPdf;

$tcpdf->writeSettings($define);

// Linien
$tcpdf->style0 = array('width' => 0.50, 'color' => array(0, 0, 0));

$tcpdf->SetCreator($define['PDF_CREATOR']);
$tcpdf->SetAuthor('Wacker GmbH');
$tcpdf->SetTitle('ZfP Statistik');
$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);

// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
$tcpdf->endPage();
$tcpdf->AddPage();
$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);

// Seiteninhalte
// MultiCell ($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)
$tcpdf->SetFont('calibrib','N',8);
$border = array('RB'=>array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$tcpdf->SetFillColor(240, 240, 240);

//pr($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_R'] - $define['QM_CELL_PADDING_L'] - 0.2);
//pr($DiagrammImages);
	foreach($DiagrammImages as $_key => $_data){

		if(!isset($_data['image'])) continue;
		if(empty($_data['image'])) continue;

		if($tcpdf->GetY() + $_data['info']['height'] > trim($define['QM_PAGE_BREAKE'])){
			$tcpdf->AddPage();
			$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);
		}

		$tcpdf->MultiCell(0, 8, $_data['headline'], 0, 'L', false, 1, $define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.5, '', true, 0, false, true, 8, 'M');

		$tcpdf->image(
			'@'.$_data['image'],
			$define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.5,
			$tcpdf->GetY(),
			$_data['info']['width'],
			$_data['info']['height'],
			'',
			'',
			'C'
		);

		$tcpdf->SetY($tcpdf->GetY() + $_data['info']['height'] + 5);
		/*
pr($tcpdf->GetY());		
pr($_data['info']['height']);
pr($tcpdf->GetY() + $_data['info']['height']);		
pr('-----');
*/		
	}

$tcpdf->Output(str_replace(' ', '_', __('Search results')).'.pdf', 'D');
?>