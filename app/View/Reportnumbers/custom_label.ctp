<?php
$label = $settings->$ReportPdf->settings->QM_WELDLABEL;

App::import('Vendor','report_label');

// neues Objekt anlegen 
$tcpdf = new XTCPDF($label->format->format, 'mm', array(trim($label->format->width), trim($label->format->height)), true, 'UTF-16', false);

$tcpdf->style1 = array('width' => 0.1, 'color' => array(0, 0, 0));

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetMargins(trim($label->format->margin_left),trim($label->format->margin_top), trim($label->format->margin_right));

$tcpdf->AddPage();
$tcpdf->SetFont('calibri', '', 8);
$tcpdf->SetFillColor(210,210,210);

$logo_w = 0;

// Horizontal top
if($label->format->border == 1 || strpos(strtolower(trim($label->format->border)), 't') !== false) {
	$tcpdf->Line(
		trim($label->format->margin_left), 
		trim($label->format->margin_top), 
		trim($label->format->height) - trim($label->format->margin_right), 
		trim($label->format->margin_top), 
		$tcpdf->style1
	);
}

// Horizontal bottom
if($label->format->border == 1 || strpos(strtolower(trim($label->format->border)), 'b') !== false) {
	$tcpdf->Line(
		trim($label->format->margin_left), 
		trim($label->format->width) - trim($label->format->margin_bottom), 
		trim($label->format->height) - trim($label->format->margin_right), 
		trim($label->format->width) - trim($label->format->margin_bottom), 
		$tcpdf->style1
	);
}

// Vertical left
if($label->format->border == 1 || strpos(strtolower(trim($label->format->border)), 'l') !== false) {
	$tcpdf->Line(
		trim($label->format->margin_left), 
		trim($label->format->margin_top), 
		trim($label->format->margin_left), 
		trim($label->format->width) - trim($label->format->margin_bottom), 
		$tcpdf->style1
	);
}

// Vertical right
if($label->format->border == 1 || strpos(strtolower(trim($label->format->border)), 'r') !== false) {
	$tcpdf->Line(
		trim($label->format->height) - trim($label->format->margin_right), 
		trim($label->format->margin_top), 
		trim($label->format->height - trim($label->format->margin_right)), 
		trim($label->format->width) - trim($label->format->margin_bottom), 
		$tcpdf->style1
	);
}

