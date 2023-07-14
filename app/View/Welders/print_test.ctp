<?php
App::import('Vendor','welder_test');

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
          $OrderPdf = 'ReportPdf';
          $settings = $xml_welder;
	$define = array();
//Einstellungen aus der PDF
	$define['radiodefault'] = $this->Pdf->radiodefault;
	$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
	$define['K_BLANK_IMAGE'] = '_blank.png';
	$define['PDF_BARCODE'] = $settings->$OrderPdf->settings->PDF_BARCODE;
	$define['HEAD_MAGNIFICATION'] = floatval($settings->$OrderPdf->settings->HEAD_MAGNIFICATION);
	$define['K_CELL_HEIGHT_RATIO'] = floatval($settings->$OrderPdf->settings->K_CELL_HEIGHT_RATIO);
	$define['K_TITLE_MAGNIFICATION'] = floatval($settings->$OrderPdf->settings->K_TITLE_MAGNIFICATION);
	$define['K_SMALL_RATIO'] = $settings->$OrderPdf->settings->K_SMALL_RATIO;
	$define['K_THAI_TOPCHARS'] = $settings->$OrderPdf->settings->K_THAI_TOPCHARS;
	$define['K_TCPDF_CALLS_IN_HTML'] = $settings->$OrderPdf->settings->K_TCPDF_CALLS_IN_HTML;
	$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $settings->$OrderPdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
	$define['PDF_PAGE_FORMAT'] = $settings->$OrderPdf->settings->PDF_PAGE_FORMAT;
	$define['PDF_PAGE_ORIENTATION'] = $settings->$OrderPdf->settings->PDF_PAGE_ORIENTATION;
	$define['PDF_CREATOR'] = $settings->$OrderPdf->settings->PDF_CREATOR;
	$define['PDF_AUTHOR'] = $settings->$OrderPdf->settings->PDF_AUTHOR;
	$define['PDF_HEADER_TITLE'] = $settings->$OrderPdf->settings->PDF_HEADER_TITLE;
	$define['PDF_HEADER_STRING'] = $settings->$OrderPdf->settings->PDF_HEADER_STRING;
	$define['PDF_UNIT'] = $settings->$OrderPdf->settings->PDF_UNIT;
	$define['PDF_MARGIN_HEADER'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_HEADER);
	$define['PDF_MARGIN_FOOTER'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_FOOTER);
	$define['PDF_MARGIN_TOP'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_TOP);
	$define['PDF_MARGIN_BOTTOM'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_BOTTOM);
	$define['PDF_MARGIN_LEFT'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_LEFT);
	$define['PDF_MARGIN_RIGHT'] = floatval($settings->$OrderPdf->settings->PDF_MARGIN_RIGHT);
	$define['PDF_FONT_NAME_MAIN'] = $settings->$OrderPdf->settings->PDF_FONT_NAME_MAIN;
	$define['PDF_FONT_SIZE_MAIN'] = $settings->$OrderPdf->settings->PDF_FONT_SIZE_MAIN;
	$define['PDF_FONT_NAME_DATA'] = $settings->$OrderPdf->settings->PDF_FONT_NAME_DATA;
	$define['PDF_FONT_SIZE_DATA'] = $settings->$OrderPdf->settings->PDF_FONT_SIZE_DATA;
	$define['PDF_FONT_MONOSPACED'] = $settings->$OrderPdf->settings->PDF_FONT_MONOSPACED;
	$define['PDF_IMAGE_SCALE_RATIO'] = floatval($settings->$OrderPdf->settings->PDF_IMAGE_SCALE_RATIO);

	// Descriptions
	$define['QM_PAGE_DESCRIPTION'] = $settings->$OrderPdf->settings->QM_PAGE_DESCRIPTION;
	$define['QM_REPORT_DESCRIPTION'] = $settings->$OrderPdf->settings->QM_REPORT_DESCRIPTION;

	// Cellpadding definieren
	$define['QM_CELL_PADDING_T'] = floatval($settings->$OrderPdf->settings->QM_CELL_PADDING_T);
	$define['QM_CELL_PADDING_R'] = floatval($settings->$OrderPdf->settings->QM_CELL_PADDING_R);
	$define['QM_CELL_PADDING_B'] = floatval($settings->$OrderPdf->settings->QM_CELL_PADDING_B);
	$define['QM_CELL_PADDING_L'] = floatval($settings->$OrderPdf->settings->QM_CELL_PADDING_L);
	$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($settings->$OrderPdf->settings->QM_CELL_LAYOUT_LINE_TOP);
	$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($settings->$OrderPdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
	$define['QM_CELL_LAYOUT_WIDTH'] = floatval($settings->$OrderPdf->settings->QM_CELL_LAYOUT_WIDTH);
	$define['QM_CELL_LAYOUT_CLEAR'] = floatval($settings->$OrderPdf->settings->QM_CELL_LAYOUT_CLEAR);
        
        $define['QM_PAGE_BREAKE'] = $settings->$OrderPdf->settings->QM_PAGE_BREAKE ;
        $MarginLeftPlus = 0;
$tcpdf->AddPage();


$tcpdf->SetFont('calibri','n','9');
// Prüferdaten ausgeben 
/*foreach($xml_welder->Welder->children() as $_key => $_xml){

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
                    if(!isset($tcpdf->xdata['welderinfo']['Eyecheck'][trim($_xml->model)])) continue;
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
}*/
$startx = $define['PDF_MARGIN_LEFT'] ;
 $tcpdf->SetFont('calibri','b','11');        
             $tcpdf->MultiCell(
		0,
		0,
		__('1  Allgemeine Angaben',true),
		0,
		'L',
		0,
		2,
		$startx,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
              );    
$tcpdf->Ln();            
$tcpdf->SetFont('calibri','n','9');
           
          
  $Yrow = $tcpdf->GetY();
  $linearrx [] = $tcpdf->GetX() ;
  $linearry [] = $tcpdf->GetY();
foreach($xml_welder->Generally->children() as $_key => $_xml){
 
	if(!isset($tcpdf->xdata [trim($_xml->model)])) continue;
	if(!isset($tcpdf->xdata[trim($_xml->model)][$_key])) continue;
	if(trim($_xml->pdf->output) != 1 || empty($_xml->pdf->output)) continue;
      
        if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio' && isset($_xml->radiooption)){                       
            $content =  trim($_xml->discription->$locale) . ": " . $_xml->radiooption->value[$tcpdf->xdata[trim($_xml->model)][$_key]];
        }         
        else if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
            $options = array('-','nein/no','ja/yes');
            if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
                $content = trim($_xml->discription->$locale) . ": " .$options[$tcpdf->xdata[trim($_xml->model)][$_key]];
            }
        }    
        else{
            $content = trim($_xml->discription->$locale) . ": " . $tcpdf->xdata[trim($_xml->model)][$_key];    
        }        
      
       // $tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), $define['QM_CELL_LAYOUT_CLEAR'],max($linearry));  	
	$tcpdf->MultiCell(
			$_xml->pdf->positioning->width, //in PDF einstellbar machen wichtig!
			7,  
			$content,
			0,
			'L',
			0,
			0,
			$startx,
			$Yrow,
			true,
			0,
			false,
			true,
			0,
			'T',
			20
		);
            
        $startx = $startx +   $_xml->pdf->positioning->width;
         $wdh = $_xml->pdf->positioning->width;
         $tcpdf->Ln();
         $linearry [] = $tcpdf->GetY(); // Positionen für Vertikale Linien
     
        if(trim($_xml->pdf->positioning->break) == 1) {
 
                  
                  $Yrow = max($linearry);
                   

                  $startx = $define['PDF_MARGIN_LEFT'] ;
                  //$tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), max($linearrx),max($linearry)); 
                }
                              

                $tcpdf->SetX($define['PDF_MARGIN_LEFT'] );
                        
        }
        $startx = $define['PDF_MARGIN_LEFT'] ;            
        $tcpdf->SetFont('calibri','b','11');        
             $tcpdf->MultiCell(
		0,
		0,
		__('2  Beurteilung',true),
		0,
		'L',
		0,
		2,
		$startx,
		$tcpdf->GetY(),
		true,
		0,
		false,
		true,
		0,
		'T',
		20
              );    
        $tcpdf->Ln();                        

  


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