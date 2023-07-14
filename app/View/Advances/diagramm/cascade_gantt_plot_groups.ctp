<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_gantt');

if(!isset($data['AdvancesCascade'])) return null;

$Karenz = 12;
$SollColor = 'azure3';
$IstColor = 'peachpuff4';

$TitleFontColor = 'black';

$ThisTitleFontColor = $TitleFontColor;

$TitleFontColorFinish = 'forestgreen';
$TitleFontColorInTime = 'dodgerblue3';
$TitleFontColorError = 'red';
$TitleFontColorHint = 'orange3';

$graph = new GanttGraph(2000,0);
$graph->SetMarginColor('white:1.7');
$graph->SetColor('white');
$graph->SetBox(false);
$graph->SetFrame(false,'black:0.25',1);
// Setup the graph title and title font
$graph->title->Set($data['CascadeGroupTitle']);
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,12);
$graph->SetDateRange($data['AdvancesCascade']['start'],$data['AdvancesCascade']['end']);
// Show three headers
$graph->ShowHeaders( GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->scale->week->SetFont(FF_FONT0);
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR2);

// Set the column headers and font
$graph->scale->actinfo->SetColTitles( array(__('Equipments',true),__('Start',true),__('End',true),__('Count',true),__('Completed',true),__('repair',true)),array(100));
$graph->scale->actinfo->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
$graph->scale->divider->SetWeight(3);

$y = 0;

foreach ($data['Scheme']['CascadeGroup'] as $key => $value) {

  if(!isset($value['Description'])) continue;
  if(!isset($value['Status'])) continue;

  $bar = new GanttBar($y,array(
				utf8_decode($value['Description']),
				$data['AdvancesCascade']['start'],
				$data['AdvancesCascade']['end'],
        ' ',
        ' ',
        ' '
			),
		$data['AdvancesCascade']['start'],
		$data['AdvancesCascade']['end'],
		0.35
	);

  // Add group markers
  $bar->leftMark->SetType( MARK_LEFTTRIANGLE );
  $bar->leftMark->Show();
  $bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
  $bar->rightMark->Show();
  $bar->SetFillColor('#a6a6a6');
  $bar->SetPattern(BAND_SOLID,'#a6a6a6');

  $graph->Add($bar);

  $y++;

  if(!is_array($value['Status'])) continue;
  if(count($value['Status']) == 0) continue;

  foreach ($value['Status'] as $_key => $_value) {

    $bar = new GanttBar($y,array(
  				' > ' . utf8_decode(__($_key,true)),
  				' ',
  				' ',
          strval($_value['result']['count']),
          strval($_value['result']['okay']),
          strval($_value['result']['error'])
  			),
  		$data['AdvancesCascade']['start'],
  		$data['AdvancesCascade']['end'],
  		0.35
  	);

    $fmt = numfmt_create('en_EN', NumberFormatter::DECIMAL);
    $progress = numfmt_format($fmt, $_value['result']['advance_all_deci']);
    $bar->progress->Set($progress);
  	$bar->progress->SetFillColor($_value['advance_line_color']);
  	$bar->progress->SetPattern(BAND_SOLID,$_value['advance_line_color']);
//    $bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
  	$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
  	$bar->leftMark->Show();
  	$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
  	$bar->rightMark->Show();
  	$bar->SetFillColor('white');
  	$bar->SetPattern(BAND_SOLID,'white');
//  	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
  	$bar->title->SetColor($ThisTitleFontColor);

    $graph->Add($bar);
    $y++;

  }

  $y++;
  $y++;
}

//die();
/*
foreach ($data['Status'] as $key => $value) {

  $fmt = numfmt_create('en_EN', NumberFormatter::DECIMAL);
  $progress = numfmt_format($fmt, $value['result']['advance_all_deci']);
  $bar->progress->Set($progress);
	$bar->progress->SetFillColor($value['advance_line_color']);
	$bar->progress->SetPattern(BAND_SOLID,$value['advance_line_color']);
  $bar->title->SetColumnFonts(array(array(FF_DV_SANSSERIF,FS_BOLD,11)));
	$bar->leftMark->SetType( MARK_LEFTTRIANGLE );
	$bar->leftMark->Show();
	$bar->rightMark->SetType( MARK_RIGHTTRIANGLE );
	$bar->rightMark->Show();
	$bar->SetFillColor('white');
	$bar->SetPattern(BAND_SOLID,'white');
	$bar->title->SetFont(FF_DV_SANSSERIF,FS_NORMAL,10);
	$bar->title->SetColor($ThisTitleFontColor);

  $graph->Add($bar);

  $y++;
  $y++;
}
*/
if(isset($return) && $return === true) {
	$data = $graph->Stroke(_IMG_HANDLER);
	ob_start();
	imagepng($data);
	$data = ob_get_contents();
	ob_end_clean();
	echo $data;
} else {
	$graph->Stroke();
}
?>
