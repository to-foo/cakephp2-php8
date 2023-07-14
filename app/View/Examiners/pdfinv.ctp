<?php
App::import('Vendor','examiner_inv');
$tcpdf = new XTCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$tcpdf->xdata = $this->request->data;
$tcpdf->SetAutoPageBreak(false,0 );
$tcpdf->SetTopMargin(50);
$ExaminerPdf = "ExaminerPdf";

$settings = $xml_examinerinv;
//$tcpdf->SetFont('calibri','n','9');

$define = array();

$define['radiodefault'] = $this->Pdf->radiodefault;

$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
$define['K_BLANK_IMAGE'] = '_blank.png';
$define['PDF_BARCODE'] = $settings->$ExaminerPdf->settings->PDF_BARCODE;
$define['HEAD_MAGNIFICATION'] = floatval($settings->$ExaminerPdf->settings->HEAD_MAGNIFICATION);
$define['K_CELL_HEIGHT_RATIO'] = floatval($settings->$ExaminerPdf->settings->K_CELL_HEIGHT_RATIO);
$define['K_TITLE_MAGNIFICATION'] = floatval($settings->$ExaminerPdf->settings->K_TITLE_MAGNIFICATION);
$define['K_SMALL_RATIO'] = $settings->$ExaminerPdf->settings->K_SMALL_RATIO;
$define['K_THAI_TOPCHARS'] = $settings->$ExaminerPdf->settings->K_THAI_TOPCHARS;
$define['K_TCPDF_CALLS_IN_HTML'] = $settings->$ExaminerPdf->settings->K_TCPDF_CALLS_IN_HTML;
$define['K_TCPDF_THROW_EXCEPTION_ERROR'] = $settings->$ExaminerPdf->settings->K_TCPDF_THROW_EXCEPTION_ERROR;
$define['PDF_PAGE_FORMAT'] = $settings->$ExaminerPdf->settings->PDF_PAGE_FORMAT;
$define['PDF_PAGE_ORIENTATION'] = $settings->$ExaminerPdf->settings->PDF_PAGE_ORIENTATION;
$define['PDF_CREATOR'] = $settings->$ExaminerPdf->settings->PDF_CREATOR;
$define['PDF_AUTHOR'] = $settings->$ExaminerPdf->settings->PDF_AUTHOR;
$define['PDF_HEADER_TITLE'] = $settings->$ExaminerPdf->settings->PDF_HEADER_TITLE;
$define['PDF_HEADER_STRING'] = $settings->$ExaminerPdf->settings->PDF_HEADER_STRING;
$define['PDF_UNIT'] = $settings->$ExaminerPdf->settings->PDF_UNIT;
$define['PDF_MARGIN_HEADER'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_HEADER);
$define['PDF_MARGIN_FOOTER'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_FOOTER);
$define['PDF_MARGIN_TOP'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_TOP);
$define['PDF_MARGIN_BOTTOM'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_BOTTOM);
$define['PDF_MARGIN_LEFT'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_LEFT);
$define['PDF_MARGIN_RIGHT'] = floatval($settings->$ExaminerPdf->settings->PDF_MARGIN_RIGHT);
$define['PDF_FONT_NAME_MAIN'] = $settings->$ExaminerPdf->settings->PDF_FONT_NAME_MAIN;
$define['PDF_FONT_SIZE_MAIN'] = $settings->$ExaminerPdf->settings->PDF_FONT_SIZE_MAIN;
$define['PDF_FONT_NAME_DATA'] = $settings->$ExaminerPdf->settings->PDF_FONT_NAME_DATA;
$define['PDF_FONT_SIZE_DATA'] = $settings->$ExaminerPdf->settings->PDF_FONT_SIZE_DATA;
$define['PDF_FONT_MONOSPACED'] = $settings->$ExaminerPdf->settings->PDF_FONT_MONOSPACED;
$define['PDF_IMAGE_SCALE_RATIO'] = floatval($settings->$ExaminerPdf->settings->PDF_IMAGE_SCALE_RATIO);

