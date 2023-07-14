<?php
App::import('Vendor','revision_info');

$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->SetAutoPageBreak(false, 0);
$tcpdf->SetTopMargin(50);
//$tcpdf->xdata = $this->request->data;
$tcpdf->locale = $locale;
$tcpdf->style0 = array('width' => 0.1, 'color' => array(0, 0, 0));
$tcpdf->style00 = array('width' => 0.1, 'color' => array(125, 125, 125));
$tcpdf->style1 = array('width' => 0.25, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 0.5, 'color' => array(0, 0, 0));
$tcpdf->style2 = array('width' => 1, 'color' => array(0, 0, 0));

//$info_array = array('next_certification_date','time_to_next_certification','time_to_next_horizon');	
//$reportimages = $_pdfData['reportimages'];
foreach($pdfData as $_pdfData) {
	//$reportimages = $_pdfData['reportimages'];
	$version = $_pdfData['version'];
	$Verfahren = $_pdfData['Verfahren'];
	$data = $_pdfData['data'];
	$settings = $_pdfData['settings'];
	$user = $_pdfData['user'];
	$changes = $_pdfData['revChanges'];
	$hiddenFields = $_pdfData['hiddenFields'];
	

	$ReportPdf = 'ReportPdf';
		$ReportEvaluation = 'Report'.$Verfahren.'Evaluation';
	//pr($settings->$ReportPdf->settings->PDF_PAGE_FORMAT);
	//die Höhe der Datenzellen im Footer herausfinden, um den Seitenumbruch zu verschieben
	$settingsArray[0] = 'Report'.$Verfahren.'Generally';
	$settingsArray[1] = 'Report'.$Verfahren.'Specific';
	$settingsArray[2] = 'Report'.$Verfahren.'Evaluation';

	$PageBreakShift = 0;
	foreach($settingsArray as $_settingsArray){
		foreach($settings->$_settingsArray as $_settings){
			foreach($_settings as $__settings){
                            if($__settings->pdf->output == 5 && $__settings->pdf->positioning->x != ''){
                                    if($__settings->pdf->positioning->height > $PageBreakShift){
                                            $PageBreakShift = $__settings->pdf->positioning->height;
                                    }
                            }
			}
		}
	}
        //
	$define = array();

	$define['radiodefault'] = $this->Pdf->radiodefault;
	$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
	$define['K_BLANK_IMAGE'] = '_blank.png';
	$define['PDF_BARCODE'] = $settings->$ReportPdf->settings->PDF_BARCODE;
	$define['HEAD_MAGNIFICATION'] = floatval($settings->$ReportPdf->settings->HEAD_MAGNIFICATION);
	$define['K_CELL_HEIGHT_RATIO'] = floatval($settings->$ReportPdf->settings->K_CELL_HEIGHT_RATIO);
	$define['K_TITLE_MAGNIFICATION'] = floatval($settings->$ReportPdf->settings->K_TITLE_MAGNIFICATION);
	$define['K_SMALL_RATIO'] = $settings->$ReportPdf->settings->K_SMALL_RATIO;
	$define['K_THAI_TOPCHARS'] = $settings->$ReportPdf->settings->K_THAI_TOPCHARS;
	$define['K_TCPDF_CALLS_IN_HTML'] = $settings->$ReportPdf->settings->K_TCPDF_CALLS_IN_HTML;
	$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $settings->$ReportPdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
	$define['PDF_PAGE_FORMAT'] = $settings->$ReportPdf->settings->PDF_PAGE_FORMAT;
	$define['PDF_PAGE_ORIENTATION'] = $settings->$ReportPdf->settings->PDF_PAGE_ORIENTATION;
	$define['PDF_CREATOR'] = $settings->$ReportPdf->settings->PDF_CREATOR;
	$define['PDF_AUTHOR'] = $settings->$ReportPdf->settings->PDF_AUTHOR;
	$define['PDF_HEADER_TITLE'] = $settings->$ReportPdf->settings->PDF_HEADER_TITLE;       
	$define['PDF_HEADER_STRING'] = $settings->$ReportPdf->settings->PDF_HEADER_STRING;
	$define['PDF_UNIT'] = $settings->$ReportPdf->settings->PDF_UNIT;
	$define['PDF_MARGIN_HEADER'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_HEADER);
	$define['PDF_MARGIN_FOOTER'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_FOOTER);
	$define['PDF_MARGIN_TOP'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_TOP);
	$define['PDF_MARGIN_BOTTOM'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_BOTTOM);
	$define['PDF_MARGIN_LEFT'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_LEFT);
	$define['PDF_MARGIN_RIGHT'] = floatval($settings->$ReportPdf->settings->PDF_MARGIN_RIGHT);
	$define['PDF_FONT_NAME_MAIN'] = $settings->$ReportPdf->settings->PDF_FONT_NAME_MAIN;
	$define['PDF_FONT_SIZE_MAIN'] = $settings->$ReportPdf->settings->PDF_FONT_SIZE_MAIN;
	$define['PDF_FONT_NAME_DATA'] = $settings->$ReportPdf->settings->PDF_FONT_NAME_DATA;
	$define['PDF_FONT_SIZE_DATA'] = $settings->$ReportPdf->settings->PDF_FONT_SIZE_DATA;
	$define['PDF_FONT_MONOSPACED'] = $settings->$ReportPdf->settings->PDF_FONT_MONOSPACED;
	$define['PDF_IMAGE_SCALE_RATIO'] = floatval($settings->$ReportPdf->settings->PDF_IMAGE_SCALE_RATIO);

	// Descriptions
	$define['QM_PAGE_DESCRIPTION'] = $settings->$ReportPdf->settings->QM_PAGE_DESCRIPTION;
	$define['QM_REPORT_DESCRIPTION'] = $settings->$ReportPdf->settings->QM_REPORT_DESCRIPTION;

	// Cellpadding definieren
	$define['QM_CELL_PADDING_T'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_T);
	$define['QM_CELL_PADDING_R'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_R);
	$define['QM_CELL_PADDING_B'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_B);
	$define['QM_CELL_PADDING_L'] = floatval($settings->$ReportPdf->settings->QM_CELL_PADDING_L);
	$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_LINE_TOP);
	$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
	$define['QM_CELL_LAYOUT_WIDTH'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_WIDTH);
	$define['QM_CELL_LAYOUT_CLEAR'] = floatval($settings->$ReportPdf->settings->QM_CELL_LAYOUT_CLEAR);

	// Seitenumbruch
	$define['QM_PAGE_BREAKE'] = $settings->$ReportPdf->settings->QM_PAGE_BREAKE - $PageBreakShift;
	$define['QM_START_FOOTER'] = $settings->$ReportPdf->settings->QM_START_FOOTER - $PageBreakShift;
	$define['QM_LINE_HEIGHT'] = $settings->$ReportPdf->settings->QM_LINE_HEIGHT;
  

   //     $tcpdf->print_version = $print_version;
	$tcpdf->printversion = $version;
	$tcpdf->xdata = $data;
	$tcpdf->xchanges = $changes;
	$tcpdf->xuser = $user;
	$tcpdf->xsettings = $settings;
	$tcpdf->hiddenFields = $hiddenFields;
	$tcpdf->Verfahren = $Verfahren; 
	$tcpdf->HaederEnd = 0;
	//$tcpdf->reportID = $reportID;
	//$tcpdf->ReportImages = $reportimages;
	$tcpdf->SetCreator($define['PDF_CREATOR']);
	$tcpdf->SetAuthor('MBQ Qualitätssicherung');
	$tcpdf->SetTitle('ZfP Prüfbericht');
	$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
	$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
	$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
	$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);
        $tcpdf->reportnumber = $define['QM_REPORT_DESCRIPTION'].' '.$this->Pdf->ConstructReportNamePdf($tcpdf->xdata);
	$tcpdf->ReportPdf = $ReportPdf;
	$tcpdf->ReportEvaluation = $ReportEvaluation;
	$tcpdf->MarginLeftPlus = 0;
	$tcpdf->QM_DPI = 150;
	$tcpdf->QM_INCH = 25.4;

	// bei neuem Prüfbericht (angehängtem Bericht) immer den Header und Footer drucken
	$tcpdf->forcePrintHeaders = true;  
	// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
	//$tcpdf->endPage();
	// neue Seiteneinstellungen eintragen um neuen Header zu schreiben
	$tcpdf->writeSettings($define);
	//$tcpdf->setPageOrientation($define['PDF_PAGE_ORIENTATION'], false, $define['PDF_MARGIN_BOTTOM']);
	$tcpdf->setPageUnit($define['PDF_UNIT']);

}        
	$tcpdf->style0 = array('width' => 0.4, 'color' => array(0, 0, 0));
	$tcpdf->style1 = array('width' => 0.3, 'color' => array(0, 0, 0));
	$tcpdf->style2 = array('width' => 0.1, 'color' => array(0, 0, 0));
	$tcpdf->style3 = array('width' => 0.1, 'color' => array(0, 0, 0));
	$tcpdf->style4 = array('B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.05,
										'color' => array(0, 0, 0)
									),
								) ;
	$tcpdf->style5 = array('B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									),
								) ;
	$tcpdf->style6 = array('R' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style7 = array(	'L' => 0,
							'T' => 0,
							'R' => 0,
							'B' => 0
								) ;

	$tcpdf->style8 = array('R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.1,
										'color' => array(0, 0, 0)
									)
								) ;

	$tcpdf->style9 = array('B' => array(
							'width' => 0.15,
							'color' => array(0, 0, 0)
							)
						) ;

	$tcpdf->style10 = array('R' => array(
								'width' => 0.2,
								'color' => array(0, 0, 0)
							),
							'B' => array(
								'width' => 0.3,
								'color' => array(0, 0, 0)
							)
						) ;
	$tcpdf->style11 = array('R' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style12 = array('R' => array(
										'width' => 0.2,
										'color' => array(0, 0, 0)
									),
							'B' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style13 = array('B' => array(
										'width' => 0.5,
										'color' => array(0, 0, 0)
									)
								) ;
	$tcpdf->style14 = array('L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;
	$tcpdf->style15 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(255, 255, 255)
									),
						) ;
	$tcpdf->style16 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(255, 255, 255)
									),
						) ;
	$tcpdf->style17 = array('B' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;

	$tcpdf->style18 = array('L' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;
	$tcpdf->style19 = array('L' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
							'R' => array(
										'width' => 0.3,
										'color' => array(0, 0, 0)
									),
							'T' => array(
										'width' => 0.15,
										'color' => array(0, 0, 0)
									),
						) ;

	$tcpdf->styleno = array('B' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'L' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'R' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
							'T' => array(
										'width' => 0,
										'color' => array(255, 255, 255)
									),
						) ;

	// Die neuen Bezeichnungen für die Linine
	$tcpdf->LineStyle1 = array(	'width' => 0.15,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines1 = 'TBRL';

	$tcpdf->LineStyle2 = array(	'width' => 0.3,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines2 = 'TBRL';

	$tcpdf->LineStyle7 = array(	'width' => 0.15,
							'cap' => 'butt',
							'join' => 'miter',
							'dash' => 0,
							'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines7 = 'B';
	$tcpdf->LineLineThickness7 = 0.15;

	$tcpdf->LineStyle8 = array(
								'width' => 0.15,
								'cap' => 'butt',
								'join' => 'miter',
								'dash' => 0,
								'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines8 = 'BR';
	$tcpdf->LineLineThickness8 = 0.15;

	$tcpdf->LineStyle9 = array(
								'width' => 0.15,
								'cap' => 'butt',
								'join' => 'miter',
								'dash' => 0,
								'color' => array(0, 0, 0)
						);
	$tcpdf->LineLines9 = 'TRL';
	$tcpdf->LineLineThickness9 = 0.15;

	$tcpdf->LineLines10 = 'RB';
	$tcpdf->LineLineThickness10 = 0.15;

	$tcpdf->LineLines11 = 'B';
	$tcpdf->LineLineThickness11 = 0.15;
        
        $tcpdf->SetY($tcpdf->HaederEnd+5);
        $tcpdf->setCellPaddings(1, 1, 1, 1);
        $tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
        
        $tcpdf->SetY($tcpdf->HaederEnd+5);
        $startx = $define['PDF_MARGIN_LEFT'];
        $starty =  $tcpdf->GetY();
        $tcpdf->SetX($define['PDF_MARGIN_LEFT'] + 0);
        $tcpdf->SetFillColor(235, 232, 232);
        $font = 'calibri';
        $fontweight = 'B';
        $fontheigth = 10;
        $tcpdf->SetFont($font, $fontweight, $fontheigth); 
        foreach($xml_revision->Revision->children() as $_key => $_xml){
             if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
             $datahead = trim($_xml->discription->$locale);
                $tcpdf->MultiCell(
                    $_xml->width,
                    7,
                    $datahead,
                    // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                    1,//$tcpdf->$LineStyle,
                    'L',
                    1,
                    1,
                    $startx,
                    $starty,
                    true,
                    0,
                    false,
                    true,
                    7,
                    'T',
                    true
                );
             $startx = $startx +   $_xml->width;

        } 

        foreach ($revision as $revisions_key => $revisions) {

         
            foreach ($revisions as $r_key => $r_value) {
       
                $r_value['Revision'] ['revision'] = $revisions_key;
                if($tcpdf->GetY() >= $define['QM_PAGE_BREAKE']){ 
                    $tcpdf->AddPage($define['PDF_PAGE_ORIENTATION'], $define['PDF_PAGE_FORMAT']);
                    $tcpdf->SetY($tcpdf->HaederEnd+5);
                    $startx = $define['PDF_MARGIN_LEFT'];
                    $starty =  $tcpdf->GetY();
                    $tcpdf->SetX($define['PDF_MARGIN_LEFT'] + 0);
                    $tcpdf->SetFillColor(235, 232, 232);
                    $font = 'calibri';
                    $fontweight = 'B';
                    $fontheigth = 10;
                    $tcpdf->SetFont($font, $fontweight, $fontheigth); 
                    foreach($xml_revision->Revision->children() as $_key => $_xml){
                         if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
                         $datahead = trim($_xml->discription->$locale);
                            $tcpdf->MultiCell(
                                $_xml->width,
                                7,
                                $datahead,
                                // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                                1,//$tcpdf->$LineStyle,
                                'L',
                                1,
                                1,
                                $startx,
                                $starty,
                                true,
                                0,
                                false,
                                true,
                                7,
                                'T',
                                true
                            );
                         $startx = $startx +   $_xml->width;

                    } 
                }


                $revrow = $r_value ['Revision']['row'];
                $revmodel = $r_value ['Revision'] ['model'];
                if(isset($reportsettings->$revmodel->$revrow)){ 
                    $fielddiscript = trim($reportsettings->$revmodel->$revrow->discription->$locale);
                    if($reportsettings->$revmodel->$revrow->fieldtype == 'radio') {
                        $r_value ['Revision'] ['this_value'] = $reportsettings->$revmodel->$revrow->radiooption->value[intval($r_value ['Revision'] ['this_value'])];
                        $r_value ['Revision'] ['last_value'] = $reportsettings->$revmodel->$revrow->radiooption->value[intval($r_value ['Revision'] ['last_value'])];
                    }        
                }else $fielddiscript = '';
                
                !empty($fielddiscript)?$r_value ['Revision']['row']= $fielddiscript:'';
                $startx = $define['PDF_MARGIN_LEFT'];
                $starty =  $tcpdf->GetY();
                foreach($xml_revision->Revision->children() as $_key => $_xml){

                    if(trim($_xml->output->print) != 1 || empty($_xml->output->print)) continue;
                    $dataout = $r_value['Revision'] [$_key];
                    $font = 'calibri';
                    $fontweight = 'N';
                    $fontheigth = 10;
                    $tcpdf->SetFillColor(255, 255, 255);
                    $tcpdf->SetFont($font, $fontweight, $fontheigth); 
                    $tcpdf->MultiCell(
                                $_xml->width,
                                7,
                                $dataout,
                                // die Linien werden in die Mulitcell gezeichnet, da alle Spalten immeer gleich groß sind
                                1,//$tcpdf->$LineStyle,
                                'L',
                                1,
                                1,
                                $startx,
                                $starty,
                                true,
                                0,
                                false,
                                true,
                                7,
                                'T',
                                true
                            );
                       // $tcpdf->SetY($tcpdf->GetY() + 2);
                        $startx = $startx +   $_xml->width;
                }
         
        }

    //$tcpdf->SetY($tcpdf->GetY() + 2);    
}

header('Content-Type: application/pdf');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename="Revision_.pdf"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

ob_end_clean();	
set_time_limit(0);

echo $tcpdf->Output('revision_.pdf', 'D');
?>