<?php
App::import('Vendor','statistics_results');

$define = array();
$ReportPdf = 'searchPdf';

$define['K_PATH_CACHE'] = sys_get_temp_dir().'/';
$define['K_BLANK_IMAGE'] = '_blank.png';
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
$define['QM_PAGE_BREAKE'] = $settings->$ReportPdf->settings->QM_PAGE_BREAKE;
$define['QM_START_FOOTER'] = $settings->$ReportPdf->settings->QM_START_FOOTER;
$define['QM_LINE_HEIGHT'] = $settings->$ReportPdf->settings->QM_LINE_HEIGHT;

$tcpdf = new XTCPDF($define['PDF_PAGE_ORIENTATION'], $define['PDF_UNIT'], 'A4', true, 'UTF-8');
$tcpdf->xuser = CakeSession::read('Auth.user');
$tcpdf->xdata = $settings;
$tcpdf->xsettings = $settings;
$tcpdf->ReportPdf = $ReportPdf;

$tcpdf->writeSettings($define);

// Linien
$tcpdf->style0 = array('width' => 0.50, 'color' => array(0, 0, 0));

$tcpdf->SetCreator($define['PDF_CREATOR']);
$tcpdf->SetAuthor('Wacker GmbH');
$tcpdf->SetTitle('ZfP Statistik');
$tcpdf->SetMargins($define['PDF_MARGIN_LEFT'], $define['PDF_MARGIN_TOP'], $define['PDF_MARGIN_RIGHT']);
$tcpdf->SetHeaderMargin($define['PDF_MARGIN_HEADER']);
$tcpdf->SetFooterMargin($define['PDF_MARGIN_FOOTER']);
$tcpdf->setCellPaddings($define['QM_CELL_PADDING_L'], $define['QM_CELL_PADDING_T'], $define['QM_CELL_PADDING_R'], $define['QM_CELL_PADDING_B']);

// vorherige Seite beenden, um den Footer mit den alten Seiteneinstellungen zu schreiben
$tcpdf->endPage();
$tcpdf->AddPage();
$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);

// Seiteninhalte
// MultiCell ($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, $valign='T', $fitcell=false)
$tcpdf->SetFont('calibrib','N',8);
$border = array('RB'=>array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
$tcpdf->SetFillColor(240, 240, 240);

/**********************************************************************
 * Datentabellen rendern
 **********************************************************************/

$width = $define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_L'] - $define['QM_CELL_PADDING_R'];

$tcpdf->MultiCell($width, 8, implode(', ',$GetSearchFieldsString), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');

// Nähte nach Art
$tcpdf->MultiCell($width, 8, __('Weld type total'), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

foreach(array(__('Type of weld', true), __('Amount', true), __('Percentage', true)) as $desc) { $tcpdf->MultiCell($width/3, 5, $desc, 1, 'C', true, 0); }

$c = 0;
foreach($this->request->data['extra']['welds']['statistics']['all'] as $_key => $_welds) {
	
	if($_key == 'reports') continue;
	
	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) {
		$tcpdf->AddPage();
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);
	}
	else {
		$tcpdf->Ln();
		$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);
	}

	$border = array('LR'=>1);
	$border['B'] = 2;
	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) $border['B'] = 2;

	$tcpdf->MultiCell($width/3, 5, __('Weld type '.$_key), $border, 'L', false, 0);
	$tcpdf->MultiCell($width/3, 5, $_welds, $border, 'C', false, 0);
	$tcpdf->MultiCell($width/3, 5, @round($_welds * 100 / $this->request->data['extra']['welds']['statistics']['all']['all'], 2).'%', $border, 'C', false, 0);
}

// Nähte nach Monaten
$tcpdf->Ln();
$tcpdf->Ln();
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

