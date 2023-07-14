<?php
$label = $settings->$ReportPdf->settings->QM_WELDLABEL;

App::import('Vendor','report_label');

// neues Objekt anlegen 
$tcpdf = new XTCPDF('L', 'mm', array(trim($label->format->width), trim($label->format->height)), true, 'UTF-16', false);

$tcpdf->style1 = array('width' => 0.1, 'color' => array(0, 0, 0));

$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetMargins(trim($label->format->margin_left),trim($label->format->margin_top), trim($label->format->margin_right));
$tcpdf->SetCellPadding(1);

$tcpdf->AddPage();
$tcpdf->SetFont('calibri', '', 8);
$tcpdf->SetFillColor(210,210,210);

// Horizontal top
$tcpdf->Line(
	trim($label->format->margin_left), 
	trim($label->format->margin_top), 
	trim($label->format->width) - trim($label->format->margin_right), 
	trim($label->format->margin_top), 
	$tcpdf->style1
);

// Horizontal bottom
$tcpdf->Line(
	trim($label->format->margin_left), 
	trim($label->format->height) - trim($label->format->margin_bottom), 
	trim($label->format->width) - trim($label->format->margin_right), 
	trim($label->format->height) - trim($label->format->margin_bottom), 
	$tcpdf->style1
);

// Vertical left
$tcpdf->Line(
	trim($label->format->margin_left), 
	trim($label->format->margin_top), 
	trim($label->format->margin_left), 
	trim($label->format->height) - trim($label->format->margin_bottom), 
	$tcpdf->style1
);

// Vertical right
$tcpdf->Line(
	trim($label->format->width) - trim($label->format->margin_right), 
	trim($label->format->margin_top), 
	trim($label->format->width - trim($label->format->margin_right)), 
	trim($label->format->height) - trim($label->format->margin_bottom), 
	$tcpdf->style1
);

if(file_exists(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png') && $report['Testingcomp']['id'] != 0){

				$tcpdf->Image(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png',
				trim($label->format->margin_left) + 1,
				trim($label->format->margin_top) + 1,
				20,
				0,
				'PNG', false, '', false, '', '', false, false, 0, false, false, false);
				
				$logo_w = $tcpdf->getImageRBX();
}

				$Firmenadresse  = null;
				if(trim($report['Testingcomp']['firmenname']) != '')$Firmenadresse  .=  trim($report['Testingcomp']['firmenname']) . "\n";
				if(trim($report['Testingcomp']['plz']) != '')$Firmenadresse  .= 	trim($report['Testingcomp']['plz']) . " ";
				if(trim($report['Testingcomp']['ort']) != '')$Firmenadresse  .= 	trim($report['Testingcomp']['ort']) . " - ";
				if(trim($report['Testingcomp']['strasse']) != '')$Firmenadresse  .= 	trim($report['Testingcomp']['strasse']) . "\n";
				if(trim($report['Testingcomp']['telefon']) != '')$Firmenadresse  .= 	"Tel. " . trim($report['Testingcomp']['telefon']) . " - ";
				if(trim($report['Testingcomp']['telefax']) != '')$Firmenadresse  .= 	"Fax. " . trim($report['Testingcomp']['telefax']) . "\n";
				if(trim($report['Testingcomp']['internet']) != '')$Firmenadresse  .= 	trim($report['Testingcomp']['internet']) . " - ";
				if(trim($report['Testingcomp']['email']) != '')$Firmenadresse  .= 	trim($report['Testingcomp']['email']) . "\n";

				$tcpdf->SetFont('calibri', 'n', 8);
				$tcpdf->MultiCell(
								0,
								0,
								setUTF8($Firmenadresse),
								0,
								'L',
								0,
								1,
								$logo_w + 2,
								trim($label->format->margin_top) + 1,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);

if(isset($settings->$ReportPdf->settings->QM_QRCODE_WELD->show) && $settings->$ReportPdf->settings->QM_QRCODE_WELD->show == 1){
	$QRurl = $this->Html->url('/', true) . 'reportnumbers/editevalution/'.$url_test[2].'/'.$url_test[3].'/'.$url_test[4].'/'.$url_test[5].'/'.$url_test[6].'/'.$url_test[7].'/'.$url_test[8].'/1';
//	$QRurl = 'http://192.168.10.155:8080/qm_systems/qmsystems/demo/reportnumbers/editevalution/'.$url_test[2].'/'.$url_test[3].'/'.$url_test[4].'/'.$url_test[5].'/'.$url_test[6].'/'.$url_test[7].'/'.$url_test[8].'/1';
	
	$QRstyle = array(
    	'border' => 0,
    	'vpadding' => 0,
    	'hpadding' => 0,
    	'fgcolor' => array(0,0,0),
   		'bgcolor' => false,
		'module_width' => 1,
		'module_height' => 1
	);
	
	$tcpdf->write2DBarcode(
			$QRurl, 
			$settings->$ReportPdf->settings->QM_QRCODE_WELD->quality, 
			$settings->$ReportPdf->settings->QM_QRCODE_WELD->x, 
			$settings->$ReportPdf->settings->QM_QRCODE_WELD->y, 
			$settings->$ReportPdf->settings->QM_QRCODE_WELD->width, 
			$settings->$ReportPdf->settings->QM_QRCODE_WELD->height, 
			$QRstyle, 
			'N');
}

foreach($label->items as $item) {
	if(trim($item->item) == 'free_field') continue;
	if(!isset($data[trim($item->model)][trim($item->item)])) continue;

	if(isset($data[trim($item->model)][trim($item->item)]) != '') $_data = $data[trim($item->model)][trim($item->item)];
	else $_data = '-';

	if(trim($item->item) == 'reportnumber_id'){
		$_data = $this->Pdf->constructReportName($report);
	}
		
	$tcpdf->SetFont('calibri', 'N', 8);
	$tcpdf->Multicell(
		trim($item->width),
		trim($item->height),
		$_data,
		'T',
		$item->textalign,
		false,
		0,
		trim($item->x),
		trim($item->y),
		false,
		0,
		false,
		false,
		trim($item->height),
		'T',
		true
	);

	$tcpdf->SetFont('calibri', 'I', 6);
	$tcpdf->Multicell(
		trim($item->width),
		trim($item->captionheight),
		trim($item->caption),
		'B',
		$item->textalign,
		false,
		0,
		trim($item->x),
		trim($item->y) + trim($item->height),
		false,
		0,
		false,
		false,
		trim($item->captionheight),
		'M',
		true
	);	
}

echo $tcpdf->Output('report_label_'.$data['ReportRtEvaluation']['description'].'.pdf', 'D');
