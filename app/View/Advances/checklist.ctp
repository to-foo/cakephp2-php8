<?php
App::import('Vendor','advance/report');

$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetProtection($permissions, '', null, 0, null);
//pr($xml);

$define = array();

foreach($xml->ReportPdf->settings->children() as $_key => $_xml){
	if(count($_xml) == 0) $define[$_key] = trim($_xml);
	if(count($_xml) > 0) $define[$_key] = ($_xml);
}

$tcpdf->endPage();
$tcpdf->writeSettings($define);
$tcpdf->setPageUnit($define['PDF_UNIT']);
$tcpdf->SetCreator($define['PDF_CREATOR']);
$tcpdf->SetAuthor('MBQ Qualitätssicherung');
$tcpdf->SetTitle('ZfP Prüfbericht');
$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);
$tcpdf->PageStartY = $tcpdf->defs['QM_CELL_LAYOUT_LINE_TOP'];
// Linienstyle
$tcpdf->style1 = array('width' => 0.3, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('T' => array('width' => 0.1,'color' => array(0, 0, 0)),'B' => array('width' => 0.1,'color' => array(0, 0, 0)));
$tcpdf->style3 = array('width' => 0.1, 'color' => array(0, 0, 0));

if(isset($Logo)) $tcpdf->logosvg = $Logo;
if(isset($LogoAdditional)) $tcpdf->logoadditional = $LogoAdditional;
if(isset($Advance)) $tcpdf->Advance = $Advance;

$tcpdf->AddPage();
$tcpdf->setY($tcpdf->PageStartY);
$tcpdf->SetFont('calibri', 'n', 10);

echo $this->element('advance/pdf/checklist_table',array('tcpdf' => $tcpdf));

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="report_.pdf"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

ob_start();
ob_end_flush();
set_time_limit(0);

echo $tcpdf->Output('report_.pdf', 'D');