$tcpdf->MultiCell($width, 8, __('Overview'), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

//pr($this->request->data['extra']['welds']['statistics']['all']);
//pr($this->request->data['extra']['months']);
//die();
//pr($this->request->data['extra']['diagrammdata']);
//die();
foreach(array(__('Tag/Monat/Jahr', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true)) as $desc) { 
	$tcpdf->MultiCell($width/5, 5, $desc, 1, 'C', true, 0); 
}

foreach($this->request->data['extra']['welds']['statistics'][$this->request->data['extra']['diagrammdata']] as $_key => $_month) {

	$day = null;
	$month = null;
	$year = null;
	$date = null;
	$date_array = explode('.',$_key);
	if(isset($date_array[2])) $day = $date_array[2];
	if(isset($date_array[1])) $month = $date_array[1];
	if(isset($date_array[0])) $year = $date_array[0];
	if($day != null) $date .= $day.'.';
	if($month != null) $date .= $month.'.';
	if($year != null) $date .= $year;
	
//	$row = array($this->request->data['extra']['months'][$month] . ' ' . $year, $_month['all'], $_month['e'], $_month['ne'], $_month['-']);

	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) {
		$tcpdf->AddPage();
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);

		foreach(array(__('Tag/Monat/Jahr', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true)) as $desc) { 
			$tcpdf->MultiCell($width/5, 5, $desc, 1, 'C', true, 0); 
		}
		$tcpdf->Ln();
	}
	else {
		$tcpdf->Ln();
		$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);
	}

	$border = array('LR'=>1);
	$border['B'] = 2;
	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) $border['B'] = 2;
//pr($this->request->data['extra']['months'][$month] . ' ' . $year, $_month['all']);
//pr($_month);
//	$tcpdf->MultiCell($width/5, 5, $_month, 1, 'C', true, 0); 
	$tcpdf->MultiCell($width/5, 5, $date, $border, 'C', false, 0);
	$tcpdf->MultiCell($width/5, 5, $_month['all'], $border, 'C', false, 0);
	$tcpdf->MultiCell($width/5, 5, $_month['e'], $border, 'C', false, 0);
	$tcpdf->MultiCell($width/5, 5, $_month['ne'], $border, 'C', false, 0);
	$tcpdf->MultiCell($width/5, 5, $_month['-'], $border, 'C', false, 0);
}

// Nähte nach Schweißern
$tcpdf->Ln();
$tcpdf->Ln();
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