// Descriptions
$define['QM_PAGE_DESCRIPTION'] = $settings->$ExaminerPdf->settings->QM_PAGE_DESCRIPTION;
$define['QM_REPORT_DESCRIPTION'] = $settings->$ExaminerPdf->settings->QM_REPORT_DESCRIPTION;

// Celladding definieren
$define['QM_CELL_PADDING_T'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_PADDING_T);
$define['QM_CELL_PADDING_R'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_PADDING_R);
$define['QM_CELL_PADDING_B'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_PADDING_B);
$define['QM_CELL_PADDING_L'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_PADDING_L);
$define['QM_CELL_LAYOUT_LINE_TOP'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_LAYOUT_LINE_TOP);
$define['QM_CELL_LAYOUT_LINE_BOTTOM'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_LAYOUT_LINE_BOTTOM);
$define['QM_CELL_LAYOUT_WIDTH'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_LAYOUT_WIDTH);
$define['QM_CELL_LAYOUT_CLEAR'] = floatval($settings->$ExaminerPdf->settings->QM_CELL_LAYOUT_CLEAR);

// Seitenumbruch
$define['QM_PAGE_BREAKE'] = $settings->$ExaminerPdf->settings->QM_PAGE_BREAKE ;
	//$define['QM_START_FOOTER'] = $settings->$DevicePdf->settings->QM_START_FOOTER - $PageBreakShift;
$define['QM_LINE_HEIGHT'] = $settings->$ExaminerPdf->settings->QM_LINE_HEIGHT;


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

if($this->Session->check('searchexaminer.params')) {
  $tcpdf->Ln();
  $tcpdf->MultiCell('', 8,  __('search parameters').':'.PHP_EOL.$this->Session->read('searchexaminer.params'), 0, 'L', false, 1, '',$tcpdf->GetY()+5, true, 0, false, true, 8, '');
}

$ybegin = $tcpdf->GetY()+ $define['PDF_MARGIN_TOP'];

$tcpdf->SetY($ybegin);
$tcpdf->Ln();

$lineArray = array();
$CellHeight = 6;
$CellHeightData = 8;
$CellHeightDataHead = 12;

// Hier wird das Array mit den auszugebenden Werten durchlaufen
// Hier werden die Zeilen für die Tabelle gebildet

//Tabellenkopf
$newpage = true;
$maxdev = count($Examiners);
$countdev = 0;

$page_separator_field = 'working_place';
$page_separator = '';
$fake_page = 1;
$page_break = 0;

if(isset($this->request->data['Examiner']['page_break']) && $this->request->data['Examiner']['page_break'] == 1) $page_break = 1;

