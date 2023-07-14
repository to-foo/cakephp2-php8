<?php
$label = $settings->$ReportPdf->settings->QM_WELDLABEL;

App::import('Vendor','report_label');

// neues Objekt anlegen
$tcpdf = new XTCPDF($label->format->format, 'mm', array(trim($label->format->width), trim($label->format->height)), true, 'UTF-16', false);

$tcpdf->style1 = array('width' => 0.1, 'color' => array(0, 0, 0));

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetMargins(trim($label->format->margin_left),trim($label->format->margin_top), trim($label->format->margin_right));
//$tcpdf->SetCellPadding(1);

foreach($printdata as $_printdata)
{

extract($_printdata);

$tcpdf->AddPage();
$tcpdf->SetFont('calibri', '', 8);
$tcpdf->SetFillColor(210,210,210);

$Style['x'] = 3;
$Style['y'] = 0;
$Style['h'] = 5;
$Style['w'] = 80;
$Style['align'] = 'N';

echo $this->element('pdf/barcode_search',array('tcpdf' => $tcpdf,'Style' => $Style,'SecID' => $SecID));

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

		$tcpdf->Image(
		  '@'.$img,
		  isset($label->format->logo_x) ? trim($label->format->logo_x) : 48,
		  isset($label->format->logo_y) ? trim($label->format->logo_y) : 0,
		  isset($label->format->logo_h) ? trim($label->format->logo_h) : 8.2,
		  isset($label->format->logo_w) ? trim($label->format->logo_w) : 0,
		  'PNG',
		  false,
		  '',
		  false,
		  150,
		  '',
		  false,
		  false,
		  0,
		  false,
		  false,
		  false
		);
	} else {

		$CompanyLogoFolder = Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'black'.DS;

		if ($handle = opendir($CompanyLogoFolder)) {

				while (false !== ($entry = readdir($handle))) {
						if(file_exists($CompanyLogoFolder . $entry) && filetype($CompanyLogoFolder . $entry) == 'file'){

							$LogoInfo = mime_content_type($CompanyLogoFolder . $entry);

							switch($LogoInfo){
								case 'image/svg+xml':

								$tcpdf->ImageSVG(
									$CompanyLogoFolder . $entry,
									isset($label->format->logo_x) ? trim($label->format->logo_x) : 48,
									isset($label->format->logo_y) ? trim($label->format->logo_y) : 0,
									isset($label->format->logo_w) ? trim($label->format->logo_w) : 0,
									isset($label->format->logo_h) ? trim($label->format->logo_h) : 8.2,
									'',
									'',
									'',
									0,
									false);

								break;
							}
						}
				}
				closedir($handle);
			}
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
/*
if(isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->show) && $settings->$ReportPdf->settings->QM_QRCODE_WELD->show == 1){
	if(Configure::check('QrCodeWeldlabelAdresse')){
		$QRurl = Configure::read('QrCodeWeldlabelAdresse') . bin2hex(Security::rijndael($this->request->projectvars['reportnumberID'], Configure::read('SignatoryHash'), 'encrypt'));
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
*/
foreach($label->items as $item) {
	if(trim($item->item) == 'free_field') continue;
	if(!isset($data[trim($item->model)][trim($item->item)])) continue;

	if(isset($data[trim($item->model)][trim($item->item)]) != '') $_data = $data[trim($item->model)][trim($item->item)];
	else $_data = '-';

	if(trim($item->item) == 'reportnumber_id'){
		// Prüfberichtsnummer erstellen
		// TODO: (später mit Format aus XML)
		//$_data = $this->Pdf->constructReportName($report, true);
//		CakeLog::write('debug', print_r($data, true));

		$_data =$report['Report']['identification'].' '.$report['Reportnumber']['year'] . '-' . $report['Reportnumber']['number'];
	}

	if(empty($item->caption)) $caption = trim($settings->{$item->model}->{$item->item}->pdf->description);
	else $caption = trim($item->caption);

	if($label->format->caption_below == 1) {

	} else {
		$tcpdf->SetFont('calibri', 'B', $item->fontsize);
		$caption = preg_replace('/[ :]+$/', '', $caption).':';
		if(empty($caption)) $caption = ' ';

		$tcpdf->Multicell(
			trim($item->captionwidth),
			trim($item->captionheight),
			trim($caption),
			0,
			$item->textalign,
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

if(trim($label->format->film_number->show) == 1 && isset($_printdata['film_count'])) {
	if($label->format->caption_below == 1) {

	} else {
		$tcpdf->SetFont('calibri', 'B', $label->format->film_number->fontsize);
		$caption = rtrim(trim($label->format->film_number->caption), ':').':';

		$tcpdf->Multicell(
			trim($label->format->film_number->captionwidth),
			trim($label->format->film_number->captionheight),
			trim($caption),
			0,
			$label->format->film_number->textalign,
			false,
			0,
			trim($label->format->film_number->x),
			trim($label->format->film_number->y),
			false,
			0,
			false,
			false,
			trim($label->format->film_number->captionheight),
			$label->format->film_number->valign,
			true
		);

		$tcpdf->SetFont('calibri', 'N', $label->format->film_number->fontsize);
		$tcpdf->Multicell(
			trim($label->format->film_number->width),
			trim($label->format->film_number->height),
			$_printdata['film_count'],
			0,
			$label->format->film_number->textalign,
			false,
			0,
			trim($label->format->film_number->x) + trim($label->format->film_number->captionwidth),
			trim($label->format->film_number->y),
			false,
			0,
			false,
			false,
			trim($label->format->film_number->height),
			$label->format->film_number->valign,
			true
		);
	}
}

if(trim($label->format->show_result->show) == 1) {
	if($label->format->caption_below == 1) {

	} else {
		$_data = null;
		$x = 0;

		foreach($result as $_key => $_result){
			if(isset($resultlist[trim($_result)])){
				if($x > 0) $_data .= ' / ';
				$_data .= count($resultlist[trim($_result)]).' x '.trim($_result);
				$x++;
			}
		}

		$tcpdf->SetFont('calibri', 'B', $label->format->show_result->fontsize);
		$caption = rtrim(trim($label->format->show_result->caption), ':').':';

		$tcpdf->Multicell(
			trim($label->format->show_result->captionwidth),
			trim($label->format->show_result->captionheight),
			trim($caption),
			0,
			$label->format->show_result->textalign,
			false,
			0,
			trim($label->format->show_result->x),
			trim($label->format->show_result->y),
			false,
			0,
			false,
			false,
			trim($label->format->show_result->captionheight),
			$label->format->show_result->valign,
			true
		);

		$tcpdf->SetFont('calibri', 'N', $label->format->show_result->fontsize);
		$tcpdf->Multicell(
			trim($label->format->show_result->width),
			trim($label->format->show_result->height),
			$_data,
			0,
			$label->format->show_result->textalign,
			false,
			0,
			trim($label->format->show_result->x) + trim($label->format->film_number->captionwidth),
			trim($label->format->show_result->y),
			false,
			0,
			false,
			false,
			trim($label->format->show_result->height),
			$label->format->show_result->valign,
			true
		);
	}
	}
}

//die();

if(isset($return_show) && $return_show === true){

	$out = $tcpdf->Output('report_.pdf', 'S');
	$string = base64_encode($out);

	echo $string;

	return;

}

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="'. 'report_label_'.$data['ReportRtEvaluation']['description'].'.pdf' .'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

ob_start();
ob_end_flush();
set_time_limit(0);

echo $tcpdf->Output('report_label_'.$data['ReportRtEvaluation']['description'].'.pdf', 'D');
