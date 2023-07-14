<?php
App::import('Vendor','welder_info');

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

//$info_array = array('next_certification_date','time_to_next_certification','time_to_next_horizon');	

$tcpdf->AddPage();


$tcpdf->SetFont('calibri','n','9');
// Prüferdaten ausgeben 
foreach($xml_welder->Welder->children() as $_key => $_xml){

	if(!isset($tcpdf->xdata['welderinfo']['info'][trim($_xml->model)])) continue;
	if(!isset($tcpdf->xdata['welderinfo']['info'][trim($_xml->model)][$_key])) continue;
	if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
	
	$tcpdf->MultiCell(
			0,
			0,
			trim($_xml->discription->$locale) . ": " . $tcpdf->xdata['welderinfo']['info'][trim($_xml->model)][$_key],
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
			isset($tcpdf->xdata['welderinfo']['info']['Welder']['remark'])? $tcpdf->xdata['welderinfo']['info']['Welder']['remark'] : '',
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
// Sehtests ausgeben 	
if (isset($tcpdf->xdata['welderinfo']['Eyecheck'])&& count($tcpdf->xdata['welderinfo']['Eyecheck']) > 0 ){
        $tcpdf->AddPage();                
        $tcpdf->SetFont('calibri','b','16');        
        $tcpdf->MultiCell(
		0,
		0,
		__('vision test',true),
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
                    foreach ($xml_eyecheck->Eyecheck->children() as $_key => $_xml) { 
                        $tcpdf->SetFont('calibri','n','9');
                         f(!isset($tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)])) continue;
                        if(!isset($tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key])) continue;
                        if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
                        $tcpdf->MultiCell(
			0,
			0,
			trim($_xml->discription->$locale) . ": " . $tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key],
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
                    }  
                foreach ($xml_eyecheckdata->EyecheckData->children() as $_key => $_xml) { 
                    $tcpdf->SetFont('calibri','n','9');
                    if(!isset($tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)])) continue;
                    if(!isset($tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key])) continue;
                    if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
                    
                    if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio' && isset($_xml->radiooption)){                       
                        $content =  trim($_xml->discription->$locale) . ": " . $_xml->radiooption->value[$tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key]];
                    }         
                    else {
                        $options = array('nein','ja');
                        if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
                            $content = trim($_xml->discription->$locale) . ": " .$options[$tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key]];
                        }
                        else{
                            $content = trim($_xml->discription->$locale) . ": " . $tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)][$_key];    
                        }
                    }
                   
                    $tcpdf->MultiCell(
			0,
			0, 
			$content,
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
			isset($tcpdf->xdata['welderinfo']['Eyecheck']['EyecheckData']['remark'])? $tcpdf->xdata['welderinfo']['Eyecheck']['EyecheckData']['remark'] : '',
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
// Qualifikationen und Zertifikate ausgeben 
if (isset($tcpdf->xdata['welderinfo']['WelderCertificate'])&& count($tcpdf->xdata['welderinfo']['WelderCertificate']) > 0 ){   
    foreach ($tcpdf->xdata['welderinfo']['WelderCertificate'] as $_key => $value)  { // erste Ebene Sector
        foreach ($tcpdf->xdata['welderinfo']['WelderCertificate'] [$_key] as $_key2 => $value2) { // zweite Ebene Qualifikation
             $tcpdf->AddPage();
             $tcpdf->SetFont('calibri','b','16');        
             $tcpdf->MultiCell(
		0,
		0,
		__('Welder Qualifications',true),
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
            foreach ($xml_certificate->WelderCertificate->children() as $_key3 => $_xml) { // Qualifikation ausgeben
                    $tcpdf->SetFont('calibri','n','9');
                    if(!isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)])) continue;
                
                    if(!isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key3])) continue;
                    if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;    
        
        
                    if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio' && isset($_xml->radiooption)){                       
                        $content =  trim($_xml->discription->$locale) . ": " . $_xml->radiooption->value[$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key3]];
                    }         
                    else {
                        $options = array('nein','ja');
                        if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
                            $content = trim($_xml->discription->$locale) . ": " .$options[$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key3]];
                        }
                        else{
                            $content = trim($_xml->discription->$locale) . ": " . $tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key3];    
                        }
                    }
                   
                    $tcpdf->MultiCell(
			0,
			0, 
			$content,
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
                    
            }
        if(isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)]['certificate_data_active']) && $tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)]['certificate_data_active'] > 0){  
             $tcpdf->SetFont('calibri','b','16');
             $tcpdf->MultiCell(
		0,
		0,
		__('',true),
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
             $tcpdf->SetFont('calibri','n','9');
             // Zertifikatdaten
             foreach ($xml_certificatedata->WelderCertificateData->children() as $_key4 => $_xml) {
                    $tcpdf->SetFont('calibri','n','9');
                    if(!isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)])) continue;
                
                    if(!isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key4])) continue;
                    if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;    
        
        
                    if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio' && isset($_xml->radiooption)){                       
                        $content =  trim($_xml->discription->$locale) . ": " . $_xml->radiooption->value[$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key4]];
                    }         
                    else {
                        $options = array('nein','ja');
                        if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
                            $content = trim($_xml->discription->$locale) . ": " .$options[$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key4]];
                        }
                        else{
                            $content = trim($_xml->discription->$locale) . ": " . $tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2][trim($_xml->model)][$_key4];    
                        }
                    }
                   
                    $tcpdf->MultiCell(
			0,
			0, 
			$content,
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
                }    
                $tcpdf->MultiCell(
                        0,
                        0, 
                        $tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['next_certification_date'],
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
                    $tcpdf->MultiCell(
			0,
			0, 
			$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['time_to_next_certification'],
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
                    $tcpdf->MultiCell(
			0,
			0, 
			$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['time_to_next_horizon'],
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
                    $tcpdf->MultiCell(
			0,
			0, 
			$tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['certification_requested'],
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
			isset($tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['remark'])? $tcpdf->xdata['welderinfo']['WelderCertificate'][$_key][$_key2]['WelderCertificateData']['remark']:'',
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
            else{
                 $tcpdf->MultiCell(
			0,
			0, 
			'Es ist keine Qualifizierung vorgesehen.',
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
                
            }
        }  
    }          
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