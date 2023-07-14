<?php
App::import('Vendor','report_tcpstrips');

$define = array();
$ReportPdf = 'StripSettings';

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
$tcpdf->xdata = json_decode(json_encode($data));
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

$tcpdf->AddPage();
$tcpdf->SetY($tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);

// PMC Box und Processor
$width = ($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - 40) / 2;
$tcpdf->setFillColor(255,0,0);

$tcpdf->SetFont('calibrib', 9);
$tcpdf->Multicell($width, 0, ' PMC box', 'B', 'L', false, 0);
$tcpdf->Multicell($width, 0, ' Processor', 'B', 'L', false, 1, $tcpdf->GetX() + 40);

$tcpdf->SetFont('calibri');
$tcpdf->Multicell(40, 0, ' ID No.', 0, 'L', false, 0);
$tcpdf->Multicell($width - 40, 0, $data['StripDatum']['description'], 0, 'L', false, 0, $tcpdf->GetX());
$tcpdf->Multicell(40, 0, ' Type', 0, 'L', false, 0, $tcpdf->GetX() + 40);
$tcpdf->Multicell($width - 40, 0, $data['StripDatum']['processor_type'], 0, 'L', false, 1, $tcpdf->GetX());

$tcpdf->Multicell(40, 0, ' Batch No.', 0, 'L', false, 0);
$tcpdf->Multicell($width - 40, 0, $data['StripDatum']['batch_no'], 0, 'L', false, 0, $tcpdf->GetX());
$tcpdf->Multicell(40, 0, ' F. No.', 0, 'L', false, 0, $tcpdf->GetX() + 40);
$tcpdf->Multicell($width - 40, 0, $data['StripDatum']['processor_f_no'], 0, 'L', false, 1, $tcpdf->GetX());
$tcpdf->Ln();


// Chemistry
$width = ($define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT']) / 4;

$tcpdf->SetFont('calibrib');
$tcpdf->Multicell($width, 0, ' Chemistry', 'B', 'L', false, 0);
$tcpdf->SetFont('calibri');
$tcpdf->Multicell($width, 0, 'Type', 'B', 'L', false, 0);
$tcpdf->Multicell($width, 0, 'Temperature', 'B', 'L', false, 0);
$tcpdf->Multicell($width, 0, 'Replenishment', 'B', 'L', false, 1);

$tcpdf->Multicell($width, 0, ' Developer', 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['developer_type'], 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['developer_temp'], 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['developer_replenishment'], 0, 'L', false, 1);

$tcpdf->Multicell($width, 0, ' Fixer', 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['fixer_type'], 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['fixer_temp'], 0, 'L', false, 0);
$tcpdf->Multicell($width, 0, $data['StripDatum']['fixer_replenishment'], 0, 'L', false, 1);


// Evaluation table
$tcpdf->Image('@'.$graph, $define['PDF_MARGIN_LEFT'] + 1, $tcpdf->GetY(), 0, 60);

$tcpdf->Output(str_replace(' ', '_', 'STRUCTURIX certified PMC chart.pdf'), 'D');