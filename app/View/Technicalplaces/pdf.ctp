<?php
App::import('Vendor','order_info');

$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->SetAutoPageBreak(true, 30);
$tcpdf->SetTopMargin(50);
$tcpdf->SetLeftMargin($xml_order->OrderPdf->settings->PDF_MARGIN_LEFT);
$tcpdf->SetRightMargin($xml_order->OrderPdf->settings->PDF_MARGIN_RIGHT);
$tcpdf->xsettings = $xml_order;
$tcpdf->xdata = $this->request->data;
$settings = $xml_order;

/*$tcpdf->locale = $locale;
$tcpdf->style0 = array('width' => 0.1, 'color' => array(0, 0, 0));
$tcpdf->style00 = array('width' => 0.1, 'color' => array(125, 125, 125));
$tcpdf->style1 = array('width' => 0.25, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 0.5, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 1, 'color' => array(0, 0, 0));
*/      $OrderPdf = 'OrderPdf';
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
           $startx = $define['PDF_MARGIN_LEFT'] ;
           
            $Yrow = $tcpdf->GetY();
            $linearrx [] = $tcpdf->GetX() ;
            $linearry [] = $tcpdf->GetY();
//Daten$linearry [] = $tcpdf->GetY();
        
foreach($xml_order->Order->children() as $_key => $_xml){

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
         $linearrx [] = $startx;
         $wdh = $_xml->pdf->positioning->width;
         $tcpdf->Ln();
         $linearry [] = $tcpdf->GetY(); // Positionen für Vertikale Linien
     
        if(trim($_xml->pdf->positioning->break) == 1) {
 
                  $linearrx [] = $startx;
                  $tcpdf->Line($define['PDF_MARGIN_LEFT'], $Yrow, max($linearrx),$Yrow);
                  $Yrow = max($linearry);
                   

                  $startx = $define['PDF_MARGIN_LEFT'] ;
                  //$tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), max($linearrx),max($linearry)); 
                }
}                               

               $tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), max($linearrx),max($linearry)); // letzte hor. Linie
                $linearry[] = $tcpdf->GetY(); // > Wert für Hor. Linien
                $tcpdf->SetX($define['PDF_MARGIN_LEFT'] );
                $tcpdf->SetY(max($linearry));
                foreach ($linearrx as $key => $value) {
                              //pr($value);
                 $tcpdf->Line($value,min($linearry) , $value, max($linearry)); //Vertikale Linien
                 
                          
                             
            }
               /*   foreach ($linearry as $_key => $_value) { 
                    $tcpdf->Line($define['PDF_MARGIN_LEFT'], $_value, max($linearrx),$_value); 
                      
                  }*/
                              



//Bemerkungen
$tcpdf->Ln();
$tcpdf->MultiCell(
		0,
		0,
		__('Remark',true).':',
		0,
		'L',
		0,
		1,			
                $tcpdf->GetX(),
		$tcpdf->GetY()+ 2,
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
			isset($tcpdf->xdata['Order']['remarks'])? $tcpdf->xdata['Order']['remarks']:'',
			0,
			'L',
			0,
			1,
			$tcpdf->GetX(),
			$tcpdf->GetY()+2,
			true,
			0,
			false,
			true,
			0,
			'T',
			20
	);
//Seite 2 Notizen
$tcpdf->AddPage();
$tcpdf->MultiCell(
		0,
		0,
		__('Notice',true).':',
		0,
		'L',
		0,
		1,			
                $tcpdf->GetX(),
		$tcpdf->GetY()+ 2,
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);
$tcpdf->Ln();
while ($tcpdf->GetY() + 8 < $define ['QM_PAGE_BREAKE']){
    $tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY(), 1);
    $tcpdf->SetY($tcpdf->getY() + 8);
}
$tcpdf->SetAutoPageBreak(false,0 );
$tcpdf->AddPage();
//Prüffortschritt

$tcpdf->SetFont('calibri', 'n', 14);
$tcpdf->MultiCell(
		0,
		0,
		__('Development',true),
		0,
		'L',
		0,
		0,			
                $tcpdf->GetX(),
		$tcpdf->GetY()+ 2,
		true,
		0,
		false,
		true,
		0,
		'T',
		20
	);
//$tcpdf->Ln();
$newpage = true;

$CellHeightData = 7;
 $tcpdf->setCellPaddings(0.9, 0.9, 0.9, 0.9);
 $ybegin = $tcpdf->GetY()+ $define['PDF_MARGIN_TOP'];
 $tcpdf->SetY($ybegin);

 $lineArray = array();
 
