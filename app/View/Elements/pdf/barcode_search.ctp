<?php
if(Configure::check('BarcodeSearchScanner') == false) return false;
if(Configure::read('BarcodeSearchScanner') == false) return false;

$style = array(
	'border' => false,
	'hpadding' => '0',
	'vpadding' => '0',
	'fgcolor' => array(0,0,0),
	'bgcolor' => false, //array(255,255,255),
	'text' => false,
	'font' => 'helvetica',
	'fontsize' => 8,
	'stretchtext' => 4
	);
	
$tcpdf->write1DBarcode($SecID, 'C39', 1, $Style['y'], 85, 5, 0.4, $style, $Style['align']);
?>