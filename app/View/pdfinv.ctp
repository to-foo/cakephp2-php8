<?php
App::import('Vendor','device_inv');
$tcpdf = new XTCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->xdata = $this->request->data;
$tcpdf->SetAutoPageBreak(false,0 );
$tcpdf->SetTopMargin(50);
$DevicePdf = "DevicePdf";    





$settings = $xml_deviceinv;
//$tcpdf->SetFont('calibri','n','9'); 

$define = array();
     
$define['radiodefault'] = $this->Pdf->radiodefault;
       
$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
$define['K_BLANK_IMAGE'] = '_blank.png';
$define['PDF_BARCODE'] = $settings->$DevicePdf->settings->PDF_BARCODE;
$define['HEAD_MAGNIFICATION'] = floatval($settings->$DevicePdf->settings->HEAD_MAGNIFICATION);
$define['K_CELL_HEIGHT_RATIO'] = floatval($settings->$DevicePdf->settings->K_CELL_HEIGHT_RATIO);
$define['K_TITLE_MAGNIFICATION'] = floatval($settings->$DevicePdf->settings->K_TITLE_MAGNIFICATION);
$define['K_SMALL_RATIO'] = $settings->$DevicePdf->settings->K_SMALL_RATIO;
$define['K_THAI_TOPCHARS'] = $settings->$DevicePdf->settings->K_THAI_TOPCHARS;
$define['K_TCPDF_CALLS_IN_HTML'] = $settings->$DevicePdf->settings->K_TCPDF_CALLS_IN_HTML;
$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $settings->$DevicePdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
$define['PDF_PAGE_FORMAT'] = $settings->$DevicePdf->settings->PDF_PAGE_FORMAT;
$define['PDF_PAGE_ORIENTATION'] = $settings->$DevicePdf->settings->PDF_PAGE_ORIENTATION;
$define['PDF_CREATOR'] = $settings->$DevicePdf->settings->PDF_CREATOR;
$define['PDF_AUTHOR'] = $settings->$DevicePdf->settings->PDF_AUTHOR;
$define['PDF_HEADER_TITLE'] = $settings->$DevicePdf->settings->PDF_HEADER_TITLE;
$define['PDF_HEADER_STRING'] = $settings->$DevicePdf->settings->PDF_HEADER_STRING;
$define['PDF_UNIT'] = $settings->$DevicePdf->settings->PDF_UNIT;
$define['PDF_MARGIN_HEADER'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_HEADER);
$define['PDF_MARGIN_FOOTER'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_FOOTER);
$define['PDF_MARGIN_TOP'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_TOP);
$define['PDF_MARGIN_BOTTOM'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_BOTTOM);
$define['PDF_MARGIN_LEFT'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_LEFT);
$define['PDF_MARGIN_RIGHT'] = floatval($settings->$DevicePdf->settings->PDF_MARGIN_RIGHT);
$define['PDF_FONT_NAME_MAIN'] = $settings->$DevicePdf->settings->PDF_FONT_NAME_MAIN;
$define['PDF_FONT_SIZE_MAIN'] = $settings->$DevicePdf->settings->PDF_FONT_SIZE_MAIN;
$define['PDF_FONT_NAME_DATA'] = $settings->$DevicePdf->settings->PDF_FONT_NAME_DATA;
$define['PDF_FONT_SIZE_DATA'] = $settings->$DevicePdf->settings->PDF_FONT_SIZE_DATA;
$define['PDF_FONT_MONOSPACED'] = $settings->$DevicePdf->settings->PDF_FONT_MONOSPACED;
$define['PDF_IMAGE_SCALE_RATIO'] = floatval($settings->$DevicePdf->settings->PDF_IMAGE_SCALE_RATIO);

// Descriptions
$define['QM_PAGE_DESCRIPTION'] = $settings->$DevicePdf->settings->QM_PAGE_DESCRIPTION;
$define['QM_REPORT_DESCRIPTION'] = $settings->$DevicePdf->settings->QM_REPORT_DESCRIPTION;
 		
