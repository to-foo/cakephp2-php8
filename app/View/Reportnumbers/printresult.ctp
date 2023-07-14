<?php
$label = $settings->$ReportPdf->settings;

App::import('Vendor','report_searchresult');
// neues Objekt anlegen 
$tcpdf = new XTCPDF(trim($label->PDF_PAGE_ORIENTATION), trim($label->PDF_UNIT), trim($label->PDF_PAGE_FORMAT), true, 'UTF-16', false);
$tcpdf->ReportPdf = $ReportPdf;
$tcpdf->xsettings = $settings;

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

// Descriptions
$define['QM_PAGE_DESCRIPTION'] = $settings->$ReportPdf->settings->QM_PAGE_DESCRIPTION;
$define['QM_REPORT_DESCRIPTION'] = $settings->$ReportPdf->settings->QM_REPORT_DESCRIPTION;

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

$tcpdf->writeSettings($define);

$fields = array();
foreach($settings->xpath('fields/*[pdf]') as $field) {
	if(trim($field->pdf->output) != 1 && strtolower(trim($field->pdf->output)) != 'yes') continue;
	
	$fields[trim($field->pdf->sorting)] = array(
		'width' => trim($field->pdf->width) != '' ? trim($field->pdf->width) : 50,
		'caption' => trim($field->pdf->caption) != '' ? trim($field->pdf->caption) : '',
		'align' => trim($field->pdf->textalign) != '' ? trim($field->pdf->textalign) : 'L',
		'model' => trim($field->model),
		'field' => trim($field->pdf->display) ? trim($field->pdf->display) : (trim($field->output) != '' ? trim($field->output) : (trim($field->option) != '' ? trim($field->option) : trim($field->key))),
	);
}
ksort($fields);
//pr($fields); die();

// Linien
$tcpdf->style0 = array('width' => 0.4, 'color' => array(0, 0, 0));
$tcpdf->style1 = array('width' => 0.1, 'color' => array(0, 0, 0));

$tcpdf->border0 = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetMargins(trim($label->format->margin_left),trim($label->format->margin_top), trim($label->format->margin_right));
$tcpdf->SetCellPadding(1);

$tcpdf->SetLeftMargin($define['PDF_MARGIN_LEFT']);
$tcpdf->AddPage();
$tcpdf->SetXY($define['PDF_MARGIN_LEFT'], $tcpdf->HeaderEnd);
$tcpdf->SetFont('calibri', '', 8);
$tcpdf->SetFillColor(210,210,210);

// Header ausgeben
$tcpdf->SetFillColor(170,170,170);
$tcpdf->setLineStyle($tcpdf->style0);
$tcpdf->SetFont('calibri', '', 10);
foreach($fields as $field) {
// http://bio.mq.edu.au/Tools/tcpdf/doc/com-tecnick-tcpdf/TCPDF.html#methodMultiCell
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
	$tcpdf->Multicell($field['width'], 6, $field['caption'], 'TRBL', 'C', true, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 1, false, true, 6);
}

$tcpdf->Ln();
$tcpdf->SetLineStyle($tcpdf->style1);

foreach($reports as $report) {
	foreach($fields as $field) {
		$matches = array();
		if(preg_match_all('/%(.*?)%/', $field['field'], $matches)) {
			foreach($matches[1] as $replace) {
				$_fields = explode('.', $replace, 2);
				if(count($_fields) == 1) { array_unshift( $_fields, $field['model']); }
				
				$field['field'] = preg_replace('/%'.$replace.'%/', $report[$_fields[0]][$_fields[1]], $field['field']);
			}
			$output = $field['field'];
		} else {
			$output = $report[$field['model']][$field['field']];
		}
		
		$tcpdf->Multicell($field['width'], 5, $output, 'RBL', $field['align'], false, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 0, false, true, 5, 'T', true);
	}

	if($tcpdf->GetY() >= $define['QM_PAGE_BREAKE']) {
		$tcpdf->AddPage();
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT'], $tcpdf->HeaderEnd);
		foreach($fields as $field) {
			$tcpdf->Multicell($field['width'], 6, $field['caption'], 'TRBL', 'C', true, 0, $tcpdf->GetX(), $tcpdf->GetY(), true, 1, false, true, 6);
		}
	}
	$tcpdf->Ln();
}
//die();

//pr($reports); die();

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'. 'report_label_.pdf' .'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_start();
ob_end_flush();
set_time_limit(0);
echo $tcpdf->Output('search_results', 'D');
