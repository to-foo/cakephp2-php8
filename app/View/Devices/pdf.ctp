<?php
App::import('Vendor','device_info');

$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->SetAutoPageBreak(true, 30);
$tcpdf->SetTopMargin(50);
$tcpdf->xdata = $this->request->data;
$tcpdf->locale = $locale;
$tcpdf->style0 = array('width' => 0.1, 'color' => array(0, 0, 0));
$tcpdf->style00 = array('width' => 0.1, 'color' => array(125, 125, 125));
$tcpdf->style1 = array('width' => 0.25, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 0.5, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 1, 'color' => array(0, 0, 0));

$info_array = array('next_certification_date','time_to_next_certification','time_to_next_horizon');

$tcpdf->AddPage();

// Gerätedaten einlesen
$tcpdf->SetFont('calibri','n','9');

foreach($xml_device->Device->children() as $_key => $_xml){

	if(!isset($tcpdf->xdata['deviceinfo']['info'][trim($_xml->model)])) continue;
	if(!isset($tcpdf->xdata['deviceinfo']['info'][trim($_xml->model)][$_key])) continue;
	if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;

        if($_xml->fieldtype == 'radio'){
        $dataout = $_xml->radiooption->value[$tcpdf->xdata['deviceinfo']['info'][trim($_xml->model)][$_key]];
        }
	else {
            $dataout = $tcpdf->xdata['deviceinfo']['info'][trim($_xml->model)][$_key];
            $dataout == '0' || $dataout == ''  ? $dataout = '/': '';
        }
	$tcpdf->MultiCell(
			0,
			0,
			trim($_xml->discription->$locale) . ": " . $dataout,
			0,
			'L',
			0,
			2,
			20,
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			0,
			'T',
			20
		);
/*
$tcpdf->Line(
	20,
	$tcpdf->GetY(),
	180,
	$tcpdf->GetY(),
	$tcpdf->style00
	);
*/
$tcpdf->SetY($tcpdf->GetY() + 2);

}

$tcpdf->MultiCell(
		0,
		0,
		__('Remark',true),
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);
$tcpdf->MultiCell(
			0,
			0,
			$tcpdf->xdata['deviceinfo']['info']['Device']['remark'],
			0,
			'L',
			0,
			2,
			20,
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			0,
			'T',
			20
	);

$tcpdf->SetY($tcpdf->GetY() + 2);
$tcpdf->Line(
	20,
	$tcpdf->GetY(),
	180,
	$tcpdf->GetY(),
	$tcpdf->style00
	);

