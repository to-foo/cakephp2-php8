<?php
	$tcpdf->SetFont('calibri', 'n', 10);
	$CellW = $tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] / 3;
 	$Startx = $tcpdf->GetX();
	$StartY = $tcpdf->GetY();
	$MaxYArray = array();
	$VerticalLines = array('X' => array(),'Y' => $StartY);

	$tcpdf->MultiCell(
		$CellW,
		5,
		__($key,true),
		0,
		'L',
		0,
		1,
		$Startx,
		$StartY,
		true,
		0,
		false,
		false,
		5,
		'T',
		true
	);

	$MaxYArray[] = $tcpdf->GetY();
	$VerticalLines['X'][] = $Startx;
	$Startx += 	$CellW;

	$tcpdf->MultiCell(
		$CellW,
		5,
		__('nicht vorgesehen',true),
		0,
		'L',
		0,
		1,
		$Startx,
		$StartY,
		true,
		0,
		false,
		false,
		0,
		'T',
		true
	);

	$MaxYArray[] = $tcpdf->GetY();
	$VerticalLines['X'][] = $Startx;
	$Startx += 	$CellW;

$tcpdf->setY(max($MaxYArray));

$tcpdf->Line($tcpdf->defs['PDF_MARGIN_LEFT'], $tcpdf->getY(), $tcpdf->defs['QM_CELL_LAYOUT_CLEAR'], $tcpdf->getY(), $tcpdf->style3);

foreach($VerticalLines['X'] as $_key => $_value) $tcpdf->Line($_value, $VerticalLines['Y'], $_value, $tcpdf->getY(), $tcpdf->style3);

?>