$tcpdf->MultiCell($width, 8, __('Involved welders'), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

foreach(array(__('Welder', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true), __('Percentage',true)) as $desc) { 
	$tcpdf->MultiCell($width/6, 5, $desc, 1, 'C', true, 0); 
}

foreach($this->request->data['extra']['welders'] as $_key => $_welders) {

	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) {
		
		$tcpdf->AddPage();
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);
		
		foreach(array(__('Welder', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true), __('Percentage',true)) as $desc) { 
			$tcpdf->MultiCell($width/6, 5, $desc, 1, 'C', true, 0); 
		}
		$tcpdf->Ln();
	}
	else {
		$tcpdf->Ln();
		$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);
	}

	$border = array('LR'=>1);
	$border['B'] = 2;

	$row = array($_key,$_welders['all'],$_welders['e'],$_welders['ne'],$_welders['-'], @round(100 * $_welders['e'] / ($_welders['all'] - $_welders['-']),2) . '%');

	$tcpdf->MultiCell($width/6, 5, $_key, $border, 'L', false, 0);
	$tcpdf->MultiCell($width/6, 5, $_welders['all'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/6, 5, $_welders['e'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/6, 5, $_welders['ne'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/6, 5, $_welders['-'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/6, 5, @round(100 * $_welders['e'] / $_welders['all'] ,2) . '%', $border, 'L', false, 0);
}

// Schweißfehler nach Häufigkeit
$tcpdf->Ln();
$tcpdf->Ln();
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

$tcpdf->MultiCell($width, 8, __('Weld errors by frequency'), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);

foreach(array(__('Type of error', true), __('Amount (all Welds)', true), __('Amount (Ne-Welds)',true)) as $desc) { 
	$tcpdf->MultiCell($width/3, 5, $desc, 1, 'C', true, 0); 
}

foreach($this->request->data['extra']['welderrors']['all'] as $_key => $_welderrors){

	if($_welderrors['value'] == 0)continue;

	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) {
		$tcpdf->AddPage();
		$tcpdf->SetXY($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L'], $tcpdf->HeaderEnd+$define['QM_CELL_PADDING_T']);
		
		foreach(array(__('Type of error', true), __('Amount (all Welds)', true), __('Amount (Ne-Welds)',true)) as $desc) { 
			$tcpdf->MultiCell($width/3, 5, $desc, 1, 'C', true, 0); 
		}
		$tcpdf->Ln();
	}
	else {
		$tcpdf->Ln();
		$tcpdf->SetX($define['PDF_MARGIN_LEFT']+$define['QM_CELL_PADDING_L']);
	}

	$border = array('LR'=>1);
	$border['B'] = 2;
	if($tcpdf->GetY() > $define['QM_PAGE_BREAKE']) $border['B'] = 2;

	$tcpdf->MultiCell($width/3, 5, $_welderrors['code'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/3, 5, $_welderrors['value'], $border, 'L', false, 0);
	$tcpdf->MultiCell($width/3, 5, isset($this->request->data['extra']['welderrors']['ne'][$_key]) ? $this->request->data['extra']['welderrors']['ne'][$_key]['value'] : 0, $border, 'L', false, 0);

}

$tcpdf->endPage();

if($this->request->data['extra']['welds']['statistics']['all']['all'] > 0){

	$tcpdf->addPage($define['PDF_PAGE_ORIENTATION']);
	$tcpdf->SetY($tcpdf->HeaderEnd + $define['QM_CELL_PADDING_T'] + 0.1);

	if($overviewData != false){
		$tcpdf->MultiCell($width, 8, $overviewCaption, 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
		$tcpdf->image(
			'@'.$overviewData,
			$define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.1,
			$tcpdf->GetY(),
			$define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_R'] - $define['QM_CELL_PADDING_L'] - 0.2,
			$define['QM_PAGE_BREAKE'] - 16,
			'',
			'',
			'C'
		);
	} else {
		$tcpdf->MultiCell($width, 8, __('no date to display',true), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
	}
}


if($this->request->data['extra']['welds']['statistics']['all']['all'] > 0){
	$tcpdf->addPage();
	$tcpdf->SetY($tcpdf->HeaderEnd + $define['QM_CELL_PADDING_T'] + 0.1);

	if($welderData != false){
		$tcpdf->MultiCell($width, 8, $welderCaption, 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
		$tcpdf->image(
		'@'.$welderData,
			$define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.1,
			$tcpdf->GetY(),
			$define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_R'] - $define['QM_CELL_PADDING_L'] - 0.2,
			$define['QM_PAGE_BREAKE'] - 16,
			'',
			'',
			'C'
		);
	} else {
		$tcpdf->MultiCell($width, 8, $welderCaption, 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
	}

}

if($this->request->data['extra']['welds']['statistics']['all']['all'] > 0){
	$tcpdf->addPage();
	$tcpdf->SetY($tcpdf->HeaderEnd + $define['QM_CELL_PADDING_T'] + 0.1);
	
	if($errorData != false){
		$tcpdf->MultiCell($width, 8, $errorCaption, 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
		$tcpdf->image(
			'@'.$errorData,
			$define['PDF_MARGIN_LEFT'] + $define['QM_CELL_PADDING_L'] + 0.1,
			$tcpdf->GetY(),
			$define['QM_CELL_LAYOUT_CLEAR'] - $define['PDF_MARGIN_LEFT'] - $define['QM_CELL_PADDING_R'] - $define['QM_CELL_PADDING_L'] - 0.2,
			$define['QM_PAGE_BREAKE'] - 16,
			'',
			'',
			'C'
		);
	} else {
		$tcpdf->MultiCell($width, 8, __('no date to display',true), 0, 'L', false, 1, '', '', true, 0, false, true, 8, 'M');
	}
}

$tcpdf->Output(str_replace(' ', '_', __('Search results')).'.pdf', 'D');