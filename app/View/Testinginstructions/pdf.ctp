<?php
App::import('Vendor','testinginstruction_info');

$tcpdf = new XTCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
//$tcpdf->SetAutoPageBreak(true, 30);

$tcpdf->SetTopMargin(50);
$tcpdf->SetLeftMargin($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_LEFT);
$tcpdf->SetRightMargin($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_RIGHT);


$tcpdf->xsettings = $arrayDataDetail;
$tcpdf->xdata = $pdfdata;


	$define = array();
//Einstellungen aus der PDF
	$define['radiodefault'] = $this->Pdf->radiodefault;
	$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
	$define['K_BLANK_IMAGE'] = '_blank.png';
	$define['PDF_BARCODE'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_BARCODE;
	$define['HEAD_MAGNIFICATION'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->HEAD_MAGNIFICATION);
	$define['K_CELL_HEIGHT_RATIO'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->K_CELL_HEIGHT_RATIO);
	$define['K_TITLE_MAGNIFICATION'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->K_TITLE_MAGNIFICATION);
	$define['K_SMALL_RATIO'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->K_SMALL_RATIO;
	$define['K_THAI_TOPCHARS'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->K_THAI_TOPCHARS;
	$define['K_TCPDF_CALLS_IN_HTML'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->K_TCPDF_CALLS_IN_HTML;
	$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
	$define['PDF_PAGE_FORMAT'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_PAGE_FORMAT;
	$define['PDF_PAGE_ORIENTATION'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_PAGE_ORIENTATION;
	$define['PDF_CREATOR'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_CREATOR;
	$define['PDF_AUTHOR'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_AUTHOR;
	$define['PDF_HEADER_TITLE'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_HEADER_TITLE;
	$define['PDF_HEADER_STRING'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_HEADER_STRING;
	$define['PDF_UNIT'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_UNIT;
	$define['PDF_MARGIN_HEADER'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_HEADER);
	$define['PDF_MARGIN_FOOTER'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_FOOTER);
	$define['PDF_MARGIN_TOP'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_TOP);
	$define['PDF_MARGIN_BOTTOM'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_BOTTOM);
	$define['PDF_MARGIN_LEFT'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_LEFT);
	$define['PDF_MARGIN_RIGHT'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_MARGIN_RIGHT);
	$define['PDF_FONT_NAME_MAIN'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_FONT_NAME_MAIN;
	$define['PDF_FONT_SIZE_MAIN'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_FONT_SIZE_MAIN;
	$define['PDF_FONT_NAME_DATA'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_FONT_NAME_DATA;
	$define['PDF_FONT_SIZE_DATA'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_FONT_SIZE_DATA;
	$define['PDF_FONT_MONOSPACED'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_FONT_MONOSPACED;
	$define['PDF_IMAGE_SCALE_RATIO'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->PDF_IMAGE_SCALE_RATIO);

	// Descriptions
	$define['QM_PAGE_DESCRIPTION'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->QM_PAGE_DESCRIPTION;
	$define['QM_REPORT_DESCRIPTION'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->QM_REPORT_DESCRIPTION;

	// Cellpadding definieren
	$define['QM_CELL_PADDING_T'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_PADDING_T);
	$define['QM_CELL_PADDING_R'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_PADDING_R);
	$define['QM_CELL_PADDING_B'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_PADDING_B);
	$define['QM_CELL_PADDING_L'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_PADDING_L);
	$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_LAYOUT_LINE_TOP);
	$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
	$define['QM_CELL_LAYOUT_WIDTH'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_LAYOUT_WIDTH);
	$define['QM_CELL_LAYOUT_CLEAR'] = floatval($arrayDataDetail->TestinginstructionsDataPdf->settings->QM_CELL_LAYOUT_CLEAR);

  $define['QM_PAGE_BREAKE'] = $arrayDataDetail->TestinginstructionsDataPdf->settings->QM_PAGE_BREAKE ;
  $MarginLeftPlus = 0;

  $tcpdf->AddPage();
  $tcpdf->SetFont('calibri','n','9');
  $startx = $define['PDF_MARGIN_LEFT'] ;
  $linearrx [] = $startx;
  $rowlength = $define['QM_CELL_LAYOUT_WIDTH'];
  $collength = $rowlength / 2;

  $tcpdf->SetY($define['PDF_MARGIN_TOP']);

    $tcpdf->Ln();
    $tcpdf->MultiCell(
    	$rowlength,
    		0,
    	$pdfdata['description']	,
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
    		0
    	);


$tcpdf->SetY($tcpdf->GetY()+2);
$begin = $tcpdf->GetY();

  foreach ($pdfdata['data'] as $key => $value) {
  if (!empty($linearry))   $tcpdf->SetY(max($linearry)) ;
    $columcounter = 1;

			if ($begin <> $tcpdf->GetY()){$tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['PDF_MARGIN_LEFT'] +$collength+$collength, $tcpdf->GetY(), 1);}

    $tcpdf->SetFont('calibri','b','12');
      $tcpdf->MultiCell(
          $rowlength,
          0,
          $key,
          0,
          'L',
          0,
          1,
          $define['PDF_MARGIN_LEFT'],
          $tcpdf->GetY()+ 2,
          true,
          0,
          false,
          true,
          0,
          'T',
          20
        );
        $tcpdf->SetFont('calibri','n','9');
        $yvalue = $tcpdf->GetY()+2;

        $xvalue = $define['PDF_MARGIN_LEFT'];
        //$linearry [] = $tcpdf->GetY();
        $columncount = 1;
        $row = $tcpdf->GetY();
        $startx = $define['PDF_MARGIN_LEFT'] ;
        $x = $startx;
        $linearry =array();
				$page = 1;
        foreach ($value as $vkey => $vvalue) {
            $tcpdf->SetFont('calibri','b','9');

            if ($tcpdf->GetY() + $tcpdf->GetStringHeight($collength,$vvalue['field'].$vvalue['value']) > $define['QM_PAGE_BREAKE']){
							$tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), $define['PDF_MARGIN_LEFT'] +$rowlength, max($linearry), 1);
							$pagebefore = $page;
              $tcpdf->AddPage();
							$page++;
							$linearry = array();
							$tcpdf->SetY($define['PDF_MARGIN_TOP']);
							$x = $define['PDF_MARGIN_LEFT'] ;
							$columncount = 1;

            }
						if($columncount == 1){
							$tcpdf->Line($define['PDF_MARGIN_LEFT'], $tcpdf->GetY(), $define['PDF_MARGIN_LEFT'] +$collength+$collength, $tcpdf->GetY(), 1);

						}

							$row = $tcpdf->GetY();
              if(empty($vvalue['value']))$vvalue['value'] = '/';

              $tcpdf->MultiCell(
            	 $collength,
            		0,
                $vvalue['field']	,
            		0,
            		'L',
            		0,
            		1,
                $x,
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
            $tcpdf->MultiCell(
            	 $collength,
            		0,
                $vvalue['value']	,
            		0,
            		'L',
            		0,
            		1,
                $x,
            		$tcpdf->GetY(),
            		true,
            		0,
            		false,
            		true,
            		0,
            		'T',
            		20
            	);
							$linearry [] =	$tcpdf->GetY();
							$columncount ++;
							if($columncount == 2 ){
							$x = $x + $collength;
							$tcpdf->SetY($row);
							$rowcount = 1;

						}else {
							$x = $startx;
							$columncount = 1;

							$tcpdf->SetY(max($linearry));


						}

						 $linearrx [] = $collength+$define['PDF_MARGIN_LEFT'] ;
						 $linearrx [] = $rowlength + $define['PDF_MARGIN_LEFT'];
						 $linearrx = array_unique ($linearrx);

					 foreach ($linearrx as $lxkey => $lxvalue) {
						 $tcpdf->Line($lxvalue, $row, $lxvalue,  max($linearry), 1);

					 }

				 }
			 }

$tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry) , $define['PDF_MARGIN_LEFT'] +$rowlength,max($linearry), 1);

if(isset($return_show) && $return_show === true){

	$out = $tcpdf->Output('testinstruction_.pdf', 'S');
	$string = base64_encode($out);

	echo $string;

	return;

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

echo $tcpdf->Output('Prüfanweisung.pdf', 'D');
?>