if(trim($label->format->logo) == 1) {
	if(file_exists(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png') && $report['Testingcomp']['id'] != 0){
		$img = $this->Image->get(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png', false, array('transparency'=>0));
		
		$size = getimagesizefromstring($img);
		$tcpdf->Image('@'.$img,
		isset($label->format->logo_x) ? trim($label->format->logo_x) : 48,
		isset($label->format->logo_y) ? trim($label->format->logo_y) : 0,
		isset($label->format->logo_h) ? trim($label->format->logo_h) : 8.2,
		isset($label->format->logo_w) ? trim($label->format->logo_w) : 0,
		'PNG', false, '', false, '', '', false, false, 0, false, false, false);
	}
}
	
	
if(trim($label->format->adress) == 1) {
	$Firmenadresse  = null;
	if(trim($report['Testingcomp']['firmenname']) != '') $Firmenadresse  .=  trim($report['Testingcomp']['firmenname']) . "\n";
	if($label->format->show_company_adress) {
		if(trim($report['Testingcomp']['plz']) != '') $Firmenadresse  .= 	trim($report['Testingcomp']['plz']) . " ";
		if(trim($report['Testingcomp']['ort']) != '') $Firmenadresse  .= 	trim($report['Testingcomp']['ort']) . "\n";
		if(trim($report['Testingcomp']['strasse']) != '') $Firmenadresse  .= 	trim($report['Testingcomp']['strasse']) . "\n";
		if(trim($report['Testingcomp']['telefon']) != '') $Firmenadresse  .= 	"Tel. " . trim($report['Testingcomp']['telefon']) . "\n";
//		if(trim($report['Testingcomp']['telefax']) != '') $Firmenadresse  .= 	"Fax. " . trim($report['Testingcomp']['telefax']) . "\n";
		if(trim($report['Testingcomp']['internet']) != '') $Firmenadresse  .= 	trim($report['Testingcomp']['internet']) . "\n";
//		if(trim($report['Testingcomp']['email']) != '') $Firmenadresse  .= 	trim($report['Testingcomp']['email']) . "\n";
	}

	$tcpdf->SetFont('calibri', 'N', $label->format->show_company_adress_fontsize);
	$tcpdf->MultiCell(
		0,
		0,
		($Firmenadresse),
		0,
		'L',
		0,
		0,
		trim($label->format->show_company_adress_x),
		trim($label->format->show_company_adress_y),
		true,
		0,
		false,
		true,
		0,
		'T',
		true
	);	
}

if(isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->show) && $settings->$ReportPdf->settings->QM_QRCODE_WELD->show == 1){

	if(Configure::check('QrCodeWeldlabelAdresse')){
		$QRurl = Configure::read('QrCodeWeldlabelAdresse') . $this->request->projectvars['reportnumberID'] . '/' . $this->request->projectvars['evalId'];
	} else {
		$QRurl = $this->Html->url('/', true);
	}

	
	$QRstyle = array(
    	'border' => 0,
    	'vpadding' => 0,
    	'hpadding' => 0,
    	'fgcolor' => array(0,0,0),
   		'bgcolor' => false,
		'module_width' => 1,
		'module_height' => 1
	);
	
	$width = isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->width) ? $settings->$ReportPdf->settings->QM_QRCODE_WELD->width : 5;
	$height = isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->height) ? $settings->$ReportPdf->settings->QM_QRCODE_WELD->height : 5;
	
	$tcpdf->write2DBarcode(
			$QRurl, 
			isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->quality) ? $settings->$ReportPdf->settings->QM_QRCODE_WELD->quality : 'QRCODE,M', 
			isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->x) ? $settings->$ReportPdf->settings->QM_QRCODE_WELD->x : ($label->format->width - $label->format->margin_right - $width), 
			isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->y) ? $settings->$ReportPdf->settings->QM_QRCODE_WELD->y : $label->format->margin_top, 
			$width, 
			$height, 
			$QRstyle, 
			'N');

	if(isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->infotext)){

		$tcpdf->SetFont('calibri', 'I', 7);
		$tcpdf->Multicell(
			0,
			0,
			trim($settings->$ReportPdf->settings->QM_QRCODE_WELD->infotext),
			0,
			'L',
			false,
			0,
			trim($settings->$ReportPdf->settings->QM_QRCODE_WELD->x) - 1,
			trim($settings->$ReportPdf->settings->QM_QRCODE_WELD->y) + trim($settings->$ReportPdf->settings->QM_QRCODE_WELD->height),
			false,
			0,
			false,
			false,
			5,
			'M',
			true
		);	
	}
}


foreach($label->items as $item) {
	if(trim($item->item) == 'free_field') continue;
	if(!isset($this->request->data[trim($item->model)][trim($item->item)])) continue;

	if(isset($this->request->data[trim($item->model)][trim($item->item)])) $_data = $this->request->data[trim($item->model)][trim($item->item)];
	else $_data = '-';

	$caption = trim($item->caption);
	if(empty($caption)) $caption = trim($settings->{$item->model}->{$item->item}->pdf->description);

	$caption = preg_replace('/[ :]+$/', '', $caption).':';
	$caption = ($caption);
	if($label->format->caption_below == 1) {
	
	} else {
		$tcpdf->SetFillColor(249,249,249);
		$tcpdf->SetFont('calibri', 'B', $item->fontsize);
		
		$tcpdf->Multicell(
			trim($item->captionwidth),
			trim($item->captionheight),
			trim($caption),
			0,
			isset($item->textalign) ? trim($item->textalign): 'L',
			false,
			0,
			trim($item->x),
			trim($item->y),
			false,
			0,
			false,
			false,
			trim($item->captionheight),
			$item->valign,
			true
		);	
		
		$fontweight = 'N';
		if($item->fontweight) $fontweight = $item->fontweight;
		
		$tcpdf->SetFont('calibri', $fontweight, $item->fontsize);
		$tcpdf->Multicell(
			trim($item->width),
			trim($item->height),
			$_data,
			0,
			$item->textalign,
			false,
			0,
			trim($item->x) + trim($item->captionwidth),
			trim($item->y),
			false,
			0,
			false,
			false,
			trim($item->height),
			$item->valign,
			true
		);
	}
}

//die();
header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'. 'custom_label.pdf' .'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_end_flush();
set_time_limit(0);

echo $tcpdf->Output('custom_label.pdf', 'D');