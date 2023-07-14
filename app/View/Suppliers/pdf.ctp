<?php
App::import('Vendor','expediting');
// create new PDF document

// Cellpadding definieren
$define['QM_CELL_PADDING_T'] = floatval(0.25);
$define['QM_CELL_PADDING_R'] = floatval(0.25);
$define['QM_CELL_PADDING_B'] = floatval(0.25);
$define['QM_CELL_PADDING_L'] = floatval(0.25);
$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval(25);
$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval(280);
$define['QM_CELL_LAYOUT_WIDTH'] = floatval(260);
$define['QM_CELL_LAYOUT_CLEAR'] = floatval(280);

// Seitenumbruch
$define['QM_PAGE_BREAKE'] = floatval(190);
$define['QM_START_FOOTER'] = floatval(190);
$define['QM_LINE_HEIGHT'] = floatval(3);

// Fonts
$define['PDF_FONT_NAME_MAIN'] = 'helvetica';
$define['PDF_FONT_SIZE_MAIN'] = 9;
$define['PDF_FONT_NAME_DATA'] = 'helvetica';
$define['PDF_FONT_SIZE_DATA'] = 9;
$define['PDF_FONT_MONOSPACED'] = 'courier';
$define['PDF_IMAGE_SCALE_RATIO'] = floatval(1.25);


$tcpdf = new XTCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

$tcpdf->writeSettings($define);
$tcpdf->data = $PdfOutput;

// set document information
$tcpdf->SetAuthor('');
$tcpdf->SetTitle('');
$tcpdf->SetSubject('');
$tcpdf->SetKeywords('');

// set auto page breaks
$tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// ---------------------------------------------------------

$tcpdf->AddPage();
$tcpdf->SetY(40);
$Inch = 25.4;
$Dpi = 300;

foreach($PdfOutput['Diagramm'] as $_key => $_data){
	
	if(empty($_data)) continue;

	$Width = $Inch * $PdfOutput['DiagrammData'][$_key]['ImageDimension']['ImageWidth'] / $Dpi;
	$Hight = $Inch * $PdfOutput['DiagrammData'][$_key]['ImageDimension']['ImageHight'] / $Dpi;

	if($define['QM_CELL_LAYOUT_WIDTH'] < $Width){
		$Hight = $define['QM_CELL_LAYOUT_WIDTH'] * $Hight / $Width;
		$Width = $define['QM_CELL_LAYOUT_WIDTH'];
	}

	if(($tcpdf->GetY() + $Hight + 20) > $define['QM_PAGE_BREAKE']){
		$tcpdf->AddPage();
		$tcpdf->SetY(40);
	}

	$tcpdf->image(
		'@'.$_data,
		15,
		$tcpdf->GetY(),
		$Width,
		$Hight,
		'',
		'',
		'C'
	);
	
	$tcpdf->SetY($tcpdf->GetY() + $Hight + 20);	
}
// Close and output PDF document
// This method has several options, check the source code documentation for more information.
header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.'report.pdf'.'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

ob_start();
ob_end_flush();
set_time_limit(0);
	
$tcpdf->Output('expediting.pdf', 'D');
?>