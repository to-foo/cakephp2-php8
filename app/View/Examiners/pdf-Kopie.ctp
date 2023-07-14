<?php
App::import('Vendor','report_blank');

// neues Objekt anlegen 
$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-16', false);

$define = array();
$define['radiodefault'] = $this->Pdf->radiodefault;
$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
$define['K_BLANK_IMAGE'] = '_blank.png';

$define['PDF_MARGIN_HEADER'] = 5.0;
$define['PDF_MARGIN_FOOTER'] = 10.0;
$define['PDF_MARGIN_TOP'] = 14.0;
$define['PDF_MARGIN_BOTTOM'] = 18.0;
$define['PDF_MARGIN_LEFT'] = 15.0;
$define['PDF_MARGIN_RIGHT'] = 15.0;

// Cellpadding definieren
$define['QM_CELL_PADDING_T'] = 0.25;
$define['QM_CELL_PADDING_R'] = 0.25;
$define['QM_CELL_PADDING_B'] = 0.25;
$define['QM_CELL_PADDING_L'] = 0.25;
$define['QM_CELL_LAYOUT_LINE_TOP'] = 25.0;
$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = 280.0;
$define['QM_CELL_LAYOUT_WIDTH'] = 180.0;
$define['QM_CELL_LAYOUT_CLEAR'] = 200.0;

// Seitenumbruch
$define['QM_PAGE_BREAKE'] = 250;
$define['QM_START_FOOTER'] = 259;
$define['QM_LINE_HEIGHT'] = 3;

$tcpdf->writeSettings($define);

// Linien
$tcpdf->style0 = array('width' => 0.4, 'color' => array(0, 0, 0));
$tcpdf->style1 = array('width' => 0.1, 'color' => array(0, 0, 0));

$tcpdf->border0 = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetMargins(trim($define['PDF_MARGIN_LEFT']),trim($define['PDF_MARGIN_TOP']), trim($define['PDF_MARGIN_RIGHT']));
$tcpdf->SetCellPadding(1);

$tcpdf->SetLeftMargin($define['PDF_MARGIN_LEFT']);

$tcpdf->title = __($Examiner['Examiner']['name']);
$tcpdf->AddPage();
$tcpdf->SetXY($define['PDF_MARGIN_LEFT'], $tcpdf->HeaderEnd);
$tcpdf->SetFont('calibri', '', 8);
$tcpdf->SetFillColor(210,210,210);

// Header ausgeben
$tcpdf->SetFillColor(170,170,170);
$tcpdf->setLineStyle($tcpdf->style0);
$tcpdf->SetFont('calibri', '', 10);

// http://bio.mq.edu.au/Tools/tcpdf/doc/com-tecnick-tcpdf/TCPDF.html#methodMultiCell
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)

/*
Hans MustermannName
MBQ QualitätssicherungFirma
123456Personal-Nr.
1999-12-01Geburtsdatum
HettstedtWohnort
Mittelstraße 14aStraße
*/

$h = 6;
$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['name'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="name"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);
$tcpdf->SetXY($x2, $y);

$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['working_place'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="working_place"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);
$tcpdf->SetXY($x2, $y);

$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['da_no'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="da_no"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);
$tcpdf->Ln();
$tcpdf->Ln();

$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['date_of_birth'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="date_of_birth"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);
$tcpdf->SetXY($x2, $y);

$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['place'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="place"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);
$tcpdf->SetXY($x2, $y);

$x = $tcpdf->GetX();
$y = $tcpdf->GetY();
$tcpdf->SetFont(null, null, 10);
$tcpdf->Multicell(185/3, $h, $Examiner['Examiner']['street'], 0, 'L', 0, 0, $x, $y, true, 0, false, true, $h, 'B', true);
$x2 = $tcpdf->GetX();
$tcpdf->SetFont(null, null, 6);
$tcpdf->Multicell(185/3, 3, trim(reset($settings['settings']->xpath('Examiner/*[key="street"]/discription/'.$locale))), 0, 'L', 0 , 0, $x, $y+6, true, 0, false, true, 3);

foreach($Examiner['Certificate'] as $_certificate) {
	$tcpdf->setFont('calibrib', null, 8);
	$tcpdf->Ln();
	$tcpdf->Ln();
	$tcpdf->Multicell(
		185, $h,
		$_certificate['Certificate']['sector'].' '.$_certificate['Certificate']['certificat'].' '.$_certificate['Certificate']['third_part'].' '._('level').' '.$_certificate['Certificate']['level'],
		0, 'L', 0, 1);
	
	$tcpdf->setFont('calibri');
	$tcpdf->Multicell(40, $h, trim(reset($settingsCertificateData['settings']->xpath('CertificateData/*[key="certified_date"]/discription/'.$locale))).':', 0, 'L', 0, 0);
	$tcpdf->Multicell(60, $h, date('Y-m-d',strtotime($_certificate['CertificateData']['certified_date'].' + '.$_certificate['CertificateData']['renewal_in_year'].'years')), 0, 'L', 0, 1);
}
//pr($Examiner); die();

$tcpdf->Ln();
$tcpdf->SetLineStyle($tcpdf->style1);

//pr($Examiner);
//die();

$name = str_replace(' ', '_', preg_replace('/[^a-z ]+/', '', strtolower($Examiner['Examiner']['name'])));
header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'.$name.'.pdf"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_end_flush();
set_time_limit(0);
echo $tcpdf->Output($name, 'D');