// Celladding definieren
$define['QM_CELL_PADDING_T'] = floatval($settings->$DevicePdf->settings->QM_CELL_PADDING_T);
$define['QM_CELL_PADDING_R'] = floatval($settings->$DevicePdf->settings->QM_CELL_PADDING_R);
$define['QM_CELL_PADDING_B'] = floatval($settings->$DevicePdf->settings->QM_CELL_PADDING_B);
$define['QM_CELL_PADDING_L'] = floatval($settings->$DevicePdf->settings->QM_CELL_PADDING_L);
$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($settings->$DevicePdf->settings->QM_CELL_LAYOUT_LINE_TOP);
$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($settings->$DevicePdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
$define['QM_CELL_LAYOUT_WIDTH'] = floatval($settings->$DevicePdf->settings->QM_CELL_LAYOUT_WIDTH);
$define['QM_CELL_LAYOUT_CLEAR'] = floatval($settings->$DevicePdf->settings->QM_CELL_LAYOUT_CLEAR);

// Seitenumbruch
$define['QM_PAGE_BREAKE'] = $settings->$DevicePdf->settings->QM_PAGE_BREAKE ;
	//$define['QM_START_FOOTER'] = $settings->$DevicePdf->settings->QM_START_FOOTER - $PageBreakShift;
$define['QM_LINE_HEIGHT'] = $settings->$DevicePdf->settings->QM_LINE_HEIGHT;
        
	 
	// neue Seiteneinstellungen eintragen um neuen Header zu schreiben
        $tcpdf->setPageUnit($define['PDF_UNIT']);
        $tcpdf->xsettings = $settings;
	$tcpdf->writeSettings($define);
	$tcpdf->setPageOrientation($define['PDF_PAGE_ORIENTATION'], false, $define['PDF_MARGIN_BOTTOM']);
	
         
        $tcpdf->SetCreator($define['PDF_CREATOR']);
        $tcpdf->SetAuthor('MBQ Qualitätssicherung');
        $tcpdf->SetTitle('Inventarliste');
        $tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
        $tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
        $tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
        $tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);
        $tcpdf->MarginLeftPlus = 0;
        
        $tcpdf->AddPage();
        $tcpdf->setCellPaddings(0.9, 0.9, 0.9, 0.9);
        $font = 'calibri';
        $fontweight = 'B';
        $fontheigth = 9;
        $tcpdf->SetFont($font, $fontweight, $fontheigth);
        if($this->Session->check('searchdevice.params')) {
            $tcpdf->Ln();
            $tcpdf->MultiCell('', 8,  __('search parameters').':'.PHP_EOL.$this->Session->read('searchdevice.params'), 0, 'L', false, 1, '',$tcpdf->GetY()+5, true, 0, false, true, 8, '');
        }     
             
                    //   pr($Devices);

            $ybegin = $tcpdf->GetY()+ $define['PDF_MARGIN_TOP'];
            $tcpdf->SetY($ybegin);
            $tcpdf->Ln();
            $lineArray = array();
            $CellHeight = 6;
            $CellHeightData = 8;
            // Hier wird das Array mit den auszugebenden Werten durchlaufen
            // Hier werden die Zeilen für die Tabelle gebildet
          
            //Tabellenkopf
            $newpage = true;
            $maxdev = count($Devices);
            $countdev = 0;
            foreach($Devices as $key => $DevicesData){
               
                // Wenn nix da ist wir die Schleife abgebrochen
                if(count($DevicesData) == 0){
                    break;
                    
                }
                $countdev++;
               // $tcpdf->Ln();  
             $linearry[] = $tcpdf->GetY();   
               
            if($newpage === true){ //1. Seite Tabellenkopf immer mit ausgegeben / wenn Neue Seite erstellt wurde -> Tabellenkopf ausgeben
                $tcpdf->SetY($ybegin);
                $linearry[] = $tcpdf->GetY();
                $font = 'calibri';
                $fontweight = 'B';
                $fontheigth = 9;
                $tcpdf->SetFillColor(235, 232, 232);
                $tcpdf->SetFont($font, $fontweight, $fontheigth);
                // Start für X-Position definieren
                $startx = $define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus;
                $tcpdf->SetX($define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus);
                foreach($tcpdf->xsettings->Device->children() as $_DevicesKey => $_DevicesSettings) {                  
                    if(!isset($tcpdf->xsettings->Device->$_DevicesKey->pdf))continue;
                    if(empty($tcpdf->xsettings->Device->$_DevicesKey->pdf->output)) continue; 
                    $_datahead = trim($_DevicesSettings->discription->$locale);
                    
                    $tcpdf->MultiCell(
                            $tcpdf->xsettings->Device->$_DevicesKey->pdf->positioning->width,
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
                     $startx = $startx +  $tcpdf->xsettings->Device->$_DevicesKey->pdf->positioning->width;
                     
                }
               //$tcpdf->SetY($tcpdf->GetY());
                $newpage = false;
            }
                
                
            $font = 'calibri';
            $fontweight = 'N';
            $fontheigth = 9;
            $tcpdf->SetFont($font, $fontweight, $fontheigth);
            $startx = $define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus;
            $Yrow = $tcpdf->GetY();
             $linearrx [] = $tcpdf->GetX() ;
                // hier werden die Spalten in den Zeilen gebildet
               foreach($tcpdf->xsettings->Device->children() as $_DevicesKey => $_DevicesSettings) {                  
                    if(!isset($_DevicesSettings->pdf))continue;
                    if(empty($_DevicesSettings->pdf->output)) continue; 
                    
                    if($_DevicesSettings->fieldtype == 'radio'){
                        $_data = $_DevicesSettings->radiooption->value[$Devices [$key] [$_DevicesKey]];
                    }
                    else $_data = trim($Devices [$key] [$_DevicesKey]) ;
                   
                        // Wenn das Datenfeld leer ist, wird ein Schrägstrich gesetzt
                        if(empty($_data) && Configure::read('ReplaceEmptyValuesInPrint')) $_data = '/';
                       
                         // Daten ausgeben
                        $tcpdf->MultiCell(
                            $tcpdf->xsettings->Device->$_DevicesKey->pdf->positioning->width,
                           0,// 0 + $CellHeightData,
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
                            '',
                            'T',
                            false
                        );
                        $startx = $startx +  $tcpdf->xsettings->Device->$_DevicesKey->pdf->positioning->width;
                        $linearrx [] = $startx;
                        $linearry [] = $tcpdf->GetY(); // Positionen für Vertikale Linien
                     
                }
                
                $tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), $define['QM_CELL_LAYOUT_CLEAR'],max($linearry));
                $tcpdf->SetY(max($linearry));
                $linearry[] = $tcpdf->GetY(); // > Wert für Hor. Linien
                $tcpdf->SetX($define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus);
                   
          
                if($tcpdf->GetY() > $define['QM_PAGE_BREAKE'] && $countdev < $maxdev){  //wenn Seitenende erreicht -> neue Seite angelegen 
                    //$tcpdf->Ln();
                    $linearrx [] = $startx ;
                    $linearry[] = $tcpdf->GetY();

                    foreach ($linearrx as $key => $value) {
                              //pr($value);
                        $tcpdf->Line($value,min($linearry) , $value, max($linearry)); //Vertikale Linien
                             
                    }
                    $linearry[] = $tcpdf->GetY();
                    
                    $tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY()); //Letzte  hor. Linie pro Seite
                    $newpage = true;
                    $tcpdf->AddPage();   
                    $tcpdf->setCellPaddings(0.9, 0.9, 0.9, 0.9);
                    $tcpdf->SetY($ybegin);
                    
                    $linearry = null;
                    $linearrx = null;
                    $linearry[] = $tcpdf->GetY();
                    //$linearrx [] = $startx ;
                }

            }
           
            $tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['QM_CELL_LAYOUT_CLEAR'], $tcpdf->GetY()); //Letzte  hor. Linie pro Seite
           
            foreach ($linearrx as $key => $value) {
                              //pr($value);
                 $tcpdf->Line($value,min($linearry) , $value, max($linearry)); //Vertikale Linien
                             
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
	
echo $tcpdf->Output('Inventarliste.pdf', 'D');
