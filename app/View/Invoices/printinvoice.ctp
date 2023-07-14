<?php
App::import('Vendor','invoices');

// Cellpadding definieren
define('QM_CELL_PADDING_T', 1);
define('QM_CELL_PADDING_R', 1);
define('QM_CELL_PADDING_B', 1);
define('QM_CELL_PADDING_L', 1);

define('QM_CELL_LAYOUT_LINE_TOP', 10);
define('QM_CELL_LAYOUT_LINE_BOTTOM', 280);
define('QM_CELL_LAYOUT_WIDTH', 180);
define('QM_CELL_LAYOUT_CLEAR', 200);

// Seitenumbruch
define('QM_PAGE_BREAKE', 172);
define('QM_START_FOOTER', 185);
define('QM_LINE_HEIGHT', 3);

// Außenkanten
define('QM_X_L', 10);
define('QM_X_R', 285);
define('QM_Y_O', 10);
define('QM_Y_U', 200);

// neues Objekt anlegen 
$tcpdf = new XTCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// Einstellungen und Daten aus dem Controller importieren
$tcpdf->orderID = $orderID;
$tcpdf->thisOrder = $thisOrder;
$tcpdf->part = $part;
$tcpdf->InvoicesLine = $line;
$tcpdf->invoices = $invoices;
$tcpdf->totalprice = $totalprice;
$tcpdf->InvoiceWidth = $InvoiceWidth;

// Einstellungen und Daten aus dem Controller importieren

$tcpdf->SetCreator(PDF_CREATOR);
$tcpdf->SetAuthor('QM-Systems');
$tcpdf->SetTitle('Aufmaßblatt ZfP');
$tcpdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$tcpdf->setCellPaddings(QM_CELL_PADDING_L, QM_CELL_PADDING_T, QM_CELL_PADDING_R, QM_CELL_PADDING_B);

// Linien
$tcpdf->style1 = array('width' => 0.25, 'color' => array(0, 0, 0));			
$tcpdf->style2 = array('B' => array('width' => 0.15, 'color' => array(0, 0, 0)));			
$tcpdf->style3 = array('width' => 0.15, 'color' => array(0, 0, 0));			
$tcpdf->style4 = array('width' => 0.75, 'color' => array(0, 0, 0));			
$tcpdf->style5 = array('LTRB' => array('width' => 0.25, 'color' => array(0, 0, 0)));			

// set font
$tcpdf->SetFont('helvetica', 'B', 7);
// add a page
$tcpdf->AddPage();
$tcpdf->SetY($tcpdf->HaederEnd);
$tcpdf->SetX(QM_X_L);

// set font
$tcpdf->SetFont('helvetica', 'N', 8);

foreach($tcpdf->invoices as $_line){
	foreach($_line as $_key => $__line){
		
		// neue Seite
		if($tcpdf->GetY() >= QM_PAGE_BREAKE){
			$tcpdf->AddPage();
			$tcpdf->SetY($tcpdf->HaederEnd);
			$tcpdf->SetX(QM_X_L);
		}
		
		$tcpdf->MultiCell(
			$tcpdf->InvoiceWidth[$_key],
			8,
			$__line,
			$tcpdf->style5,
			'C',
			false,
			0,
			$tcpdf->GetX(),
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			8,
			'M',
			true
			);
		}
	$tcpdf->Ln();
	$tcpdf->SetX(QM_X_L);
}

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.'invoicereport_.pdf'.'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_end_flush();
set_time_limit(0);

echo $tcpdf->Output('invoicereport_.pdf', 'D');