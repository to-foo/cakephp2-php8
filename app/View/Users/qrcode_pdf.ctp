<?php
$label = $settings->$ReportPdf->settings->QM_QRCODE_SINGLE;

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

if(isset($settings->$ReportPdf->settings->QM_QRCODE_SINGLE->show) && $settings->$ReportPdf->settings->QM_QRCODE_SINGLE->show == 1){

	$action = 'view';
	if(isset($url_test[8]) && $url_test[8] > 0){$action = 'editevalution';}
	
	$term = null;	
	foreach($url_test as $_key => $_url_test){
		if($_key > 1){
			$term .= $_url_test.'/';	
		}
	}

//	$QRurl = $this->Html->url('/', true) . 'reportnumbers/'.$action.'/'.$term;
	$QRurl = 'http://192.168.10.196:8080/qm_systems/qmsystems/demo/reportnumbers/'.$action.'/'.$term;
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
			$settings->$ReportPdf->settings->QM_QRCODE_SINGLE->quality, 
			$settings->$ReportPdf->settings->QM_QRCODE_SINGLE->x, 
			$settings->$ReportPdf->settings->QM_QRCODE_SINGLE->y, 
			$settings->$ReportPdf->settings->QM_QRCODE_SINGLE->width, 
			$settings->$ReportPdf->settings->QM_QRCODE_SINGLE->height, 
			$QRstyle, 
			'N');
}

if(file_exists(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png') && $report['Testingcomp']['id'] != 0){

				$tcpdf->Image(Configure::read('company_logo_folder').trim($report['Testingcomp']['id']).DS.'logo.png',
				trim($label->format->margin_left) + $settings->$ReportPdf->settings->QM_QRCODE_SINGLE->width + 5,
				trim($label->format->margin_top),
				20,
				0,
				'PNG', false, '', false, '', '', false, false, 0, false, false, false);
				
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
								trim($label->format->margin_left) + $settings->$ReportPdf->settings->QM_QRCODE_SINGLE->width + 5,
								trim($label->format->margin_top) + 10,
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);
							
							$report = "Prüfbericht\n".$report['Testingmethod']['verfahren']." ".$this->Pdf->constructReportName($report);

				if(isset($welds) && is_array($welds) && count($welds) > 0){
					$report .= ' - ' . $welds[0][$ReportEvaluation]['description'];
				}

				$tcpdf->MultiCell(
								0,
								0,
								$report,
								0,
								'L',
								0,
								1,
								trim($label->format->margin_left) + $settings->$ReportPdf->settings->QM_QRCODE_SINGLE->width + 5,
								$tcpdf->getY(),
								true,
									0,
								false,
								true,
								0,
								'T',
								true
							);

echo $tcpdf->Output('report_label_.pdf', 'D');