foreach($Examiners as $key => $ExaminersData){

  // Wenn nix da ist wir die Schleife abgebrochen
  if(count($ExaminersData) == 0) break;

  $countdev++;
  // $tcpdf->Ln();
  $linearry[] = $tcpdf->GetY();

  if(isset($page_separator_field) && !empty($page_separator_field)){

    if(empty($ExaminersData[$page_separator_field])) $this_page_seperator_field = '-';
    else $this_page_seperator_field = $ExaminersData[$page_separator_field];

    if(empty($page_separator)) $page_separator = $this_page_seperator_field;

    if($page_break == 0 && $page_separator != $this_page_seperator_field){

      $page_separator = $this_page_seperator_field;
      $fake_page++;
      $newpage = true;

      $tcpdf->AddPage();
      $tcpdf->setCellPaddings(0.9, 0.9, 0.9, 0.9);

      $ybegin = $tcpdf->GetY()+ $define['PDF_MARGIN_TOP'];
      $tcpdf->SetY($ybegin);
      $Yrow = $tcpdf->GetY();
      $linearry = array();
      $linearrx = array();
      $linearry[] = $tcpdf->GetY();

      $tcpdf->Ln();

    }
  }

  // 1. Seite Tabellenkopf immer mit ausgegeben / wenn Neue Seite erstellt wurde -> Tabellenkopf ausgeben
  if($newpage === true){

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

    foreach($tcpdf->xsettings->Examiner->children() as $_ExaminersKey => $_ExaminersSettings) {

      if(!isset($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf))continue;
      if(empty($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->output)) continue;

      $_datahead = trim($_ExaminersSettings->discription->$locale);

      $tcpdf->MultiCell(
        $tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->positioning->width,
        $CellHeightDataHead,
        $_datahead,
        1,
        'L',
        1,
        1,
        $startx,
        $ybegin,
        true,
        0,
        false,
        true,
        $CellHeightDataHead,
        'T',
        true
      );

      $startx = $startx +  $tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->positioning->width;

    }

    $newpage = false;
  }

  $font = 'calibri';
  $fontweight = 'N';
  $fontheigth = 9;

  $tcpdf->SetFont($font, $fontweight, $fontheigth);

  $startx = $define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus;
  $Yrow = $tcpdf->GetY();
  $linearrx[] = $tcpdf->GetX();

  // hier werden die Spalten in den Zeilen gebildet
  foreach($tcpdf->xsettings->Examiner->children() as $_ExaminersKey => $_ExaminersSettings) {

    if(!isset($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf)) continue;
    if(empty($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->output)) continue;

    if(!empty($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->fontcolordata->color)){

      $tcpdf->SetTextColor(
        trim($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->fontcolordata->color[0]),
        trim($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->fontcolordata->color[1]),
        trim($tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->fontcolordata->color[2])
      );

    }

    if($_ExaminersKey == 'eyecheck_date'){
      if(!empty($Examiners[$key]['eyecheck_date_color']) && is_array($Examiners[$key]['eyecheck_date_color']) && count($Examiners[$key]['eyecheck_date_color']) == 3){

        $tcpdf->SetTextColor(
          trim($Examiners[$key]['eyecheck_date_color'][0]),
          trim($Examiners[$key]['eyecheck_date_color'][1]),
          trim($Examiners[$key]['eyecheck_date_color'][2])
        );

      }
    }

    $_data = trim($Examiners[$key][$_ExaminersKey]) ;

    // Wenn das Datenfeld leer ist, wird ein Schrägstrich gesetzt
//  if(empty($_data) && Configure::read('ReplaceEmptyValuesInPrint')) $_data = '/';

    // Daten ausgeben
    $tcpdf->MultiCell(
      $tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->positioning->width,
      0,
      $_data,
      '',
      'L',
      0,
      1,
      $startx,
      $Yrow,
      true,
      0,
      false,
      true,
      0,
      'T'
    );

    $startx = $startx +  $tcpdf->xsettings->Examiner->$_ExaminersKey->pdf->positioning->width;
    $linearrx [] = $startx;
    $linearry [] = $tcpdf->GetY(); // Positionen für Vertikale Linien

    $tcpdf->SetTextColor(0,0,0);

  }

  foreach ($linearrx as $key => $value) {
    $tcpdf->Line($value,min($linearry) , $value, max($linearry)); //Vertikale Linien
  }

  $tcpdf->Line($define['PDF_MARGIN_LEFT'], max($linearry), $define['QM_CELL_LAYOUT_CLEAR'],max($linearry));

  $tcpdf->SetY(max($linearry));

  $linearry = array();
  $linearrx = array();

  $linearry[] = $tcpdf->GetY(); // > Wert für Hor. Linien

  $tcpdf->SetX($define['PDF_MARGIN_LEFT'] + $tcpdf->MarginLeftPlus);

  if($tcpdf->GetY() > $define['QM_PAGE_BREAKE'] && $countdev < $maxdev){  //wenn Seitenende erreicht -> neue Seite angelegen

    $newpage = true;

    $tcpdf->AddPage();
    $tcpdf->setCellPaddings(0.9, 0.9, 0.9, 0.9);
    $tcpdf->SetY($ybegin);

    $linearry = array();
    $linearrx = array();
    $linearry[] = $tcpdf->GetY();
    $linearrx [] = $startx;

  }
}

if(isset($Output) && $Output === true){

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

} else {

  $data = $tcpdf->Output($Filename . '.pdf', 'S');
	echo $data;

}
