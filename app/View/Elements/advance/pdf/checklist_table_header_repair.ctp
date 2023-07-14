<?php
$tcpdf->SetFillColor(219, 238, 241);
$tcpdf->SetFont('calibri', 'b', 10);
$tcpdf->MultiCell(
	$tcpdf->defs['QM_CELL_LAYOUT_WIDTH'],
	5,
	"Reparaturen",
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
