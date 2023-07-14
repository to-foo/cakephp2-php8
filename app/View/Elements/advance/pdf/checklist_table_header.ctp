<?php
	$tcpdf->SetFillColor(219, 238, 241);
$tcpdf->SetFont('calibri', 'b', 10);
	$tcpdf->MultiCell(
	$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
	5,
	$headlabel,
	$tcpdf->style2,
	'L',
	1,
	0,
	$tcpdf->GetX(),
	$tcpdf->GetY(),
	true,
	0,
	false,
	false,
	5,
	'T',
	true
);

$tcpdf->MultiCell(
	$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
	5,
	"geplant",
	$tcpdf->style2,
	'L',
	1,
	0,
	$tcpdf->GetX(),
	$tcpdf->GetY(),
	true,
	0,
	false,
	false,
	5,
	'T',
	true
);

$tcpdf->MultiCell(
	$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'] / 3,
	5,
	"geprüft",
	$tcpdf->style2,
	'L',
	1,
	1,
	$tcpdf->GetX(),
	$tcpdf->GetY(),
	true,
	0,
	false,
	false,
	5,
	'T',
	true
);
?>