// Prüfverfahren einlesen
$tcpdf->SetFont('calibri','n','14');
$tcpdf->MultiCell(
		0,
		0,
		__('Kategorie',true),
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY() + 2,
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

$tcpdf->SetFont('calibri','n','9');

if($tcpdf->xdata['deviceinfo']['info']['DeviceTestingmethod']['summary'] != null){
	$tcpdf->MultiCell(
		0,
		0,
		$tcpdf->xdata['deviceinfo']['info']['DeviceTestingmethod']['summary'],
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

$tcpdf->SetY($tcpdf->GetY() + 2);
$tcpdf->Line(
	20,
	$tcpdf->GetY(),
	180,
	$tcpdf->GetY(),
	$tcpdf->style00
	);
//$tcpdf->SetY($tcpdf->GetY() + 2);
}

if(isset($tcpdf->xdata['deviceinfo']['info']['Examiner'])){
// Verantworlichen einlesen
$tcpdf->SetFont('calibri','n','14');
	$tcpdf->MultiCell(
		0,
		0,
		__('Responsible person',true),
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY() + 2,
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

	$tcpdf->SetFont('calibri','n','9');

	$tcpdf->MultiCell(
		0,
		0,
		$tcpdf->xdata['deviceinfo']['info']['Examiner']['first_name'] . " " . $tcpdf->xdata['deviceinfo']['info']['Examiner']['name'],
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

	$tcpdf->SetY($tcpdf->GetY() + 2);
	$tcpdf->Line(
		20,
		$tcpdf->GetY(),
		180,
		$tcpdf->GetY(),
		$tcpdf->style00
	);
}

// Verantworlichen einlesen
if(isset($tcpdf->xdata['deviceinfo']['info']['Testingcomp'])){
$tcpdf->SetFont('calibri','n','14');
	$tcpdf->MultiCell(
		0,
		0,
		__('Responsible company',true),
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY() + 2,
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

	$tcpdf->SetFont('calibri','n','9');

	$tcpdf->MultiCell(
		0,
		0,
		$tcpdf->xdata['deviceinfo']['info']['Testingcomp']['summary'],
		0,
		'L',
		0,
		2,
		20,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);

	$tcpdf->SetY($tcpdf->GetY() + 2);
	$tcpdf->Line(
		20,
		$tcpdf->GetY(),
		180,
		$tcpdf->GetY(),
		$tcpdf->style00
	);

	$tcpdf->SetY($tcpdf->GetY() + 2);
}

// Überwachungen einfügen
if(isset($tcpdf->xdata['deviceinfo']['info']['DeviceCertificate']) && count($tcpdf->xdata['deviceinfo']['info']['DeviceCertificate']) > 0){

	$device_certificate_output = null;

	foreach($tcpdf->xdata['deviceinfo']['info']['DeviceCertificate'] as $_key => $_DeviceCertificate){

		$tcpdf->AddPage();

		$tcpdf->SetFont('calibri','b','14');
					$tcpdf->MultiCell(
						0,
						0,
						$_DeviceCertificate['DeviceCertificate']['certificat'],
						0,
						'L',
						0,
						2,
						20,
						$tcpdf->GetY(),
						true,
						0,
						false,
						true,
						0,
						'T',
						20
					);

		$tcpdf->SetY($tcpdf->GetY() + 1);
                foreach($xml_device_cert->DeviceCertificate->children() as $__key => $_xml_cert){


                if(!isset($_DeviceCertificate['DeviceCertificateData'][$__key])) continue;
                if(trim($_xml_cert->output->print) != 1 || empty($_xml_cert->output->print)) continue;

                if($_xml_cert->fieldtype == 'radio'){
                $dataout = $_xml_cert->radiooption->value[$_DeviceCertificate['DeviceCertificateData'][$__key]];

                }
                else {
                    $dataout = $_DeviceCertificate['DeviceCertificateData'][$__key];
                  //  pr($dataout);
                  // $dataout == '0' || $dataout == ''  ? $dataout = '/': '';
                }
                $tcpdf->SetFont('calibri','n','12');
                $tcpdf->MultiCell(
			0,
			0,
			trim($_xml_cert->discription->$locale) . ": " . $dataout,
			0,
			'L',
			0,
			2,
			20,
			$tcpdf->GetY(),
			true,
			0,
			false,
			true,
			0,
			'T',
			20
		);
/*
$tcpdf->Line(
	20,
	$tcpdf->GetY(),
	180,
	$tcpdf->GetY(),
	$tcpdf->style00
	);
*/
$tcpdf->SetY($tcpdf->GetY() + 2);

}

		/*foreach($_DeviceCertificate['DeviceCertificateData'] as $__key => $__DeviceCertificate){
		if(!isset($xml_device_certificate->DeviceCertificate->$__key->output->print) || trim($xml_device_certificate->DeviceCertificate->$__key->output->print) != 1){
			continue;
		}

			if(isset($xml_device_certificate->DeviceCertificate->$__key->discription->$locale)){
                            if($xml_device_certificate->DeviceCertificate->$__key->fieldtype == 'radio') {
                                $device_certificate_output = trim($xml_device_certificate->DeviceCertificate->$__key->discription->$locale). ': ' . $xml_device_certificate->DeviceCertificate->$__key->radiooption->value[$__DeviceCertificate];
                            }
				else $device_certificate_output = trim($xml_device_certificate->DeviceCertificate->$__key->discription->$locale) . ": " .$__DeviceCertificate;

					$tcpdf->SetFont('calibri','n',9);
					$tcpdf->MultiCell(
						0,
						0,
						$device_certificate_output,
						0,
						'L',
						0,
						2,
						20,
						$tcpdf->GetY(),
						true,
						0,
						false,
						true,
						0,
						'T',
						20
					);

					$device_certificate_output = null;
			}
		}*/

		$tcpdf->SetY($tcpdf->GetY() + 1);

		$tcpdf->Line(
			20,
			$tcpdf->GetY(),
			180,
			$tcpdf->GetY(),
			$tcpdf->style00
			);

		$tcpdf->SetY($tcpdf->GetY() + 1);

		foreach($info_array as $_info_array){

			if(!isset($_DeviceCertificate['DeviceCertificateData'][$_info_array]))continue;

			$tcpdf->MultiCell(
				0,
				0,
				$_DeviceCertificate['DeviceCertificateData'][$_info_array],
				0,
				'L',
				0,
				2,
				20,
				$tcpdf->GetY(),
				true,
				0,
				false,
				true,
				0,
				'T',
				20
			);
		}
	}
}

if(isset($return_show) && $return_show === true){

	$out = $tcpdf->Output('device.pdf', 'S');
	$string = base64_encode($out);

	echo $string;

	return;

}

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="report_.pdf"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

ob_end_flush();
set_time_limit(0);

echo $tcpdf->Output('report_.pdf', 'D');
?>