foreach($developmentdata as $key => $DevelopmentDatas) {
    
    if(count($DevelopmentDatas) == 0){
        break;
                    
    }    
    
    $linearry[] = $tcpdf->GetY();
    
    if($newpage === true) {
        $tcpdf->SetFillColor(235, 232, 232);
        $font = 'calibri';
        $fontweight = 'B';
        $fontheigth = 9;
        $tcpdf->SetFont($font, $fontweight, $fontheigth);
        $tcpdf->SetY($ybegin);
        $linearry[] = $tcpdf->GetY();
        $startx = $define['PDF_MARGIN_LEFT'] ;
        $tcpdf->SetX($startx);
        foreach($xml_development->DevelopmentData->children() as $_key => $_xml){
        //if(!isset($tcpdf->xdata [trim($_xml->model)])) continue;
	//if(!isset($tcpdf->xdata[trim($_xml->model)][$_key])) continue;
	if(trim($_xml->pdf->output) != 1 || empty($_xml->pdf->output)) continue;            
  
            
        $_datahead = trim($_xml->discription->$locale);
                
                
                    $tcpdf->MultiCell(
                            $_xml->pdf->positioning->width,// $tcpdf->xsettings->Device->$_DevicesKey->pdf->positioning->width,
                            $CellHeightData - 3,
                            $_datahead,
                            // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                            1,//$tcpdf->$LineStyle,
                            'L',
                            1,
                            1,
                            $startx,
                            $ybegin,
                            true,
                            0,
                            false,
                            true,
                            $CellHeightData,
                            'T',
                            true
                        );
                          
                        $startx = $startx +  $_xml->pdf->positioning->width;

                        
                      //  if(trim($_xml->pdf->positioning->break) == 1) {
                        //    $tcpdf->Ln();
                       // }                    
        }
        $newpage = false;
    } 
    $startx = $define['PDF_MARGIN_LEFT'];
    $Yrow = $tcpdf->GetY();
    $linearrx [] = $tcpdf->GetX() ;
    foreach($xml_development->DevelopmentData->children() as $_key => $_xml){
        //pr($DevelopmentDatas[trim($_xml->model)][$_key]);die();
        if(trim($_xml->pdf->output) != 1 || empty($_xml->pdf->output)) continue;
        if(!isset($DevelopmentDatas [trim($_xml->model)][$_key])) continue;
	 
        
        $font = 'calibri';
        $fontweight = 'N';
        $fontheigth = 9;
        $tcpdf->SetFont($font, $fontweight, $fontheigth);
        
        if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio' && isset($_xml->radiooption)){                       
            $_data =  $_xml->radiooption->value[$DevelopmentDatas [trim($_xml->model)][$_key]];
        }         
        else if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio') {
            $options = array('-','Rep','Ok');
            if((isset($_xml->fieldtype )) && $_xml->fieldtype == 'radio'){
                $_data = $options[$DevelopmentDatas [trim($_xml->model)][$_key]];
            }
        }    
        else{
                $_data = trim($DevelopmentDatas [trim($_xml->model)][$_key]);    
        }        
        
        //$_data = $DevelopmentDatas [trim($_xml->model)][$_key];
       
       // pr($_data); die();
        if(empty($_data) && Configure::read('ReplaceEmptyValuesInPrint')) $_data = '/';
        
        $tcpdf->MultiCell(
                $_xml->pdf->positioning->width,
                0,//$CellHeightData,
                $_data,
                // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                '',//$tcpdf->$LineStyle,
                'L',
                false,
                1,
                $startx,
                $Yrow,
                true,
                false,
                false,
                true,
                '',//$CellHeightData,
                'T',
                false
        );
                $startx = $startx +   $_xml->pdf->positioning->width;
                $linearrx [] = $startx;
                $linearry [] = $tcpdf->GetY(); // Positionen für Vertikale Linien       
        
        //if(trim($_xml->pdf->positioning->break) == 1) {
       //     $tcpdf->Ln();
       // }
    
    }
    $tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), $define['QM_CELL_LAYOUT_CLEAR'],max($linearry));
    $tcpdf->SetY(max($linearry));
    $linearry[] = $tcpdf->GetY(); // > Wert für Hor. Linien
    $tcpdf->SetX($define['PDF_MARGIN_LEFT']);    
    
    
                $tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY()); //Letzte  hor. Linie pro Seite
           
            foreach ($linearrx as $key => $value) {
                              //pr($value);
                 $tcpdf->Line($value,min($linearry) , $value, max($linearry)); //Vertikale Linien
            }
}



header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="Order.pdf"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

ob_end_flush();
set_time_limit(0);
	
echo $tcpdf->Output('Order.pdf', 'D');
?>